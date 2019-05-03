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
        $this->apikey               =$this->factory->getParameter('elastic_api_key');
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
}
