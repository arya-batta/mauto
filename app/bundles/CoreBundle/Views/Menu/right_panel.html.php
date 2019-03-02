<?php

/** @var \Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables $app */
$userAccess         = $view['security']->isGranted('user:users:view');
$pluginAccess       = $view['security']->isGranted('plugin:plugins:manage');
$webhookAccess      = $view['security']->isGranted('webhook:webhooks:viewown');
$themeAccess        = $view['security']->isGranted('core:themes:view');
$customFieldAccess  = $view['security']->isGranted('lead:fields:full');
$configAccess       = $view['security']->isGranted('lead:leads:viewown');
$userAccess         = $view['security']->isGranted('user:roles:view');
$categoryAccess     = $view['security']->isGranted('category:categories:view');
$apiAccess          = $view['security']->isGranted('api:clients:view');
$notificationAccess = $view['security']->isGranted('email:emails:viewown');
?>
<li class="dropdown">
    <a href="#"  class="dropdown-toggle waves-effect waves-light notification-icon-box" data-toggle="dropdown" aria-expanded="false"><i style='font-size: 20px !important;line-height: 33px;' class="mdi mdi-settings"></i></a>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" href="<?php echo $view['router']->path('le_accountinfo_action', ['objectAction' => 'edit']); ?>" data-toggle="ajax">
<!--                <i class="margin-right  fa fa-address-book-o"></i>-->
                <span><?php echo $view['translator']->trans('leadsengage.subs.account.menu.index'); ?></span>
            </a>
        </li>
        <?php if ($userAccess): ?>
            <li>
                <a class="dropdown-item" href="<?php echo $view['router']->path('le_user_index'); ?>" data-toggle="ajax">
<!--                    <i class="margin-right  fa fa-users"></i>-->
                    <span><?php echo $view['translator']->trans('mautic.user.users'); ?></span>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($configAccess): ?>
            <li>
                <a class="dropdown-item" href="<?php echo $view['router']->path('le_config_action', ['objectAction' => 'edit']); ?>" data-toggle="ajax">
<!--                    <i class="margin-right  fa fa-cogs"></i>-->
                    <span><?php echo $view['translator']->trans('mautic.config.menu.index'); ?></span>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($categoryAccess): ?>
            <li>
                <a class="dropdown-item" href="<?php echo $view['router']->path('le_category_index'); ?>" data-toggle="ajax">
<!--                    <i class="margin-right  fa fa-folder"></i>-->
                    <span><?php echo $view['translator']->trans('mautic.category.menu.index'); ?></span>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($apiAccess): ?>
            <li>
                <a class="dropdown-item" onclick="window.open('<?php echo $view['translator']->trans('mautic.api.client.list.url'); ?>', '_blank');">
<!--                    <i class="margin-right  fa fa-puzzle-piece"></i>-->
                    <span><?php echo $view['translator']->trans('mautic.api.client.menu.index'); ?></span>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($webhookAccess): ?>
            <li>
                <a class="dropdown-item" href="<?php echo $view['router']->path('le_webhook_index'); ?>" data-toggle="ajax">
<!--                    <i class="margin-right  fa fa-exchange"></i>-->
                    <span><?php echo $view['translator']->trans('mautic.webhook.webhooks'); ?></span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</li>