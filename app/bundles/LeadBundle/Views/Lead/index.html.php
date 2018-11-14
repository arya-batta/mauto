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
$view['slots']->set('mauticContent', 'lead');
$view['slots']->set('headerTitle', $view['translator']->trans('le.lead.leads'));

$pageButtons = [];
if ($permissions['lead:leads:create']) {
    $pageButtons[] = [
        'attr' => [
            'class'       => 'btn btn-default btn-nospin quickadd le-btn-default',
            'data-toggle' => 'ajaxmodal',
            'data-target' => '#MauticSharedModal',
            'href'        => $view['router']->path('le_contact_action', ['objectAction' => 'new', 'qf' => 1]),
            'data-header' => $view['translator']->trans('le.lead.lead.menu.quickadd'),
        ],
        'iconClass' => 'fa fa-bolt',
        'btnText'   => 'le.lead.lead.menu.quickadd',
        'primary'   => true,
    ];

  /**  if ($permissions['lead:imports:create']) {
        $pageButtons[] = [
            'attr' => [
                'href' => $view['router']->path('le_import_action', ['object' => 'leads', 'objectAction' => 'new']),
            ],
            'iconClass' => 'fa fa-upload',
            'btnText'   => 'le.lead.lead.import',
        ];
    }

    if ($permissions['lead:imports:view']) {
        $pageButtons[] = [
            'attr' => [
                'href' => $view['router']->path('le_import_index', ['object' => 'leads']),
            ],
            'iconClass' => 'fa fa-history',
            'btnText'   => 'le.lead.lead.import.index',
        ];
    } */
}

// Only show toggle buttons for accessibility
$extraHtml = <<<button
<div class="btn-group ml-5 sr-only ">
    <span data-toggle="tooltip" title="{$view['translator']->trans(
    'le.lead.tooltip.list'
)}" data-placement="left"><a id="table-view" href="{$view['router']->path('le_contact_index', ['page' => $page, 'view' => 'list'])}" data-toggle="ajax" class="btn btn-default"><i class="fa fa-fw fa-table"></i></span></a>
    <span data-toggle="tooltip" title="{$view['translator']->trans(
    'le.lead.tooltip.grid'
)}" data-placement="left"><a id="card-view" href="{$view['router']->path('le_contact_index', ['page' => $page, 'view' => 'grid'])}" data-toggle="ajax" class="btn btn-default"><i class="fa fa-fw fa-th-large"></i></span></a>
</div>
button;

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['lead:leads:create'],
            ],
            'routeBase'     => 'contact',
            'langVar'       => 'lead.lead',
            'customButtons' => $pageButtons,
            'extraHtml'     => $extraHtml,
            'onlyexport'    => 'true',
        ]
    )
);

$toolbarButtons = [
    [
        'attr' => [
            'class'       => 'hidden-xs le-btn-default btn btn-default btn-sm btn-nospin',
            'href'        => 'javascript: void(0)',
            'onclick'     => 'Mautic.toggleLiveLeadListUpdate();',
            'id'          => 'liveModeButton',
            'data-toggle' => false,
            'data-max-id' => $maxLeadId,
        ],
        'tooltip'   => $view['translator']->trans('le.lead.lead.live_update'),
        'iconClass' => 'fa fa-bolt',
    ],
];

if ($indexMode == 'list') {
    $toolbarButtons[] = [
        'attr' => [
            'class'          => 'hidden-xs btn btn-default btn-sm btn-nospin le-btn-default'.(($anonymousShowing) ? ' le-btn-default' : ''),
            'href'           => 'javascript: void(0)',
            'onclick'        => 'Mautic.toggleAnonymousLeads();',
            'id'             => 'anonymousLeadButton',
            'data-anonymous' => $view['translator']->trans('le.lead.lead.searchcommand.isanonymous'),
        ],
        'tooltip'   => $view['translator']->trans('le.lead.lead.anonymous_leads'),
        'iconClass' => 'fa fa-user-secret',
    ];
}
$toolbarButtons = [];
?>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('le.lead.list.thead.leadcount'); ?></h3></div>
<div style="padding-top: 15px;">
        <div class="info-box" id="leads-info-box-container">
            <a href="<?php echo $view['router']->generate('le_contact_index', ['search'=> '']); ?>" data-toggle="ajax">
                <span class="info-box-icon" style="background-color:#00c0ef;>">
                    <i class="fa fa-users" id="icon-class-leads"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text"><?php echo $view['translator']->trans('le.lead.lifecycle.graph.pie.all.lists'); ?></span>
                    <span class="info-box-number"><?php echo $totalLeadsCount; ?></span>
                </div>
            </a>
        </div>
    <div class="info-box" id="leads-info-box-container">
        <a href="<?php echo $view['router']->generate('le_contact_index', ['search'=> 'recentlyaddedleads']); ?>" data-toggle="ajax">
                <span class="info-box-icon" style="background-color: #f39c12;>">
                     <i class="fa fa-user-plus" id="icon-class-leads"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $view['translator']->trans('le.lead.list.recently.added'); ?></span>
                <span class="info-box-number"><?php echo $recentlyAdded; ?></span>
            </div>
        </a>
    </div>
    <div class="info-box" id="leads-info-box-container">
        <a href="<?php echo $view['router']->generate('le_contact_index', ['search'=> 'activeleads']); ?>" data-toggle="ajax">
                <span class="info-box-icon" style="background-color: #42CC39;>">
                    <i class="fa fa-history" id="icon-class-leads"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $view['translator']->trans('le.lead.list.active.leads'); ?></span>
                <span class="info-box-number"><?php echo $activeLeads; ?></span>
            </div>
        </a>
    </div>
    <div>
        <div class="info-box" id="leads-info-box-container">
            <a href="<?php echo $view['router']->generate('le_contact_index', ['search'=> 'donotcontact']); ?>" data-toggle="ajax">
            <span class="info-box-icon" style="background-color:#FF0000;>">
                  <i class="fa fa-user-times" id="icon-class-leads"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $view['translator']->trans('le.lead.list.churn.leads'); ?></span>
                <span class="info-box-number"><?php echo $donotContact; ?></span>
            </div>
            </a>
        </div>
    </div>
</div>

<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render(
        'MauticCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue'   => $searchValue,
            'searchHelp'    => 'le.lead.lead.help.searchcommands',
            'action'        => $currentRoute,
            'customButtons' => $toolbarButtons,
            'filters'       => $filters,
        ]
    ); ?>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>
