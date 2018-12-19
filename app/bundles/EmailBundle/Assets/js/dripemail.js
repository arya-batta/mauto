
Le.dripemailOnLoad = function (container, response) {
    Le.initSelectBeeTemplate(mQuery('#emailform_template'),'dripemail');
    Le.initEmailDynamicContent();
    Le.leadlistOnLoad(container);
    mQuery('#unsubscribe_text_div').find('.fr-element').attr('style','min-height:100px;');
    /*mQuery(".ui-tabs-panel").each(function(i){
        var totalSize = mQuery(".ui-tabs-panel").size() - 1;
        if (i != totalSize) {
            if(i == 0){
                //mQuery("#ui-tab-header1").addClass('ui-tabs-selected ui-state-active');
            }
            next = i + 2;
        }
        if (i != 0) {
            prev = i;
        }
    });*/
    var url = window.location.href;
    if (url.indexOf('emails/edit') != -1) {
        if (mQuery('textarea.builder-html').val() != 'false' && mQuery('textarea.builder-html').val().indexOf("false") < 0 && mQuery('textarea.builder-html').val() != '') {
            //Le.showdripEmailpreviewoftemplate(mQuery('textarea.builder-html').val());
        }
    }
    mQuery('#dripemail_advance_editor .chosen-container-single').removeClass('hide');
    mQuery('.next-tab, .prev-tab, .ui-state-default').click(function() {
        var selectrel = mQuery(this).attr("rel");
        mQuery('#dripEmail_PublicName .help-block').addClass('hide').html("");
        mQuery('#dripEmail_PublicName').removeClass('has-success has-error');
        if(mQuery('#dripemailform_name').val() == "") {
            mQuery('#dripEmail_PublicName').removeClass('has-success has-error').addClass('has-error');
            mQuery('#dripEmail_PublicName .custom-help').removeClass('hide').html("Campaign name can't be empty");
            return;
        } else{
            mQuery('#dripEmail_PublicName').removeClass('has-success has-error');
            mQuery('#dripEmail_PublicName .custom-help').html("");
        }
        /*if(!mQuery('#dripemail_advance_editor').hasClass('hide')) {
            if (selectrel == 3 && !mQuery(this).hasClass('ui-state-default')) {
                Le.launchBeeEditor('dripemail', 'email');
                return;
            }
        }*/
        mQuery(".ui-tabs-panel").addClass('ui-tabs-hide');
        mQuery("#fragment-"+selectrel).removeClass('ui-tabs-hide');
        mQuery(".ui-state-default").removeClass('ui-tabs-selected ui-state-active');
        mQuery("#ui-tab-header"+selectrel).addClass('ui-tabs-selected ui-state-active');

    });
    var url = window.location.href;
    if (url.indexOf('drip/edit') != -1) {
        Le.loadEmailsinDripStatCounts();
    }
    Le.reorderEmailsData(container);
    Le.getTokens('email:getBuilderTokens', function(tokens) {
        /**  mQuery.each(tokens, function(k,v){
            if (k.match(/assetlink=/i) && v.match(/a:/)){
                delete tokens[k];
            } else if (k.match(/pagelink=/i) && v.match(/a:/)){
                delete tokens[k];
            }
            if(tokens[k]=='Title' || tokens[k]=='First Name' || tokens[k]=='Last Name' || tokens[k]=='Company'){

            } else {
                delete tokens[k];
            }
        }); */

        tokens = [];
        tokens['{leadfield=title}']       = 'Title';
        tokens['{leadfield=firstname}']   = 'First Name';
        tokens['{leadfield=lastname}']    = 'Last Name';
        tokens['{leadfield=company_new}'] = 'Company';
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
            var subValue= mQuery('#emailform_subject').val();
            if(subValue == ''){
                mQuery("#emailform_subject").val(value);
            } else {
                if(subValue.includes(value)){

                } else {
                    //subValue+=value;
                    //mQuery("#emailform_subject").html.insert(value);
                    var cursorPos = mQuery('#emailform_subject').prop('selectionStart');
                    var v = subValue;
                    var textBefore = v.substring(0,  cursorPos);
                    var textAfter  = v.substring(cursorPos, v.length);

                    mQuery('#emailform_subject').val(textBefore + value + textAfter);
                }

            }
        });
    });
    mQuery('[data-verified-emails]').click(function(e) {
        e.preventDefault();
        var currentLink = mQuery(this);
        var email = currentLink.attr('data-verified-emails');
        var name = currentLink.attr('data-verified-fromname');
        mQuery("#dripemailform_fromAddress").val(email);
        mQuery("#dripemailform_fromName").val(name);
    });
    Le.filterBeeTemplates= function () {
        d = document.getElementById("filters").value;
        if(d == "all"){
            mQuery('.bee-template').removeClass('hide');
        } else {
            mQuery('.bee-template').addClass('hide');
            mQuery('.'+d).removeClass('hide');
        }
    };
    Le.removeActionButtons();
    if(mQuery('#drip-email-delay').length){
        mQuery('#drip-email-delay .chosen-single').addClass('chosen-dripemail-single');
    }
    Le.loadDripEmailStatCounts();
    Le.hideFlashMessage();
   /* mQuery(function() {
        mQuery('#flashes').delay(2000).fadeIn('normal', function() {
            mQuery(this).delay(1500).fadeOut();
            mQuery('#flashes').addClass('hide');
        });
    });*/

    if(mQuery('#dripEmail_PublicName').hasClass('has-error') || mQuery('#drip_daysEmailSend').hasClass('has-error')){
        mQuery('#ui-tab-header3').addClass('ui-tabs-selected ui-state-active');
        mQuery('#ui-tab-header2').removeClass('ui-tabs-selected ui-state-active');
        mQuery('#fragment-1').addClass('ui-tabs-hide');
        mQuery('#fragment-2').addClass('ui-tabs-hide');
        mQuery('#fragment-3').removeClass('ui-tabs-hide');
    }

};

Le.loadDripEmailScheduledStatCounts = function(){
    if (mQuery('table.email-list').length) {
        mQuery('td.drip-col-stats').each(function () {
            var id = mQuery(this).attr('data-stats');
            // Process the request one at a time or the xhr will cancel the previous
            Le.ajaxActionRequest(
                'email:getDripEmailScheduledCountStats',
                {id: id},
                function (response) {
                    if (response.success && mQuery('#scheduled-count-' + id + ' div').length) {
                        mQuery('#scheduled-count-' + id + ' > a').html(response.scheduledcount);
                    }
                },
                false,
                true
            );

        });
    }
}

Le.loadDripEmailStatCounts = function(){
    if (mQuery('table.email-list').length) {
        mQuery('tr.drip-email-col-stats').each(function () {
            var id = mQuery(this).attr('data-stats');
            // Process the request one at a time or the xhr will cancel the previous
            Le.ajaxActionRequest(
                'email:getDripEmailStats',
                {id: id},
                function (response) {
                    if (response.success && mQuery('#drip-sent-count-' + id + ' div').length) {
                        mQuery('#drip-sent-count-' + id + ' > a').html(response.sentcount);
                        mQuery('#drip-read-count-' + id + ' > a').html(response.readcount);
                        mQuery('#drip-click-count-' + id + ' > a').html(response.clickcount);
                        mQuery('#drip-unsubscribe-count-' + id + ' > a').html(response.unsubscribe);
                        mQuery('#drip-lead-count-' + id + ' > a').html(response.leadcount);
                    }
                },
                false,
                true
            );

        });
    }
}

Le.loadEmailsinDripStatCounts = function(){
    if (mQuery('table.dripemail-list').length) {
        mQuery('tr.drip-emailcol-stats').each(function () {
            var id = mQuery(this).attr('data-stats');
            // Process the request one at a time or the xhr will cancel the previous
            Le.ajaxActionRequest(
                'email:getEmailsinDripCountStats',
                {id: id},
                function (response) {
                    if (response.success && mQuery('#sent-count-' + id + ' div').length) {
                        /* if (response.pending) {
                             mQuery('#pending-' + id + ' > a').html(response.pending);
                             mQuery('#pending-' + id).removeClass('hide');
                         }*/

                        /*if (response.queued) {
                            mQuery('#queued-' + id + ' > a').html(response.queued);
                            mQuery('#queued-' + id).removeClass('hide');
                        }*/

                        mQuery('#sent-count-' + id + ' > a').html(response.sentCount);
                        mQuery('#read-count-' + id + ' > a').html(response.readCount);
                        mQuery('#read-percent-' + id + ' > a').html(response.readPercent);
                        mQuery('#scheduled-count-' + id + ' > a').html(response.scheduledcount);
                    }
                },
                false,
                true
            );

        });
    }
}

Le.reorderEmailsData = function(container){
    if (mQuery(container + ' .dripemail-list').length) {
        var bodyOverflow = {};
        mQuery(container + ' .dripemail-list tbody').sortable({
            handle: '.fa-ellipsis-v',
            helper: function(e, ui) {
                ui.children().each(function() {
                    mQuery(this).width(mQuery(this).width());
                });

                // Fix body overflow that messes sortable up
                bodyOverflow.overflowX = mQuery('body').css('overflow-x');
                bodyOverflow.overflowY = mQuery('body').css('overflow-y');
                mQuery('body').css({
                    overflowX: 'visible',
                    overflowY: 'visible'
                });

                return ui;
            },
            scroll: false,
            axis: 'y',
            containment: container + ' .dripemail-list',
            stop: function(e, ui) {
                // Restore original overflow
                mQuery('body').css(bodyOverflow);
                var order = 0;
                mQuery('tr.drip-emailcol-stats').each(function () {
                    var id = mQuery(this).attr('data-stats');
                    order = order + 1;
                    //alert(order);
                    //alert(id);
                    // Process the request one at a time or the xhr will cancel the previous
                    Le.ajaxActionRequest(
                        'email:reorderEmails',
                        {
                            id: id,
                            order:order,
                            totalsize:mQuery('tr.drip-emailcol-stats').length,
                        },
                        function (response) {
                            if (response.success) {
                                //if(mQuery('tr.drip-emailcol-stats').length == response.orderChanged) {
                                //    alert(response.orderChanged);
                                //Le.setFlashes(response.flashes);
                                //}
                            }
                        },
                        false,
                        true
                    );

                });
                // Get the page and limit
                /*mQuery.ajax({
                    type: "POST",
                    url: leAjaxUrl + "?action=lead:reorder&limit=" + mQuery('.pagination-limit').val() + '&page=' + mQuery('.pagination li.active a span').first().text(),
                    data: mQuery(container + ' .leadfield-list tbody').sortable("serialize")});*/
            }
        });
    }
}

Le.setValueforNewButton = function (value,ele){
    mQuery('#new-drip-email').attr('value',value);
    mQuery('.editor_select').addClass('editor_layout').removeClass('editor_select');
    mQuery(ele).addClass('editor_select').removeClass('editor_layout');
}

Le.openDripEmailEditor = function (){
    var editorname = mQuery('#new-drip-email').attr('value');
    if(editorname == "basic_editor"){
        mQuery('#emailform_customHtml').val('');
    }
    editorname = "dripemail_"+editorname;
    mQuery('#drip-email-container').removeClass('hide');
    mQuery('#drip-email-list-container').addClass('hide');
    mQuery('.dripemail_content').addClass('hide');
    mQuery('#'+editorname).removeClass('hide');
    mQuery('.newbutton-container').addClass('hide');
    mQuery('.saveclose-container').removeClass('hide');
    Le.showChangeThemeWarning = true;
}

Le.saveDripEmail = function (dripEntity) {
    var subject     = mQuery('#emailform_subject').val();
    var previewText = mQuery('#emailform_previewText').val();
    var customHtml  = mQuery('#emailform_customHtml').val();
    var beeJson     = mQuery('#emailform_beeJSON').val();
    if(!Le.isValidEmailSave()){
        return false;
    }
    var editorname = mQuery('#new-drip-email').attr('value');
    if(editorname == "basic_editor") {
        beeJson = "";
    }
    var data = {
        subject     : subject,
        previewText : previewText,
        customHtml  : customHtml,
        beeJson     : beeJson,
        dripEntity  : dripEntity,
    };
    Le.ajaxActionRequest('email:createDripEmails', data, function (response) {
        if (response.success){
            Le.refreshDripEmailList(response.content);
            mQuery('#emailform_subject').val('');
            mQuery('#emailform_previewText').val('');
            mQuery('#emailform_customHtml').val('');
        }
    });
}

Le.updateDripEmail = function () {
    var Emailid = mQuery('#update-drip-email').attr('value');
    if(Emailid == ""){
        return false;
    }
    var subject     = mQuery('#emailform_subject').val();
    var previewText = mQuery('#emailform_previewText').val();
    var customHtml  = mQuery('#emailform_customHtml').val();
    var beeJson     = mQuery('#emailform_beeJSON').val();
    if(!Le.isValidEmailSave()){
        return false;
    }
    var data = {
        subject     : subject,
        previewText : previewText,
        customHtml  : customHtml,
        Emailid     : Emailid,
    };
    Le.ajaxActionRequest('email:updateDripEmails', data, function (response) {
        if (response.success){
            Le.refreshDripEmailList(response.content);
            mQuery('#emailform_subject').val('');
            mQuery('#emailform_previewText').val('');
            mQuery('#emailform_customHtml').val('');
        }
    });
}

Le.isValidEmailSave = function(){
    var subject     = mQuery('#emailform_subject').val();
    if(subject == ''){
        mQuery('#DripEmail_Subject').removeClass('has-success has-error').addClass('has-error');
        mQuery('#DripEmail_Subject .custom-help').removeClass('hide').html("Subject can't be empty");
        return false;
    }
    return true;
}

Le.removeEmailfromDrip = function(Emailid, DripEmail){
    if(confirm(Le.translate('You are removing one of the emails in this drip campaign. Existing scheduled leads would no longer receive this email.'))){
        var data = {
            emailId   : Emailid,
            dripEmail : DripEmail
        };
        Le.ajaxActionRequest('email:deleteDripEmails', data, function (response) {
            if (response.success){
                Le.refreshDripEmailList(response.content);
            }
        });
    } else {
        return;
    }
}

Le.refreshDripEmailList = function(content){
    mQuery('.newbutton-container').removeClass('hide');
    mQuery('.saveclose-container').addClass('hide');
    mQuery('.update-container').addClass('hide');
    mQuery('#drip-email-container').addClass('hide');
    mQuery('#drip-email-list-container').removeClass('hide');
    mQuery('#drip-email-list-container').html('');
    mQuery('#drip-email-list-container').html(content);
    Le.activateChosenSelect(mQuery('.dripemail_form_scheduleTime'),true);
    if(mQuery('#drip-email-delay').length){
        mQuery('#drip-email-delay .chosen-single').addClass('chosen-dripemail-single')
    }
    Le.loadEmailsinDripStatCounts();
    //Le.loadDripEmailStatCounts();
}

Le.editSelectedEmail = function(){
    mQuery('.newbutton-container').addClass('hide');
    mQuery('.saveclose-container').addClass('hide');
    mQuery('.update-container').removeClass('hide');
    mQuery('#drip-email-container').removeClass('hide');
    mQuery('#drip-email-list-container').addClass('hide');
    mQuery('#drip-email-list-container').html('');
    /*Le.activateChosenSelect(mQuery('.dripemail_form_scheduleTime'),true);
    if(mQuery('#drip-email-delay').length){
        mQuery('#drip-email-delay .chosen-single').addClass('chosen-dripemail-single')
    }
    Le.loadEmailStatCounts();*/
}
Le.updateFrequencyValue = function(EmailId){
    var frequencyUnit = mQuery('#drip_emailform_scheduleTime-'+EmailId).val();
    Le.updateDripEmailFrequency(frequencyUnit,EmailId);
}

Le.updateDripEmailFrequency = function(frequencyUnit, EmailId){
    var frequencyValue = mQuery('#drip-email-frequency-value-'+EmailId).val();
    var data = {
        frequencyUnit   : frequencyUnit,
        frequencyValue  : frequencyValue,
        EmailId         : EmailId
    };
    Le.ajaxActionRequest('email:updateDripEmailFrequency', data, function (response) {
        if (response.success){
            /*mQuery('.newbutton-container').removeClass('hide');
            mQuery('.saveclose-container').addClass('hide');
            mQuery('#drip-email-container').addClass('hide');
            mQuery('#drip-email-list-container').removeClass('hide');
            mQuery('#drip-email-list-container').html('');
            mQuery('#drip-email-list-container').html(response.content);
            Le.loadEmailStatCounts();*/
        }
    });
}

Le.allowEditEmailfromDrip = function (emailId){
    var data = {
        Emailid    : emailId
    };
    Le.ajaxActionRequest('email:getEmailDetails', data, function (response) {
        if (response.success){
            Le.editSelectedEmail();
            mQuery('#update-drip-email').attr('value',response.Emailid);
            mQuery('#emailform_subject').val(response.subject);
            mQuery('#emailform_previewText').val(response.preview);
            mQuery('#emailform_customHtml').val(response.emailcontent);
            if(response.emailcontent != ""){
                mQuery('.dripemail_content .fr-placeholder').attr('style','display:none;');
            }
            mQuery('.dripemail_content .fr-element').html(response.emailcontent);
            mQuery('#emailform_beeJSON').val(response.beeJSON);
            if(!response.isBeeEditor){
                mQuery('#dripemail_basic_editor').removeClass('hide');
                mQuery('#dripemail_advance_editor').addClass('hide');
            } else {
                Le.showdripEmailpreviewoftemplate(response.emailcontent);
                mQuery('#dripemail_basic_editor').addClass('hide');
                // mQuery('#dripemail_advance_editor').removeClass('hide');
            }
        }
    });
}

Le.openBluePrintPage = function(){
    mQuery('.drip-blue-prints').removeClass('hide');
    mQuery('.dripemail-body').addClass('hide');
}

Le.closeBluePrintPage = function(){
    mQuery('.drip-blue-prints').addClass('hide');
    mQuery('.dripemail-body').removeClass('hide');
}
Le.useBluePrintDrip = function(ele){
    var dripId = mQuery(ele).attr('value');
    var currentId = mQuery(ele).attr('dripvalue');
    //alert('dripId: '+dripId);
    var data = {
        dripId    : dripId,
        currentId : currentId
    };
    Le.ajaxActionRequest('email:createBlueprintEmails', data, function (response) {
        if (response.success){
            Le.closeBluePrintPage();
            Le.refreshDripEmailList(response.content);
            Le.reorderEmailsData('#app-content');
        }
    });
}
Le.ClearScheduleTimeWidget = function(){
    mQuery('#dripemailform_scheduleDate').val('');
}
Le.toggleDripEmailPublisedListVisibility = function () {
    setTimeout(function () {
        var warningtxt = "This campaign does not have any emails. Would you like to continue with activation?";
        if(mQuery('table.dripemail-list').length){
            warningtxt = "Are you sure you want to activate this Campaign? Recipients will start receiving the emails as you configured in this campaign.";
        }
        if (mQuery('#dripemailform_isPublished_1').prop('checked')) {
            if(confirm(Le.translate(warningtxt))){

            } else {
                mQuery('#dripemailform_isPublished_0').prop('checked',true);
                mQuery('#dripemailform_isPublished_1').prop('checked',false);
                Le.toggleYesNoButtonClass('dripemailform_isPublished_0');
                mQuery('#dripemailform_isPublished_1').parent().removeClass('active');
                mQuery('#dripemailform_isPublished_0').parent().addClass('active');
            }
        }
    }, 10);
};