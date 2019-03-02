<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

/** @var \Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables $app */
$inline = $view['menu']->render('profile');
?>
<li class="dropdown d-none d-sm-block">
    <a href="" class="dropdown-toggle profile waves-effect waves-light notification-icon-box" data-toggle="dropdown" aria-expanded="false">
        <img src="<?php echo $img = $view['lead_avatar']->getUserAvatar($app->getUser()); ?>" alt="user-img">
    </a>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" class="le_beamer">
<!--                <i class="margin-right  fa  fa-bell "></i>-->
                <span><?php echo $view['translator']->trans('le.beamer.menu.index'); ?></span>
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="<?php echo $view['translator']->trans('le.help.tutorials.menu.link'); ?> " target="_blank">
<!--                <i class="margin-right fa fa-question-circle"></i>-->
                <span><?php echo $view['translator']->trans('le.help.tutorials.menu.index'); ?></span>
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="<?php echo $view['router']->path('le_featuresandideas_index'); ?>" data-toggle="ajax">
<!--                <i class="margin-right mdi mdi-alert-decagram"></i>-->
                <span><?php echo $view['translator']->trans('le.feauturesandideas.menu.index'); ?></span>
            </a>
        </li>

        <li>
            <a class="dropdown-item" href="<?php echo $view['router']->path('le_user_account'); ?>" data-toggle="ajax">
<!--                <i class="margin-right fa fa-user fs-14"></i>-->
                <span><?php echo $view['translator']->trans('mautic.user.account.settings'); ?></span>
            </a>
        </li>

        <li>
            <a class="dropdown-item" href="<?php echo $view['router']->path('le_user_logout'); ?>">
<!--                <i class="margin-right fa fa-sign-out fs-14"></i>-->
                <span><?php echo $view['translator']->trans('mautic.user.auth.logout'); ?></span>
            </a>
        </li>

        <?php if (!empty($inline)): ?>
            <li role="separator" class="divider"></li>
            <?php echo $inline; ?>
        <?php endif; ?>
    </ul>
</li>