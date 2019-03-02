<?php

?>
<style>
    #sender_profile_from_name_errors,#sender_profile_from_email_errors ,#sender_profile_errors{
        font-size: 1.2rem;
        color: #a94442;
        line-height: 2.2;
        font-style: normal;
        letter-spacing: 0;
        display: block;
    }
    td {
        padding-bottom: .3em;
    }
</style>
<div style="float: left;width: 100%;">
    <button id="open-model-btn" type="button" class="btn btn-info sender_profile_create_btn" style="float: left;margin-bottom: 15px;" data-toggle="modal" data-target="#emailVerifyModel"><?php echo $view['translator']->trans('le.core.button.sender.profile.new'); ?></button>
</div>
<div class="help-block" id ="sender_profile_errors"></div>
<table class="payment-history">
    <thead>
    <div class="modal fade le-modal-box-align" id="emailVerifyModel">
        <div class="le-modal-gradient">
        <div class="modal-dialog le-gradient-align">
            <div class="modal-content le-modal-content">
                <!-- Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Add New Sender Profile</h4>
                </div>
                <!-- body -->
                <div class="modal-body">
                    <div class="form-group" id ="from_name">
                        <label class="control-label required" for="email">New sender name</label>
                        <input type="text" class="form-control le-input" id="sender_profile_from_name" placeholder="Enter valid name" name="fromname" required="required">
                        <div class="help-block" id ="sender_profile_from_name_errors"></div>
                    </div>
                        <div class="form-group" id ="from_email">
                            <label class="control-label required" for="email">New sender email</label>
                            <input type="email" class="form-control le-input" id="sender_profile_from_email" placeholder="Enter valid email" name="fromemail" required="required">
                            <div class="help-block" id ="sender_profile_from_email_errors"></div>
                        </div>
                    <div class="alert alert-info le-alert-info" id="form-action-placeholder">
                        <p>We’ll send an email with a verification link to the new sender email inbox. Please make sure you click the verification link to activate the new profile.</p>
                    </div>
                        <div class="modal-footer">
                            <div class="button_container" id="aws_email_verification_button">
                            <button type="button"  class="btn btn-success sender_profile_verify_btn waves-effect"> <?php echo $view['translator']->trans('le.core.button.aws.verification'); ?></button>
                            <button type="button" class="btn btn-success waves-effect" data-dismiss="modal">Close</button>
                            </div>
                       </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <tr>
        <th>
          <span class="header">Sender name<span>
        </th>
        <th>
          <span class="header">Sender email<span>
        </th>
        <th>
         <span class="header">Status<span>
        </th>
        <th>
         <span class="header">Remove<span>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php $count=0;?>
    <?php foreach ($verifiedEmails as $verifiedEmail): ?>
        <tr>
            <td style="width: 25%;">
         <span class="data sender_profile_from_name_col" ><?php echo $verifiedEmail->getFromName()?><span>
            </td>
          <td style="width: 45%;">
         <span class="data sender_profile_from_email_col" ><?php echo $verifiedEmail->getVerifiedEmails()?><span>
          </td>
            <?php $emailId= explode("@",$verifiedEmail->getVerifiedEmails());
              $id=$emailId['0'];?>
          <td style="width: 20%;">
              <a type="button" id = "pending-verified-button-<?php echo $count;?>" style="color: #fff;padding: 2px 5px;margin-top: 5px;pointer-events: none;background:<?php echo $verifiedEmail->getVerificationStatus() == 0 ? '#39ac73' : '#ff4d4d' ?>;" class="btn pending_verify_button"  data-target="#" ><?php echo $verifiedEmail->getVerificationStatus() == 0 ? 'Verified' : 'Pending' ?></a>
              <a type="button" id = "re-verify-button-<?php echo $count;?>" style="padding: 2px 5px;margin-top: 5px;" class="btn btn-danger verify_sender_profile_btn <?php echo $verifiedEmail->getVerificationStatus() == 0 ? 'hide' : '' ?>"   data-target="#" data-toggle="tooltip" data-container="body" data-placement="bottom" title="" data-original-title="Click here to resend verification email">Re-Verify</a>
          </td>
          <td style="width: 10%;">
           <a type="button" id="remove-sender-button-<?php echo $count;?>" style="padding: 2px 5px;margin-top: 5px;" class="btn btn-danger remove_sender_profile_btn"  data-target="#" data-toggle="tooltip" data-container="body" data-placement="bottom" title="" data-original-title="Click here to remove sender profile">Remove</a>
          </td>
        </tr>
        <?php $count++;?>
    <?php endforeach; ?>
    </tbody>
</table>

