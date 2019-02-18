<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\DashboardBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\DashboardBundle\Entity\Widget;
use Mautic\SubscriptionBundle\Entity\KYC;
use Mautic\SubscriptionBundle\Entity\UserPreference;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class DashboardController.
 */
class DashboardController extends FormController
{
    public function indexAction()
    {
        $paymentrepository = $this->get('le.subscription.repository.payment');
        $lastpayment       = $paymentrepository->getLastPayment();
        //if ($lastpayment == null) {
        $videoarg       = $this->request->get('login');
        $loginsession   = $this->get('session');
        $loginarg       = $loginsession->get('isLogin');
        $dbhost         = $this->coreParametersHelper->getParameter('le_db_host');
        $showsetup      = false;
        $billformview   = '';
        $accformview    = '';
        $userformview   = '';
        $videoURL       = '';
        $showvideo      = false;
        $kycview        = $this->get('mautic.helper.licenseinfo')->getFirstTimeSetup($dbhost, $loginarg);

        $ismobile = InputHelper::isMobile();
        if (sizeof($kycview) > 0) {
            $showsetup      = false;
            $billformview   = $kycview[0];
            $accformview    = $kycview[1];
            $userformview   = $kycview[2];
            $videoURL       = '';
            $showvideo      = false;
            $billing        = $kycview[3];
            $account        = $kycview[4];
            $userEntity     = $kycview[5];
            /** @var \Mautic\SubscriptionBundle\Model\KYCModel $kycmodel */
            $kycmodel         = $this->getModel('subscription.kycinfo');
            $kycrepo          = $kycmodel->getRepository();
            $kycentity        = $kycrepo->findAll();
            if (sizeof($kycentity) > 0) {
                $kyc = $kycentity[0]; //$model->getEntity(1);
            } else {
                $kyc = new KYC();
            }
            $step = '';
            if ($account->getPhonenumber() == '') {
                $step = 'flname';
            } elseif ($account->getAccountname() == '' || $kyc->getIndustry() == '' || $kyc->getPrevioussoftware() == '' || $account->getWebsite() == '') {
                $step = 'aboutyourbusiness';
            } elseif ($billing->getCompanyaddress() == '' || $billing->getCity() == '') {
                $step = 'addressinfo';
            }
            if ($step != '') {
                return $this->delegateRedirect($this->generateUrl('le_welcome_action', ['step' => $step]));
            }
        } else {
            $loginsession->set('isLogin', false);
        }

        $emailProvider          = false;
        $websiteTrackingEnabled = false;
        $isSegmentCreated       = false;
        $isImportDone           = false;
        $isCampaignCreated      = false;
        $isDripCreated          = false;
        $isOneOffCreated        = false;
        $isListCreated          = false;
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $accountModel */
        $accountModel  = $this->getModel('subscription.accountinfo');

        $licenseinfo   = $this->get('mautic.helper.licenseinfo')->getLicenseEntity();
        if ($licenseinfo->getEmailProvider() != 'LeadsEngage') {
            $emailProvider = true;
        }
        /** @var \Mautic\PageBundle\Model\PageModel $pagemodel */
        $pagemodel = $this->getModel('page.page');
        $hitrepo   = $pagemodel->getHitRepository();
        $pages     = $hitrepo->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'h.organization',
                            'expr'   => 'neq',
                            'value'  => 'sampletracking',
                        ],
                    ],
                ],
                'ignore_paginator' => true,
            ]
        );
        if (!empty($pages)) {
            $websiteTrackingEnabled = true;
        }

        $listmodel       = $this->getModel('lead.list');
        $currentUser     = $this->get('security.context')->getToken()->getUser();
        $lists           = $listmodel->getEntities([
            'filter' => [
                'force' => [
                    [
                        'column' => 'l.createdBy',
                        'expr'   => 'eq',
                        'value'  => $currentUser->getId(),
                    ],
                ],
            ],
            'ignore_paginator' => true,
        ]);

        if (!empty($lists)) {
            $isSegmentCreated = true;
        }

        $importmodel       = $this->getModel('lead.import');
        $Importlists       = $importmodel->getEntities(
            [
                'filter'           => [],
                'ignore_paginator' => true,
            ]
        );

        if (!empty($Importlists)) {
            $isImportDone = true;
        }

        $campaignmodel     = $this->getModel('campaign');
        $campaignList      = $campaignmodel->getEntities(
            [
                'filter'           => [],
                'ignore_paginator' => true,
            ]
        );
        if (!empty($campaignList)) {
            $isCampaignCreated = true;
        }

        $dripcampaignmodel     = $this->getModel('email.dripemail');
        $dripcampaign          = $dripcampaignmodel->getEntities(
            [
                'filter'           => [],
                'ignore_paginator' => true,
            ]
        );
        if (!empty($dripcampaign)) {
            $isDripCreated = true;
        }

        $filter = [
            'string' => '',
            'force'  => [
                ['column' => 'e.variantParent', 'expr' => 'isNull'],
                ['column' => 'e.translationParent', 'expr' => 'isNull'],
                ['column' => 'e.emailType', 'expr' => 'eq', 'value' => 'list'],
            ],
        ];
        $emailmodel    = $this->getModel('email');
        $emailList     = $emailmodel->getEntities(
            [
                'filter'           => $filter,
                'ignore_paginator' => true,
            ]
        );

        if (!empty($emailList)) {
            $isOneOffCreated = true;
        }

        $listOptinmodel   = $this->getModel('lead.listoptin');
        $listOptin        = $listOptinmodel->getEntities(
            [
                'filter'           => [],
                'ignore_paginator' => true,
            ]
        );

        if (!empty($listOptin)) {
            $isListCreated = true;
        }

        // Init the date range filter form
        $dateRangeValues = $this->request->get('daterange', []);
        $action          = $this->generateUrl('le_dashboard_index');
        $dateRangeForm   = $this->get('form.factory')->create('daterange', $dateRangeValues, ['action' => $action]);

        // Account stats per time period
        $timeStats = $accountModel->getAccountLineChartData(
            null,
            new \DateTime($dateRangeForm->get('date_from')->getData()),
            new \DateTime($dateRangeForm->get('date_to')->getData())
        );

        $emailStats   = $accountModel->getCustomEmailStats();
        $leadStats    = $accountModel->getCustomLeadStats();
        $overallstats = $accountModel->getOverAllStats();

        return $this->delegateView(
            [
                'viewParameters' => [
                    'showvideo'            => $showvideo,
                    'videoURL'             => $videoURL,
                    'showsetup'            => $showsetup,
                    'billingform'          => $billformview,
                    'accountform'          => $accformview,
                    'userform'             => $userformview,
                    'isMobile'             => $ismobile,
                    'isProviderChanged'    => $emailProvider,
                    'isWebsiteTracking'    => $websiteTrackingEnabled,
                    'isSegmentCreated'     => $isSegmentCreated,
                    'isCampaignCreated'    => $isCampaignCreated,
                    'isDripCreated'        => $isDripCreated,
                    'isOneOffCreated'      => $isOneOffCreated,
                    'isListCreated'        => $isListCreated,
                    'isImportDone'         => $isImportDone,
                    'pricingUrl'           => $this->generateUrl('le_pricing_index'),
                    'tmpl'                 => 'index',
                    'stats'                => $timeStats,
                    'dateRangeForm'        => $dateRangeForm->createView(),
                    'emailStats'           => $emailStats,
                    'leadStats'            => $leadStats,
                    'overallstats'         => $overallstats,
                    'username'             => $this->user->getName(),
                    'isPaid'               => ($lastpayment != null),
                ],
                'contentTemplate' => 'MauticSubscriptionBundle:Subscription:success_page.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_dashboard_index',
                    'leContent'     => 'subscription',
                    'route'         => $this->generateUrl('le_dashboard_index'),
                ],
            ]
        );
        //} else {
        //    return $this->delegateRedirect($this->generateUrl('le_contact_index'));
        //}
    }

    /**
     * Generates the default view.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function index1Action()
    {
        $videoarg     = $this->request->get('login');
        $loginsession = $this->get('session');
        $loginarg     = $loginsession->get('isLogin');
        $dbhost       = $this->coreParametersHelper->getParameter('le_db_host');
        $showsetup    = false;
        $billformview = '';
        $accformview  = '';
        $userformview = '';

        $kycview = $this->get('mautic.helper.licenseinfo')->getFirstTimeSetup($dbhost, $loginarg);

        /** @var \Mautic\DashboardBundle\Model\DashboardModel $model */
        $model   = $this->getModel('dashboard');
        $widgets = $model->getWidgets();
        //$loginsession->set('isLogin', false);

        // Apply the default dashboard if no widget exists
        if (!count($widgets) && $this->user->getId()) {
            return $this->applyDashboardFileAction('global.leadsengagecustom');
        }

        $humanFormat     = 'M j, Y';
        $mysqlFormat     = 'Y-m-d';
        $action          = $this->generateUrl('le_dashboard_index');
        $dateRangeFilter = $this->request->get('daterange', []);

        // Set new date range to the session
        if ($this->request->isMethod('POST')) {
            $session = $this->get('session');
            if (!empty($dateRangeFilter['date_from'])) {
                $from = new \DateTime($dateRangeFilter['date_from']);
                $session->set('mautic.dashboard.date.from', $from->format($mysqlFormat));
            }

            if (!empty($dateRangeFilter['date_to'])) {
                $to = new \DateTime($dateRangeFilter['date_to']);
                $session->set('mautic.dashboard.date.to', $to->format($mysqlFormat));
            }

            $model->clearDashboardCache();
        }

        // Load date range from session
        $filter = $model->getDefaultFilter();

        // Set the final date range to the form
        $dateRangeFilter['date_from'] = $filter['dateFrom']->format($humanFormat);
        $dateRangeFilter['date_to']   = $filter['dateTo']->format($humanFormat);
        $dateRangeForm                = $this->get('form.factory')->create('daterange', $dateRangeFilter, ['action' => $action]);

        $model->populateWidgetsContent($widgets, $filter);

        $usermodel  =$this->getModel('user.user');
        $currentuser= $usermodel->getCurrentUserEntity();
        if ($videoarg == 'CloseVideo') {
            $loginsession->set('CloseVideo', true);

            return $this->redirect($this->generateUrl('le_dashboard_index'));
        }
        $close     = $loginsession->get('CloseVideo');

        /** @var \Mautic\SubscriptionBundle\Model\UserPreferenceModel $userprefmodel */
        $userprefmodel  = $this->getModel('subscription.userpreference');
        if ($videoarg == 'dont_show_again') {
            $userprefentity = new UserPreference();
            $userprefentity->setProperty('Dont Show Video again');
            $userprefentity->setUserid($currentuser->getId());
            $userprefmodel->saveEntity($userprefentity);
            //$this->addFlash('Video will be available in Help.');
            return $this->redirect($this->generateUrl('le_dashboard_index'));
        }
        $userprefrepo   = $userprefmodel->getRepository();
        $userprefentity = $userprefrepo->findOneBy(['userid' => $currentuser->getId()]);
        $videoURL       = ''; //$this->coreParametersHelper->getParameter('video_url');
        $repository     = $this->get('le.core.repository.subscription');
        $videoconfig    = $repository->getVideoURL();
        if (!empty($videoconfig)) {
            $videoURL = $videoconfig[0]['video_url'];
        }
        $showvideo      = false;
        if ($userprefentity == null && $close == '') {
            $showvideo = true;
        }
        $ismobile = InputHelper::isMobile();
        if (sizeof($kycview) > 0) {
            $showsetup    = true;
            $billformview = $kycview[0];
            $accformview  = $kycview[1];
            $userformview = $kycview[2];
        } else {
            $loginsession->set('isLogin', false);
        }

        return $this->delegateView([
            'viewParameters' => [
                'security'             => $this->get('mautic.security'),
                'widgets'              => $widgets,
                'dateRangeForm'        => $dateRangeForm->createView(),
                'showvideo'            => $showvideo,
                'videoURL'             => $videoURL,
                'route'                => $this->generateUrl('le_plan_index'),
                'showsetup'            => $showsetup,
                'billingform'          => $billformview,
                'accountform'          => $accformview,
                'userform'             => $userformview,
                'isMobile'             => $ismobile,
            ],
            'contentTemplate' => 'MauticDashboardBundle:Dashboard:index.html.php',
            'passthroughVars' => [
                'activeLink'    => '#le_dashboard_index',
                'leContent'     => 'dashboard',
                'route'         => $this->generateUrl('le_dashboard_index'),
            ],
        ]);
    }

    /**
     * Generate's new dashboard widget and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        //retrieve the entity
        $widget = new Widget();

        $model  = $this->getModel('dashboard');
        $action = $this->generateUrl('le_dashboard_action', ['objectAction' => 'new']);

        //get the user form factory
        $form       = $model->createForm($widget, $this->get('form.factory'), $action);
        $closeModal = false;
        $valid      = false;

        ///Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $closeModal = true;

                    //form is valid so process the data
                    $model->saveEntity($widget);
                }
            } else {
                $closeModal = true;
            }
        }

        if ($closeModal) {
            //just close the modal
            $passthroughVars = [
                'closeModal'    => 1,
                'leContent'     => 'widget',
            ];

            $filter = $model->getDefaultFilter();
            $model->populateWidgetContent($widget, $filter);

            if ($valid && !$cancelled) {
                $passthroughVars['upWidgetCount'] = 1;
                $passthroughVars['widgetHtml']    = $this->renderView('MauticDashboardBundle:Widget:detail.html.php', [
                    'widget' => $widget,
                ]);
                $passthroughVars['widgetId']     = $widget->getId();
                $passthroughVars['widgetWidth']  = $widget->getWidth();
                $passthroughVars['widgetHeight'] = $widget->getHeight();
            }

            $response = new JsonResponse($passthroughVars);

            return $response;
        } else {
            return $this->delegateView([
                'viewParameters' => [
                    'form' => $form->createView(),
                ],
                'contentTemplate' => 'MauticDashboardBundle:Widget:form.html.php',
            ]);
        }
    }

    /**
     * edit widget and processes post data.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($objectId)
    {
        $model  = $this->getModel('dashboard');
        $widget = $model->getEntity($objectId);
        $action = $this->generateUrl('le_dashboard_action', ['objectAction' => 'edit', 'objectId' => $objectId]);

        //get the user form factory
        $form       = $model->createForm($widget, $this->get('form.factory'), $action);
        $closeModal = false;
        $valid      = false;
        ///Check for a submitted form and process it
        if ($this->request->getMethod() == 'POST') {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $closeModal = true;

                    //form is valid so process the data
                    $model->saveEntity($widget);
                }
            } else {
                $closeModal = true;
            }
        }

        if ($closeModal) {
            //just close the modal
            $passthroughVars = [
                'closeModal'    => 1,
                'leContent'     => 'widget',
            ];

            $filter = $model->getDefaultFilter();
            $model->populateWidgetContent($widget, $filter);

            if ($valid && !$cancelled) {
                $passthroughVars['upWidgetCount'] = 1;
                $passthroughVars['widgetHtml']    = $this->renderView('MauticDashboardBundle:Widget:detail.html.php', [
                    'widget' => $widget,
                ]);
                $passthroughVars['widgetId']     = $widget->getId();
                $passthroughVars['widgetWidth']  = $widget->getWidth();
                $passthroughVars['widgetHeight'] = $widget->getHeight();
            }

            $response = new JsonResponse($passthroughVars);

            return $response;
        } else {
            return $this->delegateView([
                'viewParameters' => [
                    'form' => $form->createView(),
                ],
                'contentTemplate' => 'MauticDashboardBundle:Widget:form.html.php',
            ]);
        }
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
        $returnUrl = $this->generateUrl('le_dashboard_index');
        $success   = 0;
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'contentTemplate' => 'MauticDashboardBundle:Dashboard:index',
            'passthroughVars' => [
                'activeLink'    => '#le_dashboard_index',
                'success'       => $success,
                'leContent'     => 'dashboard',
            ],
        ];

        /** @var \Mautic\DashboardBundle\Model\DashboardModel $model */
        $model  = $this->getModel('dashboard');
        $entity = $model->getEntity($objectId);
        if ($entity === null) {
            $flashes[] = [
                'type'    => 'error',
                'msg'     => 'mautic.api.client.error.notfound',
                'msgVars' => ['%id%' => $objectId],
            ];
        } else {
            $model->deleteEntity($entity);
            $name      = $entity->getName();
            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'mautic.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $name,
                    '%id%'   => $objectId,
                ],
            ];
        }

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
     * Saves the widgets of current user into a json and stores it for later as a file.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveAction()
    {
        // Accept only AJAX POST requests because those are check for CSRF tokens
        if ($this->request->getMethod() !== 'POST' || !$this->request->isXmlHttpRequest()) {
            return $this->accessDenied();
        }

        $name = $this->getNameFromRequest();
        try {
            $this->getModel('dashboard')->saveSnapshot($name);
            $type = 'notice';
            $msg  = $this->translator->trans('mautic.dashboard.notice.save', [
                '%name%'    => $name,
                '%viewUrl%' => $this->generateUrl(
                    'le_dashboard_action',
                    [
                        'objectAction' => 'import',
                    ]
                ),
            ], 'flashes');
        } catch (IOException $e) {
            $type = 'error';
            $msg  = $this->translator->trans('mautic.dashboard.error.save', [
                '%msg%' => $e->getMessage(),
            ], 'flashes');
        }

        return $this->postActionRedirect(
            [
                'flashes' => [
                    [
                        'type' => $type,
                        'msg'  => $msg,
                    ],
                ],
            ]
        );
    }

    /**
     * Exports the widgets of current user into a json file and downloads it.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function exportAction()
    {
        $filename = InputHelper::filename($this->getNameFromRequest(), 'json');
        $response = new JsonResponse($this->getModel('dashboard')->toArray($name));
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Expires', 0);
        $response->headers->set('Cache-Control', 'must-revalidate');
        $response->headers->set('Pragma', 'public');

        return $response;
    }

    /**
     * Exports the widgets of current user into a json file.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteDashboardFileAction()
    {
        $file = $this->request->get('file');

        $parts = explode('.', $file);
        $type  = array_shift($parts);
        $name  = implode('.', $parts);

        $dir  = $this->container->get('mautic.helper.paths')->getSystemPath("dashboard.$type");
        $path = $dir.'/'.$name.'.json';

        if (file_exists($path) && is_writable($path)) {
            unlink($path);
        }

        return $this->redirect($this->generateUrl('le_dashboard_action', ['objectAction' => 'import']));
    }

    /**
     * Applies dashboard layout.
     *
     * @param null $file
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function applyDashboardFileAction($file = null)
    {
        if (!$file) {
            $file = $this->request->get('file');
        }

        $parts = explode('.', $file);
        $type  = array_shift($parts);
        $name  = implode('.', $parts);

        $dir  = $this->container->get('mautic.helper.paths')->getSystemPath("dashboard.$type");
        $path = $dir.'/'.$name.'.json';

        if (file_exists($path) && is_writable($path)) {
            $widgets = json_decode(file_get_contents($path), true);
            if (isset($widgets['widgets'])) {
                $widgets = $widgets['widgets'];
            }

            if ($widgets) {
                /** @var \Mautic\DashboardBundle\Model\DashboardModel $model */
                $model = $this->getModel('dashboard');

                $model->clearDashboardCache();

                $currentWidgets = $model->getWidgets();

                if (count($currentWidgets)) {
                    foreach ($currentWidgets as $widget) {
                        $model->deleteEntity($widget);
                    }
                }

                $filter = $model->getDefaultFilter();
                foreach ($widgets as $widget) {
                    $widget = $model->populateWidgetEntity($widget, $filter);
                    $model->saveEntity($widget);
                }

                return $this->redirect($this->get('router')->generate('le_dashboard_index'));
            }
        }

        return $this->redirect($this->generateUrl('le_dashboard_action', ['objectAction' => 'import']));
    }

    /**
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function importAction()
    {
        $preview = $this->request->get('preview');

        /** @var \Mautic\DashboardBundle\Model\DashboardModel $model */
        $model = $this->getModel('dashboard');

        $directories = [
            'user'   => $this->container->get('mautic.helper.paths')->getSystemPath('dashboard.user'),
            'global' => $this->container->get('mautic.helper.paths')->getSystemPath('dashboard.global'),
        ];

        $action = $this->generateUrl('le_dashboard_action', ['objectAction' => 'import']);
        $form   = $this->get('form.factory')->create('dashboard_upload', [], ['action' => $action]);

        if ($this->request->getMethod() == 'POST') {
            if (isset($form) && !$cancelled = $this->isFormCancelled($form)) {
                if ($this->isFormValid($form)) {
                    $fileData = $form['file']->getData();
                    if (!empty($fileData)) {
                        $extension = pathinfo($fileData->getClientOriginalName(), PATHINFO_EXTENSION);
                        if ($extension === 'json') {
                            $fileData->move($directories['user'], $fileData->getClientOriginalName());
                        } else {
                            $form->addError(
                                new FormError(
                                    $this->translator->trans('mautic.core.not.allowed.file.extension', ['%extension%' => $extension], 'validators')
                                )
                            );
                        }
                    } else {
                        $form->addError(
                            new FormError(
                                $this->translator->trans('mautic.dashboard.upload.filenotfound', [], 'validators')
                            )
                        );
                    }
                }
            }
        }

        $dashboardFiles = [];
        $dashboards     = [];

        // User specific layouts
        chdir($directories['user']);
        $dashboardFiles['user'] = glob('*.json');

        // Global dashboards
        chdir($directories['global']);
        $dashboardFiles['global'] = glob('*.json');

        foreach ($dashboardFiles as $type => $dirDashboardFiles) {
            $tempDashboard = [];
            foreach ($dirDashboardFiles as $dashId => $dashboard) {
                $dashboard = str_replace('.json', '', $dashboard);
                $config    = json_decode(
                    file_get_contents($directories[$type].'/'.$dirDashboardFiles[$dashId]),
                    true
                );

                // Check for name, description, etc
                $tempDashboard[$dashboard] = [
                    'type'        => $type,
                    'name'        => (isset($config['name'])) ? $config['name'] : $dashboard,
                    'description' => (isset($config['description'])) ? $config['description'] : '',
                    'widgets'     => (isset($config['widgets'])) ? $config['widgets'] : $config,
                ];
            }

            // Sort by name
            uasort($tempDashboard,
                function ($a, $b) {
                    return strnatcasecmp($a['name'], $b['name']);
                }
            );

            $dashboards = array_merge(
                $dashboards,
                $tempDashboard
            );
        }

        if ($preview && isset($dashboards[$preview])) {
            // @todo check is_writable
            $widgets = $dashboards[$preview]['widgets'];
            $filter  = $model->getDefaultFilter();
            $model->populateWidgetsContent($widgets, $filter);
        } else {
            $widgets = [];
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'       => $form->createView(),
                    'dashboards' => $dashboards,
                    'widgets'    => $widgets,
                    'preview'    => $preview,
                ],
                'contentTemplate' => 'MauticDashboardBundle:Dashboard:import.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_dashboard_index',
                    'leContent'     => 'dashboardImport',
                    'route'         => $this->generateUrl(
                        'le_dashboard_action',
                        [
                            'objectAction' => 'import',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Gets name from request and defaults it to the timestamp if not provided.
     *
     * @return string
     */
    private function getNameFromRequest()
    {
        return $this->request->get('name', (new \DateTime())->format('Y-m-dTH:i:s'));
    }
}
