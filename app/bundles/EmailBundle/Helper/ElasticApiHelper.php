<?php
/**
 * Created by PhpStorm.
 * User: prabhu
 * Date: 23/4/19
 * Time: 6:47 PM.
 */

namespace Mautic\EmailBundle\Helper;

use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ElasticApiHelper
{
    /**
     * @var MauticFactory
     */
    protected $factory;
    protected $apikey    ='';
    protected $apiUrlRoot='https://api.elasticemail.com/v2';

    public function __construct(MauticFactory $factory)
    {
        $this->factory              = $factory;
        $this->apikey               =$this->factory->getParameter('mailer_password');
    }

    public function sendApiRequest($url, $payload)
    {
        if (!isset($payload['apikey'])) {
            if ($this->apikey == '' || $this->apikey == 'le_trial_password') {
                return false;
            }
            $payload['apikey']=$this->apikey;
        }
        $ch = curl_init($this->apiUrlRoot.'/'.$url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($payload)));
        $result = curl_exec($ch);

        return json_decode($result);
    }

    public function createDomain($domain, $apikey)
    {
        $response=$this->sendApiRequest('domain/add', ['domain'=>$domain, 'trackingType' => '-2', 'apikey' => $apikey]);
        $status  =false;
        if ($response) {
            if (isset($response->success) && $response->success) {
                $status=true;
            } elseif (isset($response->success) && !$response->success && strpos($response->error, 'domain is already')) {
                $status=true;
            }
        }

        return $status;
    }

    public function deleteDomain($domain)
    {
        $response=$this->sendApiRequest('domain/delete', ['domain'=>$domain]);
        $status  =false;
        if ($response) {
            if (isset($response->success) && $response->success) {
                $status=true;
            } elseif (isset($response->success) && !$response->success && strpos($response->error, 'not found')) {
                $status=true;
            }
        }

        return $status;
    }

    public function listAllDomains()
    {
        $response=$this->sendApiRequest('domain/list', ['apikey'=>$this->apikey]);
        $list    =[];
        if ($response) {
            if (isset($response->success) && $response->success) {
                foreach ($response->data as $data) {
                    $list[$data->domain]=['spf'=>$data->spf, 'dkim'=>$data->dkim, 'mx'=>$data->mx, 'dmarc'=>$data->dmarc];
                }
            }
        }

        return $list;
    }

    public function verifyDKIM($domain)
    {
        $response=$this->sendApiRequest('domain/verifydkim', ['domain'=>$domain]);
        $status  =false;
        if ($response) {
            if (isset($response->success) && $response->success && $response->data == 'OK') {
                $status=true;
            }
        }

        return $status;
    }

    public function verifySPF($domain)
    {
        $response=$this->sendApiRequest('domain/verifyspf', ['domain'=>$domain]);
        $status  =false;
        if ($response) {
            if (isset($response->success) && $response->success && $response->data->isvalid) {
                $status=true;
            }
        }

        return $status;
    }

    public function verifyMX($domain)
    {
        $response=$this->sendApiRequest('domain/verifymx', ['domain'=>$domain]);
        $status  =false;
        if ($response) {
            if (isset($response->success) && $response->success && $response->data == 'OK') {
                $status=true;
            }
        }

        return $status;
    }

    public function verifyTracking($domain)
    {
        $response=$this->sendApiRequest('domain/verifytracking', ['domain'=>$domain]);
        $status  =false;
        if ($response) {
            if (isset($response->success) && $response->success && $response->data == 'OK') {
                $status=true;
            }
        }

        return $status;
    }

    public function verifyDMARK($domain)
    {
        $status  =false;
        $list    = $this->listAllDomains();
        if (isset($list[$domain])) {
            $status =  $list[$domain]['dmarc'];
        }

        return $status;
    }

    public function getReputationDetails()
    {
        $response=$this->sendApiRequest('account/loadreputationimpact', ['apikey'=>$this->getAccessToken()]);
        $details =[];
        if ($response) {
            if (isset($response->success) && $response->success && isset($response->data)) {
                if (isset($response->data->impact)) {
                    $data                       =$response->data;
                    $impact                     =$data->impact;
                    $complaints_score           =round($impact->abuse, 2);
                    $invalid_emails_score       =round($impact->unknownusers, 2);
                    $opened_emails_score        =round($impact->opened, 2);
                    $clicked_emails_score       =round($impact->clicked, 2);
                    $content_analysis_score     =round($impact->averagespamscore, 2);
                    $complaints_percentage      =round($data->abusepercent, 2);
                    $invalid_emails_percentage  =round($data->unknownuserspercent, 2);
                    $opened_emails_percentage   =round($data->openedpercent, 2);
                    $clicked_emails_percentage  =round($data->clickedpercent, 2);
                    $content_analysis_percentage=round($data->averagespamscore, 2);
                    $rows[]                     =['Complaints', 'Users reporting your email as spam', $complaints_percentage.'%', $complaints_score];
                    $rows[]                     =['Invalid email addresses', 'Email addresses that do not exist', $invalid_emails_percentage.'%', $invalid_emails_score];
                    $rows[]                     =['Content analysis', 'How your email content is rating to spam filtering software', $content_analysis_percentage, $content_analysis_score];
                    $rows[]                     =['Open rate', 'Your last 30 day open rate', $opened_emails_percentage.'%', $opened_emails_score];
                    $rows[]                     =['Click rate', 'Your last 30 day click rate', $clicked_emails_percentage.'%', $clicked_emails_score];
                    $details[]                  =$rows;
                    $reputation                 =$data->reputation;
                    $details[]                  =$reputation;
                    $starrating                 =round(($reputation / 100) * 5, 0, PHP_ROUND_HALF_DOWN);
                    $details[]                  =$starrating;
                }
            }
        }

        return $details;
    }

    public function getNewToken()
    {
        $newToken='';
        $response=$this->sendApiRequest('accesstoken/add', ['tokenName'=>uniqid('af_'), 'accessLevel'=> 1]);
        if ($response) {
            if (isset($response->success) && $response->success && isset($response->data)) {
                $newToken=$response->data;
            }
        }

        return $newToken;
    }

    public function getAccessToken()
    {
        $accesstoken=$this->factory->getParameter('elastic_access_token');
        if (empty($accesstoken)) {
            $accesstoken    =$this->getNewToken();
            $configurator   = $this->factory->get('mautic.configurator');
            $configurator->mergeParameters(['elastic_access_token' => $accesstoken]);
            $configurator->write();
            $cacheHelper = $this->factory->get('mautic.helper.cache');
            $cacheHelper->clearContainerFile();
        }

        return $accesstoken;
    }

    public function createSubAccount()
    {
        $domain                           =$this->factory->get('le.helper.statemachine')->getAppDomain();
        $password                         =$this->factory->getParameter('elastic_subaccount_password');
        $rootApiKey                       =$this->factory->getParameter('le_elastic_email_root_password');
        $username                         =$domain.'@anyfunnels.net';
        $payLoad['email']                 =$username;
        $payLoad['password']              =$password;
        $payLoad['confirmPassword']       =$password;
        $payLoad['requiresEmailCredits']  ='false';
        $payLoad['enableLitmusTest']      ='false';
        $payLoad['requiresLitmusCredits'] ='false';
        $payLoad['maxContacts']           ='100000';
        $payLoad['enablePrivateIPRequest']='false';
        $payLoad['sendActivation']        ='false';
        $payLoad['sendingPermission']     ='Smtp';
        $payLoad['emailSizeLimit']        ='10';
        $payLoad['dailySendLimit']        ='100000';
        $payLoad['apikey']                =$rootApiKey;
        $response                         =$this->sendApiRequest('account/addsubaccount', $payLoad);
        $apikey                           ='';
        $apierror                         ='';

        if ($response) {
            if (isset($response->success) && $response->success) {
                $apikey=$response->data;
            } else {
                $apierror=$response->error;
            }
        }

        return [$username, $apikey, $apierror];
    }

    public function updateHTTPNotification($apikey)
    {
        $webhookid        =uniqid('af_');
        $payLoad['apikey']=$apikey;
        // $payLoad['webhookID']=$webhookid;
        $payLoad['name']                       =$webhookid;
        $payLoad['notificationForAbuseReport'] ='true';
        $payLoad['notificationForClicked']     ='false';
        $payLoad['notificationForError']       ='true';
        $payLoad['notificationForOpened']      ='false';
        $payLoad['notificationForSent']        ='false';
        $payLoad['notificationForUnsubscribed']='false';
        $payLoad['notifyOncePerEmail']         ='true';
        $payLoad['webNotificationUrl']         = $this->factory->getRouter()->generate('le_mailer_transport_callback', ['transport' => 'elasticemail'], UrlGeneratorInterface::ABSOLUTE_URL);
        $response                              =$this->sendApiRequest('account/addwebhook', $payLoad);
        if ($response) {
            if (isset($response->success) && $response->success) {
                return [true, ''];
            } else {
                return [false, $response->error];
            }
        } else {
            return [false, 'api key not configured'];
        }
    }

    public function updateAccountProfile($apikey)
    {
        $payLoad['apikey']   =$apikey;
        $payLoad['firstName']='AnyFunnels';
        $payLoad['lastName'] ='Support';
        $payLoad['address1'] ='#52,New Colony 1st Main Road';
        $payLoad['city']     ='Chrompet';
        $payLoad['state']    ='Tamil Nadu';
        $payLoad['zip']      ='600044';
        $payLoad['countryID']='100';
        $response            =$this->sendApiRequest('account/updateprofile', $payLoad);
        if ($response) {
            if (isset($response->success) && $response->success) {
                return [true, ''];
            } else {
                return [false, $response->error];
            }
        } else {
            return [false, 'api key not configured'];
        }
    }

    public function checkAccountState()
    {
        $response=$this->sendApiRequest('account/load', []);
        if ($response) {
            if (isset($response->success) && $response->success) {
                $status = $response->data->statusformatted;
                if ($status == 'Active') {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    public function deleteSubAccount()
    {
        $response=$this->sendApiRequest('account/deletesubaccount', []);
        if ($response) {
            if (isset($response->success) && $response->success) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
