/* PluginBundle */
Le.matchedFields = function (index, object, integration) {
    var compoundMauticFields = ['mauticContactTimelineLink'];

    if (mQuery('#integration_details_featureSettings_updateDncByDate_0').is(':checked')) {
        compoundMauticFields.push('mauticContactIsContactableByEmail');
    }
    var integrationField = mQuery('#integration_details_featureSettings_'+object+'Fields_i_' + index).attr('data-value');
    var mauticField = mQuery('#integration_details_featureSettings_'+object+'Fields_m_' + index + ' option:selected').val();

    if (mQuery.inArray(mauticField, compoundMauticFields) >= 0) {
        mQuery('.btn-arrow' + index).removeClass('active');
        mQuery('#integration_details_featureSettings_'+object+'Fields_update_mautic'+ index +'_0').attr('checked', 'checked');
        mQuery('input[name="integration_details[featureSettings]['+object+'Fields][update_mautic' + index + ']"]').prop('disabled', true).trigger("chosen:updated");
        mQuery('.btn-arrow' + index).addClass('disabled');
    } else {
        mQuery('input[name="integration_details[featureSettings]['+object+'Fields][update_mautic' + index + ']"]').prop('disabled', false).trigger("chosen:updated");
        mQuery('.btn-arrow' + index).removeClass('disabled');
    }
    if (object == 'lead') {
        var updateMauticField = mQuery('input[name="integration_details[featureSettings]['+object+'Fields][update_mautic' + index + ']"]:checked').val();
    } else {
        var updateMauticField = mQuery('input[name="integration_details[featureSettings]['+object+'Fields][update_mautic_company' + index + ']"]:checked').val();
    }
    Le.ajaxActionRequest('plugin:matchFields', {object: object, integration: integration, integrationField : integrationField, mauticField: mauticField, updateMautic : updateMauticField}, function(response) {
        var theMessage = (response.success) ? '<i class="fa fa-check-circle text-success"></i>' : '';
        mQuery('#matched-' + index + "-" + object).html(theMessage);
    });
};
Le.initiateIntegrationAuthorization = function() {
    mQuery('#integration_details_in_auth').val(1);

    Le.postForm(mQuery('form[name="integration_details"]'), 'loadIntegrationAuthWindow');
};

Le.loadIntegrationAuthWindow = function(response) {
    if (response.newContent) {
        Le.processModalContent(response, '#IntegrationEditModal');
    } else {
        Le.stopPageLoadingBar();
        Le.stopIconSpinPostEvent();
        mQuery('#integration_details_in_auth').val(0);

        if (response.authUrl) {
            var generator = window.open(response.authUrl, 'integrationauth', 'height=500,width=500');

            if (!generator || generator.closed || typeof generator.closed == 'undefined') {
                alert(leLang.popupBlockerMessage);
            }
        }
    }
};

Le.refreshIntegrationForm = function() {
    var opener = window.opener;
    if(opener) {
            var form = opener.mQuery('form[name="integration_details"]');
            if (form.length) {
                var action = form.attr('action');
                if (action) {
                    opener.Le.startModalLoadingBar('#IntegrationEditModal');
                    opener.Le.loadAjaxModal('#IntegrationEditModal', action);
                }
            }
    }

    window.close()
};

Le.integrationOnLoad = function(container, response) {
    if (response && response.name) {
        var integration = '.integration-' + response.name;
        if (response.enabled) {
            mQuery(integration).removeClass('integration-disabled');
        } else {
            mQuery(integration).addClass('integration-disabled');
        }
    } else {
        Le.filterIntegrations();
    }
    mQuery('[data-toggle="tooltip"]').tooltip();
};

Le.integrationConfigOnLoad = function(container) {
    if (mQuery('.fields-container select.integration-field').length) {
        var selects = mQuery('.fields-container select.integration-field');
        selects.on('change', function() {
            var select   = mQuery(this),
                newValue = select.val(),
                previousValue = select.attr('data-value');
            select.attr('data-value', newValue);

            var groupSelects = mQuery(this).closest('.fields-container').find('select.integration-field').not(select);

            // Enable old value
            if (previousValue) {
                mQuery('option[value="' + previousValue + '"]', groupSelects).each(function() {
                    if (!mQuery(this).closest('select').prop('disabled')) {
                        mQuery(this).prop('disabled', false);
                        mQuery(this).removeAttr('disabled');
                    }
                });
            }

            if (newValue) {
                mQuery('option[value="' + newValue + '"]', groupSelects).each(function() {
                    if (!mQuery(this).closest('select').prop('disabled')) {
                        mQuery(this).prop('disabled', true);
                        mQuery(this).attr('disabled', 'disabled');
                    }
                });
            }

            groupSelects.each(function() {
                mQuery(this).trigger('chosen:updated');
            });
        });

        selects.each(function() {
            if (!mQuery(this).closest('.field-container').hasClass('hide')) {
                mQuery(this).trigger('change');
            }
        });
    }

    mQuery('#mapping-container .btn-new-field-mapping').off().on('click', function() {
        var mappingIndex = parseInt(mQuery('.integration_field_mapping').attr("data-index"));
        mQuery('.integration_field_mapping').attr("data-index",mappingIndex+1);
        var prototype = mQuery('.integration_field_mapping').data('prototype');
        prototype = prototype.replace(/__name__/g, mappingIndex);
        // Convert to DOM
        prototype = mQuery(prototype);
        var localList=mQuery(prototype).find('#integration_field_mapping_field_mapping_'+mappingIndex+'_localfield');
        Le.activateChosenSelect(localList);
        var remoteList=mQuery(prototype).find('#integration_field_mapping_field_mapping_'+mappingIndex+'_remotefield');
        Le.activateChosenSelect(remoteList);
        mQuery(prototype).appendTo(mQuery('.integration_field_mapping'));
        Le.registerEventsForMappingRemove();
        Le.registerEventsForMappingLists();
    });
    mQuery('#mapping-container .btn-save-field-mapping').off().on('click', function() {
        mQuery('form[name="integration_field_mapping"]').submit();
    });
    Le.registerEventsForMappingRemove();
    Le.createDefaultFieldMapping();
    Le.registerEventsForMappingLists();

};

Le.filterIntegrations = function(update) {
    var filter = mQuery('#integrationFilter').val();

    if (update) {
        mQuery.ajax({
            url: leAjaxUrl,
            type: "POST",
            data: "action=plugin:setIntegrationFilter&plugin=" + filter
        });
    }

    //activate shuffles
    if (mQuery('.shuffle-integrations').length) {
        var grid = mQuery(".shuffle-integrations");

        //give a slight delay in order for images to load so that shuffle starts out with correct dimensions
        setTimeout(function () {
            grid.shuffle('shuffle', function($el, shuffle) {
                if (filter) {
                    return $el.hasClass('plugin' + filter);
                } else {
                    return true;
                }
            });

            // Update shuffle on sidebar minimize/maximize
            mQuery("html")
                .on("fa.sidebar.minimize", function () {
                    grid.shuffle("update");
                })
                .on("fa.sidebar.maximize", function () {
                    grid.shuffle("update");
                });
        }, 500);
    }
};

Le.getIntegrationLeadFields = function (integration, el, settings) {

    if (typeof settings == 'undefined') {
        settings = {};
    }
    settings.integration = integration;
    settings.object      = 'lead';

    Le.getIntegrationFields(settings, 1, el);
};

Le.getIntegrationCompanyFields = function (integration, el, settings) {
    if (typeof settings == 'undefined') {
        settings = {};
    }
    settings.integration = integration;
    settings.object      = 'company';

    Le.getIntegrationFields(settings, 1, el);
};

Le.getIntegrationFields = function(settings, page, el) {
    var object    = settings.object ? settings.object : 'lead';
    var fieldsTab = ('lead' === object) ? '#fields-tab' : '#'+object+'-fields-container';

    if (el && mQuery(el).is('input')) {
        Le.activateLabelLoadingIndicator(mQuery(el).attr('id'));

        var namePrefix = mQuery(el).attr('name').split('[')[0];
        if ('integration_details' !== namePrefix) {
            var nameParts = mQuery(el).attr('name').match(/\[.*?\]+/g);
            nameParts = nameParts.slice(0, -1);
            settings.prefix = namePrefix + nameParts.join('') + "[" + object + "Fields]";
        }
    }
    var fieldsContainer = '#'+object+'FieldsContainer';

    var inModal = mQuery(fieldsContainer).closest('.modal');
    if (inModal) {
        var modalId = '#'+mQuery(fieldsContainer).closest('.modal').attr('id');
        Le.startModalLoadingBar(modalId);
    }

    Le.ajaxActionRequest('plugin:getIntegrationFields',
        {
            page: page,
            integration: (settings.integration) ? settings.integration : null,
            settings: settings
        },
        function(response) {
            if (response.success) {
                mQuery(fieldsContainer).replaceWith(response.html);
                Le.onPageLoad(fieldsContainer);
                Le.integrationConfigOnLoad(fieldsContainer);
                if (mQuery(fieldsTab).length) {
                    mQuery(fieldsTab).removeClass('hide');
                }
            } else {
                if (mQuery(fieldsTab).length) {
                    mQuery(fieldsTab).addClass('hide');
                }
            }

            if (el) {
                Le.removeLabelLoadingIndicator();
            }

            if (inModal) {
                Le.stopModalLoadingBar(modalId);
            }
        }
    );
};

Le.getIntegrationConfig = function (el, settings) {
    Le.activateLabelLoadingIndicator(mQuery(el).attr('id'));

    if (typeof settings == 'undefined') {
        settings = {};
    }

    settings.name = mQuery(el).attr('name');
    var data = {integration: mQuery(el).val(), settings: settings};
    mQuery('.integration-campaigns-status').html('');
    mQuery('.integration-config-container').html('');

    Le.ajaxActionRequest('plugin:getIntegrationConfig', data,
        function (response) {
            if (response.success) {
                mQuery('.integration-config-container').html(response.html);
                Le.onPageLoad('.integration-config-container', response);
            }

            Le.integrationConfigOnLoad('.integration-config-container');
            Le.removeLabelLoadingIndicator();
        }
    );


};

Le.getIntegrationCampaignStatus = function (el, settings) {
    Le.activateLabelLoadingIndicator(mQuery(el).attr('id'));
    if (typeof settings == 'undefined') {
        settings = {};
    }

    // Extract the name and ID prefixes
    var prefix = mQuery(el).attr('name').split("[")[0];

    settings.name = mQuery('#'+prefix+'_properties_integration').attr('name');
    var data = {integration:mQuery('#'+prefix+'_properties_integration').val(),campaign: mQuery(el).val(), settings: settings};

    mQuery('.integration-campaigns-status').html('');
    mQuery('.integration-campaigns-status').removeClass('hide');
    Le.ajaxActionRequest('plugin:getIntegrationCampaignStatus', data,
        function (response) {

            if (response.success) {
                mQuery('.integration-campaigns-status').append(response.html);
                Le.onPageLoad('.integration-campaigns-status', response);
            }

            Le.integrationConfigOnLoad('.integration-campaigns-status');
            Le.removeLabelLoadingIndicator();
        }
    );
};

Le.getIntegrationCampaigns = function (el, settings) {
    Le.activateLabelLoadingIndicator(mQuery(el).attr('id'));

    var data = {integration: mQuery(el).val()};

    mQuery('.integration-campaigns').html('');

    Le.ajaxActionRequest('plugin:getIntegrationCampaigns', data,
        function (response) {
            if (response.success) {
                mQuery('.integration-campaigns').html(response.html);
                Le.onPageLoad('.integration-campaigns', response);
            }

            Le.integrationConfigOnLoad('.integration-campaigns');
            Le.removeLabelLoadingIndicator();
        }
    );
};
Le.loadLeadAdsForm=function(pageId){
    var eventType = 'campaignevent_properties_leadgenform';
    var query = "action=plugin:loadLeadAdsForm&pageId=" + pageId;
    if(pageId == "-1"){
       mQuery("#fbldads-leadgenform").addClass('hide');
    }else{
        mQuery("#fbldads-leadgenform").removeClass('hide');
    }
    Le.activateLabelLoadingIndicator(eventType);
    mQuery.ajax({
        url: leAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (response.success) {
                var templateOptions = response.values;
                mQuery('#'+eventType).html('');
                if (mQuery('#'+eventType + '_chosen').length) {
                    mQuery('#'+eventType).chosen('destroy');
                }
                mQuery.each(templateOptions, function (value, label) {
                    var newOption = mQuery('<option/>').val(value).text(label);
                    newOption.appendTo(mQuery('#'+eventType));
                });
                Le.activateChosenSelect('#'+eventType);
            }
        },
        complete: function() {
            Le.removeLabelLoadingIndicator();
        }
    });
}
Le.loadCustomAudiences = function (adAccount) {
    var eventType = 'campaignevent_properties_customaudience';
    var query = "action=plugin:loadCustomAudience&adAccount=" + adAccount;
    Le.activateLabelLoadingIndicator(eventType);
    mQuery.ajax({
        url: leAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (response.success) {
                var templateOptions = response.values;
                mQuery('#'+eventType).html('');
                if (mQuery('#'+eventType + '_chosen').length) {
                    mQuery('#'+eventType).chosen('destroy');
                }
                mQuery.each(templateOptions, function (value, label) {
                    var newOption = mQuery('<option/>').val(value).text(label);
                    newOption.appendTo(mQuery('#'+eventType));
                });
                Le.activateChosenSelect('#'+eventType);
            }
        },
        complete: function() {
            Le.removeLabelLoadingIndicator();
        }
    });
};
Le.saveTokenvalue = function (name){
    var tokenvalue = mQuery("#"+name+"-token-value").val();
    mQuery("."+name+"-container .help-block").addClass('hide');
    mQuery("."+name+"-container").removeClass('has-error');
    if(tokenvalue == ""){
        mQuery("."+name+"-container").addClass('has-error');
        mQuery("."+name+"-container .help-block").removeClass('hide');
        return;
    }
    var data = {
        token: tokenvalue,
        integrationname : name,
    };
    Le.ajaxActionRequest('plugin:saveTokenvalue', data,
        function (response) {
            if (response.success) {
                Le.redirectWithBackdrop(response.redirect);
            }
        }
    );
};
Le.removeTokenvalue = function (name){
    var data = {
        integrationname : name,
    };
    Le.ajaxActionRequest('plugin:removeTokenvalue', data,
        function (response) {
            if (response.success) {
                Le.redirectWithBackdrop(response.redirect);
            }
        }
    );
};

//New integration related methods are available below

Le.createDefaultFieldMapping=function(){
    try{
        var mappingindex=mQuery('.integration_field_mapping').attr("data-index");
        if(mappingindex == 1){
            mQuery( "#mapping-container .btn-new-field-mapping" ).trigger( "click" );
        }
    }catch(err){
        alert(err);
    }
}
Le.registerEventsForMappingRemove=function(){
    mQuery('.integration_field_mapping .remove-selected').each( function (index, el) {
        mQuery(el).off().on('click', function () {
            mQuery(this).closest('.panel').animate(
                {'opacity': 0},
                'fast',
                function () {
                    mQuery(this).remove();
                    Le.updateFieldMappingPositioning();
                }
            );

        });
    });
}

Le.replaceMappingElementsIndex=function(panel,id,oldindex,newindex)
{
    if(mQuery(panel).has(id).length){
        var el=mQuery(panel).find(id);
        if (typeof el.attr('name') !== typeof undefined && el.attr('name') !== false) {
            var nameattr=el.attr('name');
            nameattr=nameattr.replace('['+oldindex+']','['+newindex+']');
            el.attr('name',nameattr);
        }
        if (typeof el.attr('id') !== typeof undefined && el.attr('id') !== false) {
            var idattr=el.attr('id');
            idattr=idattr.replace('_'+oldindex+'_','_'+newindex+'_');
            el.attr('id',idattr);
        }
    }
}

Le.updateFieldMappingPositioning = function () {
    try{
        var newindex=0;
        var mappingBase='integration_field_mapping_field_mapping_';
        var mappingHolder=mQuery('.integration_field_mapping');
        var panels=mappingHolder.children();
        for(var cindex=0;cindex<panels.length;cindex++){
            var panel=panels[cindex];
            var oldindex=mQuery(panel).attr("data-mapping-index");
            mQuery(panel).attr("data-mapping-index",newindex);
            var localfield='#'+mappingBase+oldindex+'_localfield';
            Le.replaceMappingElementsIndex(panel,localfield,oldindex,newindex);
            var localfieldChosen='#'+mappingBase+oldindex+'_localfield_chosen';
            Le.replaceMappingElementsIndex(panel,localfieldChosen,oldindex,newindex);
            var remotefield='#'+mappingBase+oldindex+'_remotefield';
            Le.replaceMappingElementsIndex(panel,remotefield,oldindex,newindex);
            var remotefieldChosen='#'+mappingBase+oldindex+'_remotefield_chosen';
            Le.replaceMappingElementsIndex(panel,remotefieldChosen,oldindex,newindex);
            var defaultfield='#'+mappingBase+oldindex+'_defaultvalue';
            Le.replaceMappingElementsIndex(panel,defaultfield,oldindex,newindex);
            newindex++;
        }
        mQuery('.integration_field_mapping').attr("data-index",newindex);
    }catch(ex){
        alert(ex);
    }
};
Le.registerEventsForMappingLists=function(){
    mQuery('.integration_field_mapping .local-mapping-fields').off().on('change', function(){
        if (mQuery(this).val()) {
            var prototype=mQuery(this).closest('.panel');
            var mappingIndex=prototype.attr('data-mapping-index');
            var fieldname=mQuery(this).val();
            if(Le.integration_fieldmapping_details[fieldname]){
                var tmpprototype = mQuery('.integration_field_mapping').data('prototype');
                tmpprototype = tmpprototype.replace(/__name__/g, mappingIndex);
                // Convert to DOM
                tmpprototype = mQuery(tmpprototype);

                //Reset default value field
                mQuery(prototype).find('.mapping-default-segment').replaceWith(mQuery(tmpprototype).find('.mapping-default-segment'));
                var fieldType=Le.integration_fieldmapping_details[fieldname]['type'];
                var nameAttr='integration_field_mapping[field_mapping]['+mappingIndex+'][defaultvalue]';
                var mappingdefaultEl = "input[name='" + nameAttr + "']";
                if (fieldType == 'select' || fieldType == 'multiselect' || fieldType == 'boolean') {
                    var fieldOptions=Le.integration_fieldmapping_details[fieldname]['properties'];
                    var selectEl = mQuery('<select>');
                    selectEl.attr('class','form-control not-chosen');
                    selectEl.attr('name',nameAttr);
                    selectEl.attr('id','integration_field_mapping_field_mapping_'+mappingIndex+'_defaultvalue');
                    mQuery(prototype).find('input[name="' + nameAttr + '"]').replaceWith(selectEl);
                    mappingdefaultEl="select[name='" + nameAttr + "']";
                    mQuery.each(fieldOptions, function(index, val) {
                        if (mQuery.isPlainObject(val)) {
                            mQuery('<option>').val(val.value).text(val.label).appendTo(mappingdefaultEl);
                        } else {
                            mQuery('<option>').val(index).text(val).appendTo(mappingdefaultEl);
                        }
                    });
                    Le.activateChosenSelect(mQuery(mappingdefaultEl));
                }else if(fieldType == 'owner_id' || fieldType == 'leadlist' || fieldType == 'listoptin' || fieldType == 'tags'){
                    var selectEl = mQuery('<select>');
                    selectEl.attr('class','form-control not-chosen');
                    selectEl.attr('name',nameAttr);
                    selectEl.attr('id','integration_field_mapping_field_mapping_'+mappingIndex+'_defaultvalue');
                    mQuery(prototype).find('input[name="' + nameAttr + '"]').replaceWith(selectEl);
                    mappingdefaultEl="select[name='" + nameAttr + "']";
                    var fieldOptions=Le.integration_property_choices[fieldname];
                    mQuery.each(fieldOptions, function(index, val) {
                        if (mQuery.isPlainObject(val)) {
                            mQuery('<option>').val(val.value).text(val.label).appendTo(mappingdefaultEl);
                        } else {
                            mQuery('<option>').val(index).text(val).appendTo(mappingdefaultEl);
                        }
                    });
                    Le.activateChosenSelect(mQuery(mappingdefaultEl));
                }else if (fieldType == 'datetime') {
                    mQuery(mappingdefaultEl).datetimepicker({
                        format: 'Y-m-d H:i',
                        lazyInit: true,
                        validateOnBlur: false,
                        allowBlank: true,
                        scrollInput: false
                    });
                } else if (fieldType == 'date') {
                    mQuery(mappingdefaultEl).datetimepicker({
                        timepicker: false,
                        format: 'Y-m-d',
                        lazyInit: true,
                        validateOnBlur: false,
                        allowBlank: true,
                        scrollInput: false,
                        closeOnDateSelect: true
                    });
                } else if (fieldType == 'time') {
                    mQuery(mappingdefaultEl).datetimepicker({
                        datepicker: false,
                        format: 'H:i',
                        lazyInit: true,
                        validateOnBlur: false,
                        allowBlank: true,
                        scrollInput: false
                    });
                }else {
                    mQuery(mappingdefaultEl).attr('type', fieldType);
                }
            }
        }
    });
}