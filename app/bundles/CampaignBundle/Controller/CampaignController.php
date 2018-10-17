<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CampaignBundle\Controller;

use Mautic\CampaignBundle\Entity\Campaign;
use Mautic\CampaignBundle\Entity\LeadEventLogRepository;
use Mautic\CampaignBundle\Model\CampaignModel;
use Mautic\CampaignBundle\Model\EventModel;
use Mautic\CoreBundle\Controller\AbstractStandardFormController;
use Mautic\LeadBundle\Controller\EntityContactsTrait;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CampaignController extends AbstractStandardFormController
{
    use EntityContactsTrait;

    /**
     * @var array
     */
    protected $addedSources = [];

    /**
     * @var array
     */
    protected $campaignEvents = [];

    /**
     * @var array
     */
    protected $campaignSources = [];

    /**
     * @var array
     */
    protected $connections = [];

    /**
     * @var array
     */
    protected $deletedEvents = [];

    /**
     * @var array
     */
    protected $deletedSources = [];

    /**
     * @var array
     */
    protected $listFilters = [];

    /**
     * @var array
     */
    protected $modifiedEvents = [];

    /**
     * @var
     */
    protected $sessionId;

    /**
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->delegateRedirect($this->generateUrl('le_pricing_index'));
        } else {
            return $this->batchDeleteStandard();
        }
    }

    /**
     * Clone an entity.
     *
     * @param $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function cloneAction($objectId)
    {
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->delegateRedirect($this->generateUrl('le_pricing_index'));
        } else {
            return $this->cloneStandard($objectId);
        }
    }

    /**
     * @param     $objectId
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function contactsAction($objectId, $page = 1)
    {
        return $this->generateContactsGrid(
            $objectId,
            $page,
            'campaign:campaigns:view',
            'campaign',
            'campaign_leads',
            null,
            'campaign_id',
            ['manually_removed' => 0]
        );
    }

    /**
     * Deletes the entity.
     *
     * @param   $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->delegateRedirect($this->generateUrl('le_pricing_index'));
        } else {
            return $this->deleteStandard($objectId);
        }
    }

    /**
     * @param      $objectId
     * @param bool $ignorePost
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction($objectId, $ignorePost = false)
    {
        if ($this->get('mautic.helper.licenseinfo')->redirectToCardinfo()) {
            return $this->delegateRedirect($this->generateUrl('mautic_accountinfo_action', ['objectAction' => 'cardinfo']));
        } elseif ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->delegateRedirect($this->generateUrl('le_pricing_index'));
        } else {
            return $this->editStandard($objectId, $ignorePost);
        }
    }

    /**
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = null)
    {
        return $this->indexStandard($page);
    }

    /**
     * Generates new form and processes post data.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction($objectId = null)
    {
        if ($this->get('mautic.helper.licenseinfo')->redirectToCardinfo()) {
            return $this->delegateRedirect($this->generateUrl('mautic_accountinfo_action', ['objectAction' => 'cardinfo']));
        } elseif ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->delegateRedirect($this->generateUrl('le_pricing_index'));
        } else {
            return $this->newStandard($objectId);
        }
    }

    public function quickaddAction()
    {
        if ($this->get('mautic.helper.licenseinfo')->redirectToCardinfo()) {
            return $this->delegateRedirect($this->generateUrl('mautic_accountinfo_action', ['objectAction' => 'cardinfo']));
        }
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->redirectToPricing();
        }
        /** @var CampaignModel $model */
        $model     = $this->getModel('campaign');
        $campaign  = $model->getEntity();
        $action    = $this->generateUrl('mautic_campaign_action', ['objectAction' => 'quickadd']);
        $form      = $model->createForm($campaign, $this->get('form.factory'), $action, ['isShortForm' => true]);
        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $name      = $campaign->getName();
                    $catId     = '';
                    if (!empty($campaign->getCategory())) {
                        $catId     = $campaign->getCategory()->getId();
                    }
                    $newaction = $this->generateUrl('mautic_campaign_action',
                        ['objectAction'    => 'new',
                            'campaignName' => $name, 'category' => $catId,
                    ]);

                    return $this->delegateRedirect($newaction);
                }
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'   => $form->createView(),
                    'lead'   => $campaign,
                ],
                'contentTemplate' => 'MauticCampaignBundle:Campaign:quickadd.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_campaign_index',
                    'mauticContent' => 'lead',
                    'route'         => $this->generateUrl(
                        'mautic_campaign_action',
                        [
                            'objectAction' => 'new',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * View a specific campaign.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    /*public function viewAction($objectId)
    {
        return $this->viewStandard($objectId, $this->getModelName(), null, null, 'campaign');
    }*/

    /**
     * @param $campaign
     * @param $oldCampaign
     */
    protected function afterEntityClone($campaign, $oldCampaign)
    {
        $tempId   = 'mautic_'.sha1(uniqid(mt_rand(), true));
        $objectId = $oldCampaign->getId();

        // Get the events that need to be duplicated as well
        $events = $oldCampaign->getEvents()->toArray();

        $campaign->setIsPublished(false);

        // Clone the campaign's events
        foreach ($events as $event) {
            $tempEventId = $event->getId();

            $clone = clone $event;
            $clone->setCampaign($campaign);
            $clone->setTempId($tempEventId);

            // Just wipe out the parent as it'll be generated when the cloned entity is saved
            $clone->setParent(null);

            $campaign->addEvent($tempEventId, $clone);
        }

//        // Update canvas settings with new event ids
        $canvasSettings = $campaign->getCanvasSettings();

        // Simulate edit
        $campaign->setCanvasSettings($canvasSettings);
        $this->setSessionCanvasSettings($tempId, $canvasSettings);
    }

    /**
     * @param      $entity
     * @param Form $form
     * @param      $action
     * @param null $persistConnections
     */
    protected function afterEntitySave($entity, Form $form, $action, $persistConnections = null)
    {
        if ($persistConnections) {
            // Update canvas settings with new event IDs then save
            $this->connections = $this->getCampaignModel()->setCanvasSettings($entity, $this->connections);
        } else {
            // Just update and add to entity
            $this->connections = $this->getCampaignModel()->setCanvasSettings($entity, $this->connections, false, $this->modifiedEvents);
        }
    }

    /**
     * @param      $isValid
     * @param      $entity
     * @param Form $form
     * @param      $action
     * @param bool $isClone
     */
    protected function afterFormProcessed($isValid, $entity, Form $form, $action, $isClone = false)
    {
        if (!$isValid) {
            // Add the canvas settings to the entity to be able to rebuild it
            $this->afterEntitySave($entity, $form, $action, false);
        } else {
            $this->clearSessionComponents($this->sessionId);
            $this->sessionId = $entity->getId();
        }
    }

    /**
     * @param      $entity
     * @param Form $form
     * @param      $action
     * @param      $isPost
     * @param null $objectId
     * @param bool $isClone
     */
    protected function beforeFormProcessed($entity, Form $form, $action, $isPost, $objectId = null, $isClone = false)
    {
        $sessionId = $this->getCampaignSessionId($entity, $action, $objectId);
        //set added/updated events
        list($this->modifiedEvents, $this->deletedEvents, $this->campaignEvents) = $this->getSessionEvents($sessionId);

        $this->connections                                                 = $this->getSessionCanvasSettings($sessionId);

        if ($isPost) {
            //  $this->getCampaignModel()->setCanvasSettings($entity, $this->connections, false, $this->modifiedEvents);
        } else {
            if (!$isClone) {
                //clear out existing fields in case the form was refreshed, browser closed, etc
                $this->clearSessionComponents($sessionId);
                $this->modifiedEvents = $this->campaignSources = [];

                if ($entity->getId()) {
                    $this->setSessionCanvasSettings($sessionId, $entity->getCanvasSettings());
                }
            }

            $this->deletedEvents = [];

            $form->get('sessionId')->setData($sessionId);
            if ($action == 'new') {
                $this->prepareCampaignEventsForNew($entity, $sessionId);
            } else {
                $this->prepareCampaignEventsForEdit($entity, $sessionId, $isClone);
            }
        }
    }

    /**
     * @param Campaign $entity
     * @param Form     $form
     * @param          $action
     * @param null     $objectId
     * @param bool     $isClone
     *
     * @return bool
     */
    protected function beforeEntitySave($entity, Form $form, $action, $objectId = null, $isClone = false)
    {
        if ($isClone) {
            // If this is a clone, we need to save the entity first to properly build the events, sources and canvas settings
            $this->getCampaignModel()->getRepository()->saveEntity($entity);
            // Set as new so that timestamps are still hydrated
            $entity->setNew();
            $this->sessionId = $entity->getId();
        }

        // Build and set Event entities
        $this->getCampaignModel()->setEvents($entity, $this->campaignEvents, $this->connections, $this->deletedEvents);

        if ('edit' === $action && null !== $this->connections) {
            if (!empty($this->deletedEvents)) {
                /** @var EventModel $eventModel */
                $eventModel = $this->getModel('campaign.event');
                $eventModel->deleteEvents($entity->getEvents()->toArray(), $this->deletedEvents);
            }
        }

        return true;
    }

    /**
     * Clear field and events from the session.
     *
     * @param $id
     */
    protected function clearSessionComponents($id)
    {
        $session = $this->get('session');
        $session->remove('mautic.campaign.'.$id.'.events.modified');
        $session->remove('mautic.campaign.'.$id.'.events.deleted');
        $session->remove('mautic.campaign.'.$id.'.events.canvassettings');
        $session->remove('mautic.campaign.'.$id.'.leadsources.current');
        $session->remove('mautic.campaign.'.$id.'.leadsources.modified');
        $session->remove('mautic.campaign.'.$id.'.leadsources.deleted');
    }

    /**
     * @return CampaignModel
     */
    protected function getCampaignModel()
    {
        /** @var CampaignModel $model */
        $model = $this->getModel($this->getModelName());

        return $model;
    }

    /**
     * @param Campaign $campaign
     * @param          $action
     * @param null     $objectId
     *
     * @return int|null|string
     */
    protected function getCampaignSessionId(Campaign $campaign, $action, $objectId = null)
    {
        if (isset($this->sessionId)) {
            return $this->sessionId;
        }

        if ($objectId) {
            $sessionId = $objectId;
        } elseif ('new' === $action && empty($sessionId)) {
            $sessionId = 'mautic_'.sha1(uniqid(mt_rand(), true));
            if ($this->request->request->has('campaign')) {
                $sessionId = $this->request->request->get('campaign[sessionId]', $sessionId, true);
            }
        } elseif ('edit' === $action) {
            $sessionId = $campaign->getId();
        }

        $this->sessionId = $sessionId;

        return $sessionId;
    }

    /**
     * @return string
     */
    protected function getControllerBase()
    {
        return 'MauticCampaignBundle:Campaign';
    }

    /**
     * @param       $start
     * @param       $limit
     * @param       $filter
     * @param       $orderBy
     * @param       $orderByDir
     * @param array $args
     */
    protected function getIndexItems($start, $limit, $filter, $orderBy, $orderByDir, array $args = [])
    {
        $session        = $this->get('session');
        $currentFilters = $session->get('mautic.campaign.list_filters', []);
        $updatedFilters = $this->request->get('filters', false);

        $sourceLists = $this->getCampaignModel()->getSourceLists();
        $listFilters = [
            'filters' => [
                'placeholder' => $this->get('translator')->trans('mautic.category.filter.placeholder'),
                'multiple'    => true,
                'groups'      => [
                  /*  'mautic.campaign.leadsource.form' => [
                        'options' => $sourceLists['forms'],
                        'prefix'  => 'form',
                    ],
                    'mautic.campaign.leadsource.list' => [
                        'options' => $sourceLists['lists'],
                        'prefix'  => 'list',
                    ],*/
                    'mautic.campaign.leadsource.category' => [
                        'options' => $sourceLists['category'],
                        'prefix'  => 'category',
                    ],
                ],
            ],
        ];

        if ($updatedFilters) {
            // Filters have been updated

            // Parse the selected values
            $newFilters     = [];
            $updatedFilters = json_decode($updatedFilters, true);

            if ($updatedFilters) {
                foreach ($updatedFilters as $updatedFilter) {
                    list($clmn, $fltr) = explode(':', $updatedFilter);

                    $newFilters[$clmn][] = $fltr;
                }

                $currentFilters = $newFilters;
            } else {
                $currentFilters = [];
            }
        }
        $session->set('mautic.campaign.list_filters', $currentFilters);

        $joinLists = $joinForms = false;
        if (!empty($currentFilters)) {
            $listIds = $catIds = $formIds =[];
            foreach ($currentFilters as $type => $typeFilters) {
                $listFilters['filters']['groups']['mautic.campaign.leadsource.'.$type]['values'] = $typeFilters;

                foreach ($typeFilters as $fltr) {
                    switch ($type) {
                        case 'list':
                            $listIds[] = (int) $fltr;
                            break;
                        case 'category':
                            $catIds[] = (int) $fltr;
                            break;
                        case 'form':
                            $formIds[] = $fltr;
                            break;
                    }
                }
            }

            if (!empty($listIds)) {
                $joinLists         = true;
                $filter['force'][] = ['column' => 'l.id', 'expr' => 'in', 'value' => $listIds];
            }

            if (!empty($formIds)) {
                $joinForms         = true;
                $filter['force'][] = ['column' => 'f.id', 'expr' => 'in', 'value' => $formIds];
            }

            if (!empty($catIds)) {
                $filter['force'][] = ['column' => 'cat.id', 'expr' => 'in', 'value' => $catIds];
            }
        }

        // Store for customizeViewArguments
        $this->listFilters = $listFilters;
        if (!empty($args)) {
            $args = array_merge(
                [
                    'joinLists' => $joinLists,
                    'joinForms' => $joinForms,
                ],
                $args
            );
        } else {
            $args = [
                'joinLists' => $joinLists,
                'joinForms' => $joinForms,
            ];
        }

        return parent::getIndexItems(
            $start,
            $limit,
            $filter,
            $orderBy,
            $orderByDir,
            $args
        );
    }

    /**
     * @return string
     */
    protected function getModelName()
    {
        return 'campaign';
    }

    /**
     * @param array $args
     * @param       $action
     *
     * @return array
     */
    protected function getPostActionRedirectArguments(array $args, $action)
    {
        switch ($action) {
            case 'new':
            case 'edit':
                if (!empty($args['entity'])) {
                    $sessionId = $this->getCampaignSessionId($args['entity'], $action);
                    $this->clearSessionComponents($sessionId);
                }
                break;
        }

        return $args;
    }

    /**
     * Get events from session.
     *
     * @param $id
     *
     * @return array
     */
    protected function getSessionEvents($id)
    {
        $session = $this->get('session');

        $modifiedEvents = $session->get('mautic.campaign.'.$id.'.events.modified', []);
        $deletedEvents  = $session->get('mautic.campaign.'.$id.'.events.deleted', []);

        $events = array_diff_key($modifiedEvents, array_flip($deletedEvents));

        return [$modifiedEvents, $deletedEvents, $events];
    }

    /**
     * Get events from session.
     *
     * @param $id
     * @param $isClone
     *
     * @return array
     */
    protected function getSessionSources($id, $isClone = false)
    {
        $session = $this->get('session');

        $campaignSources = $session->get('mautic.campaign.'.$id.'.leadsources.current', []);
        $modifiedSources = $session->get('mautic.campaign.'.$id.'.leadsources.modified', []);

        if ($campaignSources === $modifiedSources) {
            if ($isClone) {
                // Clone hasn't saved the sources yet so return the current list as added
                return [$campaignSources, [], $campaignSources];
            } else {
                return [[], [], $campaignSources];
            }
        }

        // Deleted sources
        $deletedSources = [];
        foreach ($campaignSources as $type => $sources) {
            if (isset($modifiedSources[$type])) {
                $deletedSources[$type] = array_diff_key($sources, $modifiedSources[$type]);
            } else {
                $deletedSources[$type] = $sources;
            }
        }

        // Added sources
        $addedSources = [];
        foreach ($modifiedSources as $type => $sources) {
            if (isset($campaignSources[$type])) {
                $addedSources[$type] = array_diff_key($sources, $campaignSources[$type]);
            } else {
                $addedSources[$type] = $sources;
            }
        }

        return [$addedSources, $deletedSources, $modifiedSources];
    }

    /**
     * @param array $args
     * @param       $action
     *
     * @return array
     */
    protected function getViewArguments(array $args, $action)
    {
        switch ($action) {
            case 'index':
                $args['viewParameters']['customAction'] = 'quickadd';
                $customAttr                             = [
                    'data-toggle' => 'ajaxmodal',
                    'data-target' => '#MauticSharedModal',
                    'data-header' => $this->translator->trans('le.campaign.new.add'),
                ];
                $args['viewParameters']['campaignBlocks'] =  $this->getModel('campaign')->getCampaignsBlocks();
                $args['viewParameters']['customAttr']     = $customAttr;
                $args['viewParameters']['filters']        = $this->listFilters;
                break;
            case 'view':
                /** @var Campaign $entity */
                $entity   = $args['entity'];
                $objectId = $args['objectId'];
                // Init the date range filter form
                $dateRangeValues = $this->request->get('daterange', []);
                $action          = $this->generateUrl('mautic_campaign_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
                $dateRangeForm   = $this->get('form.factory')->create('daterange', $dateRangeValues, ['action' => $action]);

                $events          = $this->getCampaignModel()->getEventRepository()->getCampaignEvents($entity->getId());

                $stats = $this->getCampaignModel()->getCampaignMetricsLineChartData(
                    null,
                    new \DateTime($dateRangeForm->get('date_from')->getData()),
                    new \DateTime($dateRangeForm->get('date_to')->getData()),
                    null,
                    ['campaign_id' => $objectId]
                );

                $session = $this->get('session');

                $campaignSources = $this->getCampaignModel()->getSourceLists();

                $this->prepareCampaignSourcesForEdit($objectId, $campaignSources, true);
                $this->prepareCampaignEventsForEdit($entity, $objectId, true);

                $args['viewParameters'] = array_merge(
                    $args['viewParameters'],
                    [
                        'campaign'            => $entity,
                        'stats'               => $stats,
                        'statistics'          => $this->getSortedEvents($args),
                        'eventSettings'       => $this->getCampaignModel()->getEvents(),
                        'sources'             => $this->getCampaignModel()->getLeadSources($entity),
                        'dateRangeForm'       => $dateRangeForm->createView(),
                        'campaignSources'     => $this->campaignSources,
                        'campaignEvents'      => $events,
                        'campaignLeads'       => $this->forward(
                            'MauticCampaignBundle:Campaign:contacts',
                            [
                                'objectId'   => $entity->getId(),
                                'page'       => $this->get('session')->get('mautic.campaign.contact.page', 1),
                                'ignoreAjax' => true,
                            ]
                        )->getContent(),
                    ]
                );
                break;

            case 'new':
            case 'edit':
                $statistics                 = $this->getSortedEvents($args);
                $args['viewParameters']     = array_merge(
                    $args['viewParameters'],
                    [
                        'eventSettings'       => $this->getCampaignModel()->getEvents(),
                        'campaignEvents'      => $this->campaignEvents,
                        'campaignSources'     => $this->campaignSources,
                        'deletedEvents'       => $this->deletedEvents,
                        'statistics'          => $statistics,
                    ]
                );
                break;
        }

        return $args;
    }

    public function getSortedEvents($args)
    {
        $entity   = $args['entity'];
        /** @var LeadEventLogRepository $eventLogRepo */
        $eventLogRepo      = $this->getDoctrine()->getManager()->getRepository('MauticCampaignBundle:LeadEventLog');
        $events            = $this->getCampaignModel()->getEventRepository()->getCampaignEvents($entity->getId(), true);
        $leadCount         = $this->getCampaignModel()->getRepository()->getCampaignLeadCount($entity->getId());
        $campaignLogCounts = $eventLogRepo->getCampaignLogCounts($entity->getId());
        $sortedEvents      = [];

        foreach ($events as $event) {
            $data             =[];
            $data['current']  =0;
            $data['done']     =0;
            $data['total']    =0;
            $data['percent']  =0;
            $data['leadcount']=$leadCount;

            if (isset($campaignLogCounts[$event['id']])) {
                $data['total'] = array_sum($campaignLogCounts[$event['id']]);
                if ($leadCount) {
                    $data['percent']   = round(($data['total'] / $leadCount) * 100, 1);
                    $data['current']   = $campaignLogCounts[$event['id']][1];
                    $data['done']      = $campaignLogCounts[$event['id']][0];
                }
            }
            $sortedEvents[$event['id']]= $data;
        }

        return $sortedEvents;
    }

    /**
     * @param      $entity
     * @param      $objectId
     * @param bool $isClone
     *
     * @return array
     */
    protected function prepareCampaignEventsForEdit($entity, $objectId, $isClone = false)
    {
        //load existing events into session
        $campaignEvents = [];

        $existingEvents = $entity->getEvents()->toArray();
        $translator     = $this->get('translator');
        $dateHelper     = $this->get('mautic.helper.template.date');
        foreach ($existingEvents as $e) {
            $event = $e->convertToArray();
            if ($isClone) {
                $id          = $e->getTempId();
                $event['id'] = $id;
            } else {
                $id =$e->getId();
            }
            unset($event['campaign']);
            unset($event['children']);
            unset($event['parent']);
            unset($event['log']);

            $label = false;
            switch ($event['triggerMode']) {
                case 'interval':
                    $label = $translator->trans(
                        'mautic.campaign.connection.trigger.interval.label'.($event['decisionPath'] == 'no' ? '_inaction' : ''),
                        [
                            '%number%' => $event['triggerInterval'],
                            '%unit%'   => $translator->transChoice(
                                'mautic.campaign.event.intervalunit.'.$event['triggerIntervalUnit'],
                                $event['triggerInterval']
                            ),
                        ]
                    );
                    break;
                case 'date':
                    $label = $translator->trans(
                        'mautic.campaign.connection.trigger.date.label'.($event['decisionPath'] == 'no' ? '_inaction' : ''),
                        [
                            '%full%' => $dateHelper->toFull($event['triggerDate']),
                            '%time%' => $dateHelper->toTime($event['triggerDate']),
                            '%date%' => $dateHelper->toShort($event['triggerDate']),
                        ]
                    );
                    break;
            }
            if ($label) {
                $event['label'] = $label;
            }

            $campaignEvents[$id] = $event;
        }

        $this->modifiedEvents = $this->campaignEvents = $campaignEvents;
        $this->get('session')->set('mautic.campaign.'.$objectId.'.events.modified', $campaignEvents);
    }

    /**
     * @param   $objectId
     * @param   $campaignSources
     */
    protected function prepareCampaignSourcesForEdit($objectId, $campaignSources, $isPost = false)
    {
        $this->campaignSources = [];
        if (is_array($campaignSources)) {
            foreach ($campaignSources as $type => $sources) {
                if (!empty($sources)) {
                    $sourceList                   = $this->getModel('campaign')->getSourceLists($type);
                    $this->campaignSources[$type] = [
                        'sourceType' => $type,
                        'campaignId' => $objectId,
                        'names'      => implode(', ', array_intersect_key($sourceList, $sources)),
                    ];
                }
            }
        }

        if (!$isPost) {
            $session = $this->get('session');
            $session->set('mautic.campaign.'.$objectId.'.leadsources.current', $campaignSources);
            $session->set('mautic.campaign.'.$objectId.'.leadsources.modified', $campaignSources);
        }
    }

    /**
     * @param $sessionId
     * @param $canvasSettings
     */
    protected function setSessionCanvasSettings($sessionId, $canvasSettings)
    {
        $this->get('session')->set('mautic.campaign.'.$sessionId.'.events.canvassettings', $canvasSettings);
    }

    /**
     * @param $sessionId
     *
     * @return mixed
     */
    protected function getSessionCanvasSettings($sessionId)
    {
        return $this->get('session')->get('mautic.campaign.'.$sessionId.'.events.canvassettings');
    }

    /**
     * @param   $events
     */
    protected function prepareCampaignEventsForNew($entity, $objectId)
    {
        $events              =$this->getModel('campaign')->getEvents();
        $tempsourceid        =hash('sha1', uniqid(mt_rand()));
        $defaultdrigger      = [
            'id'              => $tempsourceid,
            'tempId'          => $tempsourceid,
            'type'            => 'campaign.defaultsource',
            'eventType'       => 'source',
            'campaignId'      => $objectId,
            'anchor'          => '',
            'anchorEventType' => '',
            'settings'        => $events['source']['campaign.defaultsource'],
            'name'            => $this->get('translator')->trans($events['source']['campaign.defaultsource']['label']),
        ];
        $campaignEvents[$tempsourceid]=$defaultdrigger;
        $tempactionid                 =hash('sha1', uniqid(mt_rand()));
        $defaultaction                = [
            'id'              => $tempactionid,
            'tempId'          => $tempactionid,
            'type'            => 'campaign.defaultexit',
            'eventType'       => 'action',
            'campaignId'      => $objectId,
            'anchor'          => '',
            'anchorEventType' => '',
            'settings'        => $events['action']['campaign.defaultexit'],
            'name'            => $this->get('translator')->trans($events['action']['campaign.defaultexit']['label']),
        ];
        $campaignEvents[$tempactionid]=$defaultaction;
        $this->modifiedEvents         = $this->campaignEvents         = $campaignEvents;
        $this->get('session')->set('mautic.campaign.'.$objectId.'.events.modified', $campaignEvents);
        $defaultcanvassettings            =[];
        $defaultcanvassettings['id']      =hash('sha1', uniqid(mt_rand()));
        $defaultcanvassettings['type']    ='path';
        $defaulttriggers                  =[];
        $defaulttriggers['id']            =$tempsourceid;
        $defaulttriggers['type']          ='trigger';
        $defaulttriggers['category']      ='source';
        $defaulttriggers['subcategory']   ='campaign.defaultsource';
        $defaulttriggers['entry_point']   =true;
        $defaulttriggers['view']          =['label'=> 'Define your trigger...', 'incomplete'=>true];
        $defaultaction                    =[];
        $defaultaction['id']              =$tempactionid;
        $defaultaction['type']            ='exit';
        $defaultaction['category']        ='action';
        $defaultaction['subcategory']     ='campaign.defaultexit';
        $defaultsteps                     =[$defaultaction];
        $defaultcanvassettings['triggers']=[$defaulttriggers];
        $defaultcanvassettings['steps']   =$defaultsteps;
        $entity->setCanvasSettings(json_encode($defaultcanvassettings));
    }
}
