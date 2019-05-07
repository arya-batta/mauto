<?php
/**
 * Created by PhpStorm.
 * User: prabhu
 * Date: 23/4/19
 * Time: 6:47 PM.
 */

namespace Mautic\EmailBundle\Helper;

use Mautic\CoreBundle\Factory\MauticFactory;

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
        if ($this->apikey == '') {
            return false;
        }
        if (!isset($payload['apikey'])) {
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

    public function createDomain($domain)
    {
        $response=$this->sendApiRequest('domain/add', ['domain'=>$domain]);
        $status  =false;
        if (isset($response->success) && $response->success) {
            $status=true;
        } elseif (isset($response->success) && !$response->success && strpos($response->error, 'domain is already')) {
            $status=true;
        }

        return $status;
    }

    public function deleteDomain($domain)
    {
        $response=$this->sendApiRequest('domain/delete', ['domain'=>$domain]);
        $status  =false;
        if (isset($response->success) && $response->success) {
            $status=true;
        } elseif (isset($response->success) && !$response->success && strpos($response->error, 'not found')) {
            $status=true;
        }

        return $status;
    }

    public function listAllDomains()
    {
        $response=$this->sendApiRequest('domain/list', []);
        $list    =[];
        if (isset($response->success) && $response->success) {
            foreach ($response->data as $data) {
                $list[$data->domain]=['spf'=>$data->spf, 'dkim'=>$data->dkim, 'mx'=>$data->mx, 'dmarc'=>$data->dmarc];
            }
        }

        return $list;
    }

    public function verifyDKIM($domain)
    {
        $response=$this->sendApiRequest('domain/verifydkim', ['domain'=>$domain]);
        $status  =false;
        if (isset($response->success) && $response->success && $response->data == 'OK') {
            $status=true;
        }

        return $status;
    }

    public function verifySPF($domain)
    {
        $response=$this->sendApiRequest('domain/verifyspf', ['domain'=>$domain]);
        $status  =false;
        if (isset($response->success) && $response->success && $response->data->isvalid) {
            $status=true;
        }

        return $status;
    }

    public function verifyMX($domain)
    {
        $response=$this->sendApiRequest('domain/verifymx', ['domain'=>$domain]);
        $status  =false;
        if (isset($response->success) && $response->success && $response->data == 'OK') {
            $status=true;
        }

        return $status;
    }

    public function verifyTracking($domain)
    {
        $response=$this->sendApiRequest('domain/verifytracking', ['domain'=>$domain]);
        $status  =false;
        if (isset($response->success) && $response->success && $response->data == 'OK') {
            $status=true;
        }

        return $status;
    }

    public function getReputationDetails()
    {
        $response=$this->sendApiRequest('account/loadreputationimpact', ['apikey'=>$this->getAccessToken()]);
        $details =[];
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

        return $details;
    }

    public function getNewToken()
    {
        $newToken='';
        $response=$this->sendApiRequest('accesstoken/add', ['tokenName'=>uniqid('af_'), 'accessLevel'=> 1]);
        if (isset($response->success) && $response->success && isset($response->data)) {
            $newToken=$response->data;
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
        }

        return $accesstoken;
    }
}
