<?php

return [
    'routes' => [
        'main' => [
            'le_subscription_index' => [
                'path'       => '/subscription',
                'controller' => 'MauticSubscriptionBundle:Subscription:index',
            ],
            'le_plan_index' => [
                'path'       => '/plans',
                'controller' => 'MauticSubscriptionBundle:Subscription:indexplan',
            ],
            'le_pricing_index' => [
                'path'       => '/pricing',
                'controller' => 'MauticSubscriptionBundle:Subscription:indexpricing',
            ],
            'le_billing_index' => [
                'path'       => '/pricing/billing',
                'controller' => 'MauticSubscriptionBundle:Subscription:billing',
            ],
            'le_subscription_status' => [
                'path'       => '/subscription-status',
                'controller' => 'MauticSubscriptionBundle:Subscription:subscriptionstatus',
            ],
            'le_payment_status' => [
                'path'       => '/payment-status',
                'controller' => 'MauticSubscriptionBundle:Subscription:paymentstatus',
            ],
            'le_kyc_action' => [
                'path'       => '/kyc/{objectAction}/{objectId}',
                'controller' => 'MauticSubscriptionBundle:KYC:execute',
            ],
            'le_accountinfo_action' => [
                'path'       => '/accountinfo/{objectAction}/{objectId}',
                'controller' => 'MauticSubscriptionBundle:Account:execute',
            ],
            'le_viewinvoice_action' => [
                'path'       => '/viewinvoice/{id}',
                'controller' => 'MauticSubscriptionBundle:Public:viewinvoice',
            ],
            'le_success_action' => [
                'path'       => '/success',
                'controller' => 'MauticSubscriptionBundle:Subscription:offer',
            ],
            'le_welcome_action' => [
                'path'       => '/welcome',
                'controller' => 'MauticSubscriptionBundle:Subscription:welcome',
            ],
            'le_account_suspended_action' => [
                'path'       => '/account/suspended',
                'controller' => 'MauticSubscriptionBundle:Subscription:suspended',
            ],
            'le_account_under_review_action' => [
                'path'       => '/account/under/review',
                'controller' => 'MauticSubscriptionBundle:Subscription:underReview',
            ],
            'le_account_sending_domain_inactive_action' => [
                'path'       => '/account/sending/domain/inactive',
                'controller' => 'MauticSubscriptionBundle:Subscription:sendingDomainInactive',
            ],
        ],
        'public' => [
        ],
        'api'    => [],
    ],
    'menu' => [
        'main'    => [],
        'admin'   => [
            'leadsengage.subs.account.menu.index' => [
                'route'           => 'le_accountinfo_action',
                'id'              => 'le_accountinfo_index',
                'routeParameters' => ['objectAction' => 'edit'],
                'iconClass'       => 'fa fa-address-book-o',
                'priority'        => 600,
                'checks'          => [
                    'parameters' => [
                        'accountinfo_disabled' => false,
                    ],
                ],
            ],
        ],
        'extra'   => [],
        'profile' => [],
    ],
    'services' => [
        'events'  => [],
        'forms'   => [
            'mautic.form.type.kycinfo' => [
                'class'     => 'Mautic\SubscriptionBundle\Form\Type\KYCType',
                'alias'     => 'kycinfo',
                'arguments' => [
                    'mautic.report.model.report',
                    'translator',
                    'mautic.helper.language',
                    'mautic.helper.core_parameters',
                ],
            ],
            'mautic.form.type.accountinfo' => [
                'class'     => 'Mautic\SubscriptionBundle\Form\Type\AccountType',
                'alias'     => 'accountinfo',
                'arguments' => [
                    'mautic.report.model.report',
                    'translator',
                    'mautic.helper.language',
                    'mautic.helper.core_parameters',
                ],
            ],
            'mautic.form.type.billinginfo' => [
                'class'     => 'Mautic\SubscriptionBundle\Form\Type\BillingType',
                'alias'     => 'billinginfo',
                'arguments' => [
                    'mautic.report.model.report',
                    'translator',
                    'mautic.helper.language',
                    'mautic.helper.core_parameters',
                ],
            ],
        ],
        'helpers' => [],
        'menus'   => [],
        'other'   => [
            'le.core.repository.subscription' => [
                'class'     => 'Mautic\SubscriptionBundle\Entity\SubscriptionRepository',
                'arguments' => [
                    'doctrine.orm.commondb_entity_manager', 'mautic.email.repository.licenseinfo', 'le.core.repository.signup', 'mautic.subscription.model.accountinfo',
                ],
            ],
            'le.core.repository.signup' => [
                'class'     => 'Mautic\SubscriptionBundle\Entity\SignupRepository',
                'arguments' => [
                    'doctrine.orm.signupdb_entity_manager',
                ],
            ],
            'le.helper.payment' => [
                'class'     => \Mautic\SubscriptionBundle\Helper\PaymentHelper::class,
                'arguments' => [
                    'mautic.factory',
                ],
            ],
            'le.subscription.repository.payment' => [
                'class'     => \Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Mautic\SubscriptionBundle\Entity\PaymentHistory::class,
                ],
            ],
            'le.subscription.repository.stripecard' => [
                'class'     => \Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Mautic\SubscriptionBundle\Entity\StripeCard::class,
                ],
            ],
            'le.subscription.repository.statemachine' => [
                'class'     => \Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Mautic\SubscriptionBundle\Entity\StateMachine::class,
                ],
            ],
            'le.helper.statemachine' => [
                'class'     => \Mautic\SubscriptionBundle\Helper\StateMachineHelper::class,
                'arguments' => [
                    'mautic.factory', 'le.subscription.repository.statemachine',
                ],
            ],
        ],
        'models'    => [
            'mautic.subscription.model.kycinfo' => [
                'class' => 'Mautic\SubscriptionBundle\Model\KYCModel',
            ],
            'mautic.subscription.model.userpreference' => [
                'class' => 'Mautic\SubscriptionBundle\Model\UserPreferenceModel',
            ],
            'mautic.subscription.model.accountinfo' => [
                'class' => 'Mautic\SubscriptionBundle\Model\AccountInfoModel',
            ],
            'mautic.subscription.model.billinginfo' => [
                'class' => 'Mautic\SubscriptionBundle\Model\BillingModel',
            ],
        ],
        'validator' => [],
    ],
    'parameters' => [
        'razoparpay_apikey'                => '',
        'razoparpay_apisecret'             => '',
        'paypal_clientid'                  => '',
        'paypal_clientsecret'              => '',
        'paypal_mode'                      => 'sandbox', //live
        'paypal_rootpath'                  => '%kernel.root_dir%/paypal',
        'paypal_logpath'                   => '%kernel.root_dir%/paypal/log',
        'paypal_cachepath'                 => '%kernel.root_dir%/paypal/cache', // for determining paypal cache directory
        'paypal_loglevel'                  => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
        'paypal_log_enabled'               => true,
        'stripe_api_key'                   => '',
        'campaign_id'                      => null,
        'source_event_id'                  => null,
        'goal_event_id'                    => null,
    ],
];
