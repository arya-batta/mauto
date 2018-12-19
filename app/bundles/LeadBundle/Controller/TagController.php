<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 15/12/18
 * Time: 12:07 PM
 */

namespace Mautic\LeadBundle\Controller;


use Doctrine\ORM\EntityNotFoundException;
use Mautic\CoreBundle\Controller\FormController;
use Mautic\LeadBundle\Model\TagModel;
use Mautic\LeadBundle\Entity\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
/**
 * Class TagController
 * @package Mautic\LeadBundle\Controller
 */
class TagController extends FormController
{
    /**
     * @param int $page
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = 1)
    {
        /** @var TagModel $model */
        $model   = $this->getModel('lead.tag');
        $session = $this->get('session');

        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted([
            'lead:tags:full',
        ], 'RETURN_ARRAY');

        //Lists can be managed by anyone who has access to leads
        if (!$permissions['lead:tags:full']) {
            return $this->accessDenied();
        }

        if ($this->request->getMethod() == 'POST') {
            $this->setListFilters();
        }

        //set limits
        $limit = $session->get('mautic.tags.limit', $this->coreParametersHelper->getParameter('default_pagelimit'));
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $search = $this->request->get('search', $session->get('mautic.tags.filter', ''));
        $session->set('mautic.tags.filter', $search);

        //do some default filtering
        $orderBy    = $session->get('mautic.tags.orderby', 't.tag');
        $orderByDir = $session->get('mautic.tags.orderbydir', 'ASC');


        $filter = [
            'string' => $search,
            'where'  => [
                [
                    'expr' => 'like',
                    'col'  => 't.tag',
                    'val'  => '%'.$search.'%',
                ],
            ],
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
        $count =count($items);
        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            if ($count === 1) {
                $lastPage = 1;
            } else {
                $lastPage = (ceil($count / $limit)) ?: 1;
            }
            $session->set('mautic.tags.page', $lastPage);
            $returnUrl = $this->generateUrl('le_tags_index', ['page' => $lastPage]);

            return $this->postActionRedirect([
                'returnUrl'      => $returnUrl,
                'viewParameters' => [
                    'page' => $lastPage,
                    'tmpl' => $tmpl,
                ],
                'contentTemplate' => 'MauticLeadBundle:tag:index',
                'passthroughVars' => [
                    'activeLink'    => '#le_tags_index',
                    'leContent' => 'tag',
                ],
            ]);
        }

        //set what page currently on so that we can return here after form submission/cancellation
        $session->set('mautic.tags.page', $page);

        $tagIds    = array_keys($items->getIterator()->getArrayCopy());
        $leadCounts = (!empty($tagIds)) ? $model->getRepository()->getLeadCount($tagIds) : [];

        $allBlockDetails   = $model->getTagBlocks();

        $parameters = [
            'items'           => $items,
            'leadCounts'      => $leadCounts,
            'page'            => $page,
            'limit'           => $limit,
            'permissions'     => $permissions,
            'security'        => $this->get('mautic.security'),
            'tmpl'            => $tmpl,
            'currentUser'     => $this->user,
            'searchValue'     => $search,
            'filter'          => $filter,
            'allBlockDetails' => $allBlockDetails,
        ];

        return $this->delegateView([
            'viewParameters'  => $parameters,
            'contentTemplate' => 'MauticLeadBundle:Tag:list.html.php',
            'passthroughVars' => [
                'activeLink'    => '#le_tags_index',
                'route'         => $this->generateUrl('le_tags_index', ['page' => $page]),
                'leContent' => 'tag',
            ],
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(){
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            $this->redirectToPricing();
        }
        /** @var TagModel $model */
        $model     = $this->getModel('lead.tag');

        $tag = new Tag();
        $tag->setIsPublished(1);
        $action    = $this->generateUrl('le_tags_action', ['objectAction' => 'new']);
        $form      = $model->createForm($tag, $this->get('form.factory'), $action,['isNew' => true]);
        //set the page we came from
        $page = $this->get('session')->get('mautic.tags.page', 1);
        //set the return URL
        $returnUrl = $this->generateUrl('le_tags_index', ['page' => $page]);
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticLeadBundle:Tag:index',
            'passthroughVars' => [
                'activeLink'    => '#le_tags_index',
                'leContent'     => 'tag',
                'closeModal'    => 1,
            ],
        ];
        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $model->saveEntity($tag);
                    $this->addFlash('mautic.core.notice.created', [
                        '%name%'      => $tag->getTag(),
                        '%menu_link%' => 'le_tags_index',
                        '%url%'       => $this->generateUrl('le_tags_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $tag->getId(),
                        ]),
                    ]);
                    return $this->postActionRedirect($postActionVars);
                }
            }else{
                return $this->postActionRedirect($postActionVars);
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'        => $form->createView(),
                    'dripemail'   => $tag,
                ],
                'contentTemplate' => 'MauticLeadBundle:Tag:quickadd.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_tags_index',
                    'leContent'     => 'tag',
                    'route'         => $this->generateUrl(
                        'le_tags_action',
                        [
                            'objectAction' => 'new',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * @param $objectId
     * @param bool $ignorePost
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($objectId, $ignorePost = false){
        /** @var TagModel $model */
        $model = $this->getModel('lead.tag');
        $tag  = $model->getEntity($objectId);
        if ($this->get('mautic.helper.licenseinfo')->redirectToSubscriptionpage()) {
            return $this->delegateRedirect($this->generateUrl('le_pricing_index'));
        }
        //set the page we came from
        $page = $this->get('session')->get('mautic.tags.page', 1);
        //set the return URL
        $returnUrl = $this->generateUrl('le_tags_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticLeadBundle:Tag:index',
            'passthroughVars' => [
                'activeLink'    => '#le_tags_index',
                'leContent'     => 'tag',
                'closeModal'    => 1,
            ],
        ];
        $action = $this->generateUrl('le_tags_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $form   = $model->createForm($tag, $this->get('form.factory'), $action,['isNew' => false]);
        if (!$ignorePost && $this->request->getMethod() == 'POST') {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($this->isFormValid($form)) {
                    //form is valid so process the data
                    $model->saveEntity($tag);

                    $this->addFlash('mautic.core.notice.updated', [
                        '%name%'      => $tag->getTag(),
                        '%menu_link%' => 'le_tags_index',
                        '%url%'       => $this->generateUrl('le_tags_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $tag->getId(),
                        ]),
                    ]);
                   // $returnUrl      = $this->generateUrl('le_tags_index');
                    return $this->postActionRedirect($postActionVars);
                }
            }else{
                return $this->postActionRedirect($postActionVars);
            }
        }
        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'        => $form->createView(),
                ],
                'contentTemplate' => 'MauticLeadBundle:Tag:quickadd.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_tags_index',
                    'leContent'     => 'tag',
                    'route'         => $this->generateUrl(
                        'le_tags_action',
                        [
                            'objectAction' => 'edit',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * @param $objectId
     * @return array|JsonResponse|RedirectResponse
     */
    public function deleteAction($objectId){
        $page      = $this->get('session')->get('mautic.tags.page', 1);
        $returnUrl = $this->generateUrl('le_tags_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticLeadBundle:Tag:index',
            'passthroughVars' => [
                'activeLink'    => '#le_tags_index',
                'leContent' => 'tag',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            /** @var TagModel $model */
            $model = $this->getModel('lead.tag');
            $tag  = $model->getEntity($objectId);

            if ($tag === null) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'le.lead.tag.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('mautic.security')->hasEntityAccess(
                true, 'lead:tags:full')
            ) {
                return $this->accessDenied();
            } elseif ($model->isLocked($tag)) {
                return $this->isLocked($postActionVars, $tag, 'lead.tag');
            }
            $repo=$model->getRepository();
            $repo->deleteRefLead($objectId);
            $model->deleteEntity($tag);

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'mautic.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $tag->getTag(),
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
     * @return JsonResponse|RedirectResponse
     */
    public function batchDeleteAction(){
        $page      = $this->get('session')->get('mautic.tags.page', 1);
        $returnUrl = $this->generateUrl('le_tags_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticLeadBundle:Tag:index',
            'passthroughVars' => [
                'activeLink'    => '#le_tags_index',
                'leContent' => 'tag',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            /** @var TagModel $model */
            $model     = $this->getModel('lead.tag');
            $ids       = json_decode($this->request->query->get('ids', '{}'));
            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);
                $repo=$model->getRepository();
                $repo->deleteRefLead($objectId);
                if ($entity === null) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'le.lead.tags.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('mautic.security')->hasEntityAccess(
                    true, 'lead:tags:full')
                ) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'lead.tag', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'le.lead.tags.notice.batch_deleted',
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