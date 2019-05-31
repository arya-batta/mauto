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
$url = $view['router']->url('le_integration_auth_webhook_callback', ['integration' => $name]);
?>
<!-- tabs controls -->
<ul class="nav nav-tabs pr-md pl-md">
    <li class="active">
        <a href="#instruction-container" role="tab" data-toggle="tab">
            <?php echo $view['translator']->trans('le.integrations.instruction.name') ?>
        </a>
    </li>
    <li class="">
        <a href="#mapping-container" role="tab" data-toggle="tab">
            <?php echo $view['translator']->trans('le.integrations.field.mapping.name') ?>
        </a>
    </li>
    <li class="">
        <a href="#payload-container" role="tab" data-toggle="tab">
            <?php echo $view['translator']->trans('le.integrations.payload.history.name') ?>
        </a>
    </li>
</ul>
<!--/ tabs controls -->

<!-- start: tab-content -->
<div class="tab-content pa-md">
    <div class="tab-pane active bdr-w-0" id="instruction-container">
        <div class="panel panel-default bdr-t-wdh-0 mb-0 list-panel-padding">
            <div class="integration-container">
                <img style="width: auto;height: 100px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/calendly.png'); ?>">
                <h3>Integration Instructions</h3>
                <div class="integration-step">
                    <div class="step-content">
                        <h3>Step 1:</h3>
                        <h3>Register your Calendly authentication token with AnyFunnels:</h3>
                        <div class="<?php echo $name; ?>-container <?php echo isset($details['calendlytoken']) ? 'hide' : ''?>">
                            <div class="form-group col-xs-12"  style="margin-top:10px;">
                                <label class="control-label" style="margin-left: -10px;font-weight: bold;" >Token</label>
                                <input type="text" id="<?php echo $name; ?>-token-value" value="" placeholder="Your token" class="form-control webhook-integration-url" style="width: 75%;" id="<?php echo $name; ?>-token-value">
                                <div class="help-block hide">
                                    This cannot be blank.
                                </div>
                            </div>
                            <a class="btn btn-default integration-click-btn" style="margin-top: 0px;" onclick="Le.saveTokenvalue('<?php echo $name; ?>');">Save Token</a>
                        </div>
                        <div class="<?php echo $name?>-tokenlist <?php echo isset($details['calendlytoken']) ? '' : 'hide'?>">
                            <div class="table-responsive integration-table-box">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Token</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr data-container="credentials">
                                        <td class="col-secret-key">
                                            <?php echo isset($details['calendlytoken']) ? $details['calendlytoken'] : ''?>
                                        </td>
                                        <td class="col-controls">
                                            <a class="btn btn-default" style="margin: 0px;height: 30px;padding: 5px;float:right;" onclick="Le.removeTokenvalue('<?php echo $name?>');">remove</a>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div>
                            <br>
                            <h3>How to generate your Calendly token:</h3>
                            <p>Instructions for finding your token can be <a class="integration-help-link" href="https://developer.calendly.com/docs/getting-your-authentication-token">found here</a>.</p>
                        </div>
                        <img style="height:auto;width:100%;" src="<?php echo $view['assets']->getUrl('media/images/integrations/'.$name.'.gif'); ?>">
                    </div>
                </div>
                <div class="integration-step">
                    <div class="step-content">
                        <h3>Step 2 :</h3>
                        <h3>Set up automation workflow rules:</h3>
                        <p style="line-height: 1.7;">Anytime a contact creates or cancels an event in Calendly, we will automatically add them as a Lead (if they are not already in your account) in AnyFunnels.</p>
                        <p style="line-height: 1.7;">To perform an action when a contact <code>creates or cancels an event</code>, create a <a class="integration-help-link" href="<?php echo $view['router']->path('le_campaign_index', ['page' => 1]) ?>">workflow</a> where the trigger is set to a Calendly event.</p>
                        <img style="width: auto;height: 230px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/calendly_help1.png'); ?>">
                        <br><br>
                        <p>Optionally, enter the name of the specific event name you want to trigger your automation.</p>
                        <img style="width: auto;height: 350px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/calendly_help2.png'); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade in bdr-w-0" id="mapping-container">
        <?php echo $view->render(
            'MauticPluginBundle:Integrations:field_mapping.html.php',
            ['form'=> $form]
        ); ?>

    </div>
    <div class="tab-pane fade in bdr-w-0" id="payload-container">
        <?php echo $view->render(
            'MauticPluginBundle:Integrations:payload_history.html.php',
            ['payloads'=> $payloads]
        ); ?>
    </div>
</div>
<!-- end: tab-content -->
