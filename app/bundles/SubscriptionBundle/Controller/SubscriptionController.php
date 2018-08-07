<?php

namespace Mautic\SubscriptionBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use Mautic\SubscriptionBundle\Entity\Billing;
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
                'mauticContent' => 'subscription',
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
            'mauticContent' => 'prepaidplans',
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

        return $this->delegateView([
            'viewParameters' => [
                'security'        => $this->get('mautic.security'),
                'contentOnly'     => 0,
                'letoken'         => $paymenthelper->getUUIDv4(),
                'transport'       => $transport,
            ],
            'contentTemplate' => 'MauticSubscriptionBundle:Pricing:index.html.php',
            'passthroughVars' => [
                'activeLink'    => '#le_pricing_index',
                'mauticContent' => 'pricingplans',
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
            'mauticContent' => 'subscription-status',
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
                    'mauticContent' => 'payment-status',
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
                                'mauticContent' => 'prepaidplans',
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
                    'mauticContent' => 'payment-status',
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
}
