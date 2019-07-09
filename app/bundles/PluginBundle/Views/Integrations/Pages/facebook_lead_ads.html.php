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
$header = $view['translator']->trans('le.integrations.menu.name').' - '.$details['name'];
$view['slots']->set('headerTitle', $header);
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
                <img style="width: auto;height: 100px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/facebook_lead_ads.png'); ?>">
                <br><span>Summary</span>
                <li>
                    This integration will allow you to create lead in your AnyFunnels account when a user submits a Facebook Lead Ad.
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
                                <a class="btn btn-default integration-click-btn" href="<?php echo $view['router']->path('le_integrations_account_remove', ['name' => $name]) ?>" data-toggle='confirmation' data-message='Do you want to Remove?' data-confirm-text='Remove' data-confirm-callback='executeAction' data-cancel-text='Cancel' data-cancel-callback='dismissConfirmation'>Remove</a>
                            </div>
                            <div class="table-responsive integration-table-box" style="width:75%;">
                                <table class="table table-bordered">
                                    <thead>
                                    <th ><b>Page</b></th>
                                    <th ><b></b></th>
                                    </thead>
                                    <tbody>
                                    <?php if (sizeof($details['pages']) > 0):?>
                                        <?php foreach ($details['pages'] as $page):?>
                                            <tr>
                                                <td>
                                                    <?php echo $page[1] ?>
                                                </td>
                                                <td style="float: right;">
                                                    <?php
                                                    $action     =$page[2] ? 'unsubscribe' : 'subscribe';
                                                    $actionlabel=$page[2] ? 'UnSubscribe' : 'Subscribe';
                                                    $confirm    = $page[2] ? "data-toggle='confirmation' data-message='Do you want to UnSubscribe?' data-confirm-text='UnSubscribe' data-confirm-callback='executeAction' data-cancel-text='Cancel' data-cancel-callback='dismissConfirmation'" : "data-toggle='ajax'";
                                                    ?>
                                                    <a id="subscribe-btn" class="btn btn-default" href="<?php echo $view['router']->path('le_integrations_fb_page_subscription', ['integration' => $name, 'pageid'=>$page[0], 'action' => $action]) ?>" <?php echo $confirm; ?> onclick="Le.activateSpinner(this);"><span><i class="mr-5 fa fa-spinner fa-spin hide"></i></span><?php echo $actionlabel ?></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" style="text-align: center;">
                                                No Pages Found !
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="integration-step step-padding">
                    <div class="step-content">
                        <span>Step 2: (Optional - Field Mapping)</span>
                        <li>Check Field Mapping and map all lead fields to Anyfunnels Lead form fields.</li>
                        <li>By default we will take the name, email field alone if the field mapping is empty.</li>
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
                        <li>Set up automation workflow rules that will be triggered whenever you receive a lead from Facebook Integration.</li>
                        <img style="width: 60%;height: 230px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/facebook_lead_ads_help1.png'); ?>">
                        <br><br>
                        <img style="width: 60%;height: 350px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/facebook_lead_ads_help2.png'); ?>">
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


