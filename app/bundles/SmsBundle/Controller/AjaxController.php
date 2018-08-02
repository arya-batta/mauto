<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SmsBundle\Controller;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\CoreBundle\Controller\AjaxLookupControllerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends CommonAjaxController
{
    use AjaxLookupControllerTrait;

    /**
     * Tests mail transport settings.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    protected function testSmsServerConnectionAction(Request $request)
    {
        $dataArray = ['success' => 0, 'message' => '', 'to_address_empty'=>false];
        $user      = $this->get('mautic.helper.user')->getUser();

        if ($user->isAdmin() || $user->isCustomAdmin()) {
            $settings   = $request->request->all();
            $sendnumber = $user->getMobile();
            $transport  = $settings['transport'];
            $translator = $this->get('translator');
            $transport  = $translator->trans($transport);
            $result     = true;
            $content    = "Hi, \n This is Test Message. \n Team LeadsEngage.";
            $msgcontent = urlencode($content);
            switch ($transport) {
                case 'SolutionInfinity':
                    $result = $this->sendSolutionSMS($settings['url'], $sendnumber, $content, $settings['apiKey'], $settings['senderid']);
                    break;
                case 'Twilio':
                    $result = $this->sendTwilioSMS($sendnumber, $content, $settings['username'], $settings['password'], $settings['fromnumber']);
                    break;
            }
            if ($result == 'success') {
                $dataArray['success'] = 1;
                $dataArray['message'] = $translator->trans('le.send.sms.success', ['%mobile%'=>$sendnumber]);
            } else {
                $dataArray['success'] = 0;
                $dataArray['message'] = $translator->trans('le.send.sms.failed').' '.$result;
            }
        }

        return $this->sendJsonResponse($dataArray);
    }

    public function sendSolutionSMS($url, $number, $content, $username, $senderID)
    {
        if ($url == '' || $number == '' || $username == '' || $senderID == '') {
            return 'URL or Sender ID or Api Key or User number Cannot be Empty';
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

    protected function sendTwilioSMS($number, $content, $username, $password, $fromnumber)
    {
        if ($number === null) {
            return false;
        }
        if ($fromnumber == '' || $username == '' || $password == '') {
            return 'From number or Account SID or Auth Token Cannot be Empty';
        }
        try {
            $client = new \Services_Twilio($password, $username);

            $message = $client->account->messages->sendMessage(
                $fromnumber,
                $this->sanitizeNumber($number),
                $content
            );

            return 'success';
        } catch (\Services_Twilio_RestException $e) {
            return $e->getMessage();
        } catch (NumberParseException $e) {
            return $e->getMessage();
        }
    }
}
