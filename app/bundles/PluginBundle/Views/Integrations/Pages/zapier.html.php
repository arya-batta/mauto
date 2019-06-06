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
$url = 'https://zapier.com/goto/?payload=.eJw9zE0KgzAQQOGrlFnbDlIqmH3PIeNkbIP5wyQqinevpdDt4-PtoCkTqB2yuGgpS-fJCSjQMosNUaaOrcEkXnfGzyYLHBU4SYleX_Z0ZKy6nIZHqMAaP571nXNMCnGjaGS6cXD4_yExS8zX3w3rR9O0WEq_hNSnlWMtehsKEd9Xt9jWB3ZDQDg-ksA9nA:1hG0Va:0cL6KxuVwuCBI56lGNt_eze9_t8';
?>

<!-- start: tab-content -->
<div class="tab-pane active bdr-w-0" id="instruction-container">
    <div class="panel panel-default bdr-t-wdh-0 mb-0 list-panel-padding">
        <div class="integration-container">
            <img style="width: auto;height: 100px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/zapier.png'); ?>">
            <h3>Integration Instructions</h3>
            <div class="integration-step">
                <div class="step-content">
                    <h3>Step 1:</h3>
                    <h3>Use below invitation link to connect with AnyFunnels Zapier integration.</h3>
<!--                    <input type="text" id="zapiercallback" value="--><?php //echo $url;?><!--" readonly="readonly" class="form-control webhook-integration-url" style="margin-top:10px;">-->
                    <a class="btn btn-default integration-click-btn" href="<?php echo $url; ?>" target='_new' >Open Invitation Link</a>
<!--                    <a id="zapiercallback_atag" onclick="Le.copytoClipboardforms('zapiercallback');">-->
<!--                        <i aria-hidden="true" class="fa fa-clipboard"></i>-->
<!--                        --><?php //echo $view['translator']->trans(
//                            'leadsengage.subs.clicktocopy'
//                        );?>
<!--                    </a>-->
                </div>
            </div>
            <div class="integration-step">
                <div class="step-content">
                    <h3>Step 2 :</h3>
                    <h3>Create a Zap:</h3>
                    <p>We have tons of triggers and actions available.</p>
                    <img style="width: auto;height: 450px;" src="<?php echo $view['assets']->getUrl('media/images/integrations/zapier_help1.png'); ?>">
                </div>
            </div>
            <div class="integration-step hide">
                <div class="step-content">
                    <h3>Step 2 : Set up workflow rules</h3>
                    <p></p><p>To perform an action any time a person is added via Unbounce, create a <code>Submitted a landing page</code> <a class="integration-help-link" href="<?php echo $view['router']->path('le_campaign_index', ['page' => 1]) ?>">workflow</a> trigger. Enter your page name or leave it blank to configure your trigger.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end: tab-content -->