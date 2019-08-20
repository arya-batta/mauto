//ApiBundle
Le.clientOnLoad = function (container) {
    if (mQuery(container + ' #list-search').length) {
        Le.activateSearchAutocomplete('list-search', 'api.client');
    }
};

Le.refreshApiClientForm = function(url, modeEl) {
    var mode = mQuery(modeEl).val();

    if (mQuery('#client_redirectUris').length) {
        mQuery('#client_redirectUris').prop('disabled', true);
    } else {
        mQuery('#client_callback').prop('disabled', true);
    }
    mQuery('#client_name').prop('disabled', true);

    Le.loadContent(url + '/' + mode);
};

Le.regenerateApiKey = function(){
    Le.ajaxActionRequest('api:regenerateApi', {}, function (response) {
        if (response.success) {
            var apikey = response.apikey;
            mQuery('#api_token').val(apikey);
        }
    });
};