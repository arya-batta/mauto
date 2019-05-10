Le.listoptinOnLoad = function(container) {
    //mQuery('#unsubscribe_text_div').find('.fr-element').attr('style','min-height:100px;');
    Le.changeListoptinTypeClass();
};

Le.showDoubleOptInList = function(widget){
    var ListType = mQuery(widget).val();
    if(ListType == 'double') {
        mQuery('#doubleoptinemaillist').removeClass('hide');
    } else {
        mQuery('#doubleoptinemaillist').addClass('hide');
    }
};

Le.changeListoptinTypeClass = function () {
    if (mQuery('#leadlistoptin_listtype_0').prop('checked')) {
        mQuery('.doubleoptinfields').addClass('hide');
        mQuery('#leadlistoptin_listtype_0').parent().removeClass('btn-danger').addClass('btn-success');
    } else if (mQuery('#leadlistoptin_listtype_1').prop('checked')){
        mQuery('.doubleoptinfields').removeClass('hide');
    }
};

Le.toggleDoubleOptinFieldVisibility = function () {
    //add a very slight delay in order for the clicked on checkbox to be selected since the onclick action
    //is set to the parent div
    setTimeout(function () {
        if (mQuery('#leadlistoptin_listtype_1').prop('checked')) {
            mQuery('.doubleoptinfields').removeClass('hide');
        } else {
            mQuery('.doubleoptinfields').addClass('hide');
        }
    }, 10);
};

Le.toggleThankYouEmailListVisibility = function () {
    //add a very slight delay in order for the clicked on checkbox to be selected since the onclick action
    //is set to the parent div
    setTimeout(function () {
        if (mQuery('#leadlistoptin_thankyou_1').prop('checked')) {
            mQuery('#thankyouemaillist').removeClass('hide');
        } else {
            mQuery('#thankyouemaillist').addClass('hide');
        }
    }, 10);
};

Le.toggleGoodbyeEmailListVisibility = function () {
    //add a very slight delay in order for the clicked on checkbox to be selected since the onclick action
    //is set to the parent div
    setTimeout(function () {
        if (mQuery('#leadlistoptin_goodbye_1').prop('checked')) {
            mQuery('#goodbyeemaillist').removeClass('hide');
        } else {
            mQuery('#goodbyeemaillist').addClass('hide');
        }
    }, 10);
};
