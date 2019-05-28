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
$view['slots']->set('leContent', 'slack');
$view['slots']->set('headerTitle', $view['translator']->trans('le.integration.label.slack'));

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['plugin:slack:create'],
            ],
            'routeBase' => 'slack',
        ]
    )
);

?>
<div class="le-header-align" style="padding-bottom:15px;"><h3><?php echo $view['translator']->trans('le.integration.label.slack'); ?></h3></div>
<div class="info-box-holder"></div>
<div class="panel panel-default bdr-t-wdh-0 mb-0 list-panel-padding" style="margin-top: -50px;">
    <?php echo $view->render(
        'MauticCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue' => $searchValue,
            'searchHelp'  => 'mautic.sms.help.searchcommands',
            'searchId'    => 'slack-search',
            'action'      => $currentRoute,
             'filters'    => $filters, // @todo
        ]
    ); ?>

    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>

