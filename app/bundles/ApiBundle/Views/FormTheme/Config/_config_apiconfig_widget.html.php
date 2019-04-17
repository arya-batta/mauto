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
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $view['translator']->trans('le.config.tab.apiconfig'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6">
                <?php echo $view['form']->row($fields['api_enabled']); ?>
            </div>
        </div>
        <div class="row hide">
            <div class="col-md-6">
                <?php echo $view['form']->row($fields['api_enable_basic_auth']); ?>
            </div>
        </div>
        <div class="row hide">
            <div class="col-md-6">
                <?php echo $view['form']->row($fields['api_oauth2_access_token_lifetime']); ?>
            </div>
        </div>
        <div class="row hide">
            <div class="col-md-6">
                <?php echo $view['form']->row($fields['api_oauth2_refresh_token_lifetime']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="api_token_help" style="width:50%;float:left;">
                    <label class="control-label" id="callback_label_1">Api Key</label>
                </div>
                <input type="text" id="api_token" class="form-control" readonly="" value="<?php echo $userApi; ?>">
                <a style="float:left;" id="api_token_atag" onclick="Le.copytoClipboardforms('api_token');">
                    <i aria-hidden="true" class="fa fa-clipboard"></i>
                    copy to clipboard                </a>
                <p style="float:right;">For more information <a href="https://developer.anyfunnels.com/" style="text-decoration: underline;" target="_blank">click here</a></p>
            </div>
            <div class="col-sm-6 hide">
                <a class="btn btn-info" style="margin-top:25px;margin-left:-18px;" onclick='Le.regenerateApikey("<?php echo 'kaviarasan@dacamsys.com'?>");'>Generate</a>
            </div>
        </div>
    </div>
</div>