<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\EventListener;

use Mautic\CoreBundle\EventListener\ChannelTrait;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Model\AuditLogModel;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Event as Events;
use Mautic\LeadBundle\Helper\LeadChangeEventDispatcher;
use Mautic\LeadBundle\LeadEvents;
use Mautic\LeadBundle\Model\ChannelTimelineInterface;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Model\ListOptInModel;

/**
 * Class LeadSubscriber.
 */
class LeadSubscriber extends CommonSubscriber
{
    use ChannelTrait;

    /**
     * @var AuditLogModel
     */
    private $auditLogModel;

    /**
     * @var IpLookupHelper
     */
    private $ipLookupHelper;

    /**
     * @var LeadChangeEventDispatcher
     */
    private $leadEventDispatcher;

    /**
     * LeadSubscriber constructor.
     *
     * @param IpLookupHelper $ipLookupHelper
     * @param AuditLogModel  $auditLogModel
     */
    public function __construct(IpLookupHelper $ipLookupHelper, AuditLogModel $auditLogModel, LeadChangeEventDispatcher $eventDispatcher)
    {
        $this->ipLookupHelper      = $ipLookupHelper;
        $this->auditLogModel       = $auditLogModel;
        $this->leadEventDispatcher = $eventDispatcher;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::LEAD_POST_SAVE               => ['onLeadPostSave', 0],
            LeadEvents::LEAD_POST_DELETE             => ['onLeadDelete', 0],
            LeadEvents::LEAD_PRE_MERGE               => ['preLeadMerge', 0],
            LeadEvents::LEAD_POST_MERGE              => ['onLeadMerge', 0],
            LeadEvents::FIELD_POST_SAVE              => ['onFieldPostSave', 0],
            LeadEvents::FIELD_POST_DELETE            => ['onFieldDelete', 0],
            LeadEvents::NOTE_POST_SAVE               => ['onNotePostSave', 0],
            LeadEvents::NOTE_POST_DELETE             => ['onNoteDelete', 0],
            LeadEvents::TIMELINE_ON_GENERATE         => ['onTimelineGenerate', 0],
            LeadEvents::LEAD_LIST_SEND_EMAIL         => ['LeadListSendEmail', 0],
            LeadEvents::LEAD_LIST_SENDTHANKYOU_EMAIL => ['LeadListSendThankyouEmail', 0],
            LeadEvents::LEAD_LIST_SENDGOODBYE_EMAIL  => ['LeadListSendGoodbyeEmail', 0],
        ];
    }

    /**
     * Add a lead entry to the audit log.
     *
     * @param Events\LeadEvent $event
     */
    public function onLeadPostSave(Events\LeadEvent $event)
    {
        //Because there is an event within an event, there is a risk that something will trigger a loop which
        //needs to be prevented
        static $preventLoop = [];

        $lead = $event->getLead();

        if ($details = $event->getChanges()) {
            // Unset dateLastActive and dateModified to prevent un-necessary audit log entries
            unset($details['dateLastActive'], $details['dateModified']);
            if (empty($details)) {
                return;
            }

            $check = base64_encode($lead->getId().md5(json_encode($details)));
            if (!in_array($check, $preventLoop)) {
                $preventLoop[] = $check;

                // Change entry
                $log = [
                    'bundle'    => 'lead',
                    'object'    => 'lead',
                    'objectId'  => $lead->getId(),
                    'action'    => ($event->isNew()) ? 'create' : 'update',
                    'details'   => $details,
                    'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
                ];
                $this->auditLogModel->writeToLog($log);

                // Date identified entry
                if (isset($changes['dateIdentified'])) {
                    //log the day lead was identified
                    $log = [
                        'bundle'    => 'lead',
                        'object'    => 'lead',
                        'objectId'  => $lead->getId(),
                        'action'    => 'identified',
                        'details'   => [],
                        'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
                    ];
                    $this->auditLogModel->writeToLog($log);
                }

                // IP added entry
                if (isset($details['ipAddresses']) && !empty($details['ipAddresses'][1])) {
                    $log = [
                        'bundle'    => 'lead',
                        'object'    => 'lead',
                        'objectId'  => $lead->getId(),
                        'action'    => 'ipadded',
                        'details'   => $details['ipAddresses'],
                        'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
                    ];
                    $this->auditLogModel->writeToLog($log);
                }

                $this->leadEventDispatcher->dispatchEvents($event, $details);
            }
        }
    }

    /**
     * Add a lead delete entry to the audit log.
     *
     * @param Events\LeadEvent $event
     */
    public function onLeadDelete(Events\LeadEvent $event)
    {
        $lead = $event->getLead();
        $log  = [
            'bundle'    => 'lead',
            'object'    => 'lead',
            'objectId'  => $lead->deletedId,
            'action'    => 'delete',
            'details'   => ['name' => $lead->getPrimaryIdentifier()],
            'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
        ];
        $this->auditLogModel->writeToLog($log);
    }

    /**
     * Add a field entry to the audit log.
     *
     * @param Events\LeadFieldEvent $event
     */
    public function onFieldPostSave(Events\LeadFieldEvent $event)
    {
        $field = $event->getField();
        if ($details = $event->getChanges()) {
            $log = [
                'bundle'    => 'lead',
                'object'    => 'field',
                'objectId'  => $field->getId(),
                'action'    => ($event->isNew()) ? 'create' : 'update',
                'details'   => $details,
                'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
            ];
            $this->auditLogModel->writeToLog($log);
        }
    }

    /**
     * Add a field delete entry to the audit log.
     *
     * @param Events\LeadFieldEvent $event
     */
    public function onFieldDelete(Events\LeadFieldEvent $event)
    {
        $field = $event->getField();
        $log   = [
            'bundle'    => 'lead',
            'object'    => 'field',
            'objectId'  => $field->deletedId,
            'action'    => 'delete',
            'details'   => ['name', $field->getLabel()],
            'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
        ];
        $this->auditLogModel->writeToLog($log);
    }

    /**
     * Add a note entry to the audit log.
     *
     * @param Events\LeadNoteEvent $event
     */
    public function onNotePostSave(Events\LeadNoteEvent $event)
    {
        $note = $event->getNote();
        if ($details = $event->getChanges()) {
            $log = [
                'bundle'    => 'lead',
                'object'    => 'note',
                'objectId'  => $note->getId(),
                'action'    => ($event->isNew()) ? 'create' : 'update',
                'details'   => $details,
                'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
            ];
            $this->auditLogModel->writeToLog($log);
        }
    }

    /**
     * Add a note delete entry to the audit log.
     *
     * @param Events\LeadNoteEvent $event
     */
    public function onNoteDelete(Events\LeadNoteEvent $event)
    {
        $note = $event->getNote();
        $log  = [
            'bundle'    => 'lead',
            'object'    => 'note',
            'objectId'  => $note->deletedId,
            'action'    => 'delete',
            'details'   => ['text', $note->getText()],
            'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
        ];
        $this->auditLogModel->writeToLog($log);
    }

    /**
     * @param Events\LeadMergeEvent $event
     */
    public function preLeadMerge(Events\LeadMergeEvent $event)
    {
        $this->em->getRepository('MauticLeadBundle:LeadEventLog')->updateLead(
            $event->getLoser()->getId(),
            $event->getVictor()->getId()
        );
    }

    /**
     * @param Events\LeadMergeEvent $event
     */
    public function onLeadMerge(Events\LeadMergeEvent $event)
    {
        $this->em->getRepository('MauticLeadBundle:PointsChangeLog')->updateLead(
            $event->getLoser()->getId(),
            $event->getVictor()->getId()
        );

        $this->em->getRepository('MauticLeadBundle:ListLead')->updateLead(
            $event->getLoser()->getId(),
            $event->getVictor()->getId()
        );

        $this->em->getRepository('MauticLeadBundle:LeadNote')->updateLead(
            $event->getLoser()->getId(),
            $event->getVictor()->getId()
        );

        $loserValue = $event->getLoserDetails();

        $log = [
            'bundle'    => 'lead',
            'object'    => 'lead',
            'objectId'  => $event->getVictor()->getId(),
            'action'    => 'merge',
            'details'   => ['fields'=> ['firstname' => [$loserValue[0],
                                              $event->getVictor()->getFirstname(), ],
                             'lastname' => [$loserValue[1],
                                              $event->getVictor()->getLastname(), ],
                             'email' => [$loserValue[2],
                                              $event->getVictor()->getEmail(), ],
                             'mobile' => [$loserValue[3],
                                              $event->getVictor()->getMobile(), ],
                             ],
                           ],
            'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
        ];

        $this->auditLogModel->writeToLog($log);
    }

    /**
     * Compile events for the lead timeline.
     *
     * @param Events\LeadTimelineEvent $event
     */
    public function onTimelineGenerate(Events\LeadTimelineEvent $event)
    {
        $eventTypes = [
            //'lead.utmtagsadded' => 'le.lead.event.utmtagsadded',
            'lead.donotcontact' => 'le.lead.event.donotcontact',
            'lead.imported'     => 'le.lead.event.imported',
        ];

        // Following events takes the event from the lead itself, so not applicable for API
        // where we are getting events for all leads.
        if ($event->isForTimeline()) {
            $eventTypes['lead.create']     = 'le.lead.event.create';
            $eventTypes['lead.update']     = 'le.lead.event.update';
            //$eventTypes['lead.identified'] = 'le.lead.event.identified';
            //$eventTypes['lead.ipadded']    = 'le.lead.event.ipadded';
        }

        $filters = $event->getEventFilters();

        foreach ($eventTypes as $type => $label) {
            $name = $this->translator->trans($label);
            $event->addEventType($type, $name);

            if (!$event->isApplicable($type) || ($type != 'lead.utmtagsadded' && !empty($filters['search']))) {
                continue;
            }

            switch ($type) {
                case 'lead.create':
                    $this->addTimelineDateCreatedEntry($event, $type, $name);
                    break;

                case 'lead.update':
                    $this->addTimelineDateUpdatedEntry($event, $type, $name);
                    break;

                case 'lead.identified':
                    $this->addTimelineDateIdentifiedEntry($event, $type, $name);
                    break;

                case 'lead.ipadded':
                    $this->addTimelineIpAddressEntries($event, $type, $name);
                    break;

                case 'lead.utmtagsadded':
                    $this->addTimelineUtmEntries($event, $type, $name);
                    break;

                case 'lead.donotcontact':
                    $this->addTimelineDoNotContactEntries($event, $type, $name);
                    break;

                case 'lead.imported':
                    $this->addTimelineImportedEntries($event, $type, $name);
                    break;
            }
        }
    }

    /**
     * @param Events\LeadTimelineEvent $event
     * @param                          $eventTypeKey
     * @param                          $eventTypeName
     */
    protected function addTimelineIpAddressEntries(Events\LeadTimelineEvent $event, $eventTypeKey, $eventTypeName)
    {
        $lead = $event->getLead();
        $rows = $this->auditLogModel->getRepository()->getLeadIpLogs($lead, $event->getQueryOptions());

        if (!$event->isEngagementCount()) {
            // Add to counter
            $event->addToCounter($eventTypeKey, $rows);

            // Add the entries to the event array
            $ipAddresses = ($lead instanceof Lead) ? $lead->getIpAddresses()->toArray() : null;

            foreach ($rows['results'] as $row) {
                if ($ipAddresses !== null && !isset($ipAddresses[$row['ip_address']])) {
                    continue;
                }

                $event->addEvent(
                    [
                        'event'         => $eventTypeKey,
                        'eventId'       => $eventTypeKey.$row['id'],
                        'eventLabel'    => $row['ip_address'],
                        'eventType'     => $eventTypeName,
                        'eventPriority' => -1, // Usually an IP is added after another event
                        'timestamp'     => $row['date_added'],
                        'extra'         => [
                            'ipDetails' => $ipAddresses[$row['ip_address']],
                        ],
                        'contentTemplate' => 'MauticLeadBundle:SubscribedEvents\Timeline:ipadded.html.php',
                        'contactId'       => $row['lead_id'],
                    ]
                );
            }
        } else {
            // Purposively not including this in engagements graph as it's info only
        }
    }

    /**
     * @param Events\LeadTimelineEvent $event
     * @param                          $eventTypeKey
     * @param                          $eventTypeName
     */
    protected function addTimelineDateCreatedEntry(Events\LeadTimelineEvent $event, $eventTypeKey, $eventTypeName)
    {
        // Do nothing if the lead is not set
        if (!$event->getLead() instanceof Lead) {
            return;
        }

        $dateAdded = $event->getLead()->getDateAdded();
        if (!$event->isEngagementCount()) {
            $event->addToCounter($eventTypeKey, 1);

            $start = $event->getEventLimit()['start'];
            if (empty($start)) {
                $event->addEvent(
                    [
                        'event'         => $eventTypeKey,
                        'eventId'       => $eventTypeKey.$event->getLead()->getId(),
                        'icon'          => ' fa fa-user le-mg-5',
                        'eventType'     => $eventTypeName,
                        'eventPriority' => -5, // Usually something happened to create the lead so this should display afterward
                        'timestamp'     => $dateAdded,
                    ]
                );
            }
        } else {
            // Purposively not including this in engagements graph as it's info only
        }
    }

    /**
     * @param Events\LeadTimelineEvent $event
     * @param                          $eventTypeKey
     * @param                          $eventTypeName
     */
    protected function addTimelineDateUpdatedEntry(Events\LeadTimelineEvent $event, $eventTypeKey, $eventTypeName)
    {
        // Do nothing if the lead is not set
        if (!$event->getLead() instanceof Lead) {
            return;
        }

        $lead     = $event->getLead();
        $filters  = ['action' => 'update'];
        $logcount = $this->auditLogModel->getRepository()->getAuditLogsCount($lead, $filters);
        $rows     = $this->auditLogModel->getRepository()->getAuditLogs($lead, $filters);
        if (!$event->isEngagementCount()) {
            $event->addToCounter($eventTypeKey, $logcount);

            $start = $event->getEventLimit()['start'];
            if (empty($start)) {
                foreach ($rows as $row) {
                    $fieldlabel       = '';
                    $eventlabel       = '';
                    $tagaddlabel      = '';
                    $tagremovelabel   = '';

                    if (isset($row['details']['fields']) || isset($row['details']['owner'])) {
                        if (isset($row['details']['fields'])) {
                            foreach ($row['details']['fields'] as $fieldkey => $fields) {
                                $fieldlabel .= $fieldkey.' - '.$fields['1'].', ';
                            }
                        }

                        if (isset($row['details']['owner'])) {
                            $fieldlabel .= 'owner - '.$row['details']['owner'][1].', ';
                        }

                        $fieldlabel = substr($fieldlabel, 0, -2).'.';
                        $eventlabel = $this->translator->trans('le.lead.event.timeline.leadupdate').$fieldlabel;
                    }

                    if (isset($row['details']['tags'])) {
                        if (isset($row['details']['tags']['added'])) {
                            foreach ($row['details']['tags']['added'] as $addkey => $addfields) {
                                $tagaddlabel .= $addfields.', ';
                            }
                            $tagaddlabel = substr($tagaddlabel, 0, -2).'';
                            $eventlabel  = $eventlabel.$this->translator->trans('le.lead.event.timeline.leadupdate.tagadded').'"'.$tagaddlabel.'".';
                        }

                        if (isset($row['details']['tags']['removed'])) {
                            foreach ($row['details']['tags']['removed'] as $key => $fields) {
                                $tagremovelabel .= $fields.', ';
                            }
                            $tagremovelabel = substr($tagremovelabel, 0, -2).'';
                            $eventlabel     = $eventlabel.$this->translator->trans('le.lead.event.timeline.leadupdate.tagremoved').'"'.$tagremovelabel.'".';
                        }
                    }

                    if (!isset($row['details']['fields']) && !isset($row['details']['tags']) && !isset($row['details']['owner'])) {
                        $eventlabel = $eventTypeName;
                        continue;
                    }

                    $event->addEvent(
                        [
                            'event'         => $eventTypeKey,
                            'eventId'       => $eventTypeKey.$event->getLead()->getId(),
                            'eventLabel'    => $eventlabel,
                            'icon'          => 'fa fa-fw fa-history',
                            'eventType'     => $eventTypeName,
                            'eventPriority' => -1, // Usually something happened to create the lead so this should display afterward
                            'timestamp'     => $row['dateAdded'],
                        ]
                    );
                }
            } else {
                // Purposively not including this in engagements graph as it's info only
            }
        }
    }

    /**
     * @param Events\LeadTimelineEvent $event
     * @param                          $eventTypeKey
     * @param                          $eventTypeName
     */
    protected function addTimelineDateIdentifiedEntry(Events\LeadTimelineEvent $event, $eventTypeKey, $eventTypeName)
    {
        // Do nothing if the lead is not set
        if (!$event->getLead() instanceof Lead) {
            return;
        }

        if ($dateIdentified = $event->getLead()->getDateIdentified()) {
            if (!$event->isEngagementCount()) {
                $event->addToCounter($eventTypeKey, 1);

                $start = $event->getEventLimit()['start'];
                if (empty($start)) {
                    $event->addEvent(
                        [
                            'event'         => $eventTypeKey,
                            'eventId'       => $eventTypeKey.$event->getLead()->getId(),
                            'icon'          => 'fa-user',
                            'eventType'     => $eventTypeName,
                            'eventPriority' => -4, // A lead is created prior to being identified
                            'timestamp'     => $dateIdentified,
                            'featured'      => true,
                        ]
                    );
                }
            } else {
                // Purposively not including this in engagements graph as it's info only
            }
        }
    }

    /**
     * @param Events\LeadTimelineEvent $event
     * @param                          $eventTypeKey
     * @param                          $eventTypeName
     */
    protected function addTimelineUtmEntries(Events\LeadTimelineEvent $event, $eventTypeKey, $eventTypeName)
    {
        $utmRepo = $this->em->getRepository('MauticLeadBundle:UtmTag');
        $utmTags = $utmRepo->getUtmTagsByLead($event->getLead(), $event->getQueryOptions());
        // Add to counter
        $event->addToCounter($eventTypeKey, $utmTags);

        if (!$event->isEngagementCount()) {
            // Add the logs to the event array
            foreach ($utmTags['results'] as $utmTag) {
                $icon = 'fa-tag';
                if (isset($utmTag['utm_medium'])) {
                    switch (strtolower($utmTag['utm_medium'])) {
                            case 'social':
                            case 'socialmedia':
                                $icon = 'fa-'.((isset($utmTag['utm_source'])) ? strtolower($utmTag['utm_source']) : 'share-alt');
                                break;
                            case 'email':
                            case 'newsletter':
                                $icon = 'fa-envelope-o';
                                break;
                            case 'banner':
                            case 'ad':
                                $icon = 'fa-bullseye';
                                break;
                            case 'cpc':
                                $icon = 'fa-money';
                                break;
                            case 'location':
                                $icon = 'fa-map-marker';
                                break;
                            case 'device':
                                $icon = 'fa-'.((isset($utmTag['utm_source'])) ? strtolower($utmTag['utm_source']) : 'tablet');
                                break;
                        }
                }
                $event->addEvent(
                        [
                            'event'      => $eventTypeKey,
                            'eventType'  => $eventTypeName,
                            'eventId'    => $eventTypeKey.$utmTag['id'],
                            'eventLabel' => !empty($utmTag) ? $utmTag['utm_campaign'] : 'UTM Tags',
                            'timestamp'  => $utmTag['date_added'],
                            'icon'       => $icon,
                            'extra'      => [
                                'utmtags' => $utmTag,
                            ],
                            'contentTemplate' => 'MauticLeadBundle:SubscribedEvents\Timeline:utmadded.html.php',
                            'contactId'       => $utmTag['lead_id'],
                        ]
                    );
            }
        } else {
            // Purposively not including this in engagements graph as the engagement is counted by the page hit
        }
    }

    /**
     * @param Events\LeadTimelineEvent $event
     * @param                          $eventTypeKey
     * @param                          $eventTypeName
     */
    protected function addTimelineDoNotContactEntries(Events\LeadTimelineEvent $event, $eventTypeKey, $eventTypeName)
    {
        /** @var \Mautic\LeadBundle\Entity\DoNotContactRepository $dncRepo */
        $dncRepo = $this->em->getRepository('MauticLeadBundle:DoNotContact');

        /** @var \Mautic\LeadBundle\Entity\DoNotContact[] $entries */
        $rows = $dncRepo->getTimelineStats($event->getLeadId(), $event->getQueryOptions());

        // Add to counter
        $event->addToCounter($eventTypeKey, $rows);

        if (!$event->isEngagementCount()) {
            foreach ($rows['results'] as $row) {
                $type = '';
                switch ($row['reason']) {
                    case DoNotContact::UNSUBSCRIBED:
                        $row['reason'] = $this->translator->trans('le.lead.event.donotcontact_unsubscribed');
                        $type          ='unsubscribed';
                        break;
                    case DoNotContact::BOUNCED:
                        $row['reason'] = $this->translator->trans('le.lead.event.donotcontact_bounce');
                        $type          ='bounced';
                        break;
                    case DoNotContact::SPAM:
                        $row['reason'] = $this->translator->trans('le.lead.event.donotcontact_spam');
                        $type          ='spam';
                        break;
                    case DoNotContact::MANUAL:
                        $row['reason'] = $this->translator->trans('le.lead.event.donotcontact_manual');
                        $type          ='manual';
                        break;
                    case DoNotContact::IS_CONTACTABLE:
                        $row['reason'] = $this->translator->trans('le.lead.event.donotcontact_iscontactable');
                        $type          ='contactable';
                        break;
                }

                $template = 'MauticLeadBundle:SubscribedEvents\Timeline:donotcontact.html.php';
                $icon     = 'fa-ban';

                if (!empty($row['channel'])) {
                    if ($channelModel = $this->getChannelModel($row['channel'])) {
                        if ($channelModel instanceof ChannelTimelineInterface) {
                            if ($overrideTemplate = $channelModel->getChannelTimelineTemplate($eventTypeKey, $row)) {
                                $template = $overrideTemplate;
                            }

                            if ($overrideEventTypeName = $channelModel->getChannelTimelineLabel($eventTypeKey, $row)) {
                                $eventTypeName = $overrideEventTypeName;
                            }

                            if ($overrideIcon = $channelModel->getChannelTimelineIcon($eventTypeKey, $row)) {
                                $icon = $overrideIcon;
                            }
                        }

                        /* @deprecated - BC support to be removed in 3.0 */
                        // Allow a custom template if applicable
                        if (method_exists($channelModel, 'getDoNotContactLeadTimelineTemplate')) {
                            $template = $channelModel->getDoNotContactLeadTimelineTemplate($row);
                        }
                        if (method_exists($channelModel, 'getDoNotContactLeadTimelineLabel')) {
                            $eventTypeName = $channelModel->getDoNotContactLeadTimelineLabel($row);
                        }
                        if (method_exists($channelModel, 'getDoNotContactLeadTimelineIcon')) {
                            $icon = $channelModel->getDoNotContactLeadTimelineIcon($row);
                        }
                        /* end deprecation */

                        if (!empty($row['channel_id'])) {
                            if ($item = $this->getChannelEntityName($row['channel'], $row['channel_id'], true)) {
                                $row['itemName']  = $item['name'];
                                $row['itemRoute'] = $item['url'];
                            }
                            $email   = $this->factory->getModel('email')->getEntity($row['channel_id']);
                            $subject = '';
                            if ($email != null) {
                                $subject = $email->getSubject();
                            }
                        }
                    }
                }
                if ($type == 'contactable') {
                    continue;
                } elseif ($type != 'manual') {
                    if (isset($row['itemRoute'])) {
                        $label = $this->translator->trans('le.lead.event.timeline.donotcontact.'.$type.'.eventlabel', ['%subject%' => $subject, '%emailname%' => $row['itemName'], '%href%' => $row['itemRoute']]);
                    } else {
                        $label=ucfirst($row['channel']);
                    }
                } elseif ($type != '') {
                    $row['itemName'] = true;
                    $label           = $this->translator->trans('le.lead.event.timeline.donotcontact.'.$type.'.eventlabel', ['%reason%' => $row['comments']]);
                }

                $contactId = $row['lead_id'];
                unset($row['lead_id']);
                $event->addEvent(
                    [
                        'event'      => $eventTypeKey,
                        'eventId'    => $eventTypeKey.$row['id'],
                        'eventLabel' => (isset($row['itemName'])) ?
                            [
                                'label' => $label,
                                /**'href'  => $row['itemRoute'],*/
                            ] : ucfirst($row['channel']),
                        'eventType' => $eventTypeName,
                        'timestamp' => $row['date_added'],
                        'extra'     => [
                            'dnc' => $row,
                        ],
                        'contentTemplate' => $template,
                        'icon'            => $icon,
                        'contactId'       => $contactId,
                    ]
                );
            }
        }
    }

    /**
     * @param Events\LeadTimelineEvent $event
     * @param                          $eventTypeKey
     * @param                          $eventTypeName
     */
    protected function addTimelineImportedEntries(Events\LeadTimelineEvent $event, $eventTypeKey, $eventTypeName)
    {
        $eventLogRepo = $this->em->getRepository('MauticLeadBundle:LeadEventLog');
        $imports      = $eventLogRepo->getEventsByLead('lead', 'import', $event->getLead(), $event->getQueryOptions());
        // Add to counter
        $event->addToCounter($eventTypeKey, $imports);

        if (!$event->isEngagementCount()) {
            // Add the logs to the event array
            foreach ($imports['results'] as $import) {
                if (is_string($import['properties'])) {
                    $import['properties'] = json_decode($import['properties'], true);
                }
                $eventLabel = 'N/A';
                if (!empty($import['properties']['file'])) {
                    $eventLabel = $import['properties']['file'];
                } elseif ($import['object_id']) {
                    $eventLabel = $import['object_id'];
                }
                if (!empty($import['object_id'])) {
                    $eventLabel = $this->translator->trans('le.lead.import.contact.action.'.$import['action'], ['%name%' => $eventLabel]);
                    /* '%href%' => $this->router->generate(
                    'le_contact_import_action',
                    ['objectAction' => 'view', 'objectId' => $import['object_id']]
                    )*/
                }
                $event->addEvent(
                        [
                            'event'      => $eventTypeKey,
                            'eventId'    => $eventTypeKey.$import['id'],
                            'eventType'  => $eventTypeName,
                            'eventLabel' => !empty($import['object_id']) ? [
                                'label' => $eventLabel,
                                /**'href'  => $this->router->generate(
                                    'le_contact_import_action',
                                    ['objectAction' => 'view', 'objectId' => $import['object_id']]
                                ),*/
                            ] : $eventLabel,
                            'timestamp'       => $import['date_added'],
                            'icon'            => 'fa-download',
                            'extra'           => $import,
                            'contentTemplate' => 'MauticLeadBundle:SubscribedEvents\Timeline:import.html.php',
                            'contactId'       => $import['lead_id'],
                        ]
                    );
            }
        } else {
            // Purposively not including this
        }
    }

    /**
     * Send a Email for the Lead.
     *
     * @param Events\LeadListOptInEvent $event
     */
    public function LeadListSendEmail(Events\LeadListOptInEvent $event)
    {
        $list          = $event->getList();
        $isdoubleOptin = $list->getListtype();
        if ($isdoubleOptin) {
            /** @var ListOptInModel $listoptinmodel */
            $listoptinmodel = $this->factory->getModel('lead.listoptin');
            /** @var LeadModel $leadmodel */
            $leadmodel = $this->factory->getModel('lead');
            if (!$event->isBulkOperation()) {
                $lead          = $event->getLead();
                $lead->setCreatedSource(3); //Created Source FORM SUBMIT
                $lead->setStatus(6); // Not Confirmed
                $leadmodel->saveEntity($lead);
                $listLead = $listoptinmodel->getListLeadRepository()->getListEntityByid($lead->getId(), $list->getId());
                $listoptinmodel->scheduleListOptInEmail($list, $lead, $listLead);
            } else {
                /*$bulkLeads=$event->getBulkLeads();
                foreach ($bulkLeads as $lead) {
                    $this->scheduleListOptInEmail($list, $lead, $listoptinmodel);
                }*/
            }
            unset($email);
        }
    }

    /**
     * Send a Email for the Lead.
     *
     * @param Events\LeadListOptInEvent $event
     */
    public function LeadListSendGoodbyeEmail(Events\LeadListOptInEvent $event)
    {
        $list          = $event->getList();
        $lead          = $event->getLeadFields();
        $isdoubleOptin = $list->getListtype() == 'single' ? false : true;
        $emailId       = $list->getGoodbyeemail();

        if ($list->isGoodbye()) {
            /** @var EmailModel $emailModel */
            $emailModel = $this->factory->getModel('email');
            if (!empty($emailId)) {
                $email = $emailModel->getEntity($emailId);
                if ($email !== null && $email->getIsPublished()) {
                    $emailModel->sendEmail($email, $lead);
                }
            }
        }
    }

    /**
     * Send a Email for the Lead.
     *
     * @param Events\LeadListOptInEvent $event
     */
    public function LeadListSendThankyouEmail(Events\LeadListOptInEvent $event)
    {
        $list          = $event->getList();
        $lead          = $event->getLeadFields();
        $listid        = $event->getListId();
        $isdoubleOptin = $list->getListtype() == 'single' ? false : true;
        $emailId       = $list->getThankyouemail();

        /** @var ListOptInModel $listoptinmodel */
        $listoptinmodel = $this->factory->getModel('lead.listoptin');
        $listLead       = $listoptinmodel->getListLeadRepository()->getListEntityByid($lead['id'], $list);
        if ($list->getThankyou()) {
            /** @var EmailModel $emailModel */
            $emailModel = $this->factory->getModel('email');
            if (!empty($emailId)) {
                $email      = $emailModel->getEntity($emailId);
                if ($email !== null && $email->getIsPublished()) {
                    $customHtml = $listoptinmodel->replaceTokens($email->getCustomHtml(), $lead, $listLead, $list);
                    $email->setCustomHtml($customHtml);
                    $emailModel->sendEmail($email, $lead);
                }
            }
        }
    }
}
