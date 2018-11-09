<?php

/** @var \Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables $app */
$userAccess         = $view['security']->isGranted('user:users:view');
$pluginAccess       = $view['security']->isGranted('plugin:plugins:manage');
$webhookAccess      =$view['security']->isGranted('webhook:webhooks:viewown');
$themeAccess        =$view['security']->isGranted('core:themes:view');
$customFieldAccess  = $view['security']->isGranted('lead:fields:full');
$configAccess       =  $view['security']->isGranted('lead:leads:viewown');
$userAccess         = $view['security']->isGranted('user:roles:view');
$categoryAccess     = $view['security']->isGranted('category:categories:view');
$apiAccess          =$view['security']->isGranted('api:clients:view');
?>
<li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
        <span class="fa fa-cog fs-16 fa-border" id="account-settings"></span>
        <span class="text fw-sb ml-xs hidden-xs"><?php echo $view['translator']->trans('mautic.core.settings'); ?></span>
        <span class="caret ml-xs"></span>
    </a>
    <ul class="le-settings-dropdown-menu dropdown-menu">
        <li>
            <a href="<?php echo $view['router']->path('mautic_accountinfo_action', ['objectAction' => 'edit']); ?>" data-toggle="ajax">
                <i class="margin-right  fa fa-address-book-o"></i><span><?php echo $view['translator']->trans('leadsengage.subs.account.menu.index'); ?></span>
            </a>
        </li>
        <?php if ($userAccess): ?>
        <li>
            <a href="<?php echo $view['router']->path('mautic_user_index'); ?>" data-toggle="ajax">
                <i class="margin-right  fa fa-users"></i><span><?php echo $view['translator']->trans('mautic.user.users'); ?></span>
            </a>
        </li>
      <?php endif; ?>
      <?php if ($configAccess): ?>
        <li>
            <a href="<?php echo $view['router']->path('mautic_config_action', ['objectAction' => 'edit']); ?>" data-toggle="ajax">
                <i class="margin-right  fa fa-cogs"></i><span><?php echo $view['translator']->trans('mautic.config.menu.index'); ?></span>
            </a>
        </li>
      <?php endif; ?>
      <?php if ($categoryAccess): ?>
        <li>
            <a href="<?php echo $view['router']->path('mautic_category_index'); ?>" data-toggle="ajax">
                <i class="margin-right  fa fa-folder"></i><span><?php echo $view['translator']->trans('mautic.category.menu.index'); ?></span>
            </a>
        </li>
      <?php endif; ?>
      <?php /** if ($customFieldAccess): ?>
        <li>
            <a href="<?php echo $view['router']->path('mautic_contactfield_index'); ?>" data-toggle="ajax">
                <i class="margin-right  fa fa-list"></i><span><?php echo $view['translator']->trans('mautic.lead.field.menu.index'); ?></span>
            </a>
        </li>
      <?php endif; */ ?>
      <?php if ($apiAccess): ?>
        <li>
            <a href="<?php echo $view['router']->path('mautic_client_index'); ?>" data-toggle="ajax">
                <i class="margin-right  fa fa-puzzle-piece"></i><span><?php echo $view['translator']->trans('mautic.api.client.menu.index'); ?></span>
            </a>
        </li>
        <?php endif; ?>
        <?php if ($webhookAccess): ?>
        <li>
            <a href="<?php echo $view['router']->path('mautic_webhook_index'); ?>" data-toggle="ajax">
                <i class="margin-right  fa fa-exchange"></i><span><?php echo $view['translator']->trans('mautic.webhook.webhooks'); ?></span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</li>