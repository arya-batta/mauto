<?php

?>
<div class="modal fade le-modal-box-align" id="sendingdomaincreateModel">
    <div class="le-modal-gradient">
        <div class="modal-dialog le-gradient-align">
            <div class="modal-content le-modal-content">
                <!-- Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Add Domain</h4>
                </div>
                <!-- body -->
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label required" for="email">Domain</label>
                        <input type="text" class="form-control le-input" id="sender_domain_name" placeholder="yourdomain.com" name="senderdomain" required="required">
                        <div class="help-block" id ="sender_domain_name_errors"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="button_container">
                            <button type="button"  class="btn le-btn-default sender_domain_add_btn waves-effect"> Add </button>
                            <button type="button" class="btn le-btn-default waves-effect" data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade le-modal-box-align" id="sendingdomainverifyModel">
    <div class="le-modal-gradient">
        <div class="modal-dialog le-gradient-align">
            <div class="modal-content le-modal-content">
                <!-- Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Domain Validation</h4>
                </div>
                <!-- body -->
                <div class="modal-body">
                    <div class="form-group">
                        <div class="sending-domain-validation-header">SPF Validation</div>
                        <div class="spf-validation-status">
                            <i class="fa fa-spin fa-spinner"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="sending-domain-validation-header">DKIM Validation</div>
                        <div class="dkim-validation-status">
                            <i class="fa fa-spin fa-spinner"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="sending-domain-validation-header">Tracking Validation</div>
                        <div class="tracking-validation-status">
                            <i class="fa fa-spin fa-spinner"></i>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="button_container">
                            <button type="button" class="btn le-btn-default waves-effect" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="sendingdomainspfHelpModel">
    <div class="">
        <div class="modal-dialog sending-domain-model">
            <div class="modal-content le-modal-content sending-domain-content">
                <div class="modal-header">
                    <h4 class="modal-title">What is a SPF record?</h4>
                </div>
                <div class="modal-body">
                    <p>SPF stands for "Sender Policy Framework". A SPF record is in place to identify which mail servers are authorized to send mail for a given domain. It is used to prevent spammers from sending mail with fraudulent From addresses at that domain.</p>
                    <div class="alert alert-info le-alert-info" id="form-action-placeholder" style="margin-left:40px;margin-right:40px;">
                        <p>Though many DNS editors allow for the creation of an SPF record, it is recommended that the SPF record is entered as a TXT record.</p>
                    </div>
                </div>
                <!-- Header -->
                <div class="modal-header">
                    <h4 class="modal-title">How to create a SPF record?</h4>
                </div>
                <!-- body -->
                <div class="modal-body">
                    <p>Create a TXT record and enter:</p>
                    <p><span class="code-example">Host/Name: <span id="spf-host-text">@</span></span>
                    <p><?php echo $view['translator']->trans('le.sending.domain.spf.host.help.text'); ?></p>
                        <span class="code-example">Value: <span id="spf-value-text"><?php echo $view['translator']->trans('le.sending.domain.spf.value.text'); ?>
                            </span>
                        </span>
                        <a id="spf-value-text_atag" onclick="Le.copySpanTextToClipBoard('spf-value-text');">
                            <i aria-hidden="true" class="fa fa-clipboard"></i> copy to clipboard </a>
                    </p>
                    <div class="modal-footer">
                        <div class="button_container">
                            <button type="button" class="btn le-btn-default waves-effect" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="sendingdomaindkimHelpModel">
    <div class="">
        <div class="modal-dialog sending-domain-model">
            <div class="modal-content le-modal-content sending-domain-content">
                <div class="modal-header">
                    <h4 class="modal-title">What is a DKIM record?
                    </h4>
                </div>
                <div class="modal-body">
                    <p>DKIM stands for <i>"Domain Keys Identified Mail"</i>. They allow receiving servers to confirm that mail coming from a domain is authorized by the domain's administrators.</p>
                </div>
                <!-- Header -->
                <div class="modal-header">
                    <h4 class="modal-title">How to create a DKIM record?</h4>
                </div>
                <!-- body -->
                <div class="modal-body">
                    <p>Create a TXT record and enter:</p>
                    <p>
                        <span class="code-example">Host/Name: <span id="dkim-host-text"><?php echo $view['translator']->trans('le.sending.domain.dkim.host.text'); ?></span></span><a id="dkim-host-text_atag" onclick="Le.copySpanTextToClipBoard('dkim-host-text');">
                            <i aria-hidden="true" class="fa fa-clipboard"></i>
                            copy to clipboard                </a><span class="code-example">Value: <span id="dkim-value-text"><?php echo $view['translator']->trans('le.sending.domain.dkim.value.text'); ?></span></span><a id="dkim-value-text_atag" onclick="Le.copySpanTextToClipBoard('dkim-value-text');">
                            <i aria-hidden="true" class="fa fa-clipboard"></i>
                            copy to clipboard                </a></p>
                    <div class="modal-footer">
                        <div class="button_container">
                            <button type="button" class="btn le-btn-default waves-effect" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="sendingdomaintrackingHelpModel">
    <div class="">
        <div class="modal-dialog sending-domain-model">
            <div class="modal-content le-modal-content sending-domain-content">
                <!-- Header -->
                <div class="modal-header">
                    <h4 class="modal-title">How to create a tracking record?</h4>
                </div>
                <!-- body -->
                <div class="modal-body">
                    <p><?php echo $view['translator']->trans('le.sending.domain.tracking.help.text'); ?></p>
                    <p>Create a CNAME record and enter:</p>
                    <p>
                        <span class="code-example">Host/Name: <span id="tracking-host-text"><?php echo $view['translator']->trans('le.sending.domain.tracking.host.text'); ?></span></span><a id="tracking-host-text_atag" onclick="Le.copySpanTextToClipBoard('tracking-host-text');">
                            <i aria-hidden="true" class="fa fa-clipboard"></i>
                            copy to clipboard                </a><span class="code-example">Value: <span id="tracking-value-text"><?php echo $view['translator']->trans('le.sending.domain.tracking.value.text'); ?></span></span><a id="tracking-value-text_atag" onclick="Le.copySpanTextToClipBoard('tracking-value-text');">
                            <i aria-hidden="true" class="fa fa-clipboard"></i>
                            copy to clipboard                </a></p>
                    <div class="modal-footer">
                        <div class="button_container">
                            <button type="button" class="btn le-btn-default waves-effect" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="panel panel-primary sendingdomain_config">
    <div class="panel-heading sendingdomain_config">
        <h3 class="panel-title"><?php echo $view['translator']->trans('le.config.sendingdomain.header'); ?></h3>
        <p style="font-size: 12px;">
            <?php echo $view['translator']->trans('le.config.sendingdomain.desc1'); ?>
            <?php echo $view['translator']->trans('le.config.sendingdomain.desc2'); ?>
        </p>
    </div>
    <div class="panel-body">
        <div class='sending-domain-container'>
           <?php echo $view->render('MauticEmailBundle:Config:sendingdomainlist.html.php', [
               'sendingdomains'             => $sendingdomains,
           ]); ?>
        </div>
        <div class="alert alert-info le-alert-info hide" style="margin-top: 5%;" id="form-action-placeholder">
            <a href="https://anyfunnels.com/" style="display: block;text-decoration: underline;margin-bottom: 10px" target="_blank">Why sending domain is required</a>
            <a href="https://anyfunnels.com/" style="display: block;text-decoration: underline;margin-bottom: 10px" target="_blank">Contact us to configure sending domain for you</a>
        </div>
    </div>
</div>

