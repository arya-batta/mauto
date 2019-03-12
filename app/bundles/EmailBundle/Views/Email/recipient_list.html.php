<?php

?>
<div style="float: left;width: 100%;">
    <button id="open-model-btn" type="button" class="btn btn-info" style="float: left;margin-bottom: 15px;" data-toggle="modal" data-target="#emailActivateModel"><?php echo $view['translator']->trans('le.email.config.mailer.transport.test_connection'); ?></button>
</div>
<table class="payment-history">
    <thead>
    <div class="modal fade le-modal-box-align" id="emailActivateModel">
        <div class="le-modal-gradient">
        <div class="modal-dialog le-gradient-align">
            <div class="modal-content le-modal-content">
                <!-- Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Activate Email Service</h4>
                </div>
                <!-- body -->
                <div class="modal-body">
                    <div class="form-group" id ="from_name">
                        <label class="control-label required" for="email">Select a sender email to verify</label>
                        <select id="activate_sender_email">
                            <?php foreach ($EmailList as $key=> $value): ?>
                                <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                        <div class="modal-footer">
                            <div class="button_container" id="aws_email_verification_button">
                            <button type="button"  class="btn le-btn-default waves-effect" onclick="Le.testEmailServerConnection(true);"> <?php echo $view['translator']->trans('le.email.config.mailer.transport.test_connection_activate'); ?></button>
                            <button type="button" class="btn le-btn-default waves-effect" data-dismiss="modal">Close</button>
                            </div>
                       </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    </thead>
</table>

