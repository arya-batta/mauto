<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$isAdmin      = $view['security']->isAdmin();
$isCustomAdmin= $view['security']->isCustomAdmin();
?>
<div class="license-notifiation hide" id="licenseclosediv">
    <span id="license-alert-message"></span></div>

<!--<div class="navbar-nocollapse" style="height: 60px">-->
    <!-- LOGO -->

    <!-- Button mobile view to collapse sidebar menu -->
    <div class="navbar navbar-default" style="height: 60px" role="navigation">
        <div class="container-fluid" style="height: 60px">
            <ul class="list-inline menu-left mb-0">
                <li class="float-left">
                    <button class="notification-icon-box open-left waves-light waves-effect sidebar-minimizer" data-toggle="minimize"><!-- onclick="Le.changeButtonPanelStyle()"-->
                        <i class="mdi mdi-menu" style="font-size:22px !important;"></i>
                    </button>
                </li>
                <?php echo $view['actions']->render(new \Symfony\Component\HttpKernel\Controller\ControllerReference('MauticCoreBundle:Default:globalSearch')); ?>
            </ul>

            <ul class="nav-topbar navbar-right float-right list-inline">
                <li class="d-none d-sm-block">
                    <a href="#" id="btn-fullscreen" class="waves-effect waves-light notification-icon-box"><i style='font-size: 22px !important' class="mdi mdi-fullscreen"></i></a>
                </li>
                <?php echo $view['actions']->render(new \Symfony\Component\HttpKernel\Controller\ControllerReference('MauticCoreBundle:Default:notifications')); ?>
                <?php echo $view->render('MauticCoreBundle:Menu:profile.html.php'); ?>
                <?php if ($isCustomAdmin): ?>
                    <li class="d-none d-sm-block">
                        <a href="<?php echo $view['router']->generate('le_settingsmenu_action'); ?>" data-toggle="ajax"  class="dropdown-toggle waves-effect waves-light notification-icon-box"><i style='font-size: 20px !important;line-height: 33px;' class="mdi mdi-settings"></i></a>
                    </li>
                    <?php /*echo $view->render('MauticCoreBundle:Menu:right_panel.html.php');*/ ?>
                <?php endif; ?>
                <?php if ($isAdmin):?>
                    <li>
                        <a href="javascript: void(0);"  class="dropdown-toggle waves-effect waves-light notification-icon-box" data-toggle="sidebar" data-direction="rtl"><i class="mdi mdi-settings"></i></a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="nav navbar-nav navbar-right float-right " style="margin-right:7px;">
                <span id="upgrade-now" class="mailbox-read-time hide">

                </span>
                <span id="upgrade-info-trial-info" class="mailbox-read-time hide">

                </span>
            </ul>
        </div>
    </div>
<!--</div>-->
<!-- start: loading bar -->
<div class="loading-bar">
    <?php echo $view['translator']->trans('mautic.core.loading'); ?>
</div>
<!--/ end: loading bar -->