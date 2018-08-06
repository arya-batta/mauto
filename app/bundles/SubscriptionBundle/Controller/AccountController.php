<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SubscriptionBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Mautic\SubscriptionBundle\Entity\Account;
use Mautic\SubscriptionBundle\Entity\Billing;
use Mautic\SubscriptionBundle\Entity\StripeCard;

/**
 * Class AccountController.
 */
class AccountController extends FormController
{
    /**
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction()
    {
        if (!$this->user->isAdmin() && !$this->user->isCustomAdmin() && $this->coreParametersHelper->getParameter('accountinfo_disabled')) {
            return $this->accessDenied();
        }

        $paymentrepository  =$this->get('le.subscription.repository.payment');
        $planType           ='Trial';
        $lastpayment        = $paymentrepository->getLastPayment();
        if ($lastpayment != null) {
            $planType    ='Paid';
        }
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $model */
        $model         = $this->getModel('subscription.accountinfo');
        $action        = $this->generateUrl('mautic_accountinfo_action', ['objectAction' => 'edit']);
        $accrepo       = $model->getRepository();
        $accountentity = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0]; //$model->getEntity(1);
        } else {
            $account = new Account();
        }
        $form          = $model->createForm($account, $this->get('form.factory'), $action);
        if ($this->request->getMethod() == 'POST') {
            $isValid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($isValid = $this->isFormValid($form)) {
                    $data           = $this->request->request->get('accountinfo');
                    $accountname    = $data['accountname'];
                    $domainname     = $data['domainname'];
                    $email          = $data['email'];
                    $phonenumber    = $data['phonenumber'];
                    $currencysymbol = $data['currencysymbol'];
                    $timezone       = $data['timezone'];
                    $accountid      = $data['accountid'];
                    if (isset($data['needpoweredby'])) {
                        $needpoweredby = $data['needpoweredby'];
                    } else {
                        $needpoweredby = 1;
                    }
                    $account->setAccountname($accountname);
                    $account->setDomainname($domainname);
                    $account->setEmail($email);
                    $account->setPhonenumber($phonenumber);
                    $account->setCurrencysymbol($currencysymbol);
                    $account->setTimezone($timezone);
                    $account->setAccountid($accountid);
                    $account->setNeedpoweredby($needpoweredby);
                    /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
                    $configurator = $this->get('mautic.configurator');
                    $isWritabale  = $configurator->isFileWritable();
                    if ($isWritabale && $timezone != '') {
                        $configurator->mergeParameters(['default_timezone' => $timezone]);
                        $configurator->write();
                    }
                    $model->saveEntity($account);
                }
            }
            if ($cancelled || $isValid) {
                if (!$cancelled && $this->isFormApplied($form)) {
                    return $this->delegateRedirect($this->generateUrl('mautic_accountinfo_action', ['objectAction' => 'edit']));
                } else {
                    return $this->delegateRedirect($this->generateUrl('mautic_dashboard_index'));
                }
            }
        }
        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';

        return $this->delegateView([
            'viewParameters' => [
                'tmpl'               => $tmpl,
                'form'               => $form->createView(),
                'security'           => $this->get('mautic.security'),
                'actionRoute'        => 'mautic_accountinfo_action',
                'typePrefix'         => 'form',
                'planType'           => $planType,
            ],
            'contentTemplate' => 'MauticSubscriptionBundle:AccountInfo:form.html.php',
            'passthroughVars' => [
                'activeLink'    => '#mautic_accountinfo_index',
                'mauticContent' => 'accountinfo',
                'route'         => $this->generateUrl('mautic_accountinfo_action', ['objectAction' => 'edit']),
            ],
        ]);
    }

    /**
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function billingAction()
    {
        if (!$this->user->isAdmin() && !$this->user->isCustomAdmin() && $this->coreParametersHelper->getParameter('accountinfo_disabled')) {
            return $this->accessDenied();
        }

        /** @var \Mautic\SubscriptionBundle\Model\BillingModel $model */
        $model         = $this->getModel('subscription.billinginfo');
        $action        = $this->generateUrl('mautic_accountinfo_action', ['objectAction' => 'billing']);
        $billingrepo   = $model->getRepository();
        $billingentity = $billingrepo->findAll();
        if (sizeof($billingentity) > 0) {
            $billing = $billingentity[0]; //$model->getEntity(1);
        } else {
            $billing = new Billing();
        }
        $form          = $model->createForm($billing, $this->get('form.factory'), $action, ['isBilling' => true]);
        if ($this->request->getMethod() == 'POST') {
            $isValid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($isValid = $this->isFormValid($form)) {
                    $data               = $this->request->request->get('billinginfo');
                    $companyname        = $data['companyname'];
                    $companyaddressname = $data['companyaddress'];
                    $accountingemail    = $data['accountingemail'];
                    $billing->setCompanyname($companyname);
                    $billing->setCompanyaddress($companyaddressname);
                    $billing->setAccountingemail($accountingemail);
                    $model->saveEntity($billing);
                }
            }
            if ($cancelled || $isValid) {
                if (!$cancelled && $this->isFormApplied($form)) {
                    return $this->delegateRedirect($this->generateUrl('mautic_accountinfo_action', ['objectAction' => 'billing']));
                } else {
                    return $this->delegateRedirect($this->generateUrl('mautic_dashboard_index'));
                }
            }
        }
        $tmpl                 = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';
        $emailModel           =$this->getModel('email');
        $statrepo             =$emailModel->getStatRepository();
        $licenseinfo          =$this->get('mautic.helper.licenseinfo')->getLicenseEntity();
        $licensestart         =$licenseinfo->getLicenseStart();
        $contactUsage         =$licenseinfo->getActualRecordCount();
        $totalContactCredits  =$licenseinfo->getTotalRecordCount();
        $totalEmailCredits    =$licenseinfo->getTotalEmailCount();
        $currentDate          = date('Y-m-d');
        $monthStartDate       = date('Y-m-01');
        $emailValidityEndDate = $this->get('mautic.helper.licenseinfo')->getEmailValidityEndDate();
        $emailValidityEndDays = round((strtotime($emailValidityEndDate) - strtotime($currentDate)) / 86400);
        $emailUsage           =$statrepo->getSentCountsByDate($monthStartDate);
        $trialEndDays         =$this->get('mautic.helper.licenseinfo')->getLicenseRemainingDays();
        $planType             ='Trial';
        $paymentrepository    =$this->get('le.subscription.repository.payment');
        $lastpayment          =$paymentrepository->getLastPayment();
        $validityTill         ='';
        $planAmount           ='';
        $datehelper           =$this->get('mautic.helper.template.date');
        if ($lastpayment != null) {
            $planType    ='Paid';
            $validityTill=$datehelper->toDate($lastpayment->getValidityTill());
            $planAmount  =$lastpayment->getCurrency().$lastpayment->getAmount();
        }

        return $this->delegateView([
            'viewParameters' => [
                'tmpl'               => $tmpl,
                'form'               => $form->createView(),
                'security'           => $this->get('mautic.security'),
                'actionRoute'        => 'mautic_accountinfo_action',
                'typePrefix'         => 'form',
                'emailUsage'         => $emailUsage,
                'contactUsage'       => $contactUsage,
                'planType'           => $planType,
                'vallidityTill'      => $validityTill,
                'planAmount'         => $planAmount,
                'trialEndDays'       => $emailValidityEndDays.'',
                'totalContactCredits'=> $totalContactCredits,
                'totalEmailCredits'  => $totalEmailCredits,
            ],
            'contentTemplate' => 'MauticSubscriptionBundle:AccountInfo:billing.html.php',
            'passthroughVars' => [
                'activeLink'    => '#mautic_accountinfo_index',
                'mauticContent' => 'accountinfo',
                'route'         => $this->generateUrl('mautic_accountinfo_action', ['objectAction' => 'billing']),
            ],
        ]);
    }

    /**
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function paymentAction()
    {
        if (!$this->user->isAdmin() && !$this->user->isCustomAdmin() && $this->coreParametersHelper->getParameter('accountinfo_disabled')) {
            return $this->accessDenied();
        }

        $tmpl               = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';
        $paymentrepository  =$this->get('le.subscription.repository.payment');
        $planType           ='Trial';
        $lastpayment        = $paymentrepository->getLastPayment();
        if ($lastpayment != null) {
            $planType    ='Paid';
        }
        $paymentalias       =$paymentrepository->getTableAlias();
        $filter             = [
            'force'  => [
                ['column' => $paymentalias.'.paymentstatus', 'expr' => 'neq', 'value' => 'Initiated'],
            ],
        ];
        $args= [
            'filter'         => $filter,
            'orderBy'        => $paymentalias.'.id',
            'orderByDir'     => 'DESC',
         //   'ignore_paginator' => true,
        ];
        $payments=$paymentrepository->getEntities($args);

        return $this->delegateView([
            'viewParameters' => [
                'tmpl'               => $tmpl,
                'security'           => $this->get('mautic.security'),
                'actionRoute'        => 'mautic_accountinfo_action',
                'typePrefix'         => 'form',
                'payments'           => $payments,
                'planType'           => $planType,
            ],
            'contentTemplate' => 'MauticSubscriptionBundle:AccountInfo:payment.html.php',
            'passthroughVars' => [
                'activeLink'    => '#mautic_accountinfo_index',
                'mauticContent' => 'accountinfo',
                'route'         => $this->generateUrl('mautic_accountinfo_action', ['objectAction' => 'payment']),
            ],
        ]);
    }

    /**
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function cancelAction()
    {
        if (!$this->user->isAdmin() && !$this->user->isCustomAdmin() && $this->coreParametersHelper->getParameter('accountinfo_disabled')) {
            return $this->accessDenied();
        }
        $paymentrepository    =$this->get('le.subscription.repository.payment');
        $appStatus            = $this->get('mautic.helper.licenseinfo')->getAppStatus();
        $recordCount          = $this->get('mautic.helper.licenseinfo')->getTotalRecordCount();
        $licenseEndDate       = $this->get('mautic.helper.licenseinfo')->getLicenseEndDate();
        $licenseRemDays       = $this->get('mautic.helper.licenseinfo')->getLicenseRemainingDays();
        $subcancel            = $this->get('mautic.helper.licenseinfo')->getCancelDate();
        $subcanceldate        = date('F d, Y', strtotime($subcancel));
        $datehelper           =$this->get('mautic.helper.template.date');

        if ($recordCount == 'UL') {
            $recordCount= 'unlimited';
        }
        $planType           ='Trial';
        $lastpayment        = $paymentrepository->getLastPayment();
        if ($lastpayment != null) {
            $planType    ='Paid';
        }
        $license='';
        if ($planType == 'Trial') {
            $license = $licenseRemDays.' days';
        } else {
            if ($lastpayment != null) {
                $planType    ='Paid';
                $license     = $datehelper->toDate($lastpayment->getValidityTill());
            }
            $license = date('F d, Y', strtotime($license.' + 1 days'));
        }
        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';

        return $this->delegateView([
            'viewParameters' => [
                'tmpl'               => $tmpl,
                'security'           => $this->get('mautic.security'),
                'actionRoute'        => 'mautic_accountinfo_action',
                'typePrefix'         => 'form',
                'appstatus'          => $appStatus,
                'recordcount'        => $recordCount,
                'licenseenddate'     => $license,
                'planType'           => $planType,
                'canceldate'         => $subcanceldate,
             ],
            'contentTemplate' => 'MauticSubscriptionBundle:AccountInfo:cancel.html.php',
            'passthroughVars' => [
                'activeLink'    => '#mautic_accountinfo_index',
                'mauticContent' => 'accountinfo',
                'route'         => $this->generateUrl('mautic_accountinfo_action', ['objectAction' => 'cancel']),
            ],
        ]);
    }

    /**
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function cardinfoAction()
    {
        if (!$this->user->isAdmin() && !$this->user->isCustomAdmin() && $this->coreParametersHelper->getParameter('accountinfo_disabled')) {
            return $this->accessDenied();
        }
        $paymentrepository  =$this->get('le.subscription.repository.payment');
        $planType           ='Trial';
        $lastpayment        = $paymentrepository->getLastPayment();
        if ($lastpayment != null) {
            $planType    ='Paid';
        }
        $tmpl               = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';
        $stripecardrepo     =$this->get('le.subscription.repository.stripecard');
        $stripecards        = $stripecardrepo->findAll();
        if (sizeof($stripecards) > 0) {
            $stripecard = $stripecards[0];
        } else {
            $stripecard = new StripeCard();
        }
        $paymenthelper     =$this->get('le.helper.payment');

        return $this->delegateView([
            'viewParameters' => [
                'tmpl'               => $tmpl,
                'security'           => $this->get('mautic.security'),
                'actionRoute'        => 'mautic_accountinfo_action',
                'typePrefix'         => 'form',
                'stripecard'         => $stripecard,
                'letoken'            => $paymenthelper->getUUIDv4(),
                'planType'           => $planType,
            ],
            'contentTemplate' => 'MauticSubscriptionBundle:AccountInfo:cardinfo.html.php',
            'passthroughVars' => [
                'activeLink'    => '#mautic_accountinfo_index',
                'mauticContent' => 'accountinfo',
                'route'         => $this->generateUrl('mautic_accountinfo_action', ['objectAction' => 'cardinfo']),
            ],
        ]);
    }
}
