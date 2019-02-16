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
$view['slots']->set('leContent', 'tags');
$view['slots']->set('headerTitle', $view['translator']->trans('le.lead.tags.header.index'));

$pageButtons[] = [
    'attr' => [
        'class'       => 'btn btn-default btn-nospin quickadd le-btn-default waves-effect',
        'data-toggle' => 'ajaxmodal',
        'data-target' => '#leSharedModal',
        'href'        => $view['router']->path('le_tags_action', ['objectAction' => 'new']),
        'data-header' => $view['translator']->trans('le.lead.tags.header.new'),
    ],
    'iconClass' => 'fa fa-plus',
    'btnText'   => 'le.lead.tags.new',
    'primary'   => true,
];
$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
            ],
            'routebase'     => 'tag',
            'customButtons' => $pageButtons,
        ]
    )
);
?>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('le.campaign.lead.tags'); ?></h3></div>
<div class="info-box-holder">
    <?php foreach ($allBlockDetails as $key => $tagBlock): ?>
        <div class="info-box" id="leads-info-box-container">
                <span class="info-box-icon">
                    <i class="<?php echo $tagBlock[1]; ?>" id="icon-class-leads"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $tagBlock[2]; ?></span>
                <span class="info-box-number"><?php echo $tagBlock[3]; ?></span>
            </div>

        </div>
    <?php endforeach; ?>
</div>
<div class="panel panel-default bdr-t-wdh-0 list-panel-padding">
    <?php echo $view->render(
        'MauticCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue' => $searchValue,
            'searchHelp'  => 'le.lead.list.help.searchcommands',
            'action'      => $currentRoute,
            ]
    ); ?>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>
