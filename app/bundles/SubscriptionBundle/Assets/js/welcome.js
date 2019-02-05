Le.welcomeOnLoad = function() {
    mQuery('.welcome-input-text-1').on('input', function() {
        var firstname = mQuery("#welcome_firstname").val();
        var lastname = mQuery("#welcome_lastname").val();
        var phone = mQuery("#welcome_phone").val();
        mQuery(".firstname_error").addClass('hide');
        mQuery(".phone_error").addClass('hide');
        if(firstname == "" || phone == ""){
            if(mQuery(this).hasClass('firstname') && firstname == ""){
                mQuery(".firstname_error").removeClass('hide');
            } else if (mQuery(this).hasClass('phone') && phone == ""){
                mQuery(".phone_error").removeClass('hide');
            }
            mQuery('#continue-btn').attr('disabled',true).addClass('btn-disabled');
        } else {
            mQuery('#continue-btn').attr('disabled',false).removeClass('btn-disabled');
        }
    });
    mQuery('.welcome-input-text-2').on('input', function() {
        var business = mQuery("#welcome_business").val();
        var website = mQuery("#welcome_websiteurl").val();
        var industry = mQuery("#welcome_industry").val();
        var currentesp = mQuery("#welcome_currentesp").val();
        mQuery(".business_error").addClass('hide');
        mQuery(".website_error").addClass('hide');
        mQuery(".industry_error").addClass('hide');
        mQuery(".currentesp_error").addClass('hide');
        if(business == "" || website == "" || industry == "" || currentesp == ""){
            if(mQuery(this).hasClass('business') && business == ""){
                mQuery(".business_error").removeClass('hide');
            } else if (mQuery(this).hasClass('website_url') && website == ""){
                mQuery(".website_error").text('');
                mQuery(".website_error").text("Website URL can't be empty");
                mQuery(".website_error").removeClass('hide');
            } else if (mQuery(this).hasClass('industry') && industry == ""){
                mQuery(".industry_error").removeClass('hide');
            } else if (mQuery(this).hasClass('currentesp') && currentesp == ""){
                mQuery(".currentesp_error").removeClass('hide');
            }
            mQuery('#continue-btn').attr('disabled',true).addClass('btn-disabled');
        } else {
            if(mQuery(this).hasClass('website_url') && !Le.IsProperURL(website)){
                mQuery("#welcome_websiteurl").addClass('error_input');
                mQuery(".website_error").removeClass('hide');
                mQuery(".website_error").text('');
                mQuery(".website_error").text("Please provide valid website URL.");
                mQuery('#continue-btn').attr('disabled',true).addClass('btn-disabled');
            } else {
                mQuery("#welcome_websiteurl").removeClass('error_input');
                mQuery('#continue-btn').attr('disabled', false).removeClass('btn-disabled');
            }
        }
    });
    /*mQuery("#welcome_websiteurl").on('input', function() {
        var website = mQuery(this).val();
        if(Le.IsProperURL(website)){

        }
    });*/
    mQuery('.welcome-input-text-3').on('input', function() {
        var address = mQuery("#welcome_address").val();
        var city = mQuery("#welcome_city").val();
        var zipcode = mQuery("#welcome_zip").val();
        mQuery(".address_error").addClass('hide');
        mQuery(".city_error").addClass('hide');
        mQuery(".zip_error").addClass('hide');
        if(address == "" || city == "" || zipcode == ""){
            if(mQuery(this).hasClass('address') && address == ""){
                mQuery(".address_error").removeClass('hide');
            } else if (mQuery(this).hasClass('city') && city == ""){
                mQuery(".city_error").removeClass('hide');
            } else if (mQuery(this).hasClass('zip') && zipcode == ""){
                mQuery(".zip_error").removeClass('hide');
            }
            mQuery('#continue-btn').attr('disabled',true).addClass('btn-disabled');
        } else {
            mQuery('#continue-btn').attr('disabled',false).removeClass('btn-disabled');
        }
    });
    mQuery("#continue-btn").on('click', function(){
        mQuery(this).val('Please wait...');
    });
    if(!mQuery('.part1').hasClass('hide')) {
        var firstname = mQuery("#welcome_firstname").val();
        var lastname = mQuery("#welcome_lastname").val();
        var phone = mQuery("#welcome_phone").val();
        if(firstname == "" || phone == ""){
            mQuery('#continue-btn').attr('disabled',true).addClass('btn-disabled');
        } else {
            mQuery('#continue-btn').attr('disabled',false).removeClass('btn-disabled');
        }
    }
    if(!mQuery('.part2').hasClass('hide')) {
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
    }
};

Le.IsProperURL = function(url){
    regexp =  /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
    if (regexp.test(url)) {
        return true;
    } else {
        return false;
    }
};