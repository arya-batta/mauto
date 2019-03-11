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
$view['slots']->set('leContent', 'asset');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.asset.assets.menu'));

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['asset:assets:create'],
            ],
            'routeBase' => 'asset',
            'langVar'   => 'asset.asset',
        ]
    )
);
?>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('mautic.report.group.assets'); ?></h3></div>
<div class="info-box-holder">
    <?php foreach ($assetBlockDetails as $key => $assetBlock): ?>
        <div class="info-box" id="leads-info-box-container">
                <span class="info-box-icon" style="padding-top: 28px;">
                    <i class="<?php echo $assetBlock[1]; ?>" id="icon-class-leads"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $assetBlock[2]; ?></span>
                <span class="info-box-number"><?php echo $assetBlock[3]; ?></span>
            </div>

        </div>
    <?php endforeach; ?>
</div>
<div class="panel panel-default bdr-t-wdh-0 mb-0 list-panel-padding">
    <?php echo $view->render('MauticCoreBundle:Helper:list_toolbar.html.php', [
        'searchValue' => $searchValue,
        'action'      => $currentRoute,
        'searchHelp'  => 'mautic.asset.asset.help.searchcommands',
        'filters'     => $filters,
    ]); ?>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>
