<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Controller;

use Doctrine\DBAL\DBALException;
use Mautic\CoreBundle\Controller\FormController;
use Mautic\LeadBundle\Entity\LeadField;
use Mautic\LeadBundle\Model\FieldModel;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;

class FieldController extends FormController
{
    /**
     * Generate's default list view.
     *
     * @param int $page
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = 1)
    {
        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(['lead:fields:full'], 'RETURN_ARRAY');

        $session = $this->get('session');

        if (!$permissions['lead:fields:full']) {
            return $this->accessDenied();
        }

        if ($this->request->getMethod() == 'POST') {
            $this->setListFilters();
        }

        $limit  = $session->get('mautic.leadfield.limit', $this->coreParametersHelper->getParameter('default_pagelimit'));
        $search = $this->request->get('search', $session->get('mautic.leadfield.filter', ''));
        $session->set('mautic.leadfilter.filter', $search);

        //do some default filtering
        $orderBy    = $this->get('session')->get('mautic.leadfilter.orderby', 'f.order');
        $orderByDir = $this->get('session')->get('mautic.leadfilter.orderbydir', 'ASC');

        $start = ($page === 1) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $request = $this->factory->getRequest();
        $search  = $request->get('search', $session->get('mautic.lead.emailtoken.filter', ''));

        $session->set('mautic.lead.emailtoken.filter', $search);

        $filter            = ['string' => $search, 'force' => []];
        $model             = $this->getModel('lead.field');
        $repo              = $model->getRepository();
        $filter['where'][] = [
            'expr' => 'orX',
            'val'  => [['column' => $repo->getTableAlias().'.isPublished', 'expr' => 'eq', 'value' => 1], ['column' => $repo->getTableAlias().'.createdBy', 'expr' => 'isNotNull']],
        ];
        $filter['where'][] = [
            'expr' => 'andX',
            'val'  => [['column' => $repo->getTableAlias().'.alias', 'expr' => 'neq', 'value' => 'status'], ['column' => $repo->getTableAlias().'.alias', 'expr' => 'neq', 'value' => 'created_source']],
        ]; //System Fields Status,Created Source
        $fields = $model->getEntities([
            'start'      => $start,
            'limit'      => $limit,
            'filter'     => $filter,
            'orderBy'    => $orderBy,
            'orderByDir' => $orderByDir,
        ]);
        $count = count($fields);

        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            if ($count === 1) {
                $lastPage = 1;
            } else {
                $lastPage = (ceil($count / $limit)) ?: 1;
            }
            $session->set('mautic.leadfield.page', $lastPage);
            $returnUrl = $this->generateUrl('le_contactfield_index', ['page' => $lastPage]);

            return $this->postActionRedirect([
                'returnUrl'       => $returnUrl,
                'viewParameters'  => ['page' => $lastPage],
                'contentTemplate' => 'MauticLeadBundle:Field:index',
                'passthroughVars' => [
                    'activeLink'    => '#le_contactfield_index',
                    'leContent'     => 'leadfield',
                ],
            ]);
        }

        //set what page currently on so that we can return here after form submission/cancellation
        $session->set('mautic.leadfield.page', $page);

        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';

        return $this->delegateView([
            'viewParameters' => [
                'items'       => $fields,
                'searchValue' => $search,
                'permissions' => $permissions,
                'tmpl'        => $tmpl,
                'totalItems'  => $count,
                'limit'       => $limit,
                'page'        => $page,
            ],
            'contentTemplate' => 'MauticLeadBundle:Field:list.html.php',
            'passthroughVars' => [
                'activeLink'    => '#le_contactfield_index',
                'route'         => $this->generateUrl('le_contactfield_index', ['page' => $page]),
                'leContent'     => 'leadfield',
            ],
        ]);
    }

    /**
     * Generate's new form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        if (!$this->get('mautic.security')->isGranted('lead:fields:full')) {
            return $this->accessDenied();
        }

        //retrieve the entity
        $field = new LeadField();
        /** @var FieldModel $model */
        $model = $this->getModel('lead.field');
        //set the return URL for post actions
        $returnUrl     = $this->generateUrl('le_contactfield_index');
        $action        = $this->generateUrl('le_contactfield_action', ['objectAction' => 'new']);
        $maxFieldOrder = $model->getMaxFieldOrder();
        //get the user form factory
        $form = $model->createForm($field, $this->get('form.factory'), $action, ['fieldOrder' => $maxFieldOrder]);

        ///Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $request = $this->request->request->all();
                    if (isset($request['leadfield']['properties'])) {
                        $result = $model->setFieldProperties($field, $request['leadfield']['properties']);
                        if ($result !== true) {
                            //set the error
                            $form->get('properties')->addError(
                                new FormError(
                                    $this->get('translator')->trans($result, [], 'validators')
                                )
                            );
                            $valid = false;
                        }
                    }

                    if ($valid) {
                        $flashMessage = 'mautic.core.notice.created';
                        try {
                            //form is valid so process the data
                            $model->saveEntity($field);
                        } catch (DBALException $ee) {
                            $flashMessage = $ee->getMessage();
                        } catch (\Exception $e) {
                            $form['alias']->addError(
                                    new FormError(
                                        $this->get('translator')->trans('le.lead.field.failed', ['%error%' => $e->getMessage()], 'validators')
                                    )
                                );
                            $valid = false;
                        }
                        $this->addFlash(
                                $flashMessage,
                                [
                                    '%name%'      => $field->getLabel(),
                                    '%menu_link%' => 'le_contactfield_index',
                                    '%url%'       => $this->generateUrl(
                                        'le_contactfield_action',
                                        [
                                            'objectAction' => 'edit',
                                            'objectId'     => $field->getId(),
                                        ]
                                    ),
                                ]
                            );
                    }
                }
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $returnUrl,
                        'contentTemplate' => 'MauticLeadBundle:Field:index',
                        'passthroughVars' => [
                            'activeLink'    => '#le_contactfield_index',
                            'leContent'     => 'leadfield',
                        ],
                    ]
                );
            } elseif ($valid && !$cancelled) {
                return $this->editAction($field->getId(), true);
            } elseif (!$valid) {
                // some bug in Symfony prevents repopulating list options on errors
                $field   = $form->getData();
                $newForm = $model->createForm($field, $this->get('form.factory'), $action, ['fieldOrder' => $maxFieldOrder]);
                $this->copyErrorsRecursively($form, $newForm);
                $form = $newForm;
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form' => $form->createView(),
                ],
                'contentTemplate' => 'MauticLeadBundle:Field:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_contactfield_index',
                    'route'         => $this->generateUrl('le_contactfield_action', ['objectAction' => 'new']),
                    'leContent'     => 'leadfield',
                ],
            ]
        );
    }

    /**
     * Generate's edit form and processes post data.
     *
     * @param            $objectId
     * @param bool|false $ignorePost
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($objectId, $ignorePost = false)
    {
        if (!$this->get('mautic.security')->isGranted('lead:fields:full')) {
            return $this->accessDenied();
        }

        /** @var FieldModel $model */
        $model = $this->getModel('lead.field');
        $field = $model->getEntity($objectId);

        //set the return URL
        $returnUrl = $this->generateUrl('le_contactfield_index');

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'contentTemplate' => 'MauticLeadBundle:Field:index',
            'passthroughVars' => [
                'activeLink'    => '#le_contactfield_index',
                'leContent'     => 'leadfield',
            ],
        ];
        //list not found
        if ($field === null) {
            return $this->postActionRedirect(
                array_merge($postActionVars, [
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'le.lead.field.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ])
            );
        } elseif ($model->isLocked($field)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $field, 'lead.field');
        }

        $action     = $this->generateUrl('le_contactfield_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $fieldOrder = $field->getOrder();
        $form       = $model->createForm($field, $this->get('form.factory'), $action, ['fieldOrder' => $fieldOrder]);

        ///Check for a submitted form and process it
        if (!$ignorePost && $this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $request = $this->request->request->all();
                    if (isset($request['leadfield']['properties'])) {
                        $result = $model->setFieldProperties($field, $request['leadfield']['properties']);
                        if ($result !== true) {
                            //set the error
                            $form->get('properties')->addError(new FormError(
                                $this->get('translator')->trans($result, [], 'validators')
                            ));
                            $valid = false;
                        }
                    }

                    if ($valid) {
                        //form is valid so process the data
                        $model->saveEntity($field, $form->get('buttons')->get('save')->isClicked());

                        $this->addFlash('mautic.core.notice.updated', [
                            '%name%'      => $field->getLabel(),
                            '%menu_link%' => 'le_contactfield_index',
                            '%url%'       => $this->generateUrl('le_contactfield_action', [
                                'objectAction' => 'edit',
                                'objectId'     => $field->getId(),
                            ]),
                        ]);
                    }
                }
            } else {
                //unlock the entity
                $model->unlockEntity($field);
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect(
                    array_merge($postActionVars, [
                            'viewParameters'  => ['objectId' => $field->getId()],
                            'contentTemplate' => 'MauticLeadBundle:Field:index',
                        ]
                    )
                );
            } elseif ($valid) {
                // Rebuild the form with new action so that apply doesn't keep creating a clone
                $action = $this->generateUrl('le_contactfield_action', ['objectAction' => 'edit', 'objectId' => $field->getId()]);
                $form   = $model->createForm($field, $this->get('form.factory'), $action, ['fieldOrder' => $fieldOrder]);
            } else {
                // some bug in Symfony prevents repopulating list options on errors
                $field   = $form->getData();
                $newForm = $model->createForm($field, $this->get('form.factory'), $action, ['fieldOrder' => $fieldOrder]);
                $this->copyErrorsRecursively($form, $newForm);
                $form = $newForm;
            }
        } else {
            //lock the entity
            $model->lockEntity($field);
        }

        return $this->delegateView([
            'viewParameters' => [
                'form' => $form->createView(),
            ],
            'contentTemplate' => 'MauticLeadBundle:Field:form.html.php',
            'passthroughVars' => [
                'activeLink'    => '#le_contactfield_index',
                'route'         => $action,
                'leContent'     => 'leadfield',
            ],
        ]);
    }

    /**
     * Clone an entity.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cloneAction($objectId)
    {
        $model  = $this->getModel('lead.field');
        $entity = $model->getEntity($objectId);

        if ($entity != null) {
            if (!$this->get('mautic.security')->isGranted('lead:fields:full')) {
                return $this->accessDenied();
            }

            $clone = clone $entity;
            $clone->setIsPublished(false);
            $clone->setIsFixed(false);
            $model->saveEntity($clone);
            $objectId = $clone->getId();
        }

        return $this->editAction($objectId);
    }

    /**
     * Delete a field.
     *
     * @param   $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        if (!$this->get('mautic.security')->isGranted('lead:fields:full')) {
            return $this->accessDenied();
        }

        $returnUrl = $this->generateUrl('le_contactfield_index');
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'contentTemplate' => 'MauticLeadBundle:Field:index',
            'passthroughVars' => [
                'activeLink'    => '#le_contactfield_index',
                'leContent'     => 'lead',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model = $this->getModel('lead.field');
            $field = $model->getEntity($objectId);

            if ($field === null) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'le.lead.field.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif ($model->isLocked($field)) {
                return $this->isLocked($postActionVars, $field, 'lead.field');
            } elseif ($field->isFixed()) {
                //cannot delete fixed fields
                return $this->accessDenied();
            }

            $model->deleteEntity($field);

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'mautic.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $field->getLabel(),
                    '%id%'   => $objectId,
                ],
            ];
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge($postActionVars, [
                'flashes' => $flashes,
            ])
        );
    }

    /**
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        if (!$this->get('mautic.security')->isGranted('lead:fields:full')) {
            return $this->accessDenied();
        }

        $returnUrl = $this->generateUrl('le_contactfield_index');
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'contentTemplate' => 'MauticLeadBundle:Field:index',
            'passthroughVars' => [
                'activeLink'    => '#le_contactfield_index',
                'leContent'     => 'lead',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model     = $this->getModel('lead.field');
            $ids       = json_decode($this->request->query->get('ids', '{}'));
            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if ($entity === null) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'le.lead.field.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif ($entity->isFixed()) {
                    $flashes[] = [
                        'type' => 'error',
                        'msg'  => $this->translator->trans('le.field.system.field.failed'),
                    ];
                //$this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'lead.field', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'le.lead.field.notice.batch_deleted',
                    'msgVars' => [
                        '%count%' => count($entities),
                    ],
                ];
            }
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge($postActionVars, [
                'flashes' => $flashes,
            ])
        );
    }
}
