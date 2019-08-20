<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\ApiBundle\Controller;

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AjaxController.
 */
class AjaxController extends CommonAjaxController
{
    protected function regenerateApiAction(Request $request)
    {
        $dataArray = ['success' => 0, 'message' => '', 'apikey'=>false];
        $user      = $this->get('mautic.helper.user')->getUser();
        /** @var \Mautic\UserBundle\Model\UserModel $model */
        $model     = $this->getModel('user.user');
        if ($user instanceof User) {
            $currentdateTime = (new \DateTime())->format('Y-m-d H:i:s');
            $appid           = $this->coreParametersHelper->getParameter('db_name');
            $apiKey          = md5(uniqid(rand(1, 99), true));
            $user->setApiKey($apiKey);
            $model->saveEntity($user);
            $dataArray['apikey']  = $apiKey;
            $dataArray['success'] = 1;
        } else {
            $dataArray['message'] = $this->translator->trans('le.api.regenerate.failed');
        }

        return $this->sendJsonResponse($dataArray);
    }
}
