<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\Controller;

use Mautic\CoreBundle\Controller\FormController;
use Mautic\PluginBundle\Entity\Integration;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class IntegrationController.
 */
class IntegrationController extends FormController
{
    /**
     * @return JsonResponse|Response
     */
    public function indexAction()
    {
        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'list') : 'list';
        /** @var \Mautic\PluginBundle\Helper\IntegrationHelper $integrationHelper */
        $integrationHelper = $this->factory->getHelper('integration');

        return $this->delegateView(
            [
                'viewParameters' => [
                    'integrations'        => $integrationHelper->getIntegrationDetails(),
                    'tmpl'                => $tmpl,
                ],
                'contentTemplate' => 'MauticPluginBundle:Integrations:index.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#le_integrations_index',
                    'leContent'     => 'integration',
                    'route'         => $this->generateUrl('le_integrations_index'),
                ],
            ]
        );
    }

    /**
     * @param $name
     */
    public function configAction($name)
    {
        /** @var \Mautic\PluginBundle\Helper\IntegrationHelper $integrationHelper */
        $integrationHelper = $this->factory->getHelper('integration');
        $session           = $this->get('session');
        $error             =$session->get('le.integration.postauth.error', false);
        $token             =$session->get('le.integration.oauth.token', false);
        $integrationrepo   =$integrationHelper->getIntegrationRepository();
        if ($error) {
            $this->addFlash($error);
            $session->remove('le.integration.postauth.error');
        }
        if ($name == 'facebook_lead_ads' || $name == 'facebook_custom_audiences') {
            $fbapiHelper       = $this->factory->getHelper('fbapi');
        }
        $details                 =$integrationHelper->getIntegrationDetails($name);
        $details['authorization']=false;
        $integrationsettings     =[];
        if ($name == 'facebook_lead_ads') {
            $pagelist=[];
            if ($token) {
                $details['authorization']          =true;
                $responsearr                       = $fbapiHelper->getAccountDetails($token);
                $accountid                         = $responsearr['id'];
                $accountname                       = $responsearr['name'];
                $integrationsettings['accountid']  =$accountid;
                $integrationsettings['accountname']=$accountname;
                $integrationsettings['authtoken']  =$token;
                $pagelist                          =$fbapiHelper->getAllFbPages($token);
                $integrationentity                 =$integrationHelper->getIntegrationInfobyName($name);
                $integrationentity->setApiKeys($integrationsettings);
                $integrationrepo->saveEntity($integrationentity);
                $session->remove('le.integration.oauth.token');
            } else {
                $integrationsettings=$integrationHelper->getIntegrationSettingsbyName($name);
                if (sizeof($integrationsettings) > 0) {
                    $details['authorization']=true;
                    $pagelist                =$fbapiHelper->getAllFbPages($integrationsettings['authtoken']);
                    // $fbapiHelper->initFBAdsApi($integrationsettings['authtoken']);
                   // $details['leads']=$fbapiHelper->getLeadDetailsByID("380228036154078");
                }
            }
            $details['pages']=$pagelist;
        } elseif ($name == 'facebook_custom_audiences') {
            if ($token) {
                $details['authorization']          =true;
                $responsearr                       = $fbapiHelper->getAccountDetails($token);
                $accountid                         = $responsearr['id'];
                $accountname                       = $responsearr['name'];
                $integrationsettings['accountid']  =$accountid;
                $integrationsettings['accountname']=$accountname;
                $integrationsettings['authtoken']  =$token;
                $integrationrepo                   =$integrationHelper->getIntegrationRepository();
                $integrationentity                 =$integrationHelper->getIntegrationInfobyName($name);
                $integrationentity->setApiKeys($integrationsettings);
                $integrationrepo->saveEntity($integrationentity);
                $session->remove('le.integration.oauth.token');
            } else {
                $integrationsettings=$integrationHelper->getIntegrationSettingsbyName($name);
                if (sizeof($integrationsettings) > 0) {
                    $details['authorization']=true;
                }
            }
        } elseif ($name == 'calendly') {
            $integrationsettings=$integrationHelper->getIntegrationSettingsbyName($name);
            if (sizeof($integrationsettings) > 0) {
                $details['calendlytoken']=$integrationsettings['authtoken'];
            }
        } elseif ($name == 'slack') {
            if ($token) {
                $slackHelper                       = $this->factory->getHelper('slack');
                $details['authorization']          = true;
                $config_url                        = $session->get('le.integration.slack.config.url', false);
                $responsearr                       = $slackHelper->getAccountDetails($token);
                $accountid                         = $responsearr['id'];
                $accountname                       = $responsearr['name'];
                $integrationsettings['accountid']  =$accountid;
                $integrationsettings['accountname']=$accountname;
                $integrationsettings['authtoken']  =$token;
                $integrationsettings['configurl']  =$config_url;
                $integrationrepo                   =$integrationHelper->getIntegrationRepository();
                $integrationentity                 =$integrationHelper->getIntegrationInfobyName($name);
                $integrationentity->setName($name);
                $integrationentity->setApiKeys($integrationsettings);
                $integrationrepo->saveEntity($integrationentity);
                $session->remove('le.integration.oauth.token');
            } else {
                $integrationsettings=$integrationHelper->getIntegrationSettingsbyName($name);
                if (sizeof($integrationsettings) > 0) {
                    $details['authorization']=true;
                    $details['configurl']    =$integrationsettings['configurl'];
                }
            }
        }
        $details=array_merge($details, $integrationsettings);
        unset($details['authtoken']);
        $fieldMapping     =[];
        $integrationEntity=$integrationHelper->getIntegrationInfobyName($name);
        if (!empty($integrationEntity)) {
            $fieldMapping=$integrationEntity->getFeatureSettings();
        }
        $form = $this->createForm(
            'integration_field_mapping',
            $fieldMapping,
            ['action'             => $this->generateUrl('le_integrations_config', ['name' => $name]), 'integration'=>$name]
        );
        if ($this->request->getMethod() == 'POST') {
            if (!$this->isFormCancelled($form)) {
                if ($this->isFormValid($form)) {
                    $fieldMapping     = $this->request->request->get('integration_field_mapping');
                    $integrationEntity=$integrationHelper->getIntegrationInfobyName($name);
                    $integrationEntity->setFeatureSettings($fieldMapping);
                    $integrationrepo->saveEntity($integrationEntity);
                    $form = $this->createForm(
                        'integration_field_mapping',
                        $fieldMapping,
                        ['action'             => $this->generateUrl('le_integrations_config', ['name' => $name]), 'integration'=>$name]
                    );
                }
            }
        }
        $template    ='MauticPluginBundle:Integrations\Pages:'.$name.'.html.php';
        $pluginModel = $this->getModel('plugin');
        $fieldList   =$pluginModel->getFieldModel()->getFieldListWithProperties('lead', true);

        return $this->delegateView(
            [
                'viewParameters' => [
                    'fields'         => $fieldList,
                    'payloads'       => $integrationEntity->getPayLoad(200),
                    'details'        => $details,
                    'tmpl'           => 'config',
                    'name'           => $name,
                    'form'           => $this->setFormTheme($form, $template, ['MauticPluginBundle:FormTheme\FieldMapping']),
                ],
                'contentTemplate' => $template,
                'passthroughVars' => [
                    'activeLink'    => '#le_integrations_config',
                    'leContent'     => 'integrationConfig',
                    'route'         => $this->generateUrl('le_integrations_config', ['name'=>$name]),
                ],
            ]
        );
    }

    public function fbPageSubscriptionAction($integration, $pageid, $action)
    {
        $integrationHelper  = $this->factory->getHelper('integration');
        $fbapiHelper        = $this->factory->getHelper('fbapi');
        $subscriptionrepo   =$this->factory->get('le.core.repository.subscription');
        $integrationsettings=$integrationHelper->getIntegrationSettingsbyName($integration);
        if (isset($integrationsettings['authtoken'])) {
            $callbackurl=$this->generateUrl('le_integration_auth_webhook_callback', ['integration'=>$integration], 0);
            $pagetoken  =$fbapiHelper->getPageAccessToken($pageid, $integrationsettings['authtoken']);
            if ($action == 'subscribe') {
                $status=$fbapiHelper->subscribeFbPage($pageid, $pagetoken);
                $subscriptionrepo->subscribeFbLeadGenWebHook($pageid, $callbackurl);
            } elseif ($action == 'unsubscribe') {
                $status=$fbapiHelper->unsubscribeFbPage($pageid, $pagetoken);
                $subscriptionrepo->unSubscribeFbLeadGenWebHook($pageid, $callbackurl);
            }
        }

        return $this->configAction($integration);
    }

    public function accountRemoveAction($name)
    {
        $integrationHelper = $this->factory->getHelper('integration');
        $integrationrepo   =$integrationHelper->getIntegrationRepository();
        $integrations      =$integrationrepo->findBy(
            [
                'name' => $name,
            ]
        );
        if (sizeof($integrations) > 0) {
            $integrationrepo->deleteEntity($integrations[0]);
        }

        return $this->configAction($name);
    }

    /**
     * @param $integration
     *
     * @return RedirectResponse
     */
    public function authUserAction($integration)
    {
        $oauthUrl='';
        $session = $this->get('session');
        $session->set('le.integration.oauth.referer.url', $this->generateUrl('le_integrations_config', ['name'=>$integration], 0));
        $fbapiHelper = $this->factory->getHelper('fbapi');
        if ($integration == 'facebook_lead_ads') {
            $oauthUrl = $fbapiHelper->getOAuthUrlForLeadAds();
        } elseif ($integration == 'facebook_custom_audiences') {
            $oauthUrl = $fbapiHelper->getOAuthUrlForCustomAudience();
        } elseif ($integration == 'slack') {
            $slackHelper = $this->factory->getHelper('slack');
            $oauthUrl    = $slackHelper->getOAuthUrl();
        }
        $response = new RedirectResponse($oauthUrl);

        return $response;
    }
}
