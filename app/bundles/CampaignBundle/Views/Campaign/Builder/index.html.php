<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<div class="campaignevent-type-modal-backdrop hide"><div class="modal-backdrop fade in"></div></div>
<div class="modal fade campaignevent-type-modal hide" style="display: block; z-index: 9999;">
    <div class="le-modal-gradient" style="margin-top:100px;margin-left:300px;">
    <div class="modal-dialog le-gradient-align" style="width:600px;" >
        <div class="modal-content le-modal-content">
            <div class="modal-header" style="border-bottom:none;margin-left: 100px;margin-top: 20px;">
                <a href="javascript: void(0);" onclick="Mautic.closeCampaignTypeModel()" class="close" ><span aria-hidden="true">&times;</span></a>
                <h4 class="modal-title" style="color: #000;">
                    <?php echo $view['translator']->trans('le.campaignevent.type.header'); ?>
                </h4>
                <div class="modal-loading-bar"></div>
            </div>
            <div class="modal-body form-select-modal" style="padding: 20px;
">
              <div class="row">
                    <div class="col-md-6">
                        <div style="height: 120px;" class="panel panel-success">
                            <a class="campaign-event-type-anchor" href="javascript: void(0);" onclick="Mautic.selectCampaignType('Action')">
                                <div class="campaign-event-type-icon" style="background: #24aef2">
                                    <i class="hidden-xs fa fa-bullseye fa-lg campaign-event-type-icon-font"></i>
                                </div>
                                <div>
                                    <h3><?php echo $view['translator']->trans('mautic.campaign.event.action.header'); ?></h3>
                                    <p><?php echo $view['translator']->trans('mautic.campaign.event.action.descr'); ?></p>
                                </div>
                            </a>
                        </div>
                    </div>
                  <div class="col-md-6">
                          <div style="height: 120px;" class="panel panel-success">
                              <a class="campaign-event-type-anchor" href="javascript: void(0);" onclick="Mautic.selectCampaignType('Condition')">
                                  <div class="campaign-event-type-icon" style="background: #ffda24">
                                      <i class="hidden-xs fa fa-random fa-lg campaign-event-type-icon-font"></i>
                                  </div>
                                  <div>
                                      <h3><?php echo $view['translator']->trans('mautic.campaign.event.decision.header'); ?></h3>
                                      <p><?php echo $view['translator']->trans('mautic.campaign.event.decision.descr'); ?></p>
                                  </div>
                              </a>
                          </div>
                  </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
<div class="hide" id="CampaignEventPanel">
    <!--<div id="CampaignEventPanelGroups">
        <div class="row">
            <div class="mr-0 ml-0 pl-xs pr-xs campaign-group-container col-md-4" id="DecisionGroupSelector">
               <div class="panel panel-success mb-0">
                    <div class="panel-heading">
                        <div class="col-xs-8 col-sm-10 np">
                            <h3 class="panel-title"><?php echo $view['translator']->trans('mautic.campaign.event.decision.header'); ?></h3>
                        </div>
                        <div class="col-xs-4 col-sm-2 pl-0 pr-0 pt-10 pb-10 text-right">
                            <i class="hidden-xs fa fa-random fa-lg"></i>
                            <button class="visible-xs pull-right btn btn-sm btn-default btn-nospin text-success" data-type="Decision">
                                <?php echo $view['translator']->trans('mautic.core.select'); ?>
                            </button>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php echo $view['translator']->trans('mautic.campaign.event.decision.descr'); ?>
                    </div>
                    <div class="hidden-xs panel-footer text-center">
                        <button class="btn btn-lg btn-default btn-nospin text-success" data-type="Decision">
                            <?php echo $view['translator']->trans('mautic.core.select'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <div class="mr-0 ml-0 pl-xs pr-xs campaign-group-container col-md-4 " id="ActionGroupSelector">
                <div class="panel panel-primary mb-0">
                    <div class="panel-heading">
                        <div class="col-xs-8 col-sm-10 np">
                            <h3 class="panel-title"><?php echo $view['translator']->trans('mautic.campaign.event.action.header'); ?></h3>
                        </div>
                        <div class="col-xs-4 col-sm-2 pl-0 pr-0 pt-10 pb-10 text-right">
                            <i class="hidden-xs fa fa-bullseye fa-lg"></i>
                            <button class="visible-xs pull-right btn btn-sm btn-default btn-nospin text-primary" data-type="Action">
                                <?php echo $view['translator']->trans('mautic.core.select'); ?>
                            </button>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php echo $view['translator']->trans('mautic.campaign.event.action.descr'); ?>
                    </div>
                    <div class="hidden-xs panel-footer text-center">
                        <button class="btn btn-lg btn-default btn-nospin text-primary" data-type="Action">
                            <?php echo $view['translator']->trans('mautic.core.select'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <div class="mr-0 ml-0 pl-xs pr-xs campaign-group-container col-md-4" id="ConditionGroupSelector">
                <div class="panel panel-danger mb-0">
                    <div class="panel-heading">
                        <div class="col-xs-8 col-sm-10 np">
                            <h3 class="panel-title"><?php echo $view['translator']->trans('mautic.campaign.event.condition.header'); ?></h3>
                        </div>
                        <div class="col-xs-4 col-sm-2 pl-0 pr-0 pt-10 pb-10 text-right">
                            <i class="hidden-xs fa fa-filter fa-lg"></i>
                            <button class="visible-xs pull-right btn btn-sm btn-default btn-nospin text-danger" data-type="Condition">
                                <?php echo $view['translator']->trans('mautic.core.select'); ?>
                            </button>
                        </div>
                    </div>
                    <div class="panel-body"><?php echo $view['translator']->trans('mautic.campaign.event.condition.descr'); ?></div>
                    <div class="hidden-xs panel-footer text-center">
                        <button class="btn btn-lg btn-default btn-nospin text-danger" data-type="Condition">
                            <?php echo $view['translator']->trans('mautic.core.select'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>-->
    <div id="CampaignEventPanelLists" class="hide">
        <?php
        echo $view->render('MauticCampaignBundle:Campaign\Builder:source_list.html.php',
            [
                'campaignSources' => $campaignSources,
                'campaignId'      => $campaignId,
            ]
        );

        echo $view->render('MauticCampaignBundle:Campaign\Builder:event_list.html.php',
            [
                'eventSettings' => $eventSettings,
                'campaignId'    => $campaignId,
            ]
        );
        ?>

    </div>
</div>
<div class="clearfix"></div>
