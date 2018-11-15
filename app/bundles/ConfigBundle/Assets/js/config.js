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