<?php

namespace Mautic\SubscriptionBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\SubscriptionBundle\Entity\Billing;
use Mautic\SubscriptionBundle\Entity\KYC;
use PayPal\Api\Agreement;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

/**
 * Class SubscriptionController.
 */
class SubscriptionController extends CommonController
{
    public function indexAction()
    {
        return $this->delegateView([
            'viewParameters' => [
                'security'        => $this->get('mautic.security'),
                'contentOnly'     => 0,
                'plans'           => $this->factory->getAvailablePlans(),
                'isIndianCurrency'=> $this->getCurrencyType(),
                           ],
            'contentTemplate' => 'MauticSubscriptionBundle:Subscription:index.html.php',
            'passthroughVars' => [
                'activeLink'    => '#le_subscription_index',
                'leContent'     => 'subscription',
                'route'         => $this->generateUrl('le_subscription_index'),
            ],
        ]);
    }

    public function indexplanAction()
    {
        $repository=$this->get('le.core.repository.subscription');
        $planinfo  =$repository->getAllPrepaidPlans();

        return $this->delegateView([
        'viewParameters' => [
            'security'        => $this->get('mautic.security'),
            'contentOnly'     => 0,
            'plans'           => $planinfo,
            'isIndianCurrency'=> $this->getCurrencyType(),
        ],
        'contentTemplate' => 'MauticSubscriptionBundle:Plans:index.html.php',
        'passthroughVars' => [
            'activeLink'    => '#le_plan_index',
            'leContent'     => 'prepaidplans',
            'route'         => $this->generateUrl('le_plan_index'),
        ],
    ]);
    }

    public function indexpricingAction()
    {
        // $repository=$this->get('le.core.repository.subscription');
        //  $planinfo  =$repository->getAllPrepaidPlans();
        $paymenthelper     =$this->get('le.helper.payment');
        $configtransport   =$this->coreParametersHelper->getParameter('mailer_transport_name');
        $transport         ='viaothers';
        if ($configtransport == 'le.transport.vialeadsengage') {
            $transport='viaothers';
        }
        $paymentrepository  =$this->get('le.subscription.repository.payment');
        $lastpayment        = $paymentrepository->getLastPayment();
        $propayment         = 0;
        $planname           = '';
        if ($lastpayment != null) {
            $currentdate      = date('Y-m-d');
            $validityend      = $this->get('mautic.helper.licenseinfo')->getLicenseEndDate();
            $amount           = $lastpayment->getAmount();
            $planname         = $lastpayment->getPlanName();

            $propayment = $this->getProrataAmount($currentdate, $validityend, $amount);
        }

        return $this->delegateView([
            'viewParameters' => [
                'security'        => $this->get('mautic.security'),
                'contentOnly'     => 0,
                'letoken'         => $paymenthelper->getUUIDv4(),
                'transport'       => $transport,
                'tmpl'            => 'index',
                'proamount'       => $propayment,
                'planname'        => $planname,
            ],
            'contentTemplate' => 'MauticSubscriptionBundle:Pricing:index.html.php',
            'passthroughVars' => [
                'activeLink'    => '#le_pricing_index',
                'leContent'     => 'pricingplans',
                'route'         => $this->generateUrl('le_pricing_index'),
            ],
        ]);
    }

    public function subscriptionstatusAction()
    {
        $paymentid       = $this->request->get('paymentid');
        $subscriptionid  = $this->request->get('subscriptionid');
        $provider        = $this->request->get('provider');
        $status          = $this->request->get('status');
        if ($provider == 'paypal') {
            $ectoken         = $this->request->get('token');
            if ($status) {
                $agreement = new Agreement();
                try {
                    $paymenthelper=$this->get('le.helper.payment');
                    $agreement->execute($ectoken, $paymenthelper->getPayPalApiContext());
                    $subscriptionid=$agreement->getId();
                    $paymentid     ='NA';
                } catch (Exception $ex) {
                    $subscriptionid='NA';
                    $paymentid     ='NA';
                    $status        =false;
                }
            } else {
                $subscriptionid='NA';
                $paymentid     ='NA';
            }
        }

        return $this->delegateView([
        'viewParameters' => [
            'security'       => $this->get('mautic.security'),
            'contentOnly'    => 0,
            'paymentid'      => $paymentid,
            'subscriptionid' => $subscriptionid,
            'status'         => $status,
        ],
        'contentTemplate' => 'MauticSubscriptionBundle:Subscription:status.html.php',
        'passthroughVars' => [
            'activeLink'    => '#le_subscription_status',
            'leContent'     => 'subscription-status',
            'route'         => $this->generateUrl('le_subscription_status'),
        ],
    ]);
    }

    public function paymentstatusAction()
    {
        $orderid = $this->request->get('id', '');
        if ($orderid != '') {
            $paymentrepository  =$this->get('le.subscription.repository.payment');
            $paymenthistory     = $paymentrepository->findBy(['orderid' => $orderid]);
            $payment            = $paymenthistory[0];

            return $this->delegateView([
                'viewParameters' => [
                    'security'       => $this->get('mautic.security'),
                    'contentOnly'    => 0,
                    'paymentdetails' => $payment,
                ],
                'contentTemplate' => 'MauticSubscriptionBundle:Pricing:status.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_payment_status',
                    'leContent'     => 'payment-status',
                    'route'         => $this->generateUrl('le_payment_status'),
                ],
            ]);
        } else {
            $provider        = $this->request->get('provider');
            $status          = $this->request->get('status');
            if ($provider == 'paypal') {
                if ($status) {
                    $paymenthelper     =$this->get('le.helper.payment');
                    $apiContext        =$paymenthelper->getPayPalApiContext();
                    $paymentid         =$this->request->get('paymentId');
                    $payerid           =$this->request->get('PayerID');
                    $payment           =Payment::get($paymentid, $apiContext);
                    $paymentstate      =$payment->getState();
                    $transactions      =$payment->getTransactions();
                    $transaction       =$transactions[0];
                    $orderid           =$transaction->getInvoiceNumber();
                    $itemlist          =$transaction->getItemList();
                    $items             =$itemlist->getItems();
                    $item              =$items[0];
                    $plankey           =$item->getSku();
                    if ($paymentstate == 'created') {
                        $execution = new PaymentExecution();
                        $execution->setPayerId($payerid);
                        try {
                            $result    = $payment->execute($execution, $apiContext);
                            $repository=$this->get('le.core.repository.subscription');
                            $repository->updateEmailCredits($plankey);
//                    try{
//                        $payment = Payment::get($paymentid, $apiContext);
//                    }catch(Exception $ex){
//                        $status=false;
//                    }
                        } catch (Exception $ex) {
                            $status=false;
                        }
                    }
                } else {
                    $repository         =$this->get('le.core.repository.subscription');
                    $planinfo           =$repository->getAllPrepaidPlans();
                    $session            = $this->get('session');
                    $orderid            =$session->get('le.payment.orderid', '');
                    $paymentrepository  =$this->get('le.subscription.repository.payment');
                    $paymentrepository->updatePaymentStatus($orderid, '', 'Cancelled');

                    return $this->postActionRedirect(
                        [
                            'returnUrl'       => $this->generateUrl('le_plan_index'),
                            'viewParameters'  => [
                                'security'        => $this->get('mautic.security'),
                                'contentOnly'     => 0,
                                'plans'           => $planinfo,
                                'isIndianCurrency'=> $this->getCurrencyType(),
                            ],
                            'contentTemplate' => 'MauticSubscriptionBundle:Plans:index',
                            'passthroughVars' => [
                                'activeLink'    => '#le_plan_index',
                                'leContent'     => 'prepaidplans',
                            ],
                        ]
                    );
                }
            } else {
                $paymentid        = $this->request->get('paymentid');
                $orderid          = $this->request->get('orderid');
            }

            if ($status) {
                $paymentrepository  =$this->get('le.subscription.repository.payment');
                $paymentrepository->updatePaymentStatus($orderid, $paymentid, 'Paid');
                $paymenthistory     = $paymentrepository->findBy(['orderid' => $orderid]);
                $payment            =$paymenthistory[0];
            }

            return $this->delegateView([
                'viewParameters' => [
                    'security'       => $this->get('mautic.security'),
                    'contentOnly'    => 0,
                    'paymentdetails' => $payment,
                ],
                'contentTemplate' => 'MauticSubscriptionBundle:Plans:status.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_payment_status',
                    'leContent'     => 'payment-status',
                    'route'         => $this->generateUrl('le_payment_status'),
                ],
            ]);
        }
    }

    public function getCurrencyType()
    {
//        $clientip        = $this->request->getClientIp();
//        $dataArray       = json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip='.$clientip));
//        $countrycode     =$dataArray->{'geoplugin_countryCode'};
//        $isIndianCurrency=false;
//        if ($countrycode == '' || $isIndianCurrency == 'IN') {
//            $isIndianCurrency=true;
//        }
        /** @var \Mautic\SubscriptionBundle\Model\BillingModel $billingmodel */
        $billingmodel  = $this->getModel('subscription.billinginfo');
        $billingrepo   = $billingmodel->getRepository();
        $billingentity = $billingrepo->findAll();
        if (sizeof($billingentity) > 0) {
            $billing = $billingentity[0]; //$model->getEntity(1);
        } else {
            $billing = new Billing();
        }
        $country         =$billing->getCountry();
        $isIndianCurrency=false;
        if (empty($country) || $country == 'India') {
            $isIndianCurrency=true;
        }

        return $isIndianCurrency;
    }

    public function offerAction()
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
            $showsetup      = true;
            $billformview   = $kycview[0];
            $accformview    = $kycview[1];
            $userformview   = $kycview[2];
            $videoURL       = '';
            $showvideo      = false;
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
        $action          = $this->generateUrl('le_pricing_index');
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
                        'activeLink'    => '#le_contact_index',
                        'leContent'     => 'subscription',
                        'route'         => $this->generateUrl('le_contact_index'),
                    ],
                ]
            );
        //} else {
        //    return $this->delegateRedirect($this->generateUrl('le_contact_index'));
        //}
    }

    public function welcomeAction()
    {
        $loginsession      = $this->get('session');
        $loginarg          = $loginsession->get('isLogin');
        $dbhost            = $this->coreParametersHelper->getParameter('le_db_host');
        $licenseinfoHelper = $this->get('mautic.helper.licenseinfo');
        $kycview           = $licenseinfoHelper->getFirstTimeSetup('sdasda', true);
        $stepstring        = $this->request->get('step', 'flname');
        $billformview      = $kycview[0];
        $accformview       = $kycview[1];
        $userformview      = $kycview[2];
        /** @var \Mautic\SubscriptionBundle\Entity\Billing $billingEntity */
        $billingEntity  = $kycview[3];
        /** @var \Mautic\SubscriptionBundle\Entity\Account $accountEntity */
        $accountEntity  = $kycview[4];
        /** @var \Mautic\UserBundle\Entity\User $userEntity */
        $userEntity     = $kycview[5];
        $ismobile       = InputHelper::isMobile();

        /** @var \Mautic\UserBundle\Model\UserModel $userModel */
        $userModel = $this->getModel('user.user');
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $accountModel */
        $accountModel  = $this->getModel('subscription.accountinfo');
        /** @var \Mautic\SubscriptionBundle\Model\BillingModel $billingmodel */
        $billingmodel  = $this->getModel('subscription.billinginfo');

        /** @var \Mautic\SubscriptionBundle\Model\KYCModel $kycmodel */
        $kycmodel         = $this->getModel('subscription.kycinfo');
        $kycrepo          = $kycmodel->getRepository();
        $kycentity        = $kycrepo->findAll();
        if (sizeof($kycentity) > 0) {
            $kyc = $kycentity[0]; //$model->getEntity(1);
        } else {
            $kyc = new KYC();
        }
        $countrydetails  = $licenseinfoHelper->getCountryName();
        $timezone        = $countrydetails['timezone'];
        $countryname     = $countrydetails['countryname'];
        $city            = $countrydetails['city'];
        $state           = $countrydetails['state'];
        $repository      =$this->get('le.core.repository.subscription');
        $dbname          = $this->coreParametersHelper->getParameter('db_name');
        $appid           = str_replace('leadsengage_apps', '', $dbname);
        $signuprepository=$this->get('le.core.repository.signup');
        if ($this->request->getMethod() == 'POST') {
            $data = $this->request->request->get('welcome');
            if ($stepstring == 'flname') {
                $userEntity->setFirstName($data['firstname']);
                $userEntity->setLastName($data['lastname']);
                $userEntity->setMobile($data['phone']);
                $accountEntity->setPhonenumber($data['phone']);
                $accountEntity->setEmail($userEntity->getEmail());
                $signupinfo     =$repository->getSignupInfo($userEntity->getEmail());
                if (!empty($signupinfo)) {
                    $accountEntity->setDomainname($signupinfo[0]['f5']);
                    $accountEntity->setAccountname($signupinfo[0]['f2']);
                }
                $userModel->saveEntity($userEntity);
                $accountModel->saveEntity($accountEntity);
                $signupData          = $data;
                $signupData['email'] = $userEntity->getEmail();
                $signuprepository->updateSignupUserInfo($signupData);

                return $this->delegateRedirect($this->generateUrl('le_welcome_action', ['step' => 'aboutyourbusiness']));
            } elseif ($stepstring == 'aboutyourbusiness') {
                $accountEntity->setWebsite($data['websiteurl']);
                $accountEntity->setAccountname($data['business']);
                $accountModel->saveEntity($accountEntity);
                $billingEntity->setCompanyname($data['business']);
                $billingmodel->saveEntity($billingEntity);
                $kyc->setIndustry($data['industry']);
                //$kyc->setUsercount($data['empcount']);
                //$kyc->setYearsactive($data['org_experience']);
                $kyc->setSubscribercount($data['emailvol']);
                //$kyc->setSubscribersource($data['listsize']);
                $kyc->setPrevioussoftware($data['currentesp']);
                $kycmodel->saveEntity($kyc);
                $businessData          = $data;
                $businessData['email'] = $userEntity->getEmail();
                $signuprepository->updateSignupUserBusinessInfo($businessData);

                return $this->delegateRedirect($this->generateUrl('le_welcome_action', ['step' => 'addressinfo']));
            } elseif ($stepstring == 'addressinfo') {
                $address = $data['address-line-1'];
                if ($data['address-line-2'] != '') {
                    $address = $address.','.$data['address-line-2'];
                }
                $billingEntity->setCompanyaddress($address);
                $billingEntity->setCity($data['city']);
                $billingEntity->setState($data['state']);
                $billingEntity->setCountry($data['country']);
                $billingEntity->setGstnumber($data['taxid']);
                $billingEntity->setPostalcode($data['zip']);
                $billingmodel->saveEntity($billingEntity);
                $accountEntity->setTimezone($data['timezone']);
                $accountModel->saveEntity($accountEntity);
                $userEntity->setTimezone($data['timezone']);
                $userModel->saveEntity($userEntity);
                $addressData          = $data;
                $addressData['email'] = $userEntity->getEmail();
                $signuprepository->updateSignupUserAddressInfo($addressData);
                /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
                $configurator = $this->get('mautic.configurator');
                $isWritabale  = $configurator->isFileWritable();
                if ($isWritabale) {
                    if ($data['timezone'] != '') {
                        $configurator->mergeParameters(['default_timezone' => $data['timezone']]);
                        $configurator->write();
                    }
                    if ($address != '') {
                        $postaladdress = $address.','.$data['zip'].','.$data['city'].','.$data['state'].','.$data['country'];
                        $configurator->mergeParameters(['postal_address' => $postaladdress]);
                        $configurator->write();
                    }
                }

                return $this->delegateRedirect($this->generateUrl('le_dashboard_index'));
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'       => $accformview,
                    'billform'   => $billformview,
                    'userform'   => $userformview,
                    'user'       => $userEntity,
                    'billing'    => $billingEntity,
                    'account'    => $accountEntity,
                    'kyc'        => $kyc,
                    'isMobile'   => $ismobile,
                    'setupUrl'   => $this->generateUrl('le_welcome_action'),
                    'step'       => $stepstring,
                    'timezone'   => $accountEntity->getTimezone() == '' ? $timezone : $accountEntity->getTimezone(),
                    'country'    => $billingEntity->getCountry() == '' ? $countryname : $billingEntity->getCountry(),
                    'city'       => $billingEntity->getCity() == '' ? $city : $billingEntity->getCity(),
                    'state'      => $billingEntity->getState() == '' ? $state : $billingEntity->getState(),
                ],
                'contentTemplate' => 'MauticSubscriptionBundle:Subscription:setup.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_welcome_action',
                    'leContent'     => 'welcome',
                    'route'         => $this->generateUrl('le_welcome_action'),
                ],
            ]
        );
    }

    protected function getProrataAmount($start, $end, $amount)
    {
        $date1        = new \DateTime($start);
        $date2        = new \DateTime($end);
        $diff         = $date2->diff($date1)->format('%a');
        $diff         = $diff + 1;
        $prorataamount=$amount * ($diff / 31);

        return round($prorataamount);
    }
}
