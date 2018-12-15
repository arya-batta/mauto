<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Mautic\CoreBundle\Controller\FormController;
use Mautic\LeadBundle\Entity\LeadList;
use Mautic\LeadBundle\Entity\LeadListOptIn;
use Mautic\LeadBundle\Model\ListOptInModel;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ListOptInController extends FormController
{
    use EntityContactsTrait;

    /**
     * Generate's default list view.
     *
     * @param int $page
     *
     * @return JsonResponse | Response
     */
    public function indexAction($page = 1)
    {
        /** @var ListOptInModel $model */
        $model   = $this->getModel('lead.listoptin');
        $session = $this->get('session');

        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted([
            'lead:leads:viewown',
            'lead:leads:viewother',
            'lead:listoptin:viewother',
            'lead:listoptin:editother',
            'lead:listoptin:deleteother',
        ], 'RETURN_ARRAY');

        //Lists can be managed by anyone who has access to leads
        if (!$permissions['lead:leads:viewown'] && !$permissions['lead:leads:viewother']) {
            return $this->accessDenied();
        }

        if ($this->request->getMethod() == 'POST') {
            $this->setListFilters();
        }

        //set limits
        $limit = $session->get('le.listoptin.limit', $this->coreParametersHelper->getParameter('default_pagelimit'));
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $search = $this->request->get('search', $session->get('le.listoptin.filter', ''));
        $session->set('le.listoptin.filter', $search);

        //do some default filtering
        $orderBy    = $session->get('le.listoptin.orderby', 'l.name');
        $orderByDir = $session->get('le.listoptin.orderbydir', 'ASC');

        $filter = [
            'string' => $search,
            'force'  => [],
        ];

        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';

        $items = $model->getEntities(
            [
                'start'      => $start,
                'limit'      => $limit,
                'filter'     => $filter,
                'orderBy'    => $orderBy,
                'orderByDir' => $orderByDir,
            ]);

        $count = count($items);

        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            if ($count === 1) {
                $lastPage = 1;
            } else {
                $lastPage = (ceil($count / $limit)) ?: 1;
            }
            $session->set('le.listoptin.page', $lastPage);
            $returnUrl = $this->generateUrl('le_listoptin_index', ['page' => $lastPage]);

            return $this->postActionRedirect([
                'returnUrl'      => $returnUrl,
                'viewParameters' => [
                    'page' => $lastPage,
                    'tmpl' => $tmpl,
                ],
                'contentTemplate' => 'MauticLeadBundle:ListOptIn:index',
                'passthroughVars' => [
                    'activeLink'    => '#le_listoptin_index',
                    'leContent'     => 'listoptin',
                ],
            ]);
        }

        //set what page currently on so that we can return here after form submission/cancellation
        $session->set('le.listoptin.page', $page);

        $listIds            = array_keys($items->getIterator()->getArrayCopy());
        $leadCounts         = (!empty($listIds)) ? $model->getRepository()->getLeadCount($listIds) : [];
        $confirmedCounts    = (!empty($listIds)) ? $model->getRepository()->getStatusWiseLeadCount($listIds, 'confirmed_lead') : [];
        $unConfirmedCounts  = (!empty($listIds)) ? $model->getRepository()->getStatusWiseLeadCount($listIds, 'unconfirmed_lead') : [];
        $unSubscribedCounts = (!empty($listIds)) ? $model->getRepository()->getStatusWiseLeadCount($listIds, 'unsubscribed_lead') : [];

        $allBlockDetails   = $model->getListOptinsBlocks();

        $parameters = [
            'items'              => $items,
            'leadCounts'         => $leadCounts,
            'confirmedCounts'    => $confirmedCounts,
            'unConfirmedCounts'  => $unConfirmedCounts,
            'unSubscribedCounts' => $unSubscribedCounts,
            'page'               => $page,
            'limit'              => $limit,
            'permissions'        => $permissions,
            'security'           => $this->get('mautic.security'),
            'tmpl'               => $tmpl,
            'currentUser'        => $this->user,
            'searchValue'        => $search,
            'allBlockDetails'    => $allBlockDetails,
        ];

        return $this->delegateView([
            'viewParameters'  => $parameters,
            'contentTemplate' => 'MauticLeadBundle:ListOptIn:list.html.php',
            'passthroughVars' => [
                'activeLink'    => '#le_listoptin_index',
                'route'         => $this->generateUrl('le_listoptin_index', ['page' => $page]),
                'leContent'     => 'listoptin',
            ],
        ]);
    }

    /**
     * Generate's new form and processes post data.
     *
     * @return JsonResponse | RedirectResponse | Response
     */
    public function newAction()
    {
        if (!$this->get('mautic.security')->isGranted('lead:leads:viewown')) {
            return $this->accessDenied();
        }

        //retrieve the entity
        $list = new LeadListOptIn();
        /** @var ListOptInModel $model */
        $model = $this->getModel('lead.listoptin');
        //set the page we came from
        $page = $this->get('session')->get('le.listoptin.page', 1);
        //set the return URL for post actions
        $returnUrl = $this->generateUrl('le_listoptin_index', ['page' => $page]);
        $action    = $this->generateUrl('le_listoptin_action', ['objectAction' => 'new']);
        $list->setThankyou(false);
        $list->setGoodbye(false);
        //get the user form factory
        $list->setCreatedBy($this->user);
        $list->setFooterText($this->translator->trans('le.lead.list.optin.default.footer_text'));
        $form = $model->createForm($list, $this->get('form.factory'), $action);
        ///Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                $formData = $this->request->request->get('leadlistoptin');
                if (!empty($formData['footerText'])) {
                    $list->setFooterText($formData['footerText']);
                }
                if ($valid = $this->isFormValid($form) && $this->validateListoptinForm($form)) {
                    //form is valid so process the data
                    $model->saveEntity($list);

                    $this->addFlash('mautic.core.notice.created', [
                        '%name%'      => $list->getName(),
                        '%menu_link%' => 'le_listoptin_index',
                        '%url%'       => $this->generateUrl('le_listoptin_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $list->getId(),
                        ]),
                    ]);
                }
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->indexAction();
            /*return $this->postActionRedirect([
                'returnUrl'       => $returnUrl,
                'viewParameters'  => ['page' => $page],
                'contentTemplate' => 'MauticLeadBundle:List:index',
                'passthroughVars' => [
                    'activeLink'    => '#le_segment_index',
                    'leContent' => 'leadlist',
                ],
            ]);*/
            } elseif ($valid && !$cancelled) {
                return $this->editAction($list->getId(), true);
            }
        }

        return $this->delegateView([
            'viewParameters' => [
                'form'   => $form->createView(),
                'entity' => $list,
            ],
            'contentTemplate' => 'MauticLeadBundle:ListOptIn:form.html.php',
            'passthroughVars' => [
                'activeLink'    => '#le_listoptin_index',
                'route'         => $this->generateUrl('le_listoptin_action', ['objectAction' => 'new']),
                'leContent'     => 'listoptin',
            ],
        ]);
    }

    /**
     * Generate's clone form and processes post data.
     *
     * @param int  $objectId
     * @param bool $ignorePost
     *
     * @return Response
     */
    public function cloneAction($objectId, $ignorePost = false)
    {
        $postActionVars = $this->getPostActionVars();

        try {
            $list = $this->getListOptin($objectId);

            return $this->createSegmentModifyResponse(
                clone $list,
                $postActionVars,
                $this->generateUrl('le_listoptin_action', ['objectAction' => 'clone', 'objectId' => $objectId]),
                $ignorePost
            );
        } catch (AccessDeniedException $exception) {
            return $this->accessDenied();
        } catch (EntityNotFoundException $exception) {
            return $this->postActionRedirect(
                array_merge($postActionVars, [
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'le.lead.list.optin.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ])
            );
        }
    }

    /**
     * Generate's edit form and processes post data.
     *
     * @param int  $objectId
     * @param bool $ignorePost
     *
     * @return Response
     */
    public function editAction($objectId, $ignorePost = false)
    {
        $postActionVars = $this->getPostActionVars();

        try {
            $list = $this->getListOptin($objectId);

            return $this->createSegmentModifyResponse(
                $list,
                $postActionVars,
                $this->generateUrl('le_listoptin_action', ['objectAction' => 'edit', 'objectId' => $objectId]),
                $ignorePost
            );
        } catch (AccessDeniedException $exception) {
            return $this->accessDenied();
        } catch (EntityNotFoundException $exception) {
            return $this->postActionRedirect(
                array_merge($postActionVars, [
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'le.lead.list.optin.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ])
            );
        }
    }

    /**
     * Create modifying response for segments - edit/clone.
     *
     * @param LeadListOptIn $list
     * @param array         $postActionVars
     * @param string        $action
     * @param bool          $ignorePost
     *
     * @return Response
     */
    private function createSegmentModifyResponse(LeadListOptIn $list, array $postActionVars, $action, $ignorePost)
    {
        /** @var ListOptInModel $listmodel */
        $listmodel = $this->getModel('lead.listoptin');

        if ($listmodel->isLocked($list)) {
            return $this->isLocked($postActionVars, $list, 'lead.listoptin');
        }

        /** @var FormInterface $form */
        $form = $listmodel->createForm($list, $this->get('form.factory'), $action);

        ///Check for a submitted form and process it
        if (!$ignorePost && $this->request->getMethod() == 'POST') {
            if (!$cancelled = $this->isFormCancelled($form)) {
                $formData = $this->request->request->get('leadlistoptin');
                if (!empty($formData['footerText'])) {
                    file_put_contents('/var/www/log.txt', $formData['footerText']."\n", FILE_APPEND);
                    $list->setFooterText($formData['footerText']);
                }
                if ($this->isFormValid($form) && $this->validateListoptinForm($form)) {
                    //form is valid so process the data
                    $listmodel->saveEntity($list, $form->get('buttons')->get('save')->isClicked());

                    $this->addFlash('mautic.core.notice.updated', [
                        '%name%'      => $list->getName(),
                        '%menu_link%' => 'le_listoptin_index',
                        '%url%'       => $this->generateUrl('le_listoptin_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $list->getId(),
                        ]),
                    ]);

                    if ($form->get('buttons')->get('apply')->isClicked()) {
                        $contentTemplate                     = 'MauticLeadBundle:ListOptIn:form.html.php';
                        $postActionVars['contentTemplate']   = $contentTemplate;
                        $postActionVars['forwardController'] = false;
                        $postActionVars['returnUrl']         = $this->generateUrl('le_listoptin_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $list->getId(),
                        ]);

                        // Re-create the form once more with the fresh segment and action.
                        // The alias was empty on redirect after cloning.
                        $editAction = $this->generateUrl('le_listoptin_action', ['objectAction' => 'edit', 'objectId' => $list->getId()]);
                        $form       = $listmodel->createForm($list, $this->get('form.factory'), $editAction);

                        $postActionVars['viewParameters'] = [
                            'objectAction' => 'edit',
                            'objectId'     => $list->getId(),
                            'form'         => $form->createView(),
                            'entity'       => $list,
                        ];

                        return $this->postActionRedirect($postActionVars);
                    } else {
                        return $this->indexAction();
                    }
                }
            } else {
                //unlock the entity
                $listmodel->unlockEntity($list);
            }

            if ($cancelled) {
                return $this->postActionRedirect($postActionVars);
            }
        } else {
            //lock the entity
            $listmodel->lockEntity($list);
        }

        return $this->delegateView([
            'viewParameters' => [
                'form'          => $form->createView(),
                'currentListId' => $list->getId(),
                'entity'        => $list,
            ],
            'contentTemplate' => 'MauticLeadBundle:ListOptIn:form.html.php',
            'passthroughVars' => [
                'activeLink'    => '#le_listoptin_index',
                'route'         => $action,
                'leContent'     => 'listoptin',
            ],
        ]);
    }

    /**
     * Return segment if exists and user has access.
     *
     * @param int $optinId
     *
     * @return LeadListOptIn
     *
     * @throws EntityNotFoundException
     * @throws AccessDeniedException
     */
    private function getListOptin($optinId)
    {
        /** @var LeadListOptIn $segment */
        $listoptin = $this->getModel('lead.listoptin')->getEntity($optinId);

        // Check if exists
        if (!$listoptin instanceof LeadListOptIn) {
            throw new EntityNotFoundException(sprintf('Segment with id %d not found.', $optinId));
        }

        if (!$this->get('mautic.security')->hasEntityAccess(
            true, 'lead:listoptin:editother', $listoptin->getCreatedBy()
        )) {
            throw new AccessDeniedException(sprintf('User has not access on segment with id %d', $optinId));
        }

        return $listoptin;
    }

    /**
     * Get variables for POST action.
     *
     * @return array
     */
    private function getPostActionVars()
    {
        //set the page we came from
        $page = $this->get('session')->get('le.listoptin.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('le_listoptin_index', ['page' => $page]);

        return [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticLeadBundle:ListOptIn:index',
            'passthroughVars' => [
                'activeLink'    => '#le_listoptin_index',
                'leContent'     => 'listoptin',
            ],
        ];
    }

    /**
     * Delete a list.
     *
     * @param   $objectId
     *
     * @return JsonResponse | RedirectResponse
     */
    public function deleteAction($objectId)
    {
        $page      = $this->get('session')->get('le.listoptin.page', 1);
        $returnUrl = $this->generateUrl('le_listoptin_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticLeadBundle:ListOptIn:index',
            'passthroughVars' => [
                'activeLink'    => '#le_listoptin_index',
                'leContent'     => 'listoptin',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            /** @var ListOptInModel $model */
            $model = $this->getModel('lead.listoptin');
            $list  = $model->getEntity($objectId);

            if ($list === null) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'le.lead.list.optin.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('mautic.security')->hasEntityAccess(
                true, 'lead:listoptin:deleteother', $list->getCreatedBy()
            )
            ) {
                return $this->accessDenied();
            } elseif ($model->isLocked($list)) {
                return $this->isLocked($postActionVars, $list, 'lead.listoptin');
            }

            $model->deleteEntity($list);

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'mautic.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $list->getName(),
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
     * @return JsonResponse | RedirectResponse
     */
    public function batchDeleteAction()
    {
        $page      = $this->get('session')->get('le.listoptin.page', 1);
        $returnUrl = $this->generateUrl('le_listoptin_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticLeadBundle:ListOptIn:index',
            'passthroughVars' => [
                'activeLink'    => '#le_listoptin_index',
                'leContent'     => 'listoptin',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            /** @var ListOptInModel $model */
            $model     = $this->getModel('lead.listoptin');
            $ids       = json_decode($this->request->query->get('ids', '{}'));
            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if ($entity === null) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'le.lead.list.optin.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('mautic.security')->hasEntityAccess(
                    true, 'lead:listoptin:deleteother', $entity->getCreatedBy()
                )) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'lead.listoptin', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'le.lead.list.notice.batch_deleted',
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

    public function validateListoptinForm($form)
    {
        $isValidForm = true;
        $formData    = $this->request->request->get('leadlistoptin'); //$form->getData();
        if ($formData['thankyou'] && empty($formData['thankyouemail'])) {
            $isValidForm = false;
            $form['thankyouemail']->addError(new FormError($this->translator->trans('le.lead.list.optin.required', [])));
        }
        if ($formData['goodbye'] && empty($formData['goodbyeemail'])) {
            $isValidForm = false;
            $form['goodbyeemail']->addError(new FormError($this->translator->trans('le.lead.list.optin.required', [])));
        }
        if ($formData['listtype'] != 'single' && empty($formData['doubleoptinemail'])) {
            $isValidForm = false;
            $form['doubleoptinemail']->addError(new FormError($this->translator->trans('le.lead.list.optin.required', [])));
        }
        /*if ($eventType == 'source' && $type == 'pagehit') {
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
        }*/

        return $isValidForm;
    }
}
