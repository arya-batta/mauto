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
$view['slots']->set('leContent', 'pricingplans');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.core.prepaidplans'));
$plan2amount = $view['translator']->trans('le.pricing.plan.amount2');
$plan3amount = $view['translator']->trans('le.pricing.plan.amount3');
if ($preamount != 0) {
    $plan2amount = $plan2amount - $preamount;
    $plan3amount = $plan3amount - $preamount;
}
?>
<div class="row">
    <div class="col-sm-12">
        <p class="dashboard-header-p">
            <span style="font-size: 22px; line-height: 39px;"><strong><span class="dashboad-header"><?php echo $view['translator']->trans('le.pricing.page.header.msg'); ?></span></strong></span>
            <p class="hide" style="text-align: center;width:100%;">
                <span style="font-size:14px;"><?php echo $view['translator']->trans('le.pricing.page.header.desc'); ?></span>
            </p>
        </p>
    </div>
</div>
<div class="pricingplan-dashboard">
    <?php echo $view->render('MauticSubscriptionBundle:Pricing:pricing.html.php'); ?>
</div>
