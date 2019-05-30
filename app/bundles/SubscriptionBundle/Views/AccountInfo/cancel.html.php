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

?>
<!-- start: box layout -->
<div class="box-layout">
    <!-- step container -->
    <?php echo $view->render('MauticSubscriptionBundle:AccountInfo:steps.html.php', [
        'step'                => 'cancelsubscription',
        'typePrefix'          => $typePrefix,
        'actionRoute'         => $actionRoute,
        'planType'            => $planType,
        'planName'            => $planName,
    ]); ?>
    <!-- container -->
    <div class="col-md-9 bg-auto height-auto accountinfo">

        <!-- Tab panes -->
        <div class="">

            <div role="tabpanel" class="tab-pane fade in active bdr-w-0" >
                <div class="pt-md pr-md pl-md pb-md" id="paymenthistory">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?php echo $view['translator']->trans('leadsengage.cancel.subscription.title'); ?></h3>
                        </div>
                        <div class="cancelsubscription panel-body" <?php echo !$isCancelled ? 'style="display:block;padding-top:0px;"' : 'style="display:none;"' ?>>
                            <br>
                            <p style="text-align: left;font-family: 'Open Sans', Helvetica, Arial, sans-serif;font-size:14px;padding: 0 0 15px;"><?php echo $view['translator']->trans('leadsengage.cancel.'.strtolower($planType).'.description', ['%recordcount%' => $recordcount, '%licenseenddate%'=>$licenseenddate, '%planname%'=>$planLabel, '%contactcount%' => $contactcount, '%emailcount%' => $emailcount]); ?></p>
                            <br>
                            <a  href="javascript: void(0);" onclick="Le.openCancelSubscriptionModel()" <?php echo $planType == 'Paid' ? 'class="cancel-subscription1"' : 'class="hide"' ?>><?php echo $view['translator']->trans('leadsengage.cancel.subscription.title'); ?></a>
                            <br>
                            <br>
                        </div>
                        <div class="deactivatedaccount panel-body"<?php echo $isCancelled ? 'style="display:block;padding-top:0px;"' : 'style="display:none;"' ?>>
                            <br>
                            <p style="text-align: left;font-weight: normal;font-family: Open Sans, Helvetica, Arial, sans-serif;font-size:14px;"><?php echo $view['translator']->trans('leadsengage.account.cancel.description', ['%cancellationdate%' => $canceldate]); ?></p>
                            <br>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cancel-subscription-modal-backdrop hide" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #2a323c; opacity: 0.9; z-index: 9000"></div>

        <div class="modal fade in cancel-subscription-modal hide" style="display: block; z-index: 9999;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" style="padding-bottom:0px;">
                        <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.cancel-subscription-modal', '<?php echo $view['router']->path($actionRoute, ['objectAction' => 'cancel']) ?>');" class="close" ><span aria-hidden="true">&times;</span></a>
                        <h4 class="modal-title">
                            <?php echo $view['translator']->trans('le.cancel.subscription.header'); ?>
                        </h4>
                        <div class="modal-loading-bar"></div>
                    </div>
                    <div class="modal-body form-select-modal" style="padding-top:0px;">
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="control-label" style="font-weight: normal;">Select Reason for Cancellation</label>
                                <select name="cancel_reason" class="form-control" id="cancel_reason">
                                    <option value="Just_want_to_try">Just want to try</option>
                                    <option value="Product_is_difficult_to_use">Product is difficult to use</option>
                                    <option value="Email_delivery_spam_issues">Email delivery/spam issues</option>
                                    <option value="Issues_with_product">Issues with product</option>
                                    <option value="Not_happy_with_support">Not happy with support</option>
                                    <option value="Not_fulfilling_my_requirements">Not fulfilling my requirements</option>
                                    <option value="Moved_to_better_solution">Moved to better solution</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                        </div>
                        <br>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <label class="control-label" style="font-weight: normal;">Remarks/Feedback</label>
                                <textarea class="form-control" id="reason_feedback"></textarea>
                            </div>
                        </div>
                        <br>
                        <button type="button" class="btn btn-default pay-now-btn" onclick="Le.cancelSubscription();">
                            <?php echo $view['translator']->trans('le.cancel.subscription.model.button'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
