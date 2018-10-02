Mautic.testSmsServerConnection = function(sendSMS) {
    var data = {
        transport: mQuery('#config_smsconfig_sms_transport').val(),
        url:       mQuery('#config_smsconfig_account_url').val(),
        senderid:  mQuery('#config_smsconfig_account_sender_id').val(),
        apiKey:    mQuery('#config_smsconfig_account_api_key').val(),
        username:  mQuery('#config_smsconfig_account_auth_token').val(),
        password:  mQuery('#config_smsconfig_account_sid').val(),
        fromnumber:mQuery('#config_smsconfig_sms_from_number').val(),
    };

    mQuery('#smsTestButtonContainer .fa-spinner').removeClass('hide');

    Mautic.ajaxActionRequest('sms:testSmsServerConnection', data, function(response) {
        var theClass = (response.success) ? 'has-success' : 'has-error';
        var theMessage = response.message;
        if(!mQuery('#smsconfig #smsTestButtonContainer').is(':hidden')){
            mQuery('#smsconfig #smsTestButtonContainer').removeClass('has-success has-error').addClass(theClass);
            mQuery('#smsconfig #smsTestButtonContainer .help-block').html(theMessage);
            mQuery('#smsconfig #smsTestButtonContainer .fa-spinner').addClass('hide');
        }else{
            mQuery('#smsconfig #smsTestButtonContainer').removeClass('has-success has-error').addClass(theClass);
            mQuery('#smsconfig #smsTestButtonContainer .fa-spinner').addClass('hide');
        }
        if(theClass == 'has-success'){
            mQuery('#config_smsconfig_sms_status').val('Active');
        }else if(theClass == 'has-error'){
            mQuery('#config_smsconfig_sms_status').val('InActive');
        }

    });
};

Mautic.updateTextMessageStatus = function(){
    mQuery('#config_smsconfig_sms_status').val('InActive');
}