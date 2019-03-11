<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$hidepanel  =$view['security']->isAdmin() ? '' : "style='display: none;'";
$isAdmin    =$view['security']->isAdmin();
?>

<div class="panel panel-primary trackingconfig">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $view['translator']->trans('le.config.tab.pagetracking'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="form-group">
            <p><?php echo $view['translator']->trans('le.config.tab.pagetracking.info'); ?></p>
            <pre id="script_preTag" style="display:none;">&lt;script&gt;
    (function(w,d,t,u,n,a,m){w['TrackingObject']=n;
        w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),
        m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)
    })(window,document,'script','<?php echo $view['router']->url('le_js'); ?>','mt');

    mt('send', 'pageview');
&lt;/script&gt;</pre>

            <textarea id="copy_to_clipboard" style="height:180px;" class="form-control" readonly>&lt;script&gt;
    (function(w,d,t,u,n,a,m){w['TrackingObject']=n;
        w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),
        m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)
    })(window,document,'script','<?php echo $view['router']->url('le_js'); ?>','mt');

    mt('send', 'pageview');
&lt;/script&gt;</textarea>
            <a id="copy_to_clipboard_atag" onclick="Le.copytoClipboardforms('copy_to_clipboard');">
                <i aria-hidden="true" class="fa fa-clipboard"></i>
                <?php echo $view['translator']->trans(
                    'leadsengage.subs.clicktocopy'
                ); ?>
            </a>

        </div>
        <div class="row">
            <?php foreach ($form->children as $name => $f): ?>
                <?php if (in_array($name, ['track_contact_by_ip', 'track_by_tracking_url', 'track_by_fingerprint'])) {
                    ?>
                    <div class="col-md-6 <?php echo $isAdmin ? '' : 'hide'; ?>">
                        <?php echo $view['form']->row($f); ?>
                    </div>
                    <?php
                }
            endforeach; ?>
        </div>

    </div>
    <br>
    <div class="panel panel-primary" style="margin-bottom: 0px;">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo $view['translator']->trans('le.send.instructions.to.developer'); ?></h3>
        </div>
        <div class="panel-body">
            <div class="row emailinstructions" style="margin:0;">
                <?php echo $view['form']->row($form['emailInstructionsto']); ?>
            </div>
            <div class="row" style="margin:0;">
                <?php echo $view['form']->row($form['emailAdditionainfo']); ?>
            </div>
            <div class="row" style="margin:0;">
                <div id="mailerTestButtonContainer">
                    <div class="button_container">
                        <?php echo $view['form']->widget($form['send_tracking_instruction']); ?>
                        <span class="fa fa-spinner fa-spin hide"></span>
                    </div>
                    <div class="col-md-9 help-block"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-heading <?php echo $isAdmin ? '' : 'hide' ?>" >
        <h3 class="panel-title"><?php echo $view['translator']->trans('le.config.tab.tracking.facebook.pixel'); ?></h3>

    </div>
    <div class="panel-body <?php echo $isAdmin ? '' : 'hide' ?>" >
        <?php echo $view['form']->row($form['facebook_pixel_id']); ?>
        <div class="row">
            <?php foreach ($form->children as $name => $f): ?>
                <?php if (in_array($name, ['facebook_pixel_trackingpage_enabled', 'facebook_pixel_landingpage_enabled'])) {
                ?>
                    <div class="col-md-6">
                        <?php echo $view['form']->row($f); ?>
                    </div>
                    <?php
            }
            endforeach; ?>
        </div>
    </div>

    <div class="panel-heading <?php echo $isAdmin ? '' : 'hide' ?>" >
        <h3 class="panel-title"><?php echo $view['translator']->trans('le.config.tab.tracking.google.analytics'); ?></h3>

    </div>
    <div class="panel-body <?php echo $isAdmin ? '' : 'hide' ?>">
        <?php echo $view['form']->row($form['google_analytics_id']); ?>
        <div class="row">
            <?php foreach ($form->children as $name => $f): ?>
                <?php if (in_array($name, ['google_analytics_trackingpage_enabled', 'google_analytics_landingpage_enabled'])) {
                ?>
                    <div class="col-md-6">
                        <?php echo $view['form']->row($f); ?>
                    </div>
                    <?php
            }
            endforeach; ?>
        </div>
    </div>
</div>