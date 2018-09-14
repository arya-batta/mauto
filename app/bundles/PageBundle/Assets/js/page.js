//PageBundle
Mautic.pageOnLoad = function (container, response) {
    if (mQuery(container + ' #list-search').length) {
        Mautic.activateSearchAutocomplete('list-search', 'page.page');
    }

    if (mQuery(container + ' #page_template').length) {
        Mautic.toggleBuilderButton(mQuery('#page_template').val() == '');

        //Handle autohide of "Redirect URL" field if "Redirect Type" is none
        if (mQuery(container + ' select[name="page[redirectType]"]').length) {
            //Auto-hide on page loading
            Mautic.autoHideRedirectUrl(container);

            //Auto-hide on select changing
            mQuery(container + ' select[name="page[redirectType]"]').chosen().change(function(){
                Mautic.autoHideRedirectUrl(container);
            });
        }

        // Preload tokens for code mode builder
      //  Mautic.getTokens(Mautic.getBuilderTokensMethod(), function(){});
       // Mautic.initSelectTheme(mQuery('#page_template'));
        Mautic.initSelectBeeTemplate(mQuery('#page_template'),'page');
    }

    // Open the builder directly when saved from the builder
    if (response && response.inBuilder) {
        Mautic.launchBuilder('page');
        Mautic.processBuilderErrors(response);
    }
    Mautic.removeActionButtons();

    mQuery('.next-tab, .prevv-tab, .ui-state-default').click(function() {
        mQuery('#page_Title').removeClass('has-success has-error');
        mQuery('#page_Title .help-block').html("");
        if(mQuery('#page_title').val() == "") {
            mQuery('#page_Title').removeClass('has-success has-error').addClass('has-error');
            mQuery('#page_Title .help-block').html("Title name can't be empty");
            return;
        }
        var selectrel = mQuery(this).attr("rel");
        mQuery(".ui-tabs-panel").addClass('hide');
        mQuery("#fragment-page-"+selectrel).removeClass('hide');
        mQuery(".ui-state-default").removeClass('ui-tabs-selected ui-state-active');
        mQuery("#ui-tab-page-header"+selectrel).addClass('ui-tabs-selected ui-state-active');
    });
    Mautic.filterBeeTemplates= function () {
        d = document.getElementById("filters").value;
        if(d == "all"){
            mQuery('.bee-template').removeClass('hide');
        } else {
            mQuery('.bee-template').addClass('hide');
            mQuery('.'+d).removeClass('hide');
        }
    };
};

Mautic.getPageAbTestWinnerForm = function(abKey) {
    if (abKey && mQuery(abKey).val() && mQuery(abKey).closest('.form-group').hasClass('has-error')) {
        mQuery(abKey).closest('.form-group').removeClass('has-error');
        if (mQuery(abKey).next().hasClass('help-block')) {
            mQuery(abKey).next().remove();
        }
    }

    Mautic.activateLabelLoadingIndicator('page_variantSettings_winnerCriteria');

    var pageId = mQuery('#page_sessionId').val();
    var query  = "action=page:getAbTestForm&abKey=" + mQuery(abKey).val() + "&pageId=" + pageId;

    mQuery.ajax({
        url: mauticAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (typeof response.html != 'undefined') {
                if (mQuery('#page_variantSettings_properties').length) {
                    mQuery('#page_variantSettings_properties').replaceWith(response.html);
                } else {
                    mQuery('#page_variantSettings').append(response.html);
                }

                if (response.html != '') {
                    Mautic.onPageLoad('#page_variantSettings_properties', response);
                }
            }

            Mautic.removeLabelLoadingIndicator();

        },
        error: function (request, textStatus, errorThrown) {
            Mautic.processAjaxError(request, textStatus, errorThrown);
            spinner.remove();
        },
        complete: function () {
            Mautic.removeLabelLoadingIndicator();
        }
    });
};

Mautic.autoHideRedirectUrl = function(container) {
    var select = mQuery(container + ' select[name="page[redirectType]"]');
    var input = mQuery(container + ' input[name="page[redirectUrl]"]');

    //If value is none we autohide the "Redirect URL" field and empty it
    if (select.val() == '') {
        input.closest('.form-group').hide();
        input.val('');
    } else {
        input.closest('.form-group').show();
    }
};