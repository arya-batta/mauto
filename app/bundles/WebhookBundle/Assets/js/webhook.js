Le.sendHookTest = function() {

    var url = mQuery('#webhook_webhookUrl').val();
    var eventTypes = mQuery("#event-types input[type='checkbox']");
    var selectedTypes = [];

    eventTypes.each(function() {
        var item = mQuery(this);
        if (item.is(':checked')) {
            selectedTypes.push(item.val());
        }
    });

    var data = {
        action: 'webhook:sendHookTest',
        url: url,
        types: selectedTypes
    };

    var spinner = mQuery('#spinner');

    // show the spinner
    spinner.removeClass('hide');

    mQuery.ajax({
        url: leAjaxUrl,
        data: data,
        type: 'POST',
        dataType: "json",
        success: function(response) {
            if (response.success) {
                mQuery('#tester').html(response.html);
            }
        },
        error: function (request, textStatus, errorThrown) {
            var errorMsg = errorThrown;
            if (typeof request.responseJSON !== 'undefined') {
                errorMsg = request.responseJSON;
                if (errorMsg.errors && errorMsg.errors[0] && errorMsg.errors[0].message) {
                    errorMsg = errorMsg.errors[0].message;
                }
            }
            var spandiv = '<div class="has-error"><span class="help-block">'+errorMsg+'</span></div>';
            mQuery('#tester').html(spandiv);
            //Le.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function(response) {
            spinner.addClass('hide');
        }
    })
};