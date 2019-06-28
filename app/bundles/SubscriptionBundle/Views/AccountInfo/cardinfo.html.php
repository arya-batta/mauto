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
$view['slots']->set('leContent', 'accountinfo');
$view['slots']->set('headerTitle', $view['translator']->trans('leadsengage.accountinfo.header.title'));

$carderror = $view['translator']->trans('le.payment.failure.oncarderror');
$carderror = str_replace('|URL|', 'Le.openPluginModel("card-error-info")', $carderror);
$hidenoti  = '';
if ($lastpayment == null) {
    $hidenoti = 'hide';
} elseif ($lastpayment->getPaymentStatus() == 'Paid') {
    $hidenoti = 'hide';
}
?>
<!-- start: box layout -->
<div class="box-layout">
    <!-- step container -->
    <?php echo $view->render('MauticSubscriptionBundle:AccountInfo:steps.html.php', [
        'step'                => 'cardinfo',
        'typePrefix'          => $typePrefix,
        'actionRoute'         => $actionRoute,
        'planType'            => $planType,
        'planName'            => $planName,
        'isEmailVerified'     => $isEmailVerified,
    ]); ?>
    <!-- container -->
    <div class="col-md-9 bg-auto height-auto accountinfo">

        <!-- Tab panes -->
        <div class="">
            <div role="tabpanel" class="tab-pane fade in active bdr-w-0" id="cardinfo">
                <div class="pt-md pr-md pl-md pb-md">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?php echo $view['translator']->trans('leadsengage.cardinfo.history.title'); ?></h3>
                        </div>
                        <div class="cardholder-panel">
                            <div class="alert alert-info hide" id="card-holder-info" role="alert"></div>

                            <div class="state-inactive-alert-content">
                                <span class="login-notifiation <?php echo $hidenoti; ?>" style="width: 100%;text-align: left;border:1px solid #fef4f6;">
                                There was an error when we tried to bill your credit card ending in <?php echo $stripecard->getlast4digit(); ?> for your subscription to AnyFunnels.
                                <br>
                                This frequently occurs when there is<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;=> a billing error caused by your bank<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;=> a change in your billing address<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;=> insufficient credit on your account<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;=> your credit card has expired<br>
                                <br>
                                Your account has been kept Inactive temporarily, kindly update your account information with a valid credit card to reactivate your account.
                                <br><br>
                                Unfortunately if after 30 days, we still cannot successfully bill your credit card then your AnyFunnels account will be suspended automatically.
                                <br>
                                If you have a question, please <a href="https://anyfunnels.freshdesk.com/support/tickets/new" target="_blank" style="color:blue;text-decoration: underline;">click here</a> to contact our support team.
                                </span>
                            </div>
                            <div>
                                <div class="card-holder-title">
                                    Credit Card
                                </div>
                                <div class="card-holder-sub-title <?php echo empty($stripecard->getlast4digit()) ? 'hide' : ''; ?>">
                                    <?php echo 'Card ending in '.$stripecard->getlast4digit().' on file.' ?>
                                </div>

                                <div id="card-holder-widget" data-le-token="<?php echo $letoken; ?>">
                                    <!-- A Stripe Element will be inserted here. -->
                                </div>
                                <!-- Used to display form errors. -->
                                <div id="card-holder-errors" role="alert"></div>
                                <button type="button" class="btn btn-default card-update-btn">
                                    Update Card
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<div class="hide" id="card-error-info">
    <?php echo $view->render('MauticSubscriptionBundle:AccountInfo:carderror.html.php'); ?>
</div>
