<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SubscriptionBundle\Helper;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\SubscriptionBundle\Entity\Account;
use Mautic\SubscriptionBundle\Entity\StateMachine;
use Mautic\SubscriptionBundle\Entity\StateMachineRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class StateMachineHelper.
 */
class StateMachineHelper
{
    /**
     * @var MauticFactory
     */
    protected $factory;
    protected $smrepo;

    //'#01_new_signup'=>'CKAU44C1H','#02_signup_activated'=>'CKMRZN9NG','#03_trial_subscribed'=>'CKMS0EWEQ','#04_domain_configured'=>'CKMS1NCEQ','#05_payments'=>'CK6UGDSJ0','#06_state_machine'=>'CK997TVB9','#07_compliance'=>'CK6UJ924U','#08_heavy_users'=>'CJW1RRL58','#09_action_needed'=>'CJW1GM6KD','#anyfunnels_team'=>'CJQ6SET7B'

    public $channelList = [
                                'new_signup'                            => 'CKAU44C1H',
                                'new_activated_signup'                  => 'CKMRZN9NG',
                                //'90_days_trial_subscribed'              => 'CKMS0EWEQ',
                                'sending_domain_configured'             => 'CKMS1NCEQ',
                                'subscription_payment_received'         => 'CK6UGDSJ0',
                                'addon_payment_received'                => 'CK6UGDSJ0',
                                'payment_failed_internal_action_needed' => 'CK6UGDSJ0',
                                'payment_failed_customer_action_needed' => 'CK6UGDSJ0',
                                'account_activation_failed'             => 'CJW1GM6KD',
                                'failed_signup_email'                   => 'CJW1GM6KD',
                                'trial_inactive_expired'                => 'CK997TVB9',
                                'trial_inactive_suspended'              => 'CK997TVB9',
                                'customer_sending_domain_not_configured'=> 'CK997TVB9',
                                'customer_active'                       => 'CK997TVB9',
                                'customer_inactive_suspended'           => 'CK997TVB9',
                                'customer_inactive_under_review'        => 'CK997TVB9',
                                'customer_inactive_sending_domain_issue'=> 'CK997TVB9',
                                'customer_inactive_payment_issue'       => 'CK997TVB9',
                                'customer_active_card_expiring_soon'    => 'CK997TVB9',
                                'customer_inactive_exit_cancel'         => 'CK997TVB9',
                                'customer_inactive_archive'             => 'CK997TVB9',
                                'trial_inactive_archive'                => 'CK997TVB9',
                                'trial_inactive_under_review'           => 'CK997TVB9',
                                'trial_inactive_sending_domain_issue'   => 'CK997TVB9',
                                //'trial_sending_domain_not_configured'   => 'CK997TVB9',
                               // 'trial_unverified_email'                => 'CK997TVB9',
                                'first_payment'                            => 'CKYBKNQP3',
                               ];

    public $fieldLabel    = ['mobile'=>'*Mobile*', 'email'=>'*Email*', 'signup_location'=>'*Signup Location*', 'signup_device'=>'*Signup Device*', 'signup_page'=>'*Signup Page*', 'account_creation_date'=>'*Account Creation Date*', 'company_name'=>'*Business Name*', 'website_url'=>'*Website URL*', /*'current_contact_size'=>'*Current Contact Size*',*/ 'existing_email_provider'=>'*Existing Email Provider*', 'gdpr_timezone'=>'*Time Zone*', 'last_15_days_email_send'=>'*Last 15 Days Email Sent*', 'last_activity_in_app'=>'*Last Active in App*'];
    public $basicLabel    = ['firstname', 'lastname', 'domain', 'mobile', 'email', 'signup_location', 'signup_device', 'signup_page'];
    public $advanceLabel  = ['firstname', 'lastname', 'domain', 'mobile', 'email', 'account_creation_date', 'company_name', 'website_url', /*'current_contact_size',*/ 'existing_email_provider', 'city', 'country', 'gdpr_timezone', 'last_15_days_email_send', 'last_activity_in_app', 'signup_page'];
    public $basictype     = ['trial_unverified_email', 'trial_inactive_archive', 'trial_inactive_under_review', 'trial_inactive_sending_domain_issue', 'trial_sending_domain_not_configured', 'new_signup', 'new_activated_signup', 'account_activation_failed', 'trial_inactive_expired', 'trial_inactive_suspended', 'failed_signup_email'];

    public function __construct(MauticFactory $factory, StateMachineRepository $smrepo)
    {
        $this->factory   = $factory;
        $this->smrepo    = $smrepo;
    }

    public function isStateAlive($state)
    {
        $states      =$this->smrepo->findBy(
            [
                'state'   => $state,
                'isalive' => true,
            ]
        );
        if (sizeof($states) > 0) {
            return $states[0];
        } else {
            return false;
        }
    }

    public function isStateNotAlive($state)
    {
        $states      =$this->smrepo->findBy(
            [
                'state'   => $state,
                'isalive' => false,
            ]
        );
        if (sizeof($states) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function listAllActiveStates()
    {
        $states      =$this->smrepo->findBy(
            [
                'isalive' => true,
            ]
        );
        $activeStates=[];
        foreach ($states as $state) {
            $activeStates[]=   $state->getState();
        }

        return $activeStates;
    }

    public function isAnyActiveStateAlive()
    {
        $states      =$this->smrepo->findBy(
            [
                'state'   => ['Customer_Active', 'Trial_Active', 'Trial_Sending_Domain_Not_Configured', 'Customer_Sending_Domain_Not_Configured'],
                'isalive' => true,
            ]
        );
        if (sizeof($states) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function isAnyInActiveStateAlive()
    {
        $states      =$this->smrepo->findBy(
            [
                'state'   => ['Trial_Inactive_Expired', 'Trial_Inactive_Suspended', 'Customer_Inactive_Suspended', 'Customer_Inactive_Under_Review', 'Customer_Inactive_Sending_Domain_Issue', 'Customer_Inactive_Payment_Issue', 'Customer_Inactive_Archive'],
                'isalive' => true,
            ]//'Customer_Inactive_Exit_Cancel'
        );
        if (sizeof($states) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getFirstInActiveState()
    {
        $states      =$this->smrepo->findBy(
            [
                'state'   => ['Trial_Sending_Domain_Not_Configured', 'Trial_Unverified_Email', 'Trial_Inactive_Under_Review', 'Trial_Inactive_Sending_Domain_Issue', 'Trial_Inactive_Expired', 'Trial_Inactive_Suspended', 'Customer_Inactive_Payment_Issue', 'Customer_Inactive_Sending_Domain_Issue', 'Customer_Inactive_Suspended', 'Customer_Inactive_Under_Review'],
                'isalive' => true,
            ],
            ['updatedOn'=> 'ASC'],
            1,
            0
        );
        if (sizeof($states) > 0) {
            return $states[0];
        } else {
            return false;
        }
    }

    public function getFirstStateEntity()
    {
        $states      =$this->smrepo->findBy(
            [],
            ['updatedOn'=> 'ASC'],
            1,
            0
        );
        if (sizeof($states) > 0) {
            return $states[0];
        } else {
            return false;
        }
    }

    public function makeStateInActive($states)
    {
        $this->smrepo->updateActiveStatesAsInActive($states);
        $this->addLeadNotes($states, '', 'Customer Changes into Active State(s) ');
    }

    public function applyAppStatusByState()
    {
        $subsrepository=$this->factory->get('le.core.repository.subscription');
        $appStatus     ='InActive';
        if ($this->isAnyActiveStateAlive()) {
            $appStatus='Active';
        }
        $subsrepository->updateAppStatus($this->getAppDomain(), $appStatus);
    }

    public function newStateEntry($state, $reason='', $updateGlobalStatus=true)
    {
        $stateEntry=new StateMachine();
        $stateEntry->setState($state);
        $stateEntry->setIsAlive(true);
        $stateEntry->setReason($reason);
        $stateEntry->setUpdatedOn(new \DateTime());
        $this->smrepo->saveEntity($stateEntry);
        if ($state == 'Customer_Inactive_Archive' || $state == 'Trial_Inactive_Archive') {
            //$elasticApiHelper= $this->factory->get('mautic.helper.elasticapi');
            //$elasticApiHelper->deleteSubAccount();
            $subsrepository=$this->factory->get('le.core.repository.subscription');
            $subsrepository->updateAppStatus($this->getAppDomain(), 'InActive');
        }
        $comment = 'Customer Enters into this State(s)';
        $this->addLeadNotes($state, $reason, $comment);
        //if ($state != 'Customer_Active' && $state != 'Customer_Sending_Domain_Not_Configured' && $state != 'Trial_Sending_Domain_Not_Configured') {
        $this->sendInternalSlackMessage($state);
        // }
    }

    public function getAlertMessage($message, $personalize=[])
    {
        return $this->factory->get('translator')->trans($message, $personalize);
    }

    public function checkStateAndRedirectPage()
    {
        $routerHelper = $this->factory->get('templating.helper.router');
        $currentuser  = $this->factory->getUser();
        if ($currentuser->isAdmin()) {
            return false;
        }
        $paymentrepository  =$this->factory->get('le.subscription.repository.payment');
        $lastpayment        = $paymentrepository->getLastPayment();
        $prefix             = 'Trial';
        if ($lastpayment != null) {
            $prefix = 'Customer';
        }
        $activeStates=$this->listAllActiveStates();
        if (in_array('Trial_Inactive_Expired', $activeStates)) {
            return $routerHelper->generate('le_pricing_index');
        } elseif (in_array($prefix.'_Inactive_Suspended', $activeStates)) {
            return $routerHelper->generate('le_account_suspended_action');
        } elseif (in_array($prefix.'_Inactive_Under_Review', $activeStates)) {
            return $routerHelper->generate('le_account_under_review_action');
        } elseif (in_array('Customer_Inactive_Payment_Issue', $activeStates)) {
            return $routerHelper->generate('le_accountinfo_action', ['objectAction' => 'cardinfo']);
        } elseif (in_array($prefix.'_Inactive_Sending_Domain_Issue', $activeStates)) {
            return $routerHelper->generate('le_account_sending_domain_inactive_action');
        } else {
            return false;
        }
    }

    public function getAppDomain()
    {
        $cachepath=$this->factory->getParameter('cache_path');
        $details  =explode('/', $cachepath);

        return $details[sizeof($details) - 1];
    }

    public function createElasticSubAccountandAssign()
    {
        $elasticApiHelper= $this->factory->get('mautic.helper.elasticapi');
        $response        =$elasticApiHelper->createSubAccount();
        if (isset($response[2]) && $response[2] == '') {
            $apiKey=$response[1];
            //file_put_contents("/var/www/elapi.txt","API KEY:".$apiKey."\n",FILE_APPEND);
            $notifyresponse=$elasticApiHelper->updateHTTPNotification($apiKey);
            if (!$notifyresponse[0]) {
                //file_put_contents("/var/www/elapi.txt","111111:".$notifyresponse[1]."\n",FILE_APPEND);
            }
            $auresponse=$elasticApiHelper->updateAccountProfile($apiKey);
            if (!$auresponse[0]) {
                // file_put_contents("/var/www/elapi.txt","222222:".$auresponse[1]."\n",FILE_APPEND);
            }
            $this->updateElasticAccountConfiguration($response[0], $response[1]);

            return $response[1];
        } else {
            return false;
            //file_put_contents("/var/www/elapi.txt","API Failed:".$response[1]."\n",FILE_APPEND);
        }
    }

    public function updateElasticAccountConfiguration($username, $password)
    {
        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator   = $this->factory->get('mautic.configurator');
        $isWritabale    = $configurator->isFileWritable();
        if ($isWritabale) {
            try {
                $configurator->mergeParameters(['mailer_user' => $username, 'mailer_password' => $password, 'mailer_transport_name'=> 'le.transport.elasticemail']);
                $configurator->write();
                $cacheHelper = $this->factory->get('mautic.helper.cache');
                $cacheHelper->clearContainerFile();
            } catch (\Exception $ex) {
                //file_put_contents("/var/www/elapi.txt","Exception occured:".$ex->getMessage()."\n",FILE_APPEND);
            }
        } else {
            //file_put_contents("/var/www/elapi.txt","Config file is not writable"."\n",FILE_APPEND);
        }
    }

    public function checkSendingDomainStatus()
    {
        $domainStatus     =false;
        $model            = $this->factory->getModel('email');
        $elasticApiHelper = $this->factory->get('mautic.helper.elasticapi');
        $domainList       =$model->getRepository()->getAllSendingDomains();
        foreach ($domainList as $sendingdomain) {
            if ($sendingdomain->getStatus()) {
                $domain           =$sendingdomain->getDomain();
                $spf_check        =$elasticApiHelper->verifySPF($domain);
                $dkim_check       =$elasticApiHelper->verifyDKIM($domain);
                $dmark_check      =$elasticApiHelper->verifyDMARK($domain);
                // $tracking_check   =$elasticApiHelper->verifyTracking($domain);
                $sendingdomain->setdkimCheck($dkim_check);
                $sendingdomain->setspfCheck($spf_check);
                $sendingdomain->settrackingCheck(false);
                $sendingdomain->setdmarcCheck($dmark_check);
                $sendingdomain->setStatus($dkim_check && $spf_check);
                if ($sendingdomain->getIsDefault() && $sendingdomain->getStatus()) {
                    $domainStatus=true;
                }
                $model->getRepository()->saveEntity($sendingdomain);
//                if ($domainStatus) {
//                    break;
//                }
            }
        }

        return $domainStatus;
    }

    public function getStripeCardExpiryInfo()
    {
        $expiryInfo       ='';
        $stripecardrepo   = $this->factory->get('le.subscription.repository.stripecard');
        $stripecards      = $stripecardrepo->findAll();
        $stripecard       = null;
        if (sizeof($stripecards) > 0) {
            $stripecard = $stripecards[0];
        }
        if ($stripecard != null) {
            $expMonth  =$stripecard->getExpMonth();
            $expYear   =$stripecard->getExpYear();
            $expiryInfo=$expMonth.'/'.$expYear;
        }

        return $expiryInfo;
    }

    public function isStripeCardWillExpire()
    {
        $stripecardrepo   = $this->factory->get('le.subscription.repository.stripecard');
        $stripecards      = $stripecardrepo->findAll();
        $stripecard       = null;
        if (sizeof($stripecards) > 0) {
            $stripecard = $stripecards[0];
        }
        if ($stripecard != null) {
            $expMonth    =$stripecard->getExpMonth();
            $expYear     =$stripecard->getExpYear();
            $currentMonth=date('m');
            $currentYear =date('Y');
            if ($expMonth == $currentMonth && $expYear == $currentYear) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getAccountInActiveAlert($state)
    {
        $paymentrepository            = $this->factory->get('le.subscription.repository.payment');
        $lastpayment                  = $paymentrepository->getLastPayment();
        if ($lastpayment != null) {
            if ($lastpayment->getPlanName() == 'leplan1') {
                $updateOn=$state->getUpdatedOn();
                $dtHelper=new DateTimeHelper($updateOn);
                $dtHelper->add('P3D');
                $updateOn=$dtHelper->getLocalDateTime();
                $updateOn=$this->factory->get('mautic.helper.template.date')->toDate($updateOn, 'local');

                return $this->factory->get('translator')->trans('le.subscription.exit.free.plan.alert', ['%DATE%'=>$updateOn]);
            } elseif ($lastpayment->getPlanName() == 'leplan2') {
                return $this->factory->get('translator')->trans('le.subscription.exit.paid.plan.alert');
            }
        } else {
            return '';
        }
    }

    public function checkLicenseValiditityWithGracePeriod($state)
    {
        $paymentrepository            = $this->factory->get('le.subscription.repository.payment');
        $lastpayment                  = $paymentrepository->getLastPayment();
        if ($lastpayment != null) {
            if ($lastpayment->getPlanName() == 'leplan1') {
                $updateOn=$state->getUpdatedOn();
                $dtHelper=new DateTimeHelper($updateOn);
                $dtHelper->add('P3D');
                $diffdays=$dtHelper->getDiff('now', '%R%a', true);

                return $diffdays < 0;
            } elseif ($lastpayment->getPlanName() == 'leplan2') {
                $validitityTill=$lastpayment->getValidityTill();
                $dtHelper      =new DateTimeHelper($validitityTill);
                $dtHelper->add('P3D');
                $diffdays=$dtHelper->getDiff('now', '%R%a', true);

                return $diffdays < 0;
            }
        } else {
            return true;
        }
    }

    public function isAnyInActiveGivenStateAlive()
    {
        $states      =$this->smrepo->findBy(
            [
                'state'   => ['Customer_Inactive_Archive', 'Trial_Inactive_Archive', 'Trial_Inactive_Expired', 'Trial_Inactive_Suspended'],
                'isalive' => true,
            ]
        );
        if (sizeof($states) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function addLeadNotes($states, $reason, $content)
    {
        $accountstatus = '';
        if (is_array($states)) {
            foreach ($states as $state) {
                $accountstatus .= $state.',';
            }
            $accountstatus = substr($accountstatus, 0, -1);
        } else {
            $accountstatus = $states;
        }
        $content .= $accountstatus;
        $signuprepository=$this->factory->get('le.core.repository.signup');
        $signuprepository->addLeadNotes($content, $reason, $this->getAppDomain());
    }

    public function addStateWithLead()
    {
        $states        = $this->listAllActiveStates();
        $accountstatus = '';
        foreach ($states as $state) {
            $accountstatus .= $state.'|';
        }
        $accountstatus   = substr($accountstatus, 0, -1);
        $signuprepository=$this->factory->get('le.core.repository.signup');
        $signuprepository->updateLeadStateInfo($accountstatus, $this->getAppDomain());
    }

    public function getInternalSlackData($contentType, $Domain=null)
    {
        if ($Domain == null) {
            $Domain = $this->getAppDomain();
        }
        $signuprepository  =$this->factory->get('le.core.repository.signup');
        $leadData          =  $signuprepository->getLeadInfo($Domain);
        $content           ='';
        if ($leadData == null) {
            return '';
        }
        foreach ($leadData as $key => $value) {
            if (!empty($value)) {
                if (in_array($key, $this->basicLabel) || in_array($key, $this->advanceLabel)) {
                    if ($contentType == 'Basic') {
                        if (in_array($key, $this->basicLabel)) {
                            if ($key == 'firstname' || $key == 'lastname') {
                                if (!preg_match('/Name/i', $content)) {
                                    $content .= "*Name* - {$leadData['firstname']} {$leadData['lastname']}\n";
                                }
                            } elseif ($key == 'domain') {
                                $content .= "*Domain* - {$leadData['domain']}.anyfunnels.com\n";
                            } else {
                                $content .= "{$this->fieldLabel[$key]} - $leadData[$key]\n";
                            }
                        }
                    } else {
                        if (in_array($key, $this->advanceLabel)) {
                            if ($key == 'firstname' || $key == 'lastname') {
                                if (!preg_match('/Name/i', $content)) {
                                    $content .= "*Name* - {$leadData['firstname']} {$leadData['lastname']}\n";
                                }
                            } elseif ($key == 'city' || $key == 'country') {
                                if (!preg_match('/Country/i', $content)) {
                                    if (!empty($leadData['city'] && $leadData['country'])) {
                                        $content .= "*City/ Country* - {$leadData['city']}/ {$leadData['country']}\n";
                                    } else {
                                        $content .= "*City/ Country* - {$leadData[$key]}\n";
                                    }
                                }
                            } elseif ($key == 'domain') {
                                $content .= "*Domain* - {$leadData['domain']}.anyfunnels.com\n";
                            } else {
                                $content .= "{$this->fieldLabel[$key]} - $leadData[$key]\n";
                            }
                        }
                    }
                }
            }
        }

        if ($content != '') {
            $content .= "--- \n AnyFunnels Bot";
        }

//        if ($contentType == 'Basic') {
//            //$content = "*Name* - {$leadData['firstname']} {$leadData['lastname']} \n isset(*Mobile* - {$leadData['mobile']} \n *Email* - {$leadData['email']} \n *Domain* - {$leadData['domain']}.anyfunnels.com \n *Signup Location* - {$leadData['signup_location']} \n *Signup Device* - {$leadData['signup_device']} \n *Signup Page* - {$leadData['signup_page']} \n --- \n AnyFunnels Bot";
//            //$content = $this->factory->getTranslator()->trans('le.subscription.internalslack.basic.message',['%name%'=>"*Name* - {$leadData['firstname']} {$leadData['lastname']} \n",'%mobile%'=>"*Mobile* - {$leadData['mobile']} \n",'%email%'=>"*Email* - {$leadData['email']} \n",'%domain%'=>"*Domain* - {$leadData['domain']}.anyfunnels.com \n",'%signup_location%'=>"*Signup Location* - {$leadData['signup_location']} \n",'%signup_device%'=>"*Signup Device* - {$leadData['signup_device']} \n",'%signup_page%'=>"*Signup Page* - {$leadData['signup_page']} \n", '%footer%'=> "--- \n AnyFunnels Bot"]);
//        } else {
//            //$content = "*Name* - {$leadData['firstname']} {$leadData['lastname']}\n *Mobile* - {$leadData['mobile']}\n *Email* - {$leadData['email']}\n *Domain* - {$leadData['domain']}.anyfunnels.com\n *Account Creation Date* - {$leadData['account_creation_date']}\n *Business Name* - {$leadData['company_name']}\n *Website URL* - {$leadData['website_url']}\n *Current Contact Size* - {$leadData['current_contact_size']}\n *Existing Email Provider* - {$leadData['existing_email_provider']}\n *City/ Country* - {$leadData['city']}/ {$leadData['country']}\n *Time Zone* - {$leadData['gdpr_timezone']}\n *Last 15 Days Email Sent* - {$leadData['last_15_days_email_send']}\n *Last Active in App* - {$leadData['last_activity_in_app']}\n *Signup Page* - {$leadData['signup_page']}\n --- \n AnyFunnels Bot ";
//           // $content = $this->factory->getTranslator()->trans('le.subscription.internalslack.advanced.message',['%name%'=>"*Name* - {$leadData['firstname']} {$leadData['lastname']} \n",'%mobile%'=>"*Mobile* - {$leadData['mobile']} \n",'%email%'=>"*Email* - {$leadData['email']} \n",'%domain%'=>"*Domain* - {$leadData['domain']}.anyfunnels.com \n",'%account_creation_date%'=>"*Account Creation Date* - {$leadData['account_creation_date']}\n",'%company_name%'=>"*Business Name* - {$leadData['company_name']}\n",'%website_url%'=>"*Website URL* - {$leadData['website_url']}\n",'%current_contact_size%'=>"*Current Contact Size* - {$leadData['current_contact_size']}\n",'%existing_email_provider%'=>"*Existing Email Provider* - {$leadData['existing_email_provider']}\n",'%city&country%'=>"*City/ Country* - {$leadData['city']}/ {$leadData['country']}\n",'%timezone%'=>"*Time Zone* - {$leadData['gdpr_timezone']}\n",'%last_15_days_email_send%'=>"*Last 15 Days Email Sent* - {$leadData['last_15_days_email_send']}\n",'%last_activity_in_app%'=>"*Last Active in App* - {$leadData['last_activity_in_app']}\n",'%signup_page%'=>"*Signup Page* - {$leadData['signup_page']}\n",'%footer%'=> "--- \n AnyFunnels Bot"]);
//
//        }

        return $content;
    }

    public function processWebhookInternalSlack($data)
    {
        try {
            $success               = false;
            if (isset($data->state) && isset($data->domain)) {
                $stateval = strtolower($data->state);
                $this->sendInternalSlackMessage($stateval, $data->domain);
                $success           =true;
            }
        } catch (\Exception $ex) {
            $success = false;
        }

        $response = new JsonResponse(['success' => $success]);

        return $response;
    }

    public function sendInternalSlackMessage($state, $domain=null)
    {
        if (!isset($this->channelList[$state])) {
            return ['success'=>false, 'error'=>'channel not found for given state'];
        }
        $state        = strtolower($state);
        $contentType  = !in_array($state, $this->basictype) ? 'Advanced' : 'Basic';
        $slackContent = $this->getInternalSlackData($contentType, $domain);
        $res          = ['success' => true, 'error' => ''];
        if ($slackContent == '') {
            $res      = ['success' => false, 'error' => 'content is empty'];

            return $res;
        }
        $channel      = $this->channelList[$state];
        $token        = $this->factory->getParameter('slack_internal_token');
        $posturl      = "https://slack.com/api/chat.postMessage?token=$token&channel=$channel";
        $attachment   = [
            [
                'color'      => '#3292e0',
                'title'      => '#'.$state,
                'title_link' => '#',
                'text'       => $slackContent,
            ],
        ];
        $attachmentstr = urlencode(json_encode($attachment));
        $posturl .= '&attachments='.$attachmentstr;
        $curl = curl_init($posturl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded, application/json']);
        $result   = curl_exec($curl);
        $response = json_decode($result);
        $res      = ['success' => true, 'error' => ''];
        if (!$response->ok) {
            $res['success'] = false;
            $res['error']   = $response->error;
        }

        return $res;
    }

    public function sendInactiveUnderReviewEmail()
    {
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $model */
        $model               = $this->factory->getModel('subscription.accountinfo');
        $accrepo             = $model->getRepository();
        $accountentity       = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0];
        } else {
            $account = new Account();
        }
        $useremail  = $account->getEmail();
        $mailer     =  $this->factory->get('le.transport.elasticemail.transactions');
        $mailer->start();
        $message    = \Swift_Message::newInstance();
        $message->setTo([$useremail]);
        $message->setFrom(['notifications@anyfunnels.io' => 'AnyFunnels']);
        $message->setReplyTo(['support@anyfunnels.com' => 'AnyFunnels']);
        $message->setSubject($this->factory->getTranslator()->trans('le.subscription.email.underreview.subject'));
        $text = "<!DOCTYPE html>
        <html>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>

	<head>
		<title></title>
	</head>
	<body>
		<div>
            <span style='font-family: Verdana,Geneva,sans-serif;'>
                Attention Needed!
                <br>
                <br>Your account has been kept Under Review as our compliance team found that your account has high spam complaints or bounce rate or policy violations. You can’t send emails for now till you resolve the issues.
                <br>
                <br>Reply to this email or 
                <a href=\"https://anyfunnels.freshdesk.com/support/tickets/new\" rel=\"noopener noreferrer\" target=\"_blank\">Click here</a> 
                to contact our support team.
                <br>
                <br>
                ---
                <br>Regards,
                <br>AnyFunnels Team.
            </span>
            <span style='display:none;'>
                {unsubscribe}
            </span>
            <span style='display:none;'>
                {accountaddress}
            </span>
        </div>
	</body>
</html>";
        $message->setBody($text, 'text/html');
        $mailer->send($message);
    }

    public function sendSendingDomainIssueEmail()
    {
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $model */
        $model               = $this->factory->getModel('subscription.accountinfo');
        $accrepo             = $model->getRepository();
        $accountentity       = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0];
        } else {
            $account = new Account();
        }
        $useremail  = $account->getEmail();
        $mailer     =  $this->factory->get('le.transport.elasticemail.transactions');
        $mailer->start();
        $message    = \Swift_Message::newInstance();
        $message->setTo([$useremail]);
        $message->setFrom(['notifications@anyfunnels.io' => 'AnyFunnels']);
        $message->setReplyTo(['support@anyfunnels.com' => 'AnyFunnels']);
        $message->setSubject($this->factory->getTranslator()->trans('le.subscription.email.underreview.subject'));
        $text = "<!DOCTYPE html>
        <html>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>

	<head>
		<title></title>
	</head>
	<body>
		<div>
            <span style='font-family: Verdana,Geneva,sans-serif;'>
                Attention Needed!
                <br>
                <br>Email delivery for your account has been Temporarily Inactive since you don’t have any active sending domain. You can’t send emails for now till you resolve the issues.
                <br>
                <br>Reply to this email or <a href='https://anyfunnels.freshdesk.com/support/tickets/new'>click here</a> to contact our support team if needed.
                <br>
                <br>
                ---
                <br>
                Regards,
                <br>
                AnyFunnels Team.
            </span>
            <span style='display:none;'>
                {unsubscribe}
            </span>
            <span style='display:none;'>
                {accountaddress}
            </span>
        </div>
	</body>
</html>";
        $message->setBody($text, 'text/html');
        $mailer->send($message);
    }

    public function sendInacitvePaymentIssueEmail()
    {
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $model */
        $model               = $this->factory->getModel('subscription.accountinfo');
        $accrepo             = $model->getRepository();
        $accountentity       = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0];
        } else {
            $account = new Account();
        }
        $stripecardrepo   = $this->factory->get('le.subscription.repository.stripecard');
        $stripecards      = $stripecardrepo->findAll();
        $stripecard       = null;
        if (sizeof($stripecards) > 0) {
            $stripecard = $stripecards[0];
        }
        if ($stripecard != null) {
            $last4Digit  =$stripecard->getlast4digit();
        }
        $useremail  = $account->getEmail();
        $mailer     =  $this->factory->get('le.transport.elasticemail.transactions');
        $mailer->start();
        $message    = \Swift_Message::newInstance();
        $message->setTo([$useremail]);
        $message->setFrom(['notifications@anyfunnels.io' => 'AnyFunnels']);
        $message->setReplyTo(['support@anyfunnels.com' => 'AnyFunnels']);
        $message->setSubject($this->factory->getTranslator()->trans('le.subscription.email.payment.issue.subject'));
        $text = "<!DOCTYPE html>
        <html>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>

	<head>
		<title></title>
	</head>
	<body>
		<div>
            <span style=\"font-family: Verdana,Geneva,sans-serif;\">
                Attention Needed!
                <div>
                <br>
                </div>There was an error when we tried to bill your credit card ending in $last4Digit for your subscription to AnyFunnels.
                <div>
                <br>
                </div>This frequently occurs when there is
                <ul>
                    <li>a billing error caused by your bank</li>
                    <li>a change in your billing address</li>
                    <li>insufficient credit on your account</li>
                    <li>your credit card has expired</li>
                </ul>Your account has been kept Inactive temporarily, kindly update your account information with a valid credit card to reactivate your account.
                <div>
                <br>
                </div>Unfortunately, if after 30 days we still cannot successfully bill your credit card then your AnyFunnels account will be suspended automatically. If you have a question, please reply to this email or 
                <span style=\"font-family: Verdana,Geneva,sans-serif;\">
                    <a href=\"https://anyfunnels.freshdesk.com/support/tickets/new\" rel=\"noopener noreferrer\" target=\"_blank\">
                        click here
                    </a>
                    <span style=\"font-family: Verdana,Geneva,sans-serif;\">to contact our support team.
                        <br>
                        <br>
                        ---
                        <br>Regards,
                        <br>AnyFunnels Team.
                    </span>
                </span>
                <span style='display:none;'>
                    {unsubscribe}
                </span>
                <span style='display:none;'>
                    {accountaddress}
                </span>
            </span>
        </div>
	</body>
</html>";
        $message->setBody($text, 'text/html');
        $mailer->send($message);
    }
}
