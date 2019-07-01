<?php

/*
 * @copyright   2019 AnyFunnels Contributors. All rights reserved
 * @author      AnyFunnels
 *
 * @link        https://AnyFunnels.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('leContent', 'integrationConfig');
$header = $view['translator']->trans('le.integrations.menu.name').' - '.$details['name'];
$view['slots']->set('headerTitle', $header);
$configurl = isset($details['configurl']) ? $details['configurl'] : '';
?>

<!-- start: tab-content -->
<div class="tab-pane active bdr-w-0" id="instruction-container">
    <div class="panel panel-default bdr-t-wdh-0 mb-0 list-panel-padding">
        <div class="integration-container">
            <img style="width: auto;height: 100px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/slack.png'); ?>">
            <br><br>
            <span>Summary</span>
            <li>This integration will allow you to send a notification to your specific Slack channel.</li>
            <li>E.g Send notification to sales team channel when you get a new lead or send notification when your lead visits pricing page or when the lead opens an email.</li>
            <div class="integration-step">
                <div class="step-content">
                    <span>Step 1: (Setup & Authorize Slack Account)</span>
                    <?php if (!$details['authorization']): ?>
                    <div>
                        <li>Click Authorize, enter slack login details (for Oauth based authorization) and complete the basic authorization and channel setup.</li>
                    </div>
<!--                        <img alt="Add to Slack" height="40" width="139" src="https://platform.slack-edge.com/img/add_to_slack.png" srcset="https://platform.slack-edge.com/img/add_to_slack.png 1x, https://platform.slack-edge.com/img/add_to_slack@2x.png 2x">-->
                    <a style="margin-left:50px;" class="btn btn-default integration-click-btn" href="<?php echo $view['router']->path('le_new_integration_auth_user', ['integration' => $name]) ?>">Authorize</a>
                    <?php else: ?>
                        <div>
                            <li>Revoke the access from your slack account by visiting the following url</li>
                            <a href="<?php echo $configurl?>" style="color:blue;" target="_blank"><?php echo $configurl?></a>
                            <br>
                            <li>Once revoked, click the remove button to remove the slack integration.</li>
                            <a class="btn btn-default integration-click-btn" href="<?php echo $view['router']->path('le_integrations_account_remove', ['name' => $name]) ?>" data-toggle="ajax">Remove</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="integration-step step-padding">
                <div class="step-content">
                    <span>Step 2: (Optional - Slack Templates)</span>
                    <li>Go to Anyfunnels->Settings->Templates->Slack Messages</li>
                    <li>Create notification templates</li>
                </div>
            </div>
            <div class="integration-step step-padding">
                <div class="step-content">
                    <span>Step 3: (Setup Trigger to send slack notifications)</span>
                    <li>Set up automation workflow rules that will send slack notifications to specific channel with specific message as per selected templates.</li>
                    <img style="width: 60%;height: 230px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/slack_help1.png'); ?>">
                    <br><br>
                    <img style="width: 60%;height: 370px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/slack_help2.png'); ?>">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end: tab-content -->