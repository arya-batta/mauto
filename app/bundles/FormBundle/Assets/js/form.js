//FormBundle
Le.formOnLoad = function (container) {
    if (mQuery(container + ' #list-search').length) {
        Le.activateSearchAutocomplete('list-search', 'form.form');
    }
    var bodyOverflow = {};

    mQuery('select.form-builder-new-component').change(function (e) {
        mQuery(this).find('option:selected');
        Le.ajaxifyModal(mQuery(this).find('option:selected'));
        // Reset the dropdown
        mQuery(this).val('');
        mQuery(this).trigger('chosen:updated');
    });


    if (mQuery('#leforms_fields')) {
        //make the fields sortable
        mQuery('#leforms_fields').sortable({
            items: '.panel',
            cancel: '',
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
            scroll: true,
            axis: 'y',
            containment: '#leforms_fields .drop-here',
            stop: function(e, ui) {
                // Restore original overflow
                mQuery('body').css(bodyOverflow);
                mQuery(ui.item).attr('style', '');

                mQuery.ajax({
                    type: "POST",
                    url: leAjaxUrl + "?action=form:reorderFields",
                    data: mQuery('#leforms_fields').sortable("serialize", {attribute: 'data-sortable-id'}) + "&formId=" + mQuery('#leform_sessionId').val()
                });
            }
        });

        Le.initFormFieldButtons();
        Le.removeActionButtons();
    }

    mQuery("#ui-tab-header1").addClass('ui-tabs-selected ui-state-active');

    mQuery('.next-tab, .prev-tab, .ui-state-default').click(function() {
        mQuery('#Form_Name').removeClass('has-success has-error');
        mQuery('#Form_post_action').removeClass('has-success has-error');
        mQuery('#Form_Name .help-block').addClass('hide').html("");
        mQuery('#Form_post_action .help-block').addClass('hide').html("");
        mQuery('#form_url_div').removeClass('has-success has-error');
        mQuery('#form_url_div .help-block').addClass('hide').html("");
        if(mQuery('#leform_name').val() == "" && mQuery('#leform_postActionProperty').val() == ""){
            if(mQuery('.check_required').hasClass('required'))
            {
                mQuery('#Form_Name').removeClass('has-success has-error').addClass('has-error');
                mQuery('#Form_Name .custom-help').removeClass('hide').html("Name can't be empty.");
                mQuery('#Form_post_action').removeClass('has-success has-error').addClass('has-error');
                mQuery('#Form_post_action .custom-help').removeClass('hide').html("Redirect URL/Message can't be empty ");

            }else {
                mQuery('#Form_Name').removeClass('has-success has-error').addClass('has-error');
                mQuery('#Form_Name .custom-help').removeClass('hide').html("Name can't be empty.");
            }

            return;
        }
        else if(mQuery('#leform_name').val() == "") {
            mQuery('#Form_Name').removeClass('has-success has-error').addClass('has-error');
            mQuery('#Form_Name .custom-help').removeClass('hide').html("Name can't be empty.");
            return;
        } else if (mQuery('#leform_postActionProperty').val() == "" && mQuery('.check_required').hasClass('required')){
            mQuery('#Form_post_action').removeClass('has-success has-error').addClass('has-error');
            mQuery('#Form_post_action .custom-help').removeClass('hide').html("Redirect URL/Message can't be empty");
            return;
        }
        if(mQuery('#leform_formType').val() == 'smart' && mQuery('#leform_formurl').val() == ""){
            mQuery('#form_url_div').removeClass('has-success has-error').addClass('has-error');
            mQuery('#form_url_div .custom-help').removeClass('hide').html("A value is required.");
            return;
        }
        var selectrel = mQuery(this).attr("rel");
        mQuery(".ui-tabs-panel").addClass('ui-tabs-hide');
        mQuery("#fragment-"+selectrel).removeClass('ui-tabs-hide');
        mQuery(".ui-state-default").removeClass('ui-tabs-selected ui-state-active');
        mQuery("#ui-tab-header"+selectrel).addClass('ui-tabs-selected ui-state-active');
    });

    if (mQuery('#leforms_actions')) {
        //make the fields sortable
        mQuery('#leforms_actions').sortable({
            items: '.panel',
            cancel: '',
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
            scroll: true,
            axis: 'y',
            containment: '#leforms_actions .drop-here',
            stop: function(e, ui) {
                // Restore original overflow
                mQuery('body').css(bodyOverflow);
                mQuery(ui.item).attr('style', '');

                mQuery.ajax({
                    type: "POST",
                    url: leAjaxUrl + "?action=form:reorderActions",
                    data: mQuery('#leforms_actions').sortable("serialize") + "&formId=" + mQuery('#leform_sessionId').val()
                });
            }
        });

        mQuery('#leforms_actions .leform-row').on('dblclick.leformactions', function(event) {
            event.preventDefault();
            mQuery(this).find('.btn-edit').first().click();
        });
    }

    if (mQuery('leform_formType').length && mQuery('#leform_formType').val() == '') {
        //mQuery('body').addClass('noscroll');
    }

    Le.initHideItemButton('#leforms_fields');
    Le.initHideItemButton('#leforms_actions');
    Le.enableGDPRFormWidget();
    if(mQuery('#Form_post_action').hasClass('has-error')){
        mQuery('.check_required').addClass('required');
    }

    mQuery('#smart-form-scan-url-btn').off().on('click', function() {
        var currentbtn=mQuery(this);
        var scanurl=mQuery('#leform_formurl').val();
        var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
        if (!pattern.test(scanurl)) {
            alert("Enter valid url to scan");
            return false;
        }
        Le.activateButtonLoadingIndicator(currentbtn);
        Le.ajaxActionRequest(
            'form:scanFormUrl',
            {scanurl: scanurl},
            function (response) {
                if (response.success) {
                    mQuery("#fragment-1 #next-page-1").trigger( "click" );
                    mQuery("#le_smart_form_list").html(response.newContent);
                    var totalFormCount = response.totalCount;
                    var msg = "We have found "+ totalFormCount +" forms in the given page. Please choose the one you want to integrate.";
                    mQuery("#smart-action-form").removeClass('hide');
                    mQuery("#smart-action-form").html(msg);
                    Le.showSmartFormListPanel();
                    //alert("Success-->"+response.message);
                }else{
                    alert(response.message);
                }
                Le.removeButtonLoadingIndicator(currentbtn);
            },
            false,
            true
        );
    });
       var formType = mQuery('#leform_formType').val();
        if(formType == 'smart') {
            mQuery('#gdprpublished').addClass('hide');
            if (!mQuery('.smart-form-field-mapper-header-holder').hasClass('hide')) {
                mQuery('.smart-action-panel').removeClass('hide');
                mQuery('.smart-form-field-mapper-formname-holder').css('marginLeft', '-34px');
            }
        }
};

Le.setBtnBackgroundColor = function () {
    var selectedbgcolor =  mQuery('#formfield_btnbgcolor').val() ;
    var input = mQuery('#formfield_inputAttributes').val();

     if (input.indexOf('style=') == -1) {
         var value =input+" style='background-color:#"+selectedbgcolor+";color:#ffffff;'";
         mQuery('#formfield_inputAttributes').val(value);
      }else {
         var fields = input.split('background-color');
         var sec = fields[1].substr(9);
         var value = fields[0]+'background-color:#'+selectedbgcolor+';'+sec;
         mQuery('#formfield_inputAttributes').val(value);
     }
}
Le.setBtnTextColor = function () {
    var selectedtxtcolor =  mQuery('#formfield_btntxtcolor').val() ;
    var input = mQuery('#formfield_inputAttributes').val();

    if (input.indexOf('style=') == -1) {
        var value =input+" style='background-color:#ff9900;color:#"+selectedtxtcolor+";'";
        mQuery('#formfield_inputAttributes').val(value);
    }else{
        var fields = input.split(';color');
        var sec = fields[1].substr(9);
        var value = fields[0]+';color:#'+selectedtxtcolor+';'+sec;
        mQuery('#formfield_inputAttributes').val(value);
    }
}
Le.updateFormFields = function () {
    Le.activateLabelLoadingIndicator('campaignevent_properties_field');

    var formId = mQuery('#campaignevent_properties_form').val();
    Le.ajaxActionRequest('form:updateFormFields', {'formId': formId}, function(response) {
        if (response.fields) {
            var select = mQuery('#campaignevent_properties_field');
            select.find('option').remove();
            var fieldOptions = {};
            mQuery.each(response.fields, function(key, field) {
                var option = mQuery('<option></option>')
                    .attr('value', field.alias)
                    .text(field.label);
                select.append(option);
                fieldOptions[field.alias] = field.options;
            });
            select.attr('data-field-options', JSON.stringify(fieldOptions));
            select.trigger('chosen:updated');
            Le.updateFormFieldValues(select);
        }
        Le.removeLabelLoadingIndicator();
    });
};

Le.updateFormFieldValues = function (field) {
    field = mQuery(field);
    var fieldValue = field.val();
    var options = jQuery.parseJSON(field.attr('data-field-options'));
    var valueField = mQuery('#campaignevent_properties_value');
    var valueFieldAttrs = {
        'class': valueField.attr('class'),
        'id': valueField.attr('id'),
        'name': valueField.attr('name'),
        'autocomplete': valueField.attr('autocomplete'),
        'value': valueField.attr('value')
    };

    if (typeof options[fieldValue] !== 'undefined' && !mQuery.isEmptyObject(options[fieldValue])) {
        var newValueField = mQuery('<select/>')
            .attr('class', valueFieldAttrs['class'])
            .attr('id', valueFieldAttrs['id'])
            .attr('name', valueFieldAttrs['name'])
            .attr('autocomplete', valueFieldAttrs['autocomplete'])
            .attr('value', valueFieldAttrs['value']);
        mQuery.each(options[fieldValue], function(key, optionVal) {
            var option = mQuery("<option></option>")
                .attr('value', optionVal)
                .text(optionVal);
            newValueField.append(option);
        });
        valueField.replaceWith(newValueField);
    } else {
        var newValueField = mQuery('<input/>')
            .attr('type', 'text')
            .attr('class', valueFieldAttrs['class'])
            .attr('id', valueFieldAttrs['id'])
            .attr('name', valueFieldAttrs['name'])
            .attr('autocomplete', valueFieldAttrs['autocomplete'])
            .attr('value', valueFieldAttrs['value']);
        valueField.replaceWith(newValueField);
    }
};

Le.formFieldOnLoad = function (container, response) {
    //new field created so append it to the form
    if (response.fieldHtml) {
        var newHtml = response.fieldHtml;
        var fieldId = '#leform_' + response.fieldId;
        var fieldContainer = mQuery(fieldId).closest('.form-field-wrapper');

        if (mQuery(fieldId).length) {
            //replace content
            mQuery(fieldContainer).replaceWith(newHtml);
            var newField = false;
        } else {
            //append content
            var panel = mQuery('#leforms_fields .leform-button-wrapper').closest('.form-field-wrapper');
            panel.before(newHtml);
            var newField = true;
        }

        // Get the updated element
        var fieldContainer = mQuery(fieldId).closest('.form-field-wrapper');

        //activate new stuff
        mQuery(fieldContainer).find("[data-toggle='ajax']").click(function (event) {
            event.preventDefault();
            return Le.ajaxifyLink(this, event);
        });

        //initialize tooltips
        mQuery(fieldContainer).find("*[data-toggle='tooltip']").tooltip({html: true});

        //initialize ajax'd modals
        mQuery(fieldContainer).find("[data-toggle='ajaxmodal']").on('click.ajaxmodal', function (event) {
            event.preventDefault();
            Le.ajaxifyModal(this, event);
        });

        Le.initFormFieldButtons(fieldContainer);
        Le.initHideItemButton(fieldContainer);

        //show fields panel
        if (!mQuery('#fields-panel').hasClass('in')) {
            mQuery('a[href="#fields-panel"]').trigger('click');
        }

        if (newField) {
            mQuery('.bundle-main-inner-wrapper').scrollTop(mQuery('.bundle-main-inner-wrapper').height());
        }

        if (mQuery('#form-field-placeholder').length) {
            //mQuery('#form-field-placeholder').remove();
        }
    }

    var bgcolor = mQuery('#leform_input_submit').css('background-color');
    var txtcolor = mQuery('#leform_input_submit').css('color');
    var $iconbg =   Le.getBgColorHex(bgcolor);
    var $icontxt =   Le.getBgColorHex(txtcolor);
    mQuery('#formfield_btnbgcolor').minicolors('value',$iconbg);
    mQuery('#formfield_btntxtcolor').minicolors('value',$icontxt);
    Le.enableGDPRFormWidget();
    var formAlias = mQuery('#formfield_alias').val();
    if(formAlias == 'gdpr' || formAlias =='eu_gdpr_consent') {
        mQuery('#default-optionlist').val(mQuery('.leform-checkboxgrp-label').text().trim());
        mQuery('#remove-GDPR-form').remove();
    }
};

Le.getBgColorHex = function (color){
    var hex;
    if(color.indexOf('#')>-1){
        //for IE
        hex = color;
    } else {
        var rgb = color.match(/\d+/g);
        hex = ('0' + parseInt(rgb[0], 10).toString(16)).slice(-2) + ('0' + parseInt(rgb[1], 10).toString(16)).slice(-2) + ('0' + parseInt(rgb[2], 10).toString(16)).slice(-2);
    }
    return hex;
}

Le.initFormFieldButtons = function (container) {
    if (typeof container == 'undefined') {
        mQuery('#leforms_fields .leform-row').off(".leformfields");
        var container = '#leforms_fields';
    }

    mQuery(container).find('.leform-row').on('dblclick.leformfields', function(event) {
        event.preventDefault();
        mQuery(this).closest('.form-field-wrapper').find('.btn-edit').first().click();
    });
};

Le.formActionOnLoad = function (container, response) {
    //new action created so append it to the form
    if (response.actionHtml) {
        var newHtml = response.actionHtml;
        var actionId = '#leform_action_' + response.actionId;
        if (mQuery(actionId).length) {
            //replace content
            mQuery(actionId).replaceWith(newHtml);
            var newField = false;
        } else {
            //append content
            mQuery(newHtml).appendTo('#leforms_actions');
            var newField = true;
        }
        //activate new stuff
        mQuery(actionId + " [data-toggle='ajax']").click(function (event) {
            event.preventDefault();
            return Le.ajaxifyLink(this, event);
        });
        //initialize tooltips
        mQuery(actionId + " *[data-toggle='tooltip']").tooltip({html: true});

        //initialize ajax'd modals
        mQuery(actionId + " [data-toggle='ajaxmodal']").on('click.ajaxmodal', function (event) {
            event.preventDefault();

            Le.ajaxifyModal(this, event);
        });

        Le.initHideItemButton(actionId);

        mQuery('#leforms_actions .leform-row').off(".leform");
        mQuery('#leforms_actions .leform-row').on('dblclick.leformactions', function(event) {
            event.preventDefault();
            mQuery(this).find('.btn-edit').first().click();
        });

        //show actions panel
        if (!mQuery('#actions-panel').hasClass('in')) {
            mQuery('a[href="#actions-panel"]').trigger('click');
        }

        if (newField) {
            mQuery('.bundle-main-inner-wrapper').scrollTop(mQuery('.bundle-main-inner-wrapper').height());
        }

        if (mQuery('#form-action-placeholder').length) {
            //mQuery('#form-action-placeholder').remove();
        }
    }
};

Le.initHideItemButton = function(container) {
    mQuery(container).find('[data-hide-panel]').click(function(e) {
        e.preventDefault();
        mQuery(this).closest('.panel').hide('fast');
    });
}

Le.onPostSubmitActionChange = function(value) {
    mQuery('#Form_post_action .custom-help').html("");
    if (value == 'return') {
        //remove required class
        mQuery('#leform_postActionProperty').attr('type','text');
        mQuery('#leform_postActionProperty').prev().removeClass('required');
        mQuery('#Form_post_action').addClass('hide');
        mQuery('#leform_postActionProperty').val('');
    } else {
        if(value == 'redirect'){
            mQuery('#leform_postActionProperty').attr('type','url');
            mQuery('#Form_post_action').removeClass('hide');
        } else {
            mQuery('#leform_postActionProperty').attr('type','text');
            mQuery('#Form_post_action').removeClass('hide');
        }
        mQuery('#leform_postActionProperty').prev().addClass('required');
    }

    mQuery('#leform_postActionProperty').next().html('');
    mQuery('#leform_postActionProperty').parent().removeClass('has-error');
};

Le.selectFormType = function(formType) {
    if (formType == 'standalone') {
       // mQuery("#form_template_campaign").removeClass('hide').addClass('hide');
       // mQuery('option.action-standalone-only').removeClass('hide');
        mQuery('.fg1-standalone-form-specific').removeClass('hide');
        mQuery('.fg1-smart-form-specific').removeClass('hide').addClass('hide');
        mQuery('.page-header h3').text(leLang.newStandaloneForm);
    } else {
       // mQuery("#form_template_standalone").removeClass('hide').addClass('hide');
       // mQuery('option.action-standalone-only').addClass('hide');
        mQuery('#gdprpublished').addClass('hide');
        mQuery('.fg1-smart-form-specific').removeClass('hide');
        mQuery('#leforms_fields').remove();
        mQuery('.fg1-standalone-form-specific').removeClass('hide').addClass('hide');
        mQuery('.fg2-standalone-form-specific').removeClass('hide').addClass('hide');
        mQuery('.page-header h3').text(leLang.newSmartForm);
    }

    mQuery('.available-actions select').trigger('chosen:updated');

    mQuery('#leform_formType').val(formType);

    mQuery('body').removeClass('noscroll');

    mQuery('.form-type-modal').remove();
    mQuery('.form-type-modal-backdrop').remove();
};

Le.openNewFormAction = function(url){
    var formtype = mQuery('#leform_formType').val();
    url = url + "_" + formtype;
    window.location.href = url;
};

Le.updatePlaceholdervalue = function(ele){
    mQuery('#formfield_properties_placeholder').val(ele);
};
Le.toggleGDPRButtonClass = function (changedId) {
    changedId = '#' + changedId;
    if(mQuery(changedId).parent().attr('class').includes('btn-yes')){
        mQuery('.gdpr-content').removeClass('hide');
    } else {
        mQuery('.gdpr-content').addClass('hide');
    }
};
Le.enableGDPRFormWidget = function () {
    if (mQuery('#leform_isGDPRPublished_1').prop('checked')) {
        mQuery('.gdpr-content').removeClass('hide');
    }
};
Le.showSmartFormListPanel=function(){
    mQuery('#smart-action-form').removeClass('hide');
    mQuery('#le_smart_form_list').removeClass('hide');
    mQuery('#le_smart_form_fields_mapping').removeClass('hide').addClass('hide');
    mQuery('.smart-form-field-mapper-header-holder').removeClass('hide').addClass('hide');
    mQuery('.smart-action-panel').addClass('hide');
}
Le.showSmartFormFieldPanel=function(){
    mQuery('#le_smart_form_fields_mapping').removeClass('hide');
    mQuery('.smart-form-field-mapper-header-holder').removeClass('hide');
    mQuery('#le_smart_form_list').removeClass('hide').addClass('hide');
    mQuery('.smart-action-panel').removeClass('hide');
}
Le.openSmartFormPanel=function(ele){
    //alert(event);
    //event = event || window.event;
    //var currentlink = event.target || event.srcElement;
    //alert(currentlink);
    var formname=mQuery(ele).attr('data-formname');
    var formid=mQuery(ele).attr('data-formid');
    var header=formname;
    if(formname == ""){
        header=formid;
    }
    var fieldjson = mQuery('#data_formfield_'+formid).text();
    mQuery('.smart-form-field-mapper-header').html(header);
    mQuery('#leform_smartformname').val(formname);
    mQuery('#leform_smartformid').val(formid);
   // var fieldjson=mQuery(currentlink).attr('data-formfield');
    try{
        mQuery('#le_smart_form_fields_mapping').empty();
        var prototype = mQuery('#le_smart_form_fields_mapping').data('prototype');
        fieldjson=mQuery.parseJSON(fieldjson);
        mQuery.each(fieldjson, function (index, field) {
            // alert(field.name+"----->"+field.type);
            var smartfieldid="#leform_smartfields_"+index+"_smartfield";
            var leadfieldid="#leform_smartfields_"+index+"_leadfield";
            var dbfieldid="#leform_smartfields_"+index+"_dbfield";
            var fieldmapper = prototype.replace(/__name__/g,index);
            // Convert to DOM
            fieldmapper = mQuery(fieldmapper);
            var smartfield=fieldmapper.find(smartfieldid);
            smartfield.val(field.name);
            var leadfield=fieldmapper.find(leadfieldid);
            var dbfield=fieldmapper.find(dbfieldid);
            dbfield.val('f'+index);
            Le.activateChosenSelect(leadfield);
            fieldmapper.appendTo(mQuery('#le_smart_form_fields_mapping'));
        });
        mQuery('#smart-action-form').addClass('hide');
        Le.showSmartFormFieldPanel();
    }catch(err){
        alert(err);
    }
}
