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
$view['slots']->set('mauticContent', 'sms');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.sms.smses'));

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['sms:smses:create'],
            ],
            'routeBase' => 'sms',
        ]
    )
);

?>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('mautic.sms.smses'); ?></h3></div>
<div style="padding-top: 15px;">
<div class="info-box" id="leads-info-box-container">
                <span class="info-box-icon" style="background-color:<?php echo $view['translator']->trans('le.form.display.color.blocks.blue'); ?>;>">
                    <i class="fa fa-envelope-open-o done_all" id="icon-class-leads"></i></span>
        <div class="info-box-content">
            <span class="info-box-text"><?php echo $view['translator']->trans('le.index.last30daysms.sent'); ?></span>
            <span class="info-box-number"><?php echo $last30DaysSmsSent; ?></span>
        </div>
</div>
</div>
<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render(
        'MauticCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue' => $searchValue,
            'searchHelp'  => 'mautic.sms.help.searchcommands',
            'searchId'    => 'sms-search',
            'action'      => $currentRoute,
             'filters'    => $filters, // @todo
        ]
    ); ?>

    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>

