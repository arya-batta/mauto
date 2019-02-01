<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'services' => [
        'events' => [
            'mautic.sms.campaignbundle.subscriber' => [
                'class'     => 'Mautic\SmsBundle\EventListener\CampaignSubscriber',
                'arguments' => [
                    'mautic.helper.integration',
                    'mautic.sms.model.sms',
                    'mautic.helper.notification',
                    'mautic.sms.model.send_sms_to_user',
                    'mautic.helper.sms',
                    'mautic.security'
                ],
            ],
            'mautic.sms.smsbundle.subscriber' => [
                'class'     => 'Mautic\SmsBundle\EventListener\SmsSubscriber',
                'arguments' => [
                    'mautic.core.model.auditlog',
                    'mautic.page.model.trackable',
                    'mautic.page.helper.token',
                    'mautic.asset.helper.token',
                ],
            ],
            'mautic.sms.channel.subscriber' => [
                'class'     => \Mautic\SmsBundle\EventListener\ChannelSubscriber::class,
                'arguments' => [
                    'mautic.helper.integration',
                ],
            ],
            'mautic.sms.message_queue.subscriber' => [
                'class'     => \Mautic\SmsBundle\EventListener\MessageQueueSubscriber::class,
                'arguments' => [
                    'mautic.sms.model.sms',
                ],
            ],
            'mautic.sms.stats.subscriber' => [
                'class'     => \Mautic\SmsBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                ],
            ],
            'mautic.sms.configbundle.subscriber' => [
                'class' => Mautic\SmsBundle\EventListener\ConfigSubscriber::class,
            ],
        ],
        'forms' => [
            'mautic.form.type.sms' => [
                'class'     => 'Mautic\SmsBundle\Form\Type\SmsType',
                'arguments' => 'mautic.factory',
                'alias'     => 'sms',
            ],
            'mautic.form.type.smsconfig' => [
                'class' => 'Mautic\SmsBundle\Form\Type\ConfigType',
                'alias' => 'smsconfig',
            ],
            'mautic.form.type.smssend_list' => [
                'class'     => 'Mautic\SmsBundle\Form\Type\SmsSendType',
                'arguments' => 'router',
                'alias'     => 'smssend_list',
            ],
            'mautic.form.type.sms_list' => [
                'class'     => 'Mautic\SmsBundle\Form\Type\SmsListType',
                'arguments' => 'mautic.factory',
                'alias'     => 'sms_list',
            ],
            'mautic.form.type.sms.config.form' => [
                'class'     => \Mautic\SmsBundle\Form\Type\ConfigType::class,
                'alias'     => 'smsconfig',
                'arguments' => ['mautic.sms.transport_chain', 'translator','mautic.helper.user'],
            ],
        ],
        'helpers' => [
            'mautic.helper.sms' => [
                'class'     => 'Mautic\SmsBundle\Helper\SmsHelper',
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'mautic.lead.model.lead',
                    'mautic.helper.phone_number',
                    'mautic.sms.model.sms',
                    'mautic.helper.integration',
                    'mautic.helper.core_parameters',
                    'mautic.factory',
                ],
                'alias' => 'sms_helper',
            ],
        ],
        'other' => [
            'le.sms.transport.solutioninfini' => [
                'class'     => \Mautic\SmsBundle\Api\SolutionInfinityApi::class,
                'arguments' => [
                    'mautic.page.model.trackable',
                    'mautic.helper.phone_number',
                    'mautic.helper.integration',
                    'monolog.logger.mautic',
                    'mautic.helper.core_parameters',
                ],
                'tag'          => 'mautic.sms_transport',
                'tagArguments' => [
                    'integrationAlias' => 'SolutionInfinity',
                ],
                'alias' => 'SolutionInfinity',
            ],
            'le.sms.transport.leadsengage' => [
                'class'     => \Mautic\SmsBundle\Api\SolutionInfinityApi::class,
                'arguments' => [
                    'mautic.page.model.trackable',
                    'mautic.helper.phone_number',
                    'mautic.helper.integration',
                    'monolog.logger.mautic',
                    'mautic.helper.core_parameters',
                ],
                'tag'          => 'mautic.sms_transport',
                'tagArguments' => [
                    'integrationAlias' => 'SolutionInfinity',
                ],
                'alias' => 'SolutionInfinity',
            ],
            'mautic.sms.transport_chain' => [
                'class'     => \Mautic\SmsBundle\Sms\TransportChain::class,
                'arguments' => [
                    '%mautic.sms_transport%',
                    'mautic.helper.integration',
                    'monolog.logger.mautic',
                ],
            ],
            'le.sms.transport.twilio' => [
                'class'        => \Mautic\SmsBundle\Api\TwilioApi::class,
                'arguments'    => [
                    'mautic.page.model.trackable',
                    'mautic.helper.phone_number',
                    'mautic.helper.integration',
                    'monolog.logger.mautic',
                ],
                'tag'          => 'mautic.sms_transport',
                'tagArguments' => [
                    'integrationAlias' => 'Twilio',
                ],
            ],
        ],
        'models' => [
            'mautic.sms.model.sms' => [
                'class'     => 'Mautic\SmsBundle\Model\SmsModel',
                'arguments' => [
                    'mautic.page.model.trackable',
                    'mautic.lead.model.lead',
                    'mautic.channel.model.queue',
                    'mautic.sms.transport_chain',
                    'mautic.user.model.user',
                ],
            ],
            'mautic.sms.model.send_sms_to_user' => [
                'class'     => \Mautic\SmsBundle\Model\SendSmsToUser::class,
                'arguments' => [
                    'mautic.sms.model.sms',
                    'mautic.helper.licenseinfo',
                    'mautic.helper.notification',
                    'mautic.user.model.user',
                ],
            ],
        ],
        'integrations' => [
            'mautic.integration.twilio' => [
                'class' => \Mautic\SmsBundle\Integration\TwilioIntegration::class,
            ],
            'mautic.integration.solutioninfinity' => [
                'class' => \Mautic\SmsBundle\Integration\SolutionInfinityIntegration::class,
            ],
        ],
    ],
    'categories' => [
        'sms' => null,
    ],
    'routes' => [
        'main' => [
            'le_sms_index' => [
                'path'       => '/textmessage/{page}',
                'controller' => 'MauticSmsBundle:Sms:index',
            ],
            'le_sms_action' => [
                'path'       => '/textmessage/{objectAction}/{objectId}',
                'controller' => 'MauticSmsBundle:Sms:execute',
            ],
            'le_sms_contacts' => [
                'path'       => '/textmessage/view/{objectId}/contact/{page}',
                'controller' => 'MauticSmsBundle:Sms:contacts',
            ],
        ],
        'public' => [
            'le_receive_sms' => [
                'path'       => '/textmessage/receive',
                'controller' => 'MauticSmsBundle:Api\SmsApi:receive',
            ],
        ],
        'api' => [
            'mautic_api_smsesstandard' => [
                'standard_entity' => true,
                'name'            => 'smses',
                'path'            => '/smses',
                'controller'      => 'MauticSmsBundle:Api\SmsApi',
            ],
            'mautic_api_smses_send' => [
                'path'       => '/smses/{id}/contact/{contactId}/send',
                'controller' => 'MauticSmsBundle:Api\SmsApi:send',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'items' => [
                'mautic.sms.smses' => [
                    'iconClass'=> 'fa fa-weixin',
                    'route'    => 'le_sms_index',
                    'access'   => ['sms:smses:viewown', 'sms:smses:viewother'],
                    'parent'   => 'mautic.core.channels',

                    'priority' => 50,
                ],
            ],
        ],
    ],
    'parameters' => [
        'sms_enabled'              => false,
        'sms_username'             => null,
        'sms_password'             => null,
        'sms_transport'            => 'le.sms.transport.twilio',
        'account_url'              => null,
        'account_sid'              => null,
        'account_api_key'          => null,
        'account_auth_token'       => null,
        'account_sender_id'        => null,
        'sms_frequency_number'     => null,
        'sms_frequency_time'       => null,
        'sms_from_number'          => null,
        'publish_account'          => null,
        'sms_status'               => null,
        'link_shortener_url'       => '',
        'le_account_sender_id'     => '',
        'le_account_url'           => '',
        'le_account_api_key'       => '',
    ],
];
