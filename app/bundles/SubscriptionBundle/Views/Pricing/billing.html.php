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
                    <div class="modal-header" style="padding-bottom:0px;">
                        <div class="card-holder-title">
                            <?php echo $view['translator']->trans('le.pricing.model.header'); ?>
                        </div>
                        <p class="header_desc">
                            0$ for first 3 months, then just $49/month, paid monthly.<br>
                            If you decide that AnyFunnels isn't for you, you can cancel anytime.

                        </p>
                        <div class="modal-loading-bar"></div>
                    </div>
                    <br>
                    <div class="modal-body form-select-modal" style="padding-top:0px;">
                        <div class="alert alert-info hide" id="card-holder-info" role="alert"></div>
                        <div class="card-holder-title">
                            Card Information
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
                        <div class="card-holder-title">
                            Billing Information
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="businessname">Business Name</label>
                                <input type="text" id="welcome_business" class="welcome-input-text-2 business form-control le-input" name="welcome[business]" value="">
                                <p class="error_tag business_error hide">The Business name can't be empty</p>
                            </div>

                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="phone">Phone Number</label>
                                <input placeholder="e.g. +1-866-832-0000" type="tel" id="welcome_phone" class="welcome-input-text-1 phone form-control le-input" name="welcome[phone]" value="">
                                <p class="error_tag phone_error hide" >Please provide a valid mobile number.</p>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="weburl">Website URL</label>
                                <input type="url" id="welcome_websiteurl" class="welcome-input-text-2 website_url form-control le-input" name="welcome[websiteurl]" value="">
                                <p class="error_tag website_error hide">Please provide a valid mobile number</p>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="adr"> Full Address</label>
                                <input type="text" style="width:97%;" id="welcome_address" class="welcome-input-text-3 address form-control le-input" name="welcome[address-line-1]" value="">
                                <p class="error_tag address_error hide">Address line 1 can't be empty</p>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="city"> City Name</label>
                                <input type="text" id="welcome_city" class="welcome-input-text-3 city form-control le-input" name="welcome[city]" value="<?php echo $city; ?>">
                                <p class="error_tag city_error hide">The City can't be empty</p>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="zip">Zip Code</label>
                                <input type="text" id="welcome_zip" class="welcome-input-text-3 zip form-control le-input" name="welcome[zip]" value="">
                                <p class="error_tag zip_error hide">Zip/ Postal code can't be empty</p>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="state">State</label>
                                <select name="welcome[state]" class="selop not-chosen" id="selectstate">
                                    <option value="blank" selected="selected">Choose your State</option>
                                    <?php foreach ($states as $stategrouplabel => $stategroup):?>
                                        <optgroup label="<?php echo $stategrouplabel; ?>">
                                            <?php foreach ($stategroup as $state):?>
                                                <option value="<?php echo $state?>" selected="false"><?php echo $state; ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="country">Country</label>
                                <select name="welcome[country]" class="selop not-chosen" id="selectcountry">
                                    <option value="blank" selected="selected">Choose your Country</option>
                                    <?php foreach ($countries as $country):?>
                                        <option value="<?php echo $country?>" selected="false"><?php echo $country; ?></option>
                                    <?php endforeach; ?>
                                </select>
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
    mQuery("#selectcountry option[value='<?php echo $country; ?>']").attr('selected','selected');
    mQuery("#selectstate option[value='<?php echo $state; ?>']").attr('selected','selected');
</script>
