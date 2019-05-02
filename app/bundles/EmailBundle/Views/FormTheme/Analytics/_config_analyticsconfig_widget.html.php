<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$fields        = $form->children;
?>



<div class="panel panel-primary analyticsconfig">
    <div class="panel-heading analyticsconfig">
        <h3 class="panel-title"><?php echo $view['translator']->trans('le.analytics.header'); ?></h3>
    </div>
    <div class="panel-body">
        <p>
            Set default UTM codes to automatically add them to every new hyperlink in your Broadcast and Drip Sequences emails.
            <br><br>Changing these defaults will not replace UTM codes in existing emails.
        </p>
        <br>
        <!-- tabs controls -->
        <ul class="nav nav-tabs pr-md">

            <li class="active">
                <a href="#broadcast-container" role="tab" data-toggle="tab">
                    <?php echo $view['translator']->trans('le.analytics.broadcast.campaign') ?>
                </a>
            </li>
            <li class="">
                <a href="#dripcampaign-container" role="tab" data-toggle="tab">
                    <?php echo $view['translator']->trans('le.analytics.drip.campaign') ?>
                </a>
            </li>
        </ul>
        <!--/ tabs controls -->
        <!-- start: tab-content -->
        <div class="tab-content pa-md">
            <div class="tab-pane active bdr-w-0" id="broadcast-container">
                <div class="">
                    <div class="row">
                        <div class="col-sm-12">
                            <?php echo $view['form']->row($fields['list_source']); ?>
                            <p style="text-align: right;"><?php echo $view['translator']->trans('le.analytics.utm.source'); ?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php echo $view['form']->row($fields['list_medium']); ?>
                            <p style="text-align: right;"><?php echo $view['translator']->trans('le.analytics.utm.medium'); ?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php echo $view['form']->row($fields['list_campaignname']); ?>
                            <p style="text-align: right;"><?php echo $view['translator']->trans('le.analytics.utm.campaign'); ?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php echo $view['form']->row($fields['list_content']); ?>
                            <p style="text-align: right;"><?php echo $view['translator']->trans('le.analytics.utm.content'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade in bdr-w-0" id="dripcampaign-container">
                <div class="">
                    <div class="row">
                        <div class="col-sm-12">
                            <?php echo $view['form']->row($fields['drip_source']); ?>
                            <p style="text-align: right;"><?php echo $view['translator']->trans('le.analytics.utm.source'); ?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php echo $view['form']->row($fields['drip_medium']); ?>
                            <p style="text-align: right;"><?php echo $view['translator']->trans('le.analytics.utm.medium'); ?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php echo $view['form']->row($fields['drip_campaignname']); ?>
                            <p style="text-align: right;"><?php echo $view['translator']->trans('le.analytics.utm.campaign'); ?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <?php echo $view['form']->row($fields['drip_content']); ?>
                            <p style="text-align: right;"><?php echo $view['translator']->trans('le.analytics.utm.content'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <?php echo $view['form']->row($fields['analytics_status']); ?>
                </div>
            </div>
        </div>
    </div>
</div>

