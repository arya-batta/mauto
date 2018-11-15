<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Controller;

use Mautic\CoreBundle\Controller\BuilderControllerTrait;
use Mautic\CoreBundle\Controller\FormController;
use Mautic\CoreBundle\Controller\FormErrorMessagesTrait;
use Mautic\EmailBundle\Model\DripEmailModel;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\LeadBundle\Controller\EntityContactsTrait;
use Symfony\Component\HttpFoundation\JsonResponse;

class DripEmailController extends FormController
{
    use BuilderControllerTrait;
    use FormErrorMessagesTrait;
    use EntityContactsTrait;

    /**
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = 1)
    {
        /** @var DripEmailModel $model */
        $model = $this->getModel('email.dripemail');

        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'dripemail:emails:viewown',
                'dripemail:emails:viewother',
                'dripemail:emails:create',
                'dripemail:emails:editown',
                'dripemail:emails:editother',
                'dripemail:emails:deleteown',
                'dripemail:emails:deleteother',
                'dripemail:emails:publishown',
                'dripemail:emails:publishother',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions['dripemail:emails:viewown'] && !$permissions['dripemail:emails:viewother']) {
            return $this->accessDenied();
        }

        $session = $this->get('session');

        //set limits
        $limit = $session->get('mautic.email.limit', $this->coreParametersHelper->getParameter('default_pagelimit'));
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $search = $this->request->get('search', $session->get('mautic.email.filter', ''));
        $session->set('mautic.email.filter', $search);

        $ignoreListJoin = true;

        $filter = [
            'string' => $search,
            'force'  => [],
        ];
        if (!$permissions['dripemail:emails:viewother']) {
            $filter['force'][] =
                ['column' => 'd.createdBy', 'expr' => 'eq', 'value' => $this->user->getId()];
        }

        $orderBy    = $session->get('mautic.email.orderby', 'd.subject');
        $orderByDir = $session->get('mautic.email.orderbydir', 'DESC');

        $emails = $model->getEntities(
            [
                'start'          => $start,
                'limit'          => $limit,
                'filter'         => $filter,
                'orderBy'        => $orderBy,
                'orderByDir'     => $orderByDir,
                'ignoreListJoin' => $ignoreListJoin,
            ]
        );
        $count = count($emails);

        return $this->delegateView(
            [
                'viewParameters' => [
                    'searchValue'      => $search,
                    'filters'          => [],
                    'items'            => $emails,
                    'totalItems'       => $count,
                    'page'             => $page,
                    'limit'            => $limit,
                    'tmpl'             => $this->request->get('tmpl', 'index'),
                    'permissions'      => $permissions,
                    'model'            => $model,
                    'actionRoute'      => 'le_dripemail_campaign_action',
                    'indexRoute'       => 'le_dripemail_index',
                    'headerTitle'      => 'Drip Campaigns',
                    'translationBase'  => 'mautic.email.broadcast',
                    'emailBlockDetails'=> [],
                ],
                'contentTemplate' => 'MauticEmailBundle:DripEmail:list.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_dripemail_index',
                    'leContent' => 'dripemail',
                    'route'         => $this->generateUrl('le_dripemail_index', ['page' => $page]),
                ],
            ]
        );
    }

    /**
     * Generates new form and processes post data.
     *
     * @param \Mautic\EmailBundle\Entity\DripEmail $entity
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction($entity = null)
    {
        /** @var DripEmailModel $model */
        $model = $this->getModel('email.dripemail');

        if ($entity == null) {
            $entity = $model->getEntity();
        }

        $action    = $this->generateUrl('le_dripemail_index');
        $form      = $model->createForm($entity, $this->get('form.factory'), $action);

        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $model->saveEntity($entity);
                    $this->editAction($entity->getId());
                }
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'     => $form->createView(),
                    'entity'   => $entity,
                ],
                'contentTemplate' => 'MauticEmailBundle:DripEmail:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_dripemail_index',
                    'leContent' => 'dripemail',
                    'route'         => $this->generateUrl(
                        'le_dripemail_campaign_action',
                        [
                            'objectAction' => 'new',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Generates new form and processes post data.
     *
     * @param   $objectId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($objectId = null)
    {
        /** @var DripEmailModel $model */
        $model = $this->getModel('email.dripemail');

        $entity = $model->getEntity($objectId);

        $action      = $this->generateUrl('le_dripemail_campaign_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $form        = $model->createForm($entity, $this->get('form.factory'), $action);
        $routeParams = [
            'objectAction' => 'edit',
            'objectId'     => $entity->getId(),
        ];

        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator    = $this->get('mautic.configurator');
        $params          = $configurator->getParameters();
        $fromname        = $params['mailer_from_name'];
        $fromadress      = $params['mailer_from_email'];
        $fromName        = $entity->getFromName();
        $fromAdress      = $entity->getFromAddress();
        /** @var EmailModel $emailmodel */
        $emailmodel = $this->getModel('email');
        /** @var \Mautic\EmailBundle\Entity\Email $emailentity */
        $emailentity = $emailmodel->getEntity();
        $emailentity->setEmailType('list');
        $emailaction = $this->generateUrl('le_email_campaign_action', ['objectAction' => 'new']);
        //create the form
        $emailform      = $emailmodel->createForm($emailentity, $this->get('form.factory'), $emailaction, ['update_select' => false, 'isEmailTemplate' => false]);
        $returnUrl      = $this->generateUrl('le_dripemail_index');
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => 1],
            'contentTemplate' => 'MauticEmailBundle:DripEmail:index',
            'passthroughVars' => [
                'activeLink'    => 'le_dripemail_index',
                'leContent' => 'dripemail',
            ],
        ];
        if (empty($fromName)) {
            $entity->setFromName($fromname);
        }
        if (empty($fromAdress)) {
            $entity->setFromAddress($fromadress);
        }
        if ($entity === null) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'le.drip.email.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        } elseif (!$this->get('mautic.security')->hasEntityAccess(
            'dripemail:emails:editown',
            'dripemail:emails:editother',
            $entity->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        } elseif ($model->isLocked($entity)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $entity, 'email');
        }
        $signuprepo = $this->factory->get('le.core.repository.signup');
        $bluePrints = $signuprepo->getBluePrintCampaigns();
        $dripPrints = $signuprepo->getDripEmails();

        if ($this->request->getMethod() == 'POST') {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $formData = $this->request->request->get('dripemailform');
                    $entity->setName($formData['name']);
                    $entity->setFromAddress($formData['fromAddress']);
                    $entity->setFromName($formData['fromName']);
                    $entity->setReplyToAddress($formData['replyToAddress']);
                    $entity->setBccAddress($formData['bccAddress']);
                    $entity->setGoogleTags($formData['google_tags']);
                    $entity->setIsPublished($formData['isPublished']);
                    $entity->setUnsubscribeText($formData['unsubscribe_text']);
                    $entity->setPostalAddress($formData['postal_address']);
                    //$entity->setCategory($formData['category']);
                    $entity->setScheduleDate($formData['scheduleDate']);
                    //$entity->setDaysEmailSend($formData['daysEmailSend']);
                    //$changes = $entity->getChanges(true);
                    //if (!empty($changes['fromAddress']) || !empty($changes['fromName'])) {
                    $model->getRepository()->updateFromInfoinEmail($entity);
                    //}
                    $model->saveEntity($entity);
                    $this->addFlash(
                        'mautic.core.notice.updated',
                        [
                            '%name%'      => $entity->getName(),
                            '%menu_link%' => 'le_dripemail_index',
                            '%url%'       => $this->generateUrl(
                                'le_dripemail_campaign_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $entity->getId(),
                                ]
                            ),
                        ],
                        'warning'
                    );

                    return $this->redirect($this->generateUrl('le_dripemail_index'));
                    //$this->editAction($entity);
                }
            }
            $passthrough = [
                'activeLink'    => '#le_dripemail_index',
                'leContent' => 'dripemail',
            ];
            $template       = 'MauticEmailBundle:DripEmail:index';
            $viewParameters = [
            ];
            if ($cancelled) {
                return $this->redirect($this->generateUrl('le_dripemail_index'));
            }
        }

        $groupFilters  = [
            'filters' => [
                'multiple'    => false,
                'onchange'    => 'Le.filterBeeTemplates()',
            ],
        ];

        $groupFilters['filters']['groups'] = [];

        $groupFilters['filters']['groups']['']  = [
            'options' => $emailmodel->getEmailTemplateGroupNames(),
        ];

        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'email:emails:viewown',
                'email:emails:viewother',
                'email:emails:create',
                'email:emails:editown',
                'email:emails:editother',
                'email:emails:deleteown',
                'email:emails:deleteother',
                'email:emails:publishown',
                'email:emails:publishother',
            ],
            'RETURN_ARRAY'
        );
        $items = [];
        if ($objectId != null && !empty($objectId)) {
            $items = $emailmodel->getEntities(
                [
                    'filter' => [
                        'force' => [
                            [
                                'column' => 'e.dripEmail',
                                'expr'   => 'eq',
                                'value'  => $entity,
                            ],
                        ],
                    ],
                    'orderBy'          => 'e.dripEmailOrder',
                    'orderByDir'       => 'asc',
                    'ignore_paginator' => true,
                ]
            );
        }
        //dump($items);
        //foreach ($bluePrints as $key => $item){
        //    dump($dripPrints);
        //    foreach ($item as $entitys){
        //        dump($entitys);
        //   }

        //}
        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'              => $form->createView(),
                    'entity'            => $entity,
                    'emailform'         => $emailform->createView(),
                    'beetemplates'      => $this->factory->getInstalledBeeTemplates('email'),
                    'filters'           => $groupFilters,
                    'items'             => $items,
                    'permissions'       => $permissions,
                    'actionRoute'       => 'le_dripemail_campaign_action',
                    'translationBase'   => 'mautic.email.broadcast',
                    'emailEntity'       => $emailentity,
                    'bluePrints'        => $bluePrints,
                    'drips'             => $dripPrints,
                ],
                'contentTemplate' => 'MauticEmailBundle:DripEmail:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_dripemail_index',
                    'leContent' => 'dripemail',
                    'route'         => $this->generateUrl(
                        'le_dripemail_campaign_action',
                        $routeParams
                    ),
                ],
            ]
        );
    }

    /**
     * Generates new form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function quickaddAction()
    {
        /** @var DripEmailModel $model */
        $model     = $this->getModel('email.dripemail');
        $dripemail = $model->getEntity();
        $action    = $this->generateUrl('le_dripemail_campaign_action', ['objectAction' => 'quickadd']);
        $form      = $model->createForm($dripemail, $this->get('form.factory'), $action, ['isShortForm' => true]);

        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $model->saveEntity($dripemail);
                    $viewParameters = [];
                    $returnUrl      = $this->generateUrl('le_dripemail_campaign_action', ['objectAction' => 'edit', 'objectId' => $dripemail->getId()]);
                    $template       = 'MauticEmailBundle:DripEmail:edit';

                    return $this->delegateRedirect($returnUrl);
                }
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'        => $form->createView(),
                    'dripemail'   => $dripemail,
                ],
                'contentTemplate' => 'MauticEmailBundle:DripEmail:quickadd.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_dripemail_index',
                    'leContent' => 'dripemail',
                    'route'         => $this->generateUrl(
                        'le_dripemail_campaign_action',
                        [
                            'objectAction' => 'new',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        $page      = $this->get('session')->get('mautic.email.page', 1);
        $returnUrl = $this->generateUrl('le_dripemail_index', ['page' => $page]);
        $flashes   = [];
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            $this->redirectToPricing();
        }
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticEmailBundle:DripEmail:index',
            'passthroughVars' => [
                'activeLink'    => '#le_dripemail_index',
                'leContent' => 'dripemail',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model = $this->getModel('email.dripemail');
            $ids   = json_decode($this->request->query->get('ids', '{}'));

            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if ($entity === null) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'le.drip.email.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('mautic.security')->hasEntityAccess(
                    'dripemail:emails:viewown',
                    'dripemail:emails:viewother',
                    $entity->getCreatedBy()
                )
                ) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'email.dripemail', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'le.drip.email.notice.batch_deleted',
                    'msgVars' => [
                        '%count%' => count($entities),
                    ],
                ];
            }
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                [
                    'flashes' => $flashes,
                ]
            )
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
        $page      = $this->get('session')->get('mautic.email.page', 1);
        $returnUrl = $this->generateUrl('le_dripemail_index', ['page' => $page]);
        $flashes   = [];
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            $this->redirectToPricing();
        }
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticEmailBundle:DripEmail:index',
            'passthroughVars' => [
                'activeLink'    => 'le_dripemail_index',
                'leContent' => 'dripemail',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model  = $this->getModel('email.dripemail');
            $entity = $model->getEntity($objectId);

            if ($entity === null) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'le.drip.email.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('mautic.security')->hasEntityAccess(
                'dripemail:emails:deleteown',
                'dripemail:emails:deleteother',
                $entity->getCreatedBy()
            )
            ) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'email.dripemail');
            }

            $model->deleteEntity($entity);

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'mautic.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $entity->getName(),
                    '%id%'   => $objectId,
                ],
            ];
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                [
                    'flashes' => $flashes,
                ]
            )
        );
    }
}
