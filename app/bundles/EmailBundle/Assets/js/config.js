Mautic.testMonitoredEmailServerConnection = function(mailbox) {
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

    Mautic.ajaxActionRequest('email:testMonitoredEmailServerConnection', data, function(response) {
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

Mautic.testEmailServerConnection = function(sendEmail) {
    var toemail = "";
    var trackingcode = "";
    var additionalinfo = "";
    if(typeof mQuery('#config_trackingconfig_emailInstructionsto') !== "undefined" && mQuery('#config_trackingconfig_emailInstructionsto') != null){
        toemail = mQuery('#config_trackingconfig_emailInstructionsto').val();
        trackingcode = mQuery('#script_preTag').html();
        additionalinfo = mQuery('#config_trackingconfig_emailAdditionainfo').val();
    }
    var data = {
        amazon_region: mQuery('#config_emailconfig_mailer_amazon_region').val(),
        api_key:       mQuery('#config_emailconfig_mailer_api_key').val(),
        authMode:      mQuery('#config_emailconfig_mailer_auth_mode').val(),
        encryption:    mQuery('#config_emailconfig_mailer_encryption').val(),
        from_email:    mQuery('#config_emailconfig_mailer_from_email').val(),
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

    mQuery('#mailerTestButtonContainer .fa-spinner').removeClass('hide');

    Mautic.ajaxActionRequest('email:testEmailServerConnection', data, function(response) {
        var theClass = (response.success) ? 'has-success' : 'has-error';
        var theMessage = response.message;
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

    });
};

Mautic.copytoClipboardforms = function(id) {
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
};

Mautic.showBounceCallbackURL = function(modeEl) {
    var mode = mQuery(modeEl).val();
    if(mode != "mautic.transport.amazon" && mode != "mautic.transport.sendgrid_api" && mode != "mautic.transport.sparkpost" && mode != "mautic.transport.elasticemail") {
        mQuery('.transportcallback').addClass('hide');
        mQuery('.transportcallback_spam').addClass('hide');
    } else {
        var urlvalue = mQuery('#transportcallback').val();
        var replaceval = "";
        mQuery('.transportcallback').removeClass('hide');
        mQuery('.transportcallback_spam').addClass('hide');
        var notificationHelpURL = "http://help.leadsengage.io/container/show/";
        if (mode == "mautic.transport.amazon"){
            replaceval = "amazon";
            notificationHelpURL += "amazon-ses";
            mQuery('.transportcallback_spam').removeClass('hide');
        } else if(mode == "mautic.transport.sendgrid_api") {
            replaceval = "sendgrid_api";
            notificationHelpURL += replaceval;
        } else if (mode == "mautic.transport.sparkpost"){
            replaceval = "sparkpost";
            notificationHelpURL += replaceval;
        } else if (mode == "mautic.transport.elasticemail"){
            replaceval = "elasticemail";
            notificationHelpURL += replaceval
        }
        mQuery('#notificationHelpURL').attr('href',notificationHelpURL);
        var toreplace = urlvalue.split('/');
        toreplace = toreplace[toreplace.length - 2];
        urlvalue = urlvalue.replace(toreplace,replaceval);
        mQuery('#transportcallback').val(urlvalue);
    }
    mQuery('#config_emailconfig_mailer_user').val('');
    mQuery('#config_emailconfig_mailer_password').val('');
    mQuery('#config_emailconfig_mailer_api_key').val('');
};


Mautic.configOnLoad = function (container) {
    mQuery('#emailVerifyModel').on("hidden.bs.modal", function(){
        mQuery('#aws_email_verification').val('');
        mQuery('#user_email .help-block').addClass('hide');
        mQuery('#user_email .help-block').html("");
    });
    mQuery('.aws-verification-btn').click(function(e) {
        e.preventDefault();
        var currentLink = mQuery(this);
        var email = mQuery('#aws_email_verification').val();
        mQuery('#user_email .help-block').removeClass('hide');
       Mautic.activateButtonLoadingIndicator(currentLink);
        Mautic.ajaxActionRequest('email:awsEmailFormValidation', {'email': email}, function(response) {
           Mautic.removeButtonLoadingIndicator(currentLink);

            if(response.success) {
                Mautic.redirectWithBackdrop(response.redirect);
                mQuery('#emailVerifyModel').addClass('hide');
            } else {
                document.getElementById('errors').innerHTML=response.message;
                return;
            }
        });
    });

    mQuery('.delete_aws_verified_emails').click(function(e) {
        e.preventDefault();
        var currentLink = mQuery(this);
        var spans = currentLink.closest("tr").find("span");
        var email = spans.eq(0).text();

        Mautic.activateButtonLoadingIndicator(currentLink);
        Mautic.ajaxActionRequest('email:deleteAwsVerifiedEmails', {'email': email}, function(response) {
            Mautic.removeButtonLoadingIndicator(currentLink);
            if(response.success) {
                Mautic.redirectWithBackdrop(response.redirect);
            } else {
                document.getElementById('errors').innerHTML=response.message;
                return;
            }
        });
    });
}