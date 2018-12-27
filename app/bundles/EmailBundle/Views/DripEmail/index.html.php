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
$view['slots']->set('leContent', 'dripemail');
$view['slots']->set('headerTitle', $view['translator']->trans($headerTitle));

$pageButtons   = [];
$pageButtons[] = [
    'attr' => [
        'class'       => 'btn btn-default btn-nospin quickadd le-btn-default',
        'data-toggle' => 'ajaxmodal',
        'data-target' => '#leSharedModal',
        'href'        => $view['router']->path('le_dripemail_campaign_action', ['objectAction' => 'quickadd']),
        'data-header' => $view['translator']->trans('le.drip.email.new.header'),
    ],
    'iconClass' => 'fa fa-plus',
    'btnText'   => 'le.drip.email.new',
    'primary'   => true,
];
$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
            ],
            'actionRoute'   => $actionRoute,
            'customButtons' => $pageButtons,
        ]
    )
);

?>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('le.drip.email'); ?></h3></div>
<div style="padding-top: 15px;">
    <?php foreach ($dripEmailBlockDetails as $key => $dripEmailBlock): ?>
        <div class="info-box" id="leads-info-box-container">
                <span class="info-box-icon" style="background-color:<?php echo $dripEmailBlock[0]; ?>;>">
                    <i class="<?php echo $dripEmailBlock[1]; ?>" id="icon-class-leads"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $dripEmailBlock[2]; ?></span>
                <span class="info-box-number"><?php echo $dripEmailBlock[3]; ?></span>
            </div>

        </div>
    <?php endforeach; ?>
</div>
<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render(
        'MauticCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue' => $searchValue,
            'searchHelp'  => 'le.email.help.searchcommands',
            'action'      => $currentRoute,
            'filters'     => $filters,
        ]
    ); ?>

    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>

