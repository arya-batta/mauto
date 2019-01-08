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
$view['slots']->set('leContent', 'pricingplans');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.core.prepaidplans'));
?>

<div style="display: block;text-align: center" class="alert alert-danger hide" id="pricing-plan-alert-info" role="alert"> You're not quite ready to process the payment.
    You need to connect your Email Provider account to proceed further. <a href="<?php echo $view['router']->path('le_config_action', ['objectAction' => 'edit']); ?>">
        Click Here
    </a> to connect it.</div>
<br>
<div style="text-align:center;width:100%;">
    <div style="display:inline-block;">
        <div class="row">

            <div class="plan-card col-md-8">
                <div class="plan-name">
                    <h2>Engage</h2>
                    <div class="">
                        <ul class="plan-pricing">
                            <li class="price">
                                <span class="currency-symbol">$</span>49
                            </li>
                            <li class="terms">Per Month,<br> Billed Monthly</li>
                        </ul>
                        <hr>
                        <h3>Features</h3>
                        <p class="pricepara">Unlimited <b>Contacts</b></p>
                        <p class="pricepara">Unlimited <b>Campaigns</b></p>
                        <p class="pricepara">Unlimited <b>Drip Sequences</b></p>
                        <p class="pricepara">Unlimited <b>Automations</b></p>
                        <p class="pricepara">Unlimited <b>Tracking</b></p>
                        <p class="pricepara">Unlimited <b>Lead Scoring Rules</b></p>
                        <p class="pricepara">Unlimited <b>Teammates</b></p>
                        <br>
                        <h3>Emails</h3>
                        <p class="pricepara">Connect your 3rd Party Email Service Provider for emails (Amazon SES, Sendgrid, Spark Post or Elastic Email)</p>
                        <!--<a href="/signup-free-trial" class="buttonfooter" id="plancardbutt"style="margin-top: 115px;">Try for Free</a>-->
                        <br>
                        <br>
                        <br>

                        <a href="#" type="button"  data-planname="leplan1" data-plancurrency="$" data-planamount="49" data-plancredits="UL" data-validity="1" class="btn plan-btn buttonfooter">
                            Subscribe
                        </a>
                    </div>
                </div>
            </div>
            <div class="plan-card col-md-8" id="plan-card">
                <div class="plan-name">
                    <h2 >Engage Pro</h2>
                    <div class="">
                        <ul class="plan-pricing">
                            <li class="price">
                                <span class="currency-symbol">$</span>79
                            </li>
                            <li class="terms">Per Month,<br> Billed Monthly</li>
                        </ul>
                        <hr>
                        <h3>Features</h3>
                        <p class="pricepara">Unlimited <b>Contacts</b></p>
                        <p class="pricepara">Unlimited <b>Campaigns</b></p>
                        <p class="pricepara">Unlimited <b>Drip Sequences</b></p>
                        <p class="pricepara">Unlimited <b>Automations</b></p>
                        <p class="pricepara">Unlimited <b>Tracking</b></p>
                        <p class="pricepara">Unlimited <b>Lead Scoring Rules</b></p>
                        <p class="pricepara">Unlimited <b>Teammates</b></p>
                        <br>
                        <h3>Emails</h3>
                        <p class="pricepara">No 3rd party email service provider required.</p>
                        <p class="pricepara">The plan includes <b>100,000 free emails</b> every month.</p>
                        <p class="pricepara">And Pay for additional emails on actual usage if required ($9 for every 10,000 additional emails).</p>
                        <a href="#" type="button"  data-planname="leplan2" data-plancurrency="$" data-planamount="79" data-plancredits="UL" data-validity="1" class="btn plan-btn buttonfooter">
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
                            <li style="margin-left:55px;">Manage Lead Scoring, Points, Segments, Tags</li>
                            <li style="margin-left:55px;">Zapier, Webhooks & API Integrations (Coming Soon)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="pricing-type-modal-backdrop hide" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #000000; opacity: 0.9; z-index: 9000"></div>

<div class="modal fade in pricing-type-modal hide" style="display: block; z-index: 9999;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.pricing-type-modal', '<?php echo $view['router']->path('le_pricing_index'); ?>');" class="close" ><span aria-hidden="true">&times;</span></a>
                <h4 class="modal-title">
                    <?php echo $view['translator']->trans('le.pricing.model.header'); ?>
                </h4>
                <div class="modal-loading-bar"></div>
            </div>
            <div class="modal-body form-select-modal">
                <div class="alert alert-info hide" id="card-holder-info" role="alert"></div>
                <div class="card-holder-title">
                    Credit Card
                </div>
                <div id="card-holder-widget" data-le-token="<?php echo $letoken?>">
                    <!-- A Stripe Element will be inserted here. -->
                </div>
                <!-- Used to display form errors. -->
                <div id="card-holder-errors" role="alert"></div>
                <button type="button" class="btn btn-default pay-now-btn">
                </button>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>
