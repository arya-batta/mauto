<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\EventListener;

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\CampaignBundle\Model\CampaignModel;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Helper\licenseinfoHelper;
use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\PointsChangeLog;
use Mautic\LeadBundle\Form\Type\ChangeOwnerType;
use Mautic\LeadBundle\LeadEvents;
use Mautic\LeadBundle\Model\FieldModel;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Model\ListModel;

/**
 * Class CampaignSubscriber.
 */
class CampaignSubscriber extends CommonSubscriber
{
    const ACTION_LEAD_CHANGE_OWNER = 'lead.changeowner';

    /**
     * @var IpLookupHelper
     */
    protected $ipLookupHelper;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var FieldModel
     */
    protected $leadFieldModel;

    /**
     * @var ListModel
     */
    protected $listModel;

    /**
     * @var CampaignModel
     */
    protected $campaignModel;
    /**
     * @var LicenseInfoHelper
     */
    protected $licenseInfoHelper;

    /**
     * CampaignSubscriber constructor.
     *
     * @param IpLookupHelper    $ipLookupHelper
     * @param LeadModel         $leadModel
     * @param FieldModel        $leadFieldModel
     * @param LicenseInfoHelper $licenseInfoHelper
     */
    public function __construct(IpLookupHelper $ipLookupHelper, LeadModel $leadModel, FieldModel $leadFieldModel, ListModel $listModel, CampaignModel $campaignModel, LicenseInfoHelper  $licenseInfoHelper)
    {
        $this->ipLookupHelper     = $ipLookupHelper;
        $this->leadModel          = $leadModel;
        $this->leadFieldModel     = $leadFieldModel;
        $this->listModel          = $listModel;
        $this->campaignModel      = $campaignModel;
        $this->licenseInfoHelper  =  $licenseInfoHelper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD      => ['onCampaignBuild', 0],
            LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION => [
                ['onCampaignTriggerActionChangePoints', 0],
                ['onCampaignTriggerActionChangeLists', 1],
                ['onCampaignTriggerActionUpdateLead', 2],
                ['onCampaignTriggerActionUpdateTags', 3],
                ['onCampaignTriggerActionAddToCompany', 4],
                ['onCampaignTriggerActionChangeCompanyScore', 4],
                ['onCampaignTriggerActionDeleteContact', 6],
                ['onCampaignTriggerActionChangeOwner', 7],
                ['onCampaignTriggerActionChangeScore', 8],
                ['onCampaignTriggerActionSetDNC', 9],
                ['onCampaignTriggerActionRemoveDNC', 10],
            ],
            LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION => ['onCampaignTriggerCondition', 0],
        ];
    }

    /**
     * Add event triggers and actions.
     *
     * @param CampaignBuilderEvent $event
     */
    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
        //Add actions
        $action = [
            'label'           => 'le.lead.lead.events.changepoints',
            'description'     => 'le.lead.lead.events.changepoints_descr',
            'formType'        => 'leadpoints_action',
            'eventName'       => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'order'           => 9,
            'group'           => 'le.campaign.event.group.name.leadsengage',
        ];
        $event->addAction('lead.changepoints', $action);

        $action = [
            'label'           => 'le.lead.lead.events.onscorechange',
            'description'     => 'le.lead.lead.events.onscorechange_descr',
            'formType'        => 'leadscore_action',
            'eventName'       => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'order'           => 8,
            'group'           => 'le.campaign.event.group.name.leadsengage',
        ];
        $event->addAction('lead.scorechange', $action);

        $action = [
            'label'           => 'le.lead.lead.events.changelist',
            'description'     => 'le.lead.lead.events.changelist_descr',
            'formType'        => 'leadlist_action',
            'eventName'       => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'order'           => 6,
            'group'           => 'le.campaign.event.group.name.leadsengage',
        ];
        $event->addAction('lead.changelist', $action);

        $action = [
            'label'           => 'le.lead.lead.events.updatelead',
            'description'     => 'le.lead.lead.events.updatelead_descr',
            'formType'        => 'updatelead_action',
            'formTheme'       => 'MauticLeadBundle:FormTheme\ActionUpdateLead',
            'eventName'       => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'order'           => 10,
            'group'           => 'le.campaign.event.group.name.leadsengage',
        ];
        $event->addAction('lead.updatelead', $action);

        $action = [
            'label'           => 'le.lead.lead.events.changetags',
            'description'     => 'le.lead.lead.events.changetags_descr',
            'formType'        => 'modify_lead_tags',
            'eventName'       => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'order'           => 7,
            'group'           => 'le.campaign.event.group.name.leadsengage',
        ];
        $event->addAction('lead.changetags', $action);
        if ($this->security->isGranted('stage:stages:view')) {
            $action = [
                'label'           => 'le.lead.lead.events.addtocompany',
                'description'     => 'le.lead.lead.events.addtocompany_descr',
                'formType'        => 'addtocompany_action',
                'eventName'       => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'order'           => 15,
                'group'           => 'le.campaign.event.group.name.leadsengage',
            ];
            $event->addAction('lead.addtocompany', $action);
        }
        $action = [
            'label'           => 'le.lead.lead.events.changeowner',
            'description'     => 'le.lead.lead.events.changeowner_descr',
            'formType'        => ChangeOwnerType::class,
            'eventName'       => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'order'           => 14,
            'group'           => 'le.campaign.event.group.name.leadsengage',
         ];
        $event->addAction(self::ACTION_LEAD_CHANGE_OWNER, $action);
        if ($this->security->isGranted('stage:stages:view')) {
            $action = [
                'label'       => 'le.lead.lead.events.changecompanyscore',
                'description' => 'le.lead.lead.events.changecompanyscore_descr',
                'formType'    => 'scorecontactscompanies_action',
                'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'order'       => 16,
                'group'       => 'le.campaign.event.group.name.leadsengage',
            ];
            $event->addAction('lead.scorecontactscompanies', $action);
        }
        $trigger = [
            'label'                  => 'le.lead.lead.events.delete',
            'description'            => 'le.lead.lead.events.delete_descr',
            'eventName'              => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'connectionRestrictions' => [
                'target' => [
                    'decision'  => ['none'],
                    'action'    => ['none'],
                    'condition' => ['none'],
                ],
            ],
            'order'                  => 15,
            'group'                  => 'le.campaign.event.group.name.leadsengage',
        ];
        $event->addAction('lead.deletecontact', $trigger);

        $trigger = [
            'label'           => 'le.lead.lead.events.set.donotcontact',
            'description'     => 'le.lead.lead.events.set.donotcontact_descr',
            'eventName'       => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'order'           => 11,
            'group'           => 'le.campaign.event.group.name.leadsengage',
        ];

        $event->addAction('lead.setdonotcontact', $trigger);

        $trigger = [
            'label'           => 'le.lead.lead.events.remove.donotcontact',
            'description'     => 'le.lead.lead.events.remove.donotcontact_descr',
            'eventName'       => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'order'           => 12,
            'group'           => 'le.campaign.event.group.name.leadsengage',
        ];

        $event->addAction('lead.removedonotcontact', $trigger);

        $trigger = [
            'label'       => 'le.lead.lead.events.field_value',
            'description' => 'le.lead.lead.events.field_value_descr',
            'formType'    => 'campaignevent_lead_field_value',
            'formTheme'   => 'MauticLeadBundle:FormTheme\FieldValueCondition',
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
            'order'       => 4,
        ];
        $event->addCondition('lead.field_value', $trigger);

        $trigger = [
            'label'       => 'le.lead.lead.events.device',
            'description' => 'le.lead.lead.events.device_descr',
            'formType'    => 'campaignevent_lead_device',
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
            'order'       => 8,
        ];

        if ($this->security->isAdmin()) {
            $event->addCondition('lead.device', $trigger);
        }

        $trigger = [
            'label'       => 'le.lead.lead.events.tags',
            'description' => 'le.lead.lead.events.tags_descr',
            'formType'    => 'campaignevent_lead_tags',
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
            'order'       => 2,
        ];
        $event->addCondition('lead.tags', $trigger);

        $trigger = [
            'label'       => 'le.lead.lead.events.segments',
            'description' => 'le.lead.lead.events.segments_descr',
            'formType'    => 'campaignevent_lead_segments',
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
            'order'       => 1,
        ];

        $event->addCondition('lead.segments', $trigger);

        $trigger = [
            'label'       => 'le.lead.lead.events.owner',
            'description' => 'le.lead.lead.events.owner_descr',
            'formType'    => 'campaignevent_lead_owner',
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
            'order'       => 5,
        ];

        $event->addCondition('lead.owner', $trigger);

        $trigger = [
            'label'       => 'le.lead.lead.events.campaigns',
            'description' => 'le.lead.lead.events.campaigns_descr',
            'formType'    => 'campaignevent_lead_campaigns',
            'formTheme'   => 'MauticLeadBundle:FormTheme\ContactCampaignsCondition',
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
            'order'       => 3,
        ];

        $event->addCondition('lead.campaigns', $trigger);

        $trigger = [
            'label'       => 'le.lead.lead.events.decision',
            'description' => 'le.lead.lead.events.decision_descr',
            'formType'    => 'campaignlistfilter',
            'formTheme'   => 'MauticLeadBundle:FormTheme\Filter',
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
            'order'       => 2,
        ];
        $event->addCondition('lead.campaign_list_filter', $trigger);

        $source = [
            'label'         => 'mautic.campaign.leadsource.lists',
            'description'   => 'mautic.campaign.leadsource.lists.desc',
            'formType'      => 'campaignsource_lists',
            'sourcetype'    => 'lists',
            'group'         => 'le.campaign.source.group.name',
            'order'         => 2,
        ];

        $event->addSources('lists', $source);

        $source = [
            'label'         => 'le.campaign.leadsource.allleads',
            'description'   => 'le.campaign.leadsource.allleads.desc',
            'sourcetype'    => 'allleads',
            'group'         => 'le.campaign.source.group.name',
            'order'         => 1,
        ];

        //$event->addSources('allleads', $source);

        $source = [
            'label'       => 'le.lead.list.filter.tags',
            'description' => 'le.lead.lead.events.tags_descr',
            'order'       => '4',
            'formType'    => 'campaignevent_lead_tags',
            'group'       => 'le.campaign.source.group.name',
            'sourcetype'  => 'leadtags',
        ];

        $event->addSources('leadtags', $source);

        $source = [
            'label'       => 'le.lead.list.filter.tags.remove',
            'description' => 'le.lead.list.filter.tags.remove.desc',
            'order'       => '5',
            'formType'    => 'campaignevent_lead_tags',
            'group'       => 'le.campaign.source.group.name',
            'sourcetype'  => 'leadtags.remove',
        ];

        $event->addSources('leadtags.remove', $source);

        $source = [
            'label'       => 'le.lead.lead.events.completed_dripcampaign',
            'description' => 'le.lead.lead.events.completed_dripcampaign_descr',
            'formType'    => 'dripemailsend_list',
            'order'       => '6',
            'group'       => 'le.campaign.source.group.name',
            'sourcetype'  => 'dripcampaign_completed',
        ];

        $event->addSources('dripcampaign_completed', $source);

        $source = [
            'label'       => 'le.lead.lead.events.field_value',
            'description' => 'le.lead.lead.events.field_value_descr',
            'formType'    => 'campaignevent_lead_field_value',
            'formTheme'   => 'MauticLeadBundle:FormTheme\FieldValueCondition',
            'order'       => '7',
            'group'       => 'le.campaign.source.group.name',
            'sourcetype'  => 'fieldvalue',
        ];

        $event->addSources('fieldvalue', $source);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionChangePoints(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('lead.changepoints')) {
            return;
        }

        $lead   = $event->getLead();
        $points = $event->getConfig()['points'];

        $somethingHappened = false;

        if ($lead !== null && !empty($points)) {
            $lead->adjustPoints($points);

            //add a lead point change log
            $log = new PointsChangeLog();
            $log->setDelta($points);
            $log->setLead($lead);
            $log->setType('campaign');
            $log->setEventName("{$event->getEvent()['campaign']['id']}: {$event->getEvent()['campaign']['name']}");
            $log->setActionName("{$event->getEvent()['id']}: {$event->getEvent()['name']}");
            $log->setIpAddress($this->ipLookupHelper->getIpAddress());
            $log->setDateAdded(new \DateTime());
            $lead->addPointsChangeLog($log);

            $this->leadModel->saveEntity($lead);
            $somethingHappened = true;
        }

        return $event->setResult($somethingHappened);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionChangeScore(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('lead.scorechange')) {
            return;
        }
        $lead              = $event->getLead();
        $score             = $event->getConfig()['score'];
        $somethingHappened = false;

        if ($lead !== null && !empty($score)) {
            $this->leadModel->getRepository()->updateContactScore($score, $lead->getId());
            $somethingHappened = true;
        }

        return $event->setResult($somethingHappened);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionChangeLists(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('lead.changelist')) {
            return;
        }

        $addTo      = $event->getConfig()['addToLists'];
        $removeFrom = $event->getConfig()['removeFromLists'];

        $lead              = $event->getLead();
        $somethingHappened = false;

        if (!empty($addTo)) {
            $this->leadModel->addToLists($lead, $addTo);
            $somethingHappened = true;
        }

        if (!empty($removeFrom)) {
            $this->leadModel->removeFromLists($lead, $removeFrom);
            $somethingHappened = true;
        }

        return $event->setResult($somethingHappened);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionUpdateLead(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('lead.updatelead')) {
            return;
        }

        $lead = $event->getLead();

        $this->leadModel->setFieldValues($lead, $event->getConfig(), false);
        $this->leadModel->saveEntity($lead);

        return $event->setResult(true);
    }

    public function onCampaignTriggerActionChangeOwner(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext(self::ACTION_LEAD_CHANGE_OWNER)) {
            return;
        }

        $lead = $event->getLead();
        $data = $event->getConfig();
        if (empty($data['owner'])) {
            return;
        }

        $this->leadModel->updateLeadOwner($lead, $data['owner']);

        return $event->setResult(true);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionUpdateTags(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('lead.changetags')) {
            return;
        }

        $config = $event->getConfig();
        $lead   = $event->getLead();

        $addTags    = (!empty($config['add_tags'])) ? $config['add_tags'] : [];
        $removeTags = (!empty($config['remove_tags'])) ? $config['remove_tags'] : [];

        $this->leadModel->modifyTags($lead, $addTags, $removeTags);

        return $event->setResult(true);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionAddToCompany(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('lead.addtocompany')) {
            return;
        }

        $company           = $event->getConfig()['company'];
        $lead              = $event->getLead();
        $somethingHappened = false;

        if (!empty($company)) {
            $somethingHappened = $this->leadModel->addToCompany($lead, $company);
        }
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionChangeCompanyScore(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('lead.scorecontactscompanies')) {
            return;
        }

        $score = $event->getConfig()['score'];
        $lead  = $event->getLead();

        if (!$this->leadModel->scoreContactsCompany($lead, $score)) {
            return $event->setFailed('le.lead.no_company');
        } else {
            return $event->setResult(true);
        }
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionDeleteContact(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('lead.deletecontact')) {
            return;
        }

        $currentMonth     =date('Y-m');

        $this->leadModel->deleteEntity($event->getLead());
        $this->licenseInfoHelper->intRecordCount('1', false);
        $this->licenseInfoHelper->intDeleteCount('1', true);
        $this->licenseInfoHelper->intDeleteMonth($currentMonth);

        return $event->setResult(true);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionSetDNC(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('lead.setdonotcontact')) {
            return;
        }

        $lead              = $event->getLead();
        $somethingHappened = false;
        if ($lead !== null) {
            $comments = $this->translator->trans('le.email.dnc.manual');
            $this->leadModel->addDncForLead($lead, 'email', $comments, DoNotContact::MANUAL);
            $somethingHappened = true;
        }

        return $event->setResult($somethingHappened);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerActionRemoveDNC(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('lead.removedonotcontact')) {
            return;
        }

        $lead   = $event->getLead();

        $somethingHappened = false;

        if ($lead !== null) {
            $this->leadModel->removeDncForLead($lead, 'email');
            $somethingHappened = true;
        }

        return $event->setResult($somethingHappened);
    }

    /**
     * @param CampaignExecutionEvent $event
     */
    public function onCampaignTriggerCondition(CampaignExecutionEvent $event)
    {
        $lead = $event->getLead();

        if (!$lead || !$lead->getId()) {
            return $event->setResult(false);
        }

        if ($event->checkContext('lead.device')) {
            $deviceRepo = $this->leadModel->getDeviceRepository();
            $result     = false;

            $deviceType   = $event->getConfig()['device_type'];
            $deviceBrands = $event->getConfig()['device_brand'];
            $deviceOs     = $event->getConfig()['device_os'];

            if (!empty($deviceType)) {
                $result = false;
                if (!empty($deviceRepo->getDevice($lead, $deviceType))) {
                    $result = true;
                }
            }

            if (!empty($deviceBrands)) {
                $result = false;
                if (!empty($deviceRepo->getDevice($lead, null, $deviceBrands))) {
                    $result = true;
                }
            }

            if (!empty($deviceOs)) {
                $result = false;
                if (!empty($deviceRepo->getDevice($lead, null, null, null, $deviceOs))) {
                    $result = true;
                }
            }
        } elseif ($event->checkContext('lead.tags')) {
            $tagRepo = $this->leadModel->getTagRepository();
            $result  = $tagRepo->checkLeadByTags($lead, $event->getConfig()['tags']);
        } elseif ($event->checkContext('lead.segments')) {
            $listRepo = $this->listModel->getRepository();
            $result   = $listRepo->checkLeadSegmentsByIds($lead, $event->getConfig()['segments']);
        } elseif ($event->checkContext('lead.owner')) {
            $result = $this->leadModel->getRepository()->checkLeadOwner($lead, $event->getConfig()['owner']);
        } elseif ($event->checkContext('lead.campaigns')) {
            $result = $this->campaignModel->getCampaignLeadRepository()->checkLeadInCampaigns($lead, $event->getConfig());
        } elseif ($event->checkContext('lead.field_value')) {
            $result = $this->leadModel->checkLeadFieldValue($lead, $event->getConfig());
        } elseif ($event->checkContext('lead.campaign_list_filter')) {
            $listRepo = $this->listModel->getRepository();
            $result   =$listRepo->checkConditionFilter($lead, $event->getConfig()['filters']);
        }

        return $event->setResult($result);
    }
}
