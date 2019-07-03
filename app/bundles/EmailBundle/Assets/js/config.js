Le.testMonitoredEmailServerConnection = function(mailbox) {
    var data = {
        host:       mQuery('#config_emailconfig_monitored_email_' + mailbox + '_host').val(),
        port:       mQuery('#config_emailconfig_monitored_email_' + mailbox + '_port').val(),
        encryption: mQuery('#config_emailconfig_monitored_email_' + mailbox + '_encryption').val(),
        user:       mQuery('#config_emailconfig_monitored_email_' + mailbox + '_user').val(),
        password:   mQuery('#config_emailconfig_monitored_email_' + mailbox + '_password').val(),
        mailbox:    mailbox
    };

    var abortCall = false;
    if (!data.host) {
        mQuery('#config_emailconfig_monitored_email_' + mailbox + '_host').parent().addClass('has-error');
        abortCall = true;
    } else {
        mQuery('#config_emailconfig_monitored_email_' + mailbox + '_host').parent().removeClass('has-error');
    }

    if (!data.port) {
        mQuery('#config_emailconfig_monitored_email_' + mailbox + '_port').parent().addClass('has-error');
        abortCall = true;
    } else {
        mQuery('#config_emailconfig_monitored_email_' + mailbox + '_port').parent().removeClass('has-error');
    }

    if (abortCall) {
        return;
    }

    mQuery('#' + mailbox + 'TestButtonContainer .fa-spinner').removeClass('hide');

    Le.ajaxActionRequest('email:testMonitoredEmailServerConnection', data, function(response) {
        var theClass = (response.success) ? 'has-success' : 'has-error';
        var theMessage = response.message;
        mQuery('#' + mailbox + 'TestButtonContainer').removeClass('has-success has-error').addClass(theClass);
        mQuery('#' + mailbox + 'TestButtonContainer .help-block').html(theMessage);
        mQuery('#' + mailbox + 'TestButtonContainer .fa-spinner').addClass('hide');

        if (response.folders) {
            if (mailbox == 'general') {
                // Update applicable folders
                mQuery('select[data-imap-folders]').each(
                    function(index) {
                        var thisMailbox = mQuery(this).data('imap-folders');
                        if (mQuery('#config_emailconfig_monitored_email_' + thisMailbox + '_override_settings_0').is(':checked')) {
                            var folder = '#config_emailconfig_monitored_email_' + thisMailbox + '_folder';
                            var curVal = mQuery(folder).val();
                            mQuery(folder).html(response.folders);
                            mQuery(folder).val(curVal);
                            mQuery(folder).trigger('chosen:updated');
                        }
                    }
                );
            } else {
                // Find and update folder lists
                var folder = '#config_emailconfig_monitored_email_' + mailbox + '_folder';
                var curVal = mQuery(folder).val();
                mQuery(folder).html(response.folders);
                mQuery(folder).val(curVal);
                mQuery(folder).trigger('chosen:updated');
            }
        }
    });
};

Le.testEmailServerConnection = function(sendEmail) {
    Le.updateEmailStatus();
    var transport = mQuery('#config_emailconfig_mailer_transport').val();
    var port = mQuery('#config_emailconfig_mailer_host').val();
    var host = mQuery('#config_emailconfig_mailer_host').val();
    var password = mQuery('#config_emailconfig_mailer_password').val();
    var user = mQuery('#config_emailconfig_mailer_user').val();
    if(transport == "smtp" && (port == "" || host == "" || user == "" || password == "")){
        mQuery('#emailActivateModel').modal('hide');
        mQuery('#mailerTestButtonContainer .fa-spinner').removeClass('hide');
        mQuery('.emailconfig #mailerTestButtonContainer').removeClass('has-success has-error').addClass("has-error");
        mQuery('.emailconfig #mailerTestButtonContainer .help-block').html("Please configure all the values.");
        mQuery('.emailconfig #mailerTestButtonContainer .fa-spinner').addClass('hide');
        return;
    }
    var toemail = "";
    var trackingcode = "";
    var additionalinfo = "";
    if(typeof mQuery('#config_trackingconfig_emailInstructionsto') !== "undefined" && mQuery('#config_trackingconfig_emailInstructionsto') != null){
        toemail = mQuery('#config_trackingconfig_emailInstructionsto').val();
        trackingcode = mQuery('#script_preTag').html();
        additionalinfo = mQuery('#config_trackingconfig_emailAdditionainfo').val();
    }
    var fromemail = "";
    var selectedid = 0;
    if(typeof mQuery('#activate_sender_email') !== "undefined" && mQuery('#activate_sender_email') != null && sendEmail){
        selectedid = mQuery("#activate_sender_email").prop('selectedIndex');
        fromemail = mQuery('#activate_sender_email').val();
    }
    var data = {
        amazon_region: mQuery('#config_emailconfig_mailer_amazon_region').val(),
        api_key:       mQuery('#config_emailconfig_mailer_api_key').val(),
        authMode:      mQuery('#config_emailconfig_mailer_auth_mode').val(),
        encryption:    mQuery('#config_emailconfig_mailer_encryption').val(),
        from_email:    (sendEmail) ? fromemail : mQuery('#config_emailconfig_mailer_from_email').val(),
        from_name:     mQuery('#config_emailconfig_mailer_from_name').val(),
        host:          mQuery('#config_emailconfig_mailer_host').val(),
        password:      mQuery('#config_emailconfig_mailer_password').val(),
        port:          mQuery('#config_emailconfig_mailer_port').val(),
        send_test:     (typeof sendEmail !== 'undefined') ? sendEmail : false,
        transport:     mQuery('#config_emailconfig_mailer_transport').val(),
        user:          mQuery('#config_emailconfig_mailer_user').val(),
        toemail:       toemail,
        trackingcode:  trackingcode,
        additionalinfo:additionalinfo
    };
    mQuery('#emailActivateModel').modal('hide');
    mQuery('#mailerTestButtonContainer .fa-spinner').removeClass('hide');

    Le.ajaxActionRequest('email:testEmailServerConnection', data, function(response) {
        var theClass = (response.success) ? 'has-success' : 'has-error';
        var theMessage = response.message;
        if(theClass == 'has-success'){
            mQuery('#config_emailconfig_email_status').val('Active');
            mQuery('#config_emailconfig_email_status').removeClass('status_fail');
            mQuery('#config_emailconfig_email_status').addClass('status_success');

        }
        if(theClass == 'has-error'){
            mQuery('#config_emailconfig_email_status').val('InActive');
            mQuery('#config_emailconfig_email_status').removeClass('status_success');
            mQuery('#config_emailconfig_email_status').addClass('status_fail');

        }
       if(!mQuery('.emailconfig #mailerTestButtonContainer').is(':hidden')){
           mQuery('.emailconfig #mailerTestButtonContainer').removeClass('has-success has-error').addClass(theClass);
           mQuery('.emailconfig #mailerTestButtonContainer .help-block').html(theMessage);
           mQuery('.emailconfig #mailerTestButtonContainer .fa-spinner').addClass('hide');
       }else{
           mQuery('.trackingconfig #mailerTestButtonContainer').removeClass('has-success has-error').addClass(theClass);
           mQuery('.trackingconfig #mailerTestButtonContainer .fa-spinner').addClass('hide');
           if(response.to_address_empty){
               mQuery('.trackingconfig .emailinstructions').addClass('has-error');
           }else{
               mQuery('.trackingconfig #mailerTestButtonContainer .help-block').html(theMessage);
               mQuery('.trackingconfig .emailinstructions').removeClass('has-error');
           }
       }
       var id='';
        if((response.success)){
            Le.changeSenderProfileStatusFrontEnd(true,fromemail,selectedid);
        } else {
            Le.changeSenderProfileStatusFrontEnd(false,fromemail,selectedid);
        }
    });
};

Le.copytoClipboardforms = function(id) {
    try{
        var copyText = document.getElementById(id);
        copyText.select();
        document.execCommand("Copy");
        var copyTexts = document.getElementById(id+"_atag");
        copyTexts.innerHTML = '<i aria-hidden="true" class="fa fa-clipboard"></i>copied';
        setTimeout(function() {
            var copyTexta = document.getElementById(id+"_atag");
            copyTextval = '<i aria-hidden="true" class="fa fa-clipboard"></i>copy to clipboard';
            copyTexta.innerHTML = copyTextval;
        }, 1000);
    }catch(err){
        alert(err);
    }
};
Le.copySpanTextToClipBoard = function(id) {
    try{
        var range,selection;
        if (document.body.createTextRange) {
            range = document.body.createTextRange();
            range.moveToElementText(document.getElementById(id));
            range.select();
        } else if (window.getSelection) {
            selection = window.getSelection();
            range = document.createRange();
            range.selectNodeContents(document.getElementById(id));
            selection.removeAllRanges();
            selection.addRange(range);
        }
        document.execCommand("Copy");
        var copyTexts = document.getElementById(id+"_atag");
        copyTexts.innerHTML = '<i aria-hidden="true" class="fa fa-clipboard"></i>copied';
        setTimeout(function() {
            var copyTexta = document.getElementById(id+"_atag");
            copyTextval = '<i aria-hidden="true" class="fa fa-clipboard"></i>copy to clipboard';
            copyTexta.innerHTML = copyTextval;
        }, 1000);
    }catch(err){
        alert(err);
    }
};
Le.copyClipboardforms = function(id) {
    var copyText = document.getElementById(id);
    copyText.select();
    document.execCommand("Copy");
    var copyTexts = document.getElementById(id+"_atag");
    copyTexts.innerHTML = '<i aria-hidden="true" class="fa fa-clipboard"> </i> Copied';
    setTimeout(function() {
        var copyTexta = document.getElementById(id+"_atag");
        copyTextval = '<i aria-hidden="true" class="fa fa-clipboard"> </i> Copy Script';
        copyTexta.innerHTML = copyTextval;
    }, 1000);
};

Le.showBounceCallbackURL = function(modeEl) {
    var mode = mQuery(modeEl).val();
    if(mode != "le.transport.amazon" && mode != "le.transport.sendgrid_api" && mode != "le.transport.sparkpost" && mode != "le.transport.elasticemail") {
        mQuery('.transportcallback').addClass('hide');
        mQuery('.transportcallback_spam').addClass('hide');
        mQuery('#known-providers').addClass('hide');
        mQuery('#other-providers').removeClass('hide');
    } else {
        var urlvalue = mQuery('#transportcallback').val();
        var replaceval = "";
        mQuery('.transportcallback').removeClass('hide');
        mQuery('.transportcallback_spam').addClass('hide');
        var notificationHelpURL = "http://help.leadsengage.io/container/show/";
        mQuery("#callback_label_1").text("Bounce/ Spam Notification Callback URL");
        if (mode == "le.transport.amazon"){
            replaceval = "amazon";
            notificationHelpURL += "amazon-ses";
            mQuery("#callback_label_1").text("Bounce Notification Callback URL");
            mQuery('.transportcallback_spam').removeClass('hide');
        } else if(mode == "le.transport.sendgrid_api") {
            replaceval = "sendgrid_api";
            notificationHelpURL += replaceval;
        } else if (mode == "le.transport.sparkpost"){
            replaceval = "sparkpost";
            notificationHelpURL += replaceval;
        } else if (mode == "le.transport.elasticemail"){
            replaceval = "elasticemail";
            notificationHelpURL += replaceval
        }
        mQuery('#known-providers').removeClass('hide');
        mQuery('#other-providers').addClass('hide');
        mQuery('#notificationHelpURL').attr('href',notificationHelpURL);
        var toreplace = urlvalue.split('/');
        toreplace = toreplace[toreplace.length - 2];
        urlvalue = urlvalue.replace(toreplace,replaceval);
        mQuery('#transportcallback').val(urlvalue);
    }
    mQuery('#config_emailconfig_mailer_user').val('');
    mQuery('#config_emailconfig_mailer_password').val('');
    mQuery('#config_emailconfig_mailer_api_key').val('');
    if(mode != 'le.transport.vialeadsengage') {
        mQuery('#config_emailconfig_mailer_transport').val(mode);
    }
    mQuery('#config_emailconfig_mailer_amazon_region').val('');
    //Le.updateEmailStatus();
};


Le.configOnLoad = function (container){
    mQuery('#unsubscribe_text_div').find('.fr-element').attr('style','min-height:150px;');
    mQuery('#emailVerifyModel').on("hidden.bs.modal", function(){
        mQuery('#aws_email_verification').val('');
        mQuery('#user_email .help-block').addClass('hide');
        mQuery('#user_email .help-block').html("");
    });
    mQuery('.sender_profile_create_btn').click(function(e) {
        e.preventDefault();
        mQuery('#sender_profile_from_name_errors').html("");
        mQuery('#sender_profile_from_email_errors').html("");
        mQuery('#sender_profile_from_name').html("");
        mQuery('#sender_profile_from_email').html("");
    });
    mQuery('.sender_profile_verify_btn').click(function(e) {
        e.preventDefault();
        var currentLink = mQuery(this);
        var email = mQuery('#sender_profile_from_email').val();
        var fromname = mQuery('#sender_profile_from_name').val();
        var mailformat =/^([\w-\.]+@(?!gmail.com)(?!yahoo.com)(?!yahoo.co.in)(?!yahoo.in)(?!hotmail.com)(?!yahoo.co.in)(?!aol.com)(?!abc.com)(?!xyz.com)(?!pqr.com)(?!rediffmail.com)(?!live.com)(?!outlook.com)(?!me.com)(?!msn.com)(?!ymail.com)([\w-]+\.)+[\w-]{2,4})?$/;
        if(fromname == ""){
            mQuery('#sender_profile_from_name_errors').html("From name cannot be empty!");
            return;
        }
        if (!email.match(mailformat)) {
            mQuery('#sender_profile_from_email_errors').html("Please provide your business email address, since free emails like gmail/ yahoo/ hotmail and a few more are not allowed due to DMARC rules.");
            return;
        }
       mQuery('#user_email .help-block').removeClass('hide');
       Le.activateButtonLoadingIndicator(currentLink);
        Le.ajaxActionRequest('email:senderProfileVerify', {'email': email,'name':fromname}, function(response) {
           Le.removeButtonLoadingIndicator(currentLink);
            if(response.success) {
               Le.redirectWithBackdrop(response.redirect);
               mQuery('#emailVerifyModel').addClass('hide');
            } else{
                mQuery('#sender_profile_from_email_errors').html(response.message);
            return;
            }
        });
    });

    mQuery('.remove_sender_profile_btn').click(function(e) {
        e.preventDefault();
        var currentLink = mQuery(this);
        var currentrow=currentLink.closest("tr");
        var fromemail = currentrow.find(".sender_profile_from_email_col");
        var email = fromemail.text();
        Le.activateButtonLoadingIndicator(currentLink);
        Le.ajaxActionRequest('email:deleteSenderProfile', {'email': email}, function(response) {
            Le.removeButtonLoadingIndicator(currentLink);
            if(response.success) {
               // currentrow.remove();
                Le.redirectWithBackdrop(response.redirect);
            } else {
                mQuery('#sender_profile_errors').html(response.message);
                return;
            }
        });
    });

    mQuery('.verify_sender_profile_btn').click(function(e) {
        e.preventDefault();
        var currentLink = mQuery(this);
        var currentrow=currentLink.closest("tr");
        var fromemail = currentrow.find(".sender_profile_from_email_col");
        var email = fromemail.text();
        email=email.trim();
        var fromname = currentrow.find(".sender_profile_from_name_col");
        var name = fromname.text();
        name=name.trim();
        var id=currentLink.attr('id');
        id = id.substr(-1);
        Le.activateButtonLoadingIndicator(currentLink);
        Le.ajaxActionRequest('email:reVerifySenderProfile', {'email': email,'name':name}, function(response) {
            Le.removeButtonLoadingIndicator(currentLink);
            if(response.response != "") {
                mQuery('#sender_profile_errors').html(response.response);
            }else{
                Le.changeSenderProfileStatusFrontEnd(true,email,id);
               // Le.redirectWithBackdrop(response.redirect);
            }
        });
    });
   Le.hideFlashMessage();
   Le.smsconfigOnLoad(container);
   mQuery('a[data-toggle="tab"]').off().on('click', function() {
       Le.hideConfigButtonsByTabSelect(mQuery(this).attr('href'));
   });
   var selectedConfigtab=mQuery("ul[class='list-group list-group-tabs']").find("li[class='list-group-item in active']").find("a[class='steps']");
   //Le.hideConfigButtonsByTabSelect(selectedConfigtab.attr('href'));
    mQuery('#config_buttons_sendingdomain_toolbar').click(function(e) {
        e.preventDefault();
        mQuery('#sender_domain_name').val('');
        mQuery('#sender_domain_name_errors').html('');
        mQuery('#sendingdomaincreateModel').modal('show');
    });
    mQuery('.sender_domain_add_btn').click(function(e) {
        e.preventDefault();
        var currentLink = mQuery(this);
        var $url   = window.location.href;
        var domain = mQuery('#sender_domain_name').val();
        if(domain == ""){
            mQuery('#sender_domain_name_errors').html("domain name can't be empty !");
            return;
        }
        if (domain.indexOf('www') != -1 || domain.indexOf('WWW') != -1 || domain.indexOf('http') != -1 || domain.indexOf('https') != -1 || domain.indexOf('empty') != -1){
            mQuery('#sender_domain_name_errors').html("You can't use reserved keys like www,http,https");
            return;
        }
        if ($url.indexOf('//cops.') == -1){
            if(domain.indexOf('anyfunnels.net') != -1 || domain.indexOf('anyfunnels.com') != -1 || domain.indexOf('anyfunnels.io') != -1){
                mQuery('#sender_domain_name_errors').html("You can't add reserved domains");
                return;
            }
        }

        Le.activateButtonLoadingIndicator(currentLink);
        Le.ajaxActionRequest('email:senderDomainCreate', {'domain': domain}, function(response) {
            Le.removeButtonLoadingIndicator(currentLink);
            if(response.success) {
                //Le.redirectWithBackdrop(response.redirect);
                mQuery('.sending-domain-container').html(response.content);
                mQuery('#sendingdomaincreateModel').modal('hide');
                Le.refreshSendingDomainListeners();
                Le.deactivateBackgroup();
            } else{
                mQuery('#sender_domain_name_errors').html(response.message);
                return;
            }
        });
    });
    Le.refreshSendingDomainListeners();
}
Le.refreshSendingDomainListeners=function(){
    mQuery('.verify-sending-domain').click(function(e) {
        e.preventDefault();
        mQuery('#sendingdomainverifyModel .spf-validation-status').html('<i class="fa fa-spin fa-spinner"></i>');
        mQuery('#sendingdomainverifyModel .dkim-validation-status').html('<i class="fa fa-spin fa-spinner"></i>');
        mQuery('#sendingdomainverifyModel .dmark-validation-status').html('<i class="fa fa-spin fa-spinner"></i>');
        //mQuery('#sendingdomainverifyModel .tracking-validation-status').html('<i class="fa fa-spin fa-spinner"></i>');
        var currentLink = mQuery(this);
        var domain=currentLink.attr("data-domain");
        mQuery('#sendingdomainverifyModel').modal('show');
        Le.ajaxActionRequest('email:senderDomainValidation', {'domain': domain}, function(response) {
            if(response.success) {
                Le.deactivateBackgroup();
                mQuery('.sending-domain-container').html(response.content);
                if(response.spf_check){
                  mQuery('#sendingdomainverifyModel .spf-validation-status').html('<i class="fa fa-check-circle on-left" style="color:green;"></i> Valid');
                }else{
                    mQuery('#sendingdomainverifyModel .spf-validation-status').html('<i class="fa fa-times-circle  on-left" style="color:red;"></i> Error: Could not find the valid TXT record, <a onclick="Le.openSendingDomainHelp(\'spf\')" style="color:blue;text-decoration: underline;" data-help="spf">Click here</a> to know more about SPF configuration.');
                }
                if(response.dkim_check){
                    mQuery('#sendingdomainverifyModel .dkim-validation-status').html('<i class="fa fa-check-circle on-left" style="color:green;"></i> Valid');
                }else{
                    mQuery('#sendingdomainverifyModel .dkim-validation-status').html('<i class="fa fa-times-circle  on-left" style="color:red;"></i> Error: Could not find the valid DKIM record, <a onclick="Le.openSendingDomainHelp(\'dkim\')" style="color:blue;text-decoration: underline;">Click here</a> to know more about DKIM configuration.');
                }
                if(response.dmark_check){
                    mQuery('#sendingdomainverifyModel .dmark-validation-status').html('<i class="fa fa-check-circle on-left" style="color:green;"></i> Valid');
                }else{
                    mQuery('#sendingdomainverifyModel .dmark-validation-status').html('<i class="fa fa-times-circle  on-left" style="color:red;"></i> Error: Could not find the valid DMARC record');
                }
                /*if(response.tracking_check){
                    mQuery('#sendingdomainverifyModel .tracking-validation-status').html('<i class="fa fa-check-circle on-left" style="color:green;"></i> Valid');
                }else{
                    mQuery('#sendingdomainverifyModel .tracking-validation-status').html('<i class="fa fa-times-circle  on-left" style="color:red;"></i> Error: Could not find the valid Tracking record, <a onclick="Le.openSendingDomainHelp(\'tracking\')" style="color:blue;text-decoration: underline;">Click here</a> to know more about Tracking configuration.');
                }*/
                Le.refreshSendingDomainListeners();
            }
        });
    });

    mQuery('.remove-sending-domain').click(function(e) {
        e.preventDefault();
        var currentLink = mQuery(this);
        var domain=currentLink.attr("data-domain");
        Le.activateButtonLoadingIndicator(currentLink);
        Le.ajaxActionRequest('email:senderDomainDelete', {'domain': domain}, function(response) {
            Le.removeButtonLoadingIndicator(currentLink);
            if(response.success) {
                Le.deactivateBackgroup();
            } else{
                alert(response.message);
            }
            mQuery('.sending-domain-container').html(response.content);
            Le.refreshSendingDomainListeners();
        });
    });
    mQuery('.default-sending-domain').click(function(e) {
        e.preventDefault();
        var currentLink = mQuery(this);
        var domain=currentLink.attr("data-domain");
        Le.activateButtonLoadingIndicator(currentLink);
        Le.ajaxActionRequest('email:senderDomainDefault', {'domain': domain}, function(response) {
            Le.removeButtonLoadingIndicator(currentLink);
            if(response.success) {
                Le.deactivateBackgroup();
                mQuery('.sending-domain-container').html(response.content);
                Le.refreshSendingDomainListeners();
            } else{
                alert(response.message);
            }
        });
    });
    mQuery('.sending-domain-container .ecircle').click(function(e) {
        e.preventDefault();
        var currentLink = mQuery(this);
        var parentEl=currentLink.parent();
        var help=parentEl.attr("data-help");
        Le.openSendingDomainHelp(help);
    });
}
Le.openSendingDomainHelp = function (help){
    mQuery('#sendingdomain'+help+'HelpModel').modal('show');
};
Le.updateEmailStatus = function(){
    mQuery('#config_emailconfig_email_status').val('InActive');
    mQuery('#config_emailconfig_email_status').removeClass('status_success');
    mQuery('#config_emailconfig_email_status').addClass('status_fail');
    mQuery('#config_emailconfig_email_status').css('border-color','#ff0000');
    Le.updateSenderProfileStatus();
}
Le.updateSenderProfileStatus = function(){
    Le.ajaxActionRequest('email:DisableAllSenderProfile', {}, function(response) {
        if(response.success) {
            mQuery('.pending_verify_button').html("Pending");
            mQuery('.pending_verify_button').css('background','#ff4d4d');
            mQuery('.verify_sender_profile_btn').removeClass('hide');
            return;
        }
    });
}
Le.changeSenderProfileStatusFrontEnd = function(isActive, fromemail,id){
    if (isActive) {
        mQuery('#pending-verified-button-' + id).html("Verified");
        mQuery('#pending-verified-button-' + id).css('background', '#39ac73');
        mQuery('#re-verify-button-' + id).addClass('hide');
    } else {
        mQuery('#pending-verified-button-' + id).html("Pending");
        mQuery('#pending-verified-button-' + id).css('background', '#ff4d4d');
        mQuery('#re-verify-button-' + id).removeClass('hide');
    }
    return;
}

Le.hideConfigButtonsByTabSelect=function(tabSelect){
    if(tabSelect == '#sendingdomain_config'){
        mQuery("#config_buttons_cancel_toolbar").addClass("hide");
        mQuery("#config_buttons_save_toolbar").addClass("hide");
        mQuery("#config_buttons_apply_toolbar").addClass("hide");
        mQuery(".sendingdomain_config").removeClass("hide");
    }else{
        mQuery("#config_buttons_cancel_toolbar").removeClass("hide");
        mQuery("#config_buttons_save_toolbar").removeClass("hide");
        mQuery("#config_buttons_apply_toolbar").removeClass("hide");
        mQuery("#config_buttons_sendingdomain_toolbar").addClass("hide");
    }
}