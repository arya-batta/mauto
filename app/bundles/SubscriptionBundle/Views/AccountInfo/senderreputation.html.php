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
        'step'                => 'senderreputation',
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
                        <div class="panel-heading senderreputation_config">
                            <h3 class="panel-title"><?php echo $view['translator']->trans('le.config.tab.senderreputation'); ?></h3>
                            <p style="font-size: 12px;"><?php echo $view['translator']->trans('le.email.config.sender.reputation.help'); ?></p>
                        </div>
                        <div class="panel-body">
                            <table style="width: 100%;">
                                <thead>
                                <tr>
                                    <th style="width: 20%;">
                                        Property
                                    </th>
                                    <th style="width: 50%">
                                        Description
                                    </th>
                                    <th style="width: 15%;text-align: center;">
                                        Value
                                    </th>
                                    <th style="width: 15%;text-align: center;">
                                        Score Impact
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (isset($emailreputations) && sizeof($emailreputations) > 0): ?>
                                    <?php foreach ($emailreputations[0] as $emailreputation): ?>
                                        <tr>
                                            <td>
                                                <?php echo $emailreputation[0]?>
                                            </td>
                                            <td>
                                                <?php echo $emailreputation[1]?>
                                            </td>
                                            <td style="text-align: center;">
                                                <?php echo $emailreputation[2]?>
                                            </td>
                                            <td style="text-align: center;">
                                                <?php echo $emailreputation[3]?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                </tbody>
                            </table>
                            <?php if (isset($emailreputations) && sizeof($emailreputations) > 0): ?>
                                <div class="panel pull-right col-xs-12 col-sm-6 col-md-6 reputation-summary-box">
                                    <div class="col-xs-12 col-sm-5">Reputation</div>
                                    <div  class="col-xs-12 col-sm-7">
                                        <div class="col-xs-3"><strong><?php echo $emailreputations[1]?></strong></div>
                                        <div class="col-xs-7">
                                            <div style="text-align: center;">
                                                <i class="fa fa-star" style="<?php echo $emailreputations[2] > 0 ? 'color:#3292e0' : 'color:#e6e6e6'?>" aria-hidden="true"></i>
                                                <i class="fa fa-star" style="<?php echo $emailreputations[2] > 1 ? 'color:#3292e0' : 'color:#e6e6e6'?>" aria-hidden="true"></i>
                                                <i class="fa fa-star" style="<?php echo $emailreputations[2] > 2 ? 'color:#3292e0' : 'color:#e6e6e6'?>" aria-hidden="true"></i>
                                                <i class="fa fa-star" style="<?php echo $emailreputations[2] > 3 ? 'color:#3292e0' : 'color:#e6e6e6'?>" aria-hidden="true"></i>
                                                <i class="fa fa-star" style="<?php echo $emailreputations[2] > 4 ? 'color:#3292e0' : 'color:#e6e6e6'?>" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


