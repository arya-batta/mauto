<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\FormBundle\Controller;

use Mautic\CoreBundle\Controller\FormController as CommonFormController;
use Mautic\FormBundle\Entity\Field;
use Mautic\FormBundle\Entity\Form;
use Mautic\FormBundle\Exception\ValidationException;
use Mautic\FormBundle\Model\FormModel;
use Mautic\SubscriptionBundle\Entity\Account;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FormController.
 */
class FormController extends CommonFormController
{
    /**
     * @param int $page
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function indexAction($page = 1)
    {
        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'form:forms:viewown',
                'form:forms:viewother',
                'form:forms:create',
                'form:forms:editown',
                'form:forms:editother',
                'form:forms:deleteown',
                'form:forms:deleteother',
                'form:forms:publishown',
                'form:forms:publishother',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions['form:forms:viewown'] && !$permissions['form:forms:viewother']) {
            return $this->accessDenied();
        }

        if ($this->request->getMethod() == 'POST') {
            $this->setListFilters();
        }

        $listFilters = [
            'filters' => [
                'placeholder' => $this->get('translator')->trans('mautic.category.filter.placeholder'),
                'multiple'    => true,
            ],
        ];
        // Reset available groups
        $listFilters['filters']['groups'] = [];

        $session = $this->get('session');

        //set limits
        $limit = $session->get('mautic.form.limit', $this->coreParametersHelper->getParameter('default_pagelimit'));
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $search = $this->request->get('search', $session->get('mautic.form.filter', ''));
        $session->set('mautic.form.filter', $search);

        $filter = ['string' => $search, 'force' => []];

        if (!$permissions['form:forms:viewother']) {
            $filter['force'][] = ['column' => 'f.createdBy', 'expr' => 'eq', 'value' => $this->user->getId()];
        }

        $listFilters['filters']['groups']['mautic.core.filter.category'] = [
            'options' => $this->getModel('category.category')->getLookupResults('form'),
            'prefix'  => 'category',
        ];

        $updatedFilters = $this->request->get('filters', false);

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
        $this->get('session')->set('mautic.form.filter', []);

        if (!empty($currentFilters)) {
            $catIds = [];
            foreach ($currentFilters as $type => $typeFilters) {
                switch ($type) {
                    case 'category':
                        $key = 'categories';
                        break;
                }

                $listFilters['filters']['groups']['mautic.core.filter.'.$key]['values'] = $typeFilters;

                foreach ($typeFilters as $fltr) {
                    switch ($type) {
                        case 'category':
                            $catIds[] = (int) $fltr;
                            break;
                    }
                }
            }

            if (!empty($catIds)) {
                $filter['force'][] = ['column' => 'c.id', 'expr' => 'in', 'value' => $catIds];
            }
        }
        $orderBy    = $session->get('mautic.form.orderby', 'f.name');
        $orderByDir = $session->get('mautic.form.orderbydir', 'ASC');

        $forms = $this->getModel('form.form')->getEntities(
            [
                'start'      => $start,
                'limit'      => $limit,
                'filter'     => $filter,
                'orderBy'    => $orderBy,
                'orderByDir' => $orderByDir,
            ]
        );

        $count = count($forms);

        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            $lastPage = ($count === 1) ? 1 : ((ceil($count / $limit)) ?: 1) ?: 1;

            $session->set('mautic.form.page', $lastPage);
            $returnUrl = $this->generateUrl('mautic_form_index', ['page' => $lastPage]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $lastPage],
                    'contentTemplate' => 'MauticFormBundle:Form:index',
                    'passthroughVars' => [
                        'activeLink'    => '#mautic_form_index',
                        'mauticContent' => 'form',
                    ],
                ]
            );
        }

        //set what page currently on so that we can return here after form submission/cancellation
        $session->set('mautic.form.page', $page);
        $formBlockDetails = $this->getModel('form')->getFormDisplayBlocks();

        $viewParameters = [
            'searchValue'     => $search,
            'filters'         => $listFilters,
            'items'           => $forms,
            'totalItems'      => $count,
            'page'            => $page,
            'limit'           => $limit,
            'permissions'     => $permissions,
            'security'        => $this->get('mautic.security'),
            'tmpl'            => $this->request->get('tmpl', 'index'),
            'formBlockDetails'=> $formBlockDetails,
        ];

        return $this->delegateView(
            [
                'viewParameters'  => $viewParameters,
                'contentTemplate' => 'MauticFormBundle:Form:list.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_form_index',
                    'mauticContent' => 'form',
                    'route'         => $this->generateUrl('mautic_form_index', ['page' => $page]),
                ],
            ]
        );
    }

    /**
     * Loads a specific form into the detailed panel.
     *
     * @param int $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function viewAction($objectId)
    {
        /** @var \Mautic\FormBundle\Model\FormModel $model */
        $model      = $this->getModel('form');
        $activeForm = $model->getEntity($objectId);

        //set the page we came from
        $page = $this->get('session')->get('mautic.form.page', 1);

        if ($activeForm === null) {
            //set the return URL
            $returnUrl = $this->generateUrl('mautic_form_index', ['page' => $page]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'MauticFormBundle:Form:index',
                    'passthroughVars' => [
                        'activeLink'    => '#mautic_form_index',
                        'mauticContent' => 'form',
                    ],
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'mautic.form.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ]
            );
        } elseif (!$this->get('mautic.security')->hasEntityAccess(
            'form:forms:viewown',
            'form:forms:viewother',
            $activeForm->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        }

        $permissions = $this->get('mautic.security')->isGranted(
            [
                'form:forms:viewown',
                'form:forms:viewother',
                'form:forms:create',
                'form:forms:editown',
                'form:forms:editother',
                'form:forms:deleteown',
                'form:forms:deleteother',
                'form:forms:publishown',
                'form:forms:publishother',
            ],
            'RETURN_ARRAY'
        );

        // Audit Log
        $logs = $this->getModel('core.auditLog')->getLogForObject('form', $objectId, $activeForm->getDateAdded());

        // Init the date range filter form
        $dateRangeValues = $this->request->get('daterange', []);
        $action          = $this->generateUrl('mautic_form_action', ['objectAction' => 'view', 'objectId' => $objectId]);
        $dateRangeForm   = $this->get('form.factory')->create('daterange', $dateRangeValues, ['action' => $action]);

        // Submission stats per time period
        $timeStats = $this->getModel('form.submission')->getSubmissionsLineChartData(
            null,
            new \DateTime($dateRangeForm->get('date_from')->getData()),
            new \DateTime($dateRangeForm->get('date_to')->getData()),
            null,
            ['form_id' => $objectId]
        );

        // Only show actions and fields that still exist
        $customComponents  = $model->getCustomComponents();
        $activeFormActions = [];
        foreach ($activeForm->getActions() as $formAction) {
            if (!isset($customComponents['actions'][$formAction->getType()])) {
                continue;
            }
            $type                          = explode('.', $formAction->getType());
            $activeFormActions[$type[0]][] = $formAction;
        }

        $activeFormFields = [];
        $fieldHelper      = $this->get('mautic.helper.form.field_helper');
        $availableFields  = $fieldHelper->getChoiceList($customComponents['fields']);
        foreach ($activeForm->getFields() as $field) {
            if (!isset($availableFields[$field->getType()])) {
                continue;
            }

            $activeFormFields[] = $field;
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'activeForm'  => $activeForm,
                    'page'        => $page,
                    'logs'        => $logs,
                    'permissions' => $permissions,
                    'stats'       => [
                        'submissionsInTime' => $timeStats,
                    ],
                    'dateRangeForm'     => $dateRangeForm->createView(),
                    'activeFormActions' => $activeFormActions,
                    'activeFormFields'  => $activeFormFields,
                    'formScript'        => htmlspecialchars($model->getFormScript($activeForm), ENT_QUOTES, 'UTF-8'),
                    'formContent'       => htmlspecialchars($model->getContent($activeForm, false), ENT_QUOTES, 'UTF-8'),
                    'availableActions'  => $customComponents['actions'],
                ],
                'contentTemplate' => 'MauticFormBundle:Form:details.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_form_index',
                    'mauticContent' => 'form',
                    'route'         => $action,
                ],
            ]
        );
    }

    /**
     * Generates new form and processes post data.
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @throws \Exception
     */
    public function newAction($objectEntity = null, $sessionid)
    {
        /** @var \Mautic\FormBundle\Model\FormModel $model */
        $model          = $this->getModel('form');
        $entity         = $model->getEntity();
        $session        = $this->get('session');
        $objectformtype = '';
        if ($objectEntity == '0') {
            $objectEntity = null;
        }
        if ($objectEntity == 'scratch_campaign' || $objectEntity == 'scratch_standalone') {
            $objectvalues   = explode('_', $objectEntity);
            $objectEntity   = $objectvalues[0];
            $objectformtype = $objectvalues[1];
        }
        $signuprepository = $this->get('le.core.repository.signup');
        $formitems        = $signuprepository->selectformItems();
        if (empty($formitems)) {
            $objectEntity = 'scratch';
        }
        if (!$this->get('mautic.security')->isGranted('form:forms:create')) {
            return $this->accessDenied();
        }

        //set the page we came from
        $page = $this->get('session')->get('mautic.form.page', 1);

        $sessionId = $this->request->request->get('mauticform[sessionId]', 'mautic_'.sha1(uniqid(mt_rand(), true)), true);
        if ($objectEntity != null && $objectEntity != 'scratch' && $objectEntity instanceof Form) {
            $entity    = $objectEntity;
            $sessionId = $sessionid;
        } elseif ($objectEntity == 'scratch') {
            $entity->setFormType($objectformtype);
        }
        //set added/updated fields
        $modifiedFields = $session->get('mautic.form.'.$sessionId.'.fields.modified', []);
        $deletedFields  = $session->get('mautic.form.'.$sessionId.'.fields.deleted', []);

        //set added/updated actions
        $modifiedActions = $session->get('mautic.form.'.$sessionId.'.actions.modified', []);
        $deletedActions  = $session->get('mautic.form.'.$sessionId.'.actions.deleted', []);

        $action = $this->generateUrl('mautic_form_action', ['objectAction' => 'new']);
        $form   = $model->createForm($entity, $this->get('form.factory'), $action);

        ///Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //only save fields that are not to be deleted
                    $fields = array_diff_key($modifiedFields, array_flip($deletedFields));

                    //make sure that at least one field is selected
                    if (empty($fields)) {
                        //set the error
                        $form->addError(
                            new FormError(
                                $this->get('translator')->trans('mautic.form.form.fields.notempty', [], 'validators')
                            )
                        );
                        $valid = false;
                    } else {
                        $model->setFields($entity, $fields);

                        try {
                            // Set alias to prevent SQL errors
                            $alias = $model->cleanAlias($entity->getName(), '', 10);
                            $entity->setAlias($alias);

                            // Set timestamps
                            $model->setTimestamps($entity, true, false);

                            // Save the form first and new actions so that new fields are available to actions.
                            // Using the repository function to not trigger the listeners twice.

                            $model->getRepository()->saveEntity($entity);

                            // Only save actions that are not to be deleted
                            $actions = array_diff_key($modifiedActions, array_flip($deletedActions));

                            // Set and persist actions
                            $model->setActions($entity, $actions);

                            // Save and trigger listeners
                            $model->saveEntity($entity, $form->get('buttons')->get('save')->isClicked());

                            $this->addFlash(
                                'mautic.core.notice.created',
                                [
                                    '%name%'      => $entity->getName(),
                                    '%menu_link%' => 'mautic_form_index',
                                    '%url%'       => $this->generateUrl(
                                        'mautic_form_action',
                                        [
                                            'objectAction' => 'edit',
                                            'objectId'     => $entity->getId(),
                                        ]
                                    ),
                                ]
                            );

                            if ($form->get('buttons')->get('save')->isClicked()) {
                                $viewParameters = [
                                    'objectAction' => 'view',
                                    'objectId'     => $entity->getId(),
                                ];
                                $returnUrl = $this->generateUrl('mautic_form_action', $viewParameters);
                                $template  = 'MauticFormBundle:Form:view';
                            } else {
                                //return edit view so that all the session stuff is loaded
                                return $this->editAction($entity->getId(), true);
                            }
                        } catch (ValidationException $ex) {
                            $form->addError(
                                new FormError(
                                    $ex->getMessage()
                                )
                            );
                            $valid = false;
                        } catch (\Exception $e) {
                            $form['name']->addError(
                                new FormError($this->get('translator')->trans('mautic.form.schema.failed', [], 'validators'))
                            );
                            $valid = false;

                            if ('dev' == $this->container->getParameter('kernel.environment')) {
                                throw $e;
                            }
                        }
                    }
                }
            } else {
                $viewParameters = ['page' => $page];
                $returnUrl      = $this->generateUrl('mautic_form_index', $viewParameters);
                $template       = 'MauticFormBundle:Form:index';
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                //clear temporary fields
                $this->clearSessionComponents($sessionId);

                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $returnUrl,
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                        'passthroughVars' => [
                            'activeLink'    => '#mautic_form_index',
                            'mauticContent' => 'form',
                        ],
                    ]
                );
            }
        } else {
            //clear out existing fields in case the form was refreshed, browser closed, etc
            $form->get('sessionId')->setData($sessionId);
            if ($objectEntity == null || $objectEntity == 'scratch') {
                $this->clearSessionComponents($sessionId);
                $modifiedFields = $modifiedActions = $deletedActions = $deletedFields = [];

                //$form->get('sessionId')->setData($sessionId);

                $modifiedFields = $this->createDefaultFields($sessionId);
                $session->set('mautic.form.'.$sessionId.'.fields.modified', $modifiedFields);
            }
        }
        $newactionurl = $this->generateUrl('mautic_form_action', ['objectAction' => 'new', 'objectId' => 'scratch']);
        //fire the form builder event
        $customComponents = $model->getCustomComponents($sessionId);

        $fieldHelper      = $this->get('mautic.helper.form.field_helper');

        return $this->delegateView(
            [
                'viewParameters' => [
                    'fields'         => $fieldHelper->getChoiceList($customComponents['fields']),
                    'actions'        => $customComponents['choices'],
                    'actionSettings' => $customComponents['actions'],
                    'formFields'     => $modifiedFields,
                    'formActions'    => $modifiedActions,
                    'deletedFields'  => $deletedFields,
                    'deletedActions' => $deletedActions,
                    //'tmpl'           => $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index',
                    'activeForm'     => $entity,
                    'form'           => $form->createView(),
                    'contactFields'  => $this->getModel('lead.field')->getFieldListWithProperties(),
                    'companyFields'  => $this->getModel('lead.field')->getFieldListWithProperties('company'),
                    'inBuilder'      => true,
                    'formItems'      => $formitems,
                    'objectID'       => $objectEntity,
                    'newFormURL'     => $newactionurl,
                ],
                'contentTemplate' => 'MauticFormBundle:Builder:index.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_form_index',
                    'mauticContent' => 'form',
                    'route'         => $this->generateUrl(
                        'mautic_form_action',
                        [
                            'objectAction' => (!empty($valid) ? 'edit' : 'new'), //valid means a new form was applied
                            'objectId'     => $entity->getId(),
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Generates edit form and processes post data.
     *
     * @param int  $objectId
     * @param bool $ignorePost
     * @param bool $forceTypeSelection
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    public function editAction($objectId, $ignorePost = false, $forceTypeSelection = false)
    {
        /** @var \Mautic\FormBundle\Model\FormModel $model */
        $model            = $this->getModel('form');
        $formData         = $this->request->request->get('mauticform');
        $sessionId        = isset($formData['sessionId']) ? $formData['sessionId'] : null;
        $customComponents = $model->getCustomComponents();

        if ($objectId instanceof Form) {
            $entity   = $objectId;
            $objectId = 'mautic_'.sha1(uniqid(mt_rand(), true));
        } else {
            $entity = $model->getEntity($objectId);

            // Process submit of cloned form
            if ($entity == null && $objectId == $sessionId) {
                $entity = $model->getEntity();
            }
        }

        $session    = $this->get('session');
        $cleanSlate = true;

        //set the page we came from
        $page = $this->get('session')->get('mautic.form.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('mautic_form_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticFormBundle:Form:index',
            'passthroughVars' => [
                'activeLink'    => '#mautic_form_index',
                'mauticContent' => 'form',
            ],
        ];

        //form not found
        if ($entity === null) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'mautic.form.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        } elseif (!$this->get('mautic.security')->hasEntityAccess(
            'form:forms:editown',
            'form:forms:editother',
            $entity->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        } elseif ($model->isLocked($entity)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $entity, 'form.form');
        }

        $action = $this->generateUrl('mautic_form_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $form   = $model->createForm($entity, $this->get('form.factory'), $action);

        ///Check for a submitted form and process it
        if (!$ignorePost && $this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                //set added/updated fields
                $modifiedFields = $session->get('mautic.form.'.$objectId.'.fields.modified', []);
                $deletedFields  = $session->get('mautic.form.'.$objectId.'.fields.deleted', []);
                $fields         = array_diff_key($modifiedFields, array_flip($deletedFields));

                //set added/updated actions
                $modifiedActions = $session->get('mautic.form.'.$objectId.'.actions.modified', []);
                $deletedActions  = $session->get('mautic.form.'.$objectId.'.actions.deleted', []);
                $actions         = array_diff_key($modifiedActions, array_flip($deletedActions));

                if ($valid = $this->isFormValid($form)) {
                    //make sure that at least one field is selected
                    if (empty($fields)) {
                        //set the error
                        $form->addError(
                            new FormError(
                                $this->get('translator')->trans('mautic.form.form.fields.notempty', [], 'validators')
                            )
                        );
                        $valid = false;
                    } else {
                        $model->setFields($entity, $fields);
                        $model->deleteFields($entity, $deletedFields);

                        if (!$alias = $entity->getAlias()) {
                            $alias = $model->cleanAlias($entity->getName(), '', 10);
                            $entity->setAlias($alias);
                        }

                        if (!$entity->getId()) {
                            // Set timestamps because this is a new clone
                            $model->setTimestamps($entity, true, false);
                        }

                        // save the form first so that new fields are available to actions
                        // use the repository method to not trigger listeners twice
                        try {
                            $model->getRepository()->saveEntity($entity);

                            // Ensure actions are compatible with form type
                            if (!$entity->isStandalone()) {
                                foreach ($actions as $actionId => $action) {
                                    if (empty($customComponents['actions'][$action['type']]['allowCampaignForm'])) {
                                        unset($actions[$actionId]);
                                        $deletedActions[] = $actionId;
                                    }
                                }
                            }

                            if (count($actions)) {
                                // Now set and persist the actions
                                $model->setActions($entity, $actions);
                            }

                            // Delete deleted actions
                            $model->deleteActions($entity, $deletedActions);

                            // Persist and execute listeners
                            $model->saveEntity($entity, $form->get('buttons')->get('save')->isClicked());

                            // Reset objectId to entity ID (can be session ID in case of cloned entity)
                            $objectId = $entity->getId();

                            $this->addFlash(
                                'mautic.core.notice.updated',
                                [
                                    '%name%'      => $entity->getName(),
                                    '%menu_link%' => 'mautic_form_index',
                                    '%url%'       => $this->generateUrl(
                                        'mautic_form_action',
                                        [
                                            'objectAction' => 'edit',
                                            'objectId'     => $entity->getId(),
                                        ]
                                    ),
                                ]
                            );

                            if ($form->get('buttons')->get('save')->isClicked()) {
                                $viewParameters = [
                                    'objectAction' => 'view',
                                    'objectId'     => $entity->getId(),
                                ];
                                $returnUrl = $this->generateUrl('mautic_form_action', $viewParameters);
                                $template  = 'MauticFormBundle:Form:view';
                            }
                        } catch (ValidationException $ex) {
                            $form->addError(
                                new FormError(
                                    $ex->getMessage()
                                )
                            );
                            $valid = false;
                        }
                    }
                }
            } else {
                //unlock the entity
                $model->unlockEntity($entity);

                $viewParameters = ['page' => $page];
                $returnUrl      = $this->generateUrl('mautic_form_index', $viewParameters);
                $template       = 'MauticFormBundle:Form:index';
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                //remove fields from session
                $this->clearSessionComponents($objectId);

                // Clear session items in case columns changed
                $session->remove('mautic.formresult.'.$entity->getId().'.orderby');
                $session->remove('mautic.formresult.'.$entity->getId().'.orderbydir');
                $session->remove('mautic.formresult.'.$entity->getId().'.filters');

                return $this->postActionRedirect(
                    array_merge(
                        $postActionVars,
                        [
                            'returnUrl'       => $returnUrl,
                            'viewParameters'  => $viewParameters,
                            'contentTemplate' => $template,
                        ]
                    )
                );
            } elseif ($valid && $form->get('buttons')->get('apply')->isClicked()) {
                // Rebuild everything to include new ids
                $cleanSlate = true;
                $reorder    = true;

                if ($valid) {
                    // Rebuild the form with new action so that apply doesn't keep creating a clone
                    $action = $this->generateUrl('mautic_form_action', ['objectAction' => 'edit', 'objectId' => $entity->getId()]);
                    $form   = $model->createForm($entity, $this->get('form.factory'), $action);
                }
            }
        } else {
            $cleanSlate = true;

            //lock the entity
            $model->lockEntity($entity);
        }

        if (!$form->isSubmitted()) {
            $form->get('sessionId')->setData($objectId);
        }

        // Get field and action settings
        $fieldHelper     = $this->get('mautic.helper.form.field_helper');
        $availableFields = $fieldHelper->getChoiceList($customComponents['fields']);

        if ($cleanSlate) {
            //clean slate
            $this->clearSessionComponents($objectId);

            //load existing fields into session
            $modifiedFields    = [];
            $usedLeadFields    = [];
            $usedCompanyFields = [];
            $existingFields    = $entity->getFields()->toArray();
            $submitButton      = false;

            foreach ($existingFields as $formField) {
                // Check to see if the field still exists

                if ($formField->getType() == 'button') {
                    //submit button found
                    $submitButton = true;
                }
                if ($formField->getType() !== 'button' && !isset($availableFields[$formField->getType()])) {
                    continue;
                }

                $id    = $formField->getId();
                $field = $formField->convertToArray();

                if (!$id) {
                    // Cloned entity
                    $id = $field['id'] = $field['sessionId'] = 'new'.hash('sha1', uniqid(mt_rand()));
                }

                unset($field['form']);

                if (isset($customComponents['fields'][$field['type']])) {
                    // Set the custom parameters
                    $field['customParameters'] = $customComponents['fields'][$field['type']];
                }
                $field['formId'] = $objectId;

                $modifiedFields[$id] = $field;

                if (!empty($field['leadField'])) {
                    $usedLeadFields[$id] = $field['leadField'];
                }
            }
            if (!$submitButton) { //means something deleted the submit button from the form
                //add a submit button
                $modifiedFields = $this->createDefaultFields($sessionId);
                $session->set('mautic.form.'.$sessionId.'.fields.modified', $modifiedFields);
            }
            $session->set('mautic.form.'.$objectId.'.fields.leadfields', $usedLeadFields);

            if (!empty($reorder)) {
                uasort(
                    $modifiedFields,
                    function ($a, $b) {
                        return $a['order'] > $b['order'];
                    }
                );
            }

            $session->set('mautic.form.'.$objectId.'.fields.modified', $modifiedFields);
            $deletedFields = [];

            // Load existing actions into session
            $modifiedActions = [];
            $existingActions = $entity->getActions()->toArray();

            foreach ($existingActions as $formAction) {
                // Check to see if the action still exists
                if (!isset($customComponents['actions'][$formAction->getType()])) {
                    continue;
                }

                $id     = $formAction->getId();
                $action = $formAction->convertToArray();

                if (!$id) {
                    // Cloned entity so use a random Id instead
                    $action['id'] = $id = 'new'.hash('sha1', uniqid(mt_rand()));
                }
                unset($action['form']);

                $modifiedActions[$id] = $action;
            }

            if (!empty($reorder)) {
                uasort(
                    $modifiedActions,
                    function ($a, $b) {
                        return $a['order'] > $b['order'];
                    }
                );
            }

            $session->set('mautic.form.'.$objectId.'.actions.modified', $modifiedActions);
            $deletedActions = [];
        }
        $newactionurl = $this->generateUrl('mautic_form_action', ['objectAction' => 'new', 'objectId' => 'scratch']);

        return $this->delegateView(
            [
                'viewParameters' => [
                    'fields'             => $availableFields,
                    'actions'            => $customComponents['choices'],
                    'actionSettings'     => $customComponents['actions'],
                    'formFields'         => $modifiedFields,
                    'fieldSettings'      => $customComponents['fields'],
                    'formActions'        => $modifiedActions,
                    'deletedFields'      => $deletedFields,
                    'deletedActions'     => $deletedActions,
                    //'tmpl'               => $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index',
                    'activeForm'         => $entity,
                    'form'               => $form->createView(),
                    'forceTypeSelection' => $forceTypeSelection,
                    'contactFields'      => $this->getModel('lead.field')->getFieldListWithProperties('lead'),
                    'companyFields'      => $this->getModel('lead.field')->getFieldListWithProperties('company'),
                    'inBuilder'          => true,
                    'objectID'           => null,
                    'formItems'          => [],
                    'newFormURL'         => $newactionurl,
                ],
                'contentTemplate' => 'MauticFormBundle:Builder:index.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_form_index',
                    'mauticContent' => 'form',
                    'route'         => $this->generateUrl(
                        'mautic_form_action',
                        [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Clone an entity.
     *
     * @param int $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function cloneAction($objectId)
    {
        $model = $this->getModel('form.form');

        /** @var \Mautic\FormBundle\Entity\Form $entity */
        $entity = $model->getEntity($objectId);

        if ($entity != null) {
            if (!$this->get('mautic.security')->isGranted('form:forms:create')
                || !$this->get('mautic.security')->hasEntityAccess(
                    'form:forms:viewown',
                    'form:forms:viewother',
                    $entity->getCreatedBy()
                )
            ) {
                return $this->accessDenied();
            }

            $entity = clone $entity;
            $entity->setIsPublished(false);

            // Clone the forms's fields
            $fields = $entity->getFields()->toArray();
            /** @var \Mautic\FormBundle\Entity\Field $field */
            foreach ($fields as $field) {
                $fieldClone = clone $field;
                $fieldClone->setForm($entity);
                $fieldClone->setSessionId(null);
                $entity->addField($field->getId(), $fieldClone);
            }

            // Clone the forms's actions
            $actions = $entity->getActions()->toArray();
            /** @var \Mautic\FormBundle\Entity\Action $action */
            foreach ($actions as $action) {
                $actionClone = clone $action;
                $actionClone->setForm($entity);
                $entity->addAction($action->getId(), $actionClone);
            }
        }

        return $this->editAction($entity, true, true);
    }

    /**
     * Gives a preview of the form.
     *
     * @param int $objectId
     *
     * @return Response
     */
    public function previewAction($objectId)
    {
        /** @var FormModel $model */
        $model = $this->getModel('form.form');
        $form  = $model->getEntity($objectId);
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $accmodel */
        $accmodel      = $this->getModel('subscription.accountinfo');
        $accrepo       = $accmodel->getRepository();
        $accountentity = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0];
        } else {
            $account = new Account();
        }
        $ishidepoweredby = $account->getNeedpoweredby();

        if ($form === null) {
            $html =
                '<h1>'.
                $this->get('translator')->trans('mautic.form.error.notfound', ['%id%' => $objectId], 'flashes').
                '</h1>';
        } elseif (!$this->get('mautic.security')->hasEntityAccess(
            'form:forms:editown',
            'form:forms:editother',
            $form->getCreatedBy()
        )
        ) {
            $html = '<h1>'.$this->get('translator')->trans('mautic.core.error.accessdenied', [], 'flashes').'</h1>';
        } else {
            $html = $model->getContent($form, true, false, $ishidepoweredby);
        }

        $model->populateValuesWithGetParameters($form, $html);

        $viewParams = [
            'content'     => $html,
            'stylesheets' => [],
            'name'        => $form->getName(),
        ];

        $template = $form->getTemplate();
        if (!empty($template)) {
            $theme = $this->get('mautic.helper.theme')->getTheme($template);
            if ($theme->getTheme() != $template) {
                $config = $theme->getConfig();
                if (in_array('form', $config['features'])) {
                    $template = $theme->getTheme();
                } else {
                    $template = null;
                }
            }
        }

        $viewParams['template'] = $template;

        if (!empty($template)) {
            $logicalName     = $this->get('mautic.helper.theme')->checkForTwigTemplate(':'.$template.':form.html.php');
            $assetsHelper    = $this->get('templating.helper.assets');
            $slotsHelper     = $this->get('templating.helper.slots');
            $analyticsHelper = $this->get('mautic.helper.template.analytics');

            if (!empty($customStylesheets)) {
                foreach ($customStylesheets as $css) {
                    $assetsHelper->addStylesheet($css);
                }
            }

            $slotsHelper->set('pageTitle', $form->getName());

            $analytics = $analyticsHelper->getCode();

            if (!empty($analytics)) {
                $assetsHelper->addCustomDeclaration($analytics);
            }

            return $this->render($logicalName, $viewParams);
        }

        return $this->render('MauticFormBundle::form.html.php', $viewParams);
    }

    /**
     * Deletes the entity.
     *
     * @param int $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        $page      = $this->get('session')->get('mautic.form.page', 1);
        $returnUrl = $this->generateUrl('mautic_form_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticFormBundle:Form:index',
            'passthroughVars' => [
                'activeLink'    => '#mautic_form_index',
                'mauticContent' => 'form',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model  = $this->getModel('form.form');
            $entity = $model->getEntity($objectId);

            if ($entity === null) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'mautic.form.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('mautic.security')->hasEntityAccess(
                'form:forms:deleteown',
                'form:forms:deleteother',
                $entity->getCreatedBy()
            )
            ) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'form.form');
            }

            $model->deleteEntity($entity);

            $identifier = $this->get('translator')->trans($entity->getName());
            $flashes[]  = [
                'type'    => 'notice',
                'msg'     => 'mautic.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $identifier,
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

    /**
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        $page      = $this->get('session')->get('mautic.form.page', 1);
        $returnUrl = $this->generateUrl('mautic_form_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticFormBundle:Form:index',
            'passthroughVars' => [
                'activeLink'    => '#mautic_form_index',
                'mauticContent' => 'form',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model     = $this->getModel('form');
            $ids       = json_decode($this->request->query->get('ids', ''));
            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if ($entity === null) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'mautic.form.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('mautic.security')->hasEntityAccess(
                    'form:forms:deleteown',
                    'form:forms:deleteother',
                    $entity->getCreatedBy()
                )
                ) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'form.form', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'mautic.form.notice.batch_deleted',
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
     * Clear field and actions from the session.
     */
    public function clearSessionComponents($sessionId)
    {
        $session = $this->get('session');
        $session->remove('mautic.form.'.$sessionId.'.fields.modified');
        $session->remove('mautic.form.'.$sessionId.'.fields.deleted');
        $session->remove('mautic.form.'.$sessionId.'.fields.leadfields');

        $session->remove('mautic.form.'.$sessionId.'.actions.modified');
        $session->remove('mautic.form.'.$sessionId.'.actions.deleted');
    }

    public function batchRebuildHtmlAction()
    {
        $page      = $this->get('session')->get('mautic.form.page', 1);
        $returnUrl = $this->generateUrl('mautic_form_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticFormBundle:Form:index',
            'passthroughVars' => [
                'activeLink'    => '#mautic_form_index',
                'mauticContent' => 'form',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            /** @var \Mautic\FormBundle\Model\FormModel $model */
            $model = $this->getModel('form');
            $ids   = json_decode($this->request->query->get('ids', ''));
            $count = 0;
            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if ($entity === null) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'mautic.form.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('mautic.security')->hasEntityAccess(
                    'form:forms:editown',
                    'form:forms:editother',
                    $entity->getCreatedBy()
                )
                ) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'form.form', true);
                } else {
                    $model->generateHtml($entity);
                    ++$count;
                }
            }

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'mautic.form.notice.batch_html_generated',
                'msgVars' => [
                    'pluralCount' => $count,
                    '%count%'     => $count,
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

    public function cloneFormTemplateAction($formid)
    {
        $model            = $this->getModel('form');
        $signuprepository = $this->get('le.core.repository.signup');
        $formitem         = $signuprepository->selectFormTemplatebyID($formid);
        $entity           = $model->getEntity();
        $entity->setName($formitem['name']);
        $entity->setCachedHtml($formitem['cached_html']);
        $entity->setAlias($formitem['alias']);
        $entity->setTemplate($formitem['template']);
        $entity->setFormType($formitem['form_type']);
        $entity->setPostAction($formitem['post_action']);
        $entity->setPostActionProperty($formitem['post_action_property']);
        $session           = $this->get('session');
        $sessionId         = $this->request->request->get('mauticform[sessionId]', 'mautic_'.sha1(uniqid(mt_rand(), true)), true);
        $modifiedFields    = $session->get('mautic.form.'.$sessionId.'.fields.modified', []);
        $usedLeadFields    = $this->get('session')->get('mautic.form.'.$sessionId.'.fields.leadfields', []);
        $formFields        = $signuprepository->selectFormFieldsTemplatebyID($formid);
        for ($i = 0; $i < sizeof($formFields); ++$i) {
            $keyId = 'new'.hash('sha1', uniqid(mt_rand()));
            $field = new Field();

            $modifiedFields[$keyId]                            = $field->convertToArray();
            $modifiedFields[$keyId]['label']                   = $formFields[$i]['label'];
            $modifiedFields[$keyId]['alias']                   = $formFields[$i]['alias'];
            $modifiedFields[$keyId]['showLabel']               = $formFields[$i]['showLabel'];
            $modifiedFields[$keyId]['type']                    = $formFields[$i]['type'];
            $modifiedFields[$keyId]['id']                      = $keyId;
            $modifiedFields[$keyId]['inputAttributes']         = $formFields[$i]['inputAttributes'];
            $modifiedFields[$keyId]['formId']                  = $sessionId;
            $modifiedFields[$keyId]['leadField']               = $formFields[$i]['leadField'];
            $modifiedFields[$keyId]['properties']              = unserialize($formFields[$i]['properties']);
            $modifiedFields[$keyId]['validationMessage']       = $formFields[$i]['validationMessage'];
            $modifiedFields[$keyId]['isRequired']              = $formFields[$i]['isRequired'];
            $modifiedFields[$keyId]['helpMessage']             = $formFields[$i]['helpMessage'];
            $modifiedFields[$keyId]['defaultValue']            = $formFields[$i]['defaultValue'];
            $modifiedFields[$keyId]['labelAttributes']         = $formFields[$i]['labelAttributes'];
            $modifiedFields[$keyId]['containerAttributes']     = $formFields[$i]['containerAttributes'];
            $modifiedFields[$keyId]['showWhenValueExists']     = $formFields[$i]['showWhenValueExists'];
            $modifiedFields[$keyId]['isAutoFill']              = $formFields[$i]['isAutoFill'];
            $modifiedFields[$keyId]['saveResult']              = $formFields[$i]['saveResult'];
            $modifiedFields[$keyId]['showAfterXSubmissions']   = $formFields[$i]['showAfterXSubmissions'];
            if (!empty($formFields[$i]['leadField'])) {
                $usedLeadFields[$keyId] = $formFields[$i]['leadField'];
            }
            unset($modifiedFields[$keyId]['form']);
        }
        $session->set('mautic.form.'.$sessionId.'.fields.leadfields', $usedLeadFields);
        $session->set('mautic.form.'.$sessionId.'.fields.modified', $modifiedFields);
        $newEntity = clone $entity;

        return $this->newAction($newEntity, $sessionId);
    }

    public function createDefaultFields($sessionId)
    {
        //add a text field i.e. Firstname
        $field = new Field();

        $firstNameId = 'new'.hash('sha1', uniqid(mt_rand()));

        $modifiedFields[$firstNameId]                      = $field->convertToArray();
        $modifiedFields[$firstNameId]['label']             = $this->translator->trans('mautic.integration.LinkedIn.firstName');
        $modifiedFields[$firstNameId]['alias']             = 'firstname';
        $modifiedFields[$firstNameId]['showLabel']         = 1;
        $modifiedFields[$firstNameId]['type']              = 'text';
        $modifiedFields[$firstNameId]['placeholder']       = $this->translator->trans('mautic.integration.LinkedIn.firstName');
        $modifiedFields[$firstNameId]['isRequired']        = 1;
        $modifiedFields[$firstNameId]['validationMessage'] = 'Please fill your First Name';
        $modifiedFields[$firstNameId]['leadField']         = 'firstname';
        $modifiedFields[$firstNameId]['id']                = $firstNameId;
        $modifiedFields[$firstNameId]['formId']            = $sessionId;
        unset($modifiedFields[$firstNameId]['form']);

        $emailId = 'new'.hash('sha1', uniqid(mt_rand()));

        $modifiedFields[$emailId]                      = $field->convertToArray();
        $modifiedFields[$emailId]['id']                = $emailId;
        $modifiedFields[$emailId]['label']             = $this->translator->trans('mautic.email.email');
        $modifiedFields[$emailId]['alias']             = 'email';
        $modifiedFields[$emailId]['type']              = 'email';
        $modifiedFields[$emailId]['formId']            = $sessionId;
        $modifiedFields[$emailId]['leadField']         = 'email';
        $modifiedFields[$emailId]['showLabel']         = 1;
        $modifiedFields[$emailId]['isRequired']        = 1;
        $modifiedFields[$emailId]['placeholder']       = $this->translator->trans('mautic.email.email');
        $modifiedFields[$emailId]['validationMessage'] = 'Please fill your Email Address';
        unset($modifiedFields[$emailId]['form']);

        $phoneId = 'new'.hash('sha1', uniqid(mt_rand()));

        $modifiedFields[$phoneId]                    = $field->convertToArray();
        $modifiedFields[$phoneId]['id']              = $phoneId;
        $modifiedFields[$phoneId]['label']           = $this->translator->trans('mautic.core.type.tel');
        $modifiedFields[$phoneId]['alias']           = 'phone';
        $modifiedFields[$phoneId]['type']            = 'tel';
        $modifiedFields[$phoneId]['formId']          = $sessionId;
        $modifiedFields[$phoneId]['leadField']       = 'mobile';
        $modifiedFields[$phoneId]['showLabel']       = 1;
        $modifiedFields[$phoneId]['placeholder']     = $this->translator->trans('mautic.core.type.tel');
        unset($modifiedFields[$phoneId]['form']);

        $submitId = 'new'.hash('sha1', uniqid(mt_rand()));

        $modifiedFields[$submitId]                    = $field->convertToArray();
        $modifiedFields[$submitId]['label']           = $this->translator->trans('mautic.core.form.submit');
        $modifiedFields[$submitId]['alias']           = 'submit';
        $modifiedFields[$submitId]['showLabel']       = 1;
        $modifiedFields[$submitId]['type']            = 'button';
        $modifiedFields[$submitId]['id']              = $submitId;
        $modifiedFields[$submitId]['inputAttributes'] = 'class="btn btn-default"';
        $modifiedFields[$submitId]['formId']          = $sessionId;
        unset($modifiedFields[$submitId]['form']);

        return $modifiedFields;
    }
}
