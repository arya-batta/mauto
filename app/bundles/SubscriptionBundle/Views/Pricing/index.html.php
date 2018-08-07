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
<br>
<br>
<br>
<div class="pricing-plan-holder" data-email-transaport="<?php echo $transport ?>">
    <div class="col-md-4 pricing-plan-list plan-monthly">
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
<!--                    <span>Priority Support Via Email</span>-->
                    <span>Unlimited Team Members</span>
                    <span>All Leadsengage Features </span>
                </div>
                <a href="#" type="button"  data-planname="leplan2" data-plancurrency="$" data-planamount="149" data-plancredits="UL" data-validity="1" class="btn btn-success plan-btn">
                    Subscribe
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4 pricing-plan-list plan-yearly">
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
                    <!--                    <span>Priority Support Via Email</span>-->
                    <span>Unlimited Team Members</span>
                    <span>All Leadsengage Features </span>
                </div>
                <a href="#" type="button" data-planname="leplan1" data-plancurrency="$" data-planamount="<?php echo 12 * 99; ?>" data-plancredits="UL" data-validity="12" class="btn btn-success plan-btn">
                    Subscribe
                </a>
            </div>
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
