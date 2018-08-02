<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $view['translator']->trans('mautic.config.tab.smsconfig'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <?php echo $view['form']->row($form['sms_transport']); ?>
            </div>
            <div class="col-md-6">
                <?php echo $view['form']->row($form['account_url']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php echo $view['form']->row($form['account_sender_id']); ?>
            </div>
            <div class="col-md-6">
                <?php echo $view['form']->row($form['account_api_key']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php echo $view['form']->row($form['account_auth_token']); ?>
            </div>
            <div class="col-md-6">
                <?php echo $view['form']->row($form['account_sid']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?php echo $view['form']->row($form['publish_account']); ?>
            </div>
            <div class="col-sm-6 pt-lg mt-3" id="smsTestButtonContainer">
                <div class="button_container">
                    <?php echo $view['form']->widget($form['sms_test_connection_button']); ?>
                    <span class="fa fa-spinner fa-spin hide"></span>
                </div>
                <div class="col-md-9 help-block"></div>
            </div>
        </div>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo $view['translator']->trans('mautic.config.tab.frequency_rules'); ?></h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo $view['form']->row($form['sms_from_number']); ?>
                    </div>
                    <div class="col-md-12">
                        <?php echo $view['form']->row($form['sms_frequency_number']); ?>
                    </div>
                    <div class="col-md-12">
                        <?php echo $view['form']->row($form['sms_frequency_time']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>