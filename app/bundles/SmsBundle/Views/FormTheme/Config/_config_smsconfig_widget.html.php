<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$isadmin       =$view['security']->isAdmin();
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $view['translator']->trans('le.config.tab.smsconfig'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <?php echo $view['form']->row($form['sms_transport']); ?>
            </div>
            <div class="col-md-4" >
                <div id="smsStatus">
                    <?php echo $view['form']->row($form['sms_status'], ['attr' => ['tabindex' => '-1']]); ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php echo $view['form']->row($form['account_url']); ?>
                <?php echo $view['form']->row($form['sms_from_number']); ?>
            </div>
            <div class="col-md-6">
                <?php echo $view['form']->row($form['account_sender_id']); ?>
                <?php echo $view['form']->row($form['account_auth_token']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php echo $view['form']->row($form['account_api_key']); ?>
                <?php echo $view['form']->row($form['account_sid']); ?>
            </div>
            <div class="col-md-6 hide" id="smsPublish">
                <?php echo $view['form']->row($form['publish_account']); ?>
            </div>
            <div id="smsTestButtonContainer">
                <div class="col-md-12 help-block"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3" id="smsActivatebtn">
                <div class="button_container">
                    <?php echo $view->render('MauticSmsBundle:Sms:config.html.php', []); ?>
                    <span class="fa fa-spinner fa-spin hide"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel panel-primary <?php echo $isadmin ? '' : 'hide'?>">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $view['translator']->trans('mautic.core.config.form.link.shortener'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <hr class="text-muted" />
                <div class="col-md-12">
                    <?php echo $view['form']->row($form['link_shortener_url']); ?>
                </div>
            </div>
        </div>
        <p><?php echo $view['translator']->trans('mautic.core.config.form.link.shortener.help.label.url'); ?></p>
        <p><?php echo $view['translator']->trans('mautic.core.config.form.link.shortener.help.label.text'); ?></p>
    </div>
</div>
<div class="panel panel-primary <?php echo $isadmin ? '' : 'hide'?>">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $view['translator']->trans('mautic.config.tab.sms.frequency_rules'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <?php echo $view['form']->row($form['sms_frequency_number']); ?>
            </div>
            <div class="col-md-12">
                <?php echo $view['form']->row($form['sms_frequency_time']); ?>
            </div>
        </div>
    </div>
</div>