<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('leContent', 'focus');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.focus'));

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['plugin:focus:items:create'],
            ],
            'routeBase' => 'focus',
        ]
    )
);
echo $view['assets']->includeScript('plugins/leFocusBundle/Assets/js/focus.js');
?>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('mautic.focus'); ?></h3></div>
<div class="info-box-holder">
    <?php foreach ($focusBlockDetails as $key => $focusBlock): ?>
        <div class="info-box" id="leads-info-box-container">
                <span class="info-box-icon">
                    <i class="<?php echo $focusBlock[1]; ?>" id="icon-class-leads"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $focusBlock[2]; ?></span>
                <span class="info-box-number"><?php echo $focusBlock[3]; ?></span>
            </div>

        </div>
    <?php endforeach; ?>
</div>
<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render(
        'MauticCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue' => $searchValue,
            'searchHelp'  => 'mautic.core.help.searchcommands',
            'action'      => $currentRoute,
            'filters'     => $filters,
        ]
    ); ?>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>
