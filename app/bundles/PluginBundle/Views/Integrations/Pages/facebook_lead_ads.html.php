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
                <h3>Instructions</h3>
                <p>
                    Facebook Lead Ads allow you to gather person data directly from your Facebook ads.
                    <a class="integration-help-link" href="https://www.facebook.com/business/news/lead-ads-launch" target="_blank">Click here to learn more.</a>
                </p>
                <div class="integration-step">
                    <div class="step-content">
                        <h3>Step 1: Grant access to your Facebook account</h3>
                        <?php if (!$details['authorization']): ?>
                            <div>
                                <p>Click below to authorize Anyfunnels to access your account.</p>
                                <a class="btn btn-default integration-click-btn" href="<?php echo $view['router']->path('le_new_integration_auth_user', ['integration' => $name]) ?>">Authorize</a>
                            </div>
                        <?php else: ?>
                            <div>
                                <p>Anyfunnels is already authorized to access the Facebook account for <strong><?php echo $details['accountname'] ?></strong>. Click below to remove the access token we have on file and to stop sending Facebook leads to Anyfunnels.</p>
                                <a class="btn btn-default integration-click-btn" href="<?php echo $view['router']->path('le_integrations_account_remove', ['name' => $name]) ?>" data-toggle="ajax">Remove</a>
                            </div>
                            <div class="table-responsive integration-table-box">
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
                                                    ?>
                                                    <a class="btn btn-default" href="<?php echo $view['router']->path('le_integrations_fb_page_subscription', ['integration' => $name, 'pageid'=>$page[0], 'action' => $action]) ?>" data-toggle="ajax"><?php echo $actionlabel ?></a>
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
                <div class="integration-step">
                    <div class="step-content">
                        <h3>Step 2 : Set up workflow rules</h3>
                        <p></p><p>To perform an action any time a person is added via Facebook, create a <code>Submitted a landing page</code> <a class="integration-help-link" href="<?php echo $view['router']->path('le_campaign_index', ['page' => 1]) ?>">workflow</a> trigger. Choose your page and form to configure your trigger.</p>
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


