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
use Mautic\EmailBundle\Event\EmailEvent;
use Mautic\EmailBundle\Helper\MailHelper;
use Mautic\EmailBundle\MonitoredEmail\Mailbox;
use Mautic\LeadBundle\Model\CompanyModel;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PageBundle\Model\TrackableModel;
use Mautic\UserBundle\Model\UserModel;
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
        parent::saveEntity($entity, $unlock);
    }

    /**
     * @param Email $entity
     */
    public function deleteEntity($entity)
    {
        parent::deleteEntity($entity);
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
                $name = EmailEvents::EMAIL_POST_SAVE;
                break;
            case 'pre_delete':
                $name = EmailEvents::EMAIL_PRE_DELETE;
                break;
            case 'post_delete':
                $name = EmailEvents::EMAIL_POST_DELETE;
                break;
            default:
                return null;
        }

        /*if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new EmailEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        } else {*/
        return null;
        //}
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
        $isFirstmailToday = false;
        $previousDate     = date('Y-m-d H:i:s');
        foreach ($entities as $entity) {
            /*if (!$this->checkLeadCompleted($lead, $dripemail, $entity)) {
                continue;
            }*/
            if ($entity->getDripEmailOrder() == 1) {
                $dayscount        = 0;
                $configdays       = $dripemail->getDaysEmailSend();
                $dripScheduleTime = $dripemail->getScheduleDate();
                if ($dripScheduleTime == '') {
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
            $previousDate = $scheduleTime;
            //dump($isFirstmailToday);
            //dump($scheduleTime);
            $dripevent = new DripLeadEvent();
            $dripevent->setLead($lead);
            $dripevent->setCampaign($dripemail);
            $dripevent->setEmail($entity);
            $dripevent->setIsScheduled(true);
            $dripevent->setTriggerDate($scheduleTime);
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
        $sentCount =  [$this->translator->trans('le.form.display.color.blocks.blue'), 'fa fa-envelope-o', $this->translator->trans('le.email.sent.last30days.sent'),
            $this->getRepository()->getLast30DaysDripSentCounts($viewOthers = $this->factory->get('mautic.security')->isGranted('dripemail:emails:viewother')),
        ];
        $openCount = [$this->translator->trans('le.form.display.color.blocks.green'), 'fa fa-envelope-open-o', $this->translator->trans('le.email.sent.last30days.opens'),
            $this->getRepository()->getLast30DaysDripOpensCounts($viewOthers = $this->factory->get('mautic.security')->isGranted('dripemail:emails:viewother')),
        ];
        $clickCount = [$this->translator->trans('le.form.display.color.blocks.orange'), 'fa fa-envelope-open-o', $this->translator->trans('le.email.sent.last30days.clicks'),
            $this->getRepository()->getLast30DaysDripClickCounts($viewOthers = $this->factory->get('mautic.security')->isGranted('dripemail:emails:viewother')),
        ];

        $allBlockDetails[] = $sentCount;
        $allBlockDetails[] = $openCount;
        $allBlockDetails[] = $clickCount;

        return $allBlockDetails;
    }
}
