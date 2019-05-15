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

<div style="display: block;text-align: center" class="alert alert-danger hide" id="pricing-plan-alert-info" role="alert"> You're not quite ready to process the payment.
    You need to connect your Email Provider account to proceed further. <a href="<?php echo $view['router']->path('le_config_action', ['objectAction' => 'edit']); ?>">
        Click Here
    </a> to connect it.</div>
<br>
<br>
<div class="pricingplan-dashboard">
    <?php echo $view->render('MauticSubscriptionBundle:Pricing:pricing.html.php'); ?>
</div>
