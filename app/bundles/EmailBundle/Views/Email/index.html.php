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
$view['slots']->set('leContent', 'email');
$view['slots']->set('headerTitle', $view['translator']->trans($headerTitle));

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['email:emails:create'],
            ],
            'actionRoute' => $actionRoute,
        ]
    )
);

?>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('mautic.lead.emails'); ?></h3></div>
<div style="padding-top: 15px;">
    <?php foreach ($emailBlockDetails as $key => $emailBlock): ?>
        <div class="info-box" id="leads-info-box-container">
                <span class="info-box-icon" style="background-color:<?php echo $emailBlock[0]; ?>;>">
                    <i class="<?php echo $emailBlock[1]; ?>" id="icon-class-leads"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $emailBlock[2]; ?></span>
                <span class="info-box-number"><?php echo $emailBlock[3]; ?></span>
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

