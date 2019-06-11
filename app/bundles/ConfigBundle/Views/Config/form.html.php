<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
//if ($tmpl == 'index') {
    $view->extend('MauticCoreBundle:Default:content.html.php');
//}
$view['slots']->set('leContent', 'config');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.config.header.index'));

$configKeys = array_keys($form->children);
?>

<!-- start: box layout -->
<div class="box-layout">
    <!-- step container -->
    <div class="col-md-3 bg-white height-auto">
        <div class="pr-lg pl-lg pt-md pb-md">
            <?php if (!$isWritable): ?>
            <div class="alert alert-danger"><?php echo $view['translator']->trans('mautic.config.notwritable'); ?></div>
            <?php endif; ?>
            <!-- Nav tabs -->
            <ul class="list-group list-group-tabs" role="tablist">
                <?php if (!$isTrialAccount): ?>
                    <li role="presentation" class="list-group-item <?php echo $selectTab === 'sendingdomain_config' ? 'in active' : ''; ?>">
                        <a href="#sendingdomain_config" aria-controls="sendingdomain_config" role="tab" data-toggle="tab" class="steps">
                            <?php echo $view['translator']->trans('le.config.tab.'.'sendingdomain'); ?>
                        </a>
                    </li>
                    <!--<li role="presentation" class="list-group-item <?php echo $selectTab === 'senderreputation_config' ? 'in active' : ''; ?>">
                        <a href="#senderreputation_config" aria-controls="senderreputation_config" role="tab" data-toggle="tab" class="steps">
                            <?php echo $view['translator']->trans('le.config.tab.'.'senderreputation'); ?>
                        </a>
                    </li>-->
                <?php endif; ?>
            <?php foreach ($configKeys as $i => $key) : ?>
                <?php if (!isset($formConfigs[$key]) || !count($form[$key]->children)) {
    continue;
} ?>
                <li role="presentation" class="list-group-item <?php echo $selectTab === $key ? 'in active' : ''; ?>">
                    <?php $containsErrors = ($view['form']->containsErrors($form[$key])) ? ' text-danger' : ''; ?>
                    <a href="#<?php echo $key; ?>" aria-controls="<?php echo $key; ?>" role="tab" data-toggle="tab" class="steps<?php echo $containsErrors; ?>">
                        <?php echo $view['translator']->trans('le.config.tab.'.$key); ?>
                        <?php if ($containsErrors): ?>
                            <i class="fa fa-warning"></i>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- container -->
    <div class="col-md-9 bg-auto height-auto">
        <?php echo $view['form']->start($form); ?>
        <!-- Tab panes -->
        <div class="tab-content" style="border:0px;box-shadow: unset;">
            <?php foreach ($configKeys as $i => $key) : ?>
            <?php
                if (!isset($formConfigs[$key])) {
                    continue;
                }
                if (!count($form[$key]->children)):
                    $form[$key]->setRendered();
                    continue;
                endif;
            ?>
            <div role="tabpanel" class="tab-pane fade <?php echo $selectTab === $key ? 'in active' : ''; ?> bdr-w-0" id="<?php echo $key; ?>">
                <div class="pt-md pr-md pl-md pb-md">
                    <?php echo $view['form']->widget($form[$key], ['formConfig' => $formConfigs[$key], 'verifiedEmails' => $verifiedEmails, 'lastPayment' => $lastPayment, 'EmailList' => $EmailList, 'userApi' => $userapi]); ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (!$isTrialAccount): ?>
                <div role="tabpanel" class="tab-pane fade <?php echo $selectTab === 'sendingdomain_config' ? 'in active' : ''; ?> bdr-w-0" id="sendingdomain_config">
                    <div class="pt-md pr-md pl-md pb-md">
                        <?php echo $view->render(
                            'MauticEmailBundle:Config:sendingdomain.html.php',
                            ['sendingdomains' => $sendingdomains]
                        );

                        ?>
                    </div>
                </div>
                <!--<div role="tabpanel" class="tab-pane fade <?php echo $selectTab === 'senderreputation_config' ? 'in active' : ''; ?> bdr-w-0" id="senderreputation_config">
                    <div class="pt-md pr-md pl-md pb-md">
                        <?php echo $view->render(
                            'MauticEmailBundle:Config:senderreputation.html.php',
                            ['emailreputations' => $emailreputations]
                        );

                        ?>
                    </div>
                </div>-->
            <?php endif; ?>
        </div>
        <?php echo $view['form']->end($form); ?>
    </div>
</div>