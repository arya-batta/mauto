<?php

namespace Mautic\PluginBundle\Helper;

use Mautic\CoreBundle\Factory\MauticFactory;

class SlackHelper
{
    /**
     * @var MauticFactory
     */
    protected $factory;

    protected $CLIENT_ID       = '';
    protected $CLIENT_SECRET   = '';

    public function __construct(MauticFactory $factory)
    {
        $this->factory              = $factory;

        $this->CLIENT_ID       = $this->factory->getParameter('slack_client_id');
        $this->CLIENT_SECRET   = $this->factory->getParameter('slack_client_secret');
    }

    public function getOAuthUrl()
    {
        $state = $this->factory->getSession()->getId();
        $url   = 'https://slack.com/oauth/authorize?client_id='.$this->CLIENT_ID.'&scope=incoming-webhook,channels:history,chat:write:user,team:read&state='.$state;

        $oauthUrl    = $url;

        return $oauthUrl;
    }

    public function getAccountDetails($token)
    {
        $url  = 'https://slack.com/api/team.info?token='.$token;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $result                 = curl_exec($curl);
        $response               = json_decode($result);
        $accountDetails         = [];
        $accountDetails['id']   = $response->team->id;
        $accountDetails['name'] = $response->team->name;

        return $accountDetails;
    }

    public function getChannelList($token)
    {
        $url  = 'https://slack.com/api/channels.list?token='.$token;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $result      = curl_exec($curl);
        $response    = json_decode($result);
        $channels    = $response->channels;
        $channelList = [];
        foreach ($channels as $channel) {
            $channelList[$channel->id] = $channel->name;
        }

        return $channelList;
    }

    public function sendSlackMessage($token, $content, $channel)
    {
        $posturl    = "https://slack.com/api/chat.postMessage?token=$token&channel=$channel";
        $attachment = [
            [
                'color' => '#3292e0',
                'text'  => $content,
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

    public function sendInternalSlackMessage($state, $reason)
    {
        $token     = $this->factory->getParameter('slack_internal_token');
        $channel   = $this->factory->getParameter('slack_internal_channel');
        $domainurl = $this->factory->getParameter('site_url');
        $posturl   = "https://slack.com/api/chat.postMessage?token=$token&channel=$channel";
        $content   = "Customer Enter into: \n $state";
        if ($reason != '') {
            $content .= "\n Reason is \n".$reason;
        }
        $attachment = [
            [
                'color'      => '#3292e0',
                'title'      => 'Domain Access',
                'title_link' => $domainurl,
                'text'       => $content,
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
}
