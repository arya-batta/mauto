<div class="col-md-6 col-md-offset-3 mt-md" style="white-space: normal;">
    <div class="mautibot-image col-xs-6 text-center">
        <img class="img-responsive noresult-alert-image" src="<?php echo $view['mautibot']->getImage('openMouth'); ?>" />
    <?php if (!isset($header)) {
    $header = 'mautic.core.noresults.header';
} ?>
    <div class="noresult-alert-msg">
    <h4 style="font-size: 22px;"><?php echo $view['translator']->trans($header); ?></h4>
    <?php if (!isset($message)) {
    $message = 'mautic.core.noresults';
} ?>
    <p><?php echo $view['translator']->trans($message); ?></p>
    </div>
</div>
</div>
    
<?php /** if (isset($tip)): ?>
    <div class="well well col-md-6 col-md-offset-3">
        <div class="row">
            <div class="mautibot-image col-xs-3 text-center">
                <img class="img-responsive" style="max-height: 125px; margin-left: auto; margin-right: auto;" src="<?php echo $view['mautibot']->getImage('wave'); ?>" />
            </div>
            <div class="col-xs-9">
                <h4><i class="fa fa-quote-left"></i> <?php echo $view['translator']->trans('mautic.core.noresults.tip'); ?> <i class="fa fa-quote-right"></i></h4>
                <p class="mt-md"><?php echo $view['translator']->trans($tip); ?></p>
            </div>
        </div>
    </div>
<?php endif; */ ?>
