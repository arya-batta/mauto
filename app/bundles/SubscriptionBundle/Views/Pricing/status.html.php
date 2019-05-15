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
$view['slots']->set('leContent', 'payment-status');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.core.prepaidplans'));
$paymentid    =$paymentdetails->getPaymentID();
$orderid      =$paymentdetails->getOrderID();
$amount       =$paymentdetails->getAmount();
$currency     =$paymentdetails->getCurrency();
$plan         =$paymentdetails->getPlanLabel();
$addedcredits =$paymentdetails->getAddedCredits();
$creditsbefore=$paymentdetails->getBeforeCredits();
$creditsafter =$paymentdetails->getAfterCredits();
$validitytill =$paymentdetails->getValidityTill();
$validitytill =date('d-M-y', strtotime($validitytill));
?>

<div class="panel" style="margin:20px;padding-top: 20px;padding-bottom: 20px;margin-top:100px;">
    <div class="panel-body box-layout">
        <p style="text-align: center;">
            <span style="font-size: 24px;font-weight: bold;">
                <?php echo $view['translator']->trans('le.pricing.thankyou.header'); ?>
            </span>
        </p>
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-10">
                <p style="text-align: center;">
                    <span>
                        <?php echo $view['translator']->trans('le.pricing.thankyou.content'); ?>
                    </span>
                    <span>
                        <?php echo $view['translator']->trans('le.pricing.thankyou.content1'); ?>
                    </span>
                </p>
                <br>
                <br>
                <div class="text-center">
                    <a class ="price-buttonfoot" href="<?php echo $view['router']->path('le_dashboard_index'); ?>" data-toggle="ajax"><?php echo $view['translator']->trans('mautic.core.go_to_dashboard'); ?></a>
                </div>
            </div>
            <div class="col-md-1"></div>
        </div>
    </div>
</div>
<div class="payment-status-holder hide">
    <table width="100%">
        <tr>
            <td colspan="2">
                <h4>Thank you for your payment</h4>
            </td>
        </tr>
        <tr>
            <td>
                <div class="transaction-details">
                    <span class="payment-status-header">Transaction Details</span>
                </div>
            </td>
            <td>
                <div class="transaction-details">
                    <span class="payment-status-header">Order Details</span>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="transaction-details">
                    <span style="display: block"><span class="payment-status-lbl-left">Amount:</span><?php echo $currency.$amount ?></span>
                     </div>
            </td>
            <td>
                <div class="transaction-details">
                    <span style="display: block"><span class="payment-status-lbl-left">Plan:</span><?php echo $plan ?></span>
                     </div>
            </td>
        </tr> <tr>
            <td>
                <div class="transaction-details">
                    <span style="display: block"><span class="payment-status-lbl-left">Payment ID:</span><?php echo $paymentid ?></span>
                </div>
            </td>
            <td>
                <div class="transaction-details">
                    <span style="display: block"><span class="payment-status-lbl-left">Valid Till:</span><?php echo $validitytill ?></span>
                </div>
            </td>
        </tr> <tr>
            <td>
                <div class="transaction-details">
                   <span style="display: block"><span class="payment-status-lbl-left">Order ID:</span><?php echo $orderid ?></span>
                </div>
            </td>
            <td>
                <div class="transaction-details">
                    <span class="hide" style="display: block"><span class="payment-status-lbl-left">Available Contacts:</span><?php echo $creditsafter ?></span>
                </div>
            </td>
        </tr>
    </table>
</div>
<div class="clearfix"></div>
