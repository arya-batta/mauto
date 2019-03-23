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