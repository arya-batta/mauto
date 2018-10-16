Mautic.subscriptionOnLoad = function (container) {
    mQuery('#subscription-panel').on('show.bs.collapse', function (e) {
        var actives = mQuery('#subscription-panel').find('.in, .collapsing');
        actives.each(function (index, element) {
            mQuery(element).collapse('hide');
            var id = mQuery(element).attr('id');
            mQuery('a[aria-controls="' + id + '"]').addClass('collapsed');
            mQuery('#'+id).removeClass('show');
        })
    });
    mQuery('[data-plankey]').click(function(e) {
        e.preventDefault();
        var currentLink = mQuery(this);
        var plankey = currentLink.attr('data-plankey');
        var planname = currentLink.attr('data-planname');
        var planamount = currentLink.attr('data-planamount');
        var plancurrency = currentLink.attr('data-plancurrency');
        mQuery('#selected-plan').html(planname);
        mQuery('#planamount-desc').attr("plancurrency", plancurrency);
        mQuery('#planamount-desc').attr("planamount", planamount);
        mQuery('#planamount-desc').attr("plankey", plankey);
        mQuery('#planamount-desc').attr("planname", planname);
        mQuery('#payment-monthly').trigger('click');
        mQuery('a[aria-controls="sectionTwo"]').trigger('click');
    });
    mQuery('[data-frequencybtn]').click(function(e) {
       // e.preventDefault();
        var currentLink = mQuery(this);
        var frequency = currentLink.attr('data-frequencybtn');
        var pldesc = mQuery('#planamount-desc');
        var planamount = pldesc.attr('planamount');
        var plancurrency = pldesc.attr('plancurrency');
       if(frequency == "year"){
           planamount=planamount*12;
           mQuery('#paymentduration-desc').html("Amount to be paid per year");
       }else{
           mQuery('#paymentduration-desc').html("Amount to be paid per month");
       }
        mQuery('#planamount-desc').attr("totalamount", planamount);
        mQuery('#planamount-desc').attr("plancycle", frequency);
        mQuery('#planamount-desc').html(plancurrency+planamount);
    });
    mQuery('#paymentcontinue-btn').click(function(e) {
        e.preventDefault();
        var pldesc = mQuery('#planamount-desc');
        var plankey = pldesc.attr('plankey');
        var planname = pldesc.attr('planname');
        var plancurrency = pldesc.attr('plancurrency');
        var actualamount = pldesc.attr('totalamount');
        var plancycle = pldesc.attr('plancycle');
        var taxamount=0;
        var totalamount=0;
        if(plancurrency == "₹"){
            taxamount= (actualamount * 18)/100;
            totalamount=(+actualamount + +taxamount);
        }else{
            totalamount=actualamount;
        }
        totalamount= Math.round(totalamount);
        taxamount= Math.round(taxamount);
        mQuery('#sectionThree .cplantitle').html(planname);
        mQuery('#sectionThree .cplantitle').attr("planname", plankey);
        mQuery('#sectionThree .cplantitle').attr("plancycle", plancycle);
        mQuery('#sectionThree .cplantitle').attr("plancurrency", plancurrency);
        mQuery('#sectionThree .cplanamount').html(actualamount);
        mQuery('#sectionThree .cplantax').html(taxamount);
        mQuery('#sectionThree .cplantotal').html(totalamount);
        mQuery('#sectionThree .cplanamtcurrency').html(plancurrency);
        mQuery('#sectionThree .cplantaxcurrency').html(plancurrency);
        mQuery('#sectionThree .cplantotalcurrency').html(plancurrency);
        mQuery('a[aria-controls="sectionThree"]').trigger('click');
    });
    mQuery('#paymentconfirm-btn').click(function(e) {
        e.preventDefault();
        mQuery('a[aria-controls="sectionFour"]').trigger('click');
    });
    mQuery('#makepayment-btn').click(function(e) {
        e.preventDefault();
        var usernameel=mQuery('#sectionFour #payment-username');
        var useremailel=mQuery('#sectionFour #payment-useremail');
        var useraddressel=mQuery('#sectionFour #payment-useraddress');
        var username=usernameel.val();
        var useremail=useremailel.val();
        var useraddress=useraddressel.val();
        var divwrapper = mQuery(usernameel).closest('div');
        if(username == ""){
            divwrapper.addClass('has-error');
            return;
        }else{
            divwrapper.removeClass('has-error');
        }
        divwrapper = mQuery(useremailel).closest('div');
        if(useremail == ""){
            divwrapper.addClass('has-error');
            return;
        }else{
            divwrapper.removeClass('has-error');
        }
        divwrapper = mQuery(useraddressel).closest('div');
        if(useraddress == ""){
            divwrapper.addClass('has-error');
            return;
        }else{
            divwrapper.removeClass('has-error');
        }
        var planname=mQuery('#sectionThree .cplantitle').attr("planname");
        var plancycle=mQuery('#sectionThree .cplantitle').attr("plancycle");
        var plancurrency=mQuery('#sectionThree .cplantitle').attr("plancurrency");
        Mautic.activateBackdrop();
        Mautic.ajaxActionRequest('subscription:makepayment', {username: username,useremail:useremail,useraddress:useraddress,planname:planname,plancycle:plancycle,plancurrency:plancurrency}, function(response) {
            Mautic.deactivateBackgroup();
          if (response.success) {
              if(response.provider == "razorpay"){
                  Mautic.invokeRazorPay(response,username,useremail,useraddress,planname);
              }else{
                  Mautic.invokePaypalPay(response);
              }
           }else{
              alert(response.errormsg);
          }
        });

    });
}

Mautic.validateKYCForm = function() {
    var firstName = mQuery('#user_firstName').val();
    var lastName = mQuery('#user_lastName').val();
    var phonenumber = mQuery('#accountinfo_phonenumber').val();
    var email = mQuery('#user_email').val();
    var companyname = mQuery('#billinginfo_companyname').val();
    var domain = mQuery('#accountinfo_domainname').val();
    var website = mQuery('#accountinfo_website').val();
    var companyaddress = mQuery('#billinginfo_companyaddress').val();
    var postalcode = mQuery('#billinginfo_postalcode').val();
    var state = mQuery('#billinginfo_state').val();
    var city = mQuery('#billinginfo_city').val();
    var country = mQuery('#billinginfo_country').val();
    var timezone = mQuery('#accountinfo_timezone').val();
    var gstnumber = mQuery('#billinginfo_gstnumber').val();
    var isvalid = true;
    mQuery('#user_Firstname').removeClass('has-success has-error');
    mQuery('#user_Firstname .help-block').html("");
    mQuery('#user_Lastname').removeClass('has-success has-error');
    mQuery('#user_Lastname .help-block').html("");
    mQuery('#account_Mobile').removeClass('has-success has-error');
    mQuery('#account_Mobile .help-block').html("");
    mQuery('#user_Email').removeClass('has-success has-error');
    mQuery('#user_Email .help-block').html("");
    mQuery('#billing_Company').removeClass('has-success has-error');
    mQuery('#billing_Company .help-block').html("");
    mQuery('#account_Website').removeClass('has-success has-error');
    mQuery('#account_Website .help-block').html("");
    mQuery('#billing_Address').removeClass('has-success has-error');
    mQuery('#billing_Address .help-block').html("");
    mQuery('#billing_City').removeClass('has-success has-error');
    mQuery('#billing_City .help-block').html("");
    mQuery('#billing_Postal').removeClass('has-success has-error');
    mQuery('#billing_Postal .help-block').html("");
    mQuery('#billing_state').removeClass('has-success has-error');
    mQuery('#billing_state .help-block').html("");
    mQuery('#billing_country').removeClass('has-success has-error');
    mQuery('#billing_country .help-block').html("");
    mQuery('#account_timezone').removeClass('has-success has-error');
    mQuery('#account_timezone .help-block').html("");
    mQuery('#billing_GST').removeClass('has-success has-error');
    mQuery('#billing_GST .help-block').html("");
    mQuery('#condition_Agree').removeClass('has-success has-error label_control_error');
    mQuery('#condition_Agree .help-block').html("");
    mQuery('#spam_Agree').removeClass('has-success has-error label_control_error');
    mQuery('#spam_Agree .help-block').html("");
    var theClass = "has-error";
    if(firstName == ""){
        mQuery('#user_Firstname').removeClass('has-success has-error').addClass(theClass);
        mQuery('#user_Firstname .help-block').html("Firstname can't be empty");
        isvalid = false;
    }
    if(lastName == ""){
        mQuery('#user_Lastname').removeClass('has-success has-error').addClass(theClass);
        mQuery('#user_Lastname .help-block').html("Lastname can't be empty");
        isvalid = false;
    }
    if (phonenumber == ""){
        mQuery('#account_Mobile').removeClass('has-success has-error').addClass(theClass);
        mQuery('#account_Mobile .help-block').html("Mobile can't be empty");
        isvalid = false;
    } else if(phonenumber != ""){
        if (phonenumber.length < 10 || isNaN(phonenumber)){
            mQuery('#account_Mobile').removeClass('has-success has-error').addClass(theClass);
            mQuery('#account_Mobile .help-block').html("Mobile doesn't look right. Use the Valid one");
            isvalid = false;
        }
    }
    if(email == ""){
        mQuery('#user_Email').removeClass('has-success has-error').addClass(theClass);
        mQuery('#user_Email .help-block').html("Email can't be empty");
        isvalid = false;
    }
    if(companyname == ""){
        mQuery('#billing_Company').removeClass('has-success has-error').addClass(theClass);
        mQuery('#billing_Company .help-block').html("Company can't be empty");
        isvalid = false;
    }
    if(website == ""){
        mQuery('#account_Website').removeClass('has-success has-error').addClass(theClass);
        mQuery('#account_Website .help-block').html("Website can't be empty");
        isvalid = false;
    } else if (website != ""){
        var re = /^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/;
        if (!re.test(website)) {
            mQuery('#account_Website').removeClass('has-success has-error').addClass(theClass);
            mQuery('#account_Website .help-block').html("Website doesn't look right. Use the Valid one");
            isvalid = false;
        }
    }

    if(companyaddress == ""){
        mQuery('#billing_Address').removeClass('has-success has-error').addClass(theClass);
        mQuery('#billing_Address .help-block').html("Company Address can't be empty");
        isvalid = false;
    }
    if(city == ""){
        mQuery('#billing_City').removeClass('has-success has-error').addClass(theClass);
        mQuery('#billing_City .help-block').html("City can't be empty");
        isvalid = false;
    }
    if(state == ""){
        mQuery('#billing_state').removeClass('has-success has-error').addClass(theClass);
        mQuery('#billing_state .help-block').html("State can't be empty");
        isvalid = false;
    }
    if(country == ""){
        mQuery('#billing_country').removeClass('has-success has-error').addClass(theClass);
        mQuery('#billing_country .help-block').html("Country can't be empty");
        isvalid = false;
    }
    if(postalcode == ""){
        mQuery('#billing_Postal').removeClass('has-success has-error').addClass(theClass);
        mQuery('#billing_Postal .help-block').html("Zip/Postal code can't be empty");
        isvalid = false;
    }
    if (timezone == ""){
        mQuery('#account_timezone').removeClass('has-success has-error').addClass(theClass);
        mQuery('#account_timezone .help-block').html("Timezone can't be empty");
        isvalid = false;
    }
    //if((mQuery('#billinginfo_gstnumber').css('display') !== 'none') && gstnumber == ""){
    //    mQuery('#billing_GST').removeClass('has-success has-error').addClass(theClass);
    //    mQuery('#billing_GST .help-block').html("GST Number can't be empty");
    //    isvalid = false;
    //}
    //if(!mQuery('#conditionAgree').prop('checked')){
    //    mQuery('#condition_Agree').removeClass('label_control_error').addClass('label_control_error');
    //    isvalid = false;
    //}
    if(!mQuery('#spamAgree').prop('checked')){
        mQuery('#spam_Agree').removeClass('label_control_error').addClass("label_control_error");
        isvalid = false;
    }
    return isvalid;
};
var timeInterval = 0;

Mautic.SendOTPConnection = function() {
    var firstName = mQuery('#user_firstName').val();
    var lastName = mQuery('#user_lastName').val();
    var phonenumber = mQuery('#accountinfo_phonenumber').val();
    var email = mQuery('#user_email').val();
    var company = mQuery('#billinginfo_companyname').val();
    var domain = mQuery('#accountinfo_domainname').val();
    var website = mQuery('#accountinfo_website').val();
    var address = mQuery('#billinginfo_companyaddress').val();
    var postalcode = mQuery('#billinginfo_postalcode').val();
    var state = mQuery('#billinginfo_state').val();
    var city = mQuery('#billinginfo_city').val();
    var country = mQuery('#billinginfo_country').val();
    var timezone = mQuery('#accountinfo_timezone').val();
    var gstnumber = mQuery('#billinginfo_gstnumber').val();
    mQuery('#billinginfo_state').prop('required',false);
    mQuery('#billinginfo_country').prop('required',false);
    mQuery('#billinginfo_gstnumber').prop('required',false);
    var theClass = "has-error";
    var isvalid = true;
    isvalid = Mautic.validateKYCForm();
    if(!isvalid){
        return isvalid;
    }
    var otp = Mautic.getCookie('MobileVerificationOTP');
    if(isvalid) {
        var data = {
            firstName: firstName,
            lastName: lastName,
            phonenumber: phonenumber,
            email: email,
            companyname: company,
            domain: domain,
            website: website,
            companyaddress: address,
            postalcode: postalcode,
            state: state,
            city: city,
            country: country,
            timezone: timezone,
            gstnumber: gstnumber,
        };
        if(otp != ""){
            data['otp'] = otp;
        }
        
        mQuery('#kycSubmit .fa-spinner').removeClass('hide');

        Mautic.ajaxActionRequest('subscription:updateKYC', data, function (response) {
            if(response.success){
                mQuery('#kycSubmit .fa-spinner').addClass('hide');
                var otp = response.otp;
                var otpsend = response.otpsend;
                if(otpsend) {
                    //mQuery(".otp_verifications").css("display", "block");
                    mQuery(".alertmsg").css("display","none");
                    mQuery('.sms_code_div').removeClass('has-success has-error');
                    mQuery('#sms_code').val('');
                    mQuery(".otp_verifications").fadeIn("slow");
                    mQuery(".steps").css("display", "none");
                    //setTimeout(function(){
                    //    mQuery('#send_sms').removeAttr('disabled');
                    //    mQuery('#send_sms').removeClass('disabled');
                    //}, 90000);
                    maxLimit = 61;
                    timeInterval = setInterval(function(){
                        maxLimit = parseInt(maxLimit) - 1;
                        mQuery('#send_sms').html('<i class="fa fa-repeat"></i> Resend Code('+maxLimit+')s');
                        if (maxLimit == 0) {
                            mQuery('#send_sms').removeClass('disabled').attr('disabled', false);
                            mQuery('#send_sms').html('<i class="fa fa-repeat"></i> Resend Code');
                            mQuery('#send_sms').removeClass('disabled');
                            clearInterval(timeInterval);
                        }
                    },1000);
                    var mobile = response.mobile;
                    mQuery('#sms_number').val(mobile);
                    document.cookie = "MobileVerificationOTP=" + otp + "; path=/";
                    mQuery('#kyc_otpverification').html('A code was just sent to your mobile phone : <b>' + mobile + '</b>')
                } else {
                    Mautic.RedirectToGivenURL(response.redirecturl);
                    //mQuery(".steps").css("display","none");
                    //mQuery(".video_page").css("display","block");
                    //mQuery(".video_page").fadeIn("slow");

                }
            }

        });
    }
};

Mautic.LoadKYCDetails = function() {
    clearInterval(timeInterval);
    mQuery(".otp_verifications").css("display","none");
    ///mQuery(".steps").css("display","block");
    mQuery(".steps").fadeIn("slow");
    mQuery('#send_sms').attr('disabled','true');
    mQuery('#send_sms').addClass('disabled');

};

Mautic.reSendOTP = function() {
    mQuery('#send_sms').attr('disabled');
    mQuery('#send_sms').addClass('disabled');
    var phonenumber = mQuery('#sms_number').val();
    var otp = Mautic.getCookie('MobileVerificationOTP');
    mQuery(".alertmsg").css("display","none");
    mQuery('.sms_code_div').removeClass('has-success has-error');
    mQuery('#sms_code').val('');
    var data = {
        phonenumber : phonenumber,
        otp:otp,
    };
    Mautic.ajaxActionRequest('subscription:resendOTP', data, function (response) {
        if(response.success){
            mQuery('#send_sms .fa').removeClass('fa-spinner fa-spin').addClass('fa-repeat');
            //setTimeout(function(){
            //    mQuery('#send_sms').removeAttr('disabled');
            //    mQuery('#send_sms').removeClass('disabled');
            //}, 90000);
            maxLimit = 61;
            timeInterval = setInterval(function(){
                maxLimit = parseInt(maxLimit) - 1;
                mQuery('#send_sms').html('<i class="fa fa-repeat"></i> Resend Code('+maxLimit+')s');
                if (maxLimit == 0) {
                    mQuery('#send_sms').removeClass('disabled').attr('disabled', false);
                    mQuery('#send_sms').html('<i class="fa fa-repeat"></i> Resend Code');
                    mQuery('#send_sms').removeClass('disabled');
                    clearInterval(timeInterval);
                }
            },1000);
            var otp = response.otp;
            var mobile = response.mobile;
            document.cookie = "MobileVerificationOTP="+otp+"; path=/";
            mQuery('#kyc_otpverification').html('A code was just sent to your mobile phone : <b>'+mobile+'</b>')
        }

    });
};

Mautic.verifyOTP = function() {
    mQuery(".alertmsg").css("display","none");
    document.cookie.indexOf("MobileVerificationOTP");
    var otp = Mautic.getCookie('MobileVerificationOTP');
    var otpcode = mQuery('#sms_code').val();
    if(otpcode == "") {
        mQuery('.sms_code_div').removeClass('has-success has-error').addClass('has-error');
        return;
    }
    var data = {};
    if(otp != "" && otp == otpcode){
        Mautic.ajaxActionRequest('subscription:OTPVerified', data, function (response) {
            if(response.success){
                //mQuery(".otp_verifications").css("display","none");
                //mQuery(".video_page").fadeIn("slow");
                Mautic.RedirectToGivenURL(response.redirecturl);
                //mQuery(".video_page").css("display","block");
            }
        });
    } else{
        mQuery(".alertmsg").css("display","block");

    }
};

Mautic.closeAlertMSG = function() {
    mQuery(".alertmsg").css("display","none");

};

Mautic.getCookie = function(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
};

Mautic.invokeRazorPay = function(response,username,useremail,useraddress,planname) {
    var apikey=response.apikey;
    var subscriptionid=response.subscriptionid
    var options = {
        "key": apikey,
        "subscription_id": subscriptionid,
        "name": "LeadsEngage",
        "description": planname+" plan upgradtion",
        "image": "https://s3.amazonaws.com/leadsroll.com/Razer-Pay-Icon.png",
        "handler": function (response){
            var paymentid=response.razorpay_payment_id;
            var subscriptionid=response.razorpay_subscription_id;
            var signature=response.razorpay_signature;
            Mautic.ajaxActionRequest('subscription:validatepayment', {paymentid: paymentid,subscriptionid:subscriptionid,signature:signature}, function(response) {
                if (response.success) {
                    Mautic.redirectWithBackdrop(response.redirect);
                }else{
                    alert(response.errormsg);
                }
            });
        },
        "prefill": {
            "name": username,
            "email":useremail,
            "contact":"",
            "method":"card"//{card|netbanking|wallet|emi|upi}
        },
        "notes": {
            "address": useraddress
        },
        "theme": {
            "color": "#0066cc"
        }
    };
    var rzp1 = new Razorpay(options);
    rzp1.open();
};
Mautic.invokePaypalPay = function(response) {
    Mautic.redirectWithBackdrop(response.approvalurl);
   // Mautic.openInNewTab(response.approvalurl);
};