<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SmsBundle\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Mautic\ChannelBundle\Entity\MessageQueue;
use Mautic\ChannelBundle\Model\MessageQueueModel;
use Mautic\CoreBundle\Event\TokenReplacementEvent;
use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\CoreBundle\Model\AjaxLookupModelInterface;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\LeadBundle\Entity\DoNotContactRepository;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PageBundle\Model\TrackableModel;
use Mautic\SmsBundle\Entity\Sms;
use Mautic\SmsBundle\Entity\Stat;
use Mautic\SmsBundle\Event\SmsEvent;
use Mautic\SmsBundle\Event\SmsSendEvent;
use Mautic\SmsBundle\Sms\TransportChain;
use Mautic\SmsBundle\SmsEvents;
use Mautic\UserBundle\Model\UserModel;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class SmsModel
 * {@inheritdoc}
 */
class SmsModel extends FormModel implements AjaxLookupModelInterface
{
    /**
     * @var TrackableModel
     */
    protected $pageTrackableModel;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var MessageQueueModel
     */
    protected $messageQueueModel;

    /**
     * @var
     */
    protected $transport;

    /**
     * @var
     */
    protected $userModel;

    /**
     * SmsModel constructor.
     *
     * @param TrackableModel    $pageTrackableModel
     * @param LeadModel         $leadModel
     * @param MessageQueueModel $messageQueueModel
     * @param TransportChain    $transport
     * @param UserModel         $userModel
     */
    public function __construct(TrackableModel $pageTrackableModel, LeadModel $leadModel, MessageQueueModel $messageQueueModel, TransportChain $transport, UserModel $userModel)
    {
        $this->pageTrackableModel = $pageTrackableModel;
        $this->leadModel          = $leadModel;
        $this->messageQueueModel  = $messageQueueModel;
        $this->transport          = $transport;
        $this->userModel          = $userModel;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Mautic\SmsBundle\Entity\SmsRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticSmsBundle:Sms');
    }

    /**
     * @return \Mautic\SmsBundle\Entity\StatRepository
     */
    public function getStatRepository()
    {
        return $this->em->getRepository('MauticSmsBundle:Stat');
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionBase()
    {
        return 'sms:smses';
    }

    /**
     * Save an array of entities.
     *
     * @param  $entities
     * @param  $unlock
     *
     * @return array
     */
    public function saveEntities($entities, $unlock = true)
    {
        //iterate over the results so the events are dispatched on each delete
        $batchSize = 20;
        foreach ($entities as $k => $entity) {
            $isNew = ($entity->getId()) ? false : true;

            //set some defaults
            $this->setTimestamps($entity, $isNew, $unlock);

            if ($dispatchEvent = $entity instanceof Sms) {
                $event = $this->dispatchEvent('pre_save', $entity, $isNew);
            }

            $this->getRepository()->saveEntity($entity, false);

            if ($dispatchEvent) {
                $this->dispatchEvent('post_save', $entity, $isNew, $event);
            }

            if ((($k + 1) % $batchSize) === 0) {
                $this->em->flush();
            }
        }
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     *
     * @param       $entity
     * @param       $formFactory
     * @param null  $action
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws MethodNotAllowedHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof Sms) {
            throw new MethodNotAllowedHttpException(['Sms']);
        }
        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('sms', $entity, $options);
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return null|Sms
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            $entity = new Sms();
        } else {
            $entity = parent::getEntity($id);
        }

        return $entity;
    }

    /**
     * @param Sms   $sms
     * @param       $sendTo
     * @param array $options
     *
     * @return array
     */
    public function sendSms(Sms $sms, $sendTo, $options = [])
    {
        $channel = (isset($options['channel'])) ? $options['channel'] : null;

        if ($sendTo instanceof Lead) {
            $sendTo = [$sendTo];
        } elseif (!is_array($sendTo)) {
            $sendTo = [$sendTo];
        }

        $sentCount      = 0;
        $failedCount    = 0;
        $results        = [];
        $contacts       = [];
        $fetchContacts  = [];
        $sendResultText = 'Success';
        foreach ($sendTo as $lead) {
            if (!$lead instanceof Lead) {
                $fetchContacts[] = $lead;
            } else {
                $contacts[$lead->getId()] = $lead;
            }
        }

        if ($fetchContacts) {
            $foundContacts = $this->leadModel->getEntities(
                [
                    'ids' => $fetchContacts,
                ]
            );

            foreach ($foundContacts as $contact) {
                $contacts[$contact->getId()] = $contact;
            }
        }
        $contactIds = array_keys($contacts);

        /** @var DoNotContactRepository $dncRepo */
        $dncRepo = $this->em->getRepository('MauticLeadBundle:DoNotContact');
        $dnc     = $dncRepo->getChannelList('sms', $contactIds);

        if (!empty($dnc)) {
            foreach ($dnc as $removeMeId => $removeMeReason) {
                $results[$removeMeId] = [
                    'sent'   => false,
                    'status' => 'mautic.sms.campaign.failed.not_contactable',
                ];

                unset($contacts[$removeMeId], $contactIds[$removeMeId]);
            }
        }

        if (!empty($contacts)) {
            $messageQueue    = (isset($options['resend_message_queue'])) ? $options['resend_message_queue'] : null;
            $campaignEventId = (is_array($channel) && 'campaign.event' === $channel[0] && !empty($channel[1])) ? $channel[1] : null;

            $queued = $this->messageQueueModel->processFrequencyRules(
                $contacts,
                'sms',
                $sms->getId(),
                $campaignEventId,
                3,
                MessageQueue::PRIORITY_NORMAL,
                $messageQueue,
                'sms_message_stats'
            );

            if ($queued) {
                foreach ($queued as $queue) {
                    $results[$queue] = [
                        'sent'   => false,
                        'status' => 'mautic.sms.timeline.status.scheduled',
                    ];

                    unset($contacts[$queue]);
                }
            }

            $stats = [];
            // @todo we should allow batch sending based on transport, MessageBird does support 20 SMS at once
            // the transport chain is already prepared for it
            if (count($contacts)) {
                /** @var Lead $lead */
                foreach ($contacts as $lead) {
                    $leadId = $lead->getId();

                    $leadPhoneNumber = $lead->getMobile();
                    if (empty($leadPhoneNumber)) {
                        $leadPhoneNumber = $lead->getPhone();
                    }

                    if (empty($leadPhoneNumber)) {
                        $results[$leadId] = [
                            'sent'   => false,
                            'status' => 'mautic.sms.campaign.failed.missing_number',
                        ];
                    }

                    $smsEvent = new SmsSendEvent($sms->getMessage(), $lead);
                    $smsEvent->setSmsId($sms->getId());
                    $this->dispatcher->dispatch(SmsEvents::SMS_ON_SEND, $smsEvent);

                    $tokenEvent = $this->dispatcher->dispatch(
                        SmsEvents::TOKEN_REPLACEMENT,
                        new TokenReplacementEvent(
                            $smsEvent->getContent(),
                            $lead,
                            ['channel' => ['sms', $sms->getId()]]
                        )
                    );

                    $metadata = $this->transport->sendSms($leadPhoneNumber, $tokenEvent->getContent());
                    $status   = $this->translator->trans('mautic.sms.timeline.status.delivered');
                    if ($metadata != 'true') {
                        $status               = $metadata;
                        $sendResultText       = 'Failed';
                        $send                 = false;
                        ++$failedCount;
                    } else {
                        $send               = true;
                        $stats[]            = $this->createStatEntry($sms, $lead, $channel, false);
                        ++$sentCount;
                    }

                    $sendResult = [
                        'sent'       => $send,
                        'type'       => 'mautic.sms.smss',
                        'status'     => $status,
                        'id'         => $sms->getId(),
                        'name'       => $sms->getName(),
                        'content'    => $tokenEvent->getContent(),
                        'errorResult'=> $sendResultText,
                        'exception'  => $metadata,
                    ];

                    $results[$leadId] = $sendResult;

                    unset($smsEvent, $tokenEvent, $sendResult, $metadata);
                }
            }
        }

        if ($sentCount) {
            $this->getRepository()->upCount($sms->getId(), 'sent', $sentCount);
            $this->getStatRepository()->saveEntities($stats);
            $this->em->clear(Stat::class);
        }
        if ($failedCount) {
            $this->getRepository()->upCount($sms->getId(), 'failed', $failedCount);
        }

        return $results;
    }

    public function sendSmstoUser(Sms $sms, $users, $lead, $options = [])
    {
        $channel        = (isset($options['channel'])) ? $options['channel'] : null;
        $sendResultText = 'Success';
        $results        = [];
        foreach ($users as $user) {
            if (!is_array($user)) {
                $id   = $user;
                $user = ['id' => $id];
            } else {
                $id = $user['id'];
            }

            $userEntity      = $this->userModel->getEntity($id);
            $userPhoneNumber = $userEntity->getMobile();

            if (empty($userPhoneNumber)) {
                $results[$lead->getId()] = [
                    'sent'        => false,
                    'status'      => 'mautic.sms.campaign.failed.missing_number',
                    'errorResult' => 'Failed',
                ];
            }
            $smsEvent = new SmsSendEvent($sms->getMessage(), $lead);
            $smsEvent->setSmsId($sms->getId());
            $this->dispatcher->dispatch(SmsEvents::SMS_ON_SEND, $smsEvent);

            $tokenEvent = $this->dispatcher->dispatch(
                SmsEvents::TOKEN_REPLACEMENT,
                new TokenReplacementEvent(
                    $smsEvent->getContent(),
                    $lead,
                    ['channel' => ['sms', $sms->getId()]]
                )
            );

            $metadata = $this->transport->sendSms($userPhoneNumber, $tokenEvent->getContent());

            $sendResult = [
                'sent'        => false,
                'type'        => 'mautic.sms.smss',
                'status'      => 'mautic.sms.timeline.status.delivered',
                'id'          => $sms->getId(),
                'name'        => $sms->getName(),
                'content'     => $tokenEvent->getContent(),
                'errorResult' => $sendResultText,
                'exception'   => $metadata,
            ];

            if (true !== $metadata) {
                $sendResult['status'] = $metadata;
                $sendResultText       = 'Failed';
            } else {
                $sendResult['sent'] = true;
                $stats[]            = $this->createStatEntry($sms, $lead, $channel, false);
            }

            $results[$lead->getId()] = $sendResult;

            unset($smsEvent, $tokenEvent, $sendResult, $metadata);
        }

        return $results;
    }

    public function sendSampleSmstoUser(Sms $sms, $users)
    {
        $errors        = [];
        foreach ($users as $user) {
            $userPhoneNumber = $user['mobile'];

            $metadata = $this->transport->sendSms($userPhoneNumber, $sms->getMessage());

            if (true !== $metadata) {
                $errors[]       = $metadata;
            } else {
                $errors = [];
            }

            unset($smsEvent, $tokenEvent, $sendResult, $metadata);
        }

        return $errors;
    }

    /**
     * @param Sms  $sms
     * @param Lead $lead
     * @param null $source
     * @param bool $persist
     *
     * @return Stat
     */
    public function createStatEntry(Sms $sms, Lead $lead, $source = null, $persist = true)
    {
        $stat = new Stat();
        $stat->setDateSent(new \DateTime());
        $stat->setLead($lead);
        $stat->setSms($sms);
        if (is_array($source)) {
            $stat->setSourceId($source[1]);
            $source = $source[0];
        }
        $stat->setSource($source);

        if ($persist) {
            $this->getStatRepository()->saveEntity($stat);
        }

        return $stat;
    }

    /**
     * {@inheritdoc}
     *
     * @param $action
     * @param $event
     * @param $entity
     * @param $isNew
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof Sms) {
            throw new MethodNotAllowedHttpException(['Sms']);
        }

        switch ($action) {
            case 'pre_save':
                $name = SmsEvents::SMS_PRE_SAVE;
                break;
            case 'post_save':
                $name = SmsEvents::SMS_POST_SAVE;
                break;
            case 'pre_delete':
                $name = SmsEvents::SMS_PRE_DELETE;
                break;
            case 'post_delete':
                $name = SmsEvents::SMS_POST_DELETE;
                break;
            default:
                return;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new SmsEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        } else {
            return;
        }
    }

    /**
     * Joins the page table and limits created_by to currently logged in user.
     *
     * @param QueryBuilder $q
     */
    public function limitQueryToCreator(QueryBuilder &$q)
    {
        $q->join('t', MAUTIC_TABLE_PREFIX.'sms_messages', 's', 's.id = t.sms_id')
            ->andWhere('s.created_by = :userId')
            ->setParameter('userId', $this->userHelper->getUser()->getId());
    }

    /**
     * Get line chart data of hits.
     *
     * @param char      $unit          {@link php.net/manual/en/function.date.php#refsect1-function.date-parameters}
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param string    $dateFormat
     * @param array     $filter
     * @param bool      $canViewOthers
     *
     * @return array
     */
    public function getHitsLineChartData($unit, \DateTime $dateFrom, \DateTime $dateTo, $dateFormat = null, $filter = [], $canViewOthers = true)
    {
        $flag = null;

        if (isset($filter['flag'])) {
            $flag = $filter['flag'];
            unset($filter['flag']);
        }

        $chart = new LineChart($unit, $dateFrom, $dateTo, $dateFormat);
        $query = new ChartQuery($this->em->getConnection(), $dateFrom, $dateTo);

        if (!$flag || $flag === 'total_and_unique') {
            $q = $query->prepareTimeDataQuery('sms_message_stats', 'date_sent', $filter);

            if (!$canViewOthers) {
                $this->limitQueryToCreator($q);
            }

            $data = $query->loadAndBuildTimeData($q);
            $chart->setDataset($this->translator->trans('mautic.sms.show.total.sent'), $data);
        }

        return $chart->render();
    }

    /**
     * @param $idHash
     *
     * @return Stat
     */
    public function getSmsStatus($idHash)
    {
        return $this->getStatRepository()->getSmsStatus($idHash);
    }

    /**
     * Search for an sms stat by sms and lead IDs.
     *
     * @param $smsId
     * @param $leadId
     *
     * @return array
     */
    public function getSmsStatByLeadId($smsId, $leadId)
    {
        return $this->getStatRepository()->findBy(
            [
                'sms'  => (int) $smsId,
                'lead' => (int) $leadId,
            ],
            ['dateSent' => 'DESC']
        );
    }

    /**
     * Get an array of tracked links.
     *
     * @param $smsId
     *
     * @return array
     */
    public function getSmsClickStats($smsId)
    {
        return $this->pageTrackableModel->getTrackableList('sms', $smsId);
    }

    /**
     * @param        $type
     * @param string $filter
     * @param int    $limit
     * @param int    $start
     * @param array  $options
     *
     * @return array
     */
    public function getLookupResults($type, $filter = '', $limit = 10, $start = 0, $options = [])
    {
        $results = [];
        switch ($type) {
            case 'sms':
                $entities = $this->getRepository()->getSmsList(
                    $filter,
                    $limit,
                    $start,
                    $this->security->isGranted($this->getPermissionBase().':viewother'),
                    isset($options['template']) ? $options['template'] : false
                );

                foreach ($entities as $entity) {
                    $results[$entity['language']][$entity['id']] = $entity['name'];
                }

                //sort by language
                ksort($results);

                break;
        }

        return $results;
    }

    public function getSentCount($id)
    {
        $sentcount=$this->getRepository()->getSmsSentCount($viewOthers = $this->factory->get('mautic.security')->isGranted('sms:smses:viewother'), $id);

        return $sentcount;
    }

    public function getFailedCount($id)
    {
        $failedcount=$this->getRepository()->getSmsFailedCount($id);

        return $failedcount;
    }

    public function getClickCount($id)
    {
        $clickcount=$this->getRepository()->getSmsClickCounts($viewOthers = $this->factory->get('mautic.security')->isGranted('sms:smses:viewother'), $id);

        return $clickcount;
    }

    public function getSMSBlocks()
    {
        $smsOpened =  [$this->translator->trans('le.form.display.color.blocks.blue'), 'mdi mdi-email-outline', $this->translator->trans('le.email.sent.last30days.sent'),
            $this->getRepository()->getLast30DaysSmsSentCount($viewOthers = $this->factory->get('mautic.security')->isGranted('sms:smses:viewother')),
        ];
        $smsClicked = [$this->translator->trans('le.form.display.color.blocks.green'), 'mdi mdi-email-open-outline', $this->translator->trans('le.email.sent.last30days.clicks'),
            $this->getRepository()->getLast30DaysSMSClickCounts($viewOthers = $this->factory->get('mautic.security')->isGranted('sms:smses:viewother')),
        ];

        $allBlockDetails[] = $smsOpened;
        $allBlockDetails[] = $smsClicked;

        return $allBlockDetails;
    }
}
