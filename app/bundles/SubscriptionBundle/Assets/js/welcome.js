Le.welcomeOnLoad = function() {
    Le.setDefaultStateValues();
    mQuery('.welcome-input-text-1').on('input focusout', function() {
        var firstname = mQuery("#welcome_firstname").val();
        var lastname = mQuery("#welcome_lastname").val();
        var phone = mQuery("#welcome_phone").val();
        if(firstname == "" || phone == ""){
            if(mQuery(this).hasClass('firstname') && firstname == ""){
                mQuery(".firstname_error").removeClass('hide');
            } //else if (mQuery(this).hasClass('firstname') && firstname != ""){
               // mQuery(".firstname_error").addClass('hide');
            //}
            if (mQuery(this).hasClass('phone') && phone == ""){
                mQuery(".phone_error").text('');
                mQuery(".phone_error").text("Please provide a valid mobile number.");
                mQuery(".phone_error").removeClass('hide');
            } /*else if (mQuery(this).hasClass('phone') && phone != "" && (/^[0-9]+$/.test(phone))){
                mQuery(".phone_error").addClass('hide');
            } else if (mQuery(this).hasClass('phone') && (!/^[0-9]+$/.test(phone))){
                mQuery(".phone_error").removeClass('hide');
                mQuery(".phone_error").text('');
                mQuery(".phone_error").text("Please provide a valid mobile number.");
            }*/
            mQuery('#continue-btn').attr('disabled',true).addClass('btn-disabled');
        } else {
            /*if (mQuery(this).hasClass('phone') && (!/^[0-9]+$/.test(phone))){
                mQuery(".phone_error").removeClass('hide');
                mQuery(".phone_error").text('');
                mQuery(".phone_error").text("Please provide a valid mobile number.");
                mQuery('#continue-btn').attr('disabled',true).addClass('btn-disabled');
            } */
            mQuery(".firstname_error").addClass('hide');
            mQuery(".phone_error").addClass('hide');
            mQuery('#continue-btn').attr('disabled', false).removeClass('btn-disabled');

        }
    });
    mQuery('.welcome-input-text-2').on('input focusout', function() {
        var business = mQuery("#welcome_business").val();
        var website = mQuery("#welcome_websiteurl").val();
        var industry = mQuery("#welcome_industry").val();
        var currentesp = mQuery("#welcome_currentesp").val();
        if(business == "" || website == "" || industry == "" || currentesp == ""){
            if(mQuery(this).hasClass('business') && business == ""){
                mQuery(".business_error").removeClass('hide');
            } else if (mQuery(this).hasClass('business') && business != ""){
                mQuery(".business_error").addClass('hide');
            }
            if (mQuery(this).hasClass('website_url') && website == ""){
                mQuery(".website_error").text('');
                mQuery(".website_error").text("Please provide a valid website URL.");
                mQuery(".website_error").removeClass('hide');
            } else if (mQuery(this).hasClass('website_url') && website != "" && Le.IsProperURL(website)){
                mQuery(".website_error").addClass('hide');
            } else if(mQuery(this).hasClass('website_url') && !Le.IsProperURL(website)){
                mQuery(".website_error").removeClass('hide');
                mQuery(".website_error").text('');
                mQuery(".website_error").text("Please provide a valid website URL.");
            }
            if (mQuery(this).hasClass('industry') && industry == ""){
                mQuery(".industry_error").removeClass('hide');
            } else if (mQuery(this).hasClass('industry') && industry != ""){
                mQuery(".industry_error").addClass('hide');
            }
            if (mQuery(this).hasClass('currentesp') && currentesp == ""){
                mQuery(".currentesp_error").removeClass('hide');
            } else if (mQuery(this).hasClass('currentesp') && currentesp != ""){
                mQuery(".currentesp_error").addClass('hide');
            }
            mQuery('#continue-btn').attr('disabled',true).addClass('btn-disabled');
        } else {
            if(mQuery(this).hasClass('website_url') && !Le.IsProperURL(website)){
                mQuery("#welcome_websiteurl").addClass('error_input');
                mQuery(".website_error").removeClass('hide');
                mQuery(".website_error").text('');
                mQuery(".website_error").text("Please provide a valid website URL.");
                mQuery('#continue-btn').attr('disabled',true).addClass('btn-disabled');
            } else {
                mQuery(".business_error").addClass('hide');
                mQuery(".website_error").addClass('hide');
                mQuery(".industry_error").addClass('hide');
                mQuery(".currentesp_error").addClass('hide');
                mQuery("#welcome_websiteurl").removeClass('error_input');
                mQuery('#continue-btn').attr('disabled', false).removeClass('btn-disabled');
            }
        }
    });
    mQuery('.welcome-input-text-3').on('input focusout', function() {
        var address = mQuery("#welcome_address").val();
        var city = mQuery("#welcome_city").val();
        var zipcode = mQuery("#welcome_zip").val();
        if(address == "" || city == "" || zipcode == ""){
            if(mQuery(this).hasClass('address') && address == ""){
                mQuery(".address_error").removeClass('hide');
            } else if (mQuery(this).hasClass('address') && address != ""){
                mQuery(".address_error").addClass('hide');
            }
            if (mQuery(this).hasClass('city') && city == ""){
                mQuery(".city_error").removeClass('hide');
            } else if (mQuery(this).hasClass('city') && city != ""){
                mQuery(".city_error").addClass('hide');
            }
            if (mQuery(this).hasClass('zip') && zipcode == ""){
                mQuery(".zip_error").text('');
                mQuery(".zip_error").text("Zip/ Postal code can't be empty");
                mQuery(".zip_error").removeClass('hide');
            } else if (mQuery(this).hasClass('zip') && zipcode != ""){
                mQuery(".zip_error").addClass('hide');
            }
            mQuery('#continue-btn').attr('disabled',true).addClass('btn-disabled');
        } else {
            if (mQuery(this).hasClass('zip') && (!/^[0-9]+$/.test(zipcode))) {
                mQuery(".zip_error").removeClass('hide');
                mQuery(".zip_error").text('');
                mQuery(".zip_error").text("Please provide valid Zip/ Postal code.");
                mQuery('#continue-btn').attr('disabled',true).addClass('btn-disabled');
            } else {
                mQuery(".address_error").addClass('hide');
                mQuery(".city_error").addClass('hide');
                mQuery(".zip_error").addClass('hide');
                mQuery('#continue-btn').attr('disabled', false).removeClass('btn-disabled');
            }
        }
    });
    mQuery("#continue-btn").on('click', function(){
        mQuery(this).val('Please wait...');
    });
    /*if(!mQuery('.part1').hasClass('hide')) {
        var firstname = mQuery("#welcome_firstname").val();
        var lastname = mQuery("#welcome_lastname").val();
        var phone = mQuery("#welcome_phone").val();
        if(firstname == "" || phone == ""){
            mQuery('#continue-btn').attr('disabled',true).addClass('btn-disabled');
        } else {
            mQuery('#continue-btn').attr('disabled',false).removeClass('btn-disabled');
        }
    }
    if(!mQuery('.part1').hasClass('hide')) {
        var business = mQuery("#welcome_business").val();
        var website = mQuery("#welcome_websiteurl").val();
        var industry = mQuery("#welcome_industry").val();
        var currentesp = mQuery("#welcome_currentesp").val();
        if (business == "" || website == "" || industry == "" || currentesp == "") {
            mQuery('#continue-btn').attr('disabled', true).addClass('btn-disabled');
        } else {
            mQuery('#continue-btn').attr('disabled', false).removeClass('btn-disabled');
        }
    }
    if(!mQuery('.part3').hasClass('hide')) {
        var address = mQuery("#welcome_address").val();
        var city = mQuery("#welcome_city").val();
        var zipcode = mQuery("#welcome_zip").val();
        if(address == "" || city == "" || zipcode == ""){
            mQuery('#continue-btn').attr('disabled', true).addClass('btn-disabled');
        } else {
            mQuery('#continue-btn').attr('disabled', false).removeClass('btn-disabled');
        }
    }*/
};

Le.IsProperURL = function(url){
    regexp =  /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
    if (regexp.test(url)) {
        return true;
    } else {
        return false;
    }
};

Le.validateFirstTimeSetup = function(){
    var businessname = mQuery('#welcome_business').val();
    var currentlist = mQuery('#select_listsize').val();
    var currentprovider = mQuery('#welcome_currentesp').val();
    var websiteurl = mQuery('#welcome_websiteurl').val();
    var address = mQuery('#welcome_address').val();
    var city = mQuery('#welcome_city').val();
    var zipcode = mQuery('#welcome_zip').val();
    var state = mQuery('#selectstate').val();
    var country = mQuery('#selectcountry').val();
    var isvalidrequest = true;
    if(businessname == ''){
        isvalidrequest = false;
        mQuery('.business_error').removeClass('hide');
    }
    if(websiteurl == ''){
        isvalidrequest = false;
        mQuery('.website_error').removeClass('hide');
    }
    if(currentlist == '' || currentlist == 'blank'){
        isvalidrequest = false;
        mQuery('.listsize_error').removeClass('hide');
    }
    if(currentprovider == ''){
        isvalidrequest = false;
        mQuery('.currentesp_error').removeClass('hide');
    }
    if(address == ''){
        isvalidrequest = false;
        mQuery('.address_error').removeClass('hide');
    }
    if(city == ''){
        isvalidrequest = false;
        mQuery('.city_error').removeClass('hide');
    }
    if(zipcode == ''){
        isvalidrequest = false;
        mQuery('.zip_error').removeClass('hide');
    }
    if(state == '' || state == 'blank'){
        isvalidrequest = false;
        mQuery('.state_error').removeClass('hide');
    }
    if(country == '' || country == 'blank'){
        isvalidrequest = false;
        mQuery('.country_error').removeClass('hide');
    }
    if(!mQuery('#terms_conditions').prop('checked')){
        isvalidrequest = false;
        //mQuery('#termsConditions help-block').removeClass('hide');
        mQuery('#termsConditions').removeClass('label_control_error').addClass('label_control_error');
    }
    if(!mQuery('#spam_conditions').prop('checked')){
        isvalidrequest = false;
        //mQuery('#termsConditions help-block').removeClass('hide');
        mQuery('#spamConditions').removeClass('label_control_error').addClass('label_control_error');
    }
    if(isvalidrequest){
        mQuery('#firstTimeSetup_Form').submit();
    }
};