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
$view['slots']->set('leContent', 'prepaidplans');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.core.prepaidplans'));
?>
<div id="prepaidplan-panel" role="tablist" aria-multiselectable="true">
    <div class="section">
        <div class="section-header" role="tab" id="headingOne">
            <h5 class="mb-0">
                <a data-toggle="collapse" data-parent="#prepaidplan-panel" href="#sectionOne" aria-expanded="true" aria-controls="sectionOne">
                    Choose an plan based on your business needs
                </a>
            </h5>
        </div>

        <div id="sectionOne" class="collapse in" role="tabpanel" aria-labelledby="headingOne">
            <div class="section-block">
                <?php if ($plans) : ?>
                    <div class="row">
                        <?php foreach ($plans as $planInfo) : ?>
                        <?php
                            $name     = $planInfo['name'];
                            $label    = $planInfo['label'];
                            $credits  =$planInfo['credits'];
                            $months   =$planInfo['months'];
                            $rupees   =$planInfo['price_inr'];
                            $doller   = $planInfo['price_usd'];
                            ?>
                            <div class="col-md-3 prepaid-plan-list">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <h4 class="plan-header"><?php echo $label; ?></h4>
                                        <div class="price">
                                            <span><?php echo $isIndianCurrency ? '₹' : '$'; ?></span>
                                            <span><?php echo $isIndianCurrency ? number_format($rupees) : $doller; ?></span>
                                        </div>
                                            <div class="details-list">

                                                    <span><?php echo number_format($credits).' Email Credits'; ?></span>

                                                <span><?php echo $months > 1 ? $months.' Months Validity' : $months.' Month Validity' ?></span>
                                            </div>
                                        <a href="#" type="button" data-plankey="<?php echo $name; ?>" data-planname="<?php echo $label; ?>" data-plancurrency="<?php echo $isIndianCurrency ? '₹' : '$'; ?>" data-planamount="<?php echo $isIndianCurrency ? $rupees : $doller; ?>" data-plancredits="<?php echo $credits; ?>"class="btn btn-success plan-btn">
                                            Buy Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <div class="section">
        <div class="section-header" role="tab" id="headingTwo">
            <h5 class="mb-0">
                <a class="collapsed" data-toggle="collapse" data-parent="#prepaidplan-panel" href="#sectionTwo" aria-expanded="false" aria-controls="sectionTwo">
                    Plan Details
                </a>
            </h5>
        </div>
        <div id="sectionTwo" class="collapse" role="tabpanel" aria-labelledby="headingTwo">
            <div class="section-block">
<table>
    <tbody>
    <tr>
        <td>
            <span> Plan Selected </span>
        </td>
        <td>
            <span id="selected-plan"></span>
        </td>
    </tr>
    <tr>
        <td>
            <span> Available Credits </span>
        </td>
        <td>
            <span id="available-credits"></span>
        </td>
    </tr>
    <tr>
        <td>
            <span> Additional Credits </span>
        </td>
        <td>
            <span id="additional-credits"></span>
        </td>
    </tr>
    <tr>
        <td>
            <span> Total Credits After Buying </span>
        </td>
        <td>
            <span id="total-credits"></span>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <button id="paymentcontinue-btn" style="float: right;margin-right: 15px" class="btn btn-danger" value="Continue" type="button">Continue</button>
        </td>
    </tr>
    </tbody>
</table>

            </div>
        </div>
    </div>
    <div class="section">
        <div class="section-header" role="tab" id="headingThree">
            <h5 class="mb-0">
                <a style="pointer-events:none;" class="collapsed" data-toggle="collapse" data-parent="#prepaidplan-panel" href="#sectionThree" aria-expanded="false" aria-controls="sectionThree">
                    Payment Details
                </a>
            </h5>
        </div>
        <div id="sectionThree" class="collapse" role="tabpanel" aria-labelledby="headingThree">
            <div class="section-block">
                <table>
                    <tbody>
                    <tr>
                        <td>
                            <span> Plan Pricing </span>
                        </td>
                        <td>
                            <span class="currency_symbol pricing-currency"></span>
                            <span id="plan-pricing"></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span id="tax-label">  </span>
                        </td>
                        <td>
                            <span class="currency_symbol tax-currency"></span>
                            <span id="tax-amount"></span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span> Total Amount </span>
                        </td>
                        <td>
                            <span class="currency_symbol total-currency"></span>
                            <span id="total-amount"></span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button id="makepayment-btn" style="float: right;margin-right: 15px" class="btn btn-danger" value="makepayment" type="button">Make Payment</button>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
<!--    <div class="section">-->
<!--        <div class="section-header" role="tab" id="headingFour">-->
<!--            <h5 class="mb-0">-->
<!--                <a style="pointer-events:none;" class="collapsed" data-toggle="collapse" data-parent="#subscription-panel" href="#sectionFour" aria-expanded="false" aria-controls="sectionFour">-->
<!--                    Payment Details-->
<!--                </a>-->
<!--            </h5>-->
<!--        </div>-->
<!--        <div id="sectionFour" class="collapse" role="tabpanel" aria-labelledby="headingFour">-->
<!--            <div class="section-block">-->
<!--                    <form>-->
<!--                        <div class="form-group">-->
<!--                            <label for="usr">Name:</label>-->
<!--                            <input type="text" class="form-control" id="payment-username">-->
<!--                        </div>-->
<!--                        <div class="form-group">-->
<!--                            <label for="pwd">Accounting Email:</label>-->
<!--                            <input type="text" class="form-control" id="payment-useremail">-->
<!--                        </div>-->
<!--                        <div class="form-group">-->
<!--                            <label for="comment">Address:</label>-->
<!--                            <textarea class="form-control has-error" rows="5" id="payment-useraddress"></textarea>-->
<!--                        </div>-->
<!--                    </form>-->
<!--                <table>-->
<!--                    <tbody>-->
<!--                    <tr>-->
<!--                        <td>-->
<!--                            <span>-->
<!--                                <b> Note:</b> By proceeding, you agree that all the informations provided above are correct to your knowledge. It will be added to your invoice.-->
<!--                            </span>-->
<!--                        </td>-->
<!--                        <td>-->
<!---->
<!--                            <button id="makepayment-btn" class="btn btn-danger" value="Make Payment" type="button">Make Payment</button>-->
<!---->
<!--                        </td>-->
<!--                    </tr>-->
<!--                    </tbody>-->
<!--                </table>-->
<!--                </div>-->

        </div>
    </div>
<!--    <div class="subscription-desc">-->
<!--                    <div class="subscription-desc-title">About your subscription</div>-->
<!--                    <div class="subscription-desc-notes">All subscriptions will be automatically renewed from your credit card or PayPal account on a recurring basis and we'll send you a receipt each time. You can upgrade, downgrade or cancel anytime. If the subscription is canceled, refunds and termination of access will follow the <a target="_blank" href="https://leadsengage.com/">Terms of Service</a>. Prices are in <span class="prices_in">Indian Rupee</span> and subject to change. Other restrictions and taxes may apply.</div>-->
<!--    </div>-->
</div>
<div class="clearfix"></div>
