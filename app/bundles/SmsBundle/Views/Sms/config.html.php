<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 25/1/19
 * Time: 10:41 AM
 */?>
<div style="float: left;width: 100%;">
    <button id="open-model-btn" type="button" class="btn btn-info sender_profile_create_btn" style="float: left;margin-bottom: 15px;" data-toggle="modal" data-target="#activateSmsModel"><?php echo $view['translator']->trans('le.sms.config.test_connection'); ?></button>
</div>
<div class="help-block" id ="sms_config_errors"></div>
<table class="payment-history">
    <thead>
    <div class="modal fade le-modal-box-align" id="activateSmsModel">
        <div class="le-modal-gradient">
            <div class="modal-dialog le-gradient-align">
                <div class="modal-content le-modal-content">
                    <!-- Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Send a test message and activate this service</h4>
                    </div>
                    <!-- body -->
                    <div class="modal-body">
                        <div class="form-group" id ="from_name">
                            <label class="control-label required" for="email">Enter a valid mobile number with country code</label>
                            <input type="text" class="form-control le-input" id="sms_config_mobile" placeholder="Mobile Number" name="number" required="required">
                            <div class="help-block " id ="sms_config_mobile_errors" style="font-size: 1.2rem;color: #a94442;line-height: 2.2;font-style: normal;letter-spacing: 0;display: block;"></div>
                        </div>
                        <div class="modal-footer">
                            <div class="button_container" id="aws_email_verification_button">
                                <button type="button"  class="btn btn-success sms_config_btn" data-toggle="#" onclick="Le.testSmsConnection()"> <?php echo $view['translator']->trans('le.sms.config.activate'); ?></button>
                                <button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </thead>
    <tbody>
</table>