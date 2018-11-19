<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'name'        => 'Mautic Focus',
    'description' => 'Drive visitor\'s focus on your website with Mautic Focus',
    'version'     => '1.0',
    'author'      => 'Mautic, Inc',

    'routes' => [
        'main' => [
            'le_focus_index' => [
                'path'       => '/popups/{page}',
                'controller' => 'leFocusBundle:Focus:index',
            ],
            'le_focus_action' => [
                'path'       => '/popups/{objectAction}/{objectId}',
                'controller' => 'leFocusBundle:Focus:execute',
            ],
        ],
        'public' => [
            'le_focus_generate' => [
                'path'       => '/popups/{id}.js',
                'controller' => 'leFocusBundle:Public:generate',
            ],
            'le_focus_pixel' => [
                'path'       => '/popups/{id}/viewpixel.gif',
                'controller' => 'leFocusBundle:Public:viewPixel',
            ],
        ],
        'api' => [
            'mautic_api_focusstandard' => [
                'standard_entity' => true,
                'name'            => 'popups',
                'path'            => '/popups',
                'controller'      => 'leFocusBundle:Api\FocusApi',
            ],
            'mautic_api_focusjs' => [
                'path'       => '/popups/{id}/js',
                'controller' => 'leFocusBundle:Api\FocusApi:generateJs',
                'method'     => 'POST',
            ],
        ],
    ],

    'services' => [
        'events' => [
            'mautic.focus.subscriber.form_bundle' => [
                'class'     => 'MauticPlugin\leFocusBundle\EventListener\FormSubscriber',
                'arguments' => [
                    'mautic.focus.model.focus',
                ],
            ],
            'mautic.focus.subscriber.page_bundle' => [
                'class'     => 'MauticPlugin\leFocusBundle\EventListener\PageSubscriber',
                'arguments' => [
                    'mautic.focus.model.focus',
                    'router',
                ],
            ],
            'mautic.focus.subscriber.stat' => [
                'class'     => 'MauticPlugin\leFocusBundle\EventListener\StatSubscriber',
                'arguments' => [
                    'mautic.focus.model.focus',
                ],
            ],
            'mautic.focus.subscriber.focus' => [
                'class'     => 'MauticPlugin\leFocusBundle\EventListener\FocusSubscriber',
                'arguments' => [
                    'router',
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                    'mautic.page.model.trackable',
                    'mautic.page.helper.token',
                    'mautic.asset.helper.token',
                    'mautic.form.helper.token',
                    'mautic.focus.model.focus',
                ],
            ],
            'mautic.focus.stats.subscriber' => [
                'class'     => \MauticPlugin\leFocusBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                ],
            ],
            'mautic.focus.campaignbundle.subscriber' => [
                'class'     => 'MauticPlugin\leFocusBundle\EventListener\CampaignSubscriber',
                'arguments' => [
                    'mautic.campaign.model.event',
                    'mautic.focus.model.focus',
                    'mautic.page.helper.tracking',
                    'router',
                ],
            ],
        ],
        'forms' => [
            'mautic.focus.form.type.color' => [
                'class' => 'MauticPlugin\leFocusBundle\Form\Type\ColorType',
                'alias' => 'focus_color',
            ],
            'mautic.focus.form.type.content' => [
                'class' => 'MauticPlugin\leFocusBundle\Form\Type\ContentType',
                'alias' => 'focus_content',
            ],
            'mautic.focus.form.type.focus' => [
                'class'     => 'MauticPlugin\leFocusBundle\Form\Type\FocusType',
                'alias'     => 'focus',
                'arguments' => 'mautic.security',
            ],
            'mautic.focus.form.type.entity_properties' => [
                'class' => 'MauticPlugin\leFocusBundle\Form\Type\PropertiesType',
                'alias' => 'focus_entity_properties',
            ],
            'mautic.focus.form.type.properties' => [
                'class' => 'MauticPlugin\leFocusBundle\Form\Type\FocusPropertiesType',
                'alias' => 'focus_properties',
            ],
            'mautic.focus.form.type.focusshow_list' => [
                'class'     => 'MauticPlugin\leFocusBundle\Form\Type\FocusShowType',
                'arguments' => 'router',
                'alias'     => 'focusshow_list',
            ],
            'mautic.focus.form.type.focus_list' => [
                'class'     => 'MauticPlugin\leFocusBundle\Form\Type\FocusListType',
                'arguments' => 'mautic.focus.model.focus',
                'alias'     => 'focus_list',
            ],
        ],
        'models' => [
            'mautic.focus.model.focus' => [
                'class'     => 'MauticPlugin\leFocusBundle\Model\FocusModel',
                'arguments' => [
                    'mautic.form.model.form',
                    'mautic.page.model.trackable',
                    'mautic.helper.templating',
                    'event_dispatcher',
                    'mautic.lead.model.lead',
                ],
            ],
        ],
        'other' => [
            'mautic.focus.helper.token' => [
                'class'     => 'MauticPlugin\leFocusBundle\Helper\TokenHelper',
                'arguments' => [
                    'mautic.focus.model.focus',
                    'router',
                ],
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'mautic.focus' => [
                'iconClass' => 'fa fa-bullseye',
                'route'     => 'le_focus_index',
                'access'    => 'plugin:focus:items:view',
                'parent'    => 'mautic.campaigns.root',
                'priority'  => 170,
                'parent'    => 'mautic.core.components',
            ],
        ],
    ],

    'categories' => [
        'plugin:focus' => 'mautic.focus',
    ],

    'parameters' => [
        'website_snapshot_url' => 'https://mautic.net/api/snapshot',
        'website_snapshot_key' => '',
    ],
];
