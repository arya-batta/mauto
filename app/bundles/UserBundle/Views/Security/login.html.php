<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

//if (!$app->getRequest()->isXmlHttpRequest()):
    //load base template
    $view->extend('MauticUserBundle:Security:base.html.php');
    $view['slots']->set('header', $view['translator']->trans('mautic.user.auth.header'));
//else:
  //  $view->extend('MauticUserBundle:Security:ajax.html.php');
//endif;
?>
<?php if (!empty($msg)): ?>
<span class="login-notifiation" ><?php echo $view['translator']->trans($msg); ?> </span>
<?php endif; ?>
<form class="form-group login-form" name="login" data-toggle="ajax" role="form" action="<?php echo $view['router']->path('le_user_logincheck') ?>" method="post">
    <div class="input-group mb-md">
        <label class="le-login-label" for="username"><?php echo $view['translator']->trans('mautic.user.auth.form.loginusername'); ?></label>
        <input  type="text" id="username" name="_username"
               class="form-control input-lg le-login-widget" value="<?php echo $view->escape($last_username) ?>" required autofocus/>
    </div>
    <div class="input-group mb-md">
        <label for="password" class="le-login-label"><?php echo $view['translator']->trans('mautic.core.password'); ?></label>
        <input type="password" id="password" name="_password"
               class="form-control input-lg le-login-widget" required/>
    </div>

    <div class="checkbox-inline custom-primary pull-left mb-md le-login-content">
        <label for="remember_me">
            <input type="checkbox" id="remember_me" name="_remember_me" />
            <span></span>
            <?php echo $view['translator']->trans('mautic.user.auth.form.rememberme'); ?>
        </label>
    </div>

    <input type="hidden" name="_csrf_token" value="<?php echo $view->escape($view['form']->csrfToken('authenticate')) ?>" />
    <div class="le-button-container">
        <button class="btn btn-lg btn-primary btn-block le-login-button waves-effect" type="submit"><?php echo $view['translator']->trans('mautic.user.auth.form.loginbtn'); ?></button>
        <a class="le-password-reset" href="<?php echo $view['router']->path('le_user_passwordreset'); ?>"><?php echo $view['translator']->trans('mautic.user.user.passwordreset.link'); ?></a>
        <a class="le-password-reset" style="float:right;" href="https://<?php echo strtolower($view['content']->getProductBrandName()); ?>.com/signup/" target="_blank" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo $view['translator']->trans('mautic.user.user.newtoanyfunnels.link.help'); ?>"><?php echo $view['translator']->trans('mautic.user.user.newtoanyfunnels.link'); ?></a>
    </div>
    <div class="le-copyright-content">
        <?php echo $view['translator']->trans('mautic.core.copyright', ['%date%' => date('Y')]); ?>
    </div>
</form>
<?php if (!empty($integrations)): ?>
<ul class="list-group">
<?php foreach ($integrations as $sso): ?>
    <a href="<?php echo $view['router']->path('le_sso_login', ['integration' => $sso->getName()]); ?>" class="list-group-item">
        <img class="pull-left mr-xs" style="height: 16px;" src="<?php echo $view['assets']->getUrl($sso->getIcon()); ?>" >
        <p class="list-group-item-text"><?php echo $view['translator']->trans('mautic.integration.sso.'.$sso->getName()); ?></p>
    </a>
<?php endforeach; ?>
</ul>
<?php endif; ?>
