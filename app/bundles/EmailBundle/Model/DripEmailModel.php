<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Model;

use Mautic\ChannelBundle\Model\MessageQueueModel;
use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Helper\LicenseInfoHelper;
use Mautic\CoreBundle\Helper\ProgressBarHelper;
use Mautic\CoreBundle\Helper\ThemeHelper;
use Mautic\CoreBundle\Model\BuilderModelTrait;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\CoreBundle\Model\TranslationModelTrait;
use Mautic\CoreBundle\Model\VariantModelTrait;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Entity\DripEmail;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Entity\Lead as DripLead;
use Mautic\EmailBundle\Entity\LeadEventLog as DripLeadEvent;
use Mautic\EmailBundle\Event\DripEmailEvent;
use Mautic\EmailBundle\Helper\MailHelper;
use Mautic\EmailBundle\MonitoredEmail\Mailbox;
use Mautic\LeadBundle\Model\CompanyModel;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PageBundle\Model\TrackableModel;
use Mautic\UserBundle\Model\UserModel;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class DripEmailModel
 * {@inheritdoc}
 */
class DripEmailModel extends FormModel
{
    use VariantModelTrait;
    use TranslationModelTrait;
    use BuilderModelTrait;

    /**
     * @var IpLookupHelper
     */
    protected $ipLookupHelper;

    /**
     * @var ThemeHelper
     */
    protected $themeHelper;

    /**
     * @var Mailbox
     */
    protected $mailboxHelper;

    /**
     * @var MailHelper
     */
    public $mailHelper;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var CompanyModel
     */
    protected $companyModel;

    /**
     * @var TrackableModel
     */
    protected $pageTrackableModel;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var MessageQueueModel
     */
    protected $messageQueueModel;

    /**
     * @var bool
     */
    protected $updatingTranslationChildren = false;

    /**
     * @var array
     */
    protected $emailSettings = [];

    /**
     * @var Send
     */
    protected $sendModel;

    /**
     * @var LicenseInfoHelper
     */
    protected $licenseInfoHelper;

    /**
     * @var EmailModel
     */
    protected $emailModel;

    /**
     * EmailModel constructor.
     *
     * @param IpLookupHelper     $ipLookupHelper
     * @param ThemeHelper        $themeHelper
     * @param Mailbox            $mailboxHelper
     * @param MailHelper         $mailHelper
     * @param LeadModel          $leadModel
     * @param CompanyModel       $companyModel
     * @param TrackableModel     $pageTrackableModel
     * @param UserModel          $userModel
     * @param MessageQueueModel  $messageQueueModel
     * @param SendEmailToContact $sendModel
     * @param LicenseInfoHelper  $licenseInfoHelper
     * @param EmailModel         $emailModel
     */
    public function __construct(
        IpLookupHelper $ipLookupHelper,
        ThemeHelper $themeHelper,
        Mailbox $mailboxHelper,
        MailHelper $mailHelper,
        LeadModel $leadModel,
        CompanyModel $companyModel,
        TrackableModel $pageTrackableModel,
        UserModel $userModel,
        MessageQueueModel $messageQueueModel,
        SendEmailToContact $sendModel,
        LicenseInfoHelper  $licenseInfoHelper,
        EmailModel  $emailModel
    ) {
        $this->ipLookupHelper     = $ipLookupHelper;
        $this->themeHelper        = $themeHelper;
        $this->mailboxHelper      = $mailboxHelper;
        $this->mailHelper         = $mailHelper;
        $this->leadModel          = $leadModel;
        $this->companyModel       = $companyModel;
        $this->pageTrackableModel = $pageTrackableModel;
        $this->userModel          = $userModel;
        $this->messageQueueModel  = $messageQueueModel;
        $this->sendModel          = $sendModel;
        $this->licenseInfoHelper  = $licenseInfoHelper;
        $this->emailModel         = $emailModel;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Mautic\EmailBundle\Entity\DripEmailRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticEmailBundle:DripEmail');
    }

    /**
     * {@inheritdoc}
     *
     * @return \Mautic\EmailBundle\Entity\EmailRepository
     */
    public function getEmailRepository()
    {
        return $this->em->getRepository('MauticEmailBundle:Email');
    }

    /**
     * @return \Mautic\EmailBundle\Entity\StatRepository
     */
    public function getStatRepository()
    {
        return $this->em->getRepository('MauticEmailBundle:Stat');
    }

    /**
     * @return \Mautic\EmailBundle\Entity\CopyRepository
     */
    public function getCopyRepository()
    {
        return $this->em->getRepository('MauticEmailBundle:Copy');
    }

    /**
     * @return \Mautic\EmailBundle\Entity\StatDeviceRepository
     */
    public function getStatDeviceRepository()
    {
        return $this->em->getRepository('MauticEmailBundle:StatDevice');
    }

    /**
     * @return \Mautic\EmailBundle\Entity\AwsConfig
     */
    public function getAwsConfigRepository()
    {
        return $this->em->getRepository('MauticEmailBundle:AwsConfig');
    }

    /**
     * @return \Mautic\EmailBundle\Entity\AwsVerifiedEmails
     */
    public function getAwsVerifiedEmailsRepository()
    {
        return $this->em->getRepository('MauticEmailBundle:AwsVerifiedEmails');
    }

    /**
     * @return \Mautic\EmailBundle\Entity\LeadRepository
     */
    public function getCampaignLeadRepository()
    {
        return $this->em->getRepository('MauticEmailBundle:Lead');
    }

    /**
     * @return \Mautic\EmailBundle\Entity\LeadEventLogRepository
     */
    public function getCampaignLeadEventRepository()
    {
        return $this->em->getRepository('MauticEmailBundle:LeadEventLog');
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionBase()
    {
        return 'email:emails';
    }

    /**
     * {@inheritdoc}
     *
     * @param Email $entity
     * @param       $unlock
     *
     * @return mixed
     */
    public function saveEntity($entity, $unlock = true)
    {
        $isNew = ($entity->getId()) ? false : true;
        if ($isNew) {
            $entity->setIsPublished(false);
        }
        parent::saveEntity($entity, $unlock);

        $this->dispatchEvent('post_save', $entity, $isNew);
    }

    /**
     * @param Email $entity
     */
    public function deleteEntity($entity)
    {
        parent::deleteEntity($entity);

        $this->dispatchEvent('post_delete', $entity, false);
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
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof DripEmail) {
            throw new MethodNotAllowedHttpException(['DripEmail']);
        }
        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('dripemailform', $entity, $options);
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return null|DripEmail
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            $entity = new DripEmail();
        } else {
            $entity = parent::getEntity($id);
            if ($entity !== null) {
                $entity->setSessionId($entity->getId());
            }
        }

        return $entity;
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
        if (!$entity instanceof DripEmail) {
            throw new MethodNotAllowedHttpException(['DripEmail']);
        }

        switch ($action) {
            case 'pre_save':
                $name = EmailEvents::EMAIL_PRE_SAVE;
                break;
            case 'post_save':
                $name = EmailEvents::DRIPEMAIL_POST_SAVE;
                break;
            case 'pre_delete':
                $name = EmailEvents::EMAIL_PRE_DELETE;
                break;
            case 'post_delete':
                $name = EmailEvents::DRIPEMAIL_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            $event = new DripEmailEvent($entity, $isNew);
            $event->setEntityManager($this->em);

            $this->dispatcher->dispatch($name, $event);

            return $event;
        } else {
            return null;
        }
    }

    /**
     * Get a list of emails in a date range.
     *
     * @param int       $limit
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param array     $filters
     * @param array     $options
     *
     * @return array
     */
    public function getEmailList($limit = 10, \DateTime $dateFrom = null, \DateTime $dateTo = null, $filters = [], $options = [])
    {
        $q = $this->em->getConnection()->createQueryBuilder();
        $q->select('t.id, t.name, t.date_added, t.date_modified')
            ->from(MAUTIC_TABLE_PREFIX.'emails', 't')
            ->setMaxResults($limit);

        if (empty($options['canViewOthers']) || $options['canViewOthers'] == '') {
            $q->andWhere('t.created_by = :userId')
                ->setParameter('userId', $this->userHelper->getUser()->getId());
        }

        $chartQuery = new ChartQuery($this->em->getConnection(), $dateFrom, $dateTo);
        $chartQuery->applyFilters($q, $filters);
        $chartQuery->applyDateFilters($q, 'date_added');

        $results = $q->execute()->fetchAll();

        return $results;
    }

    public function addLead($campaign, $lead)
    {
        if ($this->checkLeadLinked($lead, $campaign)) {
            $dripCampaign = new DripLead();
            $dripCampaign->setCampaign($campaign);
            $dripCampaign->setLead($lead);
            $dripCampaign->setDateAdded(new \DateTime());
            $dripCampaign->setManuallyAdded(true);

            $this->saveCampaignLead($dripCampaign);
        }
    }

    public function checkLeadLinked($lead, $dripemail)
    {
        $leadLog  = $this->factory->get('mautic.email.repository.lead');
        $items    = $leadLog->checkisLeadLinked($lead, $dripemail);
        if (empty($items)) {
            return true;
        } else {
            return false;
        }
    }

    public function saveCampaignLead(DripLead $campaignLead)
    {
        try {
            $this->getCampaignLeadRepository()->saveEntity($campaignLead);

            return true;
        } catch (\Exception $exception) {
            $this->logger->log('error', $exception->getMessage());

            return false;
        }
    }

    public function saveCampaignLeadEvent(DripLeadEvent $campaignLead)
    {
        try {
            $leadEventLog  = $this->factory->get('mautic.email.repository.leadEventLog');
            $leadEventLog->saveEntity($campaignLead);

            return true;
        } catch (\Exception $exception) {
            $this->logger->log('error', $exception->getMessage());

            return false;
        }
    }

    public function checkLeadCompleted($lead, $dripemail, $email)
    {
        $leadEventLog  = $this->factory->get('mautic.email.repository.leadEventLog');
        $items         = $leadEventLog->checkisLeadCompleted($lead, $dripemail, $email);
        if (empty($items)) {
            return true;
        } else {
            return false;
        }
    }

    public function scheduleEmail($entities, $dripemail, $lead)
    {
        $isFirstmailToday      = false;
        $timezone              = $this->coreParametersHelper->getParameter('default_timezone');
        date_default_timezone_set('UTC');
        $previousDate     = date('Y-m-d H:i:s');
        $isLastEmail      = false;
        $emailCount       = 0;
        $dateHelper       = $this->factory->get('mautic.helper.template.date');
        foreach ($entities as $entity) {
            $emailCount = $emailCount + 1;
            if (!$entity->getIsPublished()) { //!$this->checkLeadCompleted($lead, $dripemail, $entity) ||
                continue;
            }
            if ($entity->getDripEmailOrder() == 1) {
                $dayscount        = 0;
                $configdays       = $dripemail->getDaysEmailSend();
                $dripScheduleTime = $dripemail->getScheduleDate();
                if ($dripScheduleTime != '') {
                    $date             = date('Y-m-d').' '.$dripScheduleTime;
                    $newTime          = $dateHelper->toTime($date, $timezone);
                    $dripScheduleTime = explode(' ', $newTime)[0];
                }
                if ($dripScheduleTime == '' || strtotime($dripScheduleTime) < strtotime(date('H:i'))) {
                    $dripScheduleTime = date('H:i');
                }
                if (!empty($configdays)) {
                    for ($i = 0; $i < 7; ++$i) {
                        $currentDay = date('D', strtotime('+'.$i.' day'));
                        if (!in_array($currentDay, $configdays)) {
                            continue;
                        } else {
                            $isFirstmailToday = true;
                            $dayscount        = $i;
                            break;
                        }
                    }
                } else {
                    $isFirstmailToday = true;
                }
                $scheduleTime = date('Y-m-d H:i:s', strtotime('+'.$entity->getScheduleTime().' + '.$dayscount.' days', strtotime($dripScheduleTime)));
            } else {
                $scheduleTime = date('Y-m-d H:i:s', strtotime($previousDate.'+'.$entity->getScheduleTime()));
            }
            if (!$isFirstmailToday) {
                continue;
            }
            if ($emailCount == count($entities)) {
                $isLastEmail = true;
            }
            $previousDate = $scheduleTime;
            //dump($isFirstmailToday);
            //dump($scheduleTime);
            $dripevent = new DripLeadEvent();
            $dripevent->setLead($lead);
            $dripevent->setCampaign($dripemail);
            $dripevent->setEmail($entity);
            $dripevent->setTriggerDate($scheduleTime);
            $dripevent->setDateTriggered(date('Y-m-d H:i:s'));
            $dripevent->setIsScheduled(true);
            $dripevent->setRotation($isLastEmail);
            $this->saveCampaignLeadEvent($dripevent);
        }
    }

    public function sendDripEmailtoLead($email, $lead)
    {
        $options   = [
            'source'         => [],
            'email_attempts' => 3,
            'email_priority' => 2,
            'email_type'     => 'transactional',
            'return_errors'  => true,
            'dnc_as_error'   => true,
            'source'         => ['email', $email->getId()],
            'allowResends'   => false,
            'customHeaders'  => [],
        ];

        //getLead
        $leadModel       = $this->factory->get('mautic.lead.model.lead');
        $leadCredentials = $leadModel->getRepository()->getLead($lead->getId());
        $emailSent       = $this->emailModel->sendEmail($email, $leadCredentials, $options);

        if (is_array($emailSent)) {
            $errors = implode('<br />', $emailSent);

            // Add to the metadata of the failed event
            $emailSent = [
                'result' => false,
                'errors' => $errors,
            ];
        } elseif (true !== $emailSent) {
            $emailSent = [
                'result' => false,
                'errors' => $emailSent,
            ];
        } else {
            $emailSent = [
                'result' => true,
                'errors' => '',
            ];
        }

        return $emailSent;
    }

    public function getDripEmailBlocks()
    {
        $sentCount =  [$this->translator->trans('le.form.display.color.blocks.blue'), 'mdi mdi-email-outline', $this->translator->trans('le.email.sent.last30days.sent'),
            $this->getRepository()->getLast30DaysDripSentCounts($viewOthers = $this->factory->get('mautic.security')->isGranted('dripemail:emails:viewother')),
        ];
        $openCount = [$this->translator->trans('le.form.display.color.blocks.green'), 'mdi mdi-email-open-outline', $this->translator->trans('le.email.sent.last30days.opens'),
            $this->getRepository()->getLast30DaysDripOpensCounts($viewOthers = $this->factory->get('mautic.security')->isGranted('dripemail:emails:viewother')),
        ];
        $clickCount = [$this->translator->trans('le.form.display.color.blocks.orange'), 'mdi mdi-email-open-outline', $this->translator->trans('le.email.sent.last30days.clicks'),
            $this->getRepository()->getLast30DaysDripClickCounts($viewOthers = $this->factory->get('mautic.security')->isGranted('dripemail:emails:viewother')),
        ];
        $unsubacribeCount = [$this->translator->trans('le.form.display.color.blocks.red'), 'mdi mdi-email-variant', $this->translator->trans('le.email.sent.drip.unsubscribe'),
            $this->getRepository()->getDripUnsubscribeCounts($viewOthers = $this->factory->get('mautic.security')->isGranted('dripemail:emails:viewother')),
        ];

        $allBlockDetails[] = $sentCount;
        $allBlockDetails[] = $openCount;
        $allBlockDetails[] = $clickCount;
        $allBlockDetails[] = $unsubacribeCount;

        return $allBlockDetails;
    }

    public function scheduleOneOffEmail($leads, $dripemail = null, $email)
    {
        $previousDate     = date('Y-m-d H:i:s');
        foreach ($leads as $lead) {
            $leadEntity = $this->leadModel->getEntity($lead['id']);
            //file_put_contents("/var/www/log.txt",json_encode($leadEntity)."\n",FILE_APPEND);
            $dripevent = new DripLeadEvent();
            $dripevent->setLead($leadEntity);
            $dripevent->setCampaign($dripemail);
            $dripevent->setEmail($email);
            $dripevent->setTriggerDate($previousDate);
            $dripevent->setDateTriggered(date('Y-m-d H:i:s'));
            $dripevent->setIsScheduled(true);
            $this->saveCampaignLeadEvent($dripevent);
        }
    }

    /**
     * Batch sleep according to settings.
     */
    protected function batchSleep()
    {
        $leadSleepTime = $this->coreParametersHelper->getParameter('batch_lead_sleep_time', false);
        if ($leadSleepTime === false) {
            $leadSleepTime = $this->coreParametersHelper->getParameter('batch_sleep_time', 1);
        }

        if (empty($leadSleepTime)) {
            return;
        }

        if ($leadSleepTime < 1) {
            usleep($leadSleepTime * 1000000);
        } else {
            sleep($leadSleepTime);
        }
    }

    public function rebuildLeadRecipients(DripEmail $entity, $limit = 1000, $maxLeads = false, OutputInterface $output = null)
    {
        $id       = $entity->getId();
        $drip     = ['id' => $id, 'filters' => $entity->getRecipients()];

        // Get a count of leads to add
        $newLeadsCount = $this->getLeadsByDrip(
            $entity,
            true
        );

        // Number of total leads to process
        $leadCount = (int) $newLeadsCount;

        if ($output) {
            $output->writeln($this->translator->trans('le.drip.email.lead.rebuild.to_be_added', ['%leads%' => $leadCount, '%batch%' => $limit]));
        }

        // Handle by batches
        $start = $lastRoundPercentage = $leadsProcessed = 0;

        // Try to save some memory
        gc_enable();
        if ($leadCount) {
            $maxCount = ($maxLeads) ? $maxLeads : $leadCount;

            if ($output) {
                $progress = ProgressBarHelper::init($output, $maxCount);
                $progress->start();
            }

            // Add leads
            while ($start < $leadCount) {
                // Keep CPU down for large lists; sleep per $limit batch
                $this->batchSleep();

                $newLeadList = $this->getLeadsByDrip(
                    $entity,
                    false
                );

                if (empty($newLeadList)) {
                    // Somehow ran out of leads so break out
                    break;
                }
                foreach ($newLeadList as $id => $l) {
                    $lead = $this->leadModel->getEntity($l['id']);
                    if ($this->checkLeadLinked($lead, $entity)) {
                        $this->addLead($entity, $lead);
                        $items = $this->emailModel->getEntities(
                            [
                                'filter' => [
                                    'force' => [
                                        [
                                            'column' => 'e.dripEmail',
                                            'expr'   => 'eq',
                                            'value'  => $entity,
                                        ],
                                    ],
                                ],
                                'orderBy'          => 'e.dripEmailOrder',
                                'orderByDir'       => 'asc',
                                'ignore_paginator' => true,
                            ]
                        );

                        $this->scheduleEmail($items, $entity, $lead);
                        $processedLeads[] = $l;
                        unset($l);

                        ++$leadsProcessed;
                        if ($output && $leadsProcessed < $maxCount) {
                            $progress->setProgress($leadsProcessed);
                        }

                        if ($maxLeads && $leadsProcessed >= $maxLeads) {
                            break;
                        }
                    }
                }
                $start += $limit;

                unset($newLeadList);

                // Free some memory
                gc_collect_cycles();

                if ($maxLeads && $leadsProcessed >= $maxLeads) {
                    if ($output) {
                        $progress->finish();
                        $output->writeln('');
                    }

                    return $leadsProcessed;
                }
            }

            if ($output) {
                $progress->finish();
                $output->writeln('');
            }
        }

        return $leadsProcessed;
    }

    /**
     * @param       $lists
     * @param bool  $idOnly
     * @param array $args
     *
     * @return mixed
     */
    public function getLeadsByDrip($drip, $countOnly = false)
    {
        return $this->getRepository()->getLeadsByDrip($drip, $countOnly);
    }

    public function getCustomEmailStats($drip)
    {
        $emails = $this->emailModel->getEntities(
            [
                'filter' => [
                    'force' => [
                        [
                            'column' => 'e.dripEmail',
                            'expr'   => 'eq',
                            'value'  => $drip,
                        ],
                    ],
                ],
                'orderBy'          => 'e.dripEmailOrder',
                'orderByDir'       => 'asc',
                'ignore_paginator' => true,
            ]
        );
        $sentcount        = 0;
        $uopencount       = 0;
        $topencount       = 0;
        $nopencount       = 0;
        $clickcount       = 0;
        $unsubscribecount = 0;
        $bouncecount      = 0;
        $spamcount        = 0;
        foreach ($emails as $item) {
            $sentcount += $this->emailModel->getRepository()->getTotalSentCounts($item->getId());
            $uopencount += $this->emailModel->getRepository()->getTotalUniqueOpenCounts($item->getId());
            $topencount += $this->emailModel->getRepository()->getTotalOpenCounts($item->getId());
            $nopencount += $this->emailModel->getRepository()->getTotalNotOpenCounts($item->getId());
            $clickcount += $this->emailModel->getRepository()->getEmailClickCounts($item->getId());
            $unsubscribecount += $this->emailModel->getRepository()->getTotalUnsubscribedCounts($item->getId());
            $bouncecount += $this->emailModel->getRepository()->getTotalBounceCounts($item->getId());
            $spamcount += $this->emailModel->getRepository()->getTotalSpamCounts($item->getId());
        }
        $emailStats                = [];
        $emailStats['sent']        = $sentcount;
        $emailStats['uopen']       = $uopencount;
        $emailStats['topen']       = $topencount;
        $emailStats['click']       = $clickcount;
        $emailStats['unsubscribe'] = $unsubscribecount;
        $emailStats['bounce']      = $bouncecount;
        $emailStats['spam']        = $spamcount;
        $emailStats['nopen']       = $nopencount;

        return $emailStats;
    }
}
