<?php

namespace Mautic\PluginBundle\Helper;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Mautic\CoreBundle\Factory\MauticFactory;

class FacebookApiHelper
{
    /**
     * @var MauticFactory
     */
    protected $factory;

    /**
     * @var Facebook
     */
    protected $fbconn;

    protected $fbAdsApi;

    protected $FBAPPID       ='';
    protected $FBSECRET      ='';
    protected $OAUTH_CALLBACK='';

    public function __construct(MauticFactory $factory)
    {
        $this->factory              = $factory;

        $this->FBAPPID       =$this->factory->getParameter('facebook_app_id');
        $this->FBSECRET      =$this->factory->getParameter('facebook_app_secret');
        $this->OAUTH_CALLBACK=$this->factory->getParameter('facebook_oauth_callback');

        $this->fbconn  = new Facebook([
            'app_id'                  => $this->FBAPPID,
            'app_secret'              => $this->FBSECRET,
        ]);
    }

    public function getAccountDetails($token)
    {
        $response    = $this->fbconn->get('/me?fields=id,name', $token);
        $graphNode   = $response->getGraphNode();
        $responsearr = $graphNode->asArray();

        return $responsearr;
    }

    public function getAllFbPages($token, $subscribedOnly=false)
    {
        $response =  $this->fbconn->get(
            '/me/accounts?fields=id,name,access_token',
            $token
        );
        $graphEdge = $response->getGraphEdge();
        $pagelist  =[];
        foreach ($graphEdge as $graphNode) {
            $responsearr=$graphNode->asArray();
            $page       =[];
            $page[]     =$responsearr['id'].'';
            $page[]     =$responsearr['name'];
            // $page[]=$responsearr['access_token'];
            // $this->subscribeFbPage($fbconn, $page[0], $responsearr['access_token']);
            $page[]=$this->getFbPageSubscriptionStatus($page[0], $responsearr['access_token']);
            if ($subscribedOnly) {
                if ($page[2]) {
                    $pagelist[]=$page;
                }
            } else {
                $pagelist[]=$page;
            }
        }

        return $pagelist;
    }

    public function getFbPageSubscriptionStatus($pageid, $pagetoken)
    {
        try {
            $status   =false;
            $response = $this->fbconn->get(
                '/'.$pageid.'/subscribed_apps',
                $pagetoken
            );
            $decodedresponse=$response->getDecodedBody();
            if (isset($decodedresponse['data'])) {
                $apps=$decodedresponse['data'];
                foreach ($apps as $app) {
                    $appid=$app['id'];
                    if ($appid == $this->FBAPPID) {
                        $status=true;
                        break;
                    }
                }
            }
        } catch (FacebookResponseException $e) {
            $status=false;
        } catch (FacebookSDKException $e) {
            $status=false;
        }

        return $status;
    }

    public function subscribeFbPage($pageid, $pagetoken)
    {
        try {
            $status   =false;
            $response = $this->fbconn->post(
                "/$pageid/subscribed_apps", ['subscribed_fields' => 'leadgen'],
                $pagetoken, null, 'v3.2'
            ); //to subscribe the page
            $decodedresponse=$response->getDecodedBody();
            if (isset($decodedresponse['status'])) {
                $status=$decodedresponse['status'];
            }
        } catch (FacebookResponseException $e) {
            $status=false;
        } catch (FacebookSDKException $e) {
            $status=false;
        }

        return $status;
    }

    public function unsubscribeFbPage($pageid, $pagetoken)
    {
        try {
            $status   =false;
            $response = $this->fbconn->delete(
                "/$pageid/subscribed_apps", ['subscribed_fields' => 'leadgen'],
                $pagetoken, null, 'v3.2'
            ); //to un subscribe the page
            $decodedresponse=$response->getDecodedBody();
            if (isset($decodedresponse['status'])) {
                $status=$decodedresponse['status'];
            }
        } catch (FacebookResponseException $e) {
            $status=false;
        } catch (FacebookSDKException $e) {
            $status=false;
        }

        return $status;
    }

    public function getPageAccessToken($pageid, $token)
    {
        $response = $this->fbconn->get(
            "/$pageid?fields=access_token",
            $token
        );
        $graphNode   = $response->getGraphNode();
        $responsearr = $graphNode->asArray();
        $pagetoken   = $responsearr['access_token'];

        return $pagetoken;
    }

    public function getFbInstance()
    {
        return $this->fbconn;
    }

    public function getOAuthUrlForLeadAds()
    {
        $helper      = $this->fbconn->getRedirectLoginHelper();
        $helper->getPersistentDataHandler()->set('state', $this->factory->getSession()->getId());
        $permissions = ['email', 'manage_pages', 'leads_retrieval'];
        $oauthUrl    = $helper->getLoginUrl($this->OAUTH_CALLBACK, $permissions);

        return $oauthUrl;
    }

    public function getOAuthUrlForCustomAudience()
    {
        $helper      = $this->fbconn->getRedirectLoginHelper();
        $helper->getPersistentDataHandler()->set('state', $this->factory->getSession()->getId());
        $permissions = ['email', 'ads_management'];
        $oauthUrl    = $helper->getLoginUrl($this->OAUTH_CALLBACK, $permissions);

        return $oauthUrl;
    }

    public function getLeadGenFormsByPage($pageid, $pageaccesstoken)
    {
        $response = $this->fbconn->get(
            "/$pageid/leadgen_forms",
            $pageaccesstoken
        );
        $graphEdge = $response->getGraphEdge();
        $formlist  =[];
        foreach ($graphEdge as $graphNode) {
            $responsearr=$graphNode->asArray();
            $form       =[];
            $form[]     =$responsearr['id'].'';
            $form[]     =$responsearr['name'];
            $formlist[] =$form;
        }

        return $formlist;
    }

    public function getAllAdAccounts($token)
    {
        // To list adaccounts
        $response  = $this->fbconn->get('/me/adaccounts?fields=id,account_id,name,account_status,disable_reason', $token, null, 'v3.2');
        $graphEdge = $response->getGraphEdge();
        foreach ($graphEdge as $graphNode) {
            $responsearr = $graphNode->asArray();
            $adaccount   = [];
            foreach ($responsearr as $key => $value) {
                $adaccount[$key] = $value;
            }
            //  $adaccount['audience']=FacebookAdsApiHelper::getFBAudiences($adaccount['id']);
            //ad account status
            //1 = ACTIVE,2 = DISABLED,3 = UNSETTLED,7 = PENDING_RISK_REVIEW,8 = PENDING_SETTLEMENT,9 = IN_GRACE_PERIOD,100 = PENDING_CLOSURE,101 = CLOSED,201 = ANY_ACTIVE,202 = ANY_CLOSED
            if ($adaccount['account_status'] < 3) {
                $adaccounts[] = $adaccount;
            }
        }

        return $adaccounts;
    }

    public function initFBAdsApi($token)
    {
        if (isset($this->fbAdsApi)) {
            return;
        }
        $this->fbAdsApi=FacebookAdsApiHelper::init($this->FBAPPID, $this->FBSECRET, $token);
    }

    public function getAudienceListByAdAccount($adAccount)
    {
        return FacebookAdsApiHelper::getFBAudiences($adAccount);
    }
}
