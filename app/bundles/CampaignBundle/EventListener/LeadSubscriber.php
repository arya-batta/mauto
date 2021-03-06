<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CampaignBundle\EventListener;

use Mautic\AssetBundle\Event\AssetLoadEvent;
use Mautic\CampaignBundle\Model\CampaignModel;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\EmailBundle\Event\EmailOpenEvent;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\ListLeadOptIn;
use Mautic\LeadBundle\Entity\Tag;
use Mautic\LeadBundle\Event\IntegrationEvent;
use Mautic\LeadBundle\Event\LeadEvent;
use Mautic\LeadBundle\Event\LeadMergeEvent;
use Mautic\LeadBundle\Event\LeadTimelineEvent;
use Mautic\LeadBundle\Event\ListChangeEvent;
use Mautic\LeadBundle\Event\ListOptInChangeEvent;
use Mautic\LeadBundle\LeadEvents;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Model\ListOptInModel;
use Mautic\PageBundle\Entity\Hit;
use Mautic\PageBundle\Event\PageHitEvent;
use Mautic\SubscriptionBundle\Helper\StateMachineHelper;
use Mautic\UserBundle\Entity\User;

/**
 * Class LeadSubscriber.
 */
class LeadSubscriber extends CommonSubscriber
{
    /**
     * @var CampaignModel
     */
    protected $campaignModel;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var StateMachineHelper
     */
    protected $smHelper;

    /**
     * LeadSubscriber constructor.
     *
     * @param CampaignModel $campaignModel
     * @param LeadModel     $leadModel
     */
    public function __construct(CampaignModel $campaignModel, LeadModel $leadModel, StateMachineHelper $smHelper)
    {
        $this->campaignModel = $campaignModel;
        $this->leadModel     = $leadModel;
        $this->smHelper      =  $smHelper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::LEAD_LIST_BATCH_CHANGE   => ['onLeadListBatchChange', 0],
            LeadEvents::LEAD_LIST_CHANGE         => ['onLeadListChange', 0],
            LeadEvents::TIMELINE_ON_GENERATE     => ['onTimelineGenerate', 0],
            LeadEvents::LEAD_POST_MERGE          => ['onLeadMerge', 0],
            LeadEvents::ADD_LEAD_WITH_CAMPAIGN   => ['AddLeadCampaignEvent', 0],
            LeadEvents::MODIFY_TAG_EVENT         => ['AddTagModifiedLead', 0],
            LeadEvents::MODIFY_LEAD_FIELD_EVENT  => ['AddModifiedLeadbasedonFields', 0],
            LeadEvents::DOWNLOAD_ASSET_EVENT     => ['DownloadAssetEvent', 0],
            LeadEvents::OPEN_EMAIL_EVENT         => ['OpenEmailEvent', 0],
            LeadEvents::CLICK_EMAIL_EVENT        => ['ClickEmailEvent', 0],
            LeadEvents::PAGE_HIT_EVENT           => ['PageHitEvent', 0],
            LeadEvents::REMOVE_TAG_EVENT         => ['RemoveTagModifiedLead', 0],
            LeadEvents::COMPLETED_DRIP_CAMPAIGN  => ['CompletedDripCampaign', 0],
            LeadEvents::LIST_OPT_IN_CHANGE       => ['onLeadOptInChanged', 0],
            LeadEvents::REMOVE_LSIT_OPTIN        => ['removeListOptin', 0],
            LeadEvents::INTEGRATION_EVENT        => ['integrationEvent', 0],
        ];
    }

    /**
     * Add/remove leads from campaigns based on batch lead list changes.
     *
     * @param ListChangeEvent $event
     */
    public function onLeadListBatchChange(ListChangeEvent $event)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            return;
        }
        static $campaignLists = [], $listCampaigns = [], $campaignReferences = [];

        $leads  = $event->getLeads();
        $list   = $event->getList();
        $action = $event->wasAdded() ? 'added' : 'removed';
        $em     = $this->em;

        $repo   = $this->campaignModel->getRepository();

        //get campaigns for the list
        $listCampaigns = $repo->getPublishedCampaignbySourceType('lists');

        // $leadLists = $em->getRepository('MauticLeadBundle:LeadList')->getLeadLists($leads, true, true);

        if (!empty($listCampaigns)) {
            foreach ($listCampaigns as $c) {
                foreach ($c as $event) {
                    $properties = unserialize($event['properties']);
                    $campaign   = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);

                    if ($action == 'added') {
                        if (in_array($list->getId(), $properties['lists'])) {
                            if ($event['goal'] != 'interrupt') {
                                $this->campaignModel->addLeads($campaign, $leads, false, true);
                                $this->campaignModel->putCampaignEventLogs($event['eventid'], $campaign, $leads);
                            } else {
                                $this->campaignModel->checkGoalAchievedByLeads($campaign, $leads, $event['eventid']);
                            }
                        }
                    }
                    unset($campaign);
                }
            }
        }

        /*if (!empty($listCampaigns[$list->getId()])) {
            foreach ($listCampaigns[$list->getId()] as $c) {
                if (!isset($campaignReferences[$c['id']])) {
                    $campaignReferences[$c['id']] = $em->getReference('MauticCampaignBundle:Campaign', $c['id']);
                }

                if ($action == 'added') {
                    $this->campaignModel->addLeads($campaignReferences[$c['id']], $leads, false, true);
                } else {
                    if (!isset($campaignLists[$c['id']])) {
                        $campaignLists[$c['id']] = [];
                        foreach ($c['lists'] as $l) {
                            $campaignLists[$c['id']][] = $l['id'];
                        }
                    }

                    $removeLeads = [];
                    foreach ($leads as $l) {
                        $lists = (isset($leadLists[$l])) ? $leadLists[$l] : [];
                        if (array_intersect(array_keys($lists), $campaignLists[$c['id']])) {
                            continue;
                        } else {
                            $removeLeads[] = $l;
                        }
                    }

                    $this->campaignModel->removeLeads($campaignReferences[$c['id']], $removeLeads, false, true);
                }
            }
        }*/

        // Save memory with batch processing
        unset($event, $em, $model, $leads, $list, $listCampaigns);
    }

    /**
     * Add/remove leads from campaigns based on lead list changes.
     *
     * @param ListChangeEvent $event
     */
    public function onLeadListChange(ListChangeEvent $event)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            return;
        }
        $lead   = $event->getLead();
        $list   = $event->getList();
        $action = $event->wasAdded() ? 'added' : 'removed';
        $repo   = $this->campaignModel->getRepository();

        //get campaigns for the list
        //$listCampaigns = $repo->getPublishedCampaignsByLeadLists($list->getId());
        $listCampaigns = $repo->getPublishedCampaignbySourceType('lists');

        //$leadLists   = $this->leadModel->getLists($lead, true);
        // $leadListIds = array_keys($leadLists);

        // If the lead was removed then don't count it
        // if ($action == 'removed') {
        // $key = array_search($list->getId(), $leadListIds);
        // unset($leadListIds[$key]);
        // }
        if (!empty($listCampaigns)) {
            foreach ($listCampaigns as $c) {
                foreach ($c as $event) {
                    $properties = unserialize($event['properties']);
                    $campaign   = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);
                    //                if (!isset($campaignLists[$c['id']])) {
//                    $campaignLists[$c['id']] = array_keys($properties['lists']);
//                }

                    if ($action == 'added') {
                        if (in_array($list->getId(), $properties['lists'])) {
                            if ($event['goal'] != 'interrupt') {
                                $this->campaignModel->addLead($campaign, $lead);
                                $this->campaignModel->putCampaignEventLog($event['eventid'], $campaign, $lead);
                            } else {
                                $this->campaignModel->checkGoalAchievedByLead($campaign, $lead, $event['eventid']);
                            }
                        }
                    }
//                else {
//                    if (array_intersect($leadListIds, $campaignLists[$c['id']])) {
//                        continue;
//                    }
//
//                    $this->campaignModel->removeLead($campaign, $lead);
//                }

                    unset($campaign);
                }
            }
        }
    }

    public function AddLeadCampaignEvent(LeadEvent $event)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            return;
        }
        $lead   = $event->getLead();
        //get campaigns for the list
        $repo              = $this->campaignModel->getRepository();
        $allLeadsCampaigns = $repo->getPublishedCampaignbySourceType('allleads');

        if (!empty($allLeadsCampaigns)) {
            foreach ($allLeadsCampaigns as $c) {
                foreach ($c as $event) {
                    $campaign = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);
                    if ($event['goal'] != 'interrupt') {
                        $this->campaignModel->addLead($campaign, $lead);
                        $this->campaignModel->putCampaignEventLog($event['eventid'], $campaign, $lead);
                    } else {
                        $this->campaignModel->checkGoalAchievedByLead($campaign, $lead, $event['eventid']);
                    }
                    unset($campaign);
                }
            }
        }
    }

    public function AddTagModifiedLead(LeadEvent $event)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            return;
        }
        $lead   = $event->getLead();
        //get campaigns for the list
        $repo              = $this->campaignModel->getRepository();
        $allLeadsCampaigns = $repo->getPublishedCampaignbySourceType('leadtags');
        if (!empty($allLeadsCampaigns)) {
            foreach ($allLeadsCampaigns as $c) {
                foreach ($c as $event) {
                    $properties = unserialize($event['properties']);
                    $campaign   = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);
                    if (!empty($lead->getTags())) {
                        foreach ($lead->getTags() as $tag) {
                            if (in_array($tag->getTag(), $properties['tags'])) {
                                if ($event['goal'] != 'interrupt') {
                                    $this->campaignModel->addLead($campaign, $lead);
                                    $this->campaignModel->putCampaignEventLog($event['eventid'], $campaign, $lead);
                                } else {
                                    $this->campaignModel->checkGoalAchievedByLead($campaign, $lead, $event['eventid']);
                                }
                                unset($campaign);
                                break;
                            }
//                            else {
//                                $this->campaignModel->removeLead($campaign, $lead);
//                            }
                        }
                    }
                }
            }
        }
    }

    public function RemoveTagModifiedLead(LeadEvent $event)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            return;
        }
        $lead        = $event->getLead();
        $removedTags = $event->getRemovedTags();

        //get campaigns for the list
        $repo              = $this->campaignModel->getRepository();
        $allLeadsCampaigns = $repo->getPublishedCampaignbySourceType('leadtags.remove');
        if (!empty($allLeadsCampaigns)) {
            foreach ($allLeadsCampaigns as $c) {
                foreach ($c as $event) {
                    $properties        = unserialize($event['properties']);
                    $campaign          = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);
                    $containsAllValues = !empty(array_intersect($properties['tags'], $removedTags));
                    if ($containsAllValues) {
                        if ($event['goal'] != 'interrupt') {
                            $this->campaignModel->addLead($campaign, $lead);
                            $this->campaignModel->putCampaignEventLog($event['eventid'], $campaign, $lead);
                        } else {
                            $this->campaignModel->checkGoalAchievedByLead($campaign, $lead, $event['eventid']);
                        }
                        unset($campaign);
                    }
                }
            }
        }
    }

    public function AddModifiedLeadbasedonFields(LeadEvent $event)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            return;
        }
        $lead   = $event->getLead();
        //get campaigns for the list
        $changes=$lead->getChanges(true);

        $repo              = $this->campaignModel->getRepository();
        $allLeadsCampaigns = $repo->getPublishedCampaignbySourceType('fieldvalue');

        if (!empty($allLeadsCampaigns)) {
            foreach ($allLeadsCampaigns as $c) {
                foreach ($c as $event) {
                    $properties = unserialize($event['properties']);
                    if (!empty($changes[$properties['field']])) {
                        if ($this->leadModel->checkLeadFieldValue($lead, $properties)) {
                            $campaign = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);
                            if ($event['goal'] != 'interrupt') {
                                $this->campaignModel->addLead($campaign, $lead);
                                $this->campaignModel->putCampaignEventLog($event['eventid'], $campaign, $lead);
                            } else {
                                $this->campaignModel->checkGoalAchievedByLead($campaign, $lead, $event['eventid']);
                            }
                            unset($campaign);
                        }
                    }
                }
            }
        }
    }

    public function DownloadAssetEvent(AssetLoadEvent $event)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            return;
        }
        $download = $event->getRecord();
        $asset    = $event->getAsset();
        $lead     = $download->getLead();
        //get campaigns for the list
        $repo              = $this->campaignModel->getRepository();
        $allLeadsCampaigns = $repo->getPublishedCampaignbySourceType('assertDownload');

        if (!empty($allLeadsCampaigns)) {
            foreach ($allLeadsCampaigns as $c) {
                foreach ($c as $event) {
                    $properties = unserialize($event['properties']);
                    if (in_array($asset->getId(), $properties['assets'])) {
                        $campaign = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);
                        if ($event['goal'] != 'interrupt') {
                            $this->campaignModel->addLead($campaign, $lead);
                            $this->campaignModel->putCampaignEventLog($event['eventid'], $campaign, $lead);
                        } else {
                            $this->campaignModel->checkGoalAchievedByLead($campaign, $lead, $event['eventid']);
                        }
                        unset($campaign);
                    }
                }
            }
        }
    }

    public function OpenEmailEvent(EmailOpenEvent $event)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            return;
        }
        $stat  = $event->getStat();
        $email = $event->getEmail();
        $lead  = $stat->getLead();
        //get campaigns for the list
        $repo              = $this->campaignModel->getRepository();
        $allLeadsCampaigns = $repo->getPublishedCampaignbySourceType('openEmail');
        if (!empty($allLeadsCampaigns)) {
            foreach ($allLeadsCampaigns as $c) {
                foreach ($c as $event) {
                    $properties   = unserialize($event['properties']);
                    $eventEmailId = $properties['emails'];
                    if ($properties['campaigntype'] == 'drip') {
                        $eventEmailId = $properties['driplist'];
                    }
                    $campaign   = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);
                    if ($email != null && in_array($email->getId(), $eventEmailId)) {
                        if ($event['goal'] != 'interrupt') {
                            $this->campaignModel->addLead($campaign, $lead);
                            $this->campaignModel->putCampaignEventLog($event['eventid'], $campaign, $lead);
                        } else {
                            $this->campaignModel->checkGoalAchievedByLead($campaign, $lead, $event['eventid']);
                        }
                        unset($campaign);
                    }
//                    else {
//                        $this->campaignModel->removeLead($campaign, $lead);
//                    }
                }
            }
        }
    }

    public function ClickEmailEvent(PageHitEvent $event)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            return;
        }
        $hit   = $event->getHit();
        $email = $hit->getEmail();
        $lead  = $hit->getLead();
        //get campaigns for the list
        $repo              = $this->campaignModel->getRepository();
        $allLeadsCampaigns = $repo->getPublishedCampaignbySourceType('clickEmail');

        if (!empty($allLeadsCampaigns)) {
            foreach ($allLeadsCampaigns as $c) {
                foreach ($c as $event) {
                    $properties   = unserialize($event['properties']);
                    $eventEmailId = $properties['emails'];
                    if ($properties['campaigntype'] == 'drip') {
                        $eventEmailId = $properties['driplist'];
                    }
                    $campaign   = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);

                    if ($email != null && in_array($email->getId(), $eventEmailId)) {
                        if ($event['goal'] != 'interrupt') {
                            $this->campaignModel->addLead($campaign, $lead);
                            $this->campaignModel->putCampaignEventLog($event['eventid'], $campaign, $lead);
                        } else {
                            $this->campaignModel->checkGoalAchievedByLead($campaign, $lead, $event['eventid']);
                        }
                        unset($campaign);
                    }
//                    else {
//                        $this->campaignModel->removeLead($campaign, $lead);
//                    }
                }
            }
        }
    }

    public function CompletedDripCampaign(LeadEvent $event)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            return;
        }
        $dripLeadID = $event->getCompletedDripsIds();
        //get campaigns for the list
        $repo              = $this->campaignModel->getRepository();
        $allLeadsCampaigns = $repo->getPublishedCampaignbySourceType('dripcampaign_completed');
        if (!empty($allLeadsCampaigns)) {
            foreach ($allLeadsCampaigns as $c) {
                foreach ($c as $event) {
                    $properties = unserialize($event['properties']);
                    if (array_key_exists($properties['dripemail'], $dripLeadID)) {
                        $leadValues= $dripLeadID[$properties['dripemail']];
                        foreach ($leadValues as $lead) {
                            $campaign = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);
                            if ($event['goal'] != 'interrupt') {
                                $this->campaignModel->addLead($campaign, $lead);
                                $this->campaignModel->putCampaignEventLog($event['eventid'], $campaign, $lead);
                            } else {
                                $this->campaignModel->checkGoalAchievedByLead($campaign, $lead, $event['eventid']);
                            }
                        }
                        unset($campaign);
                    }
                }
            }
        }
    }

    public function PageHitEvent(PageHitEvent $event)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            return;
        }
        $pagehit = $event->getHit();
        $lead    = $pagehit->getLead();
        //get campaigns for the list
        $repo              = $this->campaignModel->getRepository();
        $allLeadsCampaigns = $repo->getPublishedCampaignbySourceType('pagehit');

        if (!empty($allLeadsCampaigns)) {
            foreach ($allLeadsCampaigns as $c) {
                foreach ($c as $event) {
                    $properties = unserialize($event['properties']);
                    $result     = false;
                    if ($pagehit instanceof Page) {
                        list($parent, $children) = $pagehit->getVariants();
                        //use the parent (self or configured parent)
                        $pageHitId = $parent->getId();
                    } elseif ($pagehit instanceof Hit) {
                        $pageHitId = 0;
                        if ($pagehit->getPage() != null) {
                            $pageHitId = $pagehit->getPage()->getId();
                        }
                    } else {
                        $pageHitId = 0;
                    }

                    $limitToPages = (isset($properties['pages'])) ? $properties['pages'] : [];

                    $urlMatches = [];

                    // Check Landing Pages URL or Tracing Pixel URL
                    if (isset($properties['url']) && $properties['url']) {
                        $pageUrl     = $pagehit->getUrl();
                        $limitToUrls = explode(',', $properties['url']);

                        foreach ($limitToUrls as $url) {
                            $url              = trim($url);
                            $urlMatches[$url] = fnmatch($url, $pageUrl);
                        }
                    }

                    $refererMatches = [];

                    // Check Landing Pages URL or Tracing Pixel URL
                    if (isset($properties['referer']) && $properties['referer']) {
                        $refererUrl      = $pagehit->getReferer();
                        $limitToReferers = explode(',', $properties['referer']);

                        foreach ($limitToReferers as $referer) {
                            $referer                  = trim($referer);
                            $refererMatches[$referer] = fnmatch($referer, $refererUrl);
                        }
                    }

                    // **Page hit is true if:**
                    // 1. no landing page is set and no URL rule is set
                    $applyToAny = (empty($properties['url']) && empty($properties['referer']) && empty($limitToPages));

                    // 2. some landing pages are set and page ID match
                    $langingPageIsHit = (!empty($limitToPages) && in_array($pageHitId, $limitToPages));

                    // 3. URL rule is set and match with URL hit
                    $urlIsHit = (!empty($properties['url']) && in_array(true, $urlMatches));

                    // 3. URL rule is set and match with URL hit
                    $refererIsHit = (!empty($properties['referer']) && in_array(true, $refererMatches));

                    if ($langingPageIsHit || $urlIsHit || $refererIsHit) {
                        $result = true;
                    }
                    if ($result) {
                        $campaign = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);
                        if ($event['goal'] != 'interrupt') {
                            $this->campaignModel->addLead($campaign, $lead);
                            $this->campaignModel->putCampaignEventLog($event['eventid'], $campaign, $lead);
                        } else {
                            $this->campaignModel->checkGoalAchievedByLead($campaign, $lead, $event['eventid']);
                        }
                        unset($campaign);
                    }
                }
            }
        }
    }

    public function onLeadOptInChanged(ListOptInChangeEvent $leadevent)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            return;
        }
        $list   = $leadevent->getList();
        $lead   = $leadevent->getLead();
        $action = $leadevent->wasAdded() ? 'added' : 'removed';
        /** @var ListOptInModel $listoptinmodel */
        $listoptinmodel = $this->factory->getModel('lead.listoptin');
        /** @var ListLeadOptIn $listLead */
        $listLead = $listoptinmodel->getListLeadRepository()->getListEntityByid($lead->getId(), $list->getId());
        if (!$listLead->getConfirmedLead()) {
            return;
        }
        //get campaigns for the list
        $repo              = $this->campaignModel->getRepository();
        $allLeadsCampaigns = $repo->getPublishedCampaignbySourceType('listoptin');
        if (!empty($allLeadsCampaigns)) {
            foreach ($allLeadsCampaigns as $c) {
                foreach ($c as $event) {
                    $properties = unserialize($event['properties']);
                    if ($action == 'added') {
                        if (in_array($list->getId(), $properties['listoptin'])) {
                            $campaign = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);
                            if ($event['goal'] != 'interrupt') {
                                $this->campaignModel->addLead($campaign, $lead);
                                $this->campaignModel->putCampaignEventLog($event['eventid'], $campaign, $lead);
                            } else {
                                $this->campaignModel->checkGoalAchievedByLead($campaign, $lead, $event['eventid']);
                            }
                            unset($campaign);
                        }
                    }
                }
            }
        }
    }

    public function removeListOptin(ListOptInChangeEvent $leadevent)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            return;
        }
        $list   = $leadevent->getList();
        $lead   = $leadevent->getLead();
        $action = $leadevent->wasAdded() ? 'added' : 'removed';
        /** @var ListOptInModel $listoptinmodel */
        $listoptinmodel = $this->factory->getModel('lead.listoptin');
        /** @var ListLeadOptIn $listLead */
        $listLead = $listoptinmodel->getListLeadRepository()->getListEntityByid($lead->getId(), $list->getId());
        if (!$listLead->getConfirmedLead()) {
            return;
        }
        //get campaigns for the list
        $repo              = $this->campaignModel->getRepository();
        $allLeadsCampaigns = $repo->getPublishedCampaignbySourceType('listoptin.remove');
        if (!empty($allLeadsCampaigns)) {
            foreach ($allLeadsCampaigns as $c) {
                foreach ($c as $event) {
                    $properties = unserialize($event['properties']);
                    if ($action == 'removed') {
                        if (in_array($list->getId(), $properties['listoptin'])) {
                            $campaign = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);
                            if ($event['goal'] != 'interrupt') {
                                $this->campaignModel->addLead($campaign, $lead);
                                $this->campaignModel->putCampaignEventLog($event['eventid'], $campaign, $lead);
                            } else {
                                $this->campaignModel->checkGoalAchievedByLead($campaign, $lead, $event['eventid']);
                            }
                            unset($campaign);
                        }
                    }
                }
            }
        }
    }

    /**
     * Compile events for the lead timeline.
     *
     * @param LeadTimelineEvent $event
     */
    public function onTimelineGenerate(LeadTimelineEvent $event)
    {
        $this->addTimelineEvents($event, 'campaign.event', $this->translator->trans('mautic.campaign.triggered'));
        $this->addTimelineEvents($event, 'campaign.event.scheduled', $this->translator->trans('mautic.campaign.scheduled'));
    }

    /**
     * Update records after lead merge.
     *
     * @param LeadMergeEvent $event
     */
    public function onLeadMerge(LeadMergeEvent $event)
    {
        $this->em->getRepository('MauticCampaignBundle:LeadEventLog')->updateLead($event->getLoser()->getId(), $event->getVictor()->getId());

        $this->em->getRepository('MauticCampaignBundle:Lead')->updateLead($event->getLoser()->getId(), $event->getVictor()->getId());
    }

    /**
     * On Integration Webhook hit.
     *
     * @param IntegrationEvent $event
     */
    public function integrationEvent(IntegrationEvent $intevent)
    {
        if ($this->smHelper->isAnyInActiveStateAlive()) {
            $intevent->setIsSuccess(false);

            return;
        }
        //get campaigns for the list
        $repo                 = $this->campaignModel->getRepository();
        $integrationName      = $intevent->getIntegrationName();
        $payLoad              = $intevent->getPayload();
        $integrationName      = strtolower($integrationName);
        $isValidRecordAdd     = $this->factory->get('mautic.helper.licenseinfo')->isValidRecordAdd();
        $appStatus            = $this->factory->get('mautic.helper.licenseinfo')->getAppStatus();
        $remainingDays        = $this->factory->get('mautic.helper.licenseinfo')->getLicenseRemainingDays();
        /** @var \Mautic\PluginBundle\Helper\IntegrationHelper $integrationHelper */
        $integrationHelper = $this->factory->getHelper('integration');
        $eventName         = $integrationName;
        if ($integrationName == $this->factory->getTranslator()->trans('le.integration.name.calendly')) {
            $eventName = $payLoad->event;
        } elseif ($integrationName == 'facebook_lead_ads') {
            $eventName='fbLeadAds';
        }

        if (!$isValidRecordAdd || $remainingDays < 0 || ($appStatus == 'Suspended' || $appStatus == 'InActive')) {
            $intevent->setIsSuccess(false);

            return $intevent;
        }
        $integrationHelper->putPayLoadHistory($payLoad, $integrationName);
        $integrationHelper->updateIntegrationFieldInfo($payLoad, $integrationName);
        $allLeadsCampaigns = $repo->getPublishedCampaignbySourceType($eventName);
        if (!empty($allLeadsCampaigns)) {
            foreach ($allLeadsCampaigns as $c) {
                foreach ($c as $event) {
                    $properties = unserialize($event['properties']);
                    $data       = $integrationHelper->parseJsonResponse($payLoad, $integrationName, $properties);
                    if ($data['isvalid']) {
                        $result = $this->leadModel->findEmail($data['email']);
                        $lead   = new Lead();
                        if (count($result) > 0) {
                            $lead = $this->leadModel->getEntity($result[0]->getId());
                        }
                        try {
                            foreach ($data as $key => $value) {
                                if ($key == 'isvalid' || $key == 'created_on' || $key == 'owner_id' || $key == 'tags' || $key == 'listoptin') {
                                    continue;
                                }

                                if ($isDateField = $integrationHelper->isDateField($key)) {
                                    if (!$integrationHelper->isDate($value)) {
                                        $defaultData =  $integrationHelper->setDefaultValue($integrationName, $data, $key);
                                        $value       = $defaultData[$key];
                                    }
                                }
                                $lead->addUpdatedField($key, $value);
                            }
                            if (!empty($data['created_on'])) {
                                $lead->setDateIdentified($data['created_on']);
                                $lead->setDateAdded($data['created_on']);
                            }
                            $entityManager=$this->factory->getEntityManager();

                            if (!empty($data['owner_id'])) {
                                $owner = $entityManager->getReference(User::class, $data['owner_id']);
                                $lead->setOwner($owner);
                            }
                            if (!empty($data['tags'])) {
                                $leadTags     = $lead->getTags();
                                $tag          = $entityManager->getReference(Tag::class, $data['tags']);
                                $value        = $this->factory->getModel('lead.tag')->getRepository()->findOneBy(['id'=>$tag->getId()]);

                                if (!count($value) > 0) {
                                    $data     = $integrationHelper->setDefaultValue($integrationName, $data, 'tags');
                                }
                                $defaulttag   = null;
                                $defaultTagId = null;
                                if (!empty($data['tags'])) {
                                    $defaulttag   = $entityManager->getReference(Tag::class, $data['tags']);
                                    $defaultTagId = $defaulttag->getId();
                                }
                                $defaultValue = $this->factory->getModel('lead.tag')->getRepository()->findOneBy(['id'=>$defaultTagId]);

                                if (!empty($tag) && !$leadTags->contains($tag) && count($defaultValue) > 0 && $defaultValue->getisPublished()) {
                                    $lead->addTag($defaulttag);
                                }
                            }
                            $lead->setScore('cold');
                            $lead->setCreatedSource(5); //Created Source INTEGRATION
                            $lead->setStatus(1); //Lead Status as ACTIVE
                            $this->leadModel->saveEntity($lead);

                            if (!empty($data['listoptin'])) {
                                $listoptin = $this->factory->getModel('lead.listoptin')->getEntity($data['listoptin']);
                                if ($listoptin == null) {
                                    $data = $integrationHelper->setDefaultValue($integrationName, $data, 'listoptin');
                                }
                                $defaultlistoptin = $this->factory->getModel('lead.listoptin')->getEntity($data['listoptin']);
                                if ($defaultlistoptin != null && $defaultlistoptin->isPublished()) {
                                    $this->leadModel->modifyListOptIn($lead, [$data['listoptin']]);
                                }
                            }
                            $campaign = $this->em->getReference('MauticCampaignBundle:Campaign', $event['id']);
                            if ($event['goal'] != 'interrupt') {
                                $this->campaignModel->addLead($campaign, $lead);
                                $this->campaignModel->putCampaignEventLog($event['eventid'], $campaign, $lead);
                            } else {
                                $this->campaignModel->checkGoalAchievedByLead($campaign, $lead, $event['eventid']);
                            }
                            unset($campaign);
                            $intevent->setIsSuccess(true);
                        } catch (\Exception $ex) {
                            $intevent->setIsSuccess(false);
                        }
                    } else {
                        $intevent->setIsSuccess(false);
                    }
                }
            }
        }

        return $intevent;
    }

    /**
     * @param LeadTimelineEvent $event
     * @param                   $eventTypeKey
     * @param                   $eventTypeName
     */
    protected function addTimelineEvents(LeadTimelineEvent $event, $eventTypeKey, $eventTypeName)
    {
        $event->addSerializerGroup('campaignList');

        // Decide if those events are filtered
        //if (!$event->isApplicable($eventTypeKey)) {
        // return;
        //}

        /** @var \Mautic\CampaignBundle\Entity\LeadEventLogRepository $logRepository */
        $logRepository             = $this->em->getRepository('MauticCampaignBundle:LeadEventLog');
        $options                   = $event->getQueryOptions();
        $options['scheduledState'] = ('campaign.event' === $eventTypeKey) ? false : true;
        $logs                      = $logRepository->getLeadLogs($event->getLeadId(), $options);
        $eventSettings             = $this->campaignModel->getEvents();

        // Add total number to counter
        // $event->addToCounter($eventTypeKey, $logs);

        if (!$event->isEngagementCount()) {
            foreach ($logs['results'] as $log) {
                $template = (!empty($eventSettings['action'][$log['type']]['timelineTemplate']))
                    ? $eventSettings['action'][$log['type']]['timelineTemplate'] : 'MauticCampaignBundle:SubscribedEvents\Timeline:index.html.php';

                $label = $log['event_name'].' / '.$log['campaign_name'];

                $href = $this->router->generate('le_campaign_action', ['objectAction' => 'edit', 'objectId' => $log['campaign_id']]);

                if (empty($log['isScheduled']) && empty($log['dateTriggered'])) {
                    // Note as cancelled
                    $label .= ' <i data-toggle="tooltip" title="'.$this->translator->trans('mautic.campaign.event.cancelled')
                        .'" class="fa fa-calendar-times-o text-warning timeline-campaign-event-cancelled-'.$log['event_id'].'"></i>';
                }

                if ((!empty($log['metadata']['errors']) && empty($log['dateTriggered'])) || !empty($log['metadata']['failed'])) {
                    $title= isset($log['metadata']['reason']) ? $log['metadata']['reason'] : $this->translator->trans('mautic.campaign.event.has_last_attempt_error');
                    $label .= ' <i data-toggle="tooltip" title="'.$title.'" class="fa fa-warning text-danger"></i>';
                }
                if (empty($log['metadata']['errors']) && !empty($log['dateTriggered']) && empty($log['metadata']['failed'])) {
                    $label = $this->translator->trans('le.workflow.event.triggered.eventlabel', ['%eventname%' => $log['event_name'], '%workflowname%' => $log['campaign_name'], '%href%' => $href]);
                }
                $extra = [
                    'log' => $log,
                ];

                if ($event->isForTimeline()) {
                    $extra['campaignEventSettings'] = $eventSettings;
                }
                $type            =$log['type'];
                $eventtype       =$log['event_type'];
                $modeofsource    =$log['trigger_mode'];
                $campaignid      =$log['campaign_id'];
                $eventid         =$log['event_id'];
                $subeventTypeKey =$eventTypeKey;
                $subeventTypeName=$eventTypeName;
                $subeventTypeIcon='fa-clock-o';
                if ($type == 'email.send.to.user') {
                    continue;
                }
                if ($eventtype == 'source') {
                    if ($modeofsource == 'interrupt') {
                        $subeventTypeKey ='campaign.event.goal';
                        $subeventTypeName=$this->translator->trans('mautic.campaign.workflow.goal.acheived');
                        $subeventTypeIcon='fa-trophy';
                        $label           = $this->translator->trans('le.campaign.workflow.goal.acheived.eventlabel', ['%workflowname%' => $log['campaign_name'], '%eventname%' => $log['event_name'], '%href%' => $href]);
                    } else {
                        $subeventTypeKey ='campaign.event.started';
                        $subeventTypeName=$this->translator->trans('mautic.campaign.workflow.started');
                        $subeventTypeIcon='fa-sign-in';
                        $label           = $this->translator->trans('le.campaign.workflow.started.eventlabel', ['%workflowname%' => $log['campaign_name'], '%href%' => $href]);
                    }
                    $event->addEventType($subeventTypeKey, $subeventTypeName);
                } elseif ($eventtype == 'condition') {
                    $subeventTypeKey ='campaign.event.condition';
                    $subeventTypeName=$this->translator->trans('mautic.campaign.workflow.decision');
                    $subeventTypeIcon='fa-hourglass';
                    $label           = $this->translator->trans('le.campaign.workflow.decision.eventlabel', ['%workflowname%' => $log['campaign_name'], '%eventname%' => $log['event_name'], '%href%' => $href]);
                    $event->addEventType($subeventTypeKey, $subeventTypeName);
                } elseif ($type == 'campaign.defaultexit') {
                    if ($this->campaignModel->isWorkFlowCompleteEvent($campaignid, $eventid)) {
                        $subeventTypeKey ='campaign.event.completed';
                        $subeventTypeName=$this->translator->trans('mautic.campaign.workflow.completed');
                        $label           = $this->translator->trans('le.campaign.workflow.completed.eventlabel', ['%workflowname%' => $log['campaign_name'], '%href%' => $href]);
                    }
                    $subeventTypeIcon='fa-sign-out';
                    $event->addEventType($subeventTypeKey, $subeventTypeName);
                } else {
                    if ($type == 'campaign.defaultdelay') {
                        $subeventTypeIcon='fa-hourglass';
                    } else {
                        $subeventTypeIcon='fa-bolt';
                    }
                    $event->addEventType($subeventTypeKey, $subeventTypeName);
                }
                if (!$event->isApplicable($subeventTypeKey)) {
                    continue;
                }
                $event->addToCounter($subeventTypeKey, 1);
                $event->addEvent(
                    [
                        'event'      => $subeventTypeKey,
                        'eventId'    => $subeventTypeKey.$log['log_id'],
                        'eventLabel' => [
                            'label' => $label,
                            /**'href'  => $this->router->generate(
                            'le_campaign_action',
                            ['objectAction' => 'edit', 'objectId' => $log['campaign_id']]
                            ),*/
                        ],
                        'eventType'       => $subeventTypeName,
                        'timestamp'       => $log['dateTriggered'],
                        'extra'           => $extra,
                        'contentTemplate' => $template,
                        'icon'            => $subeventTypeIcon,
                        'contactId'       => $log['lead_id'],
                    ]
                );
            }
        }
    }
}
