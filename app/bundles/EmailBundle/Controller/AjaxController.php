<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Controller;

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\CoreBundle\Controller\AjaxLookupControllerTrait;
use Mautic\CoreBundle\Controller\VariantAjaxControllerTrait;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\EmailBundle\Entity\AwsVerifiedEmails;
use Mautic\EmailBundle\Helper\PlainTextHelper;
use Mautic\EmailBundle\Model\EmailModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AjaxController.
 */
class AjaxController extends CommonAjaxController
{
    use VariantAjaxControllerTrait;
    use AjaxLookupControllerTrait;

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function getAbTestFormAction(Request $request)
    {
        return $this->getAbTestForm(
            $request,
            'email',
            'email_abtest_settings',
            'emailform',
            'MauticEmailBundle:AbTest:form.html.php',
            ['MauticEmailBundle:AbTest:form.html.php', 'MauticEmailBundle:FormTheme\Email']
        );
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function sendBatchAction(Request $request)
    {
        $dataArray = ['success' => 0];

        /** @var \Mautic\EmailBundle\Model\EmailModel $model */
        $model    = $this->getModel('email');
        $objectId = $request->request->get('id', 0);
        $pending  = $request->request->get('pending', 0);
        $limit    = $request->request->get('batchlimit', 100);

        if ($objectId && $entity = $model->getEntity($objectId)) {
            $dataArray['success'] = 1;
            $session              = $this->container->get('session');
            $progress             = $session->get('mautic.email.send.progress', [0, (int) $pending]);
            $stats                = $session->get('mautic.email.send.stats', ['sent' => 0, 'failed' => 0, 'failedRecipients' => []]);
            $inProgress           = $session->get('mautic.email.send.active', false);

            if ($pending && !$inProgress && $entity->isPublished()) {
                $session->set('mautic.email.send.active', true);
                list($batchSentCount, $batchFailedCount, $batchFailedRecipients) = $model->sendEmailToLists($entity, null, $limit);

                $progress[0] += ($batchSentCount + $batchFailedCount);
                $stats['sent'] += $batchSentCount;
                $stats['failed'] += $batchFailedCount;

                foreach ($batchFailedRecipients as $list => $emails) {
                    $stats['failedRecipients'] = $stats['failedRecipients'] + $emails;
                }

                $session->set('mautic.email.send.progress', $progress);
                $session->set('mautic.email.send.stats', $stats);
                $session->set('mautic.email.send.active', false);
            }

            $dataArray['percent']  = ($progress[1]) ? ceil(($progress[0] / $progress[1]) * 100) : 100;
            $dataArray['progress'] = $progress;
            $dataArray['stats']    = $stats;
        }

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * Called by parent::getBuilderTokensAction().
     *
     * @param $query
     *
     * @return array
     */
    protected function getBuilderTokens($query)
    {
        /** @var \Mautic\EmailBundle\Model\EmailModel $model */
        $model = $this->getModel('email');

        return $model->getBuilderComponents(null, ['tokens'], $query, false);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function generatePlaintTextAction(Request $request)
    {
        $custom = $request->request->get('custom');
        $id     = $request->request->get('id');

        $parser = new PlainTextHelper(
            [
                'base_url' => $request->getSchemeAndHttpHost().$request->getBasePath(),
            ]
        );

        $dataArray = [
            'text' => $parser->setHtml($custom)->getText(),
        ];

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function getAttachmentsSizeAction(Request $request)
    {
        $assets = $request->get('assets', [], true);
        $size   = 0;
        if ($assets) {
            /** @var \Mautic\AssetBundle\Model\AssetModel $assetModel */
            $assetModel = $this->getModel('asset');
            $size       = $assetModel->getTotalFilesize($assets);
        }

        return $this->sendJsonResponse(['size' => $size]);
    }

    /**
     * Tests monitored email connection settings.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    protected function testMonitoredEmailServerConnectionAction(Request $request)
    {
        $dataArray = ['success' => 0, 'message' => ''];

        if ($this->user->isAdmin()) {
            $settings = $request->request->all();

            if (empty($settings['password'])) {
                $existingMonitoredSettings = $this->coreParametersHelper->getParameter('monitored_email');
                if (is_array($existingMonitoredSettings) && (!empty($existingMonitoredSettings[$settings['mailbox']]['password']))) {
                    $settings['password'] = $existingMonitoredSettings[$settings['mailbox']]['password'];
                }
            }

            /** @var \Mautic\EmailBundle\MonitoredEmail\Mailbox $helper */
            $helper = $this->factory->getHelper('mailbox');

            try {
                $helper->setMailboxSettings($settings, false);
                $folders = $helper->getListingFolders('');
                if (!empty($folders)) {
                    $dataArray['folders'] = '';
                    foreach ($folders as $folder) {
                        $dataArray['folders'] .= "<option value=\"$folder\">$folder</option>\n";
                    }
                }
                $dataArray['success'] = 1;
                $dataArray['message'] = $this->translator->trans('mautic.core.success');
            } catch (\Exception $e) {
                $dataArray['message'] = $e->getMessage();
            }
        }

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * Tests mail transport settings.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    protected function testEmailServerConnectionAction(Request $request)
    {
        $dataArray = ['success' => 0, 'message' => '', 'to_address_empty'=>false];
        $user      = $this->get('mautic.helper.user')->getUser();

        if ($user->isAdmin() || $user->isCustomAdmin()) {
            $settings   = $request->request->all();
            $mailHelper = $this->get('mautic.helper.mailer');
            $dataArray  = $mailHelper->testEmailServerConnection($settings);
        }

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * @param Request $request
     */
    protected function getEmailCountStatsAction(Request $request)
    {
        /** @var EmailModel $model */
        $model = $this->getModel('email');

        $data = [];
        if ($id = $request->get('id')) {
            if ($email = $model->getEntity($id)) {
                $pending     = $model->getPendingLeads($email, null, true);
                $queued      = $model->getQueuedCounts($email);
                $sentCount   =$email->getSentCount(true);
                $failureCount= $email->getFailureCount(true);
                $unsubCount  = $email->getUnsubscribeCount(true);
                $bounceCount =$email->getBounceCount(true);
                $spamCount   =$email->getSpamCount(true);
                $totalCount  = $pending + $sentCount;

                $clickCount = $model->getEmailClickCount($email->getId());
                if ($sentCount > 0 && $totalCount > 0) {
                    $totalSentPec = round($sentCount / $totalCount * 100);
                } else {
                    $totalSentPec = 0;
                }
                if ($failureCount > 0 && $totalCount > 0) {
                    $failurePercentage = round($failureCount / $totalCount * 100, 2);
                } else {
                    $failurePercentage = 0;
                }
                if ($unsubCount > 0 && $totalCount > 0) {
                    $unSubPercentage = round($unsubCount / $sentCount * 100, 2);
                } else {
                    $unSubPercentage = 0;
                }
                if ($bounceCount > 0 && $sentCount > 0) {
                    $bouncePercentage = round($bounceCount / $sentCount * 100, 2);
                } else {
                    $bouncePercentage = 0;
                }
                if ($spamCount > 0 && $sentCount > 0) {
                    $spamPercentage = round($spamCount / $sentCount * 100, 2);
                } else {
                    $spamPercentage = 0;
                }
                if ($clickCount > 0 && $sentCount > 0) {
                    $clickCountPercentage = round($clickCount / $sentCount * 100);
                } else {
                    $clickCountPercentage = 0;
                    $clickCount           =0;
                }

                $data = [
                    'success' => 1,
                    'pending' => 'list' === $email->getEmailType() && $pending ? $this->translator->trans(
                        'mautic.email.stat.leadcount',
                        ['%count%' => $pending]
                    ) : 0,
                    'queued'           => ($queued) ? $this->translator->trans('mautic.email.stat.queued', ['%count%' => $queued]) : 0,
                    'sentCount'        => $this->translator->trans('mautic.email.stat.sentcount', ['%count%' =>$sentCount, '%percentage%'=>$totalSentPec]),
                    'readCount'        => $this->translator->trans('mautic.email.stat.readcount', ['%count%' => $email->getReadCount(true), '%percentage%' => round($email->getReadPercentage(true))]),
                    'readPercent'      => $this->translator->trans('mautic.email.stat.readpercent', ['%count%' => $clickCount, '%percentage%'=>$clickCountPercentage]),
                    'failureCount'     => $this->translator->trans('mautic.email.stat.failurecount', ['%count%' => $failureCount, '%percentage%'=>$failurePercentage]),
                    'unsubscribeCount' => $this->translator->trans('mautic.email.stat.unsubscribecount', ['%count%' =>$unsubCount, '%percentage%'=>$unSubPercentage]),
                    'bounceCount'      => $this->translator->trans('mautic.email.stat.bouncecount', ['%count%' => $bounceCount, '%percentage%' => $bouncePercentage]),
                    'spamCount'        => $this->translator->trans('mautic.email.stat.spamcount', ['%count%' => $spamCount, '%percentage%' => $spamPercentage]),
                ];
            }
        }

        return new JsonResponse($data);
    }

    public function awsEmailFormValidationAction(Request $request)
    {
        $emailId     = InputHelper::clean($request->request->get('email'));
        $dataArray   =[];
        $validator   = $this->container->get('validator');
        $constraints = [
            new \Symfony\Component\Validator\Constraints\Email(),
            new \Symfony\Component\Validator\Constraints\NotBlank(),
        ];
        $error = $validator->validateValue($emailId, $constraints);

        if (count($error) > 0) {
            $errors[]            = $error;
            $dataArray['success']=false;
            $dataArray['message']=$this->translator->trans('mautic.core.email.required');
        } else {
            /** @var \Mautic\EmailBundle\Model\EmailModel $emailModel */
            $emailModel       = $this->factory->getModel('email');
            $emailValidator   = $this->factory->get('mautic.validator.email');
            $getAllEmailIds   = $emailModel->getAllEmailAddress();
            $awsVeridiedIds   =$emailModel->getVerifiedEmailAddress();
            $verifiedemailRepo=$emailModel->getAwsVerifiedEmailsRepository();

            /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
            $configurator     = $this->factory->get('mautic.configurator');
            $params           = $configurator->getParameters();
            $emailuser        = $params['mailer_user'];
            if (isset($params['mailer_amazon_region'])) {
                $region                = $params['mailer_amazon_region'];
            } else {
                $region='';
            }
            //$region           = $params['mailer_amazon_region'];
            $emailpassword    = $params['mailer_password'];
            $emailverifyhelper= $this->factory->get('mautic.validator.email');

            $awsAccountStatus = $emailValidator->getAwsAccountStatus($emailuser, $emailpassword, $region);
            $verifiedEmails   = $emailValidator->getVerifiedEmailList($emailuser, $emailpassword, $region);
            $isValidEmail     = $emailValidator->getVerifiedEmailAddressDetails($emailuser, $emailpassword, $region, $emailId);
            $returnUrl        = $this->generateUrl('mautic_config_action', ['objectAction' => 'edit']);
            /** @var \Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper $routerHelper */
            $awscallbackurl = $this->get('templating.helper.router')->url('mautic_mailer_transport_callback', ['transport' => 'amazon_api']);
            if ($isValidEmail == 'Policy not written') {
                $dataArray['success'] = false;
                $dataArray['message'] = $this->translator->trans('le.email.verification.policy.error');
            }

            $entity = new AwsVerifiedEmails();
            if (!empty($verifiedEmails)) {
                if (in_array($emailId, $verifiedEmails)) {
                    if (!in_array($emailId, $getAllEmailIds)) {
                        $entity->setVerifiedEmails($emailId);
                        $entity->setVerificationStatus('Verified');
                        $verifiedemailRepo->saveEntity($entity);
                        $dataArray['success']  = true;
                        $dataArray['redirect'] = $returnUrl;
                    }
                }
            }

            if (!in_array($emailId, $getAllEmailIds)) {
                if (!$isValidEmail) {
                    $result = $emailverifyhelper->sendVerificationMail($emailuser, $emailpassword, $region, $emailId, $awscallbackurl);
                    if ($result == 'Policy not written') {
                        $dataArray['success'] = false;
                        $dataArray['message'] = $this->translator->trans('le.email.verification.policy.error');
                    } elseif ($result == 'Sns Policy not written') {
                        $dataArray['success'] = false;
                        $dataArray['message'] = $this->translator->trans('le.email.verification.sns.policy.error');
                    } else {
                        $this->addFlash('le.config.aws.email.verification');
                        $dataArray['success']  = true;
                        $dataArray['message']  = $this->translator->trans('le.aws.email.verification');
                        $dataArray['redirect'] = $returnUrl;
                    }
                } else {
                    if (!$awsAccountStatus) {
                        $dataArray['success']  = false;
                        $dataArray['message']  = $this->translator->trans('le.email.verification.inactive.key');
                    } else {
                        $dataArray['success']  = true;
                        $dataArray['message']  = $this->translator->trans('le.aws.email.verification');
                        $dataArray['redirect'] = $returnUrl;
                    }
                }
            } else {
                if (!in_array($emailId, $awsVeridiedIds)) {
                    $dataArray['success'] = false;
                    $dataArray['message'] = $this->translator->trans('le.aws.email.verification.pending');
                } else {
                    $dataArray['success'] = false;
                    $dataArray['message'] = $this->translator->trans('le.aws.email.verification.verified');
                }
            }
        }

        return $this->sendJsonResponse($dataArray);
    }

    public function deleteAwsVerifiedEmailsAction(Request $request)
    {
        $emailModel = $this->factory->getModel('email');
        $email      = $request->request->get('email');
        $emailModel->deleteAwsVerifiedEmails($email);

        $returnUrl = $this->generateUrl('mautic_config_action', ['objectAction' => 'edit']);

        $dataArray['success']  =true;
        $dataArray['redirect'] =$returnUrl;

        return $this->sendJsonResponse($dataArray);
    }

    public function emailstatusAction()
    {
        $config= $this->factory->getRouter()->generate('mautic_config_action', ['objectAction' => 'edit']);
        if (!$this->get('mautic.helper.mailer')->emailstatus(false)) {
            $dataArray['success']       =true;
            $dataArray['info']          = $this->translator->trans('mautic.email.config.mailer.status.app_header', ['%config_url%' =>"<a class='url' href=$config>Click Here</a>"]);
            $dataArray['isalertneeded'] = 'false';
        } else {
            $dataArray['success']       =false;
            $dataArray['info']          = '';
            $dataArray['isalertneeded'] = 'false';
        }

        return $this->sendJsonResponse($dataArray);
    }
}
