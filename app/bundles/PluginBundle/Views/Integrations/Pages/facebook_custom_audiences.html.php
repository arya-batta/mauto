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
$view['slots']->set('leContent', 'integrationConfig');
$header =$view['translator']->trans('le.integrations.menu.name').' - '.$details['name'];
$view['slots']->set('headerTitle', $header);
?>
<div class="panel panel-default bdr-t-wdh-0 mb-0 list-panel-padding">
    <div class="integration-container">
        <img style="width: auto;height: 100px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/facebook_custom_audiences.png'); ?>">
        <br>
        <span>Summary</span>
        <li>
            This integration will allow you to send selected or segment of leads from your AnyFunnels account to facebook custom audience
        </li>
        <div class="integration-step">
            <div class="step-content">
                <span>Step 1: (Grant Access & Connect to Facebook)</span>
                <?php if (!$details['authorization']): ?>
                    <div>
                        <li>Allow AnyFunnels to access your Facebook account</li>
                        <li>Current Status: <b>Not Connected</b></li>
                        <a class="btn btn-default integration-click-btn" href="<?php echo $view['router']->path('le_new_integration_auth_user', ['integration' => $name]) ?>">Authorize</a>
                    </div>
                <?php else: ?>
                    <div>
                        <li>Current Status: <b>Connected</b></li>
                        <li>Connected (Authorized as <strong><?php echo $details['accountname'] ?></strong>)</li>
                        <a class="btn btn-default integration-click-btn" href="<?php echo $view['router']->path('le_integrations_account_remove', ['name' => $name]) ?>" data-toggle='confirmation' data-message='Do you want to Remove?' data-confirm-text='Remove' data-cancel-text='Cancel' data-cancel-callback='dismissConfirmation'>Remove</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="integration-step step-padding">
            <div class="step-content">
                <span>Step 2: (Setup Trigger to send lead data to facebook custom audience list)</span>
                <li style="line-height: 1.7;">Set up automation workflow rules that will send lead data to specific list of facebook custom audience.</li>
                <li style="line-height: 1.7;">Please note that facebook takes few minutes to a few hours to complete the sync operations at their end.</li>
                <br>
                <img style="width: 60%;height: 230px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/facebook_custom_audiences_help1.png'); ?>">
                <br><br>
                <img style="width: 60%;height: 370px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/facebook_custom_audiences_help2.png'); ?>">
            </div>
        </div>
    </div>
</div>


