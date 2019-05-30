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
use Mautic\SubscriptionBundle\Entity\StateMachine;
use Mautic\SubscriptionBundle\Entity\StateMachineRepository;

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
                'state'   => ['Customer_Active', 'Trial_Active'],
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
                'state'   => ['Trial_Inactive_Expired', 'Trial_Inactive_Suspended', 'Customer_Sending_Domain_Not_Configured', 'Customer_Inactive_Suspended', 'Customer_Inactive_Under_Review', 'Customer_Inactive_Sending_Domain_Issue', 'Customer_Inactive_Payment_Issue', 'Customer_Inactive_Exit_Cancel', 'Customer_Inactive_Archive'],
                'isalive' => true,
            ]
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
                'state'   => ['Customer_Inactive_Payment_Issue', 'Customer_Inactive_Sending_Domain_Issue', 'Customer_Sending_Domain_Not_Configured', 'Customer_Inactive_Suspended', 'Customer_Inactive_Under_Review'],
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

    public function makeStateInActive($states)
    {
        $this->smrepo->updateActiveStatesAsInActive($states);
    }

    public function applyAppStatusByState()
    {
        $subsrepository=$this->factory->get('le.core.repository.subscription');
        $appStatus     ='InActive';
        if ($this->isAnyActiveStateAlive()) {
            $appStatus='Active';
        }
        $subsrepository->updateAppStatus($this->getAppDomain, $appStatus);
    }

    public function newStateEntry($state, $reason='', $updateGlobalStatus=true)
    {
        $stateEntry=new StateMachine();
        $stateEntry->setState($state);
        $stateEntry->setIsAlive(true);
        $stateEntry->setReason($reason);
        $stateEntry->setUpdatedOn(new \DateTime());
        $this->smrepo->saveEntity($stateEntry);
        if ($state == 'Customer_Inactive_Archive') {
            //$elasticApiHelper= $this->factory->get('mautic.helper.elasticapi');
            //$elasticApiHelper->deleteSubAccount();
            $subsrepository=$this->factory->get('le.core.repository.subscription');
            $subsrepository->updateAppStatus($this->getAppDomain, 'InActive');
        }
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
        $activeStates=$this->listAllActiveStates();
        if (in_array('Trial_Inactive_Expired', $activeStates)) {
            return $routerHelper->generate('le_pricing_index');
        } elseif (in_array('Customer_Inactive_Suspended', $activeStates)) {
            return $routerHelper->generate('le_account_suspended_action');
        } elseif (in_array('Customer_Inactive_Under_Review', $activeStates)) {
            return $routerHelper->generate('le_account_under_review_action');
        } elseif (in_array('Customer_Inactive_Payment_Issue', $activeStates)) {
            return $routerHelper->generate('le_accountinfo_action', ['objectAction' => 'cardinfo']);
        } elseif (in_array('Customer_Inactive_Sending_Domain_Issue', $activeStates)) {
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
        } else {
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
                $configurator->mergeParameters(['mailer_user' => $username, 'mailer_password' => $password]);
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
                $tracking_check   =$elasticApiHelper->verifyTracking($domain);
                $domainStatus     =$dkim_check && $spf_check;
                $sendingdomain->setdkimCheck($dkim_check);
                $sendingdomain->setspfCheck($spf_check);
                $sendingdomain->settrackingCheck($tracking_check);
                $sendingdomain->setStatus($domainStatus);
                $model->getRepository()->saveEntity($sendingdomain);
                if ($domainStatus) {
                    break;
                }
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

    public function checkLicenseValiditityWithGracePeriod()
    {
        $paymentrepository            = $this->factory->get('le.subscription.repository.payment');
        $lastpayment                  = $paymentrepository->getLastPayment();
        if ($lastpayment != null) {
            $validitityTill=$lastpayment->getValidityTill();
            $dtHelper      =new DateTimeHelper($validitityTill);
            $dtHelper->add('P3D');
            $diffdays=$dtHelper->getDiff('now', '%R%a', true);

            return $diffdays < 0;
        } else {
            return true;
        }
    }
}
