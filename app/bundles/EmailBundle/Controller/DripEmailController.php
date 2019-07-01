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
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\EmailBundle\Entity\DripEmail;
use Mautic\EmailBundle\Model\DripEmailModel;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\LeadBundle\Controller\EntityContactsTrait;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\SubscriptionBundle\Entity\Account;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DripEmailController extends FormController
{
    use BuilderControllerTrait;
    use FormErrorMessagesTrait;
    use EntityContactsTrait;

    /**
     * Executes an action defined in route.
     *
     * @param int    $objectId
     * @param string $subobjectAction
     * @param int    $subobjectId
     * @param string $objectModel
     *
     * @return array|JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function executeDripAction($objectId = 0, $subobjectAction, $subobjectId = 0, $objectModel = '')
    {
        if (method_exists($this, "email{$subobjectAction}Action")) {
            return $this->{"email{$subobjectAction}Action"}($subobjectId, $objectId, $objectModel);
        }

        return $this->notFound();
    }

    /**
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = 1)
    {
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }

        /** @var DripEmailModel $model */
        $model = $this->getModel('email.dripemail');

        /** @var DripEmailModel $model */
        $emailmodel = $this->getModel('email');

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

        $listFilters = [
            'filters' => [
                'placeholder' => $this->get('translator')->trans('le.drip.email.filter.category.placeholder'),
                'multiple'    => true,
            ],
        ];

        // Reset available groups
        $listFilters['filters']['groups'] = [];

        $currentFilters = $session->get('le.dripemail.list_filters', []);
        $updatedFilters = $this->request->get('filters', false);
        $ignoreListJoin = true;

        //retrieve a titles of Category
        $listFilters['filters']['groups']['mautic.core.filter.category'] = [
            'options' => $this->getModel('category.category')->getLookupResults('dripemail'),
            'prefix'  => 'category',
            'values'  => empty($currentFilters) ? [] : array_values($currentFilters['category']),
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

        $session->set('le.dripemail.list_filters', $currentFilters);
        $search = $this->request->get('search', $session->get('mautic.dripemail.filter', ''));
        $session->set('mautic.dripemail.filter', $search);
        $filter = [
            'string' => $search,
            'force'  => [],
        ];
        if (!$permissions['dripemail:emails:viewother']) {
            $filter['force'][] =
                ['column' => 'd.createdBy', 'expr' => 'eq', 'value' => $this->user->getId()];
        }

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

        $ignoreListJoin = true;

        $orderBy    = $session->get('mautic.dripemail.orderby', 'd.subject');
        $orderByDir = $session->get('mautic.dripemail.orderbydir', 'DESC');

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

        $dripEmailBlockDetails = $model->getDripEmailBlocks();

        $emailscount = $emailmodel->getRepository()->getDripEmailCount();
        $ismobile    = InputHelper::isMobile();

        return $this->delegateView(
            [
                'viewParameters' => [
                    'searchValue'          => $search,
                    'filters'              => $listFilters,
                    'items'                => $emails,
                    'totalItems'           => $count,
                    'page'                 => $page,
                    'limit'                => $limit,
                    'tmpl'                 => $this->request->get('tmpl', 'index'),
                    'permissions'          => $permissions,
                    'model'                => $model,
                    'actionRoute'          => 'le_dripemail_campaign_action',
                    'indexRoute'           => 'le_dripemail_index',
                    'headerTitle'          => 'le.email.drip.email.menu',
                    'translationBase'      => 'mautic.email.broadcast',
                    'dripEmailBlockDetails'=> $dripEmailBlockDetails,
                    'EmailsCount'          => $emailscount,
                    'ismobile'             => $ismobile,
                ],
                'contentTemplate' => 'MauticEmailBundle:DripEmail:list.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_dripemail_index',
                    'leContent'     => 'dripemail',
                    'route'         => $this->generateUrl('le_dripemail_index', ['page' => $page]),
                ],
            ]
        );
    }

    /**
     * Loads a specific form into the detailed panel.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($objectId)
    {
        /** @var \Mautic\EmailBundle\Model\DripEmailModel $model */
        $model    = $this->getModel('email.dripemail');
        /** @var \Mautic\EmailBundle\Model\EmailModel $emailmodel */
        $emailmodel    = $this->getModel('email');
        $security      = $this->get('mautic.security');
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }
        /** @var \Mautic\EmailBundle\Entity\DripEmail $dripemail */
        $dripemail = $model->getEntity($objectId);
        //set the page we came from
        $page = $this->get('session')->get('mautic.dripemail.page', 1);

        // Init the date range filter form
        $dateRangeValues = $this->request->get('daterange', []);
        $action          = $this->generateUrl('le_dripemail_campaign_action', ['objectAction' => 'view', 'objectId' => $objectId]);
        $dateRangeForm   = $this->get('form.factory')->create('daterange', $dateRangeValues, ['action' => $action]);

        if ($dripemail->getId() == '') {
            //set the return URL
            $returnUrl = $this->generateUrl('le_dripemail_index', ['page' => $page]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'MauticEmailBundle:DripEmail:index',
                    'passthroughVars' => [
                        'activeLink'    => '#le_dripemail_index',
                        'leContent'     => 'dripemail',
                    ],
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'le.drip.email.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ]
            );
        } elseif (!$this->get('mautic.security')->hasEntityAccess(
            'email:emails:viewown',
            'email:emails:viewother',
            $dripemail->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        }

        $emailIds = $model->getRepository()->getEmailIdsByDrip($dripemail->getId());
        $stats    = [];
        if (!empty($emailIds)) {
            $stats = $emailmodel->getDripEmailGeneralStats(
                $emailIds,
                null,
                new \DateTime($dateRangeForm->get('date_from')->getData()),
                new \DateTime($dateRangeForm->get('date_to')->getData())
            );
        }

        // Audit Log
        $logs                   = $this->getModel('core.auditLog')->getLogForObject('dripemail', $dripemail->getId(), $dripemail->getDateAdded());
        $emailStats             = $model->getCustomEmailStats($dripemail);
        $last10openleads        = [];
        $last10clickleads       = [];
        $last10unsubscribeleads = [];
        $last10bounceleads      = [];
        $last10churns           = [];
        $last10fails            = [];

        $emails = $emailmodel->getEntities(
            [
                'filter' => [
                    'force' => [
                        [
                            'column' => 'e.dripEmail',
                            'expr'   => 'eq',
                            'value'  => $dripemail,
                        ],
                    ],
                ],
                'orderBy'          => 'e.dripEmailOrder',
                'orderByDir'       => 'asc',
                'ignore_paginator' => true,
            ]
        );
        if (count($emails)) {
            $last10openleads        = $emailmodel->getLeadsBasedonAction($this->translator->trans('le.lead.drip.searchcommand.read').':'.$dripemail->getId());
            $last10clickleads       = $emailmodel->getLeadsBasedonAction($this->translator->trans('le.lead.drip.searchcommand.click').':'.$dripemail->getId());
            $last10unsubscribeleads = $emailmodel->getLeadsBasedonAction($this->translator->trans('le.lead.drip.searchcommand.unsubscribe').':'.$dripemail->getId());
            $last10bounceleads      = $emailmodel->getLeadsBasedonAction($this->translator->trans('le.lead.drip.searchcommand.bounce').':'.$dripemail->getId());
            $last10churns           = $emailmodel->getLeadsBasedonAction($this->translator->trans('le.lead.drip.searchcommand.churn').':'.$dripemail->getId());
            $last10fails            = $emailmodel->getLeadsBasedonAction($this->translator->trans('le.lead.drip.searchcommand.failed').':'.$dripemail->getId());
        }

        return $this->delegateView(
            [
                'returnUrl' => $this->generateUrl(
                    'le_dripemail_campaign_action',
                    [
                        'objectAction' => 'view',
                        'objectId'     => $dripemail->getId(),
                    ]
                ),
                'viewParameters' => [
                    'entity'       => $dripemail,
                    'stats'        => $stats,
                    'logs'         => $logs,
                    'permissions'  => $security->isGranted(
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
                    ),
                    'security'      => $security,
                    'contacts'      => $this->forward(
                        'MauticEmailBundle:DripEmail:contacts',
                        [
                            'objectId'   => $dripemail->getId(),
                            'page'       => $this->get('session')->get('mautic.dripemail.contact.page', 1),
                            'ignoreAjax' => true,
                        ]
                    )->getContent(),
                    'dateRangeForm'    => $dateRangeForm->createView(),
                    'actionRoute'      => 'le_dripemail_campaign_action',
                    'indexRoute'       => 'le_dripemail_index',
                    'notificationemail'=> false,
                    'emailStats'       => $emailStats,
                    'openLeads'        => $last10openleads,
                    'clickLeads'       => $last10clickleads,
                    'unsubscribeLeads' => $last10unsubscribeleads,
                    'bounceLeads'      => $last10bounceleads,
                    'emailList'        => $emails,
                    'emailChurn'       => $last10churns,
                    'emailfailed'      => $last10fails,
                ],
                'contentTemplate' => 'MauticEmailBundle:DripEmail:details.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_dripemail_index',
                    'leContent'     => 'dripemail',
                ],
            ]
        );
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
            ['dripemail:emails:viewown', 'dripemail:emails:viewother'],
            'dripemail',
            'dripemail_leads',
            null,
            'dripemail_id'
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
                    $entity->setScheduleDate(null);
                    $entity->setIsPublished(true);
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
                    'leContent'     => 'dripemail',
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
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }
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
        $configurator     = $this->get('mautic.configurator');
        $params           = $configurator->getParameters();
        $fromname         = $params['mailer_from_name'];
        $fromadress       = $params['mailer_from_email'];
        $unsubscribetxt   = isset($params['unsubscribe_text']) ? $params['unsubscribe_text'] : '';
        $postaladdress    = $params['postal_address'];
        $unsubscribeTxt   = $entity->getUnsubscribeText();
        $postalAddress    = $entity->getPostalAddress();
        /** @var EmailModel $emailmodel */
        $emailmodel   = $this->getModel('email');
//        $fromname     ='';
//        $fromadress   ='';
//        $defaultsender=$emailmodel->getDefaultSenderProfile();
//        if (sizeof($defaultsender) > 0) {
//            $fromname  =$defaultsender[0];
//            $fromadress=$defaultsender[1];
//        }
        $fromName        = $entity->getFromName();
        $fromAdress      = $entity->getFromAddress();
        /** @var \Mautic\EmailBundle\Entity\Email $emailentity */
        $emailentity = $emailmodel->getEntity();
        $emailentity->setEmailType('list');
        $emailaction = $this->generateUrl('le_email_campaign_action', ['objectAction' => 'new']);
        //create the form
        $emailform      = $emailmodel->createForm($emailentity, $this->get('form.factory'), $emailaction, ['update_select' => false, 'isEmailTemplate' => true, 'isDripEmail' => true, 'isShortForm' => false]);
        $returnUrl      = $this->generateUrl('le_dripemail_index');
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => 1],
            'contentTemplate' => 'MauticEmailBundle:DripEmail:index',
            'passthroughVars' => [
                'activeLink'    => 'le_dripemail_index',
                'leContent'     => 'dripemail',
            ],
        ];
        if (empty($fromName)) {
            $entity->setFromName($fromname);
        }
        if (empty($fromAdress)) {
            $entity->setFromAddress($fromadress);
        }
        if (empty($unsubscribeTxt)) {
            $entity->setUnsubscribeText($unsubscribetxt);
        }
        if (empty($postalAddress)) {
            $entity->setPostalAddress($postaladdress);
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
        $signuprepo     = $this->factory->get('le.core.repository.signup');
        $bluePrints     = $signuprepo->getBluePrintCampaigns();
        $dripPrints     = $signuprepo->getDripEmails();
        $template       = 'MauticEmailBundle:DripEmail:view';
        $viewParameters = [
        ];
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
                    $scheduleDate = $formData['scheduleDate'];
                    //if($scheduleDate == ""){
                    //    $scheduleDate = date("H:i");
                    //}
                    //$entity->setScheduleDate($scheduleDate);
                    //$entity->setDaysEmailSend($formData['daysEmailSend']);
                    //$changes = $entity->getChanges(true);
                    //if (!empty($changes['fromAddress']) || !empty($changes['fromName'])) {
                    $model->getRepository()->updateFromInfoinEmail($entity);
                    $email = $this->getModel('email');
                    $model->getRepository()->updateUtmInfoinEmail($entity, $email);
                    //}
                    $paymentrepository  =$this->get('le.subscription.repository.payment');
                    $lastpayment        = $paymentrepository->getLastPayment();
                    $prefix             = 'Trial';
                    if ($lastpayment != null) {
                        $prefix = 'Customer';
                    }
                    $isStateAlive       =$this->get('le.helper.statemachine')->isStateAlive($prefix.'_Sending_Domain_Not_Configured');
                    $sendBtnClicked     =$form->get('buttons')->get('schedule')->isClicked();
                    $isUpdateFlashNeeded=true;
                    if (!$sendBtnClicked) {
                        if ($entity->isPublished() && $isStateAlive) {
                            $isUpdateFlashNeeded = false;
                            $configurl           = $this->factory->getRouter()->generate('le_sendingdomain_action');
                            $entity->setIsPublished(false);
                            $this->addFlash($this->translator->trans('le.email.config.mailer.publish.status.report', ['%url%' => $configurl, '%module%' => 'dripemail']));
                        }
                    } else {
                        $isUpdateFlashNeeded=false;
                    }
                    $model->saveEntity($entity);
                    if ($isUpdateFlashNeeded) {
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
                    }
                    //return $this->redirect($this->generateUrl('le_dripemail_index'));
                    //$this->editAction($entity);
                }
            }
            $passthrough = [
                'activeLink'    => '#le_dripemail_index',
                'leContent'     => 'dripemail',
            ];
            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                $viewParameters = [
                    'objectAction' => 'view',
                    'objectId'     => $entity->getId(),
                ];

                return $this->postActionRedirect(
                    array_merge(
                        $postActionVars,
                        [
                            'returnUrl'       => $this->generateUrl('le_dripemail_campaign_action', $viewParameters),
                            'viewParameters'  => $viewParameters,
                            'contentTemplate' => $template,
                            'passthroughVars' => $passthrough,
                        ]
                    )
                );
            } elseif ($valid && $form->get('buttons')->get('schedule')->isClicked()) {
                //return edit view so that all the session stuff is loaded
                //return $this->editAction($entity->getId(), true);
                $id             = $entity->getId();
                $viewParameters = [
                    'objectAction' => 'send',
                    'objectId'     => $id,
                ];

                return $this->redirect($this->generateUrl('le_dripemail_campaign_action', $viewParameters));
            }
            /*

            if ($cancelled) {
                return $this->postActionRedirect(
                    array_merge(
                        $postActionVars,
                        [
                            'returnUrl'       => $this->generateUrl('le_dripemail_index'),
                            'viewParameters'  => $viewParameters,
                            'contentTemplate' => $template,
                        ]
                    )
                );
            }*/
        }

        $groupFilters  = [
            'template_filters' => [
                'multiple'    => false,
                'onchange'    => 'Le.filterBeeTemplates()',
            ],
        ];

        $groupFilters['template_filters']['groups'] = [];

        $groupFilters['template_filters']['groups']['']  = [
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
        $ismobile      = InputHelper::isMobile();
        $items         = [];
        if ($objectId != null && !empty($objectId)) {
            $filter = [
                'force'  => [
                    [
                        'column' => 'e.dripEmail',
                        'expr'   => 'eq',
                        'value'  => $entity,
                    ],
                ],
            ];
            $items = $emailmodel->getEntities(
                [
                    'filter'           => $filter,
                    'orderBy'          => 'e.dripEmailOrder',
                    'orderByDir'       => 'asc',
                    'ignore_paginator' => true,
                ]
            );
        }
        $verifiedemail = $emailmodel->getVerifiedEmailAddress();
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
                    'form'                         => $this->setFormTheme($form, 'MauticEmailBundle:DripEmail:form.html.php', 'MauticLeadBundle:FormTheme\Filter'),
                    'entity'                       => $entity,
                    'emailform'                    => $emailform->createView(),
                    'beetemplates'                 => $this->factory->getInstalledBeeTemplates('email'),
                    'template_filters'             => $groupFilters,
                    'items'                        => $items,
                    'permissions'                  => $permissions,
                    'actionRoute'                  => 'le_dripemail_campaign_action',
                    'translationBase'              => 'mautic.email.broadcast',
                    'emailEntity'                  => $emailentity,
                    'bluePrints'                   => $bluePrints,
                    'drips'                        => $dripPrints,
                    'verifiedemail'                => $verifiedemail,
                    'ismobile'                     => $ismobile,
                ],
                'contentTemplate' => 'MauticEmailBundle:DripEmail:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_dripemail_index',
                    'leContent'     => 'dripemail',
                    'route'         => $this->generateUrl(
                        'le_dripemail_campaign_action',
                        $routeParams
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
        $model      = $this->getModel('email.dripemail');
        $emailmodel = $this->getModel('email');
        /** @var DripEmail $entity */
        $entity = $model->getEntity($objectId);
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }
        $paymentrepository  =$this->get('le.subscription.repository.payment');
        $lastpayment        = $paymentrepository->getLastPayment();
        $prefix             = 'Trial';
        if ($lastpayment != null) {
            $prefix = 'Customer';
        }
        $isStateAlive=$this->get('le.helper.statemachine')->isStateAlive($prefix.'_Sending_Domain_Not_Configured');
        if ($isStateAlive) {
            $configurl=$this->factory->getRouter()->generate('le_sendingdomain_action');
            $this->addFlash($this->translator->trans('le.email.config.mailer.status.report', ['%url%' => $configurl]));

            return $this->postActionRedirect(
                [
                    'passthroughVars' => [
                        'closeModal' => 1,
                        'route'      => false,
                    ],
                ]
            );
        }
        $newEntity = null;
        if ($entity != null) {
            if (!$this->get('mautic.security')->isGranted('email:emails:create')
                || !$this->get('mautic.security')->hasEntityAccess(
                    'email:emails:viewown',
                    'email:emails:viewother',
                    $entity->getCreatedBy()
                )
            ) {
                return $this->accessDenied();
            }
            $entities = $emailmodel->getEntities(
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
            $newEntity      = clone $entity;
            $newEntity->setIsPublished(true);
            $model->saveEntity($newEntity);
            foreach ($entities as $email) {
                $newEmail      = clone $email;
                //$newEmail->setIsPublished(true);
                $newEmail->setTemplate($email->getTemplate());
                $newEmail->setEmailType('dripemail');
                $newEmail->setDripEmail($newEntity);
                $emailmodel->saveEntity($newEmail);
                unset($newemail);
                unset($email);
            }
        }

        return $this->editAction($newEntity->getId());
    }

    /**
     * Generates new form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function quickaddAction()
    {
        $paymentrepository  =$this->get('le.subscription.repository.payment');
        $lastpayment        = $paymentrepository->getLastPayment();
        $prefix             = 'Trial';
        if ($lastpayment != null) {
            $prefix = 'Customer';
        }
        $isStateAlive=$this->get('le.helper.statemachine')->isStateAlive($prefix.'_Sending_Domain_Not_Configured');
        if ($isStateAlive) {
            $configurl=$this->factory->getRouter()->generate('le_sendingdomain_action');
            $this->addFlash($this->translator->trans('le.email.config.mailer.status.report', ['%url%' => $configurl]));

            return $this->postActionRedirect(
                [
                    'passthroughVars' => [
                        'closeModal' => 1,
                        'route'      => false,
                    ],
                ]
            );
        }
        /** @var DripEmailModel $model */
        $model     = $this->getModel('email.dripemail');
        $dripemail = $model->getEntity();
        $action    = $this->generateUrl('le_dripemail_campaign_action', ['objectAction' => 'quickadd']);
        $form      = $model->createForm($dripemail, $this->get('form.factory'), $action, ['isShortForm' => true]);

        if ($this->request->getMethod() == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $dripemail->setScheduleDate('11:00:00');
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
                    'leContent'     => 'dripemail',
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
        $page           = $this->get('session')->get('mautic.email.page', 1);
        $returnUrl      = $this->generateUrl('le_dripemail_index', ['page' => $page]);
        $flashes        = [];
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticEmailBundle:DripEmail:index',
            'passthroughVars' => [
                'activeLink'    => '#le_dripemail_index',
                'leContent'     => 'dripemail',
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

                    $dripEmailsRepository   = $model->getRepository();
                    $dripEmailsRepository->deleteLeadEventLogbyDrip($entity->getId());
                    $dripEmailsRepository->deleteDripEmailsbyDrip($entity->getId());
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
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticEmailBundle:DripEmail:index',
            'passthroughVars' => [
                'activeLink'    => 'le_dripemail_index',
                'leContent'     => 'dripemail',
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

            $dripEmailsRepository   = $model->getRepository();
            $dripEmailsRepository->deleteLeadEventLogbyDrip($entity->getId());
            $dripEmailsRepository->deleteDripEmailsbyDrip($entity->getId());

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

    /**
     * Manually sends emails.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function sendAction($objectId)
    {
        /** @var \Mautic\EmailBundle\Model\DripEmailModel $model */
        $model                 = $this->getModel('email.dripemail');
        $entity                = $model->getEntity($objectId);
        $session               = $this->get('session');
        $page                  = $session->get('mautic.dripemail.page', 1);
        $totalEmailCount       = $this->get('mautic.helper.licenseinfo')->getTotalEmailCount();
        $actualEmailCount      = $this->get('mautic.helper.licenseinfo')->getActualEmailCount();
        $isHavingEmailValidity = $this->get('mautic.helper.licenseinfo')->isHavingEmailValidity();
        $accountStatus         = $this->get('mautic.helper.licenseinfo')->getAccountStatus();
        if ($redirectUrl=$this->get('le.helper.statemachine')->checkStateAndRedirectPage()) {
            return $this->delegateRedirect($redirectUrl);
        }
        //set the return URL
        $returnUrl = $this->generateUrl('le_dripemail_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MauticEmailBundle:DripEmail:index',
            'passthroughVars' => [
                'activeLink'    => 'le_dripemail_index',
                'leContent'     => 'dripemail',
            ],
        ];
        $smHelper = $this->get('le.helper.statemachine');
        if ($smHelper->isStateAlive('Trial_Unverified_Email')) {
            $this->addFlash($this->translator->trans('le.email.unverified.error'));

            return $this->viewAction($objectId);
        }
        $paymentrepository  =$this->get('le.subscription.repository.payment');
        $lastpayment        = $paymentrepository->getLastPayment();
        $prefix             = 'Trial';
        if ($lastpayment != null) {
            $prefix = 'Customer';
        }
        $isStateAlive=$smHelper->isStateAlive($prefix.'_Sending_Domain_Not_Configured');
        $configurl   =$this->factory->getRouter()->generate('le_sendingdomain_action');
        if ($isStateAlive) {
            $this->addFlash($this->translator->trans('le.email.config.mailer.status.report', ['%url%' => $configurl]));

            return $this->viewAction($objectId);
        }

        //not found
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
                'email:emails:viewown',
                'email:emails:viewother',
                $entity->getCreatedBy()
            )
        ) {
            return $this->accessDenied();
        }

        // Make sure email and category are published
        $published    = $entity->isPublished();
        if (!$published) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'le.email.error.send.unpublished',
                                // 'msgVars' => ['%name%' => $entity->getName()],
                            ],
                        ],
                    ]
                )
            );
        }
        $dripEmailsRepository   = $this->getModel('email')->getRepository();
        $dripemails             = $dripEmailsRepository->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'e.dripEmail',
                            'expr'   => 'eq',
                            'value'  => $entity,
                        ],
                        [
                            'column' => 'e.emailType',
                            'expr'   => 'eq',
                            'value'  => 'dripemail',
                        ],
                    ],
                ],
                'ignore_paginator' => true,
            ]
        );
        $emails = count($dripemails);

        $action        = $this->generateUrl('le_dripemail_campaign_action', ['objectAction' => 'send', 'objectId' => $objectId]);
        $pending       = $model->getLeadsByDrip($entity, true);
        $remainingCount= $pending + $actualEmailCount;

        $paymentrepository  =$this->get('le.subscription.repository.payment');
        $licenseinfohelper  =  $this->get('mautic.helper.licenseinfo');
        $lastpayment        = $paymentrepository->getLastPayment();
        if ($lastpayment != null) {
            $isvalid = $licenseinfohelper->isValidMaxLimit($pending, 'max_email_limit', 50000, 'le.lead.max.email.count.exceeds');
            if ($isvalid) {
                $this->addFlash($isvalid);

                return $this->postActionRedirect(
                    [
                        'returnUrl'=> $this->generateUrl('le_email_campaign_index'),
                    ]
                );
            }
        }

        if (!$accountStatus) {
            if ((($totalEmailCount >= $remainingCount) || ($totalEmailCount == 'UL')) && $isHavingEmailValidity) {
                if ($this->request->getMethod() == 'POST') {//($complete || $this->isFormValid($form))) {
                    $pending                   = $model->getLeadsByDrip($entity, true);
                    $message                   = '';
                    $flashType                 = '';
                    if ($licenseinfohelper->isLeadsEngageEmailExpired($pending)) {
                        $message   = 'le.email.broadcast.usage.error';
                        $flashType = 'notice';
                    } else {
                        $entity->setIsScheduled(true);
                        $model->saveEntity($entity);
                        $message   ='le.drip.email.broadcast.send';
                        $flashType = 'sweetalert';
                    }
                    $postActionVars = [
                        'returnUrl'       => $this->generateUrl('le_dripemail_campaign_action', ['objectAction' => 'view', 'objectId' => $objectId]),
                        'viewParameters'  => ['objectAction' => 'view', 'objectId' => $objectId],
                        'contentTemplate' => 'MauticEmailBundle:DripEmail:view',
                        'passthroughVars' => [
                            'activeLink'    => 'le_dripemail_index',
                            'leContent'     => 'dripemail',
                        ],
                    ];

                    return $this->postActionRedirect(
                        array_merge(
                            $postActionVars,
                            [
                                'flashes' => [
                                    [
                                        'type'    => $flashType,
                                        'msg'     => $message,
                                    ],
                                ],
                            ]
                        )
                    );

                // return $this->viewAction($objectId);
                } else {
                    //process and send
                    $contentTemplate = 'MauticEmailBundle:Send:dripsend.html.php';
                    $viewParameters  = [
                        'entity'      => $entity,
                        'pending'     => $pending,
                        'tmpl'        => 'index',
                        'emailcount'  => $emails,
                        'actionRoute' => 'le_dripemail_campaign_action',
                        'indexRoute'  => 'le_dripemail_index',
                    ];
                }

                return $this->delegateView(
                    [
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $contentTemplate,
                        'passthroughVars' => [
                            'leContent'     => 'emailSend',
                            'route'         => $action,
                        ],
                    ]
                );
            } else {
                if (!$isHavingEmailValidity) {
                    $this->addFlash('mautic.email.validity.expired');
                } else {
                    $configurl     = $this->factory->getRouter()->generate('le_config_action', ['objectAction' => 'edit']);
                    $this->addFlash('mautic.email.count.exceeds', ['%url%'=>$configurl]);
                }

                return $this->postActionRedirect(
                    [
                        'returnUrl'=> $this->generateUrl('le_dripemail_index'),
                    ]
                );
            }
        } else {
            $this->addFlash('mautic.account.suspend');

            return $this->viewAction($objectId);
        }
    }

    /**
     * Edit Drip campaign Email.
     *
     * @param   $subobjectId
     * @param   $objectId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function emaileditAction($subobjectId, $objectId)
    {
        $returnUrl      = $this->generateUrl('le_dripemail_index');
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => 1],
            'contentTemplate' => 'MauticEmailBundle:DripEmail:index',
            'passthroughVars' => [
                'activeLink'    => 'le_dripemail_index',
                'leContent'     => 'dripemail',
            ],
        ];
        $viewParameters = [
        ];

        if ($subobjectId === null || $objectId === null) {
            $template       = 'MauticEmailBundle:DripEmail:index';

            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'returnUrl'       => $this->generateUrl('le_dripemail_index'),
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                    ]
                )
            );
        }
        /** @var DripEmailModel $model */
        $model = $this->getModel('email.dripemail');

        /** @var \Mautic\EmailBundle\Entity\DripEmail $entity */
        $entity = $model->getEntity($objectId);

        /** @var EmailModel $emailmodel */
        $emailmodel = $this->getModel('email');

        /** @var \Mautic\EmailBundle\Entity\Email $emailentity */
        $emailentity = $emailmodel->getEntity($subobjectId);

        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator      = $this->get('mautic.configurator');
        $params            = $configurator->getParameters();
        $fromname          = $params['mailer_from_name'];
        $fromadress        = $params['mailer_from_email'];
//        $fromname     ='';
//        $fromadress   ='';
//        $defaultsender=$emailmodel->getDefaultSenderProfile();
//        if (sizeof($defaultsender) > 0) {
//            $fromname  =$defaultsender[0];
//            $fromadress=$defaultsender[1];
//        }
        $fromName        = $emailentity->getFromName();
        $fromAdress      = $emailentity->getFromAddress();
        $emailaction     = $this->generateUrl('le_dripemail_email_action', ['objectId' => $entity->getId(), 'subobjectAction' => 'edit', 'subobjectId' => $emailentity->getId()]);
        //create the form
        $emailform      = $emailmodel->createForm($emailentity, $this->get('form.factory'), $emailaction, ['update_select' => false, 'isEmailTemplate' => true, 'isDripEmail' => true, 'isShortForm' => false]);

        if (empty($fromName)) {
            $emailentity->setFromName($fromname);
        }
        if (empty($fromAdress)) {
            $emailentity->setFromAddress($fromadress);
        }
        $isBeeEditor = true;
        if ($emailentity->getBeeJSON() == 'RichTextEditor') {
            $isBeeEditor = false;
        }
        if ($isBeeEditor && InputHelper::isMobile()) {
            return $this->editDenied($this->generateUrl(
                'le_dripemail_campaign_action',
                [
                    'objectAction' => 'edit',
                    'objectId'     => $entity->getId(),
                ]
            ));
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

        $routeParams = [
            'objectAction' => 'edit',
            'objectId'     => $entity->getId(),
        ];
        $postActionVars = [];
        $template       = 'MauticEmailBundle:DripEmail:edit';

        //dump($emailentity);
        $scheduledTime = $emailentity->getScheduleTime();
        if ($this->request->getMethod() == 'POST') {
            if (!$cancelled = $this->isFormCancelled($emailform)) {
                if ($valid = $this->isFormValid($emailform)) {
                    $emailentity->setScheduleTime($scheduledTime);
                    if ($emailentity->getBeeJSON() == 'RichTextEditor') {
                        $emailentity->setTemplate('');
                    }

                    $emailmodel->saveEntity($emailentity);

                    $this->addFlash(
                        'mautic.core.notice.updated',
                        [
                            '%name%'      => $emailentity->getName(),
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

                    return $this->delegateRedirect($this->generateUrl(
                        'le_dripemail_campaign_action',
                        [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]
                    ));
                }
            }

            if ($cancelled) {
                return $this->delegateRedirect($this->generateUrl(
                    'le_dripemail_campaign_action',
                    [
                        'objectAction' => 'edit',
                        'objectId'     => $entity->getId(),
                    ]
                ));
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'              => $emailform->createView(),
                    'entity'            => $emailentity,
                    'beetemplates'      => $this->factory->getInstalledBeeTemplates('email'),
                    'filters'           => $groupFilters,
                    'actionRoute'       => 'le_dripemail_campaign_action',
                    'translationBase'   => 'mautic.email.broadcast',
                    'isBeeEditor'       => $isBeeEditor,
                    'EmailCount'        => $emailentity->getDripEmailOrder(),
                ],
                'contentTemplate' => 'MauticEmailBundle:DripEmail:emailform.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_dripemail_index',
                    'leContent'     => 'dripemail',
                    'route'         => $this->generateUrl(
                        'le_dripemail_campaign_action',
                        $routeParams
                    ),
                ],
            ]
        );
    }

    /**
     * Edit Drip campaign Email.
     *
     * @param   $subobjectId
     * @param   $objectId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function emailnewAction($subobjectId, $objectId)
    {
        $returnUrl      = $this->generateUrl('le_dripemail_index');
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => 1],
            'contentTemplate' => 'MauticEmailBundle:DripEmail:index',
            'passthroughVars' => [
                'activeLink'    => 'le_dripemail_index',
                'leContent'     => 'dripemail',
            ],
        ];
        if ($objectId === null) {
            $template       = 'MauticEmailBundle:DripEmail:index';
            $viewParameters = [
            ];

            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'returnUrl'       => $this->generateUrl('le_dripemail_index'),
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                    ]
                )
            );
        }
        /** @var DripEmailModel $model */
        $model = $this->getModel('email.dripemail');

        /** @var \Mautic\EmailBundle\Entity\DripEmail $entity */
        $entity = $model->getEntity($objectId);

        /** @var EmailModel $emailmodel */
        $emailmodel = $this->getModel('email');

        /** @var \Mautic\EmailBundle\Entity\Email $emailentity */
        $emailentity = $emailmodel->getEntity();

        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        // $configurator    = $this->get('mautic.configurator');
        // $params          = $configurator->getParameters();
        //  $fromname        = $params['mailer_from_name'];
        // $fromadress      = $params['mailer_from_email'];
        /* $fromname     ='';
         $fromadress   ='';
         $defaultsender=$emailmodel->getDefaultSenderProfile();
         if (sizeof($defaultsender) > 0) {
             $fromname  =$defaultsender[0];
             $fromadress=$defaultsender[1];
         }*/
        $fromname     ='';
        $fromadress   ='';
        $defaultsender=$emailmodel->getDefaultSenderProfile();
        if (sizeof($defaultsender) > 0) {
            $fromname  =$defaultsender[0];
            $fromadress=$defaultsender[1];
        }

        $fromName        = $entity->getFromName();
        $fromAdress      = $entity->getFromAddress();
        $emailentity->setName('DripEmail - ');
        $emailentity->setIsPublished(false);
        $emailaction     = $this->generateUrl('le_dripemail_email_action', ['objectId' => $entity->getId(), 'subobjectAction' => 'new', 'subobjectId' => $subobjectId]);
        //create the form
        $emailform      = $emailmodel->createForm($emailentity, $this->get('form.factory'), $emailaction, ['update_select' => false, 'isEmailTemplate' => true, 'isDripEmail' => true, 'isShortForm' => false]);

        if (empty($fromName)) {
            $fromName = $fromname;
        }
        if (empty($fromAdress)) {
            $fromAdress= $fromadress;
        }

        $isBeeEditor   = $subobjectId;
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

        $routeParams = [
            'objectAction' => 'edit',
            'objectId'     => $entity->getId(),
        ];
        $totalitems = $emailmodel->getEntities(
            [
                'filter'           => [
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
        $template       = 'MauticEmailBundle:DripEmail:index';
        $viewParameters = [
        ];
        /** @var \Mautic\UserBundle\Model\UserModel $usermodel */
        $usermodel      = $this->getModel('user.user');
        $userentity     = $usermodel->getCurrentUserEntity();
        $postActionVars = [];
        if ($this->request->getMethod() == 'POST') {
            if (!$cancelled = $this->isFormCancelled($emailform)) {
                if ($valid = $this->isFormValid($emailform)) {
                    $emailentity->setName('DripEmail - '.$emailentity->getSubject());
                    $emailentity->setIsPublished(true);
                    $emailentity->setDripEmail($entity);
                    $emailentity->setGoogleTags(true);
                    $emailentity->setEmailType('dripemail');
                    $emailentity->setCreatedBy($userentity);
                    $emailentity->setFromName($fromName);
                    $emailentity->setFromAddress($fromAdress);
                    $emailentity->setDripEmailOrder(sizeof($totalitems) + 1);
                    $scheduleTime = '0 days';
                    if (sizeof($totalitems) > 0) {
                        $scheduleTime = '1 days';
                    }
                    if (!$isBeeEditor) {
                        $emailentity->setBeeJSON('RichTextEditor');
                    }
                    $templatename = $isBeeEditor ? $emailentity->getTemplate() : '';
                    $emailentity->setTemplate($templatename);
                    $emailentity->setScheduleTime($scheduleTime);

                    $emailmodel->saveEntity($emailentity);

                    $this->addFlash(
                        'mautic.core.notice.created',
                        [
                            '%name%'      => $emailentity->getName(),
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

                    return $this->delegateRedirect($this->generateUrl(
                        'le_dripemail_campaign_action',
                        [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]
                    ));
                }
            }

            if ($cancelled) {
                return $this->delegateRedirect($this->generateUrl(
                    'le_dripemail_campaign_action',
                    [
                        'objectAction' => 'edit',
                        'objectId'     => $entity->getId(),
                    ]
                ));
            }
        }

        $dripRoute = ['objectId' => $entity->getId(), 'subobjectAction' => 'new', 'subobjectId' => $isBeeEditor];

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'              => $emailform->createView(),
                    'entity'            => $emailentity,
                    'beetemplates'      => $this->factory->getInstalledBeeTemplates('email'),
                    'filters'           => $groupFilters,
                    'actionRoute'       => 'le_dripemail_email_action',
                    'translationBase'   => 'mautic.email.broadcast',
                    'isBeeEditor'       => $isBeeEditor,
                    'EmailCount'        => sizeof($totalitems) + 1,
                ],
                'contentTemplate' => 'MauticEmailBundle:DripEmail:emailform.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_dripemail_index',
                    'leContent'     => 'dripemail',
                    'route'         => $this->generateUrl(
                        'le_dripemail_email_action',
                        $dripRoute
                    ),
                ],
            ]
        );
    }

    public function emailpreviewAction($objectid, $subobjectId)
    {
        $driprepo    = $this->get('le.core.repository.signup');
        /** @var EmailModel $emailmodel */
        $emailmodel = $this->getModel('email');
        /** @var DripEmailModel $dripmodel */
        $dripmodel  = $this->getModel('email.dripemail');
        $email      = [];
        if ($objectid != '1') {
            $email = $driprepo->getEmailsByEmailId($objectid);
        } else {
            /** @var \Mautic\EmailBundle\Entity\Email $emailentity */
            $emailentity             = $emailmodel->getEntity($subobjectId);
            $content                 = $emailentity->getCustomHtml();
            $accountmodel            = $this->getModel('subscription.accountinfo');
            $accrepo                 = $accountmodel->getRepository();
            $accountentity           = $accrepo->findAll();
            if (sizeof($accountentity) > 0) {
                $account = $accountentity[0]; //$model->getEntity(1);
            } else {
                $account = new Account();
            }
            $dripEntity              = $emailentity->getDripEmail();
            $email[0]['custom_html'] = $content;
            $email[1]['footer']      = $dripEntity->getUnsubscribeText() == '' ? $this->coreParametersHelper->getParameter('footer_text') : $dripEntity->getUnsubscribeText();
            $email[2]['type']        = $emailentity->getBeeJSON();
            if ($account->getNeedpoweredby()) {
                $url                     = 'https://anyfunnels.com/?utm-src=email-footer-link&utm-med='.$account->getDomainname();
                $icon                    = 'https://anyfunnels.com/wp-content/uploads/leproduct/anyfunnels-footer2.png'; //$this->factory->get('templating.helper.assets')->getUrl('media/images/le_branding.png');
                $atag                    = "<br><br><div style='background-color: #FFFFFF;text-align: center;'><a href='$url' target='_blank'><img style='height: 35px;width:160px;' src='$icon'></a></div>";
                $email[3]['branding']    = $atag;
            }
        }
        $dripRoute = ['objectId' => $subobjectId, 'objectAction' => 'edit'];
        //echo $email[0]['custom_html'];
        return $this->delegateView(
            [
                'viewParameters' => [
                    'actionRoute'       => 'le_dripemail_email_action',
                    'email'             => $email,
                ],
                'contentTemplate' => 'MauticEmailBundle:DripEmail:preview.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_dripemail_index',
                    'leContent'     => 'dripemail',
                    'route'         => $this->generateUrl(
                        'le_dripemail_campaign_action',
                        $dripRoute
                    ),
                ],
            ]
        );
    }

    public function emailblueprintAction($objectid, $subobjectId)
    {
        $driprepo    = $this->get('le.core.repository.signup');

        /** @var DripEmailModel $dripmodel */
        $dripmodel = $this->getModel('email.dripemail');
        $dripemail = $dripmodel->getEntity($subobjectId);
        $items     = $driprepo->getEmailsByDripId($objectid);
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

        /** @var EmailModel $emailmodel */
        $emailmodel = $this->getModel('email');
        /** @var \Mautic\UserBundle\Model\UserModel $usermodel */
        $usermodel     = $this->getModel('user.user');
        $userentity    = $usermodel->getCurrentUserEntity();

        $dripOrder = 0;
        foreach ($items as $item) {
            $dripOrder = $dripOrder + 1;
            $newEntity = $emailmodel->getEntity();
            //file_put_contents("/var/www/log.txt",$item->getName()."\n",FILE_APPEND);
            $newEntity->setName($item['name']);
            $newEntity->setSubject($item['subject']);
            $newEntity->setPreviewText($item['preview_text']);
            $newEntity->setCustomHtml($item['custom_html']);
            $newEntity->setBeeJSON($item['bee_json']);
            $newEntity->setDripEmail($dripemail);
            $newEntity->setCreatedBy($userentity);
            $newEntity->setIsPublished(true);
            $newEntity->setGoogleTags(true);
            $newEntity->setEmailType('dripemail');
            /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
            $configurator     = $this->get('mautic.configurator');
            $params           = $configurator->getParameters();
            $fromname         = $params['mailer_from_name'];
            $fromadress       = $params['mailer_from_email'];
//            $defaultsender=$emailmodel->getDefaultSenderProfile();
//            if (sizeof($defaultsender) > 0) {
//                $newEntity->setFromName($defaultsender[0]);
//                $newEntity->setFromAddress($defaultsender[1]);
//            }
            $newEntity->setFromName($fromname);
            $newEntity->setFromAddress($fromadress);
            $newEntity->setDripEmailOrder($dripOrder);
            $newEntity->setScheduleTime($item['scheduleTime']);
            $emailmodel->saveEntity($newEntity);
        }

        return $this->delegateRedirect($this->generateUrl(
            'le_dripemail_campaign_action',
            [
                'objectAction' => 'edit',
                'objectId'     => $dripemail->getId(),
            ]
        ));
    }

    public function emaildeleteAction($objectid, $subobjectId)
    {
        /** @var DripEmailModel $dripmodel */
        $dripmodel = $this->getModel('email.dripemail');
        $dripemail = $dripmodel->getEntity($subobjectId);

        /** @var LeadModel $leadmodel */
        $leadmodel = $this->getModel('lead');
        $lead      = $leadmodel->getEntity($objectid);

        $campaignRepo      = $dripmodel->getCampaignLeadRepository();
        $campaignEventRepo = $dripmodel->getCampaignLeadEventRepository();

        $entities = $campaignRepo->getEntities(
            [
                'filter' => [
                    'force' => [
                        [
                            'column' => 'le.campaign',
                            'expr'   => 'eq',
                            'value'  => $dripemail,
                        ],
                        [
                            'column' => 'le.lead',
                            'expr'   => 'eq',
                            'value'  => $lead,
                        ],
                    ],
                ],
                'ignore_paginator' => true,
            ]
        );

        $campaignRepo->deleteEntities($entities);
        unset($entities);
        $entities = $campaignEventRepo->getEntities(
            [
                'filter' => [
                    'force' => [
                        [
                            'column' => 'dle.campaign',
                            'expr'   => 'eq',
                            'value'  => $dripemail,
                        ],
                        [
                            'column' => 'dle.lead',
                            'expr'   => 'eq',
                            'value'  => $lead,
                        ],
                    ],
                ],
                'ignore_paginator' => true,
            ]
        );
        $campaignEventRepo->deleteEntities($entities);
        unset($entities);

        return $this->redirect($this->generateUrl(
            'le_dripemail_campaign_action',
            [
                'objectAction' => 'view',
                'objectId'     => $dripemail->getId(),
            ]
        ));
    }
}
