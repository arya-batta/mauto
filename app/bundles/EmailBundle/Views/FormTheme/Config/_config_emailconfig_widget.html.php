<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$fields        = $form->children;
$fieldKeys     = array_keys($fields);
$template      = '<div class="col-md-6">{content}</div>';
$hidepanel     =$view['security']->isAdmin() ? '' : "style='display: none;'";
$isadmin       =$view['security']->isAdmin();
$hidebounceurl = '';
$hidespamurl   = 'hide';
$helpurl       = 'http://help.leadsengage.io/container/show/';
$transportname = 'amazon';

$transport          = $formConfig['parameters']['mailer_transport'];
$bouncelabel        = 'le.email.bounce.callback';
$bouncenotelabel    = 'le.email.bounce.note.callback';
$hideothernotes     = false;
if ($transport == 'le.transport.sendgrid_api') {
    $transportname = 'sendgrid_api';
    $helpurl .= $view['translator']->trans('le.email.sendgrid.helpurl.value');
} elseif ($transport == 'le.transport.amazon') {
    $bouncelabel   = 'le.email.bounce.amazon.callback';
    $hidebounceurl = '';
    $hidespamurl   = '';
    $transportname = 'amazon';
    $helpurl .= $view['translator']->trans('le.email.amazon.helpurl.value');
} elseif ($transport == 'le.transport.sparkpost') {
    $transportname = 'sparkpost';
    $helpurl .= $view['translator']->trans('le.email.sparkpost.helpurl.value');
} elseif ($transport == 'le.transport.elasticemail') {
    $transportname = 'elasticemail';
    $helpurl .= $view['translator']->trans('le.email.elasticemail.helpurl.value');
} else {
    $hidespamurl    = $hidebounceurl    = 'hide';
    $hideothernotes = true;
}
if ($formConfig['parameters']['mailer_transport_name'] == 'le.transport.vialeadsengage' && ($transport == 'le.transport.elasticemail' || $transport == 'le.transport.sendgrid_api')) {
    $hidespamurl = $hidebounceurl = 'hide';
}
$hidefield        = '<div class="col-md-6" style="display: none;">{content}</div>';
$hidesmtpsettings =  ($lastPayment != null && $lastPayment->getPlanName() == 'leplan2' && !$isadmin) ? 'style="display: block;"' : '';
?>

<?php if (count(array_intersect($fieldKeys, ['mailer_from_name', 'mailer_from_email', 'mailer_transport', 'mailer_spool_type']))): ?>
    <div class="panel panel-primary emailconfig hide">
        <div class="panel-heading emailconfig" <?php echo $hidesmtpsettings?>>
            <h3 class="panel-title"><?php echo $view['translator']->trans('le.email.config.header.mail'); ?></h3>
        </div>
        <div class="panel-body" <?php echo $hidesmtpsettings?>>

            <div class="row" >
                <?php echo $view['form']->rowIfExists($fields, 'mailer_return_path', $isadmin ? $template : $hidefield); ?>
                <?php echo $view['form']->rowIfExists($fields, 'mailer_is_owner', $isadmin ? $template : $hidefield); ?>
            </div>

            <?php if (isset($fields['mailer_from_name']) || isset($fields['mailer_from_email'])): ?>
                <hr class="text-muted" <?php echo $hidepanel ?> />
            <?php endif; ?>

            <?php if (isset($fields['mailer_transport'])): ?>
                <div class="row">
                    <div class="col-sm-6">
                        <?php if ($isadmin): ?>
                            <?php echo $view['form']->row($fields['mailer_transport']); ?>
                        <?php else: ?>
                            <?php echo $view['form']->row($fields['mailer_transport_name']); ?>
                        <?php endif; ?>
                    </div>
                    <div data-hide-on='{"config_emailconfig_mailer_transport":["sendmail","mail"]}'>
                        <div class="col-md-3">
                            <label style="margin-left: 16px;" class="control-label"><?php echo $view['translator']->trans('le.email.config.email.status.label'); ?></label>
                        <div class="col-md-12">
                            <?php echo $view['form']->widget($fields['email_status'], ['attr' => ['tabindex' => '-1']]); ?>
                        </div> </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$isadmin): ?>
            <div class="row hide">
                <?php echo $view['form']->row($fields['mailer_transport']); ?>
            </div>
            <?php endif; ?>

            <div class="row">
                <?php echo $view['form']->rowIfExists($fields, 'mailer_amazon_region', $template); ?>
            </div>

            <div class="row">
                <?php echo $view['form']->rowIfExists($fields, 'mailer_host', $template); ?>
                <?php echo $view['form']->rowIfExists($fields, 'mailer_port', $template); ?>
            </div>

            <div class="row">
                <?php echo $view['form']->rowIfExists($fields, 'mailer_encryption', $template); ?>
                <?php echo $view['form']->rowIfExists($fields, 'mailer_auth_mode', $template); ?>
            </div>

            <div class="row">
                <?php echo $view['form']->rowIfExists($fields, 'mailer_user', $template); ?>
                <?php echo $view['form']->rowIfExists($fields, 'mailer_password', $template); ?>
                <?php echo $view['form']->rowIfExists($fields, 'mailer_api_key', $template); ?>
            </div>
            <div class="row">
                <div class="col-sm-3">
                    <?php echo $view->render('MauticEmailBundle:Email:recipient_list.html.php', [
                        'EmailList'  => $EmailList,
                    ]); ?>
                </div>
                <div class="col-sm-3 hide">
                    <?php echo $view['form']->widget($fields['mailer_test_connection_button']); ?>
                    <?php /** echo $view['form']->widget($fields['mailer_test_send_button']); */ ?>
                </div>
            </div>
            <div class="row">
                <div width="150px;" id="mailerTestButtonContainer" >
                    <span class="fa fa-spinner fa-spin hide" ></span>
                    <div class="col-md-6 help-block" ></div>
                </div>
            </div>

            <?php if ($isadmin): ?>
            <div class="row transportcallback <?php echo $hidebounceurl; ?>">
                <div class="col-sm-12">
                    <a target="_blank" id = "notificationHelpURL"href="<?php echo $helpurl; ?>"><?php echo $view['translator']->trans('le.email.callback.setup.help'); ?></a>
                </div>
            </div>
            <?php endif; ?>
            <br>
            <div class="row transportcallback <?php echo $hidebounceurl; ?>">
                <div class="col-sm-12">
                <div class="transportcallback_help" style="width:50%;float:left;">
                <label class="control-label" id="callback_label_1"><?php echo $view['translator']->trans($bouncelabel); ?></label>
                </div>
                <!--<div class="transportcallback_help" style="width:50%;float:right;text-align:right;">
                    <a href="https://leadsengage.com"><?php /*echo $view['translator']->trans('le.email.amazon.bounce.help'); */?></a>
                </div>-->
                <input type="text" id="transportcallback" class="form-control" readonly value="<?php echo $view['router']->url('le_mailer_transport_callback', ['transport' => $transportname]); ?>" />
                <a id="transportcallback_atag" onclick="Le.copytoClipboardforms('transportcallback');">
                    <i aria-hidden="true" class="fa fa-clipboard"></i>
                    <?php echo $view['translator']->trans(
                        'leadsengage.subs.clicktocopy'
                    ); ?>
                </a>
                </div>
            </div>
            <br>

            <div class="row transportcallback_spam <?php echo $hidespamurl; ?>">
                <div class="col-sm-12">
                    <div class="transportcallback_help" style="width:40%;float:left;">
                        <label class="control-label" id="callback_label_2"><?php echo $view['translator']->trans('le.email.spam.callback'); ?></label>
                    </div>
                    <!--<div class="transportcallback_help" style="width:60%;float:right;text-align:right;">
                        <a href="https://leadsengage.com"><?php /*echo $view['translator']->trans('le.email.amazon.spam.help'); */?></a>
                    </div>-->
                    <input type="text" id="transportcallback_spam" class="form-control" readonly value="<?php echo $view['router']->url('le_mailer_transport_callback', ['transport' => 'amazon']); ?>" />
                    <a id="transportcallback_spam_atag" onclick="Le.copytoClipboardforms('transportcallback_spam');">
                        <i aria-hidden="true" class="fa fa-clipboard"></i>
                        <?php echo $view['translator']->trans(
                            'leadsengage.subs.clicktocopy'
                        ); ?>
                    </a>
                </div>
            </div>
            <br>
            <div class="transportcallback_help" style="width:50%;">
                <label class="control-label" id="callback_label_1"><?php echo $view['translator']->trans($bouncenotelabel); ?></label>
            </div>
            <div class="alert alert-info le-alert-info <?php echo !$hideothernotes ? '' : 'hide'?>" id="known-providers">
                <p><?php echo $view['translator']->trans('le.email.spam.bounce.description'); ?></p>
                <p><?php echo $view['translator']->trans('le.email.complains.description'); ?></p>
            </div>
            <div class="alert alert-info le-alert-info <?php echo $hideothernotes ? '' : 'hide' ?>" id="other-providers">
                <p><?php echo $view['translator']->trans('le.email.other.smtp.note'); ?></p>
            </div>
            <br>
        </div>
        <div class="panel panel-primary hide">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo $view['translator']->trans('mautic.config.tab.awsverified_emails'); ?></h3>
            </div>
            <div class="panel-body">
                <?php echo $view->render('MauticEmailBundle:Email:aws_verified_emails.html.php', [
                    'verifiedEmails'  => $verifiedEmails,
                ]); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="panel panel-primary emailconfig">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $view['translator']->trans('le.email.config.basic.mail.settings'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="alert alert-info le-alert-info" id="form-action-placeholder">
            <p><?php echo $view['translator']->trans('le.email.config.basic.mail.settings.help'); ?></p>
        </div>
        <?php if (isset($fields['mailer_transport'])): ?>
            <div class="row">
                <?php echo $view['form']->rowIfExists($fields, 'mailer_mailjet_sandbox', $template); ?>
                <?php echo $view['form']->rowIfExists($fields, 'mailer_mailjet_sandbox_default_mail', $template); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($fields['mailer_transport'])): ?>
            <hr class="text-muted" <?php echo $hidepanel ?> />
        <?php endif; ?>

        <div class="row" <?php echo $hidepanel ?>>
            <?php echo $view['form']->rowIfExists($fields, 'mailer_spool_type', $template); ?>
            <?php echo $view['form']->rowIfExists($fields, 'mailer_spool_path', $template); ?>
        </div>

        <div class="row" <?php echo $hidepanel ?>>
            <?php echo $view['form']->rowIfExists($fields, 'mailer_spool_msg_limit', $template); ?>
            <?php echo $view['form']->rowIfExists($fields, 'mailer_spool_time_limit', $template); ?>
        </div>

        <div class="row" <?php echo $hidepanel ?>>
            <?php echo $view['form']->rowIfExists($fields, 'mailer_spool_recover_timeout', $template); ?>
            <?php echo $view['form']->rowIfExists($fields, 'mailer_spool_clear_timeout', $template); ?>
        </div>
        <div class="row" style="margin:0;">
            <?php echo $view['form']->rowIfExists($fields, 'mailer_from_name'); ?>
        </div>
        <div class="row" style="margin:0;">
            <?php echo $view['form']->rowIfExists($fields, 'mailer_from_email'); ?>
        </div>
        <div class="row" style="margin:0;">
            <?php echo $view['form']->rowIfExists($fields, 'postal_address'); ?>
        </div>
        <div class="alert alert-info le-alert-info" id="form-action-placeholder">
            <p><?php echo $view['translator']->trans('le.email.config.basic.mail.settings.footer.help'); ?></p>
        </div>
        <div class="row" style="margin:0;">
            <?php echo $view['form']->rowIfExists($fields, 'footer_text'); ?>
        </div>
    </div>
</div>
<div class="panel panel-primary hide" style="margin-bottom: 0px">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $view['translator']->trans('mautic.config.tab.frequency_rules'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <?php echo $view['form']->row($fields['email_frequency_number']); ?>
            </div>
            <div class="col-md-12">
                <?php echo $view['form']->row($fields['email_frequency_time']); ?>
            </div>
        </div>
    </div>
</div>
<?php if (isset($fields['monitored_email'])): ?>
    <div class="panel panel-primary" <?php echo $hidepanel ?>>
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo $view['translator']->trans('le.email.config.header.monitored_email'); ?></h3>
        </div>
        <div class="panel-body">
            <?php if (function_exists('imap_open')): ?>
                <?php echo $view['form']->widget($form['monitored_email']); ?>
            <?php else: ?>
                <div class="alert alert-info"><?php echo $view['translator']->trans('le.email.imap_extension_missing'); ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="panel panel-primary" <?php echo $hidepanel ?> >
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $view['translator']->trans('le.email.config.header.message'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <?php echo $view['form']->rowIfExists($fields, 'webview_text', $template); ?>
            <?php echo $view['form']->rowIfExists($fields, 'default_signature_text', $template); ?>
        </div>
        <div class="row">
            <?php echo $view['form']->rowIfExists($fields, 'mailer_append_tracking_pixel', $template); ?>
            <?php echo $view['form']->rowIfExists($fields, 'mailer_convert_embed_images', $template); ?>
            <?php echo $view['form']->rowIfExists($fields, 'disable_trackable_urls', $template); ?>
        </div>
    </div>
</div>
<div class="panel panel-primary" <?php echo $hidepanel ?>>
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $view['translator']->trans('le.email.config.header.unsubscribe'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <?php echo $view['form']->rowIfExists($fields, 'unsubscribe_message', $template); ?>
            <?php echo $view['form']->rowIfExists($fields, 'resubscribe_message', $template); ?>
        </div>
        <div class="row">
            <?php echo $view['form']->rowIfExists($fields, 'unsubscribe_message', $template); ?>
            <?php echo $view['form']->rowIfExists($fields, 'resubscribe_message', $template); ?>
        </div>
        <div class="row">
            <?php echo $view['form']->rowIfExists($fields, 'show_contact_preferences', $template); ?>
            <?php echo $view['form']->rowIfExists($fields, 'show_contact_segments', $template); ?>
        </div>
        <div class="row">
            <?php echo $view['form']->rowIfExists($fields, 'show_contact_frequency', $template); ?>
            <?php echo $view['form']->rowIfExists($fields, 'show_contact_pause_dates', $template); ?>
        </div>
        <div class="row">
            <?php echo $view['form']->rowIfExists($fields, 'show_contact_categories', $template); ?>
            <?php echo $view['form']->rowIfExists($fields, 'show_contact_preferred_channels', $template); ?>
        </div>
    </div>
</div>

