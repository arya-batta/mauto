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

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\SubscriptionBundle\Entity\Account;
use Mautic\SubscriptionBundle\Entity\Billing;
use Mautic\SubscriptionBundle\Entity\KYC;
use Mautic\SubscriptionBundle\Entity\PaymentHistory;
use Mautic\SubscriptionBundle\Entity\StripeCard;
use PayPal\Api\Agreement;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Plan;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\Error;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AjaxController.
 */
class AjaxController extends CommonAjaxController
{
    /**
     * User Subscription.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function makepaymentAction(Request $request)
    {
        $username     = $request->request->get('username');
        $useremail    = $request->request->get('useremail');
        $useraddress  = $request->request->get('useraddress');
        $planname     = $request->request->get('planname');
        $plancycle    = $request->request->get('plancycle');
        $plancurrency = $request->request->get('plancurrency');
        $provider     ='paypal';
        if ($plancurrency == '₹') {
            $provider = 'razorpay';
        }
        $dataArray = ['success' => true];
        $repository=$this->get('le.core.repository.subscription');
        $planinfo  =$repository->getPlanInfo($provider, $planname, $plancycle);
        if (!empty($planinfo)) {
            $planid= $planinfo[0]['planid'];
            if ($provider == 'razorpay') {
                try {
                    $totalcount=120;
                    if ($plancycle == 'year') {
                        $totalcount=10;
                    }
                    //test card details
                    //5104015555555558 any cvv any future expiry date
                    $apikey                      =$this->coreParametersHelper->getParameter('razoparpay_apikey');
                    $apisecret                   =$this->coreParametersHelper->getParameter('razoparpay_apisecret');
                    $api                         = new Api($apikey, $apisecret);
                    $subscription                = $api->subscription->create(['plan_id' => $planid, 'customer_notify' => 1, 'total_count' => $totalcount]);
                    $subscriptionid              =$subscription->id;
                    $dataArray['subscriptionid'] =$subscriptionid;
                    $dataArray['provider']       ='razorpay';
                    $dataArray['apikey']         =$apikey;
                    //file_put_contents("/var/www/mauto/app/logs/payment.txt","New Subscription:".$subscriptionid."\n",FILE_APPEND);
                } catch (Error $ex) {
                    $dataArray['success']  =false;
                    $dataArray['errormsg'] ='Payment Error:'.$ex->getMessage();
                }
            } else {
                date_default_timezone_set('Asia/Kolkata');
                $timezone = date_default_timezone_get();
                $start_at = new \DateTime();
                $start_at->add(new \DateInterval('PT25H'));
                $start_at     =$start_at->format(\DateTime::RFC3339);
                $username     =str_replace(' ', '_', $username);
                $agreementname=$planname.'_'.$username.'_'.date('YmdHis');
                $agreement    = new Agreement();
                $agreement->setName($agreementname)
                    ->setDescription($agreementname)
                    ->setStartDate($start_at);
                $plan = new Plan();
                $plan->setId($planid);
                $agreement->setPlan($plan);

                $payer = new Payer();
                $payer->setPaymentMethod('paypal');
                $agreement->setPayer($payer);
                $clienthost          =$request->getHost();
                $clientprotocal      =$request->getScheme();
                $successparameters   =['provider' => 'paypal', 'status' => true];
                $returnUrl           = $this->generateUrl('le_subscription_status', $successparameters);
                $returnUrl           =$clientprotocal.'://'.$clienthost.$returnUrl;
                $cancelparameters    =['provider' => 'paypal', 'status' => false];
                $cancelUrl           = $this->generateUrl('le_subscription_status', $cancelparameters);
                $cancelUrl           =$clientprotocal.'://'.$clienthost.$cancelUrl;
                $merchantPreferences = new MerchantPreferences();
                $merchantPreferences->setReturnUrl($returnUrl)
                    ->setCancelUrl($cancelUrl)
                    ->setAutoBillAmount('yes')
                    ->setInitialFailAmountAction('CONTINUE')
                    ->setMaxFailAttempts('3')
//   ->setSetupFee(new Currency(array('value' => 1, 'currency' => 'USD')))
                ;
                $agreement->setOverrideMerchantPreferences($merchantPreferences);
                $cloneagreement = clone $agreement;
                try {
                    $paymenthelper            =$this->get('le.helper.payment');
                    $agreement                = $agreement->create($paymenthelper->getPayPalApiContext());
                    $approvalUrl              = $agreement->getApprovalLink();
                    $dataArray['approvalurl'] =$approvalUrl;
                } catch (Exception $ex) {
                    $dataArray['success']  =false;
                    $dataArray['errormsg'] ='Payment Error:'.$ex->getMessage();
                }
                $userTimeZone =$this->user->getTimezone();
                date_default_timezone_set($userTimeZone);
            }
        } else {
            $dataArray['success']  =false;
            $dataArray['errormsg'] ='Payment Error: Oops! Technical Error Found.Please contact support';
        }

        return $this->sendJsonResponse($dataArray);
    }

    public function updateKYCAction(Request $request)
    {
        $data = $request->request->all();

        /** @var \Mautic\UserBundle\Model\UserModel $usermodel */
        $usermodel     = $this->getModel('user.user');
        $userentity    = $usermodel->getCurrentUserEntity();

        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $model */
        $model         = $this->getModel('subscription.accountinfo');
        $accrepo       = $model->getRepository();
        $accountentity = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0]; //$model->getEntity(1);
        } else {
            $account = new Account();
        }

        /** @var \Mautic\SubscriptionBundle\Model\BillingModel $billingmodel */
        $billingmodel  = $this->getModel('subscription.billinginfo');
        $billingrepo   = $billingmodel->getRepository();
        $billingentity = $billingrepo->findAll();
        if (sizeof($billingentity) > 0) {
            $billing = $billingentity[0]; //$model->getEntity(1);
        } else {
            $billing = new Billing();
        }

        $firstName      = $data['firstName'];
        $lastName       = $data['lastName'];
        $email          = $data['email'];

        $companyname    = $data['companyname'];
        $companyaddress = $data['companyaddress'];
        $postalcode     = $data['postalcode'];
        $state          = $data['state'];
        $city           = $data['city'];
        $country        = $data['country'];
        $gstnumber      = $data['gstnumber'];
        $billing->setCompanyname($companyname);
        $billing->setAccountingemail($email);
        $billing->setCompanyaddress($companyaddress);
        $billing->setPostalcode($postalcode);
        $billing->setState($state);
        $billing->setCity($city);
        $billing->setCountry($country);
        $billing->setGstnumber($gstnumber);
        $billingmodel->saveEntity($billing);

        $phonenumber    = $data['phonenumber'];
        $timezone       = $data['timezone'];
        $website        = $data['website'];
        $repository     =$this->get('le.core.repository.subscription');
        $signupinfo     =$repository->getSignupInfo($userentity->getEmail());
        if (!empty($signupinfo)) {
            $account->setDomainname($signupinfo[0]['f5']);
            $account->setAccountname($signupinfo[0]['f2']);
        }
        $account->setAccountname($companyname);
        $account->setTimezone($timezone);
        $account->setPhonenumber($phonenumber);
        $account->setWebsite($website);
        $account->setEmail($email);
        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator = $this->get('mautic.configurator');
        $isWritabale  = $configurator->isFileWritable();
        if ($isWritabale) {
            if ($timezone != '') {
                $configurator->mergeParameters(['default_timezone' => $timezone]);
                $configurator->write();
            }
            if ($companyaddress != '') {
                $address = $companyname.', '.$companyaddress.', '.$city.', '.$postalcode.'. '.$state.', '.$country.'.';
                $configurator->mergeParameters(['postal_address' => $address]);
                $configurator->write();
            }
            /** @var \Mautic\CoreBundle\Helper\CacheHelper $cacheHelper */
            $cacheHelper = $this->get('mautic.helper.cache');
            $cacheHelper->clearContainerFile();
        }
        $dbname          = $this->coreParametersHelper->getParameter('db_name');
        $appid           = str_replace('leadsengage_apps', '', $dbname);
        $signuprepository=$this->get('le.core.repository.signup');
        $signuprepository->updateSignupInfo($data, $data, $data, $appid);
        $model->saveEntity($account);

        $signuplead = $signuprepository->getSignupLeadinfo($appid);
        if (!empty($signuplead)) {
            $signupleadid = $signuplead[0]['id'];
            $signuprepository->updateLeadwithTag($this->translator->trans('le.profile.updated.tag.name'), $signupleadid);
            $campaignid   = $this->coreParametersHelper->getParameter('campaign_id');
            $sourceId     = $this->coreParametersHelper->getParameter('source_event_id');
            $goalId       = $this->coreParametersHelper->getParameter('goal_event_id');
            if ($campaignid != null) {
                $signuprepository->linkLeadwithCampaign($campaignid, $signupleadid);
            }
            if ($campaignid != null && $sourceId != null) {
                $signuprepository->addLeadinCampaignLog($campaignid, $signupleadid, $sourceId, 0);
            }
        }

        $userentity->setEmail($email);
        $userentity->setFirstName($firstName);
        $userentity->setLastName($lastName);
        $userentity->setMobile($phonenumber);
        $userentity->setTimezone($timezone);
        $usermodel->saveEntity($userentity);
        $repository     = $this->get('le.core.repository.subscription');
        $otpsend        = false;
        $otp            = '';
        $smsconfig      = $repository->getSMSConfig();
        if (!empty($smsconfig) && $country == '') {
            $url      = $smsconfig[0]['url'];
            $username = $smsconfig[0]['username'];
            $password = $smsconfig[0]['password'];
            $senderid = $smsconfig[0]['senderid'];

            if (isset($data['otp'])) {
                $otp = $data['otp'];
            } else {
                $otp = rand(100000, 999999);
            }
            $phonenumber = substr($phonenumber, -10);
            $content     = str_replace('|OTP|', $otp, $this->translator->trans('le.send.otp.sms'));
            $metadata    = $this->sendSms($url, $phonenumber, $content, $username, $password, $senderid);
            if ($metadata) {
                $otpsend = true;
            }
        }
        if (!$otpsend) {
            $loginsession = $this->get('session');
            $loginsession->set('isLogin', false);
            $account->setMobileverified(1);
            $model->saveEntity($account);
        }
        if ($email != '') {
            $signuprepository = $this->get('le.core.repository.signup');
            $signuprepository->updateCustomerStatus('Active', 'Trial', $email);
        }
        $dataArray                = ['success' => true];
        $dataArray['otp']         = $otp;
        $dataArray['otpsend']     = false;
        $dataArray['mobile']      = $phonenumber;
        $url                      = $this->generateUrl('le_success_action');
        $dataArray['redirecturl'] = $url;

        return $this->sendJsonResponse($dataArray);
    }

    public function resendOTPAction(Request $request)
    {
        $data = $request->request->all();
        /** @var \Mautic\SubscriptionBundle\Model\BillingModel $billingmodel */
        $billingmodel   = $this->getModel('subscription.billinginfo');
        $phonenumber    = $data['phonenumber'];
        $repository     = $this->get('le.core.repository.subscription');
        $otpsend        = false;
        $smsconfig      = $repository->getSMSConfig();
        if (!empty($smsconfig)) {
            $url         = $smsconfig[0]['url'];
            $username    = $smsconfig[0]['username'];
            $password    = $smsconfig[0]['password'];
            $senderid    = $smsconfig[0]['senderid'];
            $otp         = $data['otp'];
            $phonenumber = substr($phonenumber, -10);
            $content     = str_replace('|OTP|', $otp, $this->translator->trans('le.send.otp.sms'));
            $metadata    = $this->sendSms($url, $phonenumber, $content, $username, $password, $senderid);
            if ($metadata) {
                $otpsend = true;
            }
        }
        $dataArray            = ['success' => true];
        $dataArray['otp']     = $otp;
        $dataArray['otpsend'] = $otpsend;
        $dataArray['mobile']  = $phonenumber;

        return $this->sendJsonResponse($dataArray);
    }

    public function OTPVerifiedAction(Request $request)
    {
        $loginsession = $this->get('session');
        $loginsession->set('isLogin', false);
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $model */
        $model         = $this->getModel('subscription.accountinfo');
        $accrepo       = $model->getRepository();
        $accountentity = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0]; //$model->getEntity(1);
        }
        $account->setMobileverified(1);
        $model->saveEntity($account);
        $email = $account->getEmail();
        if ($email != '') {
            $signuprepository = $this->get('le.core.repository.signup');
            $signuprepository->updateCustomerStatus('Active', 'Trial', $email);
        }
        $dataArray                = ['success' => true];
        $url                      = $this->generateUrl('le_contact_index');
        $dataArray['redirecturl'] = $url;

        return $this->sendJsonResponse($dataArray);
    }

    public function validatepaymentAction(Request $request)
    {
        $paymentid                 = $request->request->get('paymentid');
        $subscriptionid            = $request->request->get('subscriptionid');
        $signature                 = $request->request->get('signature');
        $apisecret                 =$this->coreParametersHelper->getParameter('razoparpay_apisecret');
        $expectedSignature         = hash_hmac('sha256', $paymentid.'|'.$subscriptionid, $apisecret);
        $dataArray                 = ['success' => true];
        $parameters                =['provider' => 'razorpay', 'paymentid' => $paymentid, 'subscriptionid' => $subscriptionid];
        if ($expectedSignature === $signature) {
            $parameters['status']=true;
        } else {
            $parameters['status']=false;
        }
        $returnUrl            = $this->generateUrl('le_subscription_status', $parameters);
        $dataArray['redirect']=$returnUrl;

        return $this->sendJsonResponse($dataArray);
    }

    public function purchaseplanAction(Request $request)
    {
        $dataArray        = ['success' => true];
        $plancurrency     = $request->request->get('plancurrency');
        $planamount       = $request->request->get('planamount');
        $planame          = $request->request->get('planname');
        $plankey          = $request->request->get('plankey');
        $plancredits      = $request->request->get('plancredits');
        $beforecredits    = $request->request->get('beforecredits');
        $aftercredits     = $request->request->get('aftercredits');
        $taxamount        = $request->request->get('taxamount');
        $netamount        = $request->request->get('netamount');
        $orderid          =uniqid();
        $session          = $this->get('session');
        $session->set('le.payment.orderid', $orderid);
        $paymentstatus    ='Initiated';
        $provider         ='paypal';
        if ($plancurrency == '₹') {
            $provider = 'razorpay';
        }
        $username               =$this->user->getName();
        $useremail              =$this->user->getEmail();
        $usermobile             =$this->user->getMobile();
        $dataArray['username']  =$username;
        $dataArray['useremail'] =$useremail;
        $dataArray['usermobile']=$usermobile;
        $dataArray['provider']  =$provider;
        if ($provider == 'razorpay') {
            $apikey              =$this->coreParametersHelper->getParameter('razoparpay_apikey');
            $dataArray['apikey'] =$apikey;
            $dataArray['orderid']=$orderid;
        } else {
            $clienthost          =$request->getHost();
            $clientprotocal      =$request->getScheme();
            $payer               = new Payer();
            $payer->setPaymentMethod('paypal');
            $item = new Item();
            $item->setName($planame.' Plan Purchase')
                ->setCurrency('USD')
                ->setQuantity(1)
                ->setSku($plankey) // Similar to `item_number` in Classic API
                ->setPrice($planamount);
            $itemList = new ItemList();
            $itemList->setItems([$item]);
            $details = new Details();
            $details->setShipping(0)
                ->setTax(0)
                ->setSubtotal($planamount);
            $amount = new Amount();
            $amount->setCurrency('USD')
                ->setTotal($planamount)
                ->setDetails($details);
            $transaction = new Transaction();
            $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription($planame.' Plan Purchase')
                ->setInvoiceNumber($orderid);
            $successparameters   =['provider' => 'paypal', 'status' => true];
            $returnUrl           = $this->generateUrl('le_payment_status', $successparameters);
            $returnUrl           =$clientprotocal.'://'.$clienthost.$returnUrl;
            $cancelparameters    =['provider' => 'paypal', 'status' => false];
            $cancelUrl           = $this->generateUrl('le_payment_status', $cancelparameters);
            $cancelUrl           =$clientprotocal.'://'.$clienthost.$cancelUrl;
            $redirectUrls        = new RedirectUrls();
            $redirectUrls->setReturnUrl($returnUrl)
                ->setCancelUrl($cancelUrl);
            $payment = new Payment();
            $payment->setIntent('sale')
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions([$transaction]);
            $request = clone $payment;
            try {
                $paymenthelper            =$this->get('le.helper.payment');
                $payment                  =$payment->create($paymenthelper->getPayPalApiContext());
                $approvalUrl              = $payment->getApprovalLink();
                $dataArray['approvalurl'] =$approvalUrl;
            } catch (Exception $ex) {
                $dataArray['success']  =false;
                $dataArray['errormsg'] ='Payment Error:'.$ex->getMessage();
            }
        }
        if ($dataArray['success']) {
            $userhelper   =$this->get('mautic.helper.user');
            $user         =$userhelper->getUser();
            $createdby    ='';
            $createdbyuser='';
            $createdon    ='';
            if ($user != null) {
                $createdby    =$user->getId();
                $createdbyuser=$user->getName();
            }
            $scprepository      =$this->get('le.core.repository.subscription');
            $validitytill       =$scprepository->getPlanValidity($plankey);
            $paymentrepository  =$this->get('le.subscription.repository.payment');
            $paymenthistory     =new PaymentHistory();
            $paymenthistory->setOrderID($orderid);
            $paymenthistory->setPaymentStatus($paymentstatus);
            $paymenthistory->setProvider($provider);
            $paymenthistory->setCurrency($plancurrency);
            $paymenthistory->setAmount($planamount);
            $paymenthistory->setBeforeCredits($beforecredits);
            $paymenthistory->setAddedCredits($plancredits);
            $paymenthistory->setAfterCredits($aftercredits);
            $paymenthistory->setValidityTill($validitytill);
            $paymenthistory->setPlanName($plankey);
            $paymenthistory->setPlanLabel($planame);
            $paymenthistory->setcreatedBy($createdby);
            $paymenthistory->setcreatedByUser($createdbyuser);
            $paymenthistory->setcreatedOn(new \DateTime());
            $paymenthistory->setNetamount($netamount);
            $paymenthistory->setTaxamount($taxamount);
            $paymentrepository->saveEntity($paymenthistory);
        }

        return $this->sendJsonResponse($dataArray);
    }

    public function getAvailableCountAction()
    {
        $dataArray                  = ['success' => true];
        $availablecount             = $this->get('mautic.helper.licenseinfo')->getAvailableEmailCount();
        $dataArray['availablecount']=$availablecount;

        return $this->sendJsonResponse($dataArray);
    }

    public function capturepaymentAction(Request $request)
    {
        $dataArray['success']  =true;
        $paymentid             = $request->request->get('paymentid');
        $captureamount         = $request->request->get('captureamount');
        $apikey                =$this->coreParametersHelper->getParameter('razoparpay_apikey');
        $apisecret             =$this->coreParametersHelper->getParameter('razoparpay_apisecret');
        $api                   =new Api($apikey, $apisecret);
        try {
            $payment                   = $api->payment->fetch($paymentid)->capture(['amount'=>$captureamount]);
            $paymentstatus             =$payment->status;
            $error_code                =$payment->error_code;
            $error_desc                =$payment->error_description;
            $notes                     =$payment->notes;
            $orderid                   =$notes->merchant_order_id;
            $plankey                   =$notes->plankey;
            $parameters                =['provider' => 'razorpay', 'paymentid' => $paymentid, 'orderid' => $orderid];
            if ($error_code == null) {
                $parameters['status']=true;
                $repository          =$this->get('le.core.repository.subscription');
                $repository->updateEmailCredits($plankey);
            } else {
                $parameters['status']=false;
            }
            $returnUrl            = $this->generateUrl('le_payment_status', $parameters);
            $dataArray['redirect']=$returnUrl;
        } catch (Error $ex) {
            $dataArray['success']  =false;
            $dataArray['errormsg'] ='Payment Error:'.$ex->getMessage();
        }

        return $this->sendJsonResponse($dataArray);
    }

    public function validityinfoAction(Request $request)
    {
        $emailModel            =$this->getModel('email');
        $statrepo              =$emailModel->getStatRepository();
        $licenseinfo           =$this->get('mautic.helper.licenseinfo')->getLicenseEntity();
        $contactUsage          =$licenseinfo->getActualRecordCount();
        $accountStatus         =$licenseinfo->getAppStatus();
        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator          = $this->get('mautic.configurator');
        $paramater             = $configurator->getParameters();
        $maileruser            = $paramater['mailer_user'];
        $emailpassword         = $paramater['mailer_password'];
        if (isset($paramater['mailer_amazon_region'])) {
            $region                = $paramater['mailer_amazon_region'];
        } else {
            $region='';
        }
        //$region                = $paramater['mailer_amazon_region'];
        //  $fromadress            = $paramater['mailer_from_email'];
        $transport             = $paramater['mailer_transport'];
        $date                  = new \DateTime();
        $date->modify('-1 day');
        $last24hrsDate         =  $date->format('Y-m-d H:i:s');
        $mailsent24hrs         = $statrepo->getSentCountsByDate($last24hrsDate);
        $dataArray['success']  =true;

        if ($transport == 'le.transport.amazon' && !empty($maileruser) && !empty($emailpassword)) {
            $stats                        = $this->get('mautic.validator.email')->getSendingStatistics($maileruser, $emailpassword, $region);
            if (empty($stats['Max24HourSend'])) {
                $stats['Max24HourSend'] = 'NA';
            }
            if (empty($stats['SentLast24Hours'])) {
                $stats['SentLast24Hours'] = 'NA';
            }
            $dataArray['accountstatus']   = $accountStatus;
            $dataArray['credits']         = $stats['Max24HourSend'];
            $dataArray['validity']        = $contactUsage;
            $dataArray['daysavailable']   = $stats['SentLast24Hours'];
        }
        if ($transport == 'le.transport.elasticemail' && !empty($maileruser) && !empty($emailpassword)) {
            $accountstatus              = $this->get('mautic.helper.licenseinfo')->getElasticAccountDetails($emailpassword, 'load');
            $dataArray['credits']       = $contactUsage;
            $dataArray['accountstatus'] = isset($accountstatus['statusformatted']) ? $accountstatus['statusformatted'] : '';
            $dataArray['daysavailable'] = $mailsent24hrs;
        }
        if ($transport == 'le.transport.sendgrid_api' && !empty($maileruser) && !empty($emailpassword)) {
            $accountstatus              = $this->get('mautic.helper.licenseinfo')->getSendGridStatus($maileruser);
            $dataArray['credits']       = $contactUsage;
            $dataArray['accountstatus'] = $accountstatus;
            $dataArray['daysavailable'] = $mailsent24hrs;
        }

        $dataArray['transport']      = $transport;

        return $this->sendJsonResponse($dataArray);
    }

    public function licenseusageinfoAction(Request $request)
    {
        /*$paymentrepository            = $this->get('le.subscription.repository.payment');
        $licenseinfo                  = $this->get('mautic.helper.licenseinfo')->getLicenseEntity();
        $licenseRemDays               = $this->get('mautic.helper.licenseinfo')->getLicenseRemainingDays();
        $isleplan2                    =false;
        $isClosed                     = $this->factory->get('session')->get('isalert_needed');
        $lastpayment                  = $paymentrepository->getLastPayment();

        if ($lastpayment != null && $lastpayment->getPlanName() == 'leplan2') {
            $isleplan2 =true;
        }
        $brandname=$this->coreParametersHelper->getParameter('product_brand_name');
        if ($licenseinfo->getEmailProvider() == $brandname && !$isleplan2) {
            $welcomemsg                   = $this->get('translator')->trans('le.account.signup.message');
            $action                       = $this->generateUrl('le_config_action', ['objectAction' => 'edit']);
            $welcomemsg                   = str_replace('|URL|', $action, $welcomemsg);
            $actualcount                  = $licenseinfo->getActualEmailCount();
            $totalcount                   = $licenseinfo->getTotalEmailCount();
            $welcomemsg                   = str_replace('|ACTUAL|', $actualcount, $welcomemsg);
            $welcomemsg                   = str_replace('|TOTAL|', $totalcount, $welcomemsg);
            $dataArray['info']            = $welcomemsg;
            $dataArray['needClosebutton'] = true;
            $dataArray['success']         = false;
            $dataArray['isalertneeded']   = $isClosed;
        }

        $isClosed                     = $this->factory->get('session')->get('isalert_needed');
        $dataArray['success']         = true;
        $dataArray['info']            = $this->getLicenseNotifyMessage();
        $dataArray['isalertneeded']   = $isClosed;
        $dataArray['needClosebutton'] = $isClosed;

        if ($lastpayment != null && $lastpayment->getPaymentStatus() != 'Paid') {
            $configurl                    = $this->generateUrl('le_accountinfo_action', ['objectAction' => 'cardinfo']);
            $dataArray['info']            = $this->translator->trans('le.msg.payment.failure.appheader', ['%URL%'=>$configurl]);
            $dataArray['needClosebutton'] = false;
            $dataArray['success']         = true;
            $dataArray['isalertneeded']   = $isClosed;
        }
        $infomeesage=$dataArray['info'];
        $breakstring=substr($infomeesage, -4);
        if ($breakstring == '<br>') {
            $infomeesage=substr($infomeesage, 0, strlen($infomeesage) - 4);
            $infomeesage .= $this->get('translator')->trans('le.license.dismiss.btn');
            $dataArray['info']=$infomeesage;
        }
        if ($licenseRemDays >= 0 && $licenseRemDays < 6) {
            $configurl                    = $this->generateUrl('le_pricing_index');
            $dataArray['info']            = $this->translator->trans('le.msg.license.before.expired.appheader', ['%URL%'=>$configurl, '%days%'=>$licenseRemDays]);
            $dataArray['needClosebutton'] = false;
            $dataArray['success']         = true;
            $dataArray['isalertneeded']   = $isClosed;
        } elseif ($licenseRemDays < 0) {
            $configurl                    = $this->generateUrl('le_pricing_index');
            $dataArray['info']            = $this->translator->trans('le.msg.license.expired.appheader', ['%URL%'=>$configurl]);
            $dataArray['needClosebutton'] = false;
            $dataArray['success']         = true;
            $dataArray['isalertneeded']   = $isClosed;
        }*/
        $smHelper                     = $this->get('le.helper.statemachine');
        $paymentrepository            = $this->get('le.subscription.repository.payment');
        $lastpayment                  = $paymentrepository->getLastPayment();
        $licenseRemDays               = $this->get('mautic.helper.licenseinfo')->getLicenseRemainingDays();
        if ($lastpayment == null && $licenseRemDays < 0) {
            if (!$smHelper->isStateAlive('Trial_Inactive_Expired')) {
                $smHelper->makeStateInActive(['Trial_Active']);
                $smHelper->newStateEntry('Trial_Inactive_Expired');
                $smHelper->addStateWithLead();
            }
        }
        $dataArray['success']         = false;
        $infoMessage                  ='';
        if ($state=$smHelper->isStateAlive('Customer_Inactive_Exit_Cancel')) {
            $infoMessage=$smHelper->getAccountInActiveAlert($state);
        } elseif ($smHelper->isStateAlive('Customer_Active_Card_Expiring_Soon')) {
            $cardinfoLink=$this->generateUrl('le_accountinfo_action', ['objectAction' => 'cardinfo']);
            $infoMessage = $this->translator->trans('le.pricing.stripe.card.expiry.header', ['|URL|'=>$cardinfoLink, '%card_expiry%'=>$smHelper->getStripeCardExpiryInfo()]);
        }
        if ($infoMessage != '') {
            $isClosed                     = $this->factory->get('session')->get('isalert_needed');
            $dataArray['info']            = $infoMessage;
            $dataArray['success']         = true;
            $dataArray['isalertneeded']   = $isClosed;
            $dataArray['needClosebutton'] = $isClosed;
        }

        return $this->sendJsonResponse($dataArray);
    }

    public function notificationclosedAction(Request $request)
    {
        $isClosed                   = $request->request->get('isalert_needed');
        $session                    =$this->factory->get('session');
        $dataArray['success']       = true;
        $dataArray['isalertneeded'] =$session->set('isalert_needed', $isClosed);

        return $this->sendJsonResponse($dataArray);
    }

    public function getLicenseNotifyMessage()
    {
        $licenseRemDays           = $this->get('mautic.helper.licenseinfo')->getLicenseRemainingDays();
        $licenseRemDate           = $this->get('mautic.helper.licenseinfo')->getLicenseEndDate();
        $emailUsageCount          = $this->get('mautic.helper.licenseinfo')->getTotalEmailUsage();
        $bounceUsageCount         = $this->get('mautic.helper.licenseinfo')->getEmailBounceUsageCount();
        $totalRecordUsage         = $this->get('mautic.helper.licenseinfo')->getTotalRecordUsage();
        $emailValidityEndDate     = $this->get('mautic.helper.licenseinfo')->getEmailValidityEndDate();
        $emailCountExpired        = $this->get('mautic.helper.licenseinfo')->emailCountExpired();
        $emailValidityDays        = $this->get('mautic.helper.licenseinfo')->getEmailValidityDays();
        $accountStatus            = $this->get('mautic.helper.licenseinfo')->getAccountStatus();
        $mailertransport          = $this->get('mautic.helper.licenseinfo')->getEmailProvider();
        $availablerecordcount     = $this->get('mautic.helper.licenseinfo')->getAvailableRecordCount();
        $availableemailcount      =  $this->get('mautic.helper.licenseinfo')->getAvailableEmailCount();
        $actualrecordcount        =  $this->get('mautic.helper.licenseinfo')->getActualRecordCount();
        $totalrecordcount         =  $this->get('mautic.helper.licenseinfo')->getTotalRecordCount();
        $smsCountExpired          =  $this->get('mautic.helper.licenseinfo')->smsCountExpired();
        $totalrecordcount         = $totalrecordcount != 'UL' ? number_format($totalrecordcount) : $totalrecordcount;
        $actualrecordcount        = number_format($actualrecordcount);
        $emailUssage              = false;
        $bouceUsage               = false;
        $emailsValidity           = false;
        $recordUsage              = false;
        $smsUssage                = false;
        $buyCreditRoute           =$this->generateUrl('le_plan_index');
        $pricingplanRoute         =$this->generateUrl('le_pricing_index');

        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator          = $this->get('mautic.configurator');
        $paramater             = $configurator->getParameters();
        $transport             = $paramater['mailer_transport'];

        $accountsuspendmsg   ='';
        $notifymessage       ='';
        $usageMsg            ='';
        $maxEmailUsage       = 80;
        $maxBounceUsage      =3;
        $maxEmailValidity    =7;
        $maxRecordUsage      =80;
        $buyNowButon         = 'Buy Now';
        $paymentrepository   =$this->get('le.subscription.repository.payment');
        $lastpayment         =$paymentrepository->getLastPayment();
        $lastpaymentplanname = '';
        if ($lastpayment != null) {
            $lastpaymentplanname      = $paymentrepository->getLastPayment()->getPlanName();
        }
        if ($accountStatus) {
            $accountsuspendmsg = $this->translator->trans('leadsengage.account.suspended');
        }
        if ($mailertransport == $this->translator->trans('le.transport.elasticemail') || $mailertransport == $this->translator->trans('le.transport.sendgrid_api')) {
            $accountusagelink  = $this->translator->trans('le.emailusage.link');
            $accountusagelink  = str_replace('|URL|', $this->generateUrl('le_email_usage'), $accountusagelink);
            $accountsuspendmsg = str_replace('%ATAG%', $accountusagelink, $accountsuspendmsg);
        } else {
            $accountsuspendmsg = str_replace('%ATAG%', '', $accountsuspendmsg);
        }
        /* if (!empty($licenseRemDays)) {
             if ($licenseRemDays == 1) {
                 $notifymessage = $this->translator->trans('leadsengage.msg.license.expired.today');
             } elseif ($licenseRemDays == 2) {
                 $notifymessage = $this->translator->trans('leadsengage.msg.license.expired.tommorow');
             } elseif ($licenseRemDays < 7) {
                 $notifymessage = $this->translator->trans('leadsengage.msg.license.expired', ['%licenseRemDate%' => $licenseRemDate]);
             }
             //  $notifymessage .= $this->translator->trans('le.record.usage.count', ['%contact_usage%' => $availablerecordcount, '%email_usage%' => $availableemailcount]);
             //$notifymessage .= $this->translator->trans('le.upgrade.button', ['%upgrade%' => 'Subscribe', '%url%' => $pricingplanRoute]);
         } */
        if (isset($emailUsageCount) && $emailUsageCount > $maxEmailUsage) {
            $emailUssage=true;
        }
        if (isset($bounceUsageCount) && $bounceUsageCount > $maxBounceUsage) {
            // $bouceUsage=true;
        }
        if (isset($emailValidityDays) && $emailValidityDays !== 'UL' && $emailValidityDays < $maxEmailValidity) {
            $emailsValidity=true;
        }
        if (isset($totalRecordUsage) && $totalRecordUsage > $maxRecordUsage) {
            $recordUsage=true;
        }
        if (!$smsCountExpired) {
            $smsUssage=true;
        }
        if ($emailUssage) {
            if ($emailCountExpired == 0) {
                if ($transport == 'le.transport.vialeadsengage') {
                    // if ($lastpaymentplanname != 'leplan2') {
                    $usageMsg=$this->translator->trans('le.emailusage.count.expired.freecredit');
                // }
                } else {
                    // $usageMsg=$this->translator->trans('le.emailusage.count.expired');
                }
            } else {
                // $usageMsg=$this->translator->trans('le.emailusage.count.exceeds', ['%maxEmailUsage%' => $maxEmailUsage]);
            }
        }
        /* if ($emailsValidity && $lastpayment == null) {
             if ($emailValidityDays < 0) {
                 $emailMsg=$this->translator->trans('le.emailvalidity.count.expired');
             } elseif ($emailValidityDays == 0) {
                 $emailMsg=$this->translator->trans('le.emailvalidity.count.expired.today');
             } elseif ($emailValidityDays == 1) {
                 $emailMsg=$this->translator->trans('le.emailvalidity.count.expired.tommorow');
             } else {
                 $emailMsg=$this->translator->trans('le.emailvalidity.count.exceeds', ['%emailValidityEndDate%' => $emailValidityDays]);
             }
             $emailMsg .= $this->translator->trans('le.upgrade.button', ['%upgrade%' => 'Subscribe', '%url%' => $pricingplanRoute]);
             $notifymessage .= $emailMsg;
             //            if ($usageMsg != '') {
//                $usageMsg .= ' and ';
//            }
//            $usageMsg .= $emailMsg;
//
//            if ($emailCountExpired == 0 && $emailValidity < 0) {
//                $usageMsg = $this->translator->trans('le.emailusage.count.expired');
//            } elseif ($emailCountExpired == 0 && $emailsValidity) {
//                $usageMsg =$this->translator->trans('le.emailusage.count.expired');
//            } elseif ($emailValidity < 0 && $emailUssage) {
//                $usageMsg=$this->translator->trans('le.emailvalidity.count.expired');
//            }
         } */

        if ($smsUssage) {
            if ($lastpaymentplanname != 'leplan2') {
                //$usageMsg .= $this->translator->trans('le.sms.count.exceeds');
            }
        }

        if ($emailUssage || $emailsValidity) {
            // $usageMsg .= $this->translator->trans('le.buyNow.button', ['%buyNow%' => $buyNowButon, '%url%' => $buyCreditRoute]);
        }
        $contactNotification = $availablerecordcount > 0 ? 'le.record.count.about.expired' : 'le.record.count.expired';
        if ($recordUsage) {
            if ($contactNotification != 'le.record.count.expired') {
                $usageMsg .= $this->translator->trans($contactNotification, ['%record_count%' => $actualrecordcount]);
            } else {
                $usageMsg .= $this->translator->trans($contactNotification, ['%TOTAL%' => $totalrecordcount, '%ACTUAL%' => $actualrecordcount]);
            }
            if ($lastpayment == null) {
                if ($contactNotification != 'le.record.count.expired') {
                    $usageMsg .= $this->translator->trans('le.upgrade.button', ['%upgrade%' => 'Upgrade Now!', '%url%' => $pricingplanRoute]);
                } else {
                    $usageMsg .= $this->translator->trans('le.upgrade.button.total', ['%upgrade%' => 'Upgrade Now', '%url%' => $pricingplanRoute]);
                }
            } else {
                $usageMsg .= $this->translator->trans('le.plan.renewal.message');
            }
        }

        if ($bouceUsage) {
            $usageMsg .= $this->translator->trans('le.bounce.count.exceeds', ['%bounceUsageCount%' => $bounceUsageCount]);
        }
        $notificationPriority= false;
        $leUssageMsg         ='';
        if (($recordUsage && $emailUssage && $smsUssage) || ($recordUsage && $emailUssage) || ($recordUsage && $smsUssage)) {
            if ($contactNotification != 'le.record.count.expired') {
                $leUssageMsg = $this->translator->trans($contactNotification, ['%record_count%' => $actualrecordcount]);
            } else {
                $leUssageMsg = $this->translator->trans($contactNotification, ['%TOTAL%' => $totalrecordcount, '%ACTUAL%' => $actualrecordcount]);
            }
            $notificationPriority= true;
        } elseif ($emailUssage && $smsUssage) {
            if ($lastpaymentplanname != 'leplan2') {
                $leUssageMsg = $this->translator->trans('le.emailusage.count.expired.freecredit');
            }
            $notificationPriority= true;
        }

        /*if(!empty($leUssageMsg)){
            if ($lastpayment == null) {
                $leUssageMsg .= $this->translator->trans('le.upgrade.button', ['%upgrade%' => 'Upgrade', '%url%' => $pricingplanRoute]);
            } else {
                $leUssageMsg .= $this->translator->trans('le.plan.renewal.message');
            }
        }*/
        $notificationMsg = $notificationPriority ? $leUssageMsg : $usageMsg;

        if ($usageMsg != '') {
            $notifymessage .= ' '.$notificationMsg;
        }
        if ($accountsuspendmsg != '') {
            return $accountsuspendmsg;
        } else {
            return $notifymessage;
        }
    }

    public function sendSms($url, $number, $content, $username, $password, $senderID)
    {
        try {
            $url     = $url;
            $content = urlencode($content);
            $sendurl = $url;
            $baseurl = $sendurl.'?method=sms&api_key='.$username.'&sender='.$senderID;
            $sendurl =$baseurl.'&to='.$number.'&message='.$content;
            $handle  = curl_init($sendurl);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($handle);
            $response = json_decode($response);
            $status   =$response->{'status'};
            $message  =$response->{'message'};
            if ($status == 'OK') {
                return true;
            } else {
                $this->logger->addWarning(
                    $message
                );

                return false;
            }
        } catch (NumberParseException $e) {
            $this->logger->addWarning(
                $e->getMessage(),
                ['exception' => $e]
            );

            return $e->getMessage();
        }
    }

    public function cancelpaymentAction(Request $request)
    {
        $dataArray['success']  =true;
        $orderid               = $request->request->get('orderid');
        if ($orderid != '') {
            $paymentrepository  =$this->get('le.subscription.repository.payment');
            $paymentrepository->updatePaymentStatus($orderid, '', 'Cancelled');
        }

        return $this->sendJsonResponse($dataArray);
    }

    public function updatestripecardAction(Request $request)
    {
        $dataArray['success']           =true;
        $letoken                        = $request->request->get('letoken');
        $stripetoken                    = $request->request->get('stripetoken');
        $amount                         = $request->request->get('planamount');
        $plancurrency                   = $request->request->get('plancurrency');
        $planname                       = $request->request->get('planname');
        $plancredits                    = $request->request->get('plancredits');
        $isCardUpdateAlone              = $request->request->get('isCardUpdateAlone');
        $planvalidity                   = $request->request->get('planvalidity');
        $contactcredits                 = $request->request->get('contactcredits');
        $accountdata                    = $request->request->get('accountdata');
        if ($isCardUpdateAlone == 'false') {
            $this->updateAccountData($accountdata);
        }
        $paymentCapture=true;
        if ($amount == 0) {
            $amount        = 1;
            $paymentCapture=false;
        }
        if ($isCardUpdateAlone == 'true') {
            $paymentCapture=false;
        }
        // file_put_contents("/var/www/mauto/app/logs/stripe.txt",$isCardUpdateAlone,FILE_APPEND);
        $apikey=$this->coreParametersHelper->getParameter('stripe_api_key');
        \Stripe\Stripe::setApiKey($apikey);
        $errormsg='';
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $model */
        $model         = $this->getModel('subscription.accountinfo');
        $accrepo       = $model->getRepository();
        $accountentity = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0];
        } else {
            $account = new Account();
        }
        $accountname  =$account->getAccountname();
        $accountemail =$account->getEmail();
        $accountdomain=$account->getDomainname();
        if ($accountname == '' || $accountemail == '') {
            $errormsg='Please update your account information properly to proceed further.';
        }
        if (empty($errormsg)) {
            $stripecardrepo=$this->get('le.subscription.repository.stripecard');
            $stripecards   = $stripecardrepo->findAll();
            if (sizeof($stripecards) > 0) {
                $stripecard = $stripecards[0];
            } else {
                $stripecard = new StripeCard();
            }
            try {
                if ($stripecard->getCustomerID() == '') {
                    //To create new customer with card
                    $customer=\Stripe\Customer::create([
                        'description' => "New Customer ($accountname) and their register domain is ($accountdomain)",
                        'source'      => $stripetoken, // obtained with Stripe.js
                        'email'       => $accountemail,
                    ]);
//                    , [
//                        'idempotency_key' => $letoken,
//                    ]
                } else {
                    //To update new card with existing customer
                    $customer         = \Stripe\Customer::retrieve($stripecard->getCustomerID());
                    $customer->source = $stripetoken; // obtained with Stripe.js
                    $customer         =$customer->save();
                }
                if (isset($customer)) {
                    $customerid =$customer->id;
                    $description='charge for anyfunnels product purchase';
                    if ($isCardUpdateAlone == 'true') {
                        $description='charge for card verification';
                    }
                    $charges=\Stripe\Charge::create([
                        'amount'   => $amount * 100, //100 cents = 1 dollar
                        'currency' => 'usd',
                        //"source" => $token, // obtained with Stripe.js
                        'customer'            => $customerid,
                        'description'         => $description,
                        'capture'             => $paymentCapture,
                        'statement_descriptor'=> 'leadsengage purchase',
                    ], [
                        'idempotency_key' => $letoken,
                    ]);
                }
            } catch (\Stripe\Error\Card $e) {
                // Since it's a decline, \Stripe\Error\Card will be caught
                $body = $e->getJsonBody();
                $err  = $body['error'];

                //  print('Status is:' . $e->getHttpStatus() . "\n");
                // print('Type is:' . $err['type'] . "\n");
                //print('Code is:' . $err['code'] . "\n");
                // param is '' in this case
                //  print('Param is:' . $err['param'] . "\n");
                // print('Message is:' . $err['message'] . "\n");
                $errormsg='Card Error:'.$err['message'];
            } catch (\Stripe\Error\RateLimit $e) {
                $errormsg= 'Too many requests made to the API too quickly';
                // Too many requests made to the API too quickly
            } catch (\Stripe\Error\InvalidRequest $e) {
                $errormsg= "Invalid parameters were supplied to Stripe's API->".$e->getMessage();
                // Invalid parameters were supplied to Stripe's API
            } catch (\Stripe\Error\Authentication $e) {
                $errormsg= "Authentication with Stripe's API failed";
                // Authentication with Stripe's API failed
                // (maybe you changed API keys recently)
            } catch (\Stripe\Error\ApiConnection $e) {
                $errormsg= 'Network communication with Stripe failed';
                // Network communication with Stripe failed
            } catch (\Stripe\Error\Base $e) {
                $errormsg= 'Display a very generic error to the user, and maybe send->'.$e->getMessage();
                // Display a very generic error to the user, and maybe send
                // yourself an email
            } catch (Exception $e) {
                $errormsg= 'General Error:'.$e->getMessage();
                // Something else happened, completely unrelated to Stripe
            }
            if (isset($customer) && isset($charges)) {
                $userhelper   =$this->get('mautic.helper.user');
                $user         =$userhelper->getUser();
                $updatedby    ='';
                $updatedbyuser='';
                if ($user != null) {
                    $updatedby    =$user->getId();
                    $updatedbyuser=$user->getName();
                }
                $customerid     =$customer->id;
                $data           =$customer->sources->data;
                $cardlast4digit =$data[0]->last4;
                $cardfingerprint=$data[0]->fingerprint;
                $cardbrand      =$data[0]->brand;
                $expmonth       = $data[0]->exp_month;
                $expyear        =  $data[0]->exp_year;
                $stripecard->setCustomerID($customerid);
                $stripecard->setlast4digit($cardlast4digit);
                $stripecard->setfingerprint($cardfingerprint);
                $stripecard->setbrand($cardbrand);
                $stripecard->setupdatedBy($updatedby);
                $stripecard->setupdatedByUser($updatedbyuser);
                $stripecard->setupdatedOn(new \DateTime());
                $stripecard->setExpMonth($expmonth);
                $stripecard->setExpYear($expyear);
                $stripecardrepo->saveEntity($stripecard);
            } else {
                if (empty($errormsg)) {
                    $errormsg='Some Technical Error Occurs';
                }
            }
            $statusurl='';
            if (isset($charges) && $isCardUpdateAlone == 'false') {
                $orderid        =uniqid();
                $chargeid       =$charges->id;
                $status         =$charges->status;
                $failure_code   =$charges->failure_code;
                $failure_message=$charges->failure_message;
                if ($status == 'succeeded') {
                    $userhelper   =$this->get('mautic.helper.user');
                    $user         =$userhelper->getUser();
                    $createdby    ='';
                    $createdbyuser='';
                    if ($user != null) {
                        $createdby    =$user->getId();
                        $createdbyuser=$user->getName();
                    }
                    /** @var \Mautic\CoreBundle\Helper\LicenseInfoHelper $licensehelper */
                    $licensehelper      = $this->get('mautic.helper.licenseinfo');
                    //  $currentTotalEmails = $licensehelper->getTotalEmailCount();
                    $paymentrepository  =$this->get('le.subscription.repository.payment');
                    //  $lastpayment        = $paymentrepository->getLastPayment();
                    $todaydate          = date('Y-m-d');
                    $emailplancredits   = $this->translator->trans('le.pricing.plan.email.credits.'.$planname);
                    $contactcredites    = $this->translator->trans('le.pricing.plan.contact.credits.'.$planname);
                    // if ($lastpayment != null) {
                    // $validityend      = $lastpayment->getValidityTill();
                    // $preplanName      = $lastpayment->getPlanName();
                    // $preplancredits   = $licensehelper->getEmailCreditsByPlan($preplanName);
                    //  $emailplancredits = $emailplancredits - $preplancredits;
                    // $emailplancredits = $currentTotalEmails + $emailplancredits;
                    /*if ($todaydate != $validityend) {
                        $todaydate    = $validityend;
                        $validitytill = date('Y-m-d', strtotime('-1 day +'.$planvalidity.' months', strtotime($validityend)));
                    }*/
                    //$validitytill = $validityend;
                    // } else {
                    //$planname     = '90 Days Success Offer';
                    $validitytill  = date('Y-m-d', strtotime('-1 day +'.$planvalidity.' months'));
                    $smHelper      = $this->get('le.helper.statemachine');
                    $smHelper->makeStateInActive(['Trial_Inactive_Expired', 'Trial_Active']);
                    if (!$smHelper->isStateAlive('Customer_Sending_Domain_Not_Configured')) {
                        $smHelper->newStateEntry('Customer_Sending_Domain_Not_Configured');
                        $smHelper->createElasticSubAccountandAssign();
                    }
                    $smHelper->addStateWithLead();
                    // }
                    if ($amount == 1) {
                        $amount = 0;
                    }
                    $payment            =$paymentrepository->captureStripePayment($orderid, $chargeid, $amount, $amount, $plancredits, $plancredits, $validitytill, $planname, $createdby, $createdbyuser, 'Paid');
                    $subsrepository     =$this->get('le.core.repository.subscription');
                    $subsrepository->updateContactCredits($contactcredites, $validitytill, $todaydate, false, $emailplancredits);
                    $statusurl            = $this->generateUrl('le_payment_status', ['id'=> $orderid]);
                //$signuprepository     =$this->get('le.core.repository.signup');
                    //$dbname               = $this->coreParametersHelper->getParameter('db_name');
                   // $appid                = str_replace('leadsengage_apps', '', $dbname);
                   // $signuplead           = $signuprepository->getSignupLeadinfo($appid);
//                    if (!empty($signuplead) && $lastpayment == null) {
//                        $signupleadid = $signuplead[0]['id'];
//                        $signuprepository->updateLeadwithTag($this->translator->trans('le.payment.done.tag.name'), $signupleadid);
//                        $campaignid   = $this->coreParametersHelper->getParameter('campaign_id');
//                        $sourceId     = $this->coreParametersHelper->getParameter('source_event_id');
//                        $goalId       = $this->coreParametersHelper->getParameter('goal_event_id');
//                        if ($campaignid != null && $goalId != null) {
//                            $signuprepository->unScheduleAllLeadsforCampaign($campaignid);
//                            $signuprepository->addLeadinCampaignLog($campaignid, $signupleadid, $goalId, 1);
//                        }
//                    }
                } else {
                    $errormsg=$failure_message;
                }
            } elseif (isset($charges) && $isCardUpdateAlone == 'true') {
                $smHelper      = $this->get('le.helper.statemachine');
                if ($smHelper->isStateAlive('Customer_Active_Card_Expiring_Soon')) {
                    if (!$smHelper->isStripeCardWillExpire()) {
                        $smHelper->makeStateInActive(['Customer_Active_Card_Expiring_Soon']);
                    }
                }
                if ($smHelper->isStateAlive('Customer_Inactive_Payment_Issue')) {
                    $smHelper->makeStateInActive(['Customer_Inactive_Payment_Issue']);
                    if (!$smHelper->isAnyInActiveStateAlive()) {
                        $smHelper->newStateEntry('Customer_Active', '');
                    }
                    $smHelper->addStateWithLead();
                }
            }
        }
        if (!empty($errormsg)) {
            $dataArray['success']   =false;
            $dataArray['errormsg']  =$errormsg;
        } elseif ($statusurl != '') {
            $billingmodel  = $this->getModel('subscription.billinginfo');
            $billingrepo   = $billingmodel->getRepository();
            $billingentity = $billingrepo->findAll();
            if (sizeof($billingentity) > 0) {
                $billing = $billingentity[0]; //$model->getEntity(1);
            } else {
                $billing = new Billing();
            }
            if ($billing->getAccountingemail() != '') {
                $mailer       = $this->container->get('le.transport.elasticemail.transactions');
                $paymenthelper=$this->get('le.helper.payment');
                $paymenthelper->sendPaymentNotification($payment, $billing, $mailer);
            }
            $dataArray['statusurl']  =$statusurl;
        }

        return $this->sendJsonResponse($dataArray);
    }

    public function cancelsubscriptionAction(Request $request)
    {
        $cancelreason              = $request->request->get('cancelreason');
        $cancelfeedback            = $request->request->get('cancelfeedback');
        $dataArray['success']      = true;
        $redirectUrl               = $this->generateUrl('le_accountinfo_action', ['objectAction' => 'cancel']);
        $dataArray['redirecturl']  = $redirectUrl;
        //$dataArray['error']    = true;
        // $curentDate            =date('Y-m-d');
        //$this->get('mautic.helper.licenseinfo')->intCancelDate($curentDate);
        //$this->get('mautic.helper.licenseinfo')->intAppStatus('Cancelled');
        $smHelper      = $this->get('le.helper.statemachine');
        if (!$smHelper->isStateAlive('Customer_Inactive_Exit_Cancel')) {
            //$smHelper->makeStateInActive(['Customer_Active']);
            $smHelper->newStateEntry('Customer_Inactive_Exit_Cancel', $cancelreason);
            $smHelper->addStateWithLead();
        }
        //  $mailer = $this->container->get('le.transport.elasticemail.transactions');
        // $this->sendCancelSubscriptionEmail($mailer);
        //  $this->cancelsubsEmailtoUser($mailer);

        return $this->sendJsonResponse($dataArray);
    }

    public function sendCancelSubscriptionEmail($mailer)
    {
        $mailer->start();
        $message    = \Swift_Message::newInstance();
        $message->setTo(['support@anyfunnels.com']);
        $message->setFrom(['notifications@anyfunnels.io' => 'AnyFunnels']);
        $message->setSubject($this->translator->trans('leadsengage.accountinfo.cancel.sub.description'));
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $model */
        $model         = $this->getModel('subscription.accountinfo');
        $accrepo       = $model->getRepository();
        $accountentity = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0];
        } else {
            $account = new Account();
        }
        $name      =$this->user->getFirstName();
        $useremail =$account->getEmail();
        $domain    =$account->getDomainname();

        $text = "<!DOCTYPE html>
<html>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>

	<head>
		<title></title>
	</head>
	<body>
		<div>
			Hi,
<br>
<br>Below mentioned customer requested for account cancellation.
<br>
<br><strong>Domain Name-</strong> $domain.anyfunnels.com
<br><strong>Email-</strong> $useremail
<br>
<br>Please take the necessary action.
		</div>
		
	</body>
</html>";
        $message->setBody($text, 'text/html');
        $mailer->send($message);
    }

    public function cancelsubsEmailtoUser($mailer)
    {
        $mailer->start();
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $model */
        $model               = $this->getModel('subscription.accountinfo');
        $usermodel           = $this->getModel('user.user');
        $accrepo             = $model->getRepository();
        $accountentity       = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0];
        } else {
            $account = new Account();
        }
        $useremail  = $account->getEmail();
        $domain     = $account->getDomainname();
        $message    = \Swift_Message::newInstance();
        $currentuser=$usermodel->getCurrentUserEntity();
        $name       = $currentuser->getName();
        $email      =$currentuser->getEmail();
        $message->setTo([$useremail => $name]);
        $message->setFrom(['notifications@anyfunnels.io' => 'AnyFunnels']);
        $message->setReplyTo(['support@anyfunnels.com' => 'AnyFunnels']);
        $message->setSubject($this->translator->trans('leadsengage.accountinfo.cancel.sub.description'));
        $text = "<!DOCTYPE html>
        <html>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>

	<head>
		<title></title>
	</head>
	<body>
		<div>
        Hey, $name!
        <br>
        <br> We received your request for account cancellation.
        <br>
        <br><strong>Domain Name-</strong> $domain.anyfunnels.com
        <br><strong>Associated Email-</strong> $email
        <br>
        <br>Our support team member will process your request & keep you informed shortly.
        </div>
		
	</body>
</html>";
        $message->setBody($text, 'text/html');
        $mailer->send($message);
    }

    public function TrialUpgradeAction()
    {
        $paymentrepository        = $this->get('le.subscription.repository.payment');
        $lastpayment              = $paymentrepository->getLastPayment();
        $trialEndDays             = $this->get('mautic.helper.licenseinfo')->getLicenseRemainingDays();
        if ($lastpayment != null) {
            $dataArray['success'] = false;
        } else {
            $dataArray['success'] = true;
        }
        $dataArray['error']       = true;
        $upgradeinfo              = $this->get('translator')->trans('le.upgrade_now.action');
        $trailinfo                = $this->get('translator')->trans('le.upgrade.trial.period');
        $action                   = $this->generateUrl('le_pricing_index');
        $upgradeinfo              = str_replace('|URL|', $action, $upgradeinfo);
        $trailinfo                = str_replace('|DAYS|', $trialEndDays, $trailinfo);
        if ($trialEndDays < 0) {
            $trailinfo                = $this->get('translator')->trans('le.upgrade.trial.period.expired');
        }
        $dataArray['upgradeinfo'] = $upgradeinfo;
        $dataArray['trailinfo']   = $trailinfo;

        return $this->sendJsonResponse($dataArray);
    }

    public function updateAccountData($accountData)
    {
        $licenseinfoHelper = $this->get('mautic.helper.licenseinfo');
        $subsrepository    = $this->get('le.core.repository.subscription');
        $dbhost            = $this->coreParametersHelper->getParameter('le_db_host');
        $kycview           = $licenseinfoHelper->getFirstTimeSetup($dbhost, true);
        /** @var \Mautic\SubscriptionBundle\Entity\Billing $billingEntity */
        $billingEntity  = $kycview[3];
        /** @var \Mautic\SubscriptionBundle\Entity\Account $accountEntity */
        $accountEntity  = $kycview[4];
        /** @var \Mautic\UserBundle\Entity\User $userEntity */
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
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $accountModel */
        $accountModel  = $this->getModel('subscription.accountinfo');
        /** @var \Mautic\SubscriptionBundle\Model\BillingModel $billingmodel */
        $billingmodel  = $this->getModel('subscription.billinginfo');
        //$userEntity->setMobile($accountData['phone']);
        //$accountEntity->setPhonenumber($accountData['phone']);
        $accountEntity->setWebsite($accountData['website']);
        $accountEntity->setAccountname($accountData['business']);
        //$accountEntity->setEmail($userEntity->getEmail());
        $billingEntity->setCompanyname($accountData['business']);
        $billingEntity->setCompanyaddress($accountData['address']);
        //$billingEntity->setAccountingemail($userEntity->getEmail());
        $billingEntity->setCity($accountData['city']);
        $billingEntity->setState($accountData['state']);
        $billingEntity->setCountry($accountData['country']);
        $billingEntity->setPostalcode($accountData['zipcode']);
        $kyc->setSubscribercount($accountData['currentlist']);
        $kyc->setPrevioussoftware($accountData['currentprovider']);

        $signupinfo     = $subsrepository->getSignupInfo($accountEntity->getEmail());
        /*if (!empty($signupinfo)) {
            $accountEntity->setDomainname($signupinfo[0]['f5']);
        }*/
        $billingmodel->saveEntity($billingEntity);
        $accountModel->saveEntity($accountEntity);
        $kycmodel->saveEntity($kyc);
        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator = $this->get('mautic.configurator');
        if ($accountData['address'] != '') {
            $postaladdress = $accountData['address'].', '.$accountData['city'].' - '.$accountData['zipcode'].'. '.$accountData['state'].', '.$accountData['country'].'.';
            $configurator->mergeParameters(['postal_address' => $postaladdress]);
            $configurator->write();
        }
        $signuprepository=$this->get('le.core.repository.signup');
        $signuprepository->updateAccountInfo($accountData, $userEntity->getEmail());
    }
}
