Le.configOnLoad= function(container){
    var status=mQuery('#config_smsconfig_sms_status').val();
    if(status == "InActive"){
        mQuery('#config_smsconfig_sms_status').removeClass('status_success');
         mQuery('#config_smsconfig_sms_status').addClass('status_fail');
    }
}
Le.testSmsServerConnection = function(sendSMS,mobile) {
    var data = {
        transport: mQuery('#config_smsconfig_sms_transport').val(),
        url:       mQuery('#config_smsconfig_account_url').val(),
        senderid:  mQuery('#config_smsconfig_account_sender_id').val(),
        apiKey:    mQuery('#config_smsconfig_account_api_key').val(),
        username:  mQuery('#config_smsconfig_account_auth_token').val(),
        password:  mQuery('#config_smsconfig_account_sid').val(),
        fromnumber:mQuery('#config_smsconfig_sms_from_number').val(),
        mobile    : mobile,
    };

    mQuery('#smsTestButtonContainer .fa-spinner').removeClass('hide');

    Le.ajaxActionRequest('sms:testSmsServerConnection', data, function(response) {
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
            mQuery('#config_smsconfig_sms_status').css('background-color','#008000');
            mQuery('#config_smsconfig_sms_status').css('border-color','#008000');
        }else if(theClass == 'has-error'){
            mQuery('#config_smsconfig_sms_status').val('InActive');
            mQuery('#config_smsconfig_sms_status').css('background-color','#ff0000');
            mQuery('#config_smsconfig_sms_status').css('border-color','#ff0000');

        }

    });
};

Le.updateTextMessageStatus = function(){
    mQuery('#config_smsconfig_sms_status').val('InActive');
    mQuery('#config_smsconfig_sms_status').css('background-color','#ff0000');
    mQuery('#config_smsconfig_sms_status').css('border-color','#ff0000');
}

Le.testSmsConnection = function() {
    var mobile = mQuery('#sms_config_mobile').val();
    mQuery('#sms_config_mobile').val("");
    if(mobile == ""){
        mQuery('#sms_config_mobile_errors').removeClass('hide');
        mQuery('#sms_config_mobile_errors').html("Mobile Number cannot be empty!");
        return;
    }else {
        mQuery('#sms_config_mobile_errors').addClass('hide');
    }
    mQuery('#activateSmsModel').modal('hide');
    Le.testSmsServerConnection(true,mobile)
}