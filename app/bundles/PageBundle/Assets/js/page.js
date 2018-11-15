//PageBundle
Le.pageOnLoad = function (container, response) {
    if (mQuery(container + ' #list-search').length) {
        Le.activateSearchAutocomplete('list-search', 'page.page');
    }

    if (mQuery(container + ' #page_template').length) {
        Le.toggleBuilderButton(mQuery('#page_template').val() == '');

        //Handle autohide of "Redirect URL" field if "Redirect Type" is none
        if (mQuery(container + ' select[name="page[redirectType]"]').length) {
            //Auto-hide on page loading
            Le.autoHideRedirectUrl(container);

            //Auto-hide on select changing
            mQuery(container + ' select[name="page[redirectType]"]').chosen().change(function(){
                Le.autoHideRedirectUrl(container);
            });
        }

        // Preload tokens for code mode builder
        //  Le.getTokens(Le.getBuilderTokensMethod(), function(){});
        // Le.initSelectTheme(mQuery('#page_template'));
        Le.initSelectBeeTemplate(mQuery('#page_template'),'page');
    }

    // Open the builder directly when saved from the builder
    if (response && response.inBuilder) {
        Le.launchBuilder('page');
        Le.processBuilderErrors(response);
    }
    Le.removeActionButtons();

    mQuery('.next-tab, .prevv-tab, .ui-state-default').click(function() {
        var selectrel = mQuery(this).attr("rel");
        mQuery('#page_Title').removeClass('has-success has-error');
        mQuery('#page_Title .help-block').addClass('hide').html("");
        mQuery('#redirectUrl').removeClass('has-success has-error');
        mQuery('#redirectUrl .help-block').html("");
        if(mQuery('#page_title').val() == "") {
            mQuery('#page_Title').removeClass('has-success has-error').addClass('has-error');
            mQuery('#page_Title .custom-help').removeClass('hide').html("Landing Page Title name can't be empty");
            return;
        }
        if(mQuery('#page_redirectUrl').is(':visible')) {
            if(mQuery('#page_redirectUrl').val() == "") {
                mQuery('#redirectUrl').addClass('has-error');
                mQuery('#redirectUrl .custom-help').html("Redirect URL can't be empty.");
                return;
            }
        }
        if(mQuery('#page_variantSettings_weight').val() == "" ){
            mQuery('#Page_trafficweight').removeClass('has-success has-error').addClass('has-error');
            mQuery('#Page_trafficweight .help-block').html("Traffic Weight can't be empty");
            return;
        }else{
            mQuery('#Page_trafficweight').removeClass('has-success has-error');
            mQuery('#Page_trafficweight .help-block').html("");
        }
        if(mQuery('#page_variantSettings_winnerCriteria').val() == ""){
            mQuery('#Page_winnercriteria').removeClass('has-success has-error').addClass('has-error');
            mQuery('#Page_winnercriteria .help-block').html("Winner Criteria can't be empty");
            return;
        } else {
            mQuery('#Page_winnercriteria').removeClass('has-success has-error');
            mQuery('#Page_winnercriteria .help-block').html("");
        }
        var selectrel = mQuery(this).attr("rel");
        mQuery(".ui-tabs-panel").addClass('hide');
        mQuery("#fragment-page-"+selectrel).removeClass('hide');
        mQuery(".ui-state-default").removeClass('ui-tabs-selected ui-state-active');
        mQuery("#ui-tab-page-header"+selectrel).addClass('ui-tabs-selected ui-state-active');

        if (mQuery('#ui-tab-page-header2').hasClass('ui-tabs-selected'))
        {
            if(!mQuery('#email-content-preview').hasClass('hide')) {
                mQuery('#builder_btn').removeClass('hide');
            }
        }else {
            mQuery('#builder_btn').addClass('hide');
        }
        if(!mQuery('#email-advance-container').hasClass('hide')) {
            var url = window.location.href;
            if (url.indexOf('pages/new') == -1) {
                if (mQuery('textarea.builder-html').val() != 'false' && mQuery('textarea.builder-html').val().indexOf("false") < 0 && mQuery('textarea.builder-html').val() != '') {
                    mQuery('#builder_btn').removeClass('hide');
                    Le.showpreviewoftemplate(mQuery('textarea.builder-html').val());
                }
            }
        }

    });
    Le.filterBeeTemplates= function () {
        d = document.getElementById("filters").value;
        if(d == "all"){
            mQuery('.bee-template').removeClass('hide');
        } else {
            mQuery('.bee-template').addClass('hide');
            mQuery('.'+d).removeClass('hide');
        }
    };
};

Le.getPageAbTestWinnerForm = function(abKey) {
    if (abKey && mQuery(abKey).val() && mQuery(abKey).closest('.form-group').hasClass('has-error')) {
        mQuery(abKey).closest('.form-group').removeClass('has-error');
        if (mQuery(abKey).next().hasClass('help-block')) {
            mQuery(abKey).next().remove();
        }
    }

    Le.activateLabelLoadingIndicator('page_variantSettings_winnerCriteria');

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
                    Le.onPageLoad('#page_variantSettings_properties', response);
                }
            }

            Le.removeLabelLoadingIndicator();

        },
        error: function (request, textStatus, errorThrown) {
            Le.processAjaxError(request, textStatus, errorThrown);
            spinner.remove();
        },
        complete: function () {
            Le.removeLabelLoadingIndicator();
        }
    });
};

Le.autoHideRedirectUrl = function(container) {
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
Le.openPluginModel = function(id){
    mQuery('#'+id).removeClass('hide').addClass('fade in');
}
Le.closePluginModel = function(id){
    mQuery('#'+id).removeClass('fade in').addClass('hide');
}
Le.openFormCreator = function(){
    Le.ajaxActionRequest('page:getFormsList',{}, function(response) {
        var formlist = response.forms;
        var tborder= "<select border='1' id='page_formSelect' class='form-control category-select le-input' onclick='Le.onFormSelectinPage(this.options[this.selectedIndex].value);'>";
        var i = 0;
        var firstValue = "";
        for (var key in formlist) {
            var val = key;
            var title = formlist[key];
            var value= '<option class="active-result" value="'+key+'" > '+ title +'</option>';
            tborder+= value;
            if(i == 0){
                firstValue = key;
            }
            i++;
        }
        tborder+= "</select>";

        mQuery('.insert-tokens').html(tborder);
        Le.onFormSelectinPage(firstValue);

    });
    mQuery('#bee-plugin-model').removeClass('fade in').addClass('hide');
    mQuery('#bee-plugin-form-creator').removeClass('hide').addClass('fade in');

}
Le.onFormSelectinPage = function(ele){
    var base_url = window.location.origin+mauticBaseUrl;
    //var jsurl = base_url+"form/generate.js?="+ele;
    //var jsInput = '<script type="text/javascript" src="'+jsurl+'"></script>';
    //mQuery('#javascipt_textarea_page').val(jsInput);
    if(ele != ''){
        var iframeurl = base_url+"form/"+ele;
        var iframeinput = '<iframe style="border: 0px solid;" src="'+iframeurl+'" width="350" height="350"><p>Your browser does not support iframes.</p></iframe>';
    }
    mQuery('#iframe_textarea_page').val(iframeinput);
}
Le.openVideoEmbedModel = function(){
    mQuery('#bee-plugin-model').removeClass('fade in').addClass('hide');
    mQuery('#bee-plugin-video-embed').removeClass('hide').addClass('fade in');
}
Le.ConvertURLtoEmbed = function (){
    var url = mQuery('#youtube_url').val();
    if(mQuery('#youtube_url').val() == ""){
        mQuery('#youtube_u').removeClass('has-success has-error').addClass('has-error');
        mQuery('#youtube_u .help-block').html("The Value Can't be Empty");
    }
    else {
        mQuery('#youtube_u').removeClass('has-error');
        mQuery('#youtube_u .help-block').html("");
        var url = mQuery('#youtube_url').val();
        var videoId = this.getYoutubeVideoID(url);
        var iframeMarkup = '<iframe width="560" height="315" src="//www.youtube.com/embed/'
            + videoId + '?rel=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
        mQuery('#iframe_textarea_videopage').val(iframeMarkup);
    }
}
Le.getYoutubeVideoID = function(url){
    var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
    var match = url.match(regExp);

    if (match && match[2].length == 11) {
        return match[2];
    } else {
        return 'error';
    }
}