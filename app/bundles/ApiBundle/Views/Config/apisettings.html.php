<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
//$view['slots']->set('leContent', 'config');
$view['slots']->set('headerTitle', $view['translator']->trans('le.config.api.settings.header'));
$fields        = $form['apiconfig']->children;

?>
<div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-10 bg-auto height-auto">
        <?php echo $view['form']->start($form); ?>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo $view['translator']->trans('le.config.tab.apiconfig.header'); ?></h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <?php echo $view['form']->row($fields['api_enabled']); ?>
                    </div>
                    <div class="col-md-6">
                        <div style="float:right;">
                            <a href="https://developer.anyfunnels.com/#introduction" class="btn btn-default integration-click-btn" target="_blank">API Documentation</a>
                        </div>
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
                    <div class="col-sm-10">
                        <div class="api_token_help" style="width:50%;float:left;">
                            <label class="control-label" id="callback_label_1">API Key</label>
                        </div>
                        <input type="text" id="api_token" class="form-control" readonly="" value="<?php echo $userApi; ?>">
                        <a style="float:left;" id="api_token_atag" onclick="Le.copytoClipboardforms('api_token');">
                            <i aria-hidden="true" class="fa fa-clipboard"></i>
                            copy to clipboard                </a>
                        <p style="float:right;" class="hide">For more information <a href="https://developer.anyfunnels.com/" style="text-decoration: underline;" target="_blank">click here</a></p>
                    </div>
                    <div class="col-sm-2">
                        <a class="btn btn-info" style="margin-top:25px;margin-left:-18px;" onclick='Le.regenerateApiKey();'>Generate</a>
                    </div>
                </div>
            </div>
        </div>
        <?php echo $view['form']->end($form); ?>
    </div>
    <div class="col-md-1"></div>
</div>
