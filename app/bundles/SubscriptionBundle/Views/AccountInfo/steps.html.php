<?php
/**
 * Created by PhpStorm.
 * User: prabhu
 * Date: 25/4/18
 * Time: 6:12 PM.
 */
?>
<!-- step container -->
<div class="col-md-3 bg-white height-auto">
    <div class="pr-lg pl-lg pt-md pb-md">
        <!-- Nav tabs -->
        <ul class="list-group list-group-tabs" role="tablist">
            <li role="presentation" class="list-group-item <?php echo $step == 'accountinfo' ? 'in active' : ''; ?>">
                <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($actionRoute, ['objectAction' => 'edit']) ?>');" aria-controls="<?php echo $step?>" role="tab" data-toggle="tab">
                    <?php echo $view['translator']->trans('leadsengage.accountinfo.tab.accountinfo'); ?>
                </a>
            </li>
            <li role="presentation" class="list-group-item <?php echo $step == 'billinginfo' ? 'in active' : ''; ?>">
                <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($actionRoute, ['objectAction' => 'billing']) ?>');" aria-controls="<?php echo $step?>" role="tab" data-toggle="tab">
                    <?php echo $view['translator']->trans('leadsengage.accountinfo.tab.billinginfo'); ?>
                </a>
            </li>
            <li role="presentation" class="list-group-item <?php echo $step == 'cardinfo' ? 'in active' : ''; ?>">
                <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($actionRoute, ['objectAction' => 'cardinfo']) ?>');" aria-controls="<?php echo $step?>" role="tab" data-toggle="tab">
                    <?php echo $view['translator']->trans('leadsengage.accountinfo.tab.cardinfo'); ?>
                </a>
            </li>
            <li role="presentation" class="list-group-item <?php echo $step == 'paymenthistory' ? 'in active' : ''; ?>">
                <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($actionRoute, ['objectAction' => 'payment']) ?>');" aria-controls="<?php echo $step?>" role="tab" data-toggle="tab">
                    <?php echo $view['translator']->trans('leadsengage.accountinfo.tab.paymenthistory'); ?>
                </a>
            </li>
            <?php if ($planType == 'Paid'): ?>
            <li role="presentation" class="list-group-item <?php echo $step == 'cancelsubscription' ? 'in active' : ''; ?>">
                <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($actionRoute, ['objectAction' => 'cancel']) ?>');" aria-controls="<?php echo $step?>" role="tab" data-toggle="tab">
                    <?php echo $view['translator']->trans('leadsengage.accountinfo.tab.cancelsubs'); ?>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</div>
