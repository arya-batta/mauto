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
        <h3>Instructions</h3>
        <p>
            Send person data to your Facebook Custom Audiences.
            <a class="integration-help-link" href="https://www.facebook.com/business/a/custom-audiences" target="_blank">Click here to learn more.</a>
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
                        <p>Anyfunnels is already authorized to access the Facebook account for <strong><?php echo $details['accountname'] ?></strong>. Click below to remove the access token we have on file and to stop sending Anyfunnels lead to Facebook.</p>
                        <a class="btn btn-default integration-click-btn" href="<?php echo $view['router']->path('le_integrations_account_remove', ['name' => $name]) ?>" data-toggle="ajax">Remove</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="integration-step">
            <div class="step-content">
                <h3>Step 2 : Set up workflow rules</h3>
                <p></p><p>To add lead to your custom audiences, create an action in <a class="integration-help-link" href="<?php echo $view['router']->path('le_campaign_index', ['page' => 1]) ?>">workflow</a> and choose <b>Facebook</b> as the provider. Choose the appropriate action. Then select your account and audience to configure your action.</p>
            </div>
        </div>
    </div>
</div>


