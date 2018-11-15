Le.getStageActionPropertiesForm = function(actionType) {
    Le.activateLabelLoadingIndicator('stage_type');

    var query = "action=stage:getActionForm&actionType=" + actionType;
    mQuery.ajax({
        url: leAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (typeof response.html != 'undefined') {
                mQuery('#stageActionProperties').html(response.html);
                Le.onPageLoad('#stageActionProperties', response);
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