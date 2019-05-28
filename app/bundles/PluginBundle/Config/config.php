<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'routes' => [
        'main' => [
            'le_integration_auth_callback_secure' => [
                'path'       => '/plugins/integrations/authcallback/{integration}',
                'controller' => 'MauticPluginBundle:Auth:authCallback',
            ],
            'le_integration_auth_postauth_secure' => [
                'path'       => '/plugins/integrations/authstatus/{integration}',
                'controller' => 'MauticPluginBundle:Auth:authStatus',
            ],
            'le_plugin_index' => [
                'path'       => '/plugins',
                'controller' => 'MauticPluginBundle:Plugin:index',
            ],
            'le_plugin_config' => [
                'path'       => '/plugins/config/{name}/{page}',
                'controller' => 'MauticPluginBundle:Plugin:config',
            ],
            'le_plugin_info' => [
                'path'       => '/plugins/info/{name}',
                'controller' => 'MauticPluginBundle:Plugin:info',
            ],
            'le_plugin_reload' => [
                'path'       => '/plugins/reload',
                'controller' => 'MauticPluginBundle:Plugin:reload',
            ],
            'le_integrations_index' => [
                'path'       => '/integrations',
                'controller' => 'MauticPluginBundle:Integration:index',
            ],
            'le_integrations_config' => [
                'path'       => '/integrations/{name}',
                'controller' => 'MauticPluginBundle:Integration:config',
            ],
            'le_integrations_fb_page_subscription' => [
                'path'       => '/integrations/{integration}/page/{pageid}/{action}',
                'controller' => 'MauticPluginBundle:Integration:fbPageSubscription',
            ],
            'le_integrations_account_remove' => [
                'path'       => '/integrations/{name}/remove',
                'controller' => 'MauticPluginBundle:Integration:accountRemove',
            ],
            'le_slack_index' => [
                'path'       => '/slack/{page}',
                'controller' => 'MauticPluginBundle:Slack:index',
            ],
            'le_slack_action' => [
                'path'       => '/slack/{objectAction}/{objectId}',
                'controller' => 'MauticPluginBundle:Slack:execute',
            ],
        ],
        'public' => [
            'le_integration_auth_user' => [
                'path'       => '/plugins/integrations/authuser/{integration}',
                'controller' => 'MauticPluginBundle:Auth:authUser',
            ],
            'le_new_integration_auth_user' => [
                'path'       => '/integrations/authuser/{integration}',
                'controller' => 'MauticPluginBundle:Integration:authUser',
            ],
            'le_integration_auth_callback' => [
                'path'       => '/plugins/integrations/authcallback/{integration}',
                'controller' => 'MauticPluginBundle:Auth:authCallback',
            ],
            'le_integration_auth_postauth' => [
                'path'       => '/plugins/integrations/authstatus/{integration}',
                'controller' => 'MauticPluginBundle:Auth:authStatus',
            ],
            'le_integration_auth_webhook_callback' => [
                'path'       => '/integrations/callback/{integration}',
                'controller' => 'MauticPluginBundle:Auth:webhookCallback',
            ],
        ],
    ],
    'menu' => [
        'admin' => [
            'priority' => 50,
            'items'    => [
                'mautic.plugin.plugins' => [
                    'id'        => 'le_plugin_root',
                    'iconClass' => 'fa-plus-circle',
                    // 'access'    => 'plugin:plugins:manage',
                    'route'     => 'le_plugin_index',
                    'access'    => 'admin',
                ],
            ],
        ],
    ],

    'services' => [
        'events' => [
            'mautic.plugin.pointbundle.subscriber' => [
                'class' => 'Mautic\PluginBundle\EventListener\PointSubscriber',
            ],
            'mautic.plugin.formbundle.subscriber' => [
                'class'       => 'Mautic\PluginBundle\EventListener\FormSubscriber',
                'methodCalls' => [
                    'setIntegrationHelper' => [
                        'mautic.helper.integration',
                    ],
                ],
            ],
            'mautic.plugin.campaignbundle.subscriber' => [
                'class'       => 'Mautic\PluginBundle\EventListener\CampaignSubscriber',
                'methodCalls' => [
                    'setIntegrationHelper' => [
                        'mautic.helper.integration',
                    ],
                ],
            ],
            'mautic.plugin.leadbundle.subscriber' => [
                'class'     => 'Mautic\PluginBundle\EventListener\LeadSubscriber',
                'arguments' => [
                    'mautic.plugin.model.plugin',
                ],
            ],
            'mautic.plugin.integration.subscriber' => [
                'class' => 'Mautic\PluginBundle\EventListener\IntegrationSubscriber',
            ],
        ],
        'forms' => [
            'mautic.form.type.integration.details' => [
                'class' => 'Mautic\PluginBundle\Form\Type\DetailsType',
                'alias' => 'integration_details',
            ],
            'mautic.form.type.integration.settings' => [
                'class'     => 'Mautic\PluginBundle\Form\Type\FeatureSettingsType',
                'arguments' => [
                    'session',
                    'mautic.helper.core_parameters',
                    'monolog.logger.mautic',
                ],
                'alias' => 'integration_featuresettings',
            ],
            'mautic.form.type.integration.fields' => [
                'class'     => 'Mautic\PluginBundle\Form\Type\FieldsType',
                'alias'     => 'integration_fields',
                'arguments' => 'translator',
            ],
            'mautic.form.type.integration.company.fields' => [
                'class'     => 'Mautic\PluginBundle\Form\Type\CompanyFieldsType',
                'alias'     => 'integration_company_fields',
                'arguments' => 'translator',
            ],
            'mautic.form.type.integration.keys' => [
                'class' => 'Mautic\PluginBundle\Form\Type\KeysType',
                'alias' => 'integration_keys',
            ],
            'mautic.form.type.integration.list' => [
                'class'     => 'Mautic\PluginBundle\Form\Type\IntegrationsListType',
                'arguments' => 'mautic.factory',
                'alias'     => 'integration_list',
            ],
            'mautic.form.type.integration.config' => [
                'class' => 'Mautic\PluginBundle\Form\Type\IntegrationConfigType',
                'alias' => 'integration_config',
            ],
            'mautic.form.type.integration.campaign' => [
                'class' => 'Mautic\PluginBundle\Form\Type\IntegrationCampaignsType',
                'alias' => 'integration_campaign_status',
            ],
            'le.form.type.fb_custom_audience_list' => [
                'class'     => 'Mautic\PluginBundle\Form\Type\FbCustomAudienceType',
                'arguments' => 'mautic.factory',
                'alias'     => 'fb_custom_audience_list',
            ],
            'le.form.type.fb_leadads_list' => [
                'class'     => 'Mautic\PluginBundle\Form\Type\FbLeadAdsType',
                'arguments' => 'mautic.factory',
                'alias'     => 'fb_leadads_list',
            ],
            'le.form.type.instapage_type' => [
                'class'     => 'Mautic\PluginBundle\Form\Type\InstapageFormType',
                'arguments' => 'translator',
                'alias'     => 'instapage_type',
            ],
            'le.form.type.unbounce' => [
                'class'     => 'Mautic\PluginBundle\Form\Type\UnbounceFormType',
                'arguments' => 'translator',
                'alias'     => 'unbounce_type',
            ],
            'le.form.type.calendly' => [
                'class'     => 'Mautic\PluginBundle\Form\Type\CalendlyFormType',
                'arguments' => [
                        'translator',
                        'mautic.factory',
                    ],
                'alias'     => 'calendly_type',
            ],
            'le.form.type.lead_field_mapping' => [
                'class'       => 'Mautic\PluginBundle\Form\Type\FieldMappingType',
                'alias'       => 'lead_field_mapping',
                'arguments'   => ['mautic.factory'],
            ],
            'le.form.type.integration_field_mapping' => [
                'class'       => 'Mautic\PluginBundle\Form\Type\IntegrationFieldMapping',
                'alias'       => 'integration_field_mapping',
                'arguments'   => ['mautic.factory'],
            ],
            'le.form.type.slacktype' => [
                'class'       => 'Mautic\PluginBundle\Form\Type\SlackType',
                'alias'       => 'slack',
                'arguments'   => ['mautic.factory'],
            ],
            'le.form.type.slack.listtype' => [
                'class'       => 'Mautic\PluginBundle\Form\Type\SlackListType',
                'alias'       => 'slack_list',
                'arguments'   => ['mautic.factory'],
            ],
            'le.form.type.slack_message_list' => [
                'class'     => Mautic\PluginBundle\Form\Type\SlackMessageType::class,
                'arguments' => 'mautic.factory',
                'alias'     => 'slack_message_list',
            ],
        ],
        'other' => [
            'mautic.helper.integration' => [
                'class'     => \Mautic\PluginBundle\Helper\IntegrationHelper::class,
                'arguments' => [
                    'kernel',
                    'doctrine.orm.entity_manager',
                    'mautic.helper.paths',
                    'mautic.helper.bundle',
                    'mautic.helper.core_parameters',
                    'mautic.helper.templating',
                    'mautic.plugin.model.plugin',
                ],
            ],
            'mautic.helper.fbapi' => [
                'class'     => \Mautic\PluginBundle\Helper\FacebookApiHelper::class,
                'arguments' => [
                    'mautic.factory',
                ],
            ],
            'mautic.helper.slack' => [
                'class'     => \Mautic\PluginBundle\Helper\SlackHelper::class,
                'arguments' => [
                    'mautic.factory',
                ],
            ],
        ],
        'models' => [
            'mautic.plugin.model.plugin' => [
                'class'     => 'Mautic\PluginBundle\Model\PluginModel',
                'arguments' => [
                    'mautic.lead.model.field',
                ],
            ],
            'mautic.plugin.model.slack' => [
                'class'     => 'Mautic\PluginBundle\Model\SlackModel',
                'arguments' => [
                    'mautic.factory',
                ],
            ],

            'mautic.plugin.model.integration_entity' => [
                'class' => Mautic\PluginBundle\Model\IntegrationEntityModel::class,
            ],
        ],
    ],
    'parameters' => [
        'facebook_app_id'                                   => '',
        'facebook_app_secret'                               => '',
        'facebook_oauth_callback'                           => 'https://apps.anyfunnels.com/integrations/facebook/callback.php',
        'slack_client_id'                                   => '',
        'slack_client_secret'                               => '',
        'slack_internal_token'                              => '',
        'slack_internal_channel'                            => '',
    ],
];
