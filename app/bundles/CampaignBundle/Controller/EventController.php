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
        $method  = $this->request->getMethod();
        $session = $this->get('session');
        if ($method == 'POST') {
            $type       = $this->request->get('type');
            $eventType  = $this->request->get('eventType');
            $campaignId = $this->request->get('campaignId');
            $keyId      =$this->request->get('keyId');
            $wfnodetype =$this->request->get('wfnodetype');
            //fire the builder event
            $events     = $this->getModel('campaign')->getEvents();
            $event      = [
                'id'              => $keyId,
                'tempId'          => $keyId,
                'type'            => $type,
                'eventType'       => $eventType,
                'campaignId'      => $campaignId,
                'anchor'          => '',
                'anchorEventType' => '',
            ];
            if ($wfnodetype == 'interrupt') {
                $event['triggerMode']='interrupt';
            }
            $event['settings']      = $events[$eventType][$type];
            $event['name']          = $this->get('translator')->trans($event['settings']['label']);
            $modifiedEvents         = $session->get('mautic.campaign.'.$campaignId.'.events.modified');
            $modifiedEvents[$keyId] = $event;
            $session->set('mautic.campaign.'.$campaignId.'.events.modified', $modifiedEvents);
            $passthroughVars               = [
                'leContent'         => 'campaignEvent',
                'success'           => 0,
                'route'             => false,
                'eventType'         => $eventType,
            ];
            $response                      = new JsonResponse($passthroughVars);

            return $response;
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
                    'action'   => $this->generateUrl('le_campaignevent_action', ['objectAction' => 'edit', 'objectId' => $objectId]),
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
                        unset($formData['settings']);
                        $event          = array_merge($event, $formData);
                        if ($event['eventType'] == 'condition') {
                            //set it to the event default
                            $event['name'] = $this->getConditionEventLabel($form, $event['properties']['filters']);
                        } elseif ($type == 'campaign.defaultdelay') {
                            $event['name']=$this->getDelayEventLabel($event);
                        } else {
                            $event['name'] = $this->getEventLabelName($event, $form);
                        }
                        $modifiedEvents[$objectId] = $event;
                        $session->set('mautic.campaign.'.$campaignId.'.events.modified', $modifiedEvents);
                    } else {
                        $success = 0;
                    }
                }
            }

            $viewParams = ['type' => $type, 'eventType' => $eventType, 'cud' => $form];
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
            $viewParams['accessurl']       =$this->generateUrl('le_campaignevent_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
            $viewParams['events']          =$event;
            $passthroughVars               = [
                'leContent'         => 'campaignEvent',
                'success'           => $success,
                'route'             => false,
                'eventType'         => $eventType,
            ];
            if ($closeModal) {
                if ($success) {
                    $passthroughVars['eventId']     = $objectId;
                    $passthroughVars['eventType']   = $eventType;
                    $passthroughVars['eventName']   = $event['name'];
                    $passthroughVars['type']        = $type;
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
                if (is_numeric($objectId)) {//strpos($objectId, 'new') === false
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
                'leContent'     => 'campaignEvent',
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
                'leContent'     => 'campaignEvent',
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

    public function getEventLabelName($event, $form)
    {
        $formView = $form->createView();
        $label    =$this->get('translator')->trans($event['settings']['label']);
        if ($event['type'] == 'pagehit') {
            //$choices=$formView->children['properties']->children['pages']->vars['choices'];
            //$pages  =$event['properties']['pages'];
           // $label  =$this->getFormattedEventLabel($label, $pages, $choices['en']);
        } elseif ($event['type'] == 'openEmail' || $event['type'] == 'clickEmail') {
            if ($event['properties']['campaigntype'] == 'drip') {
                $choices = $formView->children['properties']->children['driplist']->vars['choices'];
                $emails  = $event['properties']['driplist'];
                $label   = $this->getFormattedEventLabel($label, $event['type'] == 'email.send' ? [$emails] : $emails, $choices);
            } else {
                $choices = $formView->children['properties']->children['emails']->vars['choices'];
                $emails  = $event['properties']['emails'];
                $label   = $this->getFormattedEventLabel($label, $event['type'] == 'email.send' ? [$emails] : $emails, $choices['en']);
            }
        } elseif ($event['type'] == 'email.send') {
            $choices=$formView->children['properties']->children['email']->vars['choices'];
            $emails =$event['properties']['email'];
            $label  =$this->getFormattedEventLabel($label, [$emails], $choices['en']);
        } elseif ($event['type'] == 'leadtags') {
            $choices=$formView->children['properties']->children['tags']->vars['choices'];
            $tags   =$event['properties']['tags'];
            $label  =$this->getFormattedEventLabel($label, $tags, $choices);
        } elseif ($event['type'] == 'assertDownload') {
            $choices=$formView->children['properties']->children['assets']->vars['choices'];
            $assets =$event['properties']['assets'];
            $label  =$this->getFormattedEventLabel($label, $assets, $choices['en']);
        } elseif ($event['type'] == 'forms') {
            $choices=$formView->children['properties']->children['forms']->vars['choices'];
            $forms  =$event['properties']['forms'];
            $label  =$this->getFormattedEventLabel($label, $forms, $choices);
        } elseif ($event['type'] == 'lists') {
            $choices=$formView->children['properties']->children['lists']->vars['choices'];
            $lists  =$event['properties']['lists'];
            $label  =$this->getFormattedEventLabel($label, $lists, $choices);
        } elseif ($event['type'] == 'listoptin') {
            $choices=$formView->children['properties']->children['listoptin']->vars['choices'];
            $lists  =$event['properties']['listoptin'];
            $label  =$this->getFormattedEventLabel($label, $lists, $choices);
        } elseif ($event['type'] == 'lead.changeowner') {
            $choices=$formView->children['properties']->children['owner']->vars['choices'];
            $owner  =$event['properties']['owner'];
            $label  =$this->getFormattedEventLabel($label, [$owner], $choices);
        } elseif ($event['type'] == 'lead.scorechange') {
            $choices=$formView->children['properties']->children['score']->vars['choices'];
            $score  =$event['properties']['score'];
            $label  =$this->getFormattedEventLabel($label, [$score], $choices);
        } elseif ($event['type'] == 'sms.send_text_sms') {
            $choices=$formView->children['properties']->children['sms']->vars['choices'];
            $sms    =$event['properties']['sms'];
            $label  =$this->getFormattedEventLabel($label, [$sms], $choices['en']);
        } elseif ($event['type'] == 'lead.changelist' || $event['type'] == 'lead.changelistoptin') {
            $addToListschoices     =$formView->children['properties']->children['addToLists']->vars['choices'];
            $addToLists            =$event['properties']['addToLists'];
            $removeFromListschoices=$formView->children['properties']->children['removeFromLists']->vars['choices'];
            $removeFromLists       =$event['properties']['removeFromLists'];
            $label                 =$this->getLabelFromMultiChoices($label, $addToListschoices, $removeFromListschoices, $addToLists, $removeFromLists);
        } /*elseif ($event['type'] == 'lead.changelistoptin') {
            $addToListschoices     =$formView->children['properties']->children['addToLists']->vars['choices'];
            $addToLists            =$event['properties']['addToLists'];
            $removeFromListschoices=$formView->children['properties']->children['removeFromLists']->vars['choices'];
            $removeFromLists       =$event['properties']['removeFromLists'];
            $label                 =$this->getLabelFromMultiChoices($label, $addToListschoices, $removeFromListschoices, $addToLists, $removeFromLists);
        } */elseif ($event['type'] == 'lead.changetags') {
            $addTagschoices   =$formView->children['properties']->children['add_tags']->vars['choices'];
            $addToTags        =$event['properties']['add_tags'];
            $removeTagschoices=$formView->children['properties']->children['remove_tags']->vars['choices'];
            $removeToTags     =$event['properties']['remove_tags'];
            $label            =$this->getLabelFromMultiChoices($label, $addTagschoices, $removeTagschoices, $addToTags, $removeToTags);
        } elseif ($event['type'] == 'lead.changepoints') {
            $points=$event['properties']['points'];
            $label =$label.' ['.$points.']';
        } elseif ($event['type'] == 'email.send.to.user' || $event['type'] == 'sms.send_text_sms.to.user') {
            $choices=$formView->children['properties']->children['user_id']->vars['choices'];
            $user   =$event['properties']['user_id'];
            if (sizeof($user) > 0) {
                $label = $this->getFormattedEventLabel($label, $user, $choices);
            } else {
                if ($event['properties']['to_owner'] == 1) {
                    $label =$label.'[Lead Owner]';
                } elseif ($event['properties']['to'] != null) {
                    $label =$label.' ['.$event['properties']['to'].']';
                }
            }
        } elseif ($event['type'] == 'email.send.to.dripcampaign' || $event['type'] == 'remove.dripcampaign' || $event['type'] == 'restart.dripcampaign' || $event['type'] == 'dripcampaign_completed') {
            $choices     =$formView->children['properties']->children['dripemail']->vars['choices'];
            $dripemail   =$event['properties']['dripemail'];
            $label       =$this->getFormattedEventLabel($label, [$dripemail], $choices);
        } elseif ($event['type'] == 'move.dripcampaign') {
            $addDripchoices     =$formView->children['properties']->children['movedripfrom']->vars['choices'];
            $addDrip            =$event['properties']['movedripfrom'];
            $removeDripchoices  =$formView->children['properties']->children['movedripto']->vars['choices'];
            $removeDrip         =$event['properties']['movedripto'];
            $label              =$this->getLabelFromMultiChoices($label, $addDripchoices, $removeDripchoices, [$addDrip], [$removeDrip]);
        } elseif ($event['type'] == 'leadtags.remove') {
            $tags  = $event['properties']['tags'];
            $label = $label.' '.str_replace(',', ', ', json_encode($tags));
            $label =  str_replace('"', '', $label);
        } elseif ($event['type'] == 'instapage') {
            $name    = $event['properties']['page_name'];
            $tmpname = 'any';
            if ($name != '') {
                $tmpname = '"'.$name.'"';
            }
            $label = $this->translator->trans('le.integration.source.instapage.event.label', ['%NAME%' => $tmpname]);
        } elseif ($event['type'] == 'unbounce') {
            $name    = $event['properties']['pagename'];
            $tmpname = 'any';
            if ($name != '') {
                $tmpname = '"'.$name.'"';
            }
            $label = $this->translator->trans('le.integration.source.unbounce.event.label', ['%NAME%' => $tmpname]);
        } elseif ($event['type'] == 'invitee.created') {
            $name    = $event['properties']['event_name'];
            $tmpname = 'any';
            if ($name != '') {
                $tmpname = '"'.$name.'"';
            }
            $label = $this->translator->trans('le.integration.source.calendly.invitee.created.event.label', ['%NAME%' => $tmpname]);
        } elseif ($event['type'] == 'invitee.canceled') {
            $name    = $event['properties']['event_name'];
            $tmpname = 'any';
            if ($name != '') {
                $tmpname = '"'.$name.'"';
            }
            $label = $this->translator->trans('le.integration.source.calendly.invitee.canceled.event.label', ['%NAME%' => $tmpname]);
        } elseif ($event['type'] == 'fbLeadAds') {
            $pageid      = $event['properties']['leadgenform'];
            $choices     = $formView->children['properties']->children['leadgenform']->vars['choices'];
            $formatlabel = $this->getChoiceLabel($choices, $pageid);
            $tmpname     = 'a';
            if ($pageid != '' && $pageid != null && $pageid != '-1') {
                $tmpname = '"'.$formatlabel.'"';
            }
            $label = $this->translator->trans('le.integration.source.facebook.ads.event.label', ['%NAME%' => $tmpname]);
        }

        return $label;
    }

    public function getChoiceLabel($choices, $value)
    {
        // if(is_numeric($value)){
        foreach ($choices as $key => $choice) {
            if ($choice->value == $value) {
                $value=$this->get('translator')->trans($choice->label);
                break;
            }
        }

        return $value;
//        }else{
//            return $value;
//        }
    }

    public function getFormattedEventLabel($label, $data, $choices)
    {
        $line='';
        for ($index=0; $index < sizeof($data); ++$index) {
            $line .= $this->getChoiceLabel($choices, $data[$index]);
            if (sizeof($data) - 1 != $index) {
                $line .= ', ';
            }
        }
        if ($line != '') {
            $label=$label.' ['.$line.']';
        }

        return $label;
    }

    public function getLabelFromMultiChoices($label, $choice1, $choice2, $data1, $data2)
    {
        $label1='';
        if (sizeof($data1) > 0) {
            $label1=$this->getFormattedEventLabel('Add', $data1, $choice1);
        }
        $label2='';
        if (sizeof($data2) > 0) {
            $label2=$this->getFormattedEventLabel(' Remove', $data2, $choice2);
        }
        if ($label1 != '') {
            $label=$label.', '.$label1;
        }
        if ($label2 != '' && $label1 != '') {
            $label=$label.', '.$label2;
        } elseif ($label2 != '') {
            $label=$label.', '.$label2;
        }

        return $label;
    }

    public function getDelayEventLabel($event)
    {
        $translator = $this->translator;
        $label      ='';
        if ($event['triggerMode'] == 'interval') {
            $label = 'mautic.campaign.connection.trigger.interval.label';
            if ($event['anchor'] == 'no') {
                $label .= '_inaction';
            }
            $label = $translator->trans(
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
            $label                    = $translator->trans(
                $label,
                [
                    '%full%' => $dh->toFull($event['triggerDate']),
                    '%time%' => $dh->toTime($event['triggerDate']),
                    '%date%' => $dh->toShort($event['triggerDate']),
                ]
            );
        }

        return $label;
    }

    public function getConditionEventLabel($form, $filters)
    {
        $label='';
        $index=0;
        foreach ($filters as $key => $data) {
            $operator   = $data['operator'];
            $object     = $data['object'];
            $field      = $data['field'];
            $fieldlabel = $data['fieldlabel'];
            $value      = $data['filter'];
            $glue       = $data['glue'];
            $oplabel    = $this->getOperatorLabel($operator);
            $oplabel    = $this->get('translator')->trans($oplabel);
            if (is_array($value)) {
                $list   =[];
                $options=$form->get('properties')->get('filters')->getConfig()->getOption('options');
                if ($object == 'pages') {
                    $list=$options['landingpage_list']['en'];
                } elseif ($object == 'emails') {
                    $list=$options['emails']['en'];
                } elseif ($object == 'one_of_campaign') {
                    $list=$options['emails']['en'];
                } elseif ($object == 'list_categories') {
                    $list=$options['globalcategory'];
                } elseif ($object == 'list_leadlist') {
                    $list=$options['lists'];
                } elseif ($object == 'list_listoptin') {
                    $list=$options['listoptin'];
                } elseif ($object == 'list_tags') {
                    $list=$options['tags'];
                } elseif ($object == 'lead' && $field == 'owner_id') {
                    $list=$options['users'];
                } elseif ($object == 'forms') {
                    $list=$options['formsubmit_list'];
                } elseif ($object == 'assets') {
                    $list=$options['asset_downloads_list']['en'];
                } elseif ($object == 'drip_campaign') {
                    $list=$options[$data['type']];
                }
                if (!empty($list)) {
                    $displaystring='';
                    if (sizeof($value) > 0) {
                        if ($object == 'drip_campaign') {
                            if ($data['type'] != 'drip_email_list') {
                                $keys = array_keys($list);
                                foreach ($keys as $key) {
                                    $lists = $list[$key];
                                    for ($v = 0; $v < sizeof($value); ++$v) {
                                        if (isset($lists[$value[$v]])) {
                                            $displaystring .= $key.':'.$lists[$value[$v]];
                                            if ($v < sizeof($value) - 1) {
                                                $displaystring .= ', ';
                                            }
                                        }
                                    }
                                }
                            } else {
                                for ($v = 0; $v < sizeof($value); ++$v) {
                                    $stringvalue = $list[$value[$v]];
                                    $displaystring .= $stringvalue;
                                    if ($v < sizeof($value) - 1) {
                                        $displaystring .= ', ';
                                    }
                                }
                            }
                        } else {
                            for ($v = 0; $v < sizeof($value); ++$v) {
                                $displaystring .= $list[$value[$v]];
                                if ($v < sizeof($value) - 1) {
                                    $displaystring .= ', ';
                                }
                            }
                        }
                    } else {
                        $value = '';
                    }
                    if ($displaystring != '') {
                        if ($object == 'emails') {
                            if (sizeof($value > 0)) {
                                $value = '['.$displaystring.'] emails';
                            } else {
                                $value = '['.$displaystring.'] email';
                            }
                        } else {
                            $value = '['.$displaystring.']';
                        }
                    }
                } else {
                    $value='['.implode(', ', $value).']';
                }
            } else {
                if ($fieldlabel == 'Lead score') {
                    $v    =ucwords($value);
                    $value='['.$v.']';
                } elseif ($fieldlabel == 'Email activity') {
                    $value = '['.$value.'] emails';
                } elseif ($fieldlabel == 'Email bounced' || $fieldlabel == 'Email unsubscribed') {
                    $value = $value == 1 ? '[ Yes ]' : '[ No ]';
                } else {
                    $value = '['.$value.']';
                }
            }
            if ($index > 0) {
                if ($glue == 'or') {
                    $label .= ', ';
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
            } elseif ($type == 'lead.changelistoptin') {
                if (empty($formData['properties']['addToLists']) && empty($formData['properties']['removeFromLists'])) {
                    $form['properties']['addToLists']->addError(
                        new FormError($this->translator->trans('mautic.campaign.list.add.required', [], 'validators'))
                    );
                    $form['properties']['removeFromLists']->addError(
                        new FormError($this->translator->trans('mautic.campaign.list.remove.required', [], 'validators'))
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
            if ($type == 'removeFBCustomAudience' || $type == 'addFBCustomAudience') {
                if (empty($formData['properties']['adaccount'])) {
                    $form['properties']['adaccount']->addError(
                        new FormError($this->translator->trans('mautic.core.value.required', [], 'validators'))
                    );
                    $isValidForm = false;
                } elseif (!empty($formData['properties']['adaccount'])) {
                    if (empty($formData['properties']['customaudience'])) {
                        $form['properties']['customaudience']->addError(
                            new FormError($this->translator->trans('mautic.core.value.required', [], 'validators'))
                        );
                        $isValidForm = false;
                    }
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
        if ($type == 'openEmail' || $type == 'clickEmail') {
            if (!empty($formData['properties']['campaigntype'])) {
                if ($formData['properties']['campaigntype'] == 'broadcast') {
                    if (empty($formData['properties']['emails'])) {
                        $form['properties']['emails']->addError(
                            new FormError($this->translator->trans('mautic.core.value.required', [], 'validators'))
                        );
                        $isValidForm = false;
                    }
                } else {
                    if (empty($formData['properties']['driplist'])) {
                        $form['properties']['driplist']->addError(
                            new FormError($this->translator->trans('mautic.core.value.required', [], 'validators'))
                        );
                        $isValidForm = false;
                    }
                }
            }
        }

        if ($type == 'fbLeadAds') {
            if (empty($formData['properties']['fbpage'])) {
                $form['properties']['fbpage']->addError(
                    new FormError($this->translator->trans('mautic.core.value.required', [], 'validators'))
                );
                $isValidForm = false;
            } elseif (!empty($formData['properties']['fbpage'])) {
                if (empty($formData['properties']['leadgenform'])) {
                    $form['properties']['leadgenform']->addError(
                        new FormError($this->translator->trans('mautic.core.value.required', [], 'validators'))
                    );
                    $isValidForm = false;
                }
            }
        }

        return $isValidForm;
    }
}
