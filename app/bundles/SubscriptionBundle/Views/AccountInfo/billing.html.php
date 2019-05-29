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
$emailuseage      ='';
$addoncredits     = 0;
$addonCreditValue = $view['translator']->trans('le.pricing.plan.addon.credits');
if ($totalEmailCredits != 'UL') {
    $emailplancredits = 0;
    if ($planName != '') {
        $emailplancredits = $view['translator']->trans('le.pricing.plan.email.credits.'.$planName);
    }
    $addoncredits      = $totalEmailCredits - $emailplancredits;
    $addonusagecredits = $actualEmailCredits - $emailplancredits;
    if ($addoncredits < 0 || $emailplancredits == 0) {
        $addoncredits = 0;
    }
    if ($addoncredits < 0) {
        $addonusagecredits = 0;
    } else {
        $addonusagecredits = $addonCreditValue - $addoncredits;
    }
    if ($addoncredits > 0 && $emailplancredits != 0) {
        $totalEmailCredits = $emailplancredits;
        if ($actualEmailCredits > $emailplancredits) {
            $actualEmailCredits = $emailplancredits;
        }
    }
    $emailuseage=($actualEmailCredits / $totalEmailCredits) * 100;
    $emailuseage=ceil($emailuseage);
    $emailuseage=$emailuseage.'%';
}
$contactusage = '';
if ($totalContactCredits != 'UL') {
    $contactusage=($contactUsage / $totalContactCredits) * 100;
    $contactusage=ceil($contactusage);
    $contactusage=' ('.$contactusage.'% used)';
}
$actualcontactcredits = '';
if ($planName != '') {
    $planAmount = '$'.$view['translator']->trans('le.pricing.plan.amount.'.$planName);
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
        'planName'            => $planName,
    ]); ?>
    <!-- container -->
    <div class="col-md-9 bg-auto height-auto accountinfo">

        <!-- Tab panes -->
        <div class="">
            <?php echo $view['form']->start($form); ?>
            <div role="tabpanel" class="tab-pane fade in active bdr-w-0" id="billinginfo">
                <div class="pt-md pr-md pl-md pb-md">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title"><?php echo $view['translator']->trans('leadsengage.accountinfo.plan.title'); ?></h3>
                        </div>
                        <div class="panel-body">
                            <span class='plan-info-lbl1 hide'><b><?php echo $planLabel ?></b></span>
                            <div class="trial-info-block <?php echo $planLabel == 'Free Trial' ? '' : 'hide' ?>">
                                <span class='plan-info-lbl2'>You are currently in Free Trial. Usage limits: <?php echo number_format($actualEmailCredits).' out of '?><?php echo $totalEmailCredits == 'UL' ? 'Unlimited' : number_format($totalEmailCredits)?> emails (<?php echo $emailuseage?> used)
                                and <?php echo number_format($contactUsage).' out of '?><?php echo $totalContactCredits == 'UL' ? 'Unlimited' : number_format($totalContactCredits)?> contacts<?php echo $contactusage?>.</span>
                                <span class="plan-info-lbl2 <?php echo $addoncredits == 0 ? 'hide' : ''; ?>" ><br>Additional Email Credits used as of now <?php echo number_format($addonusagecredits); ?> out of 100,000</span>
                                <a href="<?php echo $view['router']->path('le_pricing_index'); ?>" class="btn btn-info plan-btn hide">
                                    Change Plan
                                </a>
                            </div>
                            <div class="paid-info-block <?php echo $planName == 'leplan1' || $planName == 'leplan2' || $planName == 'leplan3' ? '' : 'hide' ?>">
                                <span class='plan-info-lbl2'>Subscribed Plan- <?php echo $planAmount ?> <?php echo $planName == 'leplan1' ? 'for first 3 months,' : 'per month'?> <b><?php echo $planLabel; ?></b>.</span>
                                <span class='plan-info-lbl2'><b>Usage-</b></span>
                                <span class='plan-info-lbl2' style="margin-left: 20px;">Contacts: <?php echo number_format($contactUsage).' out of '?><?php echo $totalContactCredits == 'UL' ? 'Unlimited' : number_format($totalContactCredits)?> contacts<?php echo $contactusage?>.</span>
                                <span class='plan-info-lbl2' style="margin-left: 20px;">Free email credits: <?php echo number_format($actualEmailCredits).' out of '?><?php echo $totalEmailCredits == 'UL' ? 'Unlimited' : number_format($totalEmailCredits)?> emails (<?php echo $emailuseage?> used).</span>
                                <span class="plan-info-lbl2 <?php echo $addoncredits == 0 ? 'hide' : ''; ?>" style="margin-left: 20px;">Add-on email credits: <?php echo number_format($addonusagecredits); ?> out of 100,000 (Unlimited validity)</span>
                                <a href="<?php echo $view['router']->path('le_pricing_index'); ?>" class="btn btn-info plan-btn hide">
                                    Change Plan
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
                                <div class="col-md-12 <?php echo (count($form['companyname']->vars['errors'])) ? ' has-error' : ''; ?>">
                                <?php echo $view['form']->label($form['companyname']); ?>
                                <?php echo $view['form']->widget($form['companyname']); ?>
                                <?php echo $view['form']->errors($form['companyname']); ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12 <?php echo (count($form['accountingemail']->vars['errors'])) ? ' has-error' : ''; ?>">
                                <?php echo $view['form']->label($form['accountingemail']); ?>
                                <?php echo $view['form']->widget($form['accountingemail']); ?>
                                <?php echo $view['form']->errors($form['accountingemail']); ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12 <?php echo (count($form['companyaddress']->vars['errors'])) ? ' has-error' : ''; ?>">
                                <?php echo $view['form']->label($form['companyaddress']); ?>
                                <?php echo $view['form']->widget($form['companyaddress']); ?>
                                <?php echo $view['form']->errors($form['companyaddress']); ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-6 <?php echo (count($form['city']->vars['errors'])) ? ' has-error' : ''; ?>">
                                    <?php echo $view['form']->label($form['city']); ?>
                                    <?php echo $view['form']->widget($form['city']); ?>
                                    <?php echo $view['form']->errors($form['city']); ?>
                                </div>
                                <div class="col-md-6 <?php echo (count($form['state']->vars['errors'])) ? ' has-error' : ''; ?>">
                                    <?php echo $view['form']->label($form['state']); ?>
                                    <?php echo $view['form']->widget($form['state']); ?>
                                    <?php echo $view['form']->errors($form['state']); ?>
                                </div>

                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-6 <?php echo (count($form['postalcode']->vars['errors'])) ? ' has-error' : ''; ?>">
                                    <?php echo $view['form']->label($form['postalcode']); ?>
                                    <?php echo $view['form']->widget($form['postalcode']); ?>
                                    <?php echo $view['form']->errors($form['postalcode']); ?>
                                </div>
                                <div class="col-md-6 <?php echo (count($form['country']->vars['errors'])) ? ' has-error' : ''; ?>">
                                <?php echo $view['form']->label($form['country']); ?>
                                <?php echo $view['form']->widget($form['country']); ?>
                                <?php echo $view['form']->errors($form['country']); ?>
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
