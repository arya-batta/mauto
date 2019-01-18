<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CampaignBundle\Model;

use Doctrine\ORM\PersistentCollection;
use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CampaignBundle\Entity\Event;
use Mautic\CampaignBundle\Entity\Lead as CampaignLead;
use Mautic\CampaignBundle\Entity\LeadEventLog;
use Mautic\CampaignBundle\Event as Events;
use Mautic\CategoryBundle\Model\CategoryModel;
use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\CoreBundle\Helper\ProgressBarHelper;
use Mautic\CoreBundle\Model\FormModel as CommonFormModel;
use Mautic\FormBundle\Entity\Form;
use Mautic\FormBundle\Model\FormModel;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Model\ListModel;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class CampaignModel
 * {@inheritdoc}
 */
class CampaignModel extends CommonFormModel
{
    /**
     * @var mixed
     */
    protected $batchSleepTime;

    /**
     * @var mixed
     */
    protected $batchCampaignSleepTime;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var ListModel
     */
    protected $leadListModel;

    /**
     * @var FormModel
     */
    protected $formModel;

    /**
     * @var CategoryModel
     */
    protected $categoryModel;

    /**
     * @var
     */
    protected static $events;

    /**
     * @var array
     */
    private $removedLeads = [];

    /**
     * CampaignModel constructor.
     *
     * @param CoreParametersHelper $coreParametersHelper
     * @param LeadModel            $leadModel
     * @param ListModel            $leadListModel
     * @param FormModel            $formModel
     * @param CategoryModel        $categoryModel
     */
    public function __construct(CoreParametersHelper $coreParametersHelper, LeadModel $leadModel, ListModel $leadListModel, FormModel $formModel, CategoryModel $categoryModel)
    {
        $this->leadModel              = $leadModel;
        $this->leadListModel          = $leadListModel;
        $this->formModel              = $formModel;
        $this->batchSleepTime         = $coreParametersHelper->getParameter('mautic.batch_sleep_time');
        $this->batchCampaignSleepTime = $coreParametersHelper->getParameter('mautic.batch_campaign_sleep_time');
        $this->categoryModel          = $categoryModel;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Mautic\CampaignBundle\Entity\CampaignRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MauticCampaignBundle:Campaign');
    }

    /**
     * @return \Mautic\CampaignBundle\Entity\EventRepository
     */
    public function getEventRepository()
    {
        return $this->em->getRepository('MauticCampaignBundle:Event');
    }

    /**
     * @return \Mautic\CampaignBundle\Entity\LeadRepository
     */
    public function getCampaignLeadRepository()
    {
        return $this->em->getRepository('MauticCampaignBundle:Lead');
    }

    /**
     * @return \Mautic\CampaignBundle\Entity\LeadEventLogRepository
     */
    public function getCampaignLeadEventLogRepository()
    {
        return $this->em->getRepository('MauticCampaignBundle:LeadEventLog');
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getPermissionBase()
    {
        return 'campaign:campaigns';
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
        if (!$entity instanceof Campaign) {
            throw new MethodNotAllowedHttpException(['Campaign']);
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('campaign', $entity, $options);
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return null|Campaign
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            return new Campaign();
        }

        $entity = parent::getEntity($id);

        return $entity;
    }

    /**
     * @param object $entity
     */
    public function deleteEntity($entity)
    {
        // Null all the event parents for this campaign to avoid database constraints
        $this->getEventRepository()->nullEventParents($entity->getId());

        parent::deleteEntity($entity);
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
    protected function dispatchEvent($action, &$entity, $isNew = false, \Symfony\Component\EventDispatcher\Event $event = null)
    {
        if ($entity instanceof \Mautic\CampaignBundle\Entity\Lead) {
            return;
        }

        if (!$entity instanceof Campaign) {
            throw new MethodNotAllowedHttpException(['Campaign']);
        }

        switch ($action) {
            case 'pre_save':
                $name = CampaignEvents::CAMPAIGN_PRE_SAVE;
                break;
            case 'post_save':
                $name = CampaignEvents::CAMPAIGN_POST_SAVE;
                break;
            case 'pre_delete':
                $name = CampaignEvents::CAMPAIGN_PRE_DELETE;
                break;
            case 'post_delete':
                $name = CampaignEvents::CAMPAIGN_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new Events\CampaignEvent($entity, $isNew);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        } else {
            return null;
        }
    }

    /**
     * @param Campaign $entity
     * @param          $sessionEvents
     * @param          $sessionConnections
     * @param          $deletedEvents
     *
     * @return array
     */
    public function setEvents(Campaign $entity, $sessionEvents, $sessionConnections, $deletedEvents)
    {
        $eventSettings  = $this->getEvents();
        $existingEvents = $entity->getEvents()->toArray();
        $events         =
        $hierarchy      =
        $parentUpdated  = [];

        foreach ($sessionEvents as $properties) {
            $isNew = (!empty($properties['id']) && isset($existingEvents[$properties['id']])) ? false : true;
            $event = !$isNew ? $existingEvents[$properties['id']] : new Event();

            foreach ($properties as $f => $v) {
                if ($f == 'id' && strpos($v, 'new') === 0) {
                    //set the temp ID used to be able to match up connections
                    $event->setTempId($v);
                }

                if (in_array($f, ['id', 'parent'])) {
                    continue;
                }

                $func = 'set'.ucfirst($f);
                if (method_exists($event, $func)) {
                    $event->$func($v);
                }
            }

            $this->setChannelFromEventProperties($event, $properties, $eventSettings[$properties['eventType']]);

            $event->setCampaign($entity);
            $events[$properties['id']] = $event;
        }

        foreach ($deletedEvents as $deleteMe) {
            if (isset($existingEvents[$deleteMe])) {
                // Remove child from parent
                $parent = $existingEvents[$deleteMe]->getParent();
                if ($parent) {
                    $parent->removeChild($existingEvents[$deleteMe]);
                    $existingEvents[$deleteMe]->removeParent();
                }
                $entity->removeEvent($existingEvents[$deleteMe]);
                unset($events[$deleteMe]);
            }
        }
        // Assign parent/child relationships
        foreach ($events as $id => $e) {
            $entity->addEvent($id, $events[$id]);
        }

        //set event order used when querying the events
        // $this->buildOrder($hierarchy, $events, $entity);

        uasort($events, function ($a, $b) {
            $aOrder = $a->getOrder();
            $bOrder = $b->getOrder();
            if ($aOrder == $bOrder) {
                return 0;
            }

            return ($aOrder < $bOrder) ? -1 : 1;
        });

        // Persist events if campaign is being edited
        if ($entity->getId()) {
            $this->getEventRepository()->saveEntities($events);
        }

        return $events;
    }

    /**
     * @param $entity
     * @param $properties
     * @param $eventSettings
     *
     * @return bool
     */
    public function setChannelFromEventProperties($entity, $properties, &$eventSettings)
    {
        $channelSet = false;
        if (!$entity->getChannel() && !empty($eventSettings[$properties['type']]['channel'])) {
            $entity->setChannel($eventSettings[$properties['type']]['channel']);
            if (isset($eventSettings[$properties['type']]['channelIdField'])) {
                $channelIdField = $eventSettings[$properties['type']]['channelIdField'];
                if (!empty($properties['properties'][$channelIdField])) {
                    if (is_array($properties['properties'][$channelIdField])) {
                        if (count($properties['properties'][$channelIdField]) === 1) {
                            // Only store channel ID if a single item was selected
                            $entity->setChannelId($properties['properties'][$channelIdField]);
                        }
                    } else {
                        $entity->setChannelId($properties['properties'][$channelIdField]);
                    }
                }
            }
            $channelSet = true;
        }

        return $channelSet;
    }

    /**
     * @param      $entity
     * @param      $settings
     * @param bool $persist
     * @param null $events
     *
     * @return array
     */
    public function setCanvasSettings($entity, $settings, $persist = true, $events = null)
    {
        if ($events === null) {
            $events = $entity->getEvents();
        }

        $tempIds = [];
        foreach ($events as $e) {
            if ($e instanceof Event) {
                $tempIds[$e->getTempId()] = $e->getId();
            } else {
                $tempIds[$e['tempId']] = $e['id'];
            }
        }
        $availableevents=[];
        $settings       =json_decode($settings);
        $this->replaceAllTempIDS($settings, $tempIds, $availableevents);
        $settings=json_encode($settings);
        $entity->setCanvasSettings($settings);
        if (sizeof($availableevents) > 0) {
            foreach ($events as $e) {
                if (!in_array($e->getId(), $availableevents)) {
                    $entity->removeEvent($e);
                    $this->getEventRepository()->deleteEntity($e);
                }
            }
        }
        if ($persist) {
            $this->getRepository()->saveEntity($entity);
        }

        return $settings;
    }

    /**
     * @param $data
     * @param $tempIds
     * @param $availableevents
     */
    public function replaceAllTempIDS($data, $tempIds, &$availableevents)
    {
        $type=$data->type;
        $id  =$data->id;
        if ($type == 'path') {
            $triggers=$data->triggers;
            $steps   =$data->steps;
            foreach ($triggers as $key => $trigger) {
                if (isset($tempIds[$trigger->id])) {
                    $trigger->id=$tempIds[$trigger->id];
                }
                $availableevents[]=$trigger->id;
            }
            foreach ($steps as $key => $step) {
                $this->replaceAllTempIDS($step, $tempIds, $availableevents);
            }
        } elseif ($type == 'action' || $type == 'exit' || $type == 'delay') {
            if (isset($tempIds[$id])) {
                $data->id=$tempIds[$id];
            }
            $availableevents[]=$data->id;
        } elseif ($type == 'decision') {
            if (isset($tempIds[$id])) {
                $data->id=$tempIds[$id];
            }
            $availableevents[]=$data->id;
            $this->replaceAllTempIDS($data->true_path, $tempIds, $availableevents);
            $this->replaceAllTempIDS($data->false_path, $tempIds, $availableevents);
        } elseif ($type == 'fork') {
            $paths=$data->paths;
            foreach ($paths as $key => $path) {
                $this->replaceAllTempIDS($path, $tempIds, $availableevents);
            }
        } elseif ($type == 'interrupt') {
            $triggers=$data->triggers;
            foreach ($triggers as $key => $trigger) {
                if (isset($tempIds[$trigger->id])) {
                    $trigger->id=$tempIds[$trigger->id];
                }
                $availableevents[]=$trigger->id;
            }
        }
    }

    /**
     * @param $data
     * @param $eventid
     * @param $matched
     *
     * @return array
     */
    public function getAllParellalGoals($data, $eventid, $matched)
    {
        $type         =$data->type;
        $matchnotfound=sizeof($matched) == 0;
        if ($type == 'path' && $matchnotfound) {
            $steps=$data->steps;
            foreach ($steps as $key => $step) {
                $matched=$this->getAllParellalGoals($step, $eventid, $matched);
            }
        } elseif ($type == 'decision' && $matchnotfound) {
            $matched=$this->getAllParellalGoals($data->true_path, $eventid, $matched);
            $matched=$this->getAllParellalGoals($data->false_path, $eventid, $matched);
        } elseif ($type == 'fork' && $matchnotfound) {
            $paths=$data->paths;
            foreach ($paths as $key => $path) {
                $matched=$this->getAllParellalGoals($path, $eventid, $matched);
            }
        } elseif ($type == 'interrupt' && $matchnotfound) {
            $triggers=$data->triggers;
            $found   =false;
            foreach ($triggers as $key => $trigger) {
                $matched[]= $trigger->id;
                if ($eventid == $trigger->id) {
                    $found=true;
                    break;
                }
            }
            if (!$found) {
                $matched=[];
            }
        }

        return $matched;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function getExitEvent($data)
    {
        $type=$data->type;
        if ($type == 'path') {
            $steps=$data->steps;
            $step =$steps[sizeof($steps) - 1];
            if ($step->type == 'exit') {
                return [$step->id];
            }
        }

        return [];
    }

    /**
     * Gets array of custom events from bundles subscribed CampaignEvents::CAMPAIGN_ON_BUILD.
     *
     * @param string|null $type Specific type of events to retreive
     *
     * @return mixed
     */
    public function getEvents($type = null)
    {
        if (null === self::$events) {
            self::$events = [];

            //build them
            $events = [];
            $event  = new Events\CampaignBuilderEvent($this->translator);
            $this->dispatcher->dispatch(CampaignEvents::CAMPAIGN_ON_BUILD, $event);

            $events['decision']  = $event->getDecisions();
            $events['condition'] = $event->getConditions();
            $events['action']    = $event->getActions();
            $events['source']    = $event->getSources();

            $connectionRestrictions           = ['anchor' => []];
            $events['connectionRestrictions'] = $connectionRestrictions;
            self::$events                     = $events;
        }

        if (null !== $type) {
            if (!isset(self::$events[$type])) {
                throw new \InvalidArgumentException("$type not found as array key");
            }

            return self::$events[$type];
        }

        return self::$events;
    }

    /**
     * Get list of sources for a campaign.
     *
     * @param $campaign
     *
     * @return array
     */
    public function getLeadSources($campaign)
    {
        $campaignId = ($campaign instanceof Campaign) ? $campaign->getId() : $campaign;

        $sources = [];

        // Lead lists
        $sources['lists'] = $this->getRepository()->getCampaignListSources($campaignId);

        // Forms
        $sources['forms'] = $this->getRepository()->getCampaignFormSources($campaignId);

        return $sources;
    }

    /**
     * Add and/or delete lead sources from a campaign.
     *
     * @param $entity
     * @param $addedSources
     * @param $deletedSources
     */
    public function setLeadSources(Campaign $entity, $addedSources, $deletedSources)
    {
        foreach ($addedSources as $type => $sources) {
            foreach ($sources as $id => $label) {
                switch ($type) {
                    case 'lists':
                        $entity->addList($this->em->getReference('MauticLeadBundle:LeadList', $id));
                        break;
                    case 'forms':
                        $entity->addForm($this->em->getReference('MauticFormBundle:Form', $id));
                        break;
                    default:
                        break;
                }
            }
        }

        foreach ($deletedSources as $type => $sources) {
            foreach ($sources as $id => $label) {
                switch ($type) {
                    case 'lists':
                        $entity->removeList($this->em->getReference('MauticLeadBundle:LeadList', $id));
                        break;
                    case 'forms':
                        $entity->removeForm($this->em->getReference('MauticFormBundle:Form', $id));
                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
     * Get a list of source choices.
     *
     * @param string $sourceType
     * @param bool   $globalOnly
     *
     * @return array
     */
    public function getSourceLists($sourceType = null, $globalOnly = false)
    {
        $choices = [];
        switch ($sourceType) {
            case 'lists':
            case null:
                $choices['lists'] = [];
                $lists            = $globalOnly ? $this->leadListModel->getGlobalLists() : $this->leadListModel->getUserLists();

                if ($lists) {
                    foreach ($lists as $list) {
                        $choices['lists'][$list['id']] = $list['name'];
                    }
                }

            case 'forms':
            case null:
                $choices['forms'] = [];
                $viewOther        = $this->security->isGranted('form:forms:viewother');
                $repo             = $this->formModel->getRepository();
                $repo->setCurrentUser($this->userHelper->getUser());

                $forms = $repo->getFormList('', 0, 0, false, $viewOther, 'campaign');

                if ($forms) {
                    foreach ($forms as $form) {
                        $choices['forms'][$form['id']] = $form['name'];
                    }
                }

            case 'category':
            case null:
               $choices['category'] = [];
               $repo                = $this->categoryModel->getRepository();
               $repo->setCurrentUser($this->userHelper->getUser());
               $categories = $this->categoryModel->getLookupResults('campaign');

                if ($categories) {
                    foreach ($categories as $cat) {
                        $choices['category'][$cat['id']] = $cat['title'];
                    }
                }
        }

        foreach ($choices as &$typeChoices) {
            asort($typeChoices);
        }

        return ($sourceType == null) ? $choices : $choices[$sourceType];
    }

    /**
     * @param mixed $form
     *
     * @return array
     */
    public function getCampaignsByForm($form)
    {
        $formId = ($form instanceof Form) ? $form->getId() : $form;

        return $this->getRepository()->findByFormId($formId);
    }

    /**
     * Gets the campaigns a specific lead is part of.
     *
     * @param Lead $lead
     * @param bool $forList
     *
     * @return mixed
     */
    public function getLeadCampaigns(Lead $lead = null, $forList = false)
    {
        static $campaigns = [];

        if ($lead === null) {
            $lead = $this->leadModel->getCurrentLead();
        }

        if (!isset($campaigns[$lead->getId()])) {
            $repo   = $this->getRepository();
            $leadId = $lead->getId();
            //get the campaigns the lead is currently part of
            $campaigns[$leadId] = $repo->getPublishedCampaigns(null, $lead->getId(), $forList);
        }

        return $campaigns[$lead->getId()];
    }

    /**
     * Gets a list of published campaigns.
     *
     * @param bool $forList
     *
     * @return array
     */
    public function getPublishedCampaigns($forList = false)
    {
        static $campaigns = [];

        if (empty($campaigns)) {
            $this->getRepository()->setCurrentUser($this->userHelper->getUser());
            $campaigns = $this->getRepository()->getPublishedCampaigns(null, null, $forList);
        }

        return $campaigns;
    }

    /**
     * Add lead to the campaign.
     *
     * @param Campaign  $campaign
     * @param           $lead
     * @param bool|true $manuallyAdded
     */
    public function addLead(Campaign $campaign, $lead, $manuallyAdded = true)
    {
        $this->addLeads($campaign, [$lead], $manuallyAdded);

        unset($campaign, $lead);
    }

    /**
     * Add lead(s) to a campaign.
     *
     * @param Campaign $campaign
     * @param array    $leads
     * @param bool     $manuallyAdded
     * @param bool     $batchProcess
     * @param int      $searchListLead
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function addLeads(Campaign $campaign, array $leads, $manuallyAdded = false, $batchProcess = false, $searchListLead = 1)
    {
        foreach ($leads as $lead) {
            if ($lead instanceof Lead) {
                $leadId = $lead->getId();
            } else {
                $leadId = (is_array($lead) && isset($lead['id'])) ? $lead['id'] : $lead;
                $lead   = $this->em->getReference('MauticLeadBundle:Lead', $leadId);
            }

            if ($searchListLead == -1) {
                $campaignLead = null;
            } elseif ($searchListLead) {
                $campaignLead = $this->getCampaignLeadRepository()->findOneBy([
                    'lead'     => $lead,
                    'campaign' => $campaign,
                ]);
            } else {
                $campaignLead = $this->em->getReference('MauticCampaignBundle:Lead', [
                    'lead'     => $leadId,
                    'campaign' => $campaign->getId(),
                ]);
            }

            $dispatchEvent = true;
            if ($campaignLead != null) {
                if ($campaignLead->wasManuallyRemoved()) {
                    $campaignLead->setManuallyRemoved(false);
                    $campaignLead->setManuallyAdded($manuallyAdded);

                    $dispatchEvent = $this->saveCampaignLead($campaignLead);
                } else {
                    $this->em->detach($campaignLead);
                    if ($batchProcess) {
                        $this->em->detach($lead);
                    }

                    unset($campaignLead, $lead);

                    continue;
                }
            } else {
                $campaignLead = new CampaignLead();
                $campaignLead->setCampaign($campaign);
                $campaignLead->setDateAdded(new \DateTime());
                $campaignLead->setLead($lead);
                $campaignLead->setManuallyAdded($manuallyAdded);

                $dispatchEvent = $this->saveCampaignLead($campaignLead);
            }

            if ($dispatchEvent && $this->dispatcher->hasListeners(CampaignEvents::CAMPAIGN_ON_LEADCHANGE)) {
                $event = new Events\CampaignLeadChangeEvent($campaign, $lead, 'added');
                $this->dispatcher->dispatch(CampaignEvents::CAMPAIGN_ON_LEADCHANGE, $event);

                unset($event);
            }

            // Detach CampaignLead to save memory
            $this->em->detach($campaignLead);
            if ($batchProcess) {
                $this->em->detach($lead);
            }
            unset($campaignLead, $lead);
        }

        unset($leadModel, $campaign, $leads);
    }

    /**
     * Check goal achieved by lead.
     *
     * @param Campaign $campaign
     * @param Lead     $leads
     * @param          $eventid
     *
     * @return bool
     */
    public function checkGoalAchievedByLead($campaign, $lead, $eventid)
    {
        if (!$campaign instanceof Campaign) {
            $campaign   = $this->em->getReference('MauticCamapignBundle:Campaign', $campaign);
        }
        $achieved       =false;
        $completedevents=$this->getEventRepository()->getCompletedEvents($campaign->getId(), $lead->getId(), []);
        if ($completedevents > 0) {
            $canvassettings=json_decode($campaign->getCanvasSettings());
            $isLeadExited  =$this->checkCampaignLeadExited($campaign, $lead);
            if (!$isLeadExited) {
                $parellalgoals=$this->getAllParellalGoals($canvassettings, $eventid, []);
                if (sizeof($parellalgoals) > 0) {
                    $achievedgoals=$this->getEventRepository()->getCompletedEvents($campaign->getId(), $lead->getId(), $parellalgoals);
                    if ($achievedgoals == 0) {
                        $this->getEventRepository()->unScheduleAllEvents($campaign->getId(), $lead->getId());
                        $log=$this->getLogEntity($eventid, $campaign, $lead);
                        $log->setTriggerDate(new \DateTime());
                        $log->setSystemTriggered(true);
                        $this->getCampaignLeadEventLogRepository()->saveEntity($log);
                        $achieved=true;
                    }
                }
            }
        }

        return $achieved;
    }

    /**
     * Saves a campaign lead, logs the error if saving fails.
     *
     * @param CampaignLead $campaignLead
     *
     * @return bool
     */
    public function saveCampaignLead(CampaignLead $campaignLead)
    {
        try {
            $this->getCampaignLeadRepository()->saveEntity($campaignLead);

            return true;
        } catch (\Exception $exception) {
            $this->logger->log('error', $exception->getMessage());

            return false;
        }
    }

    /**
     * Remove lead from the campaign.
     *
     * @param Campaign $campaign
     * @param          $lead
     * @param bool     $manuallyRemoved
     */
    public function removeLead(Campaign $campaign, $lead, $manuallyRemoved = true)
    {
        $this->removeLeads($campaign, [$lead], $manuallyRemoved);

        unset($campaign, $lead);
    }

    /**
     * Remove lead(s) from the campaign.
     *
     * @param Campaign   $campaign
     * @param array      $leads
     * @param bool|false $manuallyRemoved
     * @param bool|false $batchProcess
     * @param bool|false $skipFindOne
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function removeLeads(Campaign $campaign, array $leads, $manuallyRemoved = false, $batchProcess = false, $skipFindOne = false)
    {
        foreach ($leads as $lead) {
            $dispatchEvent = false;
            if ($lead instanceof Lead) {
                $leadId = $lead->getId();
            } else {
                $leadId = (is_array($lead) && isset($lead['id'])) ? $lead['id'] : $lead;
                $lead   = $this->em->getReference('MauticLeadBundle:Lead', $leadId);
            }

            $this->removedLeads[$campaign->getId()][$leadId] = $leadId;
            $campaignLead                                    = (!$skipFindOne) ?
                $this->getCampaignLeadRepository()->findOneBy([
                    'lead'     => $lead,
                    'campaign' => $campaign,
                ]) :
                $this->em->getReference('MauticCampaignBundle:Lead', [
                    'lead'     => $leadId,
                    'campaign' => $campaign->getId(),
                ]);

            if ($campaignLead == null) {
                if ($batchProcess) {
                    $this->em->detach($lead);
                    unset($lead);
                }

                continue;
            }

            if (($manuallyRemoved && $campaignLead->wasManuallyAdded()) || (!$manuallyRemoved && !$campaignLead->wasManuallyAdded())) {
                //lead was manually added and now manually removed or was not manually added and now being removed

                // Manually added and manually removed so chuck it
                $dispatchEvent = true;

                $this->getEventRepository()->deleteEntity($campaignLead);
            } elseif ($manuallyRemoved) {
                $dispatchEvent = true;

                $campaignLead->setManuallyRemoved(true);
                $this->getEventRepository()->saveEntity($campaignLead);
            }

            if ($dispatchEvent) {
                //remove scheduled events if the lead was removed
                $this->removeScheduledEvents($campaign, $lead);

                if ($this->dispatcher->hasListeners(CampaignEvents::CAMPAIGN_ON_LEADCHANGE)) {
                    $event = new Events\CampaignLeadChangeEvent($campaign, $lead, 'removed');
                    $this->dispatcher->dispatch(CampaignEvents::CAMPAIGN_ON_LEADCHANGE, $event);

                    unset($event);
                }
            }

            // Detach CampaignLead to save memory
            $this->em->detach($campaignLead);

            if ($batchProcess) {
                $this->em->detach($lead);
            }

            unset($campaignLead, $lead);
        }
    }

    /**
     * @return array
     */
    public function getRemovedLeads()
    {
        return  $this->removedLeads;
    }

    /**
     * Get details of leads in a campaign.
     *
     * @param      $campaign
     * @param null $leads
     *
     * @return mixed
     */
    public function getLeadDetails($campaign, $leads = null)
    {
        $campaignId = ($campaign instanceof Campaign) ? $campaign->getId() : $campaign;

        if ($leads instanceof PersistentCollection) {
            $leads = array_keys($leads->toArray());
        }

        return $this->em->getRepository('MauticCampaignBundle:Lead')->getLeadDetails($campaignId, $leads);
    }

    /**
     * @param Campaign        $campaign
     * @param int             $limit
     * @param bool            $maxLeads
     * @param OutputInterface $output
     *
     * @return int
     */
    public function rebuildCampaignLeads(Campaign $campaign, $limit = 1000, $maxLeads = false, OutputInterface $output = null)
    {
        defined('MAUTIC_REBUILDING_CAMPAIGNS') or define('MAUTIC_REBUILDING_CAMPAIGNS', 1);

        $repo = $this->getRepository();

        // Get a list of lead lists this campaign is associated with
        $listevents = $repo->getCampaignListIds($campaign->getId());
        $totalleadsProcessed=0;
        foreach($listevents as $eventid => $lists) {
            $batchLimiters = [
                'dateTime' => (new DateTimeHelper())->toUtcString(),
            ];

            if (count($lists)) {
                // Get a count of new leads
                $newLeadsCount = $repo->getCampaignLeadsFromLists(
                    $campaign->getId(),
                    $lists,
                    [
                        'countOnly' => true,
                        'batchLimiters' => $batchLimiters,
                    ]
                );

                // Ensure the same list is used each batch
                $batchLimiters['maxId'] = (int)$newLeadsCount['maxId'];

                // Number of total leads to process
                $leadCount = (int)$newLeadsCount['count'];
            } else {
                // No lists to base campaign membership off of so ignore
                $leadCount = 0;
            }

            if ($output) {
                $output->writeln($this->translator->trans('mautic.campaign.rebuild.to_be_added', ['%leads%' => $leadCount, '%batch%' => $limit,'%eventid%' => $eventid]));
            }

            // Handle by batches
            $start = $leadsProcessed = 0;

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
                    // Get a List of new leads
                    $newLeadList = $repo->getCampaignLeadsFromLists(
                        $campaign->getId(),
                        $lists,
                        [
                            'limit' => $limit,
                            'batchLimiters' => $batchLimiters,
                        ]
                    );

                    $start += $limit;

                    $processedLeads = [];
                    foreach ($newLeadList as $l) {
                        $this->addLeads($campaign, [$l], false, true, -1);
                        $this->putCampaignEventLog($eventid, $campaign, $l);
                        $processedLeads[] = $l;
                        ++$leadsProcessed;
                        if ($output && isset($progress) && $leadsProcessed < $maxCount) {
                            $progress->setProgress($leadsProcessed);
                        }

                        unset($l);

                        if ($maxLeads && $leadsProcessed >= $maxLeads) {
                            break;
                        }
                    }
                    // Dispatch batch event
                    if (count($processedLeads) && $this->dispatcher->hasListeners(CampaignEvents::LEAD_CAMPAIGN_BATCH_CHANGE)) {
                        $this->dispatcher->dispatch(
                            CampaignEvents::LEAD_CAMPAIGN_BATCH_CHANGE,
                            new Events\CampaignLeadChangeEvent($campaign, $processedLeads, 'added')
                        );
                    }

                    unset($newLeadList);

                    // Free some memory
                    gc_collect_cycles();

                    if ($maxLeads && $leadsProcessed >= $maxLeads) {
                        // done for this round, bye bye
                        if (isset($progress)) {
                            $progress->finish();
                        }

                        return $leadsProcessed;
                    }

                }

                if ($output && isset($progress)) {
                    $progress->finish();
                    $output->writeln('');

                }
            }
            $totalleadsProcessed += $leadsProcessed;
        }

        return $totalleadsProcessed;
            // Get a count of leads to be removed
           /* $removeLeadCount = $repo->getCampaignOrphanLeads(
                $campaign->getId(),
                $lists,
                [
                    'countOnly'     => true,
                    'batchLimiters' => $batchLimiters,
                ]
            );

            // Restart batching
            $start                  = $lastRoundPercentage                  = 0;
            $leadCount              = $removeLeadCount['count'];
            $batchLimiters['maxId'] = $removeLeadCount['maxId'];

            if ($output) {
                $output->writeln($this->translator->trans('le.lead.list.rebuild.to_be_removed', ['%leads%' => $leadCount, '%batch%' => $limit]));
            }

            if ($leadCount) {
                $maxCount = ($maxLeads) ? $maxLeads : $leadCount;

                if ($output) {
                    $progress = ProgressBarHelper::init($output, $maxCount);
                    $progress->start();
                }

                // Remove leads
                while ($start < $leadCount) {
                    // Keep CPU down for large lists; sleep per $limit batch
                    $this->batchSleep();

                    $removeLeadList = $repo->getCampaignOrphanLeads(
                        $campaign->getId(),
                        $lists,
                        [
                            'limit'         => $limit,
                            'batchLimiters' => $batchLimiters,
                        ]
                    );

                    $processedLeads = [];
                    foreach ($removeLeadList as $l) {
                        $this->removeLeads($campaign, [$l], false, true, true);
                        $processedLeads[] = $l;
                        ++$leadsProcessed;
                        if (isset($progress) && $leadsProcessed < $maxCount) {
                            $progress->setProgress($leadsProcessed);
                        }

                        if ($maxLeads && $leadsProcessed >= $maxLeads) {
                            break;
                        }
                    }

                    // Dispatch batch event
                    if (count($processedLeads) && $this->dispatcher->hasListeners(CampaignEvents::LEAD_CAMPAIGN_BATCH_CHANGE)) {
                        $this->dispatcher->dispatch(
                            CampaignEvents::LEAD_CAMPAIGN_BATCH_CHANGE,
                            new Events\CampaignLeadChangeEvent($campaign, $processedLeads, 'removed')
                        );
                    }

                    $start += $limit;

                    unset($removeLeadList);

                    // Free some memory
                    gc_collect_cycles();

                    if ($maxLeads && $leadsProcessed >= $maxLeads) {
                        // done for this round, bye bye

                        if (isset($progress)) {
                            $progress->finish();
                        }

                        return $leadsProcessed;
                    }
                }

                if ($output && isset($progress)) {
                    $progress->finish();
                    $output->writeln('');
                }
            }*/


    }

    /**
     * Get leads for a campaign.  If $event is passed in, only leads who have not triggered the event are returned.
     *
     * @param Campaign $campaign
     * @param array    $event
     *
     * @return mixed
     */
    public function getCampaignLeads($campaign, $event = null)
    {
        $campaignId = ($campaign instanceof Campaign) ? $campaign->getId() : $campaign;
        $eventId    = (is_array($event) && isset($event['id'])) ? $event['id'] : $event;
        $leads      = $this->em->getRepository('MauticCampaignBundle:Lead')->getLeads($campaignId, $eventId);

        return $leads;
    }

    /**
     * @param Campaign $campaign
     * @param          $lead
     */
    public function removeScheduledEvents($campaign, $lead)
    {
        $this->em->getRepository('MauticCampaignBundle:LeadEventLog')->removeScheduledEvents($campaign->getId(), $lead->getId());
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function getCampaignListIds($id)
    {
        return $this->getRepository()->getCampaignListIds((int) $id);
    }

    /**
     * Batch sleep according to settings.
     */
    protected function batchSleep()
    {
        $eventSleepTime = $this->batchCampaignSleepTime ? $this->batchCampaignSleepTime : ($this->batchSleepTime ? $this->batchSleepTime : 1);

        if (empty($eventSleepTime)) {
            return;
        }

        if ($eventSleepTime < 1) {
            usleep($eventSleepTime * 1000000);
        } else {
            sleep($eventSleepTime);
        }
    }

    /**
     * Get line chart data of leads added to campaigns.
     *
     * @param string    $unit          {@link php.net/manual/en/function.date.php#refsect1-function.date-parameters}
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param string    $dateFormat
     * @param array     $filter
     * @param bool      $canViewOthers
     *
     * @return array
     */
    public function getLeadsAddedLineChartData($unit, \DateTime $dateFrom, \DateTime $dateTo, $dateFormat = null, $filter = [], $canViewOthers = true)
    {
        $chart = new LineChart($unit, $dateFrom, $dateTo, $dateFormat);
        $query = new ChartQuery($this->em->getConnection(), $dateFrom, $dateTo);
        $q     = $query->prepareTimeDataQuery('campaign_leads', 'date_added', $filter);

        if (!$canViewOthers) {
            $q->join('t', MAUTIC_TABLE_PREFIX.'campaigns', 'c', 'c.id = c.campaign_id')
              ->andWhere('c.created_by = :userId')
              ->setParameter('userId', $this->userHelper->getUser()->getId());
        }

        $data = $query->loadAndBuildTimeData($q);
        $chart->setDataset($this->translator->trans('mautic.campaign.campaign.leads'), $data);

        return $chart->render();
    }

    /**
     * Get line chart data of hits.
     *
     * @param string    $unit       {@link php.net/manual/en/function.date.php#refsect1-function.date-parameters}
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param string    $dateFormat
     * @param array     $filter
     *
     * @return array
     */
    public function getCampaignMetricsLineChartData($unit, \DateTime $dateFrom, \DateTime $dateTo, $dateFormat = null, $filter = [])
    {
        $events = [];
        $chart  = new LineChart($unit, $dateFrom, $dateTo, $dateFormat);
        $query  = new ChartQuery($this->em->getConnection(), $dateFrom, $dateTo);

        $contacts = $query->fetchTimeData('campaign_leads', 'date_added', $filter);
        $chart->setDataset($this->translator->trans('mautic.campaign.campaign.leads'), $contacts);

        if (isset($filter['campaign_id'])) {
            $rawEvents = $this->getEventRepository()->getCampaignEvents($filter['campaign_id']);

            // Group events by type
            if ($rawEvents) {
                foreach ($rawEvents as $event) {
                    if (isset($events[$event['type']])) {
                        $events[$event['type']][] = $event['id'];
                    } else {
                        $events[$event['type']] = [$event['id']];
                    }
                }
            }

            if ($events) {
                foreach ($events as $type => $eventIds) {
                    $filter['event_id'] = $eventIds;

                    // Exclude failed events
                    $failedSq = $this->em->getConnection()->createQueryBuilder();
                    $failedSq->select('null')
                        ->from(MAUTIC_TABLE_PREFIX.'campaign_lead_event_failed_log', 'fe')
                        ->where(
                            $failedSq->expr()->eq('fe.log_id', 't.id')
                        );
                    $filter['failed_events'] = [
                        'subquery' => sprintf('NOT EXISTS (%s)', $failedSq->getSQL()),
                    ];

                    $q       = $query->prepareTimeDataQuery('campaign_lead_event_log', 'date_triggered', $filter);
                    $rawData = $q->execute()->fetchAll();

                    if (!empty($rawData)) {
                        $triggers = $query->completeTimeData($rawData);
                        $chart->setDataset($this->translator->trans('mautic.campaign.'.$type), $triggers);
                    }
                }
                unset($filter['event_id']);
            }
        }

        return $chart->render();
    }

    /**
     * @param          $hierarchy
     * @param          $events
     * @param Campaign $entity
     * @param string   $root
     * @param int      $order
     */
    protected function buildOrder($hierarchy, &$events, $entity, $root = 'null', $order = 1)
    {
        $count = count($hierarchy);
        if (1 === $count && 'null' === array_unique(array_values($hierarchy))[0]) {
            // no parents so leave order as is

            return;
        } else {
            foreach ($hierarchy as $eventId => $parent) {
                if ($parent == $root || $count === 1) {
                    $events[$eventId]->setOrder($order);
                    unset($hierarchy[$eventId]);
                    if (count($hierarchy)) {
                        $this->buildOrder($hierarchy, $events, $entity, $eventId, $order + 1);
                    }
                }
            }
        }
    }

    /**
     * @return \Mautic\UserBundle\Entity\User|null
     */
    public function getCurrentUserEntity()
    {
        return $this->userHelper->getUser();
    }

    /**
     * @return array
     */
    public function getCampaignsBlocks()
    {
        $totalCampaigns =  [$this->translator->trans('le.form.display.color.blocks.blue'), 'fa fa-sitemap', $this->translator->trans('mautic.campaign.campaigns.all'),
            $this->getRepository()->getTotalCampaignCount($viewOthers = $this->factory->get('mautic.security')->isGranted('campaign:campaigns:viewother')),
        ];
        $activeCampaigns = [$this->translator->trans('le.form.display.color.blocks.green'), 'fa fa-sitemap', $this->translator->trans('mautic.campaign.campaigns.activel'),
            $this->getRepository()->getTotalActiveCampaignCount($viewOthers = $this->factory->get('mautic.security')->isGranted('campaign:campaigns:viewother')),
        ];
        $inactiveCampaigns = [$this->translator->trans('le.form.display.color.blocks.red'), 'fa fa-sitemap', $this->translator->trans('mautic.campaign.campaigns.inactive'),
            $this->getRepository()->getTotalInactiveCampaignCount($viewOthers = $this->factory->get('mautic.security')->isGranted('campaign:campaigns:viewother')),
        ];

        $allBlockDetails[] = $totalCampaigns;
        $allBlockDetails[] = $activeCampaigns;
        $allBlockDetails[] = $inactiveCampaigns;

        return $allBlockDetails;
    }

    /**
     * @param Event|int                                $event
     * @param Campaign                                 $campaign
     * @param \Mautic\LeadBundle\Entity\Lead|null      $lead
     * @param \Mautic\CoreBundle\Entity\IpAddress|null $ipAddress
     * @param bool                                     $systemTriggered
     *
     * @return LeadEventLog
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function getLogEntity($event, $campaign, $lead = null, $ipAddress = null, $systemTriggered = false)
    {
        $log = new LeadEventLog();

        if ($ipAddress == null) {
            // Lead triggered from system IP
            $ipAddress = $this->leadModel->getIpLookUpHelper()->getIpAddress();
        }
        $log->setIpAddress($ipAddress);

        if (!$event instanceof Event) {
            $event = $this->em->getReference('MauticCampaignBundle:Event', $event);
        }
        $log->setEvent($event);

        if (!$campaign instanceof Campaign) {
            $campaign = $this->em->getReference('MauticCampaignBundle:Campaign', $campaign);
        }
        $log->setCampaign($campaign);

        if ($lead == null) {
            $lead = $this->leadModel->getCurrentLead();
        }
        $campaignlead=$this->em->getReference('MauticCampaignBundle:Lead', [
            'lead'     => $lead->getId(),
            'campaign' => $campaign->getId(),
        ]);
        $log->setRotation($campaignlead->getRotation());
        $log->setLead($lead);
        $log->setDateTriggered(new \DateTime());
        $log->setSystemTriggered($systemTriggered);

        // Save some RAM for batch processing
        unset($event, $campaign, $lead);

        return $log;
    }

    /**
     * put new campaign event log.
     *
     * @param $eventid
     * @param $campaign
     * @param $lead
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function putCampaignEventLog($eventid, $campaign, $lead)
    {
        $event            = $this->em->getReference('MauticCampaignBundle:Event', $eventid);
        if (!$lead instanceof Lead) {
            $lead   = $this->em->getReference('MauticLeadBundle:Lead', $lead);
        }
        $campaignlead     =$this->em->getReference('MauticCampaignBundle:Lead', [
            'lead'     => $lead->getId(),
            'campaign' => $campaign->getId(),
        ]);
        $leadrotation     =      $campaignlead->getRotation();
        $campaignEventLog = $this->getCampaignLeadEventLogRepository()->findOneBy([
            'lead'     => $lead,
            'campaign' => $campaign,
          //  'event'    => $event,
            'rotation'=> $leadrotation,
        ]);
        $isLogCreated=false;
        if ($campaignEventLog == null) {
            $isLogCreated=true;
        }
        if (!$isLogCreated) {
            if ($this->checkCampaignLeadExited($campaign, $lead)) {
                $leadrotation=$leadrotation + 1;
                $this->updateCampaignLeadRotation($lead, $campaign, $leadrotation);
                $isLogCreated=true;
            }
        }
        if ($isLogCreated) {
            $log=$this->getLogEntity($event, $campaign, $lead);
            $log->setDateTriggered(new \DateTime());
            $log->setSystemTriggered(true);
            $log->setRotation($leadrotation);
            $this->getCampaignLeadEventLogRepository()->saveEntity($log);
        }
    }

    /**
     * @param $campaign
     * @param $event
     *
     * @return bool
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function isWorkFlowCompleteEvent($campaign, $event)
    {
        if (!$campaign instanceof Campaign) {
            $campaign = $this->em->getReference('MauticCampaignBundle:Campaign', $campaign);
        }
        $canvassettings=json_decode($campaign->getCanvasSettings());
        $exitevents    =$this->getExitEvent($canvassettings);
        if (sizeof($exitevents) > 0 && $exitevents[0] == $event) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $lead
     * @param $campaign
     * @param $rotation
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateCampaignLeadRotation($lead, $campaign, $rotation)
    {
        $campaignlead=$this->em->getReference('MauticCampaignBundle:Lead', [
            'lead'     => $lead->getId(),
            'campaign' => $campaign->getId(),
        ]);
        $campaignlead->setRotation($rotation);
        $this->getCampaignLeadRepository()->saveEntity($campaignlead);
    }

    /**
     * @param $campaign
     * @param $lead
     *
     * @return array|bool
     */
    public function checkCampaignLeadExited($campaign, $lead)
    {
        $canvassettings=json_decode($campaign->getCanvasSettings());
        $exitevents    =$this->getExitEvent($canvassettings);
        $isLeadExited  =$this->getEventRepository()->getCompletedEvents($campaign->getId(), $lead->getId(), $exitevents);

        return $isLeadExited;
    }
}
