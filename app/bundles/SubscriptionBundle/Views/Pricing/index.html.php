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
$view['slots']->set('mauticContent', 'pricingplans');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.core.prepaidplans'));
?>

<div style="display: block;text-align: center" class="alert alert-danger hide" id="pricing-plan-alert-info" role="alert"> You're not quite ready to process the payment.
    You need to connect your Email Provider account to proceed further. <a href="<?php echo $view['router']->path('mautic_config_action', ['objectAction' => 'edit']); ?>">
        Click Here
    </a> to connect it.</div>
<br>
<div style="text-align:center;width:100%;">
    <div style="display:inline-block;">
        <div class="pricingtables">

            <div class="pricing-table">
                <div class="pricing-head">
                    <h3>Pay Monthly</h3>
                    <div class="pricing-body">
                        <ul>
                            <li class="dollar"><span class="bill">$</span>79<br><span class="billing">Per Month, Billed Monthly</span></li>
                            <li style="margin-left:55px;">Unlimited Contacts</li>
                            <li style="margin-left:55px;">Unlimited Features</li>
                            <a href="#" type="button"  data-planname="leplan1" data-plancurrency="$" data-planamount="79" data-plancredits="UL" data-validity="1" class="btn btn-success buttonle plan-btn">
                                Subscribe
                            </a>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="pricing-tables">
                <div class="pricing-heads">
                    <h3>Pay Annually</h3>
                    <span class="discounted-price--tag">37% Off</span>
                    <div class="pricing-body">
                        <ul>
                            <li class="dollar"><span class="bill">$</span>49<br><span class="billing">Per Month, Billed Annually - <b>$588</b>/Yr</span></li>
                            <li style="margin-left:55px;">Unlimited Contacts</li>
                            <li style="margin-left:55px;">Unlimited Features</li>
                            <a href="#" type="button"  data-planname="leplan2" data-plancurrency="$" data-planamount="588" data-plancredits="UL" data-validity="12" class="btn btn-success buttonles plan-btn">
                                Subscribe
                            </a>
                        </ul>
                    </div>
                </div>
            </div>
        </div></div></div>
<div style="text-align:center;width:100%;">
    <div style="display:inline-block;">
        <div class="pricingtable1">
            <div class="pricing-table1">
                <div style="padding-left:15px;">
                    <img class="pricing-img-responsive" src="https://leadsengage.com/wp-content/uploads/leadsengage/startup.png" alt="/icon/startup.png"><br><h2>All features includes <br>the following</h2></div>
                <div class="pricing-head1">
                    <div class="pricing-body1">

                        <ul>
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
        </div></div></div>
<div class="pricing-plan-holder " data-email-transaport="<?php echo $transport ?>">
    <div class="col-md-4 pricing-plan-list plan-monthly hide">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="pricing-plan-header">
                    <div class="sub-plan-header">Pay Monthly</div>
                    <div class="price">
                        <span>$</span>
                        <span>149</span>
                    </div>
                    <div class="price-desc">Per Month, Billed Monthly</div>
                </div>
                <div class="details-list">
                    <span>Unlimited Contacts</span>
                   <span>Priority Support Via Email</span>
                    <span>Unlimited Team Members</span>
                    <span>All Leadsengage Features </span>
                </div>
                <a href="#" type="button"  data-planname="leplan2" data-plancurrency="$" data-planamount="149" data-plancredits="UL" data-validity="1" class="btn btn-success plan-btn">
                    Subscribe
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 pricing-plan-list plan-yearly hide">
        <div class="panel panel-default">
            <div class="highlight-ribbon">
                <span class="highlight-ribbon-span">Save 33%</span>
            </div>
            <div class="panel-body">
                <div class="pricing-plan-header">
                    <div class="sub-plan-header">Pay Annually</div>
                    <div class="price">
                        <span>$</span>
                        <span>99</span>
                    </div>
                    <div class="price-desc">Per Month, Billed Annually - $1188/yr</div>
                </div>
                <div class="details-list">
                    <span>Unlimited Contacts</span>
                                      <span>Priority Support Via Email</span>
                    <span>Unlimited Team Members</span>
                    <span>All Leadsengage Features </span>
                </div>
                <a href="#" type="button" data-planname="leplan1" data-plancurrency="$" data-planamount="<?php echo 12 * 99; ?>" data-plancredits="UL" data-validity="12" class="btn btn-success plan-btn">
                    Subscribe
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-10 pricing-plan-list plan-monthly pricing_panel hide" style="margin-left:8%;">
        <div class="col-sm-6 details-list">
            <!--<div class="sub-plan-header">Up to 25,000 Leads</div>
            <div class="price">
                <span>$</span>
                <span>49</span>
            </div>
            <div class="col-sm-6">
                <a href="#" type="button" data-planname="leplan2" data-plancurrency="$" data-planamount="<?php echo 49; ?>" data-plancredits="25000" data-validity="1" class="btn btn-success plan-btn" style="margin-top:5%;margin-left:60%;">
                Subscribe
                </a>
            </div>
            <div class="col-sm-6 price-desc">
                <br>
                <b>Addtional Leads</b> <br> $10/month per 5,000 additional Leads.
            </div>-->
            <p style="font-size:20px;"><b>Feature Includes</b></p>
            <span>Send Unlimited Email, SMS Campaigns</span>
            <!--                    <span>Priority Support Via Email</span>-->
            <span>Create Unlimited Automation Workflows</span>
            <span>Create Unlimited Landing Pages & Lead Capture Forms</span>
        </div>
        <div class="col-sm-6 details-list">
            <span>Track Unlimited Email Opens, Clicks</span>
            <!--                    <span>Priority Support Via Email</span>-->
            <span>Track Unlimited Website Visits & Events</span>
            <span>Manage Lead Scoring, Points, Segments, Tagss</span>
            <span>Zapier, Webhooks & API Integrations</span>
        </div>

    </div>

</div>
<div class="pricing-type-modal-backdrop hide" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #000000; opacity: 0.9; z-index: 9000"></div>

<div class="modal fade in pricing-type-modal hide" style="display: block; z-index: 9999;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <a href="javascript: void(0);" onclick="Mautic.closeModalAndRedirect('.pricing-type-modal', '<?php echo $view['router']->path('le_pricing_index'); ?>');" class="close" ><span aria-hidden="true">&times;</span></a>
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
