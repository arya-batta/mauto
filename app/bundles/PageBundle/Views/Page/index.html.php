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
$view['slots']->set('mauticContent', 'page');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.page.pages'));

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['page:pages:create'],
            ],
            'routeBase' => 'page',
        ]
    )
);
?>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('mautic.report.group.pages'); ?></h3></div>
<div style="padding-top: 15px;">
    <?php foreach ($pageBlockDetails as $key => $pageBlock): ?>
        <div class="info-box" id="leads-info-box-container">
                <span class="info-box-icon" style="background-color:<?php echo $pageBlock[0]; ?>;>">
                    <i class="<?php echo $pageBlock[1]; ?>" id="icon-class-leads"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $pageBlock[2]; ?></span>
                <span class="info-box-number"><?php echo $pageBlock[3]; ?></span>
            </div>

        </div>
    <?php endforeach; ?>
</div>
<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render(
        'MauticCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue' => $searchValue,
            'searchHelp'  => 'mautic.page.help.searchcommands',
            'action'      => $currentRoute,
            'filters'     => $filters,
        ]
    ); ?>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>
