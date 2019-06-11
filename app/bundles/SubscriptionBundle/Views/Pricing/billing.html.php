<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('leContent', 'billing');
$view['slots']->set('headerTitle', $view['translator']->trans('le.billing.page.title'));
$planamount         = $view['translator']->trans('le.pricing.plan.amount.'.$planname);
$planemailcredits   = $view['translator']->trans('le.pricing.plan.email.credits.'.$planname);
$plancontactcredits = $view['translator']->trans('le.pricing.plan.contact.credits.'.$planname);
$planvalidity       = $view['translator']->trans('le.pricing.plan.validity.'.$planname);
$plancurrency       = $view['translator']->trans('le.pricing.plan.currency.'.$planname);
?>
<br>
<br>
<br>
<div class="row pricing-div-2">
    <div class="col-md-2"></div>
    <div class="col-md-8">

        <div class="pricing-type-modal" style="display: block; z-index: 9999;">
            <div class="panel">
                <div class="panel-body box-layout">
                    <div class="modal-header hide" style="padding-bottom:0px;">
                        <div class="card-holder-title">
                            <?php echo $view['translator']->trans('le.pricing.model.header'); ?>
                        </div>
                        <p class="header_desc">
                            Subscription Charges- 0$ for first 3 months includes 100K free email credits/ month, Unlimited contacts and Unlimited access to all features. After 3 months trial, we charge 29$ per month, paid monthly.
                            <br>
                            Additional email credits- After you use 100K free email credits in a month, we charge 29$ for every 100K additional email. Additional email credits come with unlimited validity, and balance credits will get carry forward every month.
                        </p>
                        <div class="modal-loading-bar"></div>
                    </div>
                    <br>
                    <div class="modal-body form-select-modal" style="padding-top:0px;">
                        <div class="alert alert-info hide" id="card-holder-info" role="alert"></div>
                        <div class="card-holder-title">
                            Card Information
                        </div>
                        <p class="header_desc">
                            We will not charge you anything on your credit card now, you will only be billed after 3 month trial or you exhaust the free email credits in a month. You can cancel your account anytime if you wish.
                        </p>
                        <div class="row">
                            <div class="col-md-12">
                                <div id="card-holder-widget" data-le-token="<?php echo $letoken?>">
                                    <!-- A Stripe Element will be inserted here. -->
                                </div>
                            </div>
                        </div>
                        <!-- Used to display form errors. -->
                        <div id="card-holder-errors" role="alert"></div>
                        <br>
                        <br>
                        <div class="card-holder-title">
                            Business Information
                        </div>
                        <p class="header_desc">
                            Please make sure you provide your actual business information to get your account approved by our compliance team.
                        </p>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="businessname">Business Name</label>
                                <input type="text" id="welcome_business" class="form-control le-input" name="welcome[business]" value="">
                                <p class="label_control_error business_error hide">The Business name can't be empty.</p>
                            </div>
                            <div class="col-md-6">
                                <label for="weburl">Website URL</label>
                                <input type="url" id="welcome_websiteurl" class="form-control le-input" name="welcome[websiteurl]" value="">
                                <p class="label_control_error website_error hide">Website URL can't be empty.</p>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="phone">Current Contact Size</label>
                                <select name="welcome[listsize]" class="selop" id="select_listsize">
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
                                <input type="text" id="welcome_currentesp" class="form-control le-input" placeholder="e.g. Mailchimp" name="welcome[currentesp]"  value="">
                                <p class="label_control_error currentesp_error hide" >Existing Email Provider can't be empty.</p>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="adr"> Full Address</label>
                                <input type="text" style="width:97%;" id="welcome_address" class="form-control le-input" name="welcome[address-line-1]" value="">
                                <p class="label_control_error address_error hide">Full Address can't be empty</p>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="city"> City Name</label>
                                <input type="text" id="welcome_city" class="form-control le-input" name="welcome[city]" value="<?php echo $city; ?>">
                                <p class="label_control_error city_error hide">The City can't be empty</p>
                            </div>
                            <div class="col-md-6">
                                <label for="zip">Zip Code</label>
                                <input type="text" id="welcome_zip" class="form-control le-input" name="welcome[zip]" value="">
                                <p class="label_control_error zip_error hide">Zip/ Postal code can't be empty</p>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="state">State</label>
                                <select name="welcome[state]" class="selop not-chosen" id="selectstate">
                                    <option value="blank" selected="selected">Choose your State</option>
                                    <?php foreach ($states as $stategrouplabel => $stategroup):?>
                                        <optgroup label="<?php echo $stategrouplabel; ?>">
                                            <?php foreach ($stategroup as $statename):?>
                                                <option value="<?php echo $statename?>" selected="false"><?php echo $statename; ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                                <p class="label_control_error state_error hide">State can't be empty</p>
                            </div>
                            <div class="col-md-6">
                                <label for="country">Country</label>
                                <select name="welcome[country]" class="selop not-chosen" id="selectcountry">
                                    <option value="blank" selected="selected">Choose your Country</option>
                                    <?php foreach ($countries as $countryname):?>
                                        <option value="<?php echo $countryname?>" selected="false"><?php echo $countryname; ?></option>
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
                        <br>
                        <div class="row">
                            <div class="col-md-3"></div>
                            <div class="col-md-6" style="text-align: center;">
                                <button type="button" planname="<?php echo $planname?>" plancurrency="<?php echo $plancurrency?>" planamount="<?php echo $planamount?>" plancredits="<?php echo $planemailcredits?>" contactcredits="<?php echo $plancontactcredits?>" planvalidity="<?php echo $planvalidity?>" class="btn btn-default pay-now-btn price-buttonfoot">
                                    <?php echo $view['translator']->trans('le.billing.paynow.button.text'); ?>
                                </button>
                                <br>
                                <br>
                                <p id="pro-data-text" class="hide"></p>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                    </div>
                </div>
                <div class="pricing-footer">
                    <img width="300px" height="auto" src="<?php echo $view['assets']->getUrl('media/images/powered-by-stripe.png'); ?>"/>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="col-md-2"></div>
</div>
<script>
    Le.selectedCountry = "<?php echo $country ?>";
    Le.selectedState = "<?php echo $state ?>";
    Le.selectedCity = "<?php echo $city ?>";
</script>
