//live search vars
leVars.liveCache            = new Array();
leVars.lastSearchStr        = "";
leVars.globalLivecache      = new Array();
leVars.lastGlobalSearchStr  = "";

/**
 * Check if the the entity ID is temporary (for new entities)
 *
 * @param string idInputSelector
 */
Le.isNewEntity = function(idInputSelector) {
    id = mQuery(idInputSelector);
    if (id.length) {
        return id.val().match("^new_");
    }
    return null;
};

/**
 * Get entity ID of pages that have an input with id of entityId
 *
 * @returns {*}
 */
Le.getEntityId = function() {
    return (mQuery('input#entityId').length) ? mQuery('input#entityId').val() : 0;
};

/**
 * Reorder table data
 * @param name
 * @param orderby
 * @param tmpl
 * @param target
 */
Le.reorderTableData = function (name, orderby, tmpl, target, baseUrl) {
    if (typeof baseUrl == 'undefined') {
        baseUrl = window.location.pathname;
    }

    if (baseUrl.indexOf('tmpl') == -1) {
        baseUrl = baseUrl + "?tmpl=" + tmpl
    }

    var route = baseUrl + "&name=" + name + "&orderby=" + encodeURIComponent(orderby);
    Le.loadContent(route, '', 'POST', target);
};

/**
 *
 * @param name
 * @param filterby
 * @param filterValue
 * @param tmpl
 * @param target
 */
Le.filterTableData = function (name, filterby, filterValue, tmpl, target, baseUrl) {
    if (typeof baseUrl == 'undefined') {
        baseUrl = window.location.pathname;
    }

    if (baseUrl.indexOf('tmpl') == -1) {
        baseUrl = baseUrl + "?tmpl=" + tmpl
    }

    var route = baseUrl + "&name=" + name + "&filterby=" + encodeURIComponent(filterby) + "&value=" + encodeURIComponent(filterValue)
    Le.loadContent(route, '', 'POST', target);
};

/**
 *
 * @param name
 * @param limit
 * @param tmpl
 * @param target
 */
Le.limitTableData = function (name, limit, tmpl, target, baseUrl) {
    if (typeof baseUrl == 'undefined') {
        baseUrl = window.location.pathname;
    }

    if (baseUrl.indexOf('tmpl') == -1) {
        baseUrl = baseUrl + "?tmpl=" + tmpl
    }

    var route = baseUrl + "&name=" + name + "&limit=" + limit;
    Le.loadContent(route, '', 'POST', target);
};


/**
 * Filters list based on search contents
 */
Le.filterList = function (e, elId, route, target, liveCacheVar, action, overlayEnabled, overlayTarget) {
    if (typeof liveCacheVar == 'undefined') {
        liveCacheVar = "liveCache";
    }

    var el = mQuery('#' + elId);
    //only submit if the element exists, its a livesearch, or on button click

    if (el.length && (e.data.livesearch || mQuery(e.target).prop('tagName') == 'BUTTON' || mQuery(e.target).parent().prop('tagName') == 'BUTTON')) {
        var value = el.val().trim();
        //should the content be cleared?
        if (!value) {
            //force action since we have no content
            action = 'clear';
        } else if (action == 'clear') {
            el.val('');
            el.typeahead('val', '');
            value = '';
        }

        //make the request
        //@TODO reevaluate search caching as it seems to cause issues
        if (false && value && value in leVars[liveCacheVar]) {
            var response = {"newContent": leVars[liveCacheVar][value]};
            response.target = target;
            response.overlayEnabled = overlayEnabled;
            response.overlayTarget = overlayTarget;

            Le.processPageContent(response);
        } else {
            var searchName = el.attr('name');
            if (searchName == 'undefined') {
                searchName = 'search';
            }
if(searchName == 'search' && value.includes(' ')){
if(!value.startsWith('"')){
    value='"'+value;
}
    if(!value.endsWith('"')){
        value=value+'"';
    }
}
            if (typeof Le.liveSearchXhr !== 'undefined') {
                //ensure current search request is aborted
                L['liveSearchXhr'].abort();
            }

            var btn = "button[data-livesearch-parent='" + elId + "']";
            if (mQuery(btn).length && !mQuery(btn).hasClass('btn-nospin') && !Le.filterButtonClicked) {
                Le.startIconSpinOnEvent(btn);
            }

            var tmpl = mQuery('#' + elId).data('tmpl');
            if (!tmpl) {
                tmpl = 'list';
            }

            var tmplParam = (route.indexOf('tmpl') == -1) ? '&tmpl=' + tmpl : '';

            // In a modal?
            var checkInModalTarget = (overlayTarget) ? overlayTarget : target;
            var modalParent        = mQuery(checkInModalTarget).closest('.modal');
            var inModal            = mQuery(modalParent).length > 0;

            if (inModal) {
                var modalTarget = '#' + mQuery(modalParent).attr('id');
                Le.startModalLoadingBar(modalTarget);
            }
            var showLoading = (inModal) ? false : true;

            Le.liveSearchXhr = mQuery.ajax({
                showLoadingBar: showLoading,
                url: route,
                type: "GET",
                data: searchName + "=" + encodeURIComponent(value) + tmplParam,
                dataType: "json",
                success: function (response) {
                    //cache the response
                    if (response.newContent) {
                        leVars[liveCacheVar][value] = response.newContent;
                    }
                    //note the target to be updated
                    response.target = target;
                    response.overlayEnabled = overlayEnabled;
                    response.overlayTarget = overlayTarget;

                    //update the buttons class and action
                    if (mQuery(btn).length) {
                        if (action == 'clear') {
                            mQuery(btn).attr('data-livesearch-action', 'search');
                            mQuery(btn).children('i').first().removeClass('fa-eraser').addClass('fa-search');
                        } else {
                            mQuery(btn).attr('data-livesearch-action', 'clear');
                            mQuery(btn).children('i').first().removeClass('fa-search').addClass('fa-eraser');
                        }
                    }

                    if (inModal) {
                        Le.processModalContent(response);
                        Le.stopModalLoadingBar(modalTarget);
                    } else {
                        Le.processPageContent(response);
                        Le.stopPageLoadingBar();
                    }
                },
                error: function (request, textStatus, errorThrown) {
                    Le.processAjaxError(request, textStatus, errorThrown);

                    //update the buttons class and action
                    if (mQuery(btn).length) {
                        if (action == 'clear') {
                            mQuery(btn).attr('data-livesearch-action', 'search');
                            mQuery(btn).children('i').first().removeClass('fa-eraser').addClass('fa-search');
                        } else {
                            mQuery(btn).attr('data-livesearch-action', 'clear');
                            mQuery(btn).children('i').first().removeClass('fa-search').addClass('fa-eraser');
                        }
                    }
                },
                complete: function() {
                    delete Le.liveSearchXhr;
                    delete Le.filterButtonClicked;
                }
            });
        }
    }
};

/**
 * Apply filter
 * @param list
 */
Le.setSearchFilter = function (el, searchId, string) {
    if (typeof searchId == 'undefined')
        searchId = '#list-search';
    else
        searchId = '#' + searchId;

    if (string || string === '') {
        var current = string;
    } else {
        var filter  = mQuery(el).val();
        var current = mQuery('#list-search').typeahead('val') + " " + filter;
    }

    //append the filter
    mQuery(searchId).typeahead('val', current);

    //submit search
    var e = mQuery.Event("keypress", {which: 13});
    e.data = {};
    e.data.livesearch = true;
    Le.filterList(
        e,
        'list-search',
        mQuery(searchId).attr('data-action'),
        mQuery(searchId).attr('data-target'),
        'liveCache'
    );
};

/**
 * Unlock an entity
 *
 * @param model
 * @param id
 */
Le.unlockEntity = function (model, id, parameter) {
    mQuery.ajax({
        url: leAjaxUrl,
        type: "POST",
        data: "action=unlockEntity&model=" + model + "&id=" + id + "&parameter=" + parameter,
        dataType: "json"
    });
};

/**
 * Toggles published status of an entity
 *
 * @param el
 * @param model
 * @param id
 */
Le.togglePublishStatus = function (event, el, model, id, extra, backdrop) {
    event.preventDefault();

    var wasPublished = mQuery(el).hasClass('fa-toggle-on');
    if(model == "email.dripemail" && !wasPublished){
        var warningtxt = "Are you sure you want to activate this Campaign? Recipients will start receiving the emails as you configured in this campaign.";
        if(confirm(Le.translate(warningtxt))){

        } else {
            return;
        }
    }

    mQuery(el).removeClass('fa-toggle-on fa-toggle-off').addClass('fa-spin fa-spinner');

    //destroy tooltips so it can be regenerated
    mQuery(el).tooltip('destroy');
    //clear the lookup cache
    leVars.liveCache = new Array();

    if (backdrop) {
        Le.activateBackdrop();
    }

    if (extra) {
        extra = '&' + extra;
    }
    mQuery(el).tooltip('destroy');
    mQuery.ajax({
        url: leAjaxUrl,
        type: "POST",
        data: "action=togglePublishStatus&model=" + model + '&id=' + id + extra,
        dataType: "json",
        success: function (response) {
            if(!response.success){
                var addClass = (wasPublished) ? 'fa-toggle-on' : 'fa-toggle-off';
                mQuery(el).removeClass('fa-spin fa-spinner').addClass(addClass);
                var alertmsg="publish";
                if(wasPublished){
                    alertmsg="un publish";
                }
                alert("You don't have rights to "+alertmsg+" it");
            }else if (response.reload) {
                Le.redirectWithBackdrop(window.location);
            } else if (response.statusHtml) {
                mQuery(el).replaceWith(response.statusHtml);
                mQuery(el).tooltip({html: true, container: 'body'});
            }else if(response.senderprofile && reponse.success){
                var addClass = (wasPublished) ? 'fa-toggle-on' : 'fa-toggle-off';
                mQuery(el).removeClass('fa-spin fa-spinner').addClass(addClass);
            }
        },
        error: function (request, textStatus, errorThrown) {
            var addClass = (wasPublished) ? 'fa-toggle-on' : 'fa-toggle-off';
            mQuery(el).removeClass('fa-spin fa-spinner').addClass(addClass);

            Le.processAjaxError(request, textStatus, errorThrown);
        }
    });
};

/**
 * Executes a batch action
 *
 * @param action
 */
Le.executeBatchAction = function (action, el) {
    if (typeof Le.activeActions == 'undefined') {
        Le.activeActions = {};
    } else if (typeof Le.activeActions[action] != 'undefined') {
        // Action is currently being executed
        return;
    }

    var items = Le.getCheckedListIds(el, true);

    var queryGlue = action.indexOf('?') >= 0 ? '&' : '?';

    // Append the items to the action to send with the POST
    var action = action + queryGlue + 'ids=' + items;

    // Hand over processing to the executeAction method
    Le.executeAction(action);
};

/**
 * Checks that items are checked before showing confirmation
 *
 * @param container
 * @returns int
 */
Le.batchActionPrecheck = function(container) {
    if (typeof container == 'undefined') {
        container = '';
    }

    return mQuery(container + ' input[class=list-checkbox]:checked').length;
};

/**
 * Retrieves the IDs of the items checked in a list
 *
 * @param el
 * @param stringify
 * @returns {*}
 */
Le.getCheckedListIds = function(el, stringify) {
    var checkboxes = 'input[class=list-checkbox]:checked';

    // Check for a target
    if (typeof el != 'undefined' && el) {
        var target = mQuery(el).data('target');
        if (target) {
            checkboxes = target + ' ' + checkboxes;
        }
    }

    // Retrieve all of the selected items
    var items = mQuery(checkboxes).map(function () {
        return mQuery(this).val();
    }).get();

    if (stringify) {
        items = JSON.stringify(items);
    }

    return items;
};
