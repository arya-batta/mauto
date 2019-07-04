<?php
/*
 * @copyright   2019 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<!DOCTYPE html>
<html>
<head>
    <title>Let's Get Started</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="icon" type="image/x-icon" href="<?php echo $view['assets']->getUrl('media/images/favicon.ico') ?>" />
    <link rel="icon" sizes="192x192" href="<?php echo $view['assets']->getUrl('media/images/favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?php echo $view['assets']->getUrl('media/images/apple-touch-icon.png') ?>" />
    <?php echo $view['assets']->outputSystemStylesheets(); ?>
    <?php echo $view->render('MauticCoreBundle:Default:script.html.php'); ?>
</head>
<body>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-8">
        <div style="text-align:center;">
            <img style="width: 150px;margin-top: 10px;" src="<?php echo $view['assets']->getUrl('media/images/anyfunnel_logo_large_icon.png') ?>">
            <h2 class="head2">Complete your profile</h2>
            <p class="para">Please fill in all the fields below to complete your profile.</p>
        </div>
    </div>
    <div class="col-md-2"></div>
</div>
<br>
<div style="width:100%;margin-top:2%;">

    <div class="row">
        <form novalidate="" id="firstTimeSetup_Form" autocomplete="false" data-toggle="ajax" role="form" name="welcome" method="post" action="<?php echo $view['router']->generate('le_welcome_action'); ?>">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <label for="businessname">Business Name</label>
                        <input type="text" id="welcome_business" class="form-control le-input" name="welcome[business]" value="<?php echo $account->getAccountname(); ?>">
                        <p class="label_control_error business_error hide">Business name can't be empty.</p>
                    </div>
                    <div class="col-md-6">
                        <label for="weburl">Website URL</label>
                        <input type="url" id="welcome_websiteurl" class="form-control le-input" name="welcome[website]" value="<?php echo $account->getWebsite(); ?>">
                        <p class="label_control_error website_error hide">Website URL can't be empty.</p>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <label for="phone">Current Contact Size</label>
                        <select name="welcome[currentlist]" class="selop" id="select_listsize">
                            <option value="blank">Select one</option>
                            <option value="1"><500</option>
                            <option value="2">1,000 - 2,500</option>
                            <option value="3">2,500 - 5,000</option>
                            <option value="4">5,000 - 10,000</option>
                            <option value="5">10,000 - 25,000</option>
                            <option value="6">25,000 - 50,000</option>
                            <option value="7">50,000 - 100,000</option>
                            <option value="8">100,000 - 250,000</option>
                            <option value="9">>250,000</option>
                        </select>
                        <p class="label_control_error listsize_error hide">Current Contact Size can’t be empty.</p>
                    </div>
                    <div class="col-md-6">
                        <label for="phone">Existing Email Provider</label>
                        <input type="text" id="welcome_currentesp" class="form-control le-input" placeholder="e.g. Mailchimp" name="welcome[currentprovider]"  value="<?php echo $kyc->getPrevioussoftware(); ?>">
                        <p class="label_control_error currentesp_error hide" >Existing Email Provider can't be empty.</p>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <label for="adr"> Full Address</label>
                        <input type="text" style="width:97%;" id="welcome_address" class="form-control le-input" name="welcome[address]" value="<?php echo $billing->getCompanyaddress(); ?>">
                        <p class="label_control_error address_error hide">Full Address can't be empty</p>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <label for="city"> City Name</label>
                        <input type="text" id="welcome_city" class="form-control le-input" name="welcome[city]" value="<?php echo $city; ?>">
                        <p class="label_control_error city_error hide">City can't be empty</p>
                    </div>
                    <div class="col-md-6">
                        <label for="zip">Zip Code</label>
                        <input type="text" id="welcome_zip" class="form-control le-input" name="welcome[zipcode]" value="<?php echo $billing->getPostalcode(); ?>">
                        <p class="label_control_error zip_error hide">Zip code can't be empty</p>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <label for="state">State</label>
                        <select name="welcome[state]" class="selop" id="selectstate">
                            <option value="blank" <?php echo empty($state) ? 'selected' : ''?>>Choose your State</option>
                            <?php foreach ($states as $stategrouplabel => $stategroup):?>
                                <optgroup label="<?php echo $stategrouplabel; ?>">
                                    <?php foreach ($stategroup as $statename):?>
                                        <option value="<?php echo $statename?>" <?php echo !empty($state) && $state == $statename ? 'selected' : ''?>><?php echo $statename; ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <p class="label_control_error state_error hide">State can't be empty</p>
                    </div>
                    <div class="col-md-6">
                        <label for="country">Country</label>
                        <select name="welcome[country]" class="selop" id="selectcountry">
                            <option value="blank" <?php echo empty($country) ? 'selected' : ''?>>Choose your Country</option>
                            <?php foreach ($countries as $countryname):?>
                                <option value="<?php echo $countryname?>" <?php echo !empty($country) && $country == $countryname ? 'selected' : ''?>><?php echo $countryname; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="label_control_error country_error hide">Country can't be empty</p>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12" id="termsConditions">
                        <label class="control control-checkbox">
                            <?php echo $view['translator']->trans('le.subscription.termsandcontitions'); ?>
                            <input type="checkbox" id ="terms_conditions" />
                            <div class="control_indicator"></div>

                        </label>
                        <div class="help-block">

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" id="spamConditions">
                        <label class="control control-checkbox">
                            <?php echo $view['translator']->trans('le.subscription.spamandcontitions'); ?>
                            <input type="checkbox" id ="spam_conditions" />
                            <div class="control_indicator"></div>

                        </label>
                        <div class="help-block">

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <br>
                        <div style="width:50%;float:left;">
                            <a id="continue-btn" onclick="Le.validateFirstTimeSetup();" class="btn btn-primary waves-effect" >Save and Go to Dashboard</a>
                        </div>
                        <div class="welcome-page-footer hide" style="width: 50%;float: right;text-align: right;">
                            <p>©2019 AnyFunnels, All Rights Reserved<br>
                                <a href="http://anyfunnels.com/anti-spam-policy/" target="_blank">Anti Spam Policy</a>, <a href="http://anyfunnels.com/terms-of-service/" target="_blank">Terms of Service</a></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2"></div>
        </form>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
        <br>
    </div>

</div>

<script>
    Le.welcomeOnLoad();
    mQuery("#select_listsize option[value='<?php echo $kyc->getSubscribercount(); ?>']").attr('selected','selected');
    Le.activateChosenSelect(mQuery("#select_listsize"));
    Le.activateChosenSelect(mQuery("#selectstate"));
    Le.activateChosenSelect(mQuery("#selectcountry"));
</script>
</body>
</html>