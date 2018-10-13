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
$view['slots']->set('mauticContent', 'accountinfo');
$view['slots']->set('headerTitle', $view['translator']->trans('leadsengage.accountinfo.header.title'));
$contactusageper='';
if ($totalContactCredits != 'UL') {
    $contactusageper=($contactUsage / $totalContactCredits) * 100;
    $contactusageper=ceil($contactusageper);
    $contactusageper='('.$contactusageper.'%)';
}
?>
<!-- start: box layout -->
<div class="box-layout">
    <!-- step container -->
    <?php echo $view->render('MauticSubscriptionBundle:AccountInfo:steps.html.php', [
        'step'                => 'billinginfo',
        'typePrefix'          => $typePrefix,
        'actionRoute'         => $actionRoute,
        'planType'            => $planType,
    ]); ?>
    <!-- container -->
    <div class="col-md-9 bg-auto height-auto bdr-l accountinfo">

        <!-- Tab panes -->
        <div class="tab-content">
            <?php echo $view['form']->start($form); ?>
            <div role="tabpanel" class="tab-pane fade in active bdr-w-0" id="billinginfo">
                <div class="pt-md pr-md pl-md pb-md">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?php echo $view['translator']->trans('leadsengage.accountinfo.plan.title'); ?></h3>
                        </div>
                        <div class="panel-body">
                            <span class='plan-info-lbl1'>Plan Type: <b><?php echo $planType ?></b></span>
                            <div class="trial-info-block <?php echo $planType == 'Free Trial' ? '' : 'hide' ?>">
                                <span class='plan-info-lbl2'>Your current plan is <b>Free Trial</b> and includes <b><?php echo $totalContactCredits == 'UL' ? 'unlimited' : $totalContactCredits ?></b> Leads & Features.  Your free trial <?php echo $trialEndDays < 0 ? 'is <b>Expired</b>':'ends in <b>'.$trialEndDays.'</b> days '?></span>
                                <a href="<?php echo $view['router']->path('le_pricing_index'); ?>" class="btn btn-success plan-btn">
                                    Browse Subscription Plans
                                </a>
                            </div>
                            <div class="paid-info-block <?php echo $planType == 'Free Trial' ? 'hide' : '' ?>">
                                <span class='plan-info-lbl2'>Your current plan is <b><?php echo $planAmount ?></b> per month <?php echo $planname == 'leplan1' ? ' paid monthly' : ' paid annually' ?> and includes <b><?php echo $totalContactCredits == 'UL' ? 'unlimited' : $totalContactCredits?></b> Leads & Features. </span>
                                <span class='plan-info-lbl2'>Your next billing date is <b> <?php echo $vallidityTill ?> </b>.</span>
                                <a href="<?php echo $view['router']->path('le_pricing_index'); ?>" class="btn btn-success plan-btn">
                                    Browse Subscription Plans
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pt-md pr-md pl-md pb-md">
                    <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo $view['translator']->trans('leadsengage.accountinfo.billing.title'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <div class="row" style="margin-left: 10px;margin-right:10px;">
                            <div class="row hide">
                            <p style="color: #342345;font-family: 'Open Sans', Helvetica, Arial, sans-serif;font-size:13px;">Plan Type: Basic</p>
                            <br>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                <?php echo $view['form']->label($form['companyname']); ?>
                                <?php echo $view['form']->widget($form['companyname']); ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                <?php echo $view['form']->label($form['accountingemail']); ?>
                                <?php echo $view['form']->widget($form['accountingemail']); ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                <?php echo $view['form']->label($form['companyaddress']); ?>
                                <?php echo $view['form']->widget($form['companyaddress']); ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php echo $view['form']->label($form['city']); ?>
                                    <?php echo $view['form']->widget($form['city']); ?>
                                </div>
                                <div class="col-md-6">
                                    <?php echo $view['form']->label($form['state']); ?>
                                    <?php echo $view['form']->widget($form['state']); ?>
                                </div>

                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php echo $view['form']->label($form['postalcode']); ?>
                                    <?php echo $view['form']->widget($form['postalcode']); ?>
                                </div>
                                <div class="col-md-6">
                                <?php echo $view['form']->label($form['country']); ?>
                                <?php echo $view['form']->widget($form['country']); ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php echo $view['form']->label($form['gstnumber']); ?>
                                    <?php echo $view['form']->widget($form['gstnumber']); ?>
                                </div>
                            </div>
                            <br>
                        </div>
                    </div>
                </div>
                </div>
            </div>
            <?php echo $view['form']->end($form); ?>
        </div>
    </div>
</div>
