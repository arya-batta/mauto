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
$view['slots']->set('leContent', 'leadlist');
$view['slots']->set('headerTitle', $view['translator']->trans('le.lead.list.header.index'));

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => true, // this is intentional. Each user can segment leads
            ],
            'routeBase' => 'segment',
            'langVar'   => 'lead.list',
            //'tooltip'   => 'le.lead.lead.segment.add.help',
        ]
    )
);
?>
<div class="le-header-align" style="padding-bottom:15px;"><h3><?php echo $view['translator']->trans('le.campaign.lead.segments'); ?></h3></div>
<div class="info-box-holder">
    <?php foreach ($allBlockDetails as $key => $segmentBlock): ?>
    <div class="info-box hide" id="leads-info-box-container">
                <span class="info-box-icon">
                    <i class="<?php echo $segmentBlock[1]; ?>" id="icon-class-leads"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $segmentBlock[2]; ?></span>
                <span class="info-box-number"><?php echo $segmentBlock[3]; ?></span>
            </div>

    </div>
    <?php endforeach; ?>
</div>
<div class="panel panel-default bdr-t-wdh-0 list-panel-padding" style="margin-top: -50px;">
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
