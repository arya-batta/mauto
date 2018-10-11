<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\EventListener;

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\CampaignBundle\Model\EventModel;
use Mautic\ChannelBundle\Model\MessageQueueModel;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Event\EmailOpenEvent;
use Mautic\EmailBundle\Event\EmailReplyEvent;
use Mautic\EmailBundle\Exception\EmailCouldNotBeSentException;
use Mautic\EmailBundle\Helper\UrlMatcher;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\EmailBundle\Model\SendEmailToUser;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\NotificationBundle\Helper\NotificationHelper;
use Mautic\PageBundle\Entity\Hit;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CampaignSubscriber.
 */
class CampaignSubscriber extends CommonSubscriber
{
    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var EmailModel
     */
    protected $emailModel;

    /**
     * @var EmailModel
     */
    protected $messageQueueModel;

    /**
     * @var EventModel
     */
    protected $campaignEventModel;

    /**
     * @var SendEmailToUser
     */
    private $sendEmailToUser;
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /*
     * @var NotificationHelper
     */
    protected $notificationhelper;

    /**
     * @param LeadModel           $leadModel
     * @param EmailModel          $emailModel
     * @param EventModel          $eventModel
     * @param MessageQueueModel   $messageQueueModel
     * @param SendEmailToUser     $sendEmailToUser
     * @param TranslatorInterface $translator
     */
    public function __construct(
        LeadModel $leadModel,
        EmailModel $emailModel,
        EventModel $eventModel,
        MessageQueueModel $messageQueueModel,
        SendEmailToUser $sendEmailToUser,
        TranslatorInterface  $translator,
        NotificationHelper $notificationhelper
    ) {
        $this->leadModel          = $leadModel;
        $this->emailModel         = $emailModel;
        $this->campaignEventModel = $eventModel;
        $this->messageQueueModel  = $messageQueueModel;
        $this->sendEmailToUser    = $sendEmailToUser;
        $this->translator         = $translator;
        $this->notificationhelper = $notificationhelper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD       => ['onCampaignBuild', 0],
            EmailEvents::EMAIL_ON_OPEN              => ['onEmailOpen', 0],
            EmailEvents::ON_CAMPAIGN_TRIGGER_ACTION => [
                ['onCampaignTriggerActionSendEmailToContact', 0],
                ['onCampaignTriggerActionSendEmailToUser', 1],
            ],
            EmailEvents::ON_CAMPAIGN_TRIGGER_DECISION => ['onCampaignTriggerDecision', 0],
            EmailEvents::EMAIL_ON_REPLY               => ['onEmailReply', 0],
        ];
    }

    /**
     * @param CampaignBuilderEvent $event
     */
    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
        $isAdmin =$this->factory->getUser()->isAdmin();
        $event->addDecision(
            'email.open',
            [
                'label'                  => 'mautic.email.campaign.event.open',
                'description'            => 'mautic.email.campaign.event.open_descr',
                'eventName'              => EmailEvents::ON_CAMPAIGN_TRIGGER_DECISION,
                'connectionRestrictions' => [
                    'source' => [
                        'action' => [
                            'email.send',
                        ],
                    ],
                ],
                'order'                  => 1,
            ]
        );

        $event->addDecision(
            'email.click',
            [
                'label'                  => 'mautic.email.campaign.event.click',
                'description'            => 'mautic.email.campaign.event.click_descr',
                'eventName'              => EmailEvents::ON_CAMPAIGN_TRIGGER_DECISION,
                'formType'               => 'email_click_decision',
                'connectionRestrictions' => [
                    'source' => [
                        'action' => [
                            'email.send',
                        ],
                    ],
                ],
                'order'                   => 2,
            ]
        );

        $event->addAction(
            'email.send',
            [
                'label'           => 'mautic.email.campaign.event.send',
                'description'     => 'mautic.email.campaign.event.send_descr',
                'eventName'       => EmailEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formType'        => 'emailsend_list',
                'formTypeOptions' => ['update_select' => 'campaignevent_properties_email', 'with_email_types' => true],
                'formTheme'       => 'MauticEmailBundle:FormTheme\EmailSendList',
                'channel'         => 'email',
                'channelIdField'  => 'email',
                'order'           => 1,
                'group'           => 'le.campaign.event.group.name.leadsengage',
            ]
        );
        if ($isAdmin) {
            $event->addDecision(
                'email.reply',
                [
                    'label'                  => 'mautic.email.campaign.event.reply',
                    'description'            => 'mautic.email.campaign.event.reply_descr',
                    'eventName'              => EmailEvents::ON_CAMPAIGN_TRIGGER_DECISION,
                    'connectionRestrictions' => [
                        'source' => [
                            'action' => [
                                'email.send',
                            ],
                        ],
                    ],
                    'order'                   => 9,
                ]
            );
        }
        $event->addAction(
            'email.send.to.user',
            [
                'label'           => 'mautic.email.campaign.event.send.to.user',
                'description'     => 'mautic.email.campaign.event.send.to.user_descr',
                'eventName'       => EmailEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formType'        => 'email_to_user',
                'formTypeOptions' => ['update_select' => 'campaignevent_properties_useremail_email'],
                'formTheme'       => 'MauticEmailBundle:FormTheme\EmailSendList',
                'channel'         => 'email',
                'channelIdField'  => 'email',
                'order'           => 11,
                'group'           => 'le.campaign.event.group.name.leadsengage',
            ]
        );

        $event->addSources(
            'openEmail',
            [
                'label'           => 'le.email.campaign.event.email.open',
                'description'     => 'le.email.campaign.event.email.open_descr',
                'sourcetype'      => 'openEmail',
                'formType'        => 'emailsend_list',
                'order'           => '7',
                'group'           => 'le.campaign.source.group.name',
            ]
        );

        $event->addSources(
            'clickEmail',
            [
                'label'           => 'le.email.campaign.event.email.click',
                'description'     => 'le.email.campaign.event.email.click_descr',
                'sourcetype'      => 'clickEmail',
                'formType'        => 'emailsend_list',
                'order'           => '8',
                'group'           => 'le.campaign.source.group.name',
            ]
        );
    }

    /**
     * Trigger campaign event for opening of an email.
     *
     * @param EmailOpenEvent $event
     */
    public function onEmailOpen(EmailOpenEvent $event)
    {
        $email = $event->getEmail();

        if ($email !== null) {
            $this->campaignEventModel->triggerEvent('email.open', $email, 'email', $email->getId());
        }
    }

    /**
     * Trigger campaign event for reply to an email.
     *
     * @param EmailReplyEvent $event
     */
    public function onEmailReply(EmailReplyEvent $event)
    {
        $email = $event->getEmail();
        if ($email !== null) {
            $this->campaignEventModel->triggerEvent('email.reply', $email, 'email', $email->getId());
        }
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerDecision(CampaignExecutionEvent $event)
    {
        /** @var Email $eventDetails */
        $eventDetails = $event->getEventDetails();
        $eventParent  = $event->getEvent()['parent'];
        $eventConfig  = $event->getConfig();

        if ($eventDetails == null) {
            return $event->setResult(false);
        }

        //check to see if the parent event is a "send email" event and that it matches the current email opened or clicked
        if (!empty($eventParent) && $eventParent['type'] === 'email.send') {
            // click decision
            if ($event->checkContext('email.click')) {
                /** @var Hit $hit */
                $hit = $eventDetails;
                if ($eventDetails->getEmail()->getId() == (int) $eventParent['properties']['email']) {
                    if (!empty($eventConfig['urls']['list'])) {
                        $limitToUrls = (array) $eventConfig['urls']['list'];
                        if (UrlMatcher::hasMatch($limitToUrls, $hit->getUrl())) {
                            return $event->setResult(true);
                        }
                    } else {
                        return $event->setResult(true);
                    }
                }

                return $event->setResult(false);
            } elseif ($event->checkContext('email.open')) {
                // open decision
                return $event->setResult($eventDetails->getId() === (int) $eventParent['properties']['email']);
            } elseif ($event->checkContext('email.reply')) {
                // reply decision
                return $event->setResult($eventDetails->getId() === (int) $eventParent['properties']['email']);
            }
        }

        return $event->setResult(false);
    }

    /**
     * Triggers the action which sends email to contact.
     *
     * @param CampaignExecutionEvent $event
     *
     * @return CampaignExecutionEvent|null
     */
    public function onCampaignTriggerActionSendEmailToContact(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('email.send')) {
            return;
        }

        $leadCredentials = $event->getLeadFields();

        if (empty($leadCredentials['email'])) {
            return $event->setFailed('Contact does not have an email');
        }

        $config  = $event->getConfig();
        $emailId = (int) $config['email'];
        $email   = $this->emailModel->getEntity($emailId);
        $status  = $this->emailModel->mailHelper->emailstatus();
        if (!$email || !$email->isPublished()) {
            return $event->setFailed('Email not found or published');
        }
        if (!$status) {
            $this->notificationhelper->sendNotificationonFailure(true, false);
            $configurl=$this->factory->getRouter()->generate('mautic_config_action', ['objectAction' => 'edit']);

            return $event->setFailed($this->translator->trans('mautic.email.config.mailer.status.report', ['%url%'=>$configurl]));
        }
        $emailSent = false;
        $type      = (isset($config['email_type'])) ? $config['email_type'] : 'transactional';
        $options   = [
            'source'         => ['campaign.event', $event->getEvent()['id']],
            'email_attempts' => (isset($config['attempts'])) ? $config['attempts'] : 3,
            'email_priority' => (isset($config['priority'])) ? $config['priority'] : 2,
            'email_type'     => $type,
            'return_errors'  => true,
            'dnc_as_error'   => true,
        ];

        $event->setChannel('email', $emailId);

        // Determine if this email is transactional/marketing
        $stats = [];
        if ('marketing' == $type) {
            // Determine if this lead has received the email before
            $leadIds   = implode(',', [$leadCredentials['id']]);
            $stats     = $this->emailModel->getStatRepository()->checkContactsSentEmail($leadIds, $emailId);
            $emailSent = true; // Assume it was sent to prevent the campaign event from getting rescheduled over and over
        }

        if (empty($stats)) {
            $emailSent = $this->emailModel->sendEmail($email, $leadCredentials, $options);
        }

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
        }

        return $event->setResult($emailSent);
    }

    /**
     * Triggers the action which sends email to user, contact owner or specified email addresses.
     *
     * @param CampaignExecutionEvent $event
     *
     * @return CampaignExecutionEvent|null
     */
    public function onCampaignTriggerActionSendEmailToUser(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('email.send.to.user')) {
            return;
        }

        $config = $event->getConfig();
        $lead   = $event->getLead();
        $status = $this->emailModel->mailHelper->emailstatus();
        if (!$status) {
            $configurl=$this->factory->getRouter()->generate('mautic_config_action', ['objectAction' => 'edit']);
            return $event->setFailed($this->translator->trans('mautic.email.config.mailer.status.report', ['%url%'=>$configurl]));
        }
        try {
            $this->sendEmailToUser->sendEmailToUsers($config, $lead);
            $event->setResult(true);
        } catch (EmailCouldNotBeSentException $e) {
            $event->setFailed($e->getMessage());
        }

        return $event;
    }
}
