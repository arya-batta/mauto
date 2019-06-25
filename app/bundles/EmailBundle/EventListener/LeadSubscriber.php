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

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\EmailBundle\Entity\EmailReplyRepositoryInterface;
use Mautic\LeadBundle\Event\LeadMergeEvent;
use Mautic\LeadBundle\Event\LeadTimelineEvent;
use Mautic\LeadBundle\LeadEvents;

/**
 * Class LeadSubscriber.
 */
class LeadSubscriber extends CommonSubscriber
{
    /** @var EmailReplyRepositoryInterface */
    private $emailReplyRepository;

    /**
     * LeadSubscriber constructor.
     *
     * @param EmailReplyRepositoryInterface $emailReplyRepository
     */
    public function __construct(EmailReplyRepositoryInterface $emailReplyRepository)
    {
        $this->emailReplyRepository = $emailReplyRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::TIMELINE_ON_GENERATE => ['onTimelineGenerate', 0],
            LeadEvents::LEAD_POST_MERGE      => ['onLeadMerge', 0],
        ];
    }

    /**
     * Compile events for the lead timeline.
     *
     * @param LeadTimelineEvent $event
     */
    public function onTimelineGenerate(LeadTimelineEvent $event)
    {
        $this->addEmailEvents($event, 'read');
        $this->addEmailEvents($event, 'sent');
        $this->addEmailEvents($event, 'failed');

        if ($this->security->isAdmin()) {
            $this->addDripCampaignEvents($event, 'campaign.event', $this->translator->trans('le.drip.campaign.completed'));
        }

        $this->addDripCampaignEvents($event, 'campaign.event.scheduled', $this->translator->trans('le.drip.campaign.scheduled'));
        $this->addEmailReplies($event);
    }

    /**
     * @param LeadMergeEvent $event
     */
    public function onLeadMerge(LeadMergeEvent $event)
    {
        $this->em->getRepository('MauticEmailBundle:Stat')->updateLead(
            $event->getLoser()->getId(),
            $event->getVictor()->getId()
        );
    }

    /**
     * @param LeadTimelineEvent $event
     * @param                   $state
     */
    protected function addEmailEvents(LeadTimelineEvent $event, $state)
    {
        // Set available event types
        $eventTypeKey  = 'email.'.$state;
        $eventTypeName = $this->translator->trans('le.email.'.$state);
        $event->addEventType($eventTypeKey, $eventTypeName);
        $event->addSerializerGroup('emailList');

        // Decide if those events are filtered
        if (!$event->isApplicable($eventTypeKey)) {
            return;
        }

        /** @var \Mautic\EmailBundle\Entity\StatRepository $statRepository */
        $statRepository        = $this->em->getRepository('MauticEmailBundle:Stat');
        $queryOptions          = $event->getQueryOptions();
        $queryOptions['state'] = $state;
        $stats                 = $statRepository->getLeadStats($event->getLeadId(), $queryOptions);

        // Add total to counter
        $event->addToCounter($eventTypeKey, $stats);
        /** @var \Mautic\LeadBundle\Entity\DoNotContactRepository $dncRepo */
        $dncRepo = $this->em->getRepository('MauticLeadBundle:DoNotContact');
        if (!$event->isEngagementCount()) {
            // Add the events to the event array
            foreach ($stats['results'] as $stat) {
                if ($stat['emailType'] == 'template') {
                    continue;
                }
                $dripurl  = '';
                $dripname = '';
                if (!empty($stat['email_name'])) {
                    if (!empty($stat['dripEmailId'])) {
                        $dripurl            = $this->router->generate('le_dripemail_campaign_action', ['objectAction' => 'edit', 'objectId' => $stat['dripEmailId']]);
                        $dripentity         = $this->factory->getModel('email.dripemail')->getEntity($stat['dripEmailId']);
                        $dripname           = $dripentity->getName();
                    }
                    if ($stat['tokens'] != null && is_array(unserialize($stat['tokens']))) {
                        $search          = array_keys(unserialize($stat['tokens']));
                        $replace         = unserialize($stat['tokens']);
                        $subjectReplaced = str_ireplace($search, $replace, $stat['subject'], $updated);
                    } else {
                        $subjectReplaced = $stat['subject'];
                    }
                    if ('sent' == $state) {
                        if (!empty($stat['idHash'])) {
                            $href  =$this->router->generate('le_email_webview', ['idHash' => $stat['idHash']]);
                            $label = $this->translator->trans('le.email.timeline.event.'.$stat['emailType'].'.sent.eventlabel', ['%subject%' => $subjectReplaced, '%emailname%' => !empty($stat['dripEmailId']) ? $dripname : $stat['email_name'], '%href%' => $href, '%style%' => 'color: #069;text-decoration: none;', '%dripurl%' => $dripurl]);
                        } else {
                            $label = $this->translator->trans('le.email.timeline.event.'.$stat['emailType'].'.sent.eventlabel', ['%subject%' => $subjectReplaced, '%emailname%' => !empty($stat['dripEmailId']) ? $dripname : $stat['email_name'], '%href%' => '', '%style%' => '', '%dripurl%' => $dripurl]);
                        }
                    } elseif ('read' == $state) {
                        $open_date        =$this->factory->get('mautic.helper.template.date')->toFull($stat['lastOpened'], 'UTC');
                        $totalreadcount   = $this->factory->getModel('email')->getRepository()->getTotalOpenCounts($stat['email_id'], $stat['lead_id']);
                        $stat['dateRead'] = $stat['lastOpened'];
                        if (!empty($stat['idHash'])) {
                            $href  =$this->router->generate('le_email_webview', ['idHash' => $stat['idHash']]);
                            $label = $this->translator->trans('le.email.timeline.event.'.$stat['emailType'].'.read.eventlabel', ['%subject%' => $subjectReplaced, '%emailname%' => !empty($stat['dripEmailId']) ? $dripname : $stat['email_name'], '%readcount%' => $totalreadcount, '%dateread%' => $open_date, '%href%' => $href, '%style%' => 'color: #069;text-decoration: none;', '%dripurl%' => $dripurl]);
                        } else {
                            $label = $this->translator->trans('le.email.timeline.event.'.$stat['emailType'].'.read.eventlabel', ['%subject%' => $subjectReplaced, '%emailname%' => !empty($stat['dripEmailId']) ? $dripname : $stat['email_name'], '%readcount%' => $totalreadcount, '%dateread%' => $open_date, '%href%' => '', '%style%' => '', '%dripurl%' => $dripurl]);
                        }
                    } elseif ('failed' == $state) {
                        $href = '';
                        if (!empty($stat['idHash'])) {
                            $href = $this->router->generate('le_email_webview', ['idHash' => $stat['idHash']]);
                        }
                        /** @var \Mautic\LeadBundle\Entity\DoNotContact $entries */
                        $entries          = $dncRepo->getTimelineStatsChannel($stat['lead_id'], $stat['email_id']);
                        $label            = $this->translator->trans('le.email.timeline.event.'.$stat['emailType'].'.failed.eventlabel', ['%subject%' => $subjectReplaced, '%emailname%' => !empty($stat['dripEmailId']) ? $dripname : $stat['email_name'], '%href%' => $href, '%dripurl%' => $dripurl]);
                        $label            = $label.' <br><b>Reason</b>: '.$entries[$stat['email_id']];
                    }

                    //$label = $stat['email_name'];
                   /* if (!empty($stat['dripEmailId'])) {
                        $dripCampaignName = $statRepository->getDripName($stat['dripEmailId']);
                        $label            = $stat['email_name'].'/'.$dripCampaignName;
                    }*/
                } elseif (!empty($stat['storedSubject'])) {
                    $label = $this->translator->trans('le.email.timeline.event.custom_email').': '.$stat['storedSubject'];
                } else {
                    //$label = $this->translator->trans('le.email.timeline.event.custom_email');
                }

                if (!empty($stat['idHash'])) {
                    $eventName = [
                        'label'      => $label,
                        /**'href'       => $this->router->generate('le_email_webview', ['idHash' => $stat['idHash']]),*/
                        'isExternal' => true,
                    ];
                } else {
                    $eventName = $label;
                }
                if ('failed' == $state or 'sent' == $state) { //this is to get the correct column for date dateSent
                    $dateSent = 'sent';
                } else {
                    $dateSent = 'read';
                }

                $contactId = $stat['lead_id'];
                unset($stat['lead_id']);

                $event->addEvent(
                    [
                        'event'      => $eventTypeKey,
                        'eventId'    => $eventTypeKey.$stat['id'],
                        'eventLabel' => $eventName,
                        'eventType'  => $eventTypeName,
                        'timestamp'  => $stat['date'.ucfirst($dateSent)],
                        'extra'      => [
                            'stat' => $stat,
                            'type' => $state,
                        ],
                        'contentTemplate' => 'MauticEmailBundle:SubscribedEvents\Timeline:index.html.php',
                        'icon'            => ($state == 'read') ? 'mdi mdi-email-open-outline fs-24' : 'mdi mdi-email-outline fs-24',
                        'contactId'       => $contactId,
                    ]
                );
            }
        }
    }

    /**
     * @param LeadTimelineEvent $event
     */
    protected function addEmailReplies(LeadTimelineEvent $event)
    {
        return;
        $eventTypeKey  = 'email.replied';
        $eventTypeName = $this->translator->trans('le.email.replied');
        $event->addSerializerGroup('emailList');
        $event->addEventType($eventTypeKey, $eventTypeName);
        $options          = $event->getQueryOptions();
        $replies          = $this->emailReplyRepository->getByLeadIdForTimeline($event->getLeadId(), $options);
        if (!$event->isEngagementCount()) {
            foreach ($replies['results'] as $reply) {
                $label = $this->translator->trans('le.email.timeline.event.email_reply');
                if (!empty($reply['email_name'])) {
                    $label .= ': '.$reply['email_name'];
                } elseif (!empty($reply['storedSubject'])) {
                    $label .= ': '.$reply['storedSubject'];
                }

                $contactId = $reply['lead_id'];
                unset($reply['lead_id']);

                $event->addEvent(
                    [
                        'event'      => $eventTypeKey,
                        'eventId'    => $eventTypeKey.$reply['id'],
                        'eventLabel' => $label,
                        'eventType'  => $eventTypeName,
                        'timestamp'  => $reply['date_replied'],
                        'icon'       => 'fa-envelope',
                        'contactId'  => $contactId,
                    ]
                );
            }
        }
    }

    /**
     * @param LeadTimelineEvent $event
     * @param                   $state
     */
    protected function addDripCampaignEvents(LeadTimelineEvent $event, $eventTypeKey, $eventTypeName)
    {
        // Set available event types
        $event->addEventType($eventTypeKey, $eventTypeName);
        $event->addSerializerGroup('dripcampaignList');

        // Decide if those events are filtered
        if (!$event->isApplicable($eventTypeKey)) {
            return;
        }

        /** @var \Mautic\EmailBundle\Entity\LeadEventLogRepository $logRepository */
        $logRepository                 = $this->em->getRepository('MauticEmailBundle:LeadEventLog');
        $queryOptions                  = $event->getQueryOptions();
        $queryOptions['scheduledState']= ('campaign.event' === $eventTypeKey) ? false : true;
        $campaigns                     = $logRepository->getLeadLogs($event->getLeadId(), $queryOptions);
        // Add total to counter
        $event->addToCounter($eventTypeKey, $campaigns);

        if (!$event->isEngagementCount()) {
            // Add the events to the event array
            foreach ($campaigns['results'] as $campaign) {
                if (!empty($campaign['email_subject']) && !empty($campaign['drip_name'])) {
                    $label = $campaign['email_subject'].'/'.$campaign['drip_name'];
                } else {
                    continue;
                }
                $schedule_date=$this->factory->get('mautic.helper.template.date')->toFull($campaign['triggerDate']);
                $eventName    = $label;
                $eventName    = [
                    'label'      => $eventTypeKey == 'campaign.event.scheduled' ? $this->translator->trans('le.email.timeline.event.drip.scheduled.eventlabel', ['%emailname%' => $campaign['drip_name'], '%scheduleddate%' =>$schedule_date, '%href%' =>$this->router->generate('le_dripemail_campaign_action', ['objectAction' => 'edit', 'objectId' => $campaign['dripemail_id']])]) : $label,
                    /**'href'       => $this->router->generate('le_dripemail_campaign_action', ['objectAction' => 'edit', 'objectId' => $campaign['dripemail_id']]),*/
                    'isExternal' => true,
                ];

                $contactId = $campaign['lead_id'];
                unset($campaign['lead_id']);

                $subeventTypeIcon='fa-sign-out';
                if ($queryOptions['scheduledState']) {
                    $subeventTypeIcon='fa-bolt';
                }

                $event->addEvent(
                    [
                        'event'      => $eventTypeKey,
                        'eventId'    => $eventTypeKey.$campaign['log_id'],
                        'eventLabel' => $eventName,
                        'eventType'  => $eventTypeName,
                        'timestamp'  => $campaign['dateTriggered'],
                        'extra'      => [
                            'log' => $campaign,
                        ],
                        'contentTemplate' => 'MauticEmailBundle:SubscribedEvents\DripSearch:index.html.php',
                        'icon'            => $subeventTypeIcon,
                        'contactId'       => $contactId,
                    ]
                );
            }
        }
    }
}
