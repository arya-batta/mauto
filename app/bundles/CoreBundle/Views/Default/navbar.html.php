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
<!-- start: loading bar -->
<div class="loading-bar">
    <?php echo $view['translator']->trans('mautic.core.loading'); ?>
</div>
<!--/ end: loading bar -->

<!-- start: navbar nocollapse -->
<div class="navbar-nocollapse">
    <!-- start: left nav -->
    <ul class="nav navbar-nav navbar-left">
        <a href="javascript:void(0)" data-toggle="minimize" class="sidebar-minimizer" onclick="Le.changeButtonPanelStyle();">
            <span class="fa fa-bars w3-xxlarge le-sidebar-minimizer"></span>
        </a>
    </ul>
    <ul class="nav navbar-nav navbar-left">
        <li class="hidden-xs" data-toggle="tooltip" data-placement="right" title="Minimize Sidebar">
            <a href="javascript:void(0)" data-toggle="minimize" class="sidebar-minimizer"><span class="arrow fs-14"></span></a>
        </li>
        <li class="visible-xs">
            <a href="javascript: void(0);" data-toggle="sidebar" data-direction="ltr">
                <i class="fa fa-navicon fs-16"></i>
            </a>
        </li>
        <?php echo $view['actions']->render(new \Symfony\Component\HttpKernel\Controller\ControllerReference('MauticCoreBundle:Default:notifications')); ?>
        <?php echo $view['actions']->render(new \Symfony\Component\HttpKernel\Controller\ControllerReference('MauticCoreBundle:Default:globalSearch')); ?>
    </ul>
    <!--/ end: left nav -->

    <!-- start: right nav -->
    <ul class="nav navbar-nav navbar-right" style="margin-left: -25px;">
        <?php if ($isCustomAdmin): ?>
        <?php echo $view->render('MauticCoreBundle:Menu:right_panel.html.php'); ?>
        <?php endif; ?>
    </ul>
    <ul class="nav navbar-nav navbar-right">
        <?php echo $view->render('MauticCoreBundle:Menu:profile.html.php'); ?>
    <!-- Hided Right Panel -->
      <?php if ($isAdmin):?>
            <li>
                <a href="javascript: void(0);" data-toggle="sidebar" data-direction="rtl">
                    <i class="fa fa-cog fs-16"></i>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <ul class="nav navbar-nav navbar-right">
        <span id="upgrade-now" class="mailbox-read-time hide">

        </span>
        <span id="upgrade-info-trial-info" class="mailbox-read-time hide">

        </span>
    </ul>
    <!-- start: right nav -->
   <!-- <ul class="nav navbar-nav navbar-right">
        <?php /*echo $view->render('MauticCoreBundle:Menu:support.html.php'); */?>
    </ul>-->
    <div class="navbar-toolbar pull-right mt-15 mr-10">
    <?php
    echo $view['buttons']->reset($app->getRequest(), \Mautic\CoreBundle\Templating\Helper\ButtonHelper::LOCATION_NAVBAR)
        ->renderButtons();
    ?>
    </div>


    <!--/ end: right nav -->
</div>
<!--/ end: navbar nocollapse -->