Le.accountinfoOnLoad = function (container) {
    if(mQuery('.cardholder-panel').is(':visible')) {
        var stripe = getStripeClient();
        var card=getStripeCard(stripe);
        mountStripeCard(stripe,card,'#card-holder-widget');
        mQuery('.cardholder-panel .card-update-btn').click(function(e) {
            e.preventDefault();
            Le.activateBackdrop();
            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    Le.deactivateBackgroup();
                    // Inform the user if there was an error.
                    var errorElement = document.getElementById('card-holder-errors');
                    var message=Le.showerror(result.error.message);
                    errorElement.textContent = message;
                } else {
                    // Send the token to your server.
                    stripeTokenHandler(card,result.token,".cardholder-panel",null);
                }
            });
        });
    }
    if(mQuery('.cancelsubscription').is(':visible')) {
        mQuery('.cancelsubscription .cancel-subscription').click(function(e) {
            e.preventDefault();
            Le.activateBackdrop();
            Le.ajaxActionRequest('subscription:cancelsubscription', {}, function(response) {
                e.preventDefault();
                Le.activateBackdrop();
                Le.deactivateBackgroup();
                if(response.success) {
                    mQuery('.cancelsubscription').addClass('hide');
                    mQuery('.deactivatedaccount').addClass('show');
                }
            });
        });
    }
}
Le.billingOnLoad = function (container) {
    var stripe = getStripeClient();
    var card=getStripeCard(stripe);
    if(mQuery('#card-holder-widget').is(':visible')) {
        mountStripeCard(stripe, card, '#card-holder-widget');
    }
    mQuery('.pay-now-btn').click(function(e) {
        e.preventDefault();
        var currentLink = mQuery(this);
        var planamount = currentLink.attr('planamount');
        var plancurrency = currentLink.attr('plancurrency');
        var planname = currentLink.attr('planname');
        var plancredits = currentLink.attr('plancredits');
        var planvalidity = currentLink.attr('planvalidity');
        var contactcredits = currentLink.attr('contactcredits');
        Le.activateButtonLoadingIndicator(currentLink);
        stripe.createToken(card).then(function(result) {
            if (result.error) {
                Le.removeButtonLoadingIndicator(currentLink);
                // Inform the user if there was an error.
                var errorElement = document.getElementById('card-holder-errors');
                var message=Le.showerror(result.error.message);
                errorElement.textContent = message;
            } else {
                // Send the token to your server.
                stripeTokenHandler(card,result.token,'.pricing-type-modal',currentLink);
            }
        });
    });
    //Le.pricingplansOnLoad(container);
}
Le.pricingplansOnLoad = function (container) {
    /*var stripe = getStripeClient();
    var card=getStripeCard(stripe);
    mQuery('[data-planname]').click(function(e) {
        var currentLink = mQuery(this);
        var planname = currentLink.attr('data-planname');
        var planamount = currentLink.attr('data-planamount');
        var plancurrency = currentLink.attr('data-plancurrency');
        var plancredits = currentLink.attr('data-plancredits');
        var plancontactcredits = currentLink.attr('data-contactcredits');
        var planvalidity = currentLink.attr('data-validity');
        var paynowbtn=mQuery('.pay-now-btn');
        var headerdesc=mQuery('.header_desc');
        var planamountarr = [];
        planamountarr['leplan1'] = 0;
        planamountarr['leplan2'] = 49;
        planamountarr['leplan3'] = 99;
        /*var emailtransport=mQuery('.pricing-plan-holder').attr('data-email-transaport');
        if(emailtransport == 'viale'){
            mQuery('#pricing-plan-alert-info').removeClass('hide');
        }else{
            //mQuery('#pricing-plan-alert-info').addClass('hide');
            var prodatatext=mQuery('#pro-data-text');
            prodatatext.addClass('hide');
            var prodataval = "";
            if(planamount != planamountarr[planname]){
                prodataval = " ($"+planamount+" Pro rata charges for $"+planamountarr[planname]+" plan)";
            }
            if(planname == 'leplan1'){
                prodatatext.html("<b>Note:</b><br>We will not charge your card today.<br>You will only be billed when you send more than 10K free emails in a month.");
                //prodatatext.removeClass('hide');
            } else if(planname == 'leplan2'){
                prodatatext.html("<b>Note:</b><br>We will charge $"+planamount+" today"+prodataval+".<br>If you send more than 100K emails in a month, then you will be billed for additional email credits as you use ($7 for every 10K emails).");
                //prodatatext.removeClass('hide');
            } else {
                prodatatext.html("<b>Note:</b><br>We will charged $"+planamount+" today"+prodataval+".<br>If you send more than 250K emails in a month, then you will be billed for additional email credits as you use ($5 for every 10K emails).");
                //prodatatext.removeClass('hide');
            }
            //headerdesc.text(planamountarr[planname]+'$ per month');
            paynowbtn.html("Subscribe 0$ Plan");
            paynowbtn.attr("planamount",planamount);
            paynowbtn.attr("plancurrency",plancurrency);
            paynowbtn.attr("plancredits",plancredits);
            paynowbtn.attr("planname",planname);
            paynowbtn.attr("planvalidity",planvalidity);
            paynowbtn.attr("contactcredits",plancontactcredits);
            //mQuery('.pricing-type-modal-backdrop').removeClass('hide');
            //mQuery('.pricing-type-modal').removeClass('hide');
            mQuery('.pricing-div-2').removeClass('hide');
            mQuery('.pricing-div-1').addClass('hide');
            mountStripeCard(stripe,card,'#card-holder-widget');
        //}
    });*/

}
function getStripeClient(){
    // Create a Stripe client.
    var stripe = Stripe(stripeKey);//pk_test_6ZK3IyRbtk82kqU1puGcg9i6 //'pk_live_SaCvf4xx8HojET3eQfTBhiY2'
    return stripe;
}
function getStripeCard(stripe){
    // Create an instance of Elements.
    var elements = stripe.elements();
// Custom styling can be passed to options when creating an Element.
// (Note that this demo uses a wider set of styles than the guide below.)
    var style = {
        base: {
            color: '#32325d',
            lineHeight: '18px',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };
// Create an instance of the card Element.
    var card = elements.create('card', {style: style});
    return card;
}
function mountStripeCard(stripe,card,elementid){
// Add an instance of the card Element into the `card-element` <div>.
    card.mount(elementid);
// Handle real-time validation errors from the card Element.
    card.addEventListener('change', function(event) {
        var displayError = document.getElementById('card-holder-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
}

function stripeTokenHandler(card,token,rootclass,btnelement){
    try{
        clearInfoText();
        var chwidget = mQuery(rootclass+' #card-holder-widget');
        var letoken=chwidget.attr("data-le-token");
        var stripetoken=token.id;
        var planamount = 1;
        var plancurrency="$";
        var plancredits=0;
        var planname="";
        var planvalidity="";
        var contactcredits = 0;
        var isCardUpdateAlone=true;
        if(btnelement != null){
            planamount = btnelement.attr('planamount');
            plancurrency = btnelement.attr('plancurrency');
            planname = btnelement.attr('planname');
            plancredits = btnelement.attr('plancredits');
            planvalidity = btnelement.attr('planvalidity');
            contactcredits = btnelement.attr('contactcredits');
            isCardUpdateAlone=false;
        }
        var accountdata = {};
        if(!isCardUpdateAlone){
            var businessname = mQuery('#welcome_business').val();
            var currentlist = mQuery('#select_listsize').val();
            var currentprovider = mQuery('#welcome_currentesp').val();
            var email = mQuery('#welcome_accountemail').val();
            var address = mQuery('#welcome_address').val();
            var city = mQuery('#welcome_city').val();
            var zipcode = mQuery('#welcome_zip').val();
            var state = mQuery('#selectstate').val();
            var country = mQuery('#selectcountry').val();
            var website = mQuery('#welcome_website').val();
            var isvalidrequest = true;
            if(businessname == ''){
                isvalidrequest = false;
                mQuery('.business_error').removeClass('hide');
            }
            if(email == ''){
                isvalidrequest = false;
                mQuery('.email_error').removeClass('hide');
            }
            /*if(currentlist == '' || currentlist == 'blank'){
                isvalidrequest = false;
                mQuery('.listsize_error').removeClass('hide');
            }*/
            /*if(currentprovider == ''){
                isvalidrequest = false;
                mQuery('.currentesp_error').removeClass('hide');
            }*/
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
            /*if(!mQuery('#terms_conditions').prop('checked')){
                isvalidrequest = false;
                //mQuery('#termsConditions help-block').removeClass('hide');
                mQuery('#termsConditions').removeClass('label_control_error').addClass('label_control_error');
            }
            if(!mQuery('#spam_conditions').prop('checked')){
                isvalidrequest = false;
                //mQuery('#termsConditions help-block').removeClass('hide');
                mQuery('#spamConditions').removeClass('label_control_error').addClass('label_control_error');
            }*/
            if(!isvalidrequest){
                Le.removeButtonLoadingIndicator(mQuery('.pay-now-btn'));
                return;
            }

            accountdata = {
                business : businessname,
                currentlist : currentlist,
                currentprovider : currentprovider,
                email : email,
                address : address,
                city : city,
                zipcode : zipcode,
                state : state,
                country : country,
                website: website,
            };
        }

        var data = {
            letoken : letoken,
            stripetoken : stripetoken,
            planamount : planamount,
            plancurrency : plancurrency,
            plancredits : plancredits,
            planname : planname,
            planvalidity : planvalidity,
            isCardUpdateAlone : isCardUpdateAlone,
            contactcredits : contactcredits,
            accountdata : accountdata
        };
        // Insert the token ID into the form so it gets submitted to the server
        Le.ajaxActionRequest('subscription:updatestripecard', data, function(response) {

            if(isCardUpdateAlone){
                Le.deactivateBackgroup();
            }else{
                Le.removeButtonLoadingIndicator(mQuery('.pay-now-btn'));
            }
            if (response.success) {
                card.clear();
                if(isCardUpdateAlone){
                    setInfoText("Card updated successfully");
                    location.reload();
                }else{
                    //alert(response.statusurl);
                    Le.redirectWithBackdrop(response.statusurl);
                }
            }
            else{
                // Inform the user if there was an error.
                var errors=response.errormsg;
                var error=Le.showerror(errors);
                setInfoText(error);
                mQuery('html, body').animate({scrollTop:0}, '100');
            }
        });
    }catch(err){
    // alert(err);
    }
}
function clearInfoText() {
    var infoElement = mQuery("#card-holder-info");
    infoElement.html("");
    infoElement.addClass('hide');
}
function setInfoText(info){
    var infoElement = mQuery("#card-holder-info");
    infoElement.html(info);
    infoElement.removeClass('hide');
}
Le.showerror= function (error){
    var keys=[
        "Your card was declined",
        "insufficient funds",
        " security code is incorrect"];
    var errors=[
        "Your card was declined. Please use a vaild credit card.",
        "Your card has insufficient funds.",
        " Incorrect CVV or expiry date of the card. Please retry with correct details."];
    for (var i = 0; i < keys.length; i++) {
        if (error.includes(keys[i]) == true) {
            var message = errors[i];
            mQuery("#card-holder-widget").addClass("StripeElement--invalid");
        }
    }
    if (message !== undefined && message !== null){
        message = message;
    }else{
        message = error;
    }
    return message;
};
Le.openCancelSubscriptionModel = function(){
    mQuery('.cancel-subscription-modal-backdrop').removeClass('hide');
    mQuery('.cancel-subscription-modal').removeClass('hide');
};
Le.cancelSubscription = function(){
    try{
        Le.activateBackdrop();
        var cancelreason = mQuery('#cancel_reason option:selected').text();
        var cancelfeedback = mQuery('#reason_feedback').val();
        Le.ajaxActionRequest('subscription:cancelsubscription', {cancelreason:cancelreason,cancelfeedback:cancelfeedback}, function(response) {
            Le.deactivateBackgroup();
            if(response.success) {
                Le.loadContent(response.redirecturl);
                mQuery('body').removeClass('noscroll');
            }
        });
    }catch(err){
     //alert(err);
    }
};