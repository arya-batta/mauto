<?php

?>
<style>
    #errors {
        font-size: 1.2rem;
        color: #a94442;
        line-height: 2.2;
        font-style: normal;
        letter-spacing: 0;
    }
    td {
        padding-bottom: .3em;
    }
</style>
<table class="payment-history">
    <thead>
    <button id="open-model-btn" type="button" class="btn btn-info" style="margin-left: 18%;" data-toggle="modal" data-target="#emailVerifyModel"><?php echo $view['translator']->trans('le.core.button.aws.verification'); ?></button>
    <div class="modal fade le-modal-box-align" id="emailVerifyModel">
        <div class="le-modal-gradient">
        <div class="modal-dialog le-gradient-align">
            <div class="modal-content le-modal-content">
                <!-- Header -->
                <div class="modal-header">
                    <h4 class="modal-title">AWS Email Verification</h4>
                </div>
                <!-- body -->
                <div class="modal-body">
                        <div class="form-group" id ="user_email">
                            <label class="control-label required" for="email">E-mail address</label>
                            <input type="email" class="form-control le-input" id="aws_email_verification" placeholder="Enter valid email" name="email" required="required">
                            <div class="help-block" id ="errors"></div>
                        </div>
                        <br>
                        <div class="modal-footer">
                            <div class="button_container" id="aws_email_verification_button">
                            <button type="button"  class="btn btn-success aws-verification-btn" id="aws_emailverification-btn"> <?php echo $view['translator']->trans('le.core.button.aws.verification'); ?></button>
                            <button type="button" class="btn btn-success" data-dismiss="modal">Close</button>
                            </div>
                       </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <tr>
        <th>
          <span class="header">Verified Emails<span>
        </th>
        <th>
         <span class="header">Status<span>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($verifiedEmails as $verifiedEmail): ?>
        <tr>
          <td>
         <span class="data aws_verified_emails_list" ><?php echo $verifiedEmail->getVerifiedEmails()?><span>
          </td>
          <td>
           <span class="data"><?php echo $verifiedEmail->getVerificationStatus() ?><span>
          </td>
          <td>
           <a type="button" style="padding:3px 7px;" class="btn btn-danger delete_aws_verified_emails"  data-target="#" >Delete</a>
          </td>
        </tr>
    <?php endforeach; ?>
    <tr>
    </tr>
    </tbody>
</table>

