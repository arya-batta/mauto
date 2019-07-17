//ConfigBundle
/**
 * @deprecated - use Le.initializeFormFieldVisibilitySwitcher() instead
 * @param formName
 */
Le.hideSpecificConfigFields = function(formName) {
	initializeFormFieldVisibilitySwitcher(formName);
};

Le.removeConfigValue = function(action, el) {
    Le.executeAction(action, function(response) {
    	if (response.success) {
            mQuery(el).parent().addClass('hide');
        }
	});
};

/**
 *
 * @returns string|false
 */
Le.parseQuery = function (query) {
    var vars = query.split('&');
    var queryString = {};
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split('=');
        var key = decodeURIComponent(pair[0]);
        var value = decodeURIComponent(pair[1]);
        // If first entry with this name
        if (typeof queryString[key] === 'undefined') {
            queryString[key] = decodeURIComponent(value);
            // If second entry with this name
        } else if (typeof queryString[key] === 'string') {
            var arr = [queryString[key], decodeURIComponent(value)];
            queryString[key] = arr;
            // If third or later entry with this name
        } else {
            queryString[key].push(decodeURIComponent(value));
        }
    }
    return queryString;
}

Le.parseUrlHashParameter = function(url) {
    var url = url.split('#');
    if ('undefined' != typeof url[1]) {
        return url[1];
    }

    return false;
}

Le.observeConfigTabs = function() {
    if (!mQuery('#config_emailconfig_last_shown_tab').length) {
        return;
    }
    var parameters = Le.parseQuery(window.location.search.substr(1));
    if ('undefiend' != typeof parameters['tab']) {
        mQuery('#config_emailconfig_last_shown_tab').val(parameters['tab']);
        mQuery('a[data-toggle="tab"]').each(function (i, tab) {
            if (mQuery(tab).attr('href') == ('#' + parameters['tab'])) {
                mQuery(tab).tab('show');
            }
        });
    }

    mQuery('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        var tab = Le.parseUrlHashParameter(e.target.href);
        if (tab) {
            mQuery('#config_emailconfig_last_shown_tab').val(tab);
        }
    });
}
mQuery(Le.observeConfigTabs);