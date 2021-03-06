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
            'le_config_action' => [
                'path'       => '/config/{objectAction}/{objectId}',
                'controller' => 'MauticConfigBundle:Config:execute',
                'defaults'   => [
                    'objectId' => 'emailconfig',
                ],
            ],
            'le_sendingdomain_action' => [
                'path'       => '/config/sendingdomain',
                'controller' => 'MauticConfigBundle:Config:sendingDomain',
            ],
            'le_apisettings_action' => [
                'path'       => '/config/apisettings',
                'controller' => 'MauticConfigBundle:Config:apiSettings',
            ],
            'le_settingsmenu_action' => [
                'path'       => '/settings',
                'controller' => 'MauticConfigBundle:Config:settingsMenu',
            ],
            'le_sysinfo_index' => [
                'path'       => '/sysinfo',
                'controller' => 'MauticConfigBundle:Sysinfo:index',
            ],
        ],
        'public' => [
            'le_sender_profile_verify_link' => [
                'path'       => '/verify/sender/{idhash}',
                'controller' => 'MauticEmailBundle:Public:verifySenderProfile',
            ],
        ],
    ],

    'menu' => [
        'admin' => [
            'mautic.config.menu.index' => [
                'route'           => 'le_config_action',
                'routeParameters' => ['objectAction' => 'edit'],
                'iconClass'       => 'fa-cogs',
                'priority'        => 500,
                'id'              => 'le_config_index',
                'access'          => ['lead:leads:viewown', 'lead:leads:viewother'],
                //'access'          => 'admin',
            ],
            'mautic.sysinfo.menu.index' => [
                'route'           => 'le_sysinfo_index',
                'iconClass'       => 'fa-life-ring',
                'priority'        => 350,
                'id'              => 'le_sysinfo_index',
                'access'          => 'admin',
                'checks'          => [
                    'parameters' => [
                        'sysinfo_disabled' => false,
                    ],
                ],
            ],
            'le.config.setupmenu.header.title' => [
                'route'           => 'le_settingsmenu_action',
                'iconClass'       => 'mdi mdi-settings',
                'id'              => 'le_settingsmenu_action',
                'priority'        => 450,
            ],
        ],
    ],

    'services' => [
        'events' => [
            'mautic.config.subscriber' => [
                'class'     => 'Mautic\ConfigBundle\EventListener\ConfigSubscriber',
                'arguments' => [
                    'mautic.helper.core_parameters',
                ],
            ],
        ],

        'forms' => [
            'mautic.form.type.config' => [
                'class'     => 'Mautic\ConfigBundle\Form\Type\ConfigType',
                'arguments' => [
                    'mautic.config.form.restriction_helper',
                ],
                'alias' => 'config',
            ],
        ],
        'models' => [
            'mautic.config.model.sysinfo' => [
                'class'     => \Mautic\ConfigBundle\Model\SysinfoModel::class,
                'arguments' => [
                    'mautic.helper.paths',
                    'mautic.helper.core_parameters',
                    'translator',
                ],
            ],
            // @deprecated 2.12.0; to be removed in 3.0
            'mautic.config.model.config' => [
                'class' => \Mautic\ConfigBundle\Model\ConfigModel::class,
            ],
        ],
        'others' => [
            'mautic.config.mapper' => [
                'class'     => \Mautic\ConfigBundle\Mapper\ConfigMapper::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                ],
            ],
            'mautic.config.form.restriction_helper' => [
                'class'     => \Mautic\ConfigBundle\Form\Helper\RestrictionHelper::class,
                'arguments' => [
                    'translator',
                    '%mautic.security.restrictedConfigFields%',
                    '%mautic.security.restrictedConfigFields.displayMode%',
                ],
            ],
        ],
    ],
];
