/** SmsBundle **/
Le.smsOnLoad = function (container, response) {
    if (mQuery(container + ' #list-search').length) {
        Le.activateSearchAutocomplete('list-search', 'sms');
    }
    Le.getTokens('email:getBuilderTokens', function(tokens) {
       /** mQuery.each(tokens, function(k,v){
            if (k.match(/assetlink=/i) && v.match(/a:/)){
                delete tokens[k];
            } else if (k.match(/pagelink=/i) && v.match(/a:/)){
                delete tokens[k];
            }
            if(tokens[k]=='Title' || tokens[k]=='First Name' || tokens[k]=='Last Name' || tokens[k]=='Company' || tokens[k] == 'Mobile' || tokens[k] == 'Email' || tokens[k] == 'Lead Owner Name' || tokens[k] == 'Lead Owner Mobile' || tokens[k] == 'Lead Owner Email'){

            } else {
                delete tokens[k];
            }
        }); */
       tokens = [];
       tokens['{leadfield=title}']       = 'Title';
       tokens['{leadfield=firstname}']   = 'First Name';
       tokens['{leadfield=lastname}']    = 'Last Name';
       tokens['{leadfield=company_new}'] = 'Company';
       tokens['{leadfield=mobile}']      = 'Mobile';
       tokens['{leadfield=email}']       = 'Email';
       tokens['{lead_owner_name}']       = 'Lead Owner Name';
       tokens['{lead_owner_mobile}']     = 'Lead Owner Mobile';
       tokens['{lead_owner_email}']      = 'Lead Owner Email';
        var k, keys = [];
        for (k in tokens) {
            if (tokens.hasOwnProperty(k)) {
                keys.push(k);
            }
        }
        //keys.sort();
        //var tborder= "<table border='1' class='email-subject-table' ><tbody style='background-color:whitesmoke;'><tr>";
        var tborder= "<div border='1' class='email-subject-table' ><tbody style='background-color:whitesmoke;'><tr>";
        for (var i = 0; i < keys.length; i++) {
            var val = keys[i];
            var title = tokens[val];
            if(i % 3 == 0 && i  !=0 ){
                tborder+= "</tr><tr>";
            }
            var value= '<li class="email-subject-table-border"><a class="email-subject-token" id="insert-value" data-cmd="inserttoken" data-email-token="' + val + '" title="' + title + '">' + title +'</a></li>';
            tborder+= value;
        }
        tborder+= "</div>";


        mQuery('.insert-tokens').html(tborder);
        mQuery('[data-email-token]').click(function(e) {
            e.preventDefault();
            var currentLink = mQuery(this);
            var value = currentLink.attr('data-email-token');
            var subValue= mQuery('#sms_message').val();
            if(subValue == ''){
                mQuery("#sms_message").val(value);
            } else {
                if(subValue.includes(value)){

                } else {
                    //subValue+=value;
                    //mQuery("#sms_message").val(subValue);
                    var cursorPos = mQuery('#sms_message').prop('selectionStart');
                    var v = subValue;
                    var textBefore = v.substring(0,  cursorPos);
                    var textAfter  = v.substring(cursorPos, v.length);

                    mQuery('#sms_message').val(textBefore + value + textAfter);
                }

            }
        });
    });
    Le.removeActionButtons();
};
Le.CheckSMSStatus = function () {
    if(mQuery('.license-notifiation').hasClass('hide')) {
        Le.ajaxActionRequest('sms:smsstatus', {}, function (response) {
            if (response.success) {
                if (response.info != '' && response.isalertneeded != "true") {
                  //  if (mQuery('.license-notifiation').hasClass('hide')) {
                        mQuery('.license-notifiation').removeClass('hide');
                        mQuery('.license-notifiation').css('display','table');
                        mQuery('.license-notifiation').css('table-layout','fixed');
                        mQuery('.license-notifiation #license-alert-message').html('');
                        mQuery('.license-notifiation #license-alert-message').html(response.info);
                        mQuery('.button-notification').addClass('hide');
                        mQuery('#fixed-content').attr('style', 'margin-top:195px;');
                        Le.registerSmsDismissBtn();
                        Le.changeButtonPanelStyle(false);
                        Le.adJustFixedHeader(true);
                  //  }
                } else {
                    //mQuery('.license-notifiation').addClass('hide');
                    Le.adJustFixedHeader(false);
                }
            }
        });
    }
}
Le.registerSmsDismissBtn=function(){
    mQuery('.smsdismissbtn').click(function(e) {
        Le.closeLicenseButton();
        Le.adJustFixedHeader(true);
        Le.changeButtonPanelStyle(false);
        Le.ajaxActionRequest('subscription:notificationclosed', {'isalert_needed': "true"}, function(response) {
        });
    });
};
Le.selectSmsType = function(smsType) {
    if (smsType == 'list') {
        mQuery('#leadList').removeClass('hide');
        mQuery('#publishStatus').addClass('hide');
        mQuery('.page-header h3').text(leLang.newListSms);
    } else {
        mQuery('#publishStatus').removeClass('hide');
        mQuery('#leadList').addClass('hide');
        mQuery('.page-header h3').text(leLang.newTemplateSms);
    }

    mQuery('#sms_smsType').val(smsType);

    mQuery('body').removeClass('noscroll');

    mQuery('.sms-type-modal').remove();
    mQuery('.sms-type-modal-backdrop').remove();
};

Le.standardSmsUrl = function(options) {
    if (!options) {
        return;
    }

    var url = options.windowUrl;
    if (url) {
        var editEmailKey = '/sms/edit/smsId';
        if (url.indexOf(editEmailKey) > -1) {
            options.windowUrl = url.replace('smsId', mQuery('#campaignevent_properties_sms').val());
        }
    }

    return options;
};

Le.disabledSmsAction = function(opener) {
    if (typeof opener == 'undefined') {
        opener = window;
    }

    var sms = opener.mQuery('#campaignevent_properties_sms').val();

    var disabled = sms === '' || sms === null;

    opener.mQuery('#campaignevent_properties_editSmsButton').prop('disabled', disabled);
};