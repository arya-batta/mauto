<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<!DOCTYPE html>
<html>
<?php echo $view->render('MauticCoreBundle:Default:head.html.php'); ?>
<body class="header-fixed">
<?php echo $view->render('MauticCoreBundle:Default:googletagmanager.html.php'); ?>
<!-- start: app-wrapper -->
<section id="app-wrapper">
    <?php $view['assets']->outputScripts('bodyOpen'); ?>

    <!-- start: app-sidebar(left) -->
    <aside class="app-sidebar sidebar-left">
        <?php echo $view->render('MauticCoreBundle:LeftPanel:index.html.php'); ?>
    </aside>
    <!--/ end: app-sidebar(left) -->

    <!-- start: app-sidebar(right) -->
    <aside class="app-sidebar sidebar-right">
        <?php echo $view->render('MauticCoreBundle:RightPanel:index.html.php'); ?>
    </aside>
    <!--/ end: app-sidebar(right) -->

    <!-- start: app-header -->
   <header id="app-header" class="navbar">
        <?php echo $view->render('MauticCoreBundle:Default:navbar.html.php'); ?>
        <?php echo $view->render('MauticCoreBundle:Notification:flashes.html.php'); ?>
    </header>
    <!--/ end: app-header -->
    <?php $anitSpamUrl    ='https://leadsengage.com/anti-spam-policy/'; ?>
    <?php $privacyUrl     ='https://leadsengage.com/privacy-policy/'; ?>
    <?php $termsAndCondUrl='https://leadsengage.com/terms-of-service/'; ?>
    <!-- start: app-footer(need to put on top of #app-content)-->
    <footer id="app-footer">
        <div class="container-fluid">
            <div class="col-lg-12">
                <div class="pull-left hidden-xs">
                    <?php echo $view['translator']->trans('mautic.core.copyright.anti.spam', ['%anitSpamUrl%' => $anitSpamUrl, '%privacyUrl%' => $privacyUrl, '%termsAndCondUrl%' => $termsAndCondUrl]); ?>
                </div>
                <div class="pull-right">
                    <?php echo $view['translator']->trans('mautic.core.copyright', ['%date%' => date('Y')]); ?></div><?php
                /** @var \Mautic\CoreBundle\Templating\Helper\VersionHelper $version */
                $version = $view['version'];
                $version->getVersion(); ?>
            </div>
        </div>
    </footer>
    <!--/ end: app-content -->

    <!-- start: app-content -->
    <input type="text" style="height:1px;width:1px;border:0px solid;" tabindex="-1" id="builder_url_text" value="" />
    <section id="app-content" style="margin-bottom: 5%;">
        <?php $view['slots']->output('_content'); ?>
    </section>
    <!--/ end: app-content -->

</section>
<!--/ end: app-wrapper -->

<script>

    Le.onPageLoad('body');
    <?php if ($app->getEnvironment() === 'dev'): ?>
    mQuery( document ).ajaxComplete(function(event, XMLHttpRequest, ajaxOption){
        if(XMLHttpRequest.responseJSON && typeof XMLHttpRequest.responseJSON.ignore_wdt == 'undefined' && XMLHttpRequest.getResponseHeader('x-debug-token')) {
            if (mQuery('[class*="sf-tool"]').length) {
                mQuery('[class*="sf-tool"]').remove();
            }

            mQuery.get(leBaseUrl + '_wdt/'+XMLHttpRequest.getResponseHeader('x-debug-token'),function(data){
                mQuery('body').append('<div class="sf-toolbar-reload">'+data+'</div>');
            });
        }
    });
    <?php endif; ?>
</script>
<?php $view['assets']->outputScripts('bodyClose'); ?>
<?php echo $view->render('MauticCoreBundle:Helper:modal.html.php', [
    'id'            => 'leSharedModal',
    'footerButtons' => true,
]); ?>
</body>
</html>
