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
$view['slots']->set('leContent', 'client');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.api.client.header.index'));

$view['slots']->set('actions', $view->render('MauticCoreBundle:Helper:page_actions.html.php', [
    'templateButtons' => [
        'new' => $permissions['create'],
    ],
    'routeBase' => 'client',
    'langVar'   => 'api.client',
    'editMode'  => 'ajaxmodal',
    'editAttr'  => [
        'data-target' => '#leSharedModal',
        'data-header' => $view['translator']->trans('mautic.api.client.header.new'),
        'data-toggle' => 'ajaxmodal',
    ],
]));
?>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('mautic.api.client.menu.index'); ?></h3></div>
<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render('MauticCoreBundle:Helper:list_toolbar.html.php', [
        'searchValue' => $searchValue,
        'searchHelp'  => 'mautic.api.client.help.searchcommands',
        'filters'     => $filters,
    ]); ?>
    <a class="btn le-btn-default btn-nospin" style="float: right;margin-top: -50px; margin-right: 93px;" onclick="window.open('<?php echo $view['translator']->trans('mautic.api.client.list.url');?>', '_blank');">
        <i class="fa fa-file-text"> </i><span style="margin-left: 2px;font: "500 14px/31px Roboto, sans-serif !important> Developer Document</span>
     </a>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>
