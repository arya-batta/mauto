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
$view['slots']->set('leContent', 'subscription');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.core.subscriptions'));
?>
<div id="subscription-panel" role="tablist" aria-multiselectable="true">
    <div class="section">
        <div class="section-header" role="tab" id="headingOne">
            <h5 class="mb-0">
                <a data-toggle="collapse" data-parent="#subscription-panel" href="#sectionOne" aria-expanded="true" aria-controls="sectionOne">
                    Choose an edition based on your business needs
                </a>
            </h5>
        </div>

        <div id="sectionOne" class="collapse show in" role="tabpanel" aria-labelledby="headingOne">
            <div class="section-block">
                <?php if ($plans) : ?>
                    <div class="row">
                        <?php foreach ($plans as $planKey => $planInfo) : ?>
                        <?php
                            $planname= $planInfo['name'];
                            $plankey = $planInfo['key'];
                            $features=$planInfo['features'];
                            $details =$planInfo['details'];
                            $price   =$planInfo['price'];
                            $rupees  =$price['INR'];
                            $doller  =$price['USD'];
                            ?>
                            <div class="col-md-3 plan-list">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <h4 class="plan-header"><?php echo $planname; ?></h4>
                                        <div class="price">
                                            <span><?php echo $isIndianCurrency ? '₹' : '$'; ?></span>
                                            <span><?php echo $isIndianCurrency ? $rupees : $doller; ?></span>
                                        </div>
                                            <div class="details-list">
                                                <?php if ($details) : ?>
                                                <?php foreach ($details as $detail): ?>
                                                    <span><?php echo $detail; ?></span>
                                                <?php endforeach; ?>

                                                <?php endif; ?>
                                            </div>
                                        <a href="#" type="button" data-plankey="<?php echo $plankey; ?>" data-planname="<?php echo $planname; ?>" data-plancurrency="<?php echo $isIndianCurrency ? '₹' : '$'; ?>" data-planamount="<?php echo $isIndianCurrency ? $rupees : $doller; ?>"class="btn btn-success plan-btn">
                                            Upgrade
                                        </a>
                                        <div class="feature-list">
                                            <?php if ($features) : ?>
                                                    <?php foreach ($features as $feature): ?>
                                                        <span><?php echo $feature; ?></span>
                                                    <?php endforeach; ?>

                                            <?php endif; ?>
                                        </div>
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
                <a class="collapsed" data-toggle="collapse" data-parent="#subscription-panel" href="#sectionTwo" aria-expanded="false" aria-controls="sectionTwo">
                    Edition Details
                </a>
            </h5>
        </div>
        <div id="sectionTwo" class="collapse" role="tabpanel" aria-labelledby="headingTwo">
            <div class="section-block">
<table>
    <tbody>
    <tr>
        <td>
            <span> Payment Frequency </span>
        </td>
        <td>
            <div class="paymentduration">
                <input type="radio" name="duration" data-frequencybtn="year" id="payment-yearly" value="year" class="paymentduration-btn">
                <label for="yearly" class="paymentduration-lbl" type="radio">Yearly</label>
                <input type="radio" name="duration" data-frequencybtn="month" id="payment-monthly" value="month" class="paymentduration-btn" checked="checked">
                <label for="monthly" class="paymentduration-lbl" type="radio">Monthly</label>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <span> Selected Edition </span>
        </td>
        <td>
            <span id="selected-plan"></span>
        </td>
    </tr>
    <tr>
        <td>
           <span id="paymentduration-desc"> Amount to be paid per month </span>
        </td>
        <td>
            <span id="planamount-desc" planname="" plankey="" plancurrency="" planamount="" totalamount="" plancycle=""></span>
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
                <a style="pointer-events:none;" class="collapsed" data-toggle="collapse" data-parent="#subscription-panel" href="#sectionThree" aria-expanded="false" aria-controls="sectionThree">
                    Review Details
                </a>
            </h5>
        </div>
        <div id="sectionThree" class="collapse" role="tabpanel" aria-labelledby="headingThree">
            <div class="section-block">
               <div class="summary-title">
                   Order Summary
               </div>
                <table>
                    <tbody>
                    <tr>
                        <td>
                            Item
                        </td>
                        <td>
                            Price
                        </td>
                        <td>
                            Tax
                        </td>
                        <td>
                            <span>Total<span><span class="short-desc">(amount to be paid)</span>
                        </td>
                    </tr>
                    <tr>
                    <tr>
                        <td >
                            <span class="cplantitle"></span>
                        </td>
                        <td >
                            <span class="cplanamtcurrency cplancurrency"></span>
                            <span class="cplanamount cplanamtlbl"></span>
                        </td>
                        <td>
                            <span class="cplantaxcurrency cplancurrency"></span>
                            <span class="cplantax cplanamtlbl"></span>
                        </td>
                        <td>
                            <span class="cplantotalcurrency cplancurrency"></span>
                            <span class="cplantotal cplanamtlbl"></span>
                        </td>
                    </tr>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <button id="paymentconfirm-btn" style="float: right;margin-right: 15px" class="btn btn-danger" value="Confirm" type="button">Confirm</button>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
    <div class="section">
        <div class="section-header" role="tab" id="headingFour">
            <h5 class="mb-0">
                <a style="pointer-events:none;" class="collapsed" data-toggle="collapse" data-parent="#subscription-panel" href="#sectionFour" aria-expanded="false" aria-controls="sectionFour">
                    Payment Details
                </a>
            </h5>
        </div>
        <div id="sectionFour" class="collapse" role="tabpanel" aria-labelledby="headingFour">
            <div class="section-block">
                    <form>
                        <div class="form-group">
                            <label for="usr">Name:</label>
                            <input type="text" class="form-control" id="payment-username">
                        </div>
                        <div class="form-group">
                            <label for="pwd">Accounting Email:</label>
                            <input type="text" class="form-control" id="payment-useremail">
                        </div>
                        <div class="form-group">
                            <label for="comment">Address:</label>
                            <textarea class="form-control has-error" rows="5" id="payment-useraddress"></textarea>
                        </div>
                    </form>
                <table>
                    <tbody>
                    <tr>
                        <td>
                            <span>
                                <b> Note:</b> By proceeding, you agree that all the informations provided above are correct to your knowledge. It will be added to your invoice.
                            </span>
                        </td>
                        <td>

                            <button id="makepayment-btn" class="btn btn-danger" value="Make Payment" type="button">Make Payment</button>

                        </td>
                    </tr>
                    </tbody>
                </table>
                </div>

        </div>
    </div>
    <div class="subscription-desc">
                    <div class="subscription-desc-title">About your subscription</div>
                    <div class="subscription-desc-notes">All subscriptions will be automatically renewed from your credit card or PayPal account on a recurring basis and we'll send you a receipt each time. You can upgrade, downgrade or cancel anytime. If the subscription is canceled, refunds and termination of access will follow the <a target="_blank" href="http://anyfunnels.com/">Terms of Service</a>. Prices are in <span class="prices_in">Indian Rupee</span> and subject to change. Other restrictions and taxes may apply.</div>
    </div>
</div>
<div class="clearfix"></div>
