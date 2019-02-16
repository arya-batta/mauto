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
$view['slots']->set('leContent', 'sms');
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
<div class="sms-notification <?php echo $isEnabled ? 'hide' : ''; ?>" id="licenseclosebutton">
    <span id="license-alert-message">Text Message are currently unpublished in your account settings, publish and configure your text message service provider API credentials to activate the service.</span>
    <img style="cursor: pointer" class="button-notification" src="<?php echo $view['assets']->getUrl('media/images/button.png') ?>" onclick="Le.closeSMSNotification()" width="10" height="10">
</div>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('mautic.sms.smses'); ?></h3></div>
<div class="info-box-holder">
    <?php foreach ($smsBlockDetails as $key =>  $smsBlock): ?>
        <div class="info-box" id="leads-info-box-container">
                <span class="info-box-icon">
                    <i class="<?php echo $smsBlock[1]; ?>" id="icon-class-leads"></i></span>
            <div class="info-box-content">
                <span class="info-box-text"><?php echo $smsBlock[2]; ?></span>
                <span class="info-box-number"><?php echo $smsBlock[3]; ?></span>
            </div>

        </div>
    <?php endforeach; ?>
</div>
<div class="panel panel-default bdr-t-wdh-0 mb-0 list-panel-padding">
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

