<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('mauticContent', 'company');
$view['slots']->set('headerTitle', $view['translator']->trans('le.companies.menu.root'));

$pageButtons = [];
if ($permissions['lead:leads:create']) {
    $pageButtons[] = [
        'attr' => [
            'href' => $view['router']->path('le_import_action', ['object' => 'companies', 'objectAction' => 'new']),
        ],
        'iconClass' => 'fa fa-upload',
        'btnText'   => 'le.lead.lead.import',
    ];

    $pageButtons[] = [
        'attr' => [
            'href' => $view['router']->path('le_import_index', ['object' => 'companies']),
        ],
        'iconClass' => 'fa fa-history',
        'btnText'   => 'le.lead.lead.import.index',
    ];
}

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['lead:leads:create'],
            ],
            'routeBase'     => 'company',
            'customButtons' => $pageButtons,
        ]
    )
);
?>

<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render(
        'MauticCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue' => $searchValue,
            'searchHelp'  => 'mautic.core.help.searchcommands',
            'action'      => $currentRoute,
        ]
    ); ?>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>
