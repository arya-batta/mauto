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
<div class="modal fade le-modal-box-align" id="sendingdomainspfHelpModel">
    <div class="le-modal-gradient">
        <div class="modal-dialog le-gradient-align">
            <div class="modal-content le-modal-content">
                <!-- Header -->
                <div class="modal-header">
                    <h4 class="modal-title">How to create a SPF record?</h4>
                </div>
                <!-- body -->
                <div class="modal-body">
                    <p>Create a SPF record and enter:</p>
                    <p>
    <span class="code-example">Host/Name: <span id="spf-host-text">@</span>
    </span>
                    <p style="color: #888;"><?php echo $view['translator']->trans('le.sending.domain.spf.host.help.text'); ?></p>
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
<div class="modal fade le-modal-box-align" id="sendingdomaindkimHelpModel">
    <div class="le-modal-gradient">
        <div class="modal-dialog le-gradient-align">
            <div class="modal-content le-modal-content">
                <!-- Header -->
                <div class="modal-header">
                    <h4 class="modal-title">How to create a DKIM record?</h4>
                </div>
                <!-- body -->
                <div class="modal-body">
                    <p>Create a DKIM record and enter:</p>
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
<div class="modal fade le-modal-box-align" id="sendingdomaintrackingHelpModel">
    <div class="le-modal-gradient">
        <div class="modal-dialog le-gradient-align">
            <div class="modal-content le-modal-content">
                <!-- Header -->
                <div class="modal-header">
                    <h4 class="modal-title">How to create a tracking record?</h4>
                </div>
                <!-- body -->
                <div class="modal-body">
                    <p style="color: #888;"><?php echo $view['translator']->trans('le.sending.domain.tracking.help.text'); ?></p>
                    <p>Create a Tracking record and enter:</p>
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
        <h3 class="panel-title"><?php echo $view['translator']->trans('le.config.tab.sendingdomain'); ?></h3>
    </div>
    <div class="panel-body">
        <div class='sending-domain-container'>
           <?php echo $view->render('MauticEmailBundle:Config:sendingdomainlist.html.php', [
               'sendingdomains'             => $sendingdomains,
           ]); ?>
        </div>
        <div class="alert alert-info le-alert-info" style="margin-top: 5%;" id="form-action-placeholder">
            <a href="https://anyfunnels.com/" style="display: block;text-decoration: underline;margin-bottom: 10px" target="_blank">Why sending domain is required</a>
            <a href="https://anyfunnels.com/" style="display: block;text-decoration: underline;margin-bottom: 10px" target="_blank">Contact us to configure sending domain for you</a>
        </div>
    </div>
</div>

