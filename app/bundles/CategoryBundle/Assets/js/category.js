/** CategoryBundle **/

Le.categoryOnLoad = function (container, response) {
    if (mQuery(container + ' #list-search').length) {
        Le.activateSearchAutocomplete('list-search', 'category');
    }

    if (response && response.inForm) {
        var newOption = mQuery('<option />').val(response.categoryId);
        newOption.html(response.categoryName);

        mQuery(".category-select option:last").prev().before(newOption);
        newOption.prop('selected', true);

        mQuery('.category-select').trigger("chosen:updated");
    }
};