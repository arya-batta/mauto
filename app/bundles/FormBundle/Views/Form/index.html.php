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
$view['slots']->set('leContent', 'form');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.form.forms'));

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['form:forms:create'],
            ],
            'routeBase' => 'form',
            'langVar'   => 'form.form',
        ]
    )
);

?>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('mautic.form.forms'); ?></h3></div>
<div class="info-box-holder">
    <?php foreach ($formBlockDetails as $key => $formBlock): ?>
        <div class="info-box" id="leads-info-box-container">
                <span class="info-box-icon" style="padding-top:28px;">
                    <i class="<?php echo $formBlock[1]; ?>" id="icon-class-leads"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $formBlock[2]; ?></span>
                <span class="info-box-number"><?php echo $formBlock[3]; ?></span>
            </div>

        </div>
    <?php endforeach; ?>
</div>
<div class="panel panel-default bdr-t-wdh-0 mb-0 list-panel-padding">
    <?php echo $view->render(
        'MauticCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue' => $searchValue,
            'searchHelp'  => 'mautic.form.form.help.searchcommands',
            'searchId'    => 'form-search',
            'action'      => $currentRoute,
            'filters'     => $filters,
        ]
    ); ?>

    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>

