<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Mautic\LeadBundle\Controller\EntityContactsTrait;
use Mautic\PluginBundle\Entity\Slack;
use Mautic\SmsBundle\Entity\Sms;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SlackController extends FormController
{
    use EntityContactsTrait;

    /**
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = 1)
    {
        /** @var \Mautic\PluginBundle\Model\SlackModel $model */
        $model = $this->getModel('plugin.slack');

        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'plugin:slack:viewown',
                'plugin:slack:viewother',
                'plugin:slack:create',
                'plugin:slack:editown',
                'plugin:slack:editother',
                'plugin:slack:deleteown',
                'plugin:slack:deleteother',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions['plugin:slack:viewown'] && !$permissions['plugin:slack:viewother']) {
            return $this->accessDenied();
        }

        if ($this->request->getMethod() == 'POST') {
            $this->setListFilters();
        }
        $listFilters = [
            'filters' => [
                'placeholder' => $this->get('translator')->trans('le.category.filter.placeholder'),
                'multiple'    => true,
            ],
        ];
        // Reset available groups
        $listFilters['filters']['groups'] = [];

        $session = $this->get('session');

        //set limits
        $limit = $session->get('mautic.slack.limit', $this->coreParametersHelper->getParameter('default_pagelimit'));
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $search = $this->request->get('search', $session->get('mautic.slack.filter', ''));
        $session->set('mautic.slack.filter', $search);

        $filter = ['string' => $search, 'force' => []];

        if (!$permissions['plugin:slack:viewother']) {
            $filter['force'][] =
                [
                    'column' => 's.createdBy',
                    'expr'   => 'eq',
                    'value'  => $this->user->getId(),
                ];
        }
        $listFilters['filters']['groups']['mautic.core.filter.category'] = [
            'options' => $this->getModel('category.category')->getLookupResults('plugin'),
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
        $orderBy    = $session->get('mautic.slack.orderby', 's.name');
        $orderByDir = $session->get('mautic.slack.orderbydir', 'DESC');

        $smss = $model->getEntities([
            'start'      => $start,
            'limit'      => $limit,
            'filter'     => $filter,
            'orderBy'    => $orderBy,
            'orderByDir' => $orderByDir,
        ]);

        $count = count($smss);
        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            if ($count === 1) {
                $lastPage = 1;
            } else {
                $lastPage = (floor($count / $limit)) ?: 1;
            }

            $session->set('mautic.slack.page', $lastPage);
            $returnUrl = $this->generateUrl('le_slack_index', ['page' => $lastPage]);

            return $this->postActionRedirect([
                'returnUrl'       => $returnUrl,
                'viewParameters'  => ['page' => $lastPage],
                'contentTemplate' => 'MauticPluginBundle:Slack:index',
                'passthroughVars' => [
                    'activeLink'    => '#le_slack_index',
                    'leContent'     => 'slack',
                ],
            ]);
        }
        $session->set('mautic.slack.page', $page);

        return $this->delegateView([
            'viewParameters' => [
                'searchValue'      => $search,
                'items'            => $smss,
                'totalItems'       => $count,
                'page'             => $page,
                'limit'            => $limit,
                'tmpl'             => $this->request->get('tmpl', 'index'),
                'permissions'      => $permissions,
                'model'            => $model,
                'security'         => $this->get('mautic.security'),
                'filters'          => $listFilters,
            ],
            'contentTemplate' => 'MauticPluginBundle:Slack:list.html.php',
            'passthroughVars' => [
                'activeLink'    => '#le_slack_index',
                'leContent'     => 'slack',
                'route'         => $this->generateUrl('le_slack_index', ['page' => $page]),
            ],
        ]);
    }

    /**
     * Generates new form and processes post data.
     *
     * @param Sms $entity
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction($entity = null)
    {
        /** @var \Mautic\PluginBundle\Model\SlackModel $model */
        $model = $this->getModel('plugin.slack');

        if (!$entity instanceof Slack) {
            /** @var \Mautic\PluginBundle\Entity\Slack $entity */
            $entity = $model->getEntity();
        }

        $method  = $this->request->getMethod();
        $session = $this->get('session');

        if (!$this->get('mautic.security')->isGranted('plugin:slack:create')) {
            return $this->accessDenied();
        }

        //set the page we came from
        $page   = $session->get('mautic.slack.page', 1);
        $action = $this->generateUrl('le_slack_action', ['objectAction' => 'new']);

        //create the form
        $form = $model->createForm($entity, $this->get('form.factory'), $action);

        ///Check for a submitted form and process it
        if ($method == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //form is valid so process the data
                    $model->saveEntity($entity);

                    $this->addFlash(
                        'mautic.core.notice.created',
                        [
                            '%name%'      => $entity->getName(),
                            '%menu_link%' => 'le_slack_index',
                            '%url%'       => $this->generateUrl(
                                'le_slack_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $entity->getId(),
                                ]
                            ),
                        ]
                    );

                    if ($form->get('buttons')->get('save')->isClicked()) {
                        $viewParameters = ['page' => $page];
                        $returnUrl      = $this->generateUrl('le_slack_index', $viewParameters);
                        $template       = 'MauticPluginBundle:Slack:index';
                    /* $viewParameters = [
                         'objectAction' => 'view',
                         'objectId'     => $entity->getId(),
                     ];
                     $returnUrl = $this->generateUrl('le_sms_action', $viewParameters);
                     $template  = 'MauticSmsBundle:Sms:view';*/
                    } else {
                        //return edit view so that all the session stuff is loaded
                        return $this->editAction($entity->getId(), true);
                    }
                }
            } else {
                $viewParameters = ['page' => $page];
                $returnUrl      = $this->generateUrl('le_slack_index', $viewParameters);
                $template       = 'MauticPluginBundle:Slack:index';
                //clear any modified content
                $session->remove('mautic.slack.'.$entity->getId().'.content');
            }

            $passthrough = [
                'activeLink'    => 'le_slack_index',
                'leContent'     => 'slack',
            ];

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $returnUrl,
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                        'passthroughVars' => $passthrough,
                    ]
                );
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'   => $form->createView(),
                    'slack'  => $entity,
                ],
                'contentTemplate' => 'MauticPluginBundle:Slack:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_slack_index',
                    'leContent'     => 'slack',
                    'route'         => $this->generateUrl(
                        'le_slack_action',
                        [
                            'objectAction' => 'new',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * @param      $objectId
     * @param bool $ignorePost
     * @param bool $forceTypeSelection
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction($objectId, $ignorePost = false, $forceTypeSelection = false)
    {
        /** @var \Mautic\PluginBundle\Model\SlackModel $model */
        $model   = $this->getModel('plugin.slack');
        $method  = $this->request->getMethod();
        $entity  = $model->getEntity($objectId);
        $session = $this->get('session');
        $page    = $session->get('mautic.slack.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('le_slack_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticPluginBundle:Slack:index',
            'passthroughVars' => [
                'activeLink'    => 'le_slack_index',
                'leContent'     => 'slack',
            ],
        ];

        //not found
        if ($entity === null) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'mautic.sms.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        } elseif (!$this->get('mautic.security')->hasEntityAccess(
            'plugin:slack:viewown',
            'plugin:slack:viewother',
            $entity->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        } elseif ($model->isLocked($entity)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $entity, 'sms');
        }

        //Create the form
        $action = $this->generateUrl('le_slack_action', ['objectAction' => 'edit', 'objectId' => $objectId]);

        $form = $model->createForm($entity, $this->get('form.factory'), $action);

        ///Check for a submitted form and process it
        if (!$ignorePost && $method == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //form is valid so process the data
                    $model->saveEntity($entity, $form->get('buttons')->get('save')->isClicked());

                    $this->addFlash(
                        'mautic.core.notice.updated',
                        [
                            '%name%'      => $entity->getName(),
                            '%menu_link%' => 'le_slack_index',
                            '%url%'       => $this->generateUrl(
                                'le_slack_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $entity->getId(),
                                ]
                            ),
                        ],
                        'warning'
                    );
                }
            } else {
                //clear any modified content
                $session->remove('mautic.slack.'.$objectId.'.content');
                //unlock the entity
                $model->unlockEntity($entity);
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect($postActionVars);
            }
        } else {
            //lock the entity
            $model->lockEntity($entity);
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'               => $form->createView(),
                    'slack'              => $entity,
                ],
                'contentTemplate' => 'MauticPluginBundle:Slack:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_slack_index',
                    'leContent'     => 'slack',
                    'route'         => $this->generateUrl(
                        'le_slack_action',
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
     * @param $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function cloneAction($objectId)
    {
        $model  = $this->getModel('plugin.slack');
        $entity = $model->getEntity($objectId);

        if ($entity != null) {
            if (!$this->get('mautic.security')->isGranted('plugin:slack:create')
                || !$this->get('mautic.security')->hasEntityAccess(
                    'plugin:slack:viewown',
                    'plugin:slack:viewother',
                    $entity->getCreatedBy()
                )
            ) {
                return $this->accessDenied();
            }

            $entity = clone $entity;
        }

        return $this->newAction($entity);
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
        $page      = $this->get('session')->get('mautic.slack.page', 1);
        $returnUrl = $this->generateUrl('le_slack_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticPluginBundle:Slack:index',
            'passthroughVars' => [
                'activeLink'    => 'le_slack_index',
                'leContent'     => 'slack',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model  = $this->getModel('plugin.slack');
            $entity = $model->getEntity($objectId);

            if ($entity === null) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'mautic.sms.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('mautic.security')->hasEntityAccess(
                'plugin:slack:deleteown',
                'plugin:slack:deleteother',
                $entity->getCreatedBy()
            )
            ) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'slack');
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
                ['flashes' => $flashes]
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
        $page      = $this->get('session')->get('mautic.slack.page', 1);
        $returnUrl = $this->generateUrl('le_slack_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticPluginBundle:Slack:index',
            'passthroughVars' => [
                'activeLink'    => '#le_slack_index',
                'leContent'     => 'slack',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model = $this->getModel('slack');
            $ids   = json_decode($this->request->query->get('ids', '{}'));

            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if ($entity === null) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'mautic.sms.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('mautic.security')->hasEntityAccess(
                    'plugin:slack:viewown',
                    'plugin:slack:viewother',
                    $entity->getCreatedBy()
                )
                ) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'slack', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'mautic.sms.notice.batch_deleted',
                    'msgVars' => [
                        '%count%' => count($entities),
                    ],
                ];
            }
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                ['flashes' => $flashes]
            )
        );
    }
}
