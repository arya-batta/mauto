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
$view['slots']->set('leContent', 'listoptin');
$view['slots']->set('headerTitle', $view['translator']->trans('le.lead.list.optin.header.index'));

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => true, // this is intentional. Each user can segment leads
            ],
            'routeBase' => 'listoptin',
            'langVar'   => 'lead.list.optin',
        ]
    )
);
?>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('le.lead.list.optin'); ?></h3></div>
<div class="info-box-holder">
    <?php foreach ($allBlockDetails as $key => $ListBlock): ?>
    <div class="info-box" id="leads-info-box-container">
                <span class="info-box-icon">
                    <i class="<?php echo $ListBlock[1]; ?>" id="icon-class-leads"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $ListBlock[2]; ?></span>
                <span class="info-box-number"><?php echo $ListBlock[3]; ?></span>
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
