<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$request     = $app->getRequest();
$contentOnly = $request->get('contentOnly', false) || $view['slots']->get('contentOnly', false) || !empty($contentOnly);
$modalView   = $request->get('modal', false) || $view['slots']->get('inModal', false) || !empty($modalView);

if (!$request->isXmlHttpRequest() && !$modalView):
    //load base template
    $template = ($contentOnly) ? 'slim' : 'base';
    $view->extend("MauticCoreBundle:Default:$template.html.php");
endif;
$videostyle = 'margin-right: 7%;';
$closestyle = 'padding: 8px 10px 8px 10px;margin-top:0.6%;';
if (isset($isMobile) && $isMobile) {
    $videostyle = 'margin-right: 48%;';
    $closestyle = 'padding: 8px 10px 8px 10px;margin-top:1.8%;';
}
$enableHeader     =true;
$marginforcontent = 'fixed-content';
if (!empty($tmpl)) {
    $enableHeader     = (($tmpl == 'index') ? 'hide' : '');
    $marginforcontent = (($tmpl == 'index') ? '' : 'fixed-content');
}
?>
<?php if (!$modalView): ?>
    <div class="content-body">
        <?php if ($view['slots']->get('mauticContent', '') == 'dashboard' && $showvideo): ?>
            <div id="dashboard-widgets" class="dashboard-widgets cards">
                <div class="card-flex widget" style="width:100%;" role="document">
                    <div class="card" style="height:550px;">
                        <div class="card-header">
                            <a href="javascript: void(0);" onclick="Mautic.RedirectToGivenURL('<?php echo $view['router']->path('mautic_dashboard_index', ['login' => 'CloseVideo']); ?>');" class="dont_show_again close_button" style="<?php echo $closestyle; ?>"><span><i class="fa fa-close"></i><span style="padding:4px;">Close</span></span></a>
                            <p style="padding:10px 15px;font-size:16px;">
                                <?php if (!$isMobile) {
    echo $view['translator']->trans('leadsengage.kyc.video_header');
}?>
                            </p>
                            <div class="dropdown" style="<?php echo $videostyle; ?>">
                                <!--<span class="dont_show_again">
                                <?php /*echo $view->render('MauticCoreBundle:Helper:confirm.html.php', [
                                    'message'         => $view['translator']->trans('le.video.confirm.message'),
                                    'iconClass'       => 'fa fa-eye-slash',
                                    'confirmText'     => $view['translator']->trans('leadsengage.kyc.dont_show'),
                                    'confirmAction'   => $view['router']->path('mautic_dashboard_index', ['login' => 'dont_show_again']),
                                    'btnText'         => $view['translator']->trans('leadsengage.kyc.dont_show'),
                                    'btnClass'        => 'btn btn-primary btn-send',
                                ]);
                                */?>
                            </span>-->
                                <a href="javascript: void(0);" class="btn btn-primary btn-send" style="margin-right: 10px;" onclick="Mautic.RedirectToGivenURL('<?php echo $view['router']->path('mautic_dashboard_index', ['login' => 'dont_show_again']); ?>');"<span><i class="fa fa-eye-slash"></i><span>Don't Show Again</span></span></a>
                            </div>
                        </div>
                        <br>
                        <div class="card-body" style="margin-left:12%;">
                            <iframe width="87%" height="450px"
                                    src="<?php echo $videoURL; ?>">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php echo $view->render('MauticCoreBundle:Default:pageheader.html.php', ['enableHeader' => $enableHeader]); ?>
    <?php if (!empty($campaignBlocks)): ?>
        <div class="le-header-align"><h3><?php echo $view['translator']->trans('le.campaigns.root'); ?></h3></div>
        <div style="padding-top: 15px;">
            <?php foreach ($campaignBlocks as $key => $segmentBlock): ?>
                <div class="info-box" id="leads-info-box-container" style="width: 25%;">
                <span class="info-box-icon" style="background-color:<?php echo $segmentBlock[0]; ?>;>">
                    <i class="<?php echo $segmentBlock[1]; ?>" id="icon-class-leads"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text"><?php echo $segmentBlock[2]; ?></span>
                        <span class="info-box-number"><?php echo $segmentBlock[3]; ?></span>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div id="<?php echo $marginforcontent; ?>">

    </div>
    <?php $view['slots']->output('_content'); ?>
</div>

    <?php $view['slots']->output('modal'); ?>
    <?php echo $view['security']->getAuthenticationContent(); ?>
<?php else: ?>
    <?php $view['slots']->output('_content'); ?>
<?php endif; ?>
