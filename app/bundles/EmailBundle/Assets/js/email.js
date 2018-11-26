/** EmailBundle **/
Le.emailOnLoad = function (container, response) {
    if (mQuery('#emailform_plainText').length) {
        // @todo initiate the token dropdown
        var plaintext = mQuery('#emailform_plainText');

        Le.initAtWho(plaintext, plaintext.attr('data-token-callback'));
        //builder disabled due to bee editor
      //  Le.initSelectTheme(mQuery('#emailform_template'));
        Le.initSelectBeeTemplate(mQuery('#emailform_template'),'email');
        Le.initEmailDynamicContent();

        Le.prepareVersioning(
            function (content) {
                console.log('undo');
            },
            function (content) {
                console.log('redo');
            }
        );

        // Open the builder directly when saved from the builder
        if (response && response.inBuilder) {
            Le.isInBuilder = true;
            Le.launchBuilder('emailform');
            Le.processBuilderErrors(response);
        }
    } else if (mQuery(container + ' #list-search').length) {
        Le.activateSearchAutocomplete('list-search', 'email');
    }
    mQuery('#unsubscribe_text_div').find('.fr-element').attr('style','min-height:100px;');
    mQuery(".ui-tabs-panel").each(function(i){
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
    });
    if(!mQuery("#fragment-3").hasClass('ui-tabs-hide')){
        mQuery('.sendEmailTest').removeClass('hide');
    } else {
        mQuery('.sendEmailTest').addClass('hide');
    }
    mQuery('.next-tab, .prev-tab, .ui-state-default').click(function() {
        var selectrel = mQuery(this).attr("rel");

        mQuery('#Email_TemplateName').removeClass('has-success has-error');
        mQuery('#Email_Subject').removeClass('has-success has-error');
        mQuery('#leadlists').removeClass('has-success has-error');
        mQuery('#Email_TemplateName .help-block').addClass('hide').html("");
        mQuery('#Email_Subject .help-block').addClass('hide').html("");
        mQuery('#leadlists .help-block').addClass('hide').html("");
        if(mQuery('#emailform_name').val() == "" && mQuery('#emailform_subject').val() == ""){
            mQuery('#Email_TemplateName').removeClass('has-success has-error').addClass('has-error');
            mQuery('#Email_TemplateName .custom-help').removeClass('hide').html("Campaign name can't be empty");
            mQuery('#Email_Subject').removeClass('has-success has-error').addClass('has-error');
            mQuery('#Email_Subject .custom-help').removeClass('hide').html("Subject can't be empty");
            return;
        } else{
            mQuery('#Email_TemplateName').removeClass('has-success has-error');
            mQuery('#Email_TemplateName .custom-help').html("");
            mQuery('#Email_Subject').removeClass('has-success has-error');
            mQuery('#Email_Subject .custom-help').html("");
        }

        if(mQuery('#emailform_name').val() == "") {
            mQuery('#Email_TemplateName').removeClass('has-success has-error').addClass('has-error');
            mQuery('#Email_TemplateName .custom-help').removeClass('hide').html("Campaign name can't be empty");
            return;
        } else{
            mQuery('#Email_TemplateName').removeClass('has-success has-error');
            mQuery('#Email_TemplateName .custom-help').html("");
        }

        if (mQuery('#emailform_subject').val() == ""){
            mQuery('#Email_Subject').removeClass('has-success has-error').addClass('has-error');
            mQuery('#Email_Subject .custom-help').removeClass('hide').html("Subject can't be empty");
            return;
        } else{
            mQuery('#Email_Subject').removeClass('has-success has-error');
            mQuery('#Email_Subject .custom-help').html("");
        }
        if(mQuery('#emailform_variantSettings_weight').val() == "" && mQuery('#emailform_variantSettings_winnerCriteria').val() == ""){
            mQuery('#Email_trafficweight').removeClass('has-success has-error').addClass('has-error');
            mQuery('#Email_trafficweight .help-block').html("Traffic Weight can't be empty");
            mQuery('#Email_winnercriteria').removeClass('has-success has-error').addClass('has-error');
            mQuery('#Email_winnercriteria .help-block').html("Winner Criteria can't be empty");
            return;
        } else {
            mQuery('#Email_trafficweight').removeClass('has-success has-error');
            mQuery('#Email_trafficweight .help-block').html("");
            mQuery('#Email_winnercriteria').removeClass('has-success has-error');
            mQuery('#Email_winnercriteria .help-block').html("");
        }

        if(mQuery('#emailform_variantSettings_weight').val() == "") {
            mQuery('#Email_trafficweight').removeClass('has-success has-error').addClass('has-error');
            mQuery('#Email_trafficweight .help-block').html("Traffic Weight can't be empty");
            return;
        }else{
            mQuery('#Email_trafficweight').removeClass('has-success has-error');
            mQuery('#Email_trafficweight .help-block').html("");
        }

        if(mQuery('#emailform_variantSettings_winnerCriteria').val() == "") {
            mQuery('#Email_winnercriteria').removeClass('has-success has-error').addClass('has-error');
            mQuery('#Email_winnercriteria .help-block').html("Winner Criteria can't be empty");
            return;
        } else {
            mQuery('#Email_winnercriteria').removeClass('has-success has-error');
            mQuery('#Email_winnercriteria .help-block').html("");
        }
        if(!mQuery('#email-advance-container').hasClass('hide')) {
            if (selectrel == 3 && !mQuery(this).hasClass('ui-state-default')) {
                Le.launchBeeEditor('emailform', 'email');
                return;
            }
            var url = window.location.href;
            if (url.indexOf('broadcasts/new') == -1) {
                if (mQuery('textarea.builder-html').val() != 'false' && mQuery('textarea.builder-html').val().indexOf("false") < 0 && mQuery('textarea.builder-html').val() != '') {
                    Le.showpreviewoftemplate(mQuery('textarea.builder-html').val());
                }
            }
        }
        mQuery(".ui-tabs-panel").addClass('ui-tabs-hide');
        mQuery("#fragment-"+selectrel).removeClass('ui-tabs-hide');
        mQuery(".ui-state-default").removeClass('ui-tabs-selected ui-state-active');
        mQuery("#ui-tab-header"+selectrel).addClass('ui-tabs-selected ui-state-active');
        if(selectrel == 3){
            mQuery('.sendEmailTest').removeClass('hide');
        } else {
            mQuery('.sendEmailTest').addClass('hide');
        }
        if (mQuery('#ui-tab-header2').hasClass('ui-tabs-selected'))
        {
            if(!mQuery('#email-content-preview').hasClass('hide')) {
                mQuery('#builder_btn').removeClass('hide');
            }else{
                mQuery('#builder_btn').addClass('hide');
            }
        }else {
            mQuery('#builder_btn').addClass('hide');
        }
    });

    Le.loadEmailStatCounts();

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
        var value = currentLink.attr('data-verified-emails');
        mQuery("#emailform_fromAddress").val(value);
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
};

Le.dripemailOnLoad = function (container, response) {
    Le.initSelectBeeTemplate(mQuery('#emailform_template'),'email');
    Le.initEmailDynamicContent();
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
    mQuery('.next-tab, .prev-tab, .ui-state-default').click(function() {
        var selectrel = mQuery(this).attr("rel");

        mQuery('#dripEmail_PublicName').removeClass('has-success has-error');
        if(mQuery('#dripemailform_name').val() == "") {
            mQuery('#dripEmail_PublicName').removeClass('has-success has-error').addClass('has-error');
            mQuery('#dripEmail_PublicName .custom-help').removeClass('hide').html("Campaign name can't be empty");
            return;
        } else{
            mQuery('#dripEmail_PublicName').removeClass('has-success has-error');
            mQuery('#dripEmail_PublicName .custom-help').html("");
        }
        if(!mQuery('#dripemail_advance_editor').hasClass('hide')) {
            if (selectrel == 3 && !mQuery(this).hasClass('ui-state-default')) {
                Le.launchBeeEditor('dripemail', 'email');
                return;
            }
        }
        mQuery(".ui-tabs-panel").addClass('ui-tabs-hide');
        mQuery("#fragment-"+selectrel).removeClass('ui-tabs-hide');
        mQuery(".ui-state-default").removeClass('ui-tabs-selected ui-state-active');
        mQuery("#ui-tab-header"+selectrel).addClass('ui-tabs-selected ui-state-active');

    });
    Le.loadEmailStatCounts();
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
        var value = currentLink.attr('data-verified-emails');
        mQuery("#emailform_fromAddress").val(value);
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
    Le.loadDripEmailScheduledStatCounts();
    Le.loadDripEmailStatCounts();
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
        mQuery('td.drip-email-col-stats').each(function () {
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
                            order:order
                        },
                        function (response) {
                            if (response.success) {

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

Le.loadEmailStatCounts = function(){
    if (mQuery('table.email-list').length) {
        mQuery('td.col-stats').each(function () {
            var id = mQuery(this).attr('data-stats');
            // Process the request one at a time or the xhr will cancel the previous
            Le.ajaxActionRequest(
                'email:getEmailCountStats',
                {id: id},
                function (response) {
                    if (response.success && mQuery('#sent-count-' + id + ' div').length) {
                        /* if (response.pending) {
                             mQuery('#pending-' + id + ' > a').html(response.pending);
                             mQuery('#pending-' + id).removeClass('hide');
                         }*/

                        if (response.queued) {
                            mQuery('#queued-' + id + ' > a').html(response.queued);
                            mQuery('#queued-' + id).removeClass('hide');
                        }

                        mQuery('#pending-' + id + ' > a').html(response.pending);
                        mQuery('#sent-count-' + id + ' > a').html(response.sentCount);
                        mQuery('#read-count-' + id + ' > a').html(response.readCount);
                        mQuery('#read-percent-' + id + ' > a').html(response.readPercent);
                        mQuery('#failure-count-' + id + ' > a').html(response.failureCount);
                        mQuery('#unsubscribe-count-' + id + ' > a').html(response.unsubscribeCount);
                        mQuery('#bounce-count-' + id + ' > a').html(response.bounceCount);
                        mQuery('#spam-count-' + id + ' > a').html(response.spamCount);
                    }
                },
                false,
                true
            );

        });
    }
}
Le.emailOnUnload = function(id) {
    if (id === '#app-content') {
        delete Le.listCompareChart;
    }

    if (typeof Le.ajaxActionXhrQueue !== 'undefined') {
        delete Le.ajaxActionXhrQueue['email:getEmailCountStats'];
    }
};
Le.opengoogletags = function (id) {
    if(id == 'emailform_google_tags_1'){
        mQuery(".gtags").removeClass('hide');
        Le.toggleYesNoButtonClass(id);
    } else {
        Le.toggleYesNoButtonClass(id);
        mQuery(".gtags").addClass('hide');
    }
};

Le.insertEmailBuilderToken = function(editorId, token) {
    var editor = Le.getEmailBuilderEditorInstances();
    editor[instance].insertText(token);
};

Le.getEmailAbTestWinnerForm = function(abKey) {
    if (abKey && mQuery(abKey).val() && mQuery(abKey).closest('.form-group').hasClass('has-error')) {
        mQuery(abKey).closest('.form-group').removeClass('has-error');
        if (mQuery(abKey).next().hasClass('help-block')) {
            mQuery(abKey).next().remove();
        }
    }

    Le.activateLabelLoadingIndicator('emailform_variantSettings_winnerCriteria');
    var emailId = mQuery('#emailform_sessionId').val();

    var query = "action=email:getAbTestForm&abKey=" + mQuery(abKey).val() + "&emailId=" + emailId;

    mQuery.ajax({
        url: leAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (typeof response.html != 'undefined') {
                if (mQuery('#emailform_variantSettings_properties').length) {
                    mQuery('#emailform_variantSettings_properties').replaceWith(response.html);
                } else {
                    mQuery('#emailform_variantSettings').append(response.html);
                }

                if (response.html != '') {
                    Le.onPageLoad('#emailform_variantSettings_properties', response);
                }
            }
        },
        error: function (request, textStatus, errorThrown) {
            Le.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function() {
            Le.removeLabelLoadingIndicator();
        }
    });
};

Le.submitSendForm = function () {
    Le.dismissConfirmation();
    mQuery('.btn-send').prop('disabled', true);
    mQuery('form[name=\'batch_send\']').submit();
};

Le.emailSendOnLoad = function (container, response) {
    if (mQuery('.email-send-progress').length) {
        if (!mQuery('#emailSendProgress').length) {
            Le.clearModeratedInterval('emailSendProgress');
        } else {
            Le.setModeratedInterval('emailSendProgress', 'sendEmailBatch', 2000);
        }
    }
};

Le.emailSendOnUnload = function () {
    if (mQuery('.email-send-progress').length) {
        Le.clearModeratedInterval('emailSendProgress');
        if (typeof Le.sendEmailBatchXhr != 'undefined') {
            Le.sendEmailBatchXhr.abort();
            delete Le.sendEmailBatchXhr;
        }
    }
};

Le.sendEmailBatch = function () {
    var data = 'id=' + mQuery('.progress-bar-send').data('email') + '&pending=' + mQuery('.progress-bar-send').attr('aria-valuemax') + '&batchlimit=' + mQuery('.progress-bar-send').data('batchlimit');
    Le.sendEmailBatchXhr = Le.ajaxActionRequest('email:sendBatch', data, function (response) {
        if (response.progress) {
            if (response.progress[0] > 0) {
                mQuery('.imported-count').html(response.progress[0]);
                mQuery('.progress-bar-send').attr('aria-valuenow', response.progress[0]).css('width', response.percent + '%');
                mQuery('.progress-bar-send span.sr-only').html(response.percent + '%');
            }

            if (response.progress[0] >= response.progress[1]) {
                Le.clearModeratedInterval('emailSendProgress');

                setTimeout(function () {
                    mQuery.ajax({
                        type: 'POST',
                        showLoadingBar: false,
                        url: window.location,
                        data: 'complete=1',
                        success: function (response) {

                            if (response.newContent) {
                                // It's done so pass to process page
                                Le.processPageContent(response);
                            }
                        }
                    });
                }, 1000);
            }
        }

        Le.moderatedIntervalCallbackIsComplete('emailSendProgress');
    });
};

Le.autoGeneratePlaintext = function() {
    mQuery('.plaintext-spinner').removeClass('hide');

    Le.ajaxActionRequest(
        'email:generatePlaintText',
        {
            id: mQuery('#emailform_sessionId').val(),
            custom: mQuery('#emailform_customHtml').val()
        },
        function (response) {
            mQuery('#emailform_plainText').val(response.text);
            mQuery('.plaintext-spinner').addClass('hide');
        }
    );
};

Le.selectEmailType = function(emailType) {
    if (emailType == 'list') {
        mQuery('#leadList').removeClass('hide');
        mQuery('#segmentTranslationParent').removeClass('hide');
        mQuery('#templateTranslationParent').addClass('hide');
        mQuery('.page-header h3').text(leLang.newListEmail);
    } else {
        mQuery('#segmentTranslationParent').addClass('hide');
        mQuery('#templateTranslationParent').removeClass('hide');
        mQuery('#leadList').addClass('hide');
        mQuery('.page-header h3').text(leLang.newTemplateEmail);
    }

    mQuery('#emailform_emailType').val(emailType);

    mQuery('body').removeClass('noscroll');

    mQuery('.email-type-modal').remove();
    mQuery('.email-type-modal-backdrop').remove();
};

Le.selectEmailEditor = function(editorType) {
    var basic= mQuery('#email-basic-container');
    var advance= mQuery('#email-advance-container');
    var other= mQuery('#email-other-container');
    var builderbtn= mQuery('.btn-beeditor');
    var activateTab='';
    if (editorType == 'basic' || editorType == 'code') {
        advance.addClass('hide');
        builderbtn.addClass('hide');
        //mQuery('.fragment-2-buttons').attr("style","margin-left: 50%;");
        var textarea = mQuery('textarea.bee-editor-json');
        textarea.val("");
        activateTab='basic';
    } else {
        mQuery('#unsubscribe_text_div').addClass('hide');
        var templateJSON = mQuery('textarea.bee-editor-json');
        // Populate default content
        if (!templateJSON.length || !templateJSON.val().length) {
            Le.setBeeTemplateJSON(Le.beeTemplate);
        }
        basic.addClass('hide');
        activateTab='advance';

    }

    var fromAddress=mQuery('[for=emailform_fromAddress]');
    var fromAddressParent=fromAddress.parent();
    if (fromAddressParent.hasClass('has-error')) {
        activateTab='other';
    }
    if(activateTab == 'basic'){
        basic.trigger('click');
        if(editorType == 'code'){
            var editor = mQuery('.builder-html').data('froala.editor');
            editor.commands.exec('html');
        }
        mQuery('#filters_chosen').addClass('hide');
    } else if(activateTab == 'advance'){
        advance.trigger('click');
    } else{
        other.trigger('click');
    }
    mQuery('.email-type-modal').remove();
    mQuery('.email-type-modal-backdrop').remove();
};

Le.getTotalAttachmentSize = function() {
    var assets = mQuery('#emailform_assetAttachments').val();
    if (assets) {
        assets = {
            'assets': assets
        };
        Le.ajaxActionRequest('email:getAttachmentsSize', assets, function(response) {
            if(response.size == "failed"){
                mQuery('#Emailasset_Attachments').removeClass('has-success has-error').addClass('has-error');
                mQuery('#Emailasset_Attachments .help-block').html("This attachment exceeds the limit, and you can attach a maximum 1 MB of file size in total.");
                //mQuery('#attachment-size').text(response.size);
            } else {
                mQuery('#Emailasset_Attachments').removeClass('has-success has-error');
                mQuery('#Emailasset_Attachments .help-block').html("");
                mQuery('#attachment-size').text(response.size);
            }
        });
    } else {
        mQuery('#attachment-size').text('0');
    }
};

Le.standardEmailUrl = function(options) {
    if (options && options.windowUrl && options.origin) {
        var url = options.windowUrl;
        var editEmailKey = '/broadcast/edit/emailId';
        var previewEmailKey = '/broadcast/preview/emailId';
        if(options.origin == '#campaignevent_properties_useremail_email'){
             previewEmailKey = '/emailnotifications/preview/emailId';
        }
        if (url.indexOf(editEmailKey) > -1 ||
            url.indexOf(previewEmailKey) > -1) {
            options.windowUrl = url.replace('emailId', mQuery(options.origin).val());
        }
    }

    return options;
};

/**
 * Enables/Disables email preview and edit. Can be triggered from campaign or form actions
 * @param opener
 * @param origin
 */
Le.disabledEmailAction = function(opener, origin) {
    if (typeof opener == 'undefined') {
        opener = window;
    }
    var email = opener.mQuery(origin);
    if (email.length == 0) return;
    var emailId = email.val();
    var disabled = emailId === '' || emailId === null;

    opener.mQuery('[id$=_editEmailButton]').prop('disabled', disabled);
    opener.mQuery('[id$=_previewEmailButton]').prop('disabled', disabled);
};

Le.initEmailDynamicContent = function() {
    if (mQuery('#dynamic-content-container').length) {
        mQuery('#emailFilters .remove-selected').each( function (index, el) {
            mQuery(el).on('click', function () {
                mQuery(this).closest('.panel').animate(
                    {'opacity': 0},
                    'fast',
                    function () {
                        mQuery(this).remove();
                    }
                );

                if (!mQuery('#emailFilters li:not(.placeholder)').length) {
                    mQuery('#emailFilters li.placeholder').removeClass('hide');
                } else {
                    mQuery('#emailFilters li.placeholder').addClass('hide');
                }
            });
        });

        mQuery('#addNewDynamicContent').on('click', function (e) {
            e.preventDefault();

            Le.createNewDynamicContentItem();
        });

        Le.initDynamicContentItem();
    }
};

Le.createNewDynamicContentItem = function(jQueryVariant) {
    // To support the parent.mQuery from the builder
    var mQuery = (typeof jQueryVariant != 'undefined') ? jQueryVariant : window.mQuery;

    var tabHolder               = mQuery('#dynamicContentTabs');
    var filterHolder            = mQuery('#dynamicContentContainer');
    var dynamicContentPrototype = mQuery('#dynamicContentPrototype').data('prototype');
    var dynamicContentIndex     = tabHolder.find('li').length - 1;
    while (mQuery('#emailform_dynamicContent_' + dynamicContentIndex).length > 0) {
        dynamicContentIndex++; // prevent duplicate ids
    }
    var tabId                   = '#emailform_dynamicContent_' + dynamicContentIndex;
    var tokenName               = 'Dynamic Content ' + (dynamicContentIndex + 1);
    var newForm                 = dynamicContentPrototype.replace(/__name__/g, dynamicContentIndex);
    var newTab                  = mQuery('<li><a role="tab" data-toggle="tab" href="' + tabId + '">' + tokenName + '</a></li>');

    tabHolder.append(newTab);
    filterHolder.append(newForm);

    var itemContainer = mQuery(tabId);
    var textarea      = itemContainer.find('.editor');
    var firstInput    = itemContainer.find('input[type="text"]').first();

    textarea.froalaEditor(mQuery.extend({}, Le.basicFroalaOptions, {
        // Set custom buttons with separator between them.
        toolbarSticky: false,
        toolbarButtons: ['undo', 'redo', '|', 'bold', 'italic', 'underline', 'paragraphFormat', 'fontFamily', 'fontSize', 'color', 'align', 'formatOL', 'formatUL', 'quote', 'clearFormatting', 'token', 'insertLink', 'insertImage', 'insertTable', 'html', 'fullscreen'],
        heightMin: 100
    }));

    tabHolder.find('i').first().removeClass('fa-spinner fa-spin').addClass('fa-plus text-success');
    newTab.find('a').tab('show');

    firstInput.focus();

    Le.updateDynamicContentDropdown();

    Le.initDynamicContentItem(tabId, mQuery, tokenName);

    return tabId;
};

Le.createNewDynamicContentFilter = function(el, jQueryVariant) {
    // To support the parent.mQuery from the builder
    var mQuery = (typeof jQueryVariant != 'undefined') ? jQueryVariant : window.mQuery;

    var $this                = mQuery(el);
    var parentElement        = $this.parents('.panel');
    var tabHolder            = parentElement.find('.nav');
    var filterHolder         = parentElement.find('.tab-content');
    var filterBlockPrototype = mQuery('#filterBlockPrototype');
    var filterIndex          = filterHolder.find('.tab-pane').length - 1;
    var dynamicContentIndex  = $this.parents('.tab-pane').attr('id').match(/\d+$/)[0];

    var filterPrototype   = filterBlockPrototype.data('prototype');
    var filterContainerId = '#emailform_dynamicContent_' + dynamicContentIndex + '_filters_' + filterIndex ;
    // prevent duplicate ids
    while (mQuery(filterContainerId).length > 0) {
        filterIndex++;
        filterContainerId = '#emailform_dynamicContent_' + dynamicContentIndex + '_filters_' + filterIndex ;
    }
    var newTab            = mQuery('<li><a role="tab" data-toggle="tab" href="' + filterContainerId + '">Variation ' + (filterIndex + 1) + '</a></li>');
    var newForm           = filterPrototype.replace(/__name__/g, filterIndex)
        .replace(/dynamicContent_0_filters/g, 'dynamicContent_' + dynamicContentIndex + '_filters')
        .replace(/dynamicContent]\[0]\[filters/g, 'dynamicContent][' + dynamicContentIndex + '][filters');

    tabHolder.append(newTab);
    filterHolder.append(newForm);

    var filterContainer  = mQuery(filterContainerId);
    var availableFilters = filterContainer.find('select[data-le="available_filters"]');
    var altTextarea      = filterContainer.find('.editor');
    var removeButton     = filterContainer.find('.remove-item');

    Le.activateChosenSelect(availableFilters, false, mQuery);

    availableFilters.on('change', function() {
        var $this = mQuery(this);

        if ($this.val()) {
            Le.addDynamicContentFilter($this.val(), mQuery);
            $this.val('');
            $this.trigger('chosen:updated');
        }
    });

    altTextarea.froalaEditor(mQuery.extend({}, Le.basicFroalaOptions, {
        // Set custom buttons with separator between them.
        toolbarSticky: false,
        toolbarButtons: ['undo', 'redo', '|', 'bold', 'italic', 'underline', 'paragraphFormat', 'fontFamily', 'fontSize', 'color', 'align', 'formatOL', 'formatUL', 'quote', 'clearFormatting', 'token', 'insertLink', 'insertImage', 'insertTable', 'html', 'fullscreen'],
        heightMin: 100
    }));

    Le.initRemoveEvents(removeButton, mQuery);

    newTab.find('a').tab('show');

    return filterContainerId;
};

Le.initDynamicContentItem = function (tabId, jQueryVariant, tokenName) {
    // To support the parent.mQuery from the builder
    var mQuery = (typeof jQueryVariant != 'undefined') ? jQueryVariant : window.mQuery;

    var $el = mQuery('#dynamic-content-container');
    if ($el.size() == 0){
        mQuery = parent.mQuery;
        $el = mQuery('#dynamic-content-container');
    }

    if (tabId || typeof tabId != "undefined") {
        $el = mQuery(tabId);
    }

    $el.find('.addNewDynamicContentFilter').on('click', function (e) {
        e.preventDefault();

        Le.createNewDynamicContentFilter(this);
    });

    if (typeof tokenName != 'undefined') {
        $el.find('.dynamic-content-token-name').val(tokenName);
    }

    if ($el.find('.dynamic-content-token-name').val() == '') {
        var dynamicContent = $el.attr('id').match(/\d+$/);
        if (dynamicContent) {
            var dynamicContentIndex  = dynamicContent[0];
            $el.find('.dynamic-content-token-name').val('Dynamic Content ' + dynamicContentIndex);
        }
    }

    $el.find('a.remove-selected').on('click', function() {
        mQuery(this).closest('.panel').animate(
            {'opacity': 0},
            'fast',
            function () {
                mQuery(this).remove();
            }
        );
    });

    $el.find('select[data-le="available_filters"]').on('change', function() {
        var $this = mQuery(this);

        if ($this.val()) {
            Le.addDynamicContentFilter($this.val(), mQuery);
            $this.val('');
            $this.trigger('chosen:updated');
        }
    });

    Le.initRemoveEvents($el.find('.remove-item'), mQuery);
};

Le.updateDynamicContentDropdown = function () {
    var options = [];

    mQuery('#dynamicContentTabs').find('a[data-toggle="tab"]').each(function () {
        var prototype       = '<li><a class="fr-command" data-cmd="dynamicContent" data-param1="__tokenName__">__tokenName__</a></li>';
        var newOption       = prototype.replace(/__tokenName__/g, mQuery(this).text());

        options.push(newOption);
    });

    mQuery('button[data-cmd="dynamicContent"]').next().find('ul').html(options.join(''));
};

Le.initRemoveEvents = function (elements, jQueryVariant) {
    var mQuery = (typeof jQueryVariant != 'undefined') ? jQueryVariant : window.mQuery;
    if (elements.hasClass('remove-selected')) {
        elements.on('click', function() {
            mQuery(this).closest('.panel').animate(
                {'opacity': 0},
                'fast',
                function () {
                    mQuery(this).remove();
                }
            );
        });
    } else {
        elements.on('click', function (e) {
            e.preventDefault();
            var $this         = mQuery(this);
            var parentElement = $this.parents('.tab-pane.dynamic-content');

            if ($this.hasClass('remove-filter')) {
                parentElement = $this.parents('.tab-pane.dynamic-content-filter');
            }

            var tabLink      = mQuery('a[href="#' + parentElement.attr('id') + '"]').parent();
            var tabContainer = tabLink.parent();

            parentElement.remove();
            tabLink.remove();
            // if tabContainer is for variants, show the first one, if it is the DEC vertical list, show the second one
            if (tabContainer.hasClass('tabs-left') || $this.hasClass('remove-filter')) {
                tabContainer.find('li').first().next().find('a').tab('show');
            } else {
                tabContainer.find('li').first().find('a').tab('show');
            }

            Le.updateDynamicContentDropdown();
        });
    }
};

Le.addDynamicContentFilter = function (selectedFilter, jQueryVariant) {
    var mQuery = (typeof jQueryVariant != 'undefined') ? jQueryVariant : window.mQuery;

    var dynamicContentItems  = mQuery('.tab-pane.dynamic-content');
    var activeDynamicContent = dynamicContentItems.filter(':visible');
    var dynamicContentIndex  = activeDynamicContent.attr('id').match(/\d+$/)[0]; //dynamicContentItems.index(activeDynamicContent);

    var dynamicContentFilterContainers      = activeDynamicContent.find('div[data-filter-container]');
    var activeDynamicContentFilterContainer = dynamicContentFilterContainers.filter(':visible');
    var dynamicContentFilterIndex           = dynamicContentFilterContainers.index(activeDynamicContentFilterContainer);

    var selectedOption  = mQuery('option[data-le="available_' + selectedFilter + '"]').first();
    var label           = selectedOption.text();

    // create a new filter
    var filterNum   = activeDynamicContentFilterContainer.children('.panel').length;
    var prototype   = mQuery('#filterSelectPrototype').data('prototype');
    var fieldObject = selectedOption.data('field-object');
    var fieldType   = selectedOption.data('field-type');
    var isSpecial   = (mQuery.inArray(fieldType, ['leadlist', 'lead_email_received', 'tags', 'multiselect', 'boolean', 'select', 'country', 'timezone', 'region', 'stage', 'locale']) != -1);

    // Update the prototype settings
    prototype = prototype.replace(/__name__/g, filterNum)
        .replace(/__label__/g, label)
        .replace(/dynamicContent_0_filters/g, 'dynamicContent_' + dynamicContentIndex + '_filters')
        .replace(/dynamicContent]\[0]\[filters/g, 'dynamicContent][' + dynamicContentIndex + '][filters')
        .replace(/filters_0_filters/g, 'filters_' + dynamicContentFilterIndex + '_filters')
        .replace(/filters]\[0]\[filters/g, 'filters][' + dynamicContentFilterIndex + '][filters');

    if (filterNum === 0) {
        prototype = prototype.replace(/in-group/g, '');
    }

    // Convert to DOM
    prototype = mQuery(prototype);

    if (fieldObject == 'company') {
        prototype.find('.object-icon').removeClass('fa-user').addClass('fa-building');
    } else {
        prototype.find('.object-icon').removeClass('fa-building').addClass('fa-user');
    }

    var filterBase  = "emailform[dynamicContent][" + dynamicContentIndex + "][filters][" + dynamicContentFilterIndex + "][filters][" + filterNum + "]";
    var filterIdBase = "emailform_dynamicContent_" + dynamicContentIndex + "_filters_" + dynamicContentFilterIndex + "_filters_" + filterNum;

    if (isSpecial) {
        var templateField = fieldType;
        if (fieldType == 'boolean' || fieldType == 'multiselect') {
            templateField = 'select';
        }
        var template = mQuery('#templates .' + templateField + '-template').clone();
        var $template = mQuery(template);
        var templateNameAttr = $template.attr('name').replace(/__name__/g, filterNum)
            .replace(/__dynamicContentIndex__/g, dynamicContentIndex)
            .replace(/__dynamicContentFilterIndex__/g, dynamicContentFilterIndex);
        var templateIdAttr = $template.attr('id').replace(/__name__/g, filterNum)
            .replace(/__dynamicContentIndex__/g, dynamicContentIndex)
            .replace(/__dynamicContentFilterIndex__/g, dynamicContentFilterIndex);

        $template.attr('name', templateNameAttr);
        $template.attr('id', templateIdAttr);

        prototype.find('input[name="' + filterBase + '[filter]"]').replaceWith(template);
    }

    if (activeDynamicContentFilterContainer.find('.panel').length == 0) {
        // First filter so hide the glue footer
        prototype.find(".panel-footer").addClass('hide');
    }

    prototype.find("input[name='" + filterBase + "[field]']").val(selectedFilter);
    prototype.find("input[name='" + filterBase + "[type]']").val(fieldType);
    prototype.find("input[name='" + filterBase + "[object]']").val(fieldObject);

    var filterEl = (isSpecial) ? "select[name='" + filterBase + "[filter]']" : "input[name='" + filterBase + "[filter]']";

    activeDynamicContentFilterContainer.append(prototype);

    Le.initRemoveEvents(activeDynamicContentFilterContainer.find("a.remove-selected"), mQuery);

    var filter = '#' + filterIdBase + '_filter';

    var fieldOptions = fieldCallback = '';
    //activate fields
    if (isSpecial) {
        if (fieldType == 'select' || fieldType == 'boolean' || fieldType == 'multiselect') {
            // Generate the options
            fieldOptions = selectedOption.data("field-list");

            mQuery.each(fieldOptions, function(index, val) {
                mQuery('<option>').val(index).text(val).appendTo(filterEl);
            });
        }
    } else if (fieldType == 'lookup') {
        fieldCallback = selectedOption.data("field-callback");
        if (fieldCallback && typeof Le[fieldCallback] == 'function') {
            fieldOptions = selectedOption.data("field-list");
            Le[fieldCallback](filterIdBase + '_filter', selectedFilter, fieldOptions);
        }
    } else if (fieldType == 'datetime') {
        mQuery(filter).datetimepicker({
            format: 'Y-m-d H:i',
            lazyInit: true,
            validateOnBlur: false,
            allowBlank: true,
            scrollInput: false
        });
    } else if (fieldType == 'date') {
        mQuery(filter).datetimepicker({
            timepicker: false,
            format: 'Y-m-d',
            lazyInit: true,
            validateOnBlur: false,
            allowBlank: true,
            scrollInput: false,
            closeOnDateSelect: true
        });
    } else if (fieldType == 'time') {
        mQuery(filter).datetimepicker({
            datepicker: false,
            format: 'H:i',
            lazyInit: true,
            validateOnBlur: false,
            allowBlank: true,
            scrollInput: false
        });
    } else if (fieldType == 'lookup_id') {
        //switch the filter and display elements
        var oldFilter = mQuery(filterEl);
        var newDisplay = mQuery(oldFilter).clone();
        mQuery(newDisplay).attr('name', filterBase + '[display]')
            .attr('id', filterIdBase + '_display');

        var oldDisplay = mQuery(prototype).find("input[name='" + filterBase + "[display]']");
        var newFilter = mQuery(oldDisplay).clone();
        mQuery(newFilter).attr('name', filterBase + '[filter]')
            .attr('id', filterIdBase + '_filter');

        mQuery(oldFilter).replaceWith(newFilter);
        mQuery(oldDisplay).replaceWith(newDisplay);

        var fieldCallback = selectedOption.data("field-callback");
        if (fieldCallback && typeof Le[fieldCallback] == 'function') {
            fieldOptions = selectedOption.data("field-list");
            Le[fieldCallback](filterIdBase + '_display', selectedFilter, fieldOptions, mQuery);
        }
    } else {
        mQuery(filter).attr('type', fieldType);
    }

    var operators = mQuery(selectedOption).data('field-operators');
    mQuery('#' + filterIdBase + '_operator').html('');
    mQuery.each(operators, function (value, label) {
        var newOption = mQuery('<option/>').val(value).text(label);
        newOption.appendTo(mQuery('#' + filterIdBase + '_operator'));
    });

    // Convert based on first option in list
    Le.convertDynamicContentFilterInput('#' + filterIdBase + '_operator', mQuery);
};

Le.convertDynamicContentFilterInput = function(el, jQueryVariant) {
    var mQuery = (typeof jQueryVariant != 'undefined') ? jQueryVariant : window.mQuery;
    var operator = mQuery(el).val();
    // Extract the filter number
    var regExp    = /emailform_dynamicContent_(\d+)_filters_(\d+)_filters_(\d+)_operator/;
    var matches   = regExp.exec(mQuery(el).attr('id'));

    var dynamicContentIndex       = matches[1];
    var dynamicContentFilterIndex = matches[2];
    var filterNum                 = matches[3];

    var filterId       = '#emailform_dynamicContent_' + dynamicContentIndex + '_filters_' + dynamicContentFilterIndex + '_filters_' + filterNum + '_filter';
    var filterEl       = mQuery(filterId);
    var filterElParent = filterEl.parent();

    // Reset has-error
    if (filterElParent.hasClass('has-error')) {
        filterElParent.find('div.help-block').hide();
        filterElParent.removeClass('has-error');
    }

    var disabled = (operator == 'empty' || operator == '!empty');
    filterEl.prop('disabled', disabled);

    if (disabled) {
        filterEl.val('');
    }

    var newName = '';
    var lastPos;

    if (filterEl.is('select')) {
        var isMultiple  = filterEl.attr('multiple');
        var multiple    = (operator == 'in' || operator == '!in');
        var placeholder = filterEl.attr('data-placeholder');

        if (multiple && !isMultiple) {
            filterEl.attr('multiple', 'multiple');

            // Update the name
            newName =  filterEl.attr('name') + '[]';
            filterEl.attr('name', newName);

            placeholder = leLang['chosenChooseMore'];
        } else if (!multiple && isMultiple) {
            filterEl.removeAttr('multiple');

            // Update the name
            newName = filterEl.attr('name');
            lastPos = newName.lastIndexOf('[]');
            newName = newName.substring(0, lastPos);

            filterEl.attr('name', newName);

            placeholder = leLang['chosenChooseOne'];
        }

        if (multiple) {
            // Remove empty option
            filterEl.find('option[value=""]').remove();

            // Make sure none are selected
            filterEl.find('option:selected').removeAttr('selected');
        } else {
            // Add empty option
            filterEl.prepend("<option value='' selected></option>");
        }

        // Destroy the chosen and recreate
        if (mQuery(filterId + '_chosen').length) {
            filterEl.chosen('destroy');
        }

        filterEl.attr('data-placeholder', placeholder);

        Le.activateChosenSelect(filterEl, false, mQuery);
    }
};
Le.checkemailstatus = function(){
    if(mQuery('.license-notifiation').hasClass('hide')) {
        Le.ajaxActionRequest('email:emailstatus', {}, function (response) {
            if (response.success) {
                if (response.info != "" && response.isalertneeded != "true") {
                    if (mQuery('.license-notifiation').hasClass('hide')) {
                        mQuery('.license-notifiation').removeClass('hide');
                        mQuery('.license-notifiation').css('display','table');
                        mQuery('.license-notifiation').css('table-layout','fixed');
                        mQuery('.button-notification').addClass('hide');
                        mQuery('.license-notifiation #license-alert-message').html(response.info);
                        mQuery('#fixed-content').attr('style', 'margin-top:215px;');
                        mQuery('.content-body').attr('style', 'padding-top:82px;');
                    }
                } else {
                    mQuery('.license-notifiation').addClass('hide');
                }
            }
        });
    }
}

Le.changeButtonPanelStyle = function (){
    if(!mQuery('.ui-tabs-panel .fixed-header').hasClass('ui-panel-fixed-button-panel')){
        mQuery('.ui-tabs-panel .fixed-header').addClass('ui-panel-fixed-button-panel');
        if(mQuery('.license-notifiation').hasClass('hide')) {
            mQuery('#fixed-content').attr('style', 'margin-top:145px;');
        } else {
            mQuery('#fixed-content').attr('style', 'margin-top:205px;');
        }
    } else {
        mQuery('.ui-tabs-panel .fixed-header').removeClass('ui-panel-fixed-button-panel');
        if(mQuery('.license-notifiation').hasClass('hide')) {
            mQuery('#fixed-content').attr('style', 'margin-top:155px;');
        } else {
            mQuery('#fixed-content').attr('style', 'margin-top:215px;');
        }
    }
}

Le.setValueforNewButton = function (value,ele){
    mQuery('#new-drip-email').attr('value',value);
    mQuery('.editor_select').addClass('editor_layout').removeClass('editor_select');
    mQuery(ele).addClass('editor_select').removeClass('editor_layout');
}

Le.openDripEmailEditor = function (){
    var editorname = mQuery('#new-drip-email').attr('value');
    editorname = "dripemail_"+editorname;
    mQuery('#drip-email-container').removeClass('hide');
    mQuery('#drip-email-list-container').addClass('hide');
    mQuery('.dripemail_content').addClass('hide');
    mQuery('#'+editorname).removeClass('hide');
    mQuery('.newbutton-container').addClass('hide');
    mQuery('.saveclose-container').removeClass('hide');
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
    var data = {
        emailId   : Emailid,
        dripEmail : DripEmail
    };
    Le.ajaxActionRequest('email:deleteDripEmails', data, function (response) {
        if (response.success){
            Le.refreshDripEmailList(response.content);
        }
    });
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
    Le.loadEmailStatCounts();
    Le.loadDripEmailScheduledStatCounts();
    Le.loadDripEmailStatCounts();
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
    var frequencyUnit = mQuery('#drip_emailform_scheduleTime').val();
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
        }
    });
}