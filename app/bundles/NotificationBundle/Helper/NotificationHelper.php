<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\NotificationBundle\Helper;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Templating\Helper\AssetsHelper;
use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

class NotificationHelper
{
    /**
     * @var MauticFactory
     */
    protected $factory;

    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;

    /**
     * @var CoreParametersHelper
     */
    protected $coreParametersHelper;

    /**
     * @var AssetsHelper
     */
    protected $assetsHelper;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Request
     */
    protected $request;

    protected $usermodel;

    protected $notificationModel;

    /*
     * @var TranslatorInterface
     */
    protected $translator;

    protected $em;

    /**
     * NotificationHelper constructor.
     *
     * @param MauticFactory        $factory
     * @param AssetsHelper         $assetsHelper
     * @param CoreParametersHelper $coreParametersHelper
     * @param IntegrationHelper    $integrationHelper
     * @param Router               $router
     * @param RequestStack         $requestStack
     * @param TranslatorInterface  $translator
     */
    public function __construct(MauticFactory $factory, AssetsHelper $assetsHelper, CoreParametersHelper $coreParametersHelper, IntegrationHelper $integrationHelper, Router $router, RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->factory              = $factory;
        $this->assetsHelper         = $assetsHelper;
        $this->coreParametersHelper = $coreParametersHelper;
        $this->integrationHelper    = $integrationHelper;
        $this->router               = $router;
        $this->request              = $requestStack;
        $this->translator           = $translator;
        $this->em                   = $factory->getEntityManager();
    }

    /**
     * @param string $notification
     *
     * @return bool
     */
    public function unsubscribe($notification)
    {
        /** @var \Mautic\LeadBundle\Entity\LeadRepository $repo */
        $repo = $this->factory->getEntityManager()->getRepository('MauticLeadBundle:Lead');

        $lead = $repo->getLeadByEmail($notification);

        /** @var \Mautic\LeadBundle\Model\LeadModel $leadModel */
        $leadModel = $this->factory->getModel('lead.lead');

        return $leadModel->addDncForLead($lead, 'notification', null, DoNotContact::UNSUBSCRIBED);
    }

    public function getHeaderScript()
    {
        if ($this->hasScript()) {
            return 'MauticJS.insertScript(\'https://cdn.onesignal.com/sdks/OneSignalSDK.js\');
                    var OneSignal = OneSignal || [];';
        }
    }

    public function getScript()
    {
        if ($this->hasScript()) {
            $integration = $this->integrationHelper->getIntegrationObject('OneSignal');

            if (!$integration || $integration->getIntegrationSettings()->getIsPublished() === false) {
                return;
            }

            $settings        = $integration->getIntegrationSettings();
            $keys            = $integration->getDecryptedApiKeys();
            $supported       = $settings->getSupportedFeatures();
            $featureSettings = $settings->getFeatureSettings();

            $appId                      = $keys['app_id'];
            $safariWebId                = $keys['safari_web_id'];
            $welcomenotificationEnabled = in_array('welcome_notification_enabled', $supported);
            $notificationSubdomainName  = $featureSettings['subdomain_name'];
            $leadAssociationUrl         = $this->router->generate(
                'mautic_subscribe_notification',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $welcomenotificationText = '';

            if (!$welcomenotificationEnabled) {
                $welcomenotificationText = 'welcomeNotification: { "disable": true },';
            }

            $server        = $this->request->getCurrentRequest()->server;
            $https         = (parse_url($server->get('HTTP_REFERER'), PHP_URL_SCHEME) == 'https') ? true : false;
            $subdomainName = '';

            if (!$https && $notificationSubdomainName) {
                $subdomainName = 'subdomainName: "'.$notificationSubdomainName.'",
                httpPermissionRequest: {
                    enable: true,
                    useCustomModal: true
                },';
            }

            $oneSignalInit = <<<JS
var scrpt = document.createElement('link');
scrpt.rel ='manifest';
scrpt.href ='/manifest.json';
var head = document.getElementsByTagName('head')[0];
head.appendChild(scrpt);

OneSignal.push(["init", {
    appId: "{$appId}",
    safari_web_id: "{$safariWebId}",
    autoRegister: true,
    {$welcomenotificationText}
    {$subdomainName}
    notifyButton: {
        enable: false // Set to false to hide
    }
}]);

var postUserIdToMautic = function(userId) {
    var data = [];
    data['osid'] = userId;
    MauticJS.makeCORSRequest('GET', '{$leadAssociationUrl}', data);
};

OneSignal.push(function() {
    OneSignal.getUserId(function(userId) {
        if (! userId) {
            OneSignal.on('subscriptionChange', function(isSubscribed) {
                if (isSubscribed) {
                    OneSignal.getUserId(function(newUserId) {
                        postUserIdToMautic(newUserId);
                    });
                }
            });
        } else {
            postUserIdToMautic(userId);
        }
    });
    // Just to be sure we've grabbed the ID
    window.onbeforeunload = function() {
        OneSignal.getUserId(function(userId) {
            if (userId) {
                postUserIdToMautic(userId);
            }
        });
    };
});
JS;

            if (!$https && $notificationSubdomainName) {
                $oneSignalInit .= <<<'JS'
OneSignal.push(function() {
    OneSignal.on('notificationPermissionChange', function(permissionChange) {
        if(currentPermission == 'granted'){
        setTimeout(function(){
            OneSignal.registerForPushNotifications({httpPermissionRequest: true});
        }, 100);
        }
    });
});
JS;
            }

            return $oneSignalInit;
        }
    }

    private function hasScript()
    {
        $landingPage = true;
        $server      = $this->request->getCurrentRequest()->server;
        $cookies     = $this->request->getCurrentRequest()->cookies;
        // already exist
        if ($cookies->get('mtc_osid')) {
            return false;
        }

        if (strpos($server->get('HTTP_REFERER'), $this->coreParametersHelper->getParameter('site_url')) === false) {
            $landingPage = false;
        }

        $integration = $this->integrationHelper->getIntegrationObject('OneSignal');

        if (!$integration || $integration->getIntegrationSettings()->getIsPublished() === false) {
            return false;
        }

        $supportedFeatures = $integration->getIntegrationSettings()->getSupportedFeatures();

        // disable on Landing pages
        if ($landingPage === true && !in_array('landing_page_enabled', $supportedFeatures)) {
            return false;
        }

        // disable on Landing pages
        if ($landingPage === false && !in_array('tracking_page_enabled', $supportedFeatures)) {
            return false;
        }

        return true;
    }

    public function sendNotificationonFailure($isemail = false, $isEnabled = true, $errorMessage = '')
    {
        $configurl     = $this->factory->getRouter()->generate('mautic_config_action', ['objectAction' => 'edit']);
        $messagetext   = 'le.sms.configuration.failure.notification';
        $messageheader = 'le.sms.configuration.failure.header';
        if ($isemail) {
            $messagetext   = 'le.email.send.failed.message';
            $messageheader = 'le.email.send.failed';
        }
       // $message = $this->translator->trans($messagetext);
        $message = $this->translator->trans($messagetext, ['%URL%'=>$configurl]);
        if ($errorMessage != '' && $errorMessage != 'Failed') {
            $message = $errorMessage;
            if(strpos($message, 'Insufficient credits') !== false){
                $message = "le.sms.configuration.failure.solutioninfini";
            }
        }
        $header                  = $this->translator->trans($messageheader);
        $isRead                  = 0;
        $iconClass               = null;
        $this->userModel         = $this->factory->getModel('user.user');
        $this->notificationModel = $this->factory->getModel('core.notification');
        $adminusers              = $this->userModel->getAdminUserList();
        $userid                  = $adminusers[0]['id'];
        $user                    = $this->userModel->getEntity($userid);
        $datetime                = null;
        $type                    = 'error';
        $response                = $this->notificationModel->getNotificationbyHeader($this->translator->trans($messageheader));
        if (!$isEnabled) {
            $isRead = 0;
            if (sizeof($response) > 0) {
                $this->notificationModel->updateNotification($response[0]['id'], $message, $type, $isRead, $header, $iconClass, $datetime, $user);
            } else {
                $this->notificationModel->addNotification($message, $type, $isRead, $header, $iconClass, $datetime, $user);
            }
        } else {
            $isRead = 1;
            if (sizeof($response) > 0) {
                $this->notificationModel->updateNotification($response[0]['id'], $message, $type, $isRead, $header, $iconClass, $datetime, $user);
            }
        }
    }
}
