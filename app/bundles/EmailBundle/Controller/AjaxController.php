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
use Mautic\EmailBundle\Entity\LeadEventLogRepository;
use Mautic\EmailBundle\Entity\LeadRepository;
use Mautic\EmailBundle\Helper\PlainTextHelper;
use Mautic\EmailBundle\Model\DripEmailModel;
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
            $stats                = $session->get('le.email.send.stats', ['sent' => 0, 'failed' => 0, 'failedRecipients' => []]);
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
                $session->set('le.email.send.stats', $stats);
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
            $dataArray  = $mailHelper->testEmailServerConnection($settings, true);
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
                $sentCount   = $email->getSentCount(true);
                $failureCount= $email->getFailureCount(true);
                $unsubCount  = $email->getUnsubscribeCount(true);
                $bounceCount = $email->getBounceCount(true);
                $spamCount   = $email->getSpamCount(true);
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
                        'le.email.stat.leadcount',
                        ['%count%' => $pending]
                    ) : 0,
                    'queued'           => ($queued) ? $this->translator->trans('le.email.stat.queued', ['%count%' => $queued]) : 0,
                    'sentCount'        => $this->translator->trans('le.email.stat.sentcount', ['%count%' =>$sentCount, '%percentage%'=>$totalSentPec]),
                    'readCount'        => $this->translator->trans('le.email.stat.readcount', ['%count%' => $email->getReadCount(true), '%percentage%' => round($email->getReadPercentage(true))]),
                    'readPercent'      => $this->translator->trans('le.email.stat.readpercent', ['%count%' => $clickCount, '%percentage%'=>$clickCountPercentage]),
                    'failureCount'     => $this->translator->trans('le.email.stat.failurecount', ['%count%' => $failureCount, '%percentage%'=>$failurePercentage]),
                    'unsubscribeCount' => $this->translator->trans('le.email.stat.unsubscribecount', ['%count%' =>$unsubCount, '%percentage%'=>$unSubPercentage]),
                    'bounceCount'      => $this->translator->trans('le.email.stat.bouncecount', ['%count%' => $bounceCount, '%percentage%' => $bouncePercentage]),
                    'spamCount'        => $this->translator->trans('le.email.stat.spamcount', ['%count%' => $spamCount, '%percentage%' => $spamPercentage]),
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
            $dataArray['message']=$this->translator->trans('le.core.email.required');
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
        $configurl= $this->factory->getRouter()->generate('mautic_config_action', ['objectAction' => 'edit']);
        if (!$this->get('mautic.helper.mailer')->emailstatus(false)) {
            $dataArray['success']       = true;
            $dataArray['info']          = $this->translator->trans('le.email.config.mailer.status.app_header', ['%url%'=>$configurl]);
            $dataArray['isalertneeded'] = 'false';
        } else {
            $dataArray['success']       = false;
            $dataArray['info']          = '';
            $dataArray['isalertneeded'] = 'false';
        }

        return $this->sendJsonResponse($dataArray);
    }

    public function createDripEmailsAction(Request $request)
    {
        $data = $request->request->all();

        /** @var \Mautic\UserBundle\Model\UserModel $usermodel */
        $usermodel     = $this->getModel('user.user');
        $userentity    = $usermodel->getCurrentUserEntity();

        /** @var DripEmailModel $dripModel */
        $dripModel     = $this->getModel('email.dripemail');

        $subject      = $data['subject'];
        $previewText  = $data['previewText'];
        $customHtml   = $data['customHtml'];
        $beeJson      = $data['beeJson'];
        $dripEmail    = $data['dripEntity'];

        $dripEntity    = $dripModel->getEntity($dripEmail);

        /** @var EmailModel $emailmodel */
        $emailmodel = $this->getModel('email');

        /** @var \Mautic\EmailBundle\Entity\Email $emailentity */
        $emailentity = $emailmodel->getEntity();

        $emailentity->setName('DripEmail - '.$subject);
        $emailentity->setSubject($subject);
        $emailentity->setPreviewText($previewText);
        $emailentity->setCustomHtml($customHtml);
        $emailentity->setBeeJSON($beeJson);
        $emailentity->setDripEmail($dripEntity);
        $emailentity->setCreatedBy($userentity);
        $emailentity->setIsPublished(true);
        $emailentity->setGoogleTags(true);
        $emailentity->setEmailType('dripemail');

        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator= $this->get('mautic.configurator');

        $params          = $configurator->getParameters();
        $fromname        = $params['mailer_from_name'];
        $fromadress      = $params['mailer_from_email'];
        $emailentity->setFromName($fromname);
        $emailentity->setFromAddress($fromadress);

        $totalitems = $emailmodel->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'e.dripEmail',
                            'expr'   => 'eq',
                            'value'  => $dripEntity,
                        ],
                    ],
                ],
                'orderBy'          => 'e.dripEmailOrder',
                'orderByDir'       => 'asc',
                'ignore_paginator' => true,
            ]
        );
        $emailentity->setDripEmailOrder(sizeof($totalitems) + 1);
        $scheduleTime = '0 days';
        if (sizeof($totalitems) > 0) {
            $scheduleTime = '1 days';
        }
        $emailentity->setScheduleTime($scheduleTime);
        $emailmodel->saveEntity($emailentity);

        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'email:emails:viewown',
                'email:emails:viewother',
                'email:emails:create',
                'email:emails:editown',
                'email:emails:editother',
                'email:emails:deleteown',
                'email:emails:deleteother',
                'email:emails:publishown',
                'email:emails:publishother',
            ],
            'RETURN_ARRAY'
        );

        $items = $emailmodel->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'e.dripEmail',
                            'expr'   => 'eq',
                            'value'  => $dripEntity,
                        ],
                    ],
                ],
                'orderBy'          => 'e.dripEmailOrder',
                'orderByDir'       => 'asc',
                'ignore_paginator' => true,
            ]
        );
        $htmlContent = $this->render('MauticEmailBundle:DripEmail:emaillist.html.php', [
            'items'             => $items,
            'permissions'       => $permissions,
            'actionRoute'       => 'le_dripemail_campaign_action',
            'translationBase'   => 'mautic.email.broadcast',
            'entity'            => $dripEntity,
        ]);
        $htmlContent = strstr($htmlContent, '<div class="table-responsive">');

        $responseArr             = [];
        $responseArr['success']  = true;
        $responseArr['content']  = $htmlContent;

        return $this->sendJsonResponse($responseArr);
    }

    public function deleteDripEmailsAction(Request $request)
    {
        $data = $request->request->all();

        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'email:emails:viewown',
                'email:emails:viewother',
                'email:emails:create',
                'email:emails:editown',
                'email:emails:editother',
                'email:emails:deleteown',
                'email:emails:deleteother',
                'email:emails:publishown',
                'email:emails:publishother',
            ],
            'RETURN_ARRAY'
        );

        $emailId      = $data['emailId'];
        $dripEmail    = $data['dripEmail'];

        /** @var DripEmailModel $dripModel */
        $dripModel     = $this->getModel('email.dripemail');
        $dripEntity    = $dripModel->getEntity($dripEmail);
        /** @var EmailModel $emailmodel */
        $emailmodel = $this->getModel('email');
        $entity     = $emailmodel->getEntity($emailId);
        /** @var LeadEventLogRepository $leadEventLog */
        $leadEventLog  = $this->get('mautic.email.repository.leadEventLog');
        $leadEvents    = $leadEventLog->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'dle.email',
                            'expr'   => 'eq',
                            'value'  => $entity,
                        ],
                    ],
                ],
                'ignore_paginator' => true,
            ]
        );
        foreach ($leadEvents as $event) {
            $leadEventLog->deleteEntity($event);
        }
        $emailmodel->deleteEntity($entity);

        $items = $emailmodel->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'e.dripEmail',
                            'expr'   => 'eq',
                            'value'  => $dripEntity,
                        ],
                    ],
                ],
                'orderBy'          => 'e.dripEmailOrder',
                'orderByDir'       => 'asc',
                'ignore_paginator' => true,
            ]
        );
        $emailOrder = 0;
        foreach ($items as $item) {
            $item->setDripEmailOrder($emailOrder + 1);
            $emailmodel->saveEntity($item);
        }

        $items = $emailmodel->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'e.dripEmail',
                            'expr'   => 'eq',
                            'value'  => $dripEntity,
                        ],
                    ],
                ],
                'orderBy'          => 'e.dripEmailOrder',
                'orderByDir'       => 'asc',
                'ignore_paginator' => true,
            ]
        );

        $htmlContent = $this->render('MauticEmailBundle:DripEmail:emaillist.html.php', [
            'items'             => $items,
            'permissions'       => $permissions,
            'actionRoute'       => 'le_dripemail_campaign_action',
            'translationBase'   => 'mautic.email.broadcast',
            'entity'            => $dripEntity,
        ]);

        $htmlContent = strstr($htmlContent, '<div class="table-responsive">');

        $responseArr             = [];
        $responseArr['success']  = true;
        $responseArr['content']  = $htmlContent;

        return $this->sendJsonResponse($responseArr);
    }

    public function updateDripEmailFrequencyAction(Request $request)
    {
        $data = $request->request->all();

        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'email:emails:viewown',
                'email:emails:viewother',
                'email:emails:create',
                'email:emails:editown',
                'email:emails:editother',
                'email:emails:deleteown',
                'email:emails:deleteother',
                'email:emails:publishown',
                'email:emails:publishother',
            ],
            'RETURN_ARRAY'
        );

        $frequencyUnit   = $data['frequencyUnit'];
        $frequencyValue  = $data['frequencyValue'];
        $emailId         = $data['EmailId'];
        $scheduleTime    = $frequencyValue.' '.$frequencyUnit;

        /** @var EmailModel $emailmodel */
        $emailmodel = $this->getModel('email');
        $entity     = $emailmodel->getEntity($emailId);
        $entity->setScheduleTime($scheduleTime);
        $emailmodel->saveEntity($entity);

        /** @var DripEmailModel $dripModel */
        $dripModel     = $this->getModel('email.dripemail');

        $dripEntity    = $dripModel->getEntity($entity->getDripEmail()->getId());

        $items = $emailmodel->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'e.dripEmail',
                            'expr'   => 'eq',
                            'value'  => $dripEntity,
                        ],
                    ],
                ],
                'orderBy'          => 'e.dripEmailOrder',
                'orderByDir'       => 'asc',
                'ignore_paginator' => true,
            ]
        );

        $htmlContent = $this->render('MauticEmailBundle:DripEmail:emaillist.html.php', [
            'items'             => $items,
            'permissions'       => $permissions,
            'actionRoute'       => 'le_dripemail_campaign_action',
            'translationBase'   => 'mautic.email.broadcast',
            'entity'            => $dripEntity,
        ]);

        $htmlContent = strstr($htmlContent, '<div class="table-responsive">');

        $responseArr             = [];
        $responseArr['success']  = true;
        $responseArr['content']  = $htmlContent;

        return $this->sendJsonResponse($responseArr);
    }

    /**
     * @param Request $request
     */
    protected function getDripEmailScheduledCountStatsAction(Request $request)
    {
        /** @var EmailModel $model */
        $emailmodel = $this->getModel('email');

        /** @var DripEmailModel $model */
        $model = $this->getModel('email.dripemail');

        $data = [];
        if ($id = $request->get('id')) {
            if ($email = $emailmodel->getEntity($id)) {
                $eventLogRepo  = $model->getCampaignLeadEventRepository();
                $events        = $eventLogRepo->getScheduledEventsbyDripEmail($email);
                $scheduledlead = sizeof($events);
                $data          = [
                    'success'        => 1,
                    'scheduledcount' => $this->translator->trans('le.drip.email.stat.scheduledcount', ['%count%' =>$scheduledlead]),
                ];
            }
        }

        return new JsonResponse($data);
    }

    /**
     * @param Request $request
     */
    protected function getDripEmailStatsAction(Request $request)
    {
        /** @var DripEmailModel $model */
        $model = $this->getModel('email.dripemail');

        /** @var EmailModel $model */
        $emailmodel = $this->getModel('email');

        /** @var LeadRepository $leadRepo */
        $leadRepo   = $this->factory->get('mautic.email.repository.lead');

        $data = [];
        if ($id = $request->get('id')) {
            if ($dripemail = $model->getEntity($id)) {
                $emailEntities = $emailmodel->getEntities(
                    [
                        'filter'           => [
                            'force' => [
                                [
                                    'column' => 'e.dripEmail',
                                    'expr'   => 'eq',
                                    'value'  => $dripemail,
                                ],
                            ],
                        ],
                        'orderBy'          => 'e.dripEmailOrder',
                        'orderByDir'       => 'asc',
                        'ignore_paginator' => true,
                    ]
                );
                $leads = $leadRepo->getEntities(
                    [
                        'filter'           => [
                            'force' => [
                                [
                                    'column' => 'le.campaign',
                                    'expr'   => 'eq',
                                    'value'  => $dripemail,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
                $dripSentCount  = 0;
                $dripReadCount  = 0;
                $dripClickCount = 0;
                $dripUnsubCount = 0;
                foreach ($emailEntities as $email) {
                    $sentCount       = $email->getSentCount(true);
                    $readCount       = $email->getReadCount(true);
                    $clickCount      = $emailmodel->getEmailClickCount($email->getId());
                    $unsubCount      = $email->getUnsubscribeCount(true);
                    $dripSentCount += $sentCount;
                    $dripReadCount += $readCount;
                    $dripClickCount += $clickCount;
                    $dripUnsubCount += $unsubCount;
                }
                $dripclickCountPercentage  = 0;
                $dripreadCountPercentage   = 0;
                $dripunsubsCountPercentage = 0;
                if ($dripClickCount > 0 && $dripSentCount > 0) {
                    $dripclickCountPercentage  = round($dripClickCount / $dripSentCount * 100);
                }
                if ($dripReadCount > 0 && $dripSentCount > 0) {
                    $dripreadCountPercentage   = round($dripReadCount / $dripSentCount * 100);
                }
                if ($dripUnsubCount > 0 && $dripSentCount > 0) {
                    $dripunsubsCountPercentage = round($dripUnsubCount / $dripSentCount * 100);
                }
                $data = [
                    'success'     => 1,
                    'sentcount'   => $this->translator->trans('le.drip.email.stat.sentcount', ['%count%'  =>$dripSentCount]),
                    'readcount'   => $this->translator->trans('le.drip.email.stat.opencount', ['%count%'  =>$dripReadCount, '%percentage%'  => $dripreadCountPercentage]),
                    'clickcount'  => $this->translator->trans('le.drip.email.stat.clickcount', ['%count%' =>$dripClickCount, '%percentage%' => $dripclickCountPercentage]),
                    'unsubscribe' => $this->translator->trans('le.drip.email.stat.unsubscribe', ['%count%' =>$dripUnsubCount, '%percentage%' => $dripunsubsCountPercentage]),
                    'leadcount'   => $this->translator->trans('le.drip.email.stat.leadcount', ['%count%'  => sizeof($leads)]),
                ];
            }
        }

        return new JsonResponse($data);
    }

    public function updateDripEmailsAction(Request $request)
    {
        $data = $request->request->all();

        /** @var DripEmailModel $dripModel */
        $dripModel     = $this->getModel('email.dripemail');

        $subject      = $data['subject'];
        $previewText  = $data['previewText'];
        $customHtml   = $data['customHtml'];
        $Emailid      = $data['Emailid'];

        /** @var EmailModel $emailmodel */
        $emailmodel = $this->getModel('email');

        /** @var \Mautic\EmailBundle\Entity\Email $emailentity */
        $emailentity = $emailmodel->getEntity($Emailid);

        $emailentity->setSubject($subject);
        $emailentity->setPreviewText($previewText);
        $emailentity->setCustomHtml($customHtml);
        $dripEntity = $emailentity->getDripEmail();
        $emailmodel->saveEntity($emailentity);

        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'email:emails:viewown',
                'email:emails:viewother',
                'email:emails:create',
                'email:emails:editown',
                'email:emails:editother',
                'email:emails:deleteown',
                'email:emails:deleteother',
                'email:emails:publishown',
                'email:emails:publishother',
            ],
            'RETURN_ARRAY'
        );

        $items = $emailmodel->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'e.dripEmail',
                            'expr'   => 'eq',
                            'value'  => $dripEntity,
                        ],
                    ],
                ],
                'orderBy'          => 'e.dripEmailOrder',
                'orderByDir'       => 'asc',
                'ignore_paginator' => true,
            ]
        );
        $htmlContent = $this->render('MauticEmailBundle:DripEmail:emaillist.html.php', [
            'items'             => $items,
            'permissions'       => $permissions,
            'actionRoute'       => 'le_dripemail_campaign_action',
            'translationBase'   => 'mautic.email.broadcast',
            'entity'            => $dripEntity,
        ]);
        $htmlContent = strstr($htmlContent, '<div class="table-responsive">');

        $responseArr             = [];
        $responseArr['success']  = true;
        $responseArr['content']  = $htmlContent;

        return $this->sendJsonResponse($responseArr);
    }

    public function getEmailDetailsAction(Request $request)
    {
        $data     = $request->request->all();
        $Emailid  = $data['Emailid'];
        /** @var EmailModel $emailmodel */
        $emailmodel = $this->getModel('email');

        /** @var \Mautic\EmailBundle\Entity\Email $emailentity */
        $emailentity = $emailmodel->getEntity($Emailid);
        if (!empty($emailentity)) {
            $responseArr                 = [];
            $responseArr['success']      = true;
            $responseArr['subject']      = $emailentity->getSubject();
            $responseArr['emailcontent'] = $emailentity->getCustomHtml();
            $responseArr['beeJSON']      = $emailentity->getBeeJSON();
            $responseArr['preview']      = $emailentity->getPreviewText();
            $responseArr['Emailid']      = $emailentity->getId();
            $responseArr['isBeeEditor']  = empty($emailentity->getBeeJSON()) ? false : true;
        } else {
            $responseArr             = [];
            $responseArr['success']  = false;
        }

        return $this->sendJsonResponse($responseArr);
    }

    public function createBlueprintEmailsAction(Request $request)
    {
        $data        = $request->request->all();
        $dripId      = $data['dripId'];
        $currentId   = $data['currentId'];
        $driprepo    = $this->get('le.core.repository.signup');

        /** @var DripEmailModel $dripmodel */
        $dripmodel = $this->getModel('email.dripemail');
        $dripemail = $dripmodel->getEntity($currentId);
        $items     = $driprepo->getEmailsByDripId($dripId);
        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'email:emails:viewown',
                'email:emails:viewother',
                'email:emails:create',
                'email:emails:editown',
                'email:emails:editother',
                'email:emails:deleteown',
                'email:emails:deleteother',
                'email:emails:publishown',
                'email:emails:publishother',
            ],
            'RETURN_ARRAY'
        );

        /** @var EmailModel $emailmodel */
        $emailmodel = $this->getModel('email');
        /** @var \Mautic\UserBundle\Model\UserModel $usermodel */
        $usermodel     = $this->getModel('user.user');
        $userentity    = $usermodel->getCurrentUserEntity();

        $dripOrder = 0;
        foreach ($items as $item) {
            $dripOrder = $dripOrder + 1;
            $newEntity = $emailmodel->getEntity();
            //file_put_contents("/var/www/log.txt",$item->getName()."\n",FILE_APPEND);
            $newEntity->setName($item['name']);
            $newEntity->setSubject($item['subject']);
            $newEntity->setPreviewText($item['preview_text']);
            $newEntity->setCustomHtml($item['custom_html']);
            $newEntity->setBeeJSON($item['bee_json']);
            $newEntity->setDripEmail($dripemail);
            $newEntity->setCreatedBy($userentity);
            $newEntity->setIsPublished(true);
            $newEntity->setGoogleTags(true);
            $newEntity->setEmailType('dripemail');
            /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
            $configurator= $this->get('mautic.configurator');

            $params          = $configurator->getParameters();
            $fromname        = $params['mailer_from_name'];
            $fromadress      = $params['mailer_from_email'];
            $newEntity->setFromName($fromname);
            $newEntity->setFromAddress($fromadress);
            $newEntity->setDripEmailOrder($dripOrder);
            $newEntity->setScheduleTime($item['scheduleTime']);
            $emailmodel->saveEntity($newEntity);
        }
        $items = $emailmodel->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'e.dripEmail',
                            'expr'   => 'eq',
                            'value'  => $dripemail,
                        ],
                    ],
                ],
                'orderBy'          => 'e.dripEmailOrder',
                'orderByDir'       => 'asc',
                'ignore_paginator' => true,
            ]
        );
        $htmlContent = $this->render('MauticEmailBundle:DripEmail:emaillist.html.php', [
            'items'             => $items,
            'permissions'       => $permissions,
            'actionRoute'       => 'le_dripemail_campaign_action',
            'translationBase'   => 'mautic.email.broadcast',
            'entity'            => $dripemail,
        ]);
        $htmlContent = strstr($htmlContent, '<div class="table-responsive">');

        $responseArr             = [];
        $responseArr['success']  = true;
        $responseArr['content']  = $htmlContent;

        return $this->sendJsonResponse($responseArr);
    }

    public function reorderEmailsAction(Request $request)
    {
        $data     = $request->request->all();
        $emailId  = $data['id'];
        $order    = $data['order'];

        /** @var EmailModel $emailmodel */
        $emailmodel = $this->getModel('email');
        $entity     = $emailmodel->getEntity($emailId);
        $entity->setDripEmailOrder($order);
        $emailmodel->saveEntity($entity);

        $responseArr             = [];
        $responseArr['success']  = true;

        return $this->sendJsonResponse($responseArr);
    }
}
