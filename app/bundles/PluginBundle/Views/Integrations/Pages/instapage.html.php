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
                <h3>Instructions</h3>
                <div class="integration-step">
                    <div class="step-content">
                        <h3>Step 1: Set up a webhook integration in Instapage using the following URL</h3>
                        <input type="text" id="instapagecallback" value="<?php echo $url; ?>" readonly="readonly" class="form-control webhook-integration-url" style="margin-top:10px;">
                        <a id="instapagecallback_atag" onclick="Le.copytoClipboardforms('instapagecallback');">
                            <i aria-hidden="true" class="fa fa-clipboard"></i>
                            <?php echo $view['translator']->trans(
                                'leadsengage.subs.clicktocopy'
                            ); ?>
                        </a>
                        <div>
                            <p>Enter the URL above and set your webhook to "Send POST + JSON".</p>
                        </div>
                        <img style="height:auto;width:100%;" src="<?php echo $view['assets']->getUrl('media/images/integrations/'.$name.'.gif'); ?>">
                        <!--<video autoplay muted loop>
                    <source src="<?php echo $view['assets']->getUrl('media/images/integrations/'.$name.'.mp4'); ?>">
                </video>-->
                        <br>
                        <div>
                            <p><b>Note:</b>Email field is mandatory to create lead.</p>
                        </div>
                    </div>
                </div>
                <div class="integration-step">
                    <div class="step-content">
                        <h3>Step 2 : Set up workflow rules</h3>
                        <p></p><p>To perform an action any time a person is added via Instapage, create a <code>Submitted a landing page</code> <a class="integration-help-link" href="<?php echo $view['router']->path('le_campaign_index', ['page' => 1]) ?>">workflow</a> trigger. Enter your page name or leave it blank to configure your trigger.</p>
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
