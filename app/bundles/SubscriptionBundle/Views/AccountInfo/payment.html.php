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

    <?php echo $view->render('MauticSubscriptionBundle:AccountInfo:steps.html.php', [
        'step'                => 'paymenthistory',
        'typePrefix'          => $typePrefix,
        'actionRoute'         => $actionRoute,
        'planType'            => $planType,
    ]); ?>

    <!-- container -->
    <div class="col-md-9 bg-auto height-auto bdr-l accountinfo">
        <!-- Tab panes -->
        <div class="tab-content">

            <div role="tabpanel" class="tab-pane fade in active bdr-w-0" id="paymenthistory">
                <div class="pt-md pr-md pl-md pb-md">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                        <h3 class="panel-title"><?php echo $view['translator']->trans('leadsengage.payment.history.title'); ?></h3>
                        </div>
                        <div class="panel-body">
                            <?php echo $view->render('MauticSubscriptionBundle:AccountInfo:payment_history.html.php', [
                                'payments'         => $payments,
                            ]); ?>
                    </div>
                </div>
                </div>
            </div>

        </div>
    </div>
</div>
