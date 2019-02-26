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
            $smsHelper  = $this->get('mautic.helper.sms');
            $dataArray  = $smsHelper->testSmsServerConnection($settings, true);
        }

        return $this->sendJsonResponse($dataArray);
    }

    public function smsstatusAction()
    {
        $isClosed                     = $this->factory->get('session')->get('isalert_needed');
        if ($this->factory->getParameter('publish_account')) {
            if (!$this->get('mautic.helper.sms')->getSmsTransportStatus(false)) {
                $configurl                  =$this->factory->getRouter()->generate('le_config_action', ['objectAction' => 'edit']);
                $dataArray['success']       =true;
                $infotext                   ='mautic.sms.appheader.status.fail';
                $dataArray['info']          = $this->translator->trans($infotext, ['%url%'=>$configurl]);
                $dataArray['isalertneeded'] = $isClosed;
            } else {
                $dataArray['success']       =false;
                $dataArray['info']          = '';
                $dataArray['isalertneeded'] = $isClosed;
            }
        } else {
            $dataArray['success']       =false;
            $dataArray['info']          = '';
            $dataArray['isalertneeded'] = $isClosed;
        }

        return $this->sendJsonResponse($dataArray);
    }
}
