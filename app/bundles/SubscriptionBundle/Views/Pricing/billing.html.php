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
$billingCity        =$billing->getCity();
$billingState       =$billing->getState();
$billingCountry     =$billing->getCountry();
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
                       <div class="card-holder-title">
                            Billing Information
                        </div>
                        <p class="header_desc">
                            This information will appear on your invoice.
                        </p>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="businessname">Company</label>
                                <input type="text" id="welcome_business" class="form-control le-input" name="welcome[business]" value="<?php echo $account->getAccountname(); ?>">
                                <p class="label_control_error business_error hide">Business name can't be empty.</p>
                            </div>
                            <div class="col-md-6">
                                <label for="weburl">Accounting Email</label>
                                <input type="url" id="welcome_accountemail" class="form-control le-input" name="welcome[email]" value="<?php echo $account->getEmail(); ?>">
                                <p class="label_control_error email_error hide">Accounting Email can't be empty.</p>
                            </div>
                        </div>
                        <div class="row hide">
                        <div class="col-md-6">
                            <label for="weburl">Website</label>
                            <input type="url" id="welcome_website" class="form-control le-input" name="welcome[website]" value="<?php echo $account->getWebsite(); ?>">
                            <p class="label_control_error website_error hide">WebSite Email can't be empty.</p>
                        </div>
                        </div>
                        <div class="row hide">
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
                                <input type="text" id="welcome_currentesp" class="form-control le-input" placeholder="e.g. Mailchimp" name="welcome[currentesp]"  value="<?php echo $kyc->getPrevioussoftware(); ?>">
                                <p class="label_control_error currentesp_error hide" >Existing Email Provider can't be empty.</p>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="adr"> Full Address</label>
                                <input type="text" style="width:97%;" id="welcome_address" class="form-control le-input" name="welcome[address-line-1]" value="<?php echo $billing->getCompanyaddress(); ?>">
                                <p class="label_control_error address_error hide">Full Address can't be empty</p>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="city"> City Name</label>
                                <input type="text" id="welcome_city" class="form-control le-input" name="welcome[city]" value="<?php echo $billingCity; ?>">
                                <p class="label_control_error city_error hide">City can't be empty</p>
                            </div>
                            <div class="col-md-6">
                                <label for="state">State</label>
                                <select name="welcome[state]" class="selop" id="selectstate" style="width: 95%;">
                                    <option value="blank" <?php echo empty($billingState) ? 'selected' : ''?>>Choose your State</option>
                                    <?php foreach ($states as $stategrouplabel => $stategroup):?>
                                        <optgroup label="<?php echo $stategrouplabel; ?>">
                                            <?php foreach ($stategroup as $statename):?>
                                                <option value="<?php echo $statename?>" <?php echo !empty($billingState) && $billingState == $statename ? 'selected' : ''?>><?php echo $statename; ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                                <p class="label_control_error state_error hide">State can't be empty</p>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="zip">Zip Code</label>
                                <input type="text" id="welcome_zip" class="form-control le-input" name="welcome[zip]" value="<?php echo $billing->getPostalcode(); ?>">
                                <p class="label_control_error zip_error hide">Zip code can't be empty</p>
                            </div>
                            <div class="col-md-6">
                                <label for="country">Country</label>
                                <select name="welcome[country]" class="selop" id="selectcountry" style="width: 95%;">
                                    <option value="blank" <?php echo empty($billingCountry) ? 'selected' : ''?>>Choose your Country</option>
                                    <?php foreach ($countries as $countryname):?>
                                        <option value="<?php echo $countryname?>" <?php echo !empty($billingCountry) && $billingCountry == $countryname ? 'selected' : ''?>><?php echo $countryname; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="label_control_error country_error hide">Country can't be empty</p>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="city"> TAXID</label>
                                <input type="text" id="welcome_taxid" class="form-control le-input" name="welcome[taxid]" value="<?php echo $billing->getGstnumber(); ?>">
                                <p class="label_control_error taxid_error hide">TAXID can't be empty</p>
                            </div>
                        </div>
                        <br>
                        <div class="alert alert-info hide" id="card-holder-info" role="alert"></div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card-holder-title">
                                    Credit Card Information
                                </div>
                            </div>
                            <div class="col-md-4 hide">
                                <span style="text-align:right;color: rgba(22, 122, 198, 0.63);float: right;" data-toggle="tooltip"
                                   title="<?php echo $view['translator']->trans('le.subscription.credit.card.tooltip'); ?>">
                                    <i class="fa fa-question-circle"></i> Why do we ask for your CC?
                                </span>
                            </div>
                        </div>
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
                        <div class="row hide">
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
                        <div class="row hide">
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
                                    <?php echo $view['translator']->trans('Save and subscribe'); ?>
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
    mQuery("#select_listsize option[value='<?php echo $kyc->getSubscribercount(); ?>']").attr('selected','selected');
</script>
