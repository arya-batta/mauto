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
<div class="payment-status-holder">
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
                    <span style="display: block"><span class="payment-status-lbl-left">Amount:</span><?php echo $currency.$amount ?></span>
                    <span style="display: block"><span class="payment-status-lbl-left">Payment ID:</span><?php echo $paymentid ?></span>
                    <span style="display: block"><span class="payment-status-lbl-left">Order ID:</span><?php echo $orderid ?></span>
                </div>
            </td>
            <td>
                <div class="transaction-details">
                    <span class="payment-status-header">Order Details</span>
                    <span style="display: block"><span class="payment-status-lbl-left">Plan:</span><?php echo $plan ?></span>
                    <span style="display: block"><span class="payment-status-lbl-left">Available Credits Before Payment:</span><?php echo $creditsbefore ?></span>
                    <span style="display: block"><span class="payment-status-lbl-left">Added Email Credits:</span><?php echo $addedcredits ?></span>
                    <span style="display: block"><span class="payment-status-lbl-left">Available Credits After Payment:</span><?php echo $creditsafter ?></span>
                    <span style="display: block"><span class="payment-status-lbl-left">Valid Till:</span><?php echo $validitytill ?></span>
                </div>
            </td>
        </tr>
    </table>
</div>
<div class="clearfix"></div>
