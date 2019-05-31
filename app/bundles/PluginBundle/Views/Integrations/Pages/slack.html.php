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
            <h3>Integration Instructions</h3>
            <p style="line-height: 1.7;">
                Post a notification message to a specific Slack channel for a particular event (e.g., When a lead submits a form, notify your sales team in Slack channel).
            </p>
            <div class="integration-step">
                <div class="step-content">
                    <h3>Step 1:</h3>
                    <h3>Grant access to your Slack account</h3>
                    <?php if (!$details['authorization']): ?>
                    <div>
                        <p>Click below to authorize Anyfunnels to access your Slack account.</p>
                    </div>
<!--                        <img alt="Add to Slack" height="40" width="139" src="https://platform.slack-edge.com/img/add_to_slack.png" srcset="https://platform.slack-edge.com/img/add_to_slack.png 1x, https://platform.slack-edge.com/img/add_to_slack@2x.png 2x">-->
                    <a class="btn btn-default integration-click-btn" href="<?php echo $view['router']->path('le_new_integration_auth_user', ['integration' => $name]) ?>">Authorize</a>
                    <?php else: ?>
                        <div>
                            <h3>Revoke Access</h3>
                            <p>First, you should revoke the access from your slack account by accessing the below URL,</p>
                            <a href="<?php echo $configurl?>" style="color:blue;" target="_blank"><?php echo $configurl?></a>
                            <br>
                            <p>Once you revoked the access, click 'Remove' button to remove the slack acccount details from your AnyFunnels account.</p>
                            <a class="btn btn-default integration-click-btn" href="<?php echo $view['router']->path('le_integrations_account_remove', ['name' => $name]) ?>" data-toggle="ajax">Remove</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="integration-step">
                <div class="step-content">
                    <h3>Step 2 :</h3>
                    <h3>Set up automation workflow rules:</h3>
                    <p>To post a Slack message, create an action in  <a class="integration-help-link" href="<?php echo $view['router']->path('le_campaign_index', ['page' => 1]) ?>">workflow</a> and choose Slack as the provider. Choose the appropriate action.</p>
                    <img style="width: auto;height: 230px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/slack_help1.png'); ?>">
                    <br><br>
                    <p>Then select your Slack channel and message template to act.</p>
                    <img style="width: auto;height: 370px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/slack_help2.png'); ?>">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end: tab-content -->