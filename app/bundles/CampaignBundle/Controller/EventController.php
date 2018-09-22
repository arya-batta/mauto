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

use Mautic\CampaignBundle\Entity\Event;
use Mautic\CoreBundle\Controller\FormController as CommonFormController;
use Mautic\LeadBundle\Entity\OperatorListTrait;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;

class EventController extends CommonFormController
{
    private $supportedEventTypes = ['decision', 'action', 'condition', 'source'];
    use OperatorListTrait;

    /**
     * Generates new form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        $success = 0;
        $valid   = $cancelled   = false;
        $method  = $this->request->getMethod();
        $session = $this->get('session');
        if ($method == 'POST') {
            $event                = $this->request->request->get('campaignevent');
            $type                 = $event['type'];
            $eventType            = $event['eventType'];
            $campaignId           = $event['campaignId'];
            $anchorName           = $event['anchor'];
            $event['triggerDate'] = (!empty($event['triggerDate'])) ? $this->factory->getDate($event['triggerDate'])->getDateTime() : null;
        } else {
            $type       = $this->request->query->get('type');
            $eventType  = $this->request->query->get('eventType');
            $campaignId = $this->request->query->get('campaignId');
            $anchorName = $this->request->query->get('anchor', '');
            $event      = [
                'type'            => $type,
                'eventType'       => $eventType,
                'campaignId'      => $campaignId,
                'anchor'          => $anchorName,
                'anchorEventType' => $this->request->query->get('anchorEventType', ''),
            ];
        }

        //set the eventType key for events
        if (!in_array($eventType, $this->supportedEventTypes)) {
            return $this->modalAccessDenied();
        }

        //ajax only for form fields
        if (!$type
            || !$this->request->isXmlHttpRequest()
            || !$this->get('mautic.security')->isGranted(
                [
                    'campaign:campaigns:edit',
                    'campaign:campaigns:create',
                ],
                'MATCH_ONE'
            )
        ) {
            return $this->modalAccessDenied();
        }

        $event['isnew']=true;
        //fire the builder event
        $events = $this->getModel('campaign')->getEvents();
        $form   = $this->get('form.factory')->create(
            'campaignevent',
            $event,
            [
                'action'   => $this->generateUrl('mautic_campaignevent_action', ['objectAction' => 'new']),
                'settings' => $events[$eventType][$type],
            ]
        );
        $event['settings'] = $events[$eventType][$type];

        $form->get('campaignId')->setData($campaignId);

        //Check for a submitted form and process it
        if ($method == 'POST') {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form) && $this->validateEventsSource($type, $form, $eventType) && $this->validateEventsAction($type, $form, $eventType)) {
                    $success = 1;

                    //form is valid so process the data
                    $keyId = 'new'.hash('sha1', uniqid(mt_rand()));

                    //save the properties to session
                    $modifiedEvents = $session->get('mautic.campaign.'.$campaignId.'.events.modified');
                    $formData       = $form->getData();
                    $event          = array_merge($event, $formData);
                    $event['id']    = $event['tempId']    = $keyId;
                    if ($event['eventType'] == 'condition') {
                        //set it to the event default
                        $event['name'] = $this->getConditionEventLabel($event['properties']['filters']);
                    } else {
                        $event['name'] = $this->get('translator')->trans($event['settings']['label']);
                    }
                    $modifiedEvents[$keyId] = $event;
                    $session->set('mautic.campaign.'.$campaignId.'.events.modified', $modifiedEvents);
                } else {
                    $success = 0;
                }
            }
        }

        $viewParams = ['type' => $type, 'eventType' => $eventType];
        if ($cancelled || $valid) {
            $closeModal = true;
        } else {
            $closeModal = false;
            $formThemes = ['MauticCampaignBundle:FormTheme\Event'];
            if (isset($event['settings']['formTheme'])) {
                $formThemes[] = $event['settings']['formTheme'];
            }

            $viewParams['form']             = $this->setFormTheme($form, 'MauticCampaignBundle:Campaign:index.html.php', $formThemes);
            $viewParams['eventHeader']      = $this->get('translator')->trans($event['settings']['label']);
            $viewParams['eventDescription'] = (!empty($event['settings']['description'])) ? $this->get('translator')->trans(
                $event['settings']['description']
            ) : '';
        }

        $viewParams['hideTriggerMode'] = isset($event['settings']['hideTriggerMode']) && $event['settings']['hideTriggerMode'];
        $viewParams['accessurl']       =$this->generateUrl('mautic_campaignevent_action', ['objectAction' => 'new']);
        $passthroughVars               = [
            'mauticContent'     => 'campaignEvent',
            'success'           => $success,
            'route'             => false,
            'eventType'         => $eventType,
        ];

        if (!empty($keyId)) {
            //prevent undefined errors
            $entity = new Event();
            $blank  = $entity->convertToArray();
            $event  = array_merge($blank, $event);

            $template = (empty($event['settings']['template'])) ? 'MauticCampaignBundle:Event:generic.html.php'
                : $event['settings']['template'];

            $passthroughVars['eventId']   = $keyId;
            $passthroughVars['eventHtml'] = $this->renderView(
                $template,
                [
                    'event'      => $event,
                    'id'         => $keyId,
                    'campaignId' => $campaignId,
                ]
            );
            $passthroughVars['eventType'] = $eventType;

            $translator = $this->translator;
            if ($event['triggerMode'] == 'interval') {
                $label = 'mautic.campaign.connection.trigger.interval.label';
                if ($anchorName == 'no') {
                    $label .= '_inaction';
                }
                $passthroughVars['label'] = $translator->trans(
                    $label,
                    [
                        '%number%' => $event['triggerInterval'],
                        '%unit%'   => $translator->transChoice(
                            'mautic.campaign.event.intervalunit.'.$event['triggerIntervalUnit'],
                            $event['triggerInterval']
                        ),
                    ]
                );
            } elseif ($event['triggerMode'] == 'date') {
                /** @var \Mautic\CoreBundle\Templating\Helper\DateHelper $dh */
                $dh    = $this->factory->getHelper('template.date');
                $label = 'mautic.campaign.connection.trigger.date.label';
                if ($anchorName == 'no') {
                    $label .= '_inaction';
                }
                $passthroughVars['label'] = $translator->trans(
                    $label,
                    [
                        '%full%' => $dh->toFull($event['triggerDate']),
                        '%time%' => $dh->toTime($event['triggerDate']),
                        '%date%' => $dh->toShort($event['triggerDate']),
                    ]
                );
            }
        }

        if ($closeModal) {
            //just close the modal
            $passthroughVars['closeModal'] = 1;
            $response                      = new JsonResponse($passthroughVars);

            return $response;
        } else {
            return $this->ajaxAction(
                [
                    'contentTemplate' => 'MauticCampaignBundle:Event:form.html.php',
                    'viewParameters'  => $viewParams,
                    'passthroughVars' => $passthroughVars,
                ]
            );
        }
    }

    /**
     * Generates edit form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($objectId)
    {
        $session    = $this->get('session');
        $method     = $this->request->getMethod();
        $campaignId = ($method == 'POST')
            ? $this->request->request->get('campaignevent[campaignId]', '', true)
            : $this->request->query->get(
                'campaignId'
            );
        $modifiedEvents = $session->get('mautic.campaign.'.$campaignId.'.events.modified', []);
        $success        = 0;
        $valid          = $cancelled          = false;
        $event          = (array_key_exists($objectId, $modifiedEvents)) ? $modifiedEvents[$objectId] : null;
        if ($method == 'POST') {
            $event['type']            =$this->request->request->get('campaignevent[type]', '', true);
            $event['anchor']          = $this->request->request->get('campaignevent[anchor]', '', true);
            $event['anchorEventType'] = $this->request->request->get('campaignevent[anchorEventType]', '', true);
            unset($event['properties']);
        } else {
            $type              = $this->request->query->get('type', $event['type'], true);
            $eventType         = $this->request->query->get('eventType', $event['eventType'], true);
            $event['type']     =$type;
            $event['eventType']=$eventType;
            if ($eventType != 'source' && !isset($event['anchor'])) {
                // Used to generate label
                $event['anchor'] = $event['decisionPath'];
            }

            if ($this->request->query->has('anchor')) {
                // Override the anchor
                $event['anchor'] = $this->request->get('anchor');
            }

            if ($this->request->query->has('anchorEventType')) {
                // Override the anchorEventType
                $event['anchorEventType'] = $this->request->get('anchorEventType');
            }
        }

        if ($event !== null) {
            $type      = $event['type'];
            $eventType = $event['eventType'];
            if (!in_array($eventType, $this->supportedEventTypes)) {
                return $this->modalAccessDenied();
            }

            //ajax only for form fields
            if (!$type || !$this->request->isXmlHttpRequest()
                || !$this->get('mautic.security')->isGranted(
                    [
                        'campaign:campaigns:edit',
                        'campaign:campaigns:create',
                    ],
                    'MATCH_ONE'
                )
            ) {
                return $this->modalAccessDenied();
            }
            $event['isnew']=false;
            //fire the builder event
            $events = $this->getModel('campaign')->getEvents();
            $form   = $this->get('form.factory')->create(
                'campaignevent',
                $event,
                [
                    'action'   => $this->generateUrl('mautic_campaignevent_action', ['objectAction' => 'edit', 'objectId' => $objectId]),
                    'settings' => $events[$eventType][$type],
                ]
            );
            $event['settings'] = $events[$eventType][$type];

            $form->get('campaignId')->setData($campaignId);

            //Check for a submitted form and process it
            if ($method == 'POST') {
                if (!$cancelled = $this->isFormCancelled($form)) {
                    if ($valid = $this->isFormValid($form) && $this->validateEventsSource($type, $form, $eventType) && $this->validateEventsAction($type, $form, $eventType)) {
                        $success = 1;

                        //save the properties to session
                        $modifiedEvents = $session->get('mautic.campaign.'.$campaignId.'.events.modified');
                        $formData       = $form->getData();
                        $event          = array_merge($event, $formData);
                        if ($event['eventType'] == 'condition') {
                            //set it to the event default
                            $event['name'] = $this->getConditionEventLabel($event['properties']['filters']);
                        } else {
                            $event['name'] = $this->get('translator')->trans($event['settings']['label']);
                        }
                        $modifiedEvents[$objectId] = $event;

                        $session->set('mautic.campaign.'.$campaignId.'.events.modified', $modifiedEvents);
                    } else {
                        $success = 0;
                    }
                }
            }

            $viewParams = ['type' => $type, 'eventType' => $eventType];
            if ($cancelled || $valid) {
                $closeModal = true;
            } else {
                $closeModal = false;
                $formThemes = ['MauticCampaignBundle:FormTheme\Event'];
                if (isset($event['settings']['formTheme'])) {
                    $formThemes[] = $event['settings']['formTheme'];
                }
                $viewParams['form']             = $this->setFormTheme($form, 'MauticCampaignBundle:Campaign:index.html.php', $formThemes);
                $viewParams['eventHeader']      = $this->get('translator')->trans($event['settings']['label']);
                $viewParams['eventDescription'] = (!empty($event['settings']['description'])) ? $this->get('translator')->trans(
                    $event['settings']['description']
                ) : '';
            }
            $viewParams['hideTriggerMode'] = isset($event['settings']['hideTriggerMode']) && $event['settings']['hideTriggerMode'];
            $viewParams['accessurl']       =$this->generateUrl('mautic_campaignevent_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
            $viewParams['events']          =$event;
            $passthroughVars               = [
                'mauticContent'     => 'campaignEvent',
                'success'           => $success,
                'route'             => false,
                'eventType'         => $eventType,
            ];

            if ($closeModal) {
                if ($success) {
                    //prevent undefined errors
                    $entity = new Event();
                    $blank  = $entity->convertToArray();
                    $event  = array_merge($blank, $event);

                    $template = (empty($event['settings']['template'])) ? 'MauticCampaignBundle:Event:generic.html.php'
                        : $event['settings']['template'];

                    $passthroughVars['eventId']    = $objectId;
                    $passthroughVars['updateHtml'] = $this->renderView(
                        $template,
                        [
                            'event'      => $event,
                            'id'         => $objectId,
                            'update'     => true,
                            'campaignId' => $campaignId,
                        ]
                    );
                    $passthroughVars['eventType'] = $eventType;

                    $translator = $this->translator;
                    if ($event['triggerMode'] == 'interval') {
                        $label = 'mautic.campaign.connection.trigger.interval.label';
                        if ($event['anchor'] == 'no') {
                            $label .= '_inaction';
                        }
                        $passthroughVars['label'] = $translator->trans(
                            $label,
                            [
                                '%number%' => $event['triggerInterval'],
                                '%unit%'   => $translator->transChoice(
                                    'mautic.campaign.event.intervalunit.'.$event['triggerIntervalUnit'],
                                    $event['triggerInterval']
                                ),
                            ]
                        );
                    } elseif ($event['triggerMode'] == 'date') {
                        $label = 'mautic.campaign.connection.trigger.date.label';
                        if ($event['anchor'] == 'no') {
                            $label .= '_inaction';
                        }
                        /** @var \Mautic\CoreBundle\Templating\Helper\DateHelper $dh */
                        $dh                       = $this->factory->getHelper('template.date');
                        $passthroughVars['label'] = $translator->trans(
                            $label,
                            [
                                '%full%' => $dh->toFull($event['triggerDate']),
                                '%time%' => $dh->toTime($event['triggerDate']),
                                '%date%' => $dh->toShort($event['triggerDate']),
                            ]
                        );
                    }
                }
                //just close the modal
                $passthroughVars['closeModal'] = 1;
                $response                      = new JsonResponse($passthroughVars);

                return $response;
            } else {
                return $this->ajaxAction(
                    [
                        'contentTemplate' => 'MauticCampaignBundle:Event:form.html.php',
                        'viewParameters'  => $viewParams,
                        'passthroughVars' => $passthroughVars,
                    ]
                );
            }
        } else {
            return $this->modalAccessDenied();
        }
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
        $campaignId     = $this->request->query->get('campaignId');
        $session        = $this->get('session');
        $modifiedEvents = $session->get('mautic.campaign.'.$campaignId.'.events.modified', []);
        $deletedEvents  = $session->get('mautic.campaign.'.$campaignId.'.events.deleted', []);

        //ajax only for form fields
        if (!$this->request->isXmlHttpRequest()
            || !$this->get('mautic.security')->isGranted(
                [
                    'campaign:campaigns:edit',
                    'campaign:campaigns:create',
                ],
                'MATCH_ONE'
            )
        ) {
            return $this->accessDenied();
        }

        $event = (array_key_exists($objectId, $modifiedEvents)) ? $modifiedEvents[$objectId] : null;

        if ($this->request->getMethod() == 'POST' && $event !== null) {
            $events            = $this->getModel('campaign')->getEvents();
            $event['settings'] = $events[$event['eventType']][$event['type']];

            // Add the field to the delete list
            if (!in_array($objectId, $deletedEvents)) {
                //If event is new don't add to deleted list
                if (strpos($objectId, 'new') === false) {
                    $deletedEvents[] = $objectId;
                    $session->set('mautic.campaign.'.$campaignId.'.events.deleted', $deletedEvents);
                }

                //Always remove from modified list if deleted
                if (isset($modifiedEvents[$objectId])) {
                    unset($modifiedEvents[$objectId]);
                    $session->set('mautic.campaign.'.$campaignId.'.events.modified', $modifiedEvents);
                }
            }

            $dataArray = [
                'mauticContent' => 'campaignEvent',
                'success'       => 1,
                'route'         => false,
                'eventId'       => $objectId,
                'deleted'       => 1,
                'event'         => $event,
            ];
        } else {
            $dataArray = ['success' => 0];
        }

        $response = new JsonResponse($dataArray);

        return $response;
    }

    /**
     * Undeletes the entity.
     *
     * @param   $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function undeleteAction($objectId)
    {
        $campaignId     = $this->request->query->get('campaignId');
        $session        = $this->get('session');
        $modifiedEvents = $session->get('mautic.campaign.'.$campaignId.'.events.modified', []);
        $deletedEvents  = $session->get('mautic.campaign.'.$campaignId.'.events.deleted', []);

        //ajax only for form fields
        if (!$this->request->isXmlHttpRequest()
            || !$this->get('mautic.security')->isGranted(
                [
                    'campaign:campaigns:edit',
                    'campaign:campaigns:create',
                ],
                'MATCH_ONE'
            )
        ) {
            return $this->accessDenied();
        }

        $event = (array_key_exists($objectId, $modifiedEvents)) ? $modifiedEvents[$objectId] : null;

        if ($this->request->getMethod() == 'POST' && $event !== null) {
            $events            = $this->getModel('campaign')->getEvents();
            $event['settings'] = $events[$event['eventType']][$event['type']];

            //add the field to the delete list
            if (in_array($objectId, $deletedEvents)) {
                $key = array_search($objectId, $deletedEvents);
                unset($deletedEvents[$key]);
                $session->set('mautic.campaign.'.$campaignId.'.events.deleted', $deletedEvents);
            }

            $template = (empty($event['settings']['template'])) ? 'MauticCampaignBundle:Event:generic.html.php'
                : $event['settings']['template'];

            //prevent undefined errors
            $entity = new Event();
            $blank  = $entity->convertToArray();
            $event  = array_merge($blank, $event);

            $dataArray = [
                'mauticContent' => 'campaignEvent',
                'success'       => 1,
                'route'         => false,
                'eventId'       => $objectId,
                'eventHtml'     => $this->renderView(
                    $template,
                    [
                        'event'      => $event,
                        'id'         => $objectId,
                        'campaignId' => $campaignId,
                    ]
                ),
            ];
        } else {
            $dataArray = ['success' => 0];
        }

        $response = new JsonResponse($dataArray);

        return $response;
    }

    public function getConditionEventLabel($filters)
    {
        $label='';
        $index=0;
        foreach ($filters as $key => $data) {
            $operator   = $data['operator'];
            $fieldlabel = $data['fieldlabel'];
            $value      = $data['filter'];
            $glue       = $data['glue'];
            $oplabel    = $this->getOperatorLabel($operator);
            $oplabel    = $this->get('translator')->trans($oplabel);
            if (is_array($value)) {
                $value='['.implode(',', $value).']';
            } else {
                $value="'".$value."'";
            }
            if ($index > 0) {
                if ($glue == 'or') {
                    $label .= ',';
                }
                $label .= ' '.$glue.' ';
            }
            $label .= $fieldlabel.' '.$oplabel.' '.$value;
            ++$index;
        }

        return $label;
    }

    public function validateEventsAction($type, $form, $eventType)
    {
        if ($eventType != 'action') {
            return true;
        }
        $formData = $form->getData();
        if ($eventType == 'action') {
            $isValidForm =true;
            if ($type == 'lead.changelist') {
                if (empty($formData['properties']['addToLists']) && empty($formData['properties']['removeFromLists'])) {
                    $form['properties']['addToLists']->addError(
                    new FormError($this->translator->trans('mautic.campaign.segment.add.required', [], 'validators'))
                );
                    $form['properties']['removeFromLists']->addError(
                    new FormError($this->translator->trans('mautic.campaign.segment.remove.required', [], 'validators'))
                );
                    $isValidForm =false;
                }
            } elseif ($type == 'lead.changetags') {
                if (empty($formData['properties']['add_tags']) && empty($formData['properties']['remove_tags'])) {
                    $form['properties']['add_tags']->addError(
                    new FormError($this->translator->trans('mautic.campaign.tags.add', [], 'validators'))
                );
                    $form['properties']['remove_tags']->addError(
                    new FormError($this->translator->trans('mautic.campaign.tags.remove', [], 'validators'))
                );
                    $isValidForm =false;
                }
            } elseif ($type == 'campaign.addremovelead') {
                if (empty($formData['properties']['addTo']) && empty($formData['properties']['removeFrom'])) {
                    $form['properties']['addTo']->addError(
                    new FormError($this->translator->trans('mautic.campaign.workflow.add', [], 'validators'))
                );
                    $form['properties']['removeFrom']->addError(
                    new FormError($this->translator->trans('mautic.campaign.workflow.remove', [], 'validators'))
                );
                    $isValidForm =false;
                }
            }

            return  $isValidForm;
        }
    }

    public function validateEventsSource($type, $form, $eventType)
    {
        if ($eventType != 'source') {
            return true;
        }
        $isValidForm = true;
        $formData    = $form->getData();
        if ($eventType == 'source' && $type == 'pagehit') {
            if (empty($formData['properties']['pages']) && empty($formData['properties']['url']) && empty($formData['properties']['referer'])) {
                $form['properties']['pages']->addError(
                        new FormError($this->translator->trans('mautic.core.value.required', [], 'validators'))
                    );
                $form['properties']['url']->addError(
                        new FormError($this->translator->trans('mautic.core.value.required', [], 'validators'))
                    );
                $form['properties']['referer']->addError(
                        new FormError($this->translator->trans('mautic.core.value.required', [], 'validators'))
                    );
                $isValidForm = false;
            }
        }

        return $isValidForm;
    }
}
