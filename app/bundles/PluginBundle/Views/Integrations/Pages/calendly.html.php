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
                <br>
                <span>Summary</span>
                <li>This integration will allow you to start an action in your AnyFunnels account when a user creates an event in Calendy page.</li>
                <div class="integration-step">
                    <div class="step-content">
                        <span>Step 1: (Setup & Configure Calendy Token)</span>
                        <li>Go to your Calendy account, Go to Integration and copy the Calendy token code as per the video below</li>
                        <li>Paste the token key and register the calendy authentication token with AnyFunnels</li>
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
                        <div class="hide">
                            <br>
                            <span>How to generate your Calendly token:</span>
                            <p>Instructions for finding your token can be <a class="integration-help-link" href="https://developer.calendly.com/docs/getting-your-authentication-token">found here</a>.</p>
                        </div>

                    </div>
                </div>
                <div class="integration-step step-padding">
                    <div class="step-content">
                        <span>Step 2: (Optional - Field Mapping)</span>
                        <li>Check Field Mapping and map all lead fields to Anyfunnels Lead form fields.</li>
                        <li>By default we will take the email field alone if the field mapping is empty</li>
                        <br>
                        <img style="height:auto;width:60%;" src="<?php echo $view['assets']->getUrl('media/images/integrations/'.$name.'.gif'); ?>">
                    </div>
                </div>
                <div class="integration-step step-padding">
                    <div class="step-content">
                        <span>Step 3: (Optional - Test Sample Data Transfer)</span>
                        <li>Check Transaction Logs to make sure you receive the data from integration for your first sample test data.</li>
                    </div>
                </div>
                <div class="integration-step step-padding">
                    <div class="step-content">
                        <span>Step 4: (Configure Trigger for handling Incoming Data)</span>
                        <li>Set up automation workflow rules that will be triggered whenever you create or cancel an event in your calendy account.</li>
                        <li>Please note that a new lead is created when an event is triggered.</li>
                        <li>The lead creation is skipped if the lead is already present in the AnyFunnels account.</li>
                        <br>
                        <img style="width: 60%;height: 230px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/calendly_help1.png'); ?>">
                        <br><br>
                        <img style="width: 60%;height: 350px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/calendly_help2.png'); ?>">
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
            [
                'payloads' => $payloads,
                'name'     => $name,
            ]
        ); ?>
    </div>
</div>
<!-- end: tab-content -->
