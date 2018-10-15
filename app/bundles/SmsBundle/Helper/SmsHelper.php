<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SmsBundle\Helper;

use Doctrine\ORM\EntityManager;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\PhoneNumberHelper;
use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\SmsBundle\Model\SmsModel;

class SmsHelper
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var PhoneNumberHelper
     */
    protected $phoneNumberHelper;

    /**
     * @var SmsModel
     */
    protected $smsModel;

    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;
    /**
     * @var CoreParametersHelper
     */
    protected $coreParametersHelper;
    /**
     * @var MauticFactory
     */
    protected $factory;

    protected $configurator;

    /**
     * SmsHelper constructor.
     *
     * @param EntityManager        $em
     * @param LeadModel            $leadModel
     * @param PhoneNumberHelper    $phoneNumberHelper
     * @param SmsModel             $smsModel
     * @param IntegrationHelper    $integrationHelper
     * @param CoreParametersHelper $coreParametersHelper
     * @param MauticFactory        $factory
     */
    public function __construct(EntityManager $em, LeadModel $leadModel, PhoneNumberHelper $phoneNumberHelper, SmsModel $smsModel, IntegrationHelper $integrationHelper, CoreParametersHelper $coreParametersHelper, MauticFactory $factory)
    {
        $this->em                   = $em;
        $this->leadModel            = $leadModel;
        $this->phoneNumberHelper    = $phoneNumberHelper;
        $this->smsModel             = $smsModel;
        $this->integrationHelper    = $integrationHelper;
        $integration                = $integrationHelper->getIntegrationObject('SolutionInfinity');
        $settings                   = $integration->getIntegrationSettings()->getFeatureSettings();
        $this->smsFrequencyNumber   = $settings['frequency_number'];
        $this->coreParametersHelper = $coreParametersHelper;
        $this->factory              = $factory;
    }

    public function getSmsTransportStatus($sendsms = true)
    {
        $cacheHelper = $this->factory->get('mautic.helper.cache');
        $cacheHelper->clearContainerFile();
        $configurator          = $this->factory->get('mautic.configurator');
        $settings[]            ='';
        $settings['transport'] =$this->coreParametersHelper->getParameter('sms_transport');
        $settings['url']       =$this->coreParametersHelper->getParameter('account_url');
        $settings['senderid']  =$this->coreParametersHelper->getParameter('account_sender_id');
        $settings['apiKey']    =$this->coreParametersHelper->getParameter('account_api_key');
        $settings['username']  =$this->coreParametersHelper->getParameter('sms_username');
        $settings['password']  =$this->coreParametersHelper->getParameter('sms_password');
        $settings['fromnumber']=$this->coreParametersHelper->getParameter('sms_sending_phone_number');
        $sms_status            = $this->coreParametersHelper->getParameter('sms_status');
        if ($sms_status == 'Active') {
            if (!$sendsms) {
                return true;
            }
            $cacheHelper = $this->factory->get('mautic.helper.cache');
            $cacheHelper->clearContainerFile();
            $result = $this->testSmsServerConnection($settings, false);
            if ($result['success']) {
                return true;
            } else {
                $configurator->mergeParameters(['sms_status' => 'InActive']);
                $configurator->write();

                return false;
            }
        } else {
            if (!$sendsms) {
                return false;
            }
            $cacheHelper = $this->factory->get('mautic.helper.cache');
            $cacheHelper->clearContainerFile();
            $result = $this->testSmsServerConnection($settings, false);
            if ($result['success']) {
                $configurator->mergeParameters(['sms_status' => 'Active']);
                $configurator->write();

                return true;
            } else {
                return false;
            }
        }
    }

    public function unsubscribe($number)
    {
        $number = $this->phoneNumberHelper->format($number, PhoneNumberFormat::E164);

        /** @var \Mautic\LeadBundle\Entity\LeadRepository $repo */
        $repo = $this->em->getRepository('MauticLeadBundle:Lead');

        $args = [
            'filter' => [
                'force' => [
                    [
                        'column' => 'mobile',
                        'expr'   => 'eq',
                        'value'  => $number,
                    ],
                ],
            ],
        ];

        $leads = $repo->getEntities($args);

        if (!empty($leads)) {
            $lead = array_shift($leads);
        } else {
            // Try to find the lead based on the given phone number
            $args['filter']['force'][0]['column'] = 'phone';

            $leads = $repo->getEntities($args);

            if (!empty($leads)) {
                $lead = array_shift($leads);
            } else {
                return false;
            }
        }

        return $this->leadModel->addDncForLead($lead, 'sms', null, DoNotContact::UNSUBSCRIBED);
    }

    public function testSmsServerConnection($settings, $standardnumber=false)
    {
        $dataArray = ['success' => 0, 'message' => '', 'to_address_empty'=>false];
        $user      = $this->factory->get('mautic.helper.user')->getUser();
        if ($standardnumber){
            $sendnumber = $user->getMobile();
        }
        else{
            $sendnumber = '123456789';
        }

        $transport  = $settings['transport'];
        $translator = $this->factory->get('translator');
        $transport  = $translator->trans($transport);
        if ($transport == 'LeadsEngage'){
            $settings['url']        = $this->factory->get('mautic.helper.core_parameters')->getParameter('le_account_url');
            $settings['apiKey']     = $this->factory->get('mautic.helper.core_parameters')->getParameter('le_account_api_key');
            $settings['senderid']   = $this->factory->get('mautic.helper.core_parameters')->getParameter('le_account_sender_id');
        }
        $result     = true;
        $content    = "Hi, \n This is Test Message. \n Team LeadsEngage.";
        $msgcontent = urlencode($content);
        switch ($transport) {
            case 'SolutionInfinity':
            case 'LeadsEngage':
                $result = $this->sendSolutionSMS($settings['url'], $sendnumber, $content, $settings['apiKey'], $settings['senderid'], $standardnumber);
                break;
            case 'Twilio':
                $result = $this->sendTwilioSMS($sendnumber, $content, $settings['username'], $settings['password'], $settings['fromnumber'], $standardnumber);
                break;
        }
        if ($result == 'success') {
            $dataArray['success'] = 1;
            $dataArray['message'] = $translator->trans('le.send.sms.success', ['%mobile%'=>$sendnumber]);
        } else {
            $dataArray['success'] = 0;
            $dataArray['message'] = $result.'. '.$translator->trans('le.send.sms.failed');
        }

        return $dataArray;
    }

    public function sendSolutionSMS($url, $number, $content, $username, $senderID, $standardnumber = false)
    {
        if ($url == '' || $number == '' || $username == '' || $senderID == '') {
            return 'URL or Sender ID or Api Key or User number Cannot be Empty';
        }
        if(!$standardnumber){
            $number = "";
        }

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
                return 'success';
            } else {
                if (strpos($message, 'No Valid mobile numbers found') !== false) {
                    return 'success';
                }

                return $message;
            }
        } catch (NumberParseException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $number
     *
     * @return string
     *
     * @throws NumberParseException
     */
    protected function sanitizeNumber($number)
    {
        $util   = PhoneNumberUtil::getInstance();
        $parsed = $util->parse($number, 'IN');

        return $util->format($parsed, PhoneNumberFormat::E164);
    }

    protected function sendTwilioSMS($number, $content, $username, $password, $fromnumber, $standardnumber = false)
    {
        if ($number === null) {
            return false;
        }
        if ($fromnumber == '' || $username == '' || $password == '') {
            return 'From number or Account SID or Auth Token Cannot be Empty';
        }
        try {
            $client = new \Services_Twilio($password, $username);
            if (!$standardnumber) {
                $message = $client->account->messages->sendMessage(
                    $fromnumber,
                    $this->sanitizeNumber($number),
                    $content
                );
            }

            return 'success';
        } catch (\Services_Twilio_RestException $e) {
            return $e->getMessage();
        } catch (NumberParseException $e) {
            return $e->getMessage();
        }
    }
}
