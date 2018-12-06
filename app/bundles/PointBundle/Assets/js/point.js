//PointBundle
Le.pointOnLoad = function (container) {
    if (mQuery(container + ' #list-search').length) {
        Le.activateSearchAutocomplete('list-search', 'point');
    }
    Le.removeActionButtons();
    var value = mQuery('#point_properties_campaigntype').val();
    Le.getSelectedCampaignValue(value);
};

Le.pointTriggerOnLoad = function (container) {
    if (mQuery(container + ' #list-search').length) {
        Le.activateSearchAutocomplete('list-search', 'point.trigger');
    }

    if (mQuery('#triggerEvents')) {
        //make the fields sortable
        mQuery('#triggerEvents').sortable({
            items: '.trigger-event-row',
            handle: '.reorder-handle',
            stop: function(i) {
                mQuery.ajax({
                    type: "POST",
                    url: leAjaxUrl + "?action=point:reorderTriggerEvents",
                    data: mQuery('#triggerEvents').sortable("serialize") + "&triggerId=" + mQuery('#pointtrigger_sessionId').val()
                });
            }
        });

        mQuery('#triggerEvents .trigger-event-row').on('mouseover.triggerevents', function() {
            mQuery(this).find('.form-buttons').removeClass('hide');
        }).on('mouseout.triggerevents', function() {
            mQuery(this).find('.form-buttons').addClass('hide');
        }).on('dblclick.triggerevents', function(event) {
            event.preventDefault();
            mQuery(this).find('.btn-edit').first().click();
        });
    }
};

Le.pointTriggerEventOnLoad = function (container, response) {
    //new action created so append it to the form
    if (response.eventHtml) {
        var newHtml = response.eventHtml;
        var eventId = '#triggerEvent_' + response.eventId;
        if (mQuery(eventId).length) {
            //replace content
            mQuery(eventId).replaceWith(newHtml);
            var newField = false;
        } else {
            //append content
            mQuery(newHtml).appendTo('#triggerEvents');
            var newField = true;
        }

        //initialize tooltips
        mQuery(eventId + " *[data-toggle='tooltip']").tooltip({html: true});

        //activate new stuff
        mQuery(eventId + " a[data-toggle='ajax']").click(function (event) {
            event.preventDefault();
            return Le.ajaxifyLink(this, event);
        });

        //initialize ajax'd modals
        mQuery(eventId + " a[data-toggle='ajaxmodal']").on('click.ajaxmodal', function (event) {
            event.preventDefault();

            Le.ajaxifyModal(this, event);
        });

        mQuery('#triggerEvents .trigger-event-row').off(".triggerevents");
        mQuery('#triggerEvents .trigger-event-row').on('mouseover.triggerevents', function() {
            mQuery(this).find('.form-buttons').removeClass('hide');
        }).on('mouseout.triggerevents', function() {
            mQuery(this).find('.form-buttons').addClass('hide');
        }).on('dblclick.triggerevents', function(event) {
            event.preventDefault();
            mQuery(this).find('.btn-edit').first().click();
        });

        //show events panel
        if (!mQuery('#events-panel').hasClass('in')) {
            mQuery('a[href="#events-panel"]').trigger('click');
        }

        if (mQuery('#triggerEventPlaceholder').length) {
            mQuery('#triggerEventPlaceholder').remove();
        }
    }
};

Le.getPointActionPropertiesForm = function(actionType) {
    Le.activateLabelLoadingIndicator('point_type');

    var query = "action=point:getActionForm&actionType=" + actionType;
    mQuery.ajax({
        url: leAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (typeof response.html != 'undefined') {
                mQuery('#pointActionProperties').html(response.html);
                Le.onPageLoad('#pointActionProperties', response);
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
Le.EnablesOption = function (urlActionProperty) {
    if (urlActionProperty === 'point_properties_returns_within' && mQuery('#point_properties_returns_within').val() > 0) {
        mQuery('#point_properties_returns_after').val(0);
    } else {
        if (urlActionProperty === 'point_properties_returns_after' && mQuery('#point_properties_returns_after').val() > 0) {
            mQuery('#point_properties_returns_within').val(0);
        }
    }
};

Le.getSelectedCampaignValue = function (value) {
    if(value == "broadcast") {
        var formType = 'point_properties_driplist';
        if (mQuery(location).attr('href').includes("automations")) {
            formType = 'campaignevent_properties_driplist';
        }
        Le.resetDropDownValues(formType);
        mQuery('#pointemailaction').removeClass('hide');
        mQuery('#pointdripemailaction').addClass('hide');
        mQuery('#dripemaillist').addClass('hide');
    } else if(value == "drip") {
        var formType = 'point_properties_emails';
        if (mQuery(location).attr('href').includes("automations")) {
            formType = 'campaignevent_properties_emails';
        }
        Le.resetDropDownValues(formType);
        mQuery('#pointdripemailaction').removeClass('hide');
        var dripList=mQuery('#point_properties_dripemail').val();
        if(dripList != ''){
            mQuery('#dripemaillist').removeClass('hide');
        }
        mQuery('#pointemailaction').addClass('hide');
    } else {
        mQuery('#pointdripemailaction').addClass('hide');
        mQuery('#pointemailaction').addClass('hide');
    }
};
Le.convertDripFilterInput = function (templateId) {
    var eventType = 'point_properties_driplist';
    if (mQuery(location).attr('href').includes("automations")) {
        eventType = 'campaignevent_properties_driplist';
    }
    var query = "action=point:getDripFilterInput&templateId=" + templateId;
    Le.activateLabelLoadingIndicator(eventType);
    mQuery('#dripemaillist').removeClass('hide');
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
                //var index=0;
                mQuery.each(templateOptions, function (value, label) {
                    var newOption = mQuery('<option/>').val(value).text(label);
                    //if(index == 0){
                     //   mQuery(newOption).attr('selected','selected');
                   // }
                    newOption.appendTo(mQuery('#'+eventType));
                   // index++;
                });
                Le.activateChosenSelect('#'+eventType);
            }
        },
        complete: function() {
            Le.removeLabelLoadingIndicator();
        }
    });
};
Le.resetDropDownValues =function (type) {
    mQuery('#'+type + ' ' +'option').removeAttr("selected");
    if (mQuery('#'+type + '_chosen').length) {
        mQuery('#'+type).chosen('destroy');
    }
    Le.activateChosenSelect(mQuery('#'+type));
};
