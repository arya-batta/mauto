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
                <img style="width: auto;height: 100px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/instapage.png'); ?>">
                <br>
                <span>Summary</span>
                <li>This integration will allow you to create lead in your AnyFunnels account when a user submits a lead in Instapage account.</li>
                <div class="integration-step">
                    <div class="step-content">
                        <span>Set up a webhook integration in Instapage using the following URL</span>
                        <input type="text" id="instapagecallback" value="<?php echo $url; ?>" readonly="readonly" class="form-control webhook-integration-url" style="margin-top:10px;width:60%;margin-left:50px;">
                        <a style="margin-left:50px;" id="instapagecallback_atag" onclick="Le.copytoClipboardforms('instapagecallback');">
                            <i aria-hidden="true" class="fa fa-clipboard"></i>
                            <?php echo $view['translator']->trans(
                                'leadsengage.subs.clicktocopy'
                            ); ?>
                        </a>
                        <div>
                            <br>
                            <li>Copy the callback url and you need to paste this inside your Instapage account webhooks section.</li>
                            <li>Go to your Instapage account, Go to settings->Integration and follow the instructions as per the video below</li>
                        </div>
                        <br>
                        <img style="height:auto;width:60%;" src="<?php echo $view['assets']->getUrl('media/images/integrations/'.$name.'.gif'); ?>">
                        <!--<video autoplay muted loop>
                    <source src="<?php echo $view['assets']->getUrl('media/images/integrations/'.$name.'.mp4'); ?>">
                </video>-->
                        <br>
                        <div>
                            <p>Please note that the email field is mandatory to create a lead.</p>
                        </div>
                    </div>
                </div>
                <div class="integration-step step-padding">
                    <div class="step-content">
                        <span>Step 2: (Optional - Field Mapping)</span>
                        <li>Check Field Mapping and map all lead fields to Anyfunnels Lead form fields.</li>
                        <li>By default we will take the email field alone if the field mapping is empty</li>
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
                        <li>Set up automation workflow rules that will be triggered whenever you receive a lead from Instapage Integration.</li>
                        <img style="width: 60%;height: 230px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/instapage_help1.png'); ?>">
                        <br><br>
                        <img style="width: 60%;height: 350px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/instapage_help2.png'); ?>">
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
