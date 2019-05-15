<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<div style="text-align:center;width:100%;">
    <div style="">
        <br>
        <div class="row">
            <div class="col-md-12">
                <span style="text-align:center;font-weight: bold;font-size:22px;font-weight: normal;color: #000;">
                    <?php echo $isDashboard ? 'Step #1 - Subscribe your plan.' : 'Simple Pricing, Unlimited Access to All Features '?>
                </span>
                <br>
                <span style="font-size:14px;">
                    All plan includes unlimited access to all features. No yearly contracts, cancel your account any time.
                </span>
            </div>
        </div>
        <div class="row" style="margin-left:20px;">
            <div class="plan-card col-md-8">
                <div class="plan-name">
                    <h2 style="text-align: center;">FREE FOREVER</h2>
                    <div class="">
                        <ul class="plan-pricing">
                            <li class="price">
                                <span class="currency-symbol">$</span>0
                            </li>
                            <li class="terms">Forever</li>
                        </ul>
                        <hr>
                        <h3>PLAN INCLUDES</h3>
                        <p class="pricepara">Up to 10,000 contacts</p>
                        <p class="pricepara">Free 10,000 email credits per month</p>
                        <p class="pricepara">Additional emails - 10$ for every 10K emails</p>
                        <p class="pricepara">Includes AnyFunnels logo in email footer</p>

                        <a href="#" type="button" data-planname="leplan1" data-plancurrency="$" data-planamount="<?php echo $plan1?>" data-plancredits="10000" data-contactcredits="10000" data-validity="1" class="btn plan-btn buttonfooter <?php echo $planname != '' ? 'buttonfooter-disabled' : '' ?>">
                            Subscribe
                        </a>
                    </div>
                </div>
            </div>
            <div class="plan-card col-md-8">
                <div class="plan-name">
                    <h2 style="text-align: center;">STARTER</h2>
                    <div class="">
                        <ul class="plan-pricing">
                            <li class="price">
                                <span class="currency-symbol">$</span>49
                            </li>
                            <li class="terms">Per Month, Billed Monthly</li>
                        </ul>
                        <hr>
                        <h3>PLAN INCLUDES</h3>
                        <p class="pricepara">Up to 25,000 contacts</p>
                        <p class="pricepara">Free 100,000 email credits per month</p>
                        <p class="pricepara">Additional emails - 7$ for every 10K emails</p>
                        <p class="pricepara">No AnyFunnels logo in email footer</p>

                        <a href="#" type="button"  data-planname="leplan2" data-plancurrency="$" data-planamount="<?php echo $plan2?>" data-plancredits="100000" data-contactcredits="25000" data-validity="1" class="btn plan-btn buttonfooter <?php echo $planname == 'leplan1' || $planname == '' ? '' : 'buttonfooter-disabled' ?>">
                            Subscribe
                        </a>
                    </div>
                </div>
            </div>
            <div class="plan-card col-md-8" id="plan-card">
                <div class="plan-name">
                    <h2 style="text-align: center;">GROWTH</h2>
                    <div class="">
                        <ul class="plan-pricing">
                            <li class="price">
                                <span class="currency-symbol">$</span>99
                            </li>
                            <li class="terms">Per Month, Billed Monthly</li>
                        </ul>
                        <hr>
                        <h3>PLAN INCLUDES</h3>
                        <p class="pricepara">Unlimited contacts</p>
                        <p class="pricepara">Free 250,000 email credits per month</p>
                        <p class="pricepara">Additional emails - 5$ for every 10K emails</p>
                        <p class="pricepara">No AnyFunnels logo in email footer</p>
                        <a href="#" type="button"  data-planname="leplan3" data-plancurrency="$" data-planamount="<?php echo $plan3?>" data-plancredits="250000" data-contactcredits="UL" data-validity="1" class="btn plan-btn buttonfooter  <?php echo $planname == 'leplan3' ? 'buttonfooter-disabled' : '' ?>">
                            Subscribe
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="hide" style="text-align:center;width:100%;">
    <div style="display:inline-block;">
        <div class="pricingtable1">
            <div class="pricing-table1">
                <div class="pricing-head1" style="padding-top:50px;">
                    <h3><b>Special Limited Time Offer</b></h3>
                    <div class="pricing-body">
                        <ul>

                            <li class="dollar"><span class="bill">$</span>49<br><span class="billing">for first 90 days (Save $98)</span></li>
                            <li class="dollars" style="margin-top:0px;line-height: 15px;"><span class="billing">then $49/ month, paid month on month.</span></li>
                            <a href="#" type="button"  data-planname="leplan1" data-plancurrency="$" data-planamount="49" data-plancredits="UL" data-validity="1" class="btn btn-success buttonles plan-btn">
                                Subscribe
                            </a>
                        </ul>
                    </div>
                </div>
                <div class="pricing-head1">
                    <div class="pricing-body1">

                        <ul>
                            <li style="margin-left:55px;">Add Unlimited Contacts</li>
                            <li style="margin-left:55px;">Send Unlimited Email, SMS Campaigns</li>
                            <li style="margin-left:55px;">Create Unlimited Automation Workflows</li>
                            <li style="margin-left:55px;">Create Unlimited Landing Pages & Lead Capture Forms</li>
                            <li style="margin-left:55px;">Track Unlimited Email Opens, Clicks</li>
                            <li style="margin-left:55px;">Track Unlimited Website Visits & Events</li>
                            <li style="margin-left:55px;">Manage Scoring, Points, Segments, Tags</li>
                            <li style="margin-left:55px;">Zapier, Webhooks & API Integrations (Coming Soon)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="pricing-type-modal-backdrop hide" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #2a323c; opacity: 0.9; z-index: 9000"></div>

<div class="modal fade in pricing-type-modal hide" style="display: block; z-index: 9999;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="padding-bottom:0px;">
                <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.pricing-type-modal', '<?php echo $redirecturl ?>');" class="close" ><span aria-hidden="true">&times;</span></a>
                <div class="card-holder-title">
                    <?php echo $view['translator']->trans('le.pricing.model.header'); ?>
                </div>
                <p class="header_desc"></p>
                <div class="modal-loading-bar"></div>
            </div>
            <div class="modal-body form-select-modal" style="padding-top:0px;">
                <div class="alert alert-info hide" id="card-holder-info" role="alert"></div>
                <div class="card-holder-title">
                    Add your card
                </div>
                <div id="card-holder-widget" data-le-token="<?php echo $letoken?>">
                    <!-- A Stripe Element will be inserted here. -->
                </div>
                <!-- Used to display form errors. -->
                <div id="card-holder-errors" role="alert"></div>
                <button type="button" class="btn btn-default pay-now-btn">
                </button>
                <br>
                <br>
                <p id="pro-data-text" class="hide"></p>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
