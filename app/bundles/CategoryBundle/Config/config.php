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
            'le_category_index' => [
                'path'       => '/groups/{bundle}/{page}',
                'controller' => 'MauticCategoryBundle:Category:index',
                'defaults'   => [
                    'bundle' => 'category',
                ],
            ],
            'le_category_action' => [
                'path'       => '/groups/{bundle}/{objectAction}/{objectId}',
                'controller' => 'MauticCategoryBundle:Category:executeCategory',
                'defaults'   => [
                    'bundle' => 'category',
                ],
            ],
        ],
        'api' => [
            'mautic_api_categoriesstandard' => [
                'standard_entity' => true,
                'name'            => 'categories',
                'path'            => '/categories',
                'controller'      => 'MauticCategoryBundle:Api\CategoryApi',
            ],
        ],
    ],

    'menu' => [
        'admin' => [
            'priority' => 100,
            'items'    => [
                'mautic.category.menu.index' => [
                    'route'      => 'le_category_index',
                    'access'     => 'category:categories:view',
                    'id'         => 'le_category_index',
                    'iconClass'  => 'fa fa-folder',
                    'priority'   => 450,
                ],
            ],
        ],
    ],

    'services' => [
        'events' => [
            'mautic.category.subscriber' => [
                'class'     => 'Mautic\CategoryBundle\EventListener\CategorySubscriber',
                'arguments' => [
                    'mautic.helper.bundle',
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                ],
            ],
        ],
        'forms' => [
            'mautic.form.type.category' => [
                'class'     => 'Mautic\CategoryBundle\Form\Type\CategoryListType',
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'translator',
                    'mautic.category.model.category',
                    'router',
                ],
                'alias' => 'category',
            ],
            'mautic.form.type.category_form' => [
                'class'     => 'Mautic\CategoryBundle\Form\Type\CategoryType',
                'alias'     => 'category_form',
                'arguments' => [
                    'translator',
                    'session',
                ],
            ],
            'mautic.form.type.category_bundles_form' => [
                'class'     => 'Mautic\CategoryBundle\Form\Type\CategoryBundlesType',
                'arguments' => [
                    'event_dispatcher',
                ],
                'alias' => 'category_bundles_form',
            ],
        ],
        'models' => [
            'mautic.category.model.category' => [
                'class'     => 'Mautic\CategoryBundle\Model\CategoryModel',
                'arguments' => [
                    'request_stack',
                ],
            ],
        ],
    ],
];
