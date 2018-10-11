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
    <div class="modal-dialog le-gradient-align" style="width:700px;" >
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
                            <a class="campaign-event-type-anchor" href="javascript: void(0);" onclick="Mautic.selectCampaignType('action')">
                                <div class="campaign-event-type-icon" style="background: #24aef2">
                                    <svg  viewBox="0 0 10 16" version="1.1" width="10" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M10 7H6l3-7-9 9h4l-3 7z"></path></svg>
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
                              <a class="campaign-event-type-anchor" href="javascript: void(0);" onclick="Mautic.selectCampaignType('decision')">
                                  <div class="campaign-event-type-icon" style="background: #ffda24">
                                      <svg  viewBox="0 0 14 16" version="1.1" width="14" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M6 10h2v2H6v-2zm4-3.5C10 8.64 8 9 8 9H6c0-.55.45-1 1-1h.5c.28 0 .5-.22.5-.5v-1c0-.28-.22-.5-.5-.5h-1c-.28 0-.5.22-.5.5V7H4c0-1.5 1.5-3 3-3s3 1 3 2.5zM7 2.3c3.14 0 5.7 2.56 5.7 5.7s-2.56 5.7-5.7 5.7A5.71 5.71 0 0 1 1.3 8c0-3.14 2.56-5.7 5.7-5.7zM7 1C3.14 1 0 4.14 0 8s3.14 7 7 7 7-3.14 7-7-3.14-7-7-7z"></path></svg>
                                  </div>
                                  <div>
                                      <h3><?php echo $view['translator']->trans('mautic.campaign.event.decision.header'); ?></h3>
                                      <p><?php echo $view['translator']->trans('mautic.campaign.event.decision.descr'); ?></p>
                                  </div>
                              </a>
                          </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div style="height: 120px;" class="panel panel-success">
                            <a class="campaign-event-type-anchor" href="javascript: void(0);" onclick="Mautic.selectCampaignType('fork')">
                                <div class="campaign-event-type-icon" style="background: #f224f2">
                                    <svg viewBox="0 0 20 20" version="1.1" aria-hidden="true" class="icon-button-fork">
                                        <path d="M19.9,15.7L17.1,16C16.9,9.5,14.4,7.5,13,5.6C11.6,3.7,11.7,0,11.7,0h0H8.3h0c0,0,0.1,3.7-1.3,5.6C5.6,7.5,3.1,9.5,2.9,16 l-2.7-0.2C0,15.7,0,15.8,0,15.9l2.2,2l2.2,2c0.1,0.1,0.2,0.1,0.3,0l2.2-2l2.2-2c0.1-0.1,0-0.2-0.1-0.2L6.2,16 c0.2-5.9,2.4-7.2,3.8-8.9c1.4,1.6,3.6,3,3.8,8.9l-2.9-0.3c-0.1,0-0.2,0.1-0.1,0.2l2.2,2l2.2,2c0.1,0.1,0.2,0.1,0.3,0l2.2-2l2.2-2 C20,15.8,20,15.7,19.9,15.7z" fill-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3><?php echo $view['translator']->trans('le.campaign.event.fork.header'); ?></h3>
                                    <p><?php echo $view['translator']->trans('le.campaign.event.fork.description'); ?></p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="height: 120px;" class="panel panel-success">
                            <a class="campaign-event-type-anchor" href="javascript: void(0);" onclick="Mautic.selectCampaignType('goal')">
                                <div class="campaign-event-type-icon" style="background: #24f28c">
                                    <svg viewBox="0 0 14 16" version="1.1" width="14" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M14 6l-4.9-.64L7 1 4.9 5.36 0 6l3.6 3.26L2.67 14 7 11.67 11.33 14l-.93-4.74z"></path></svg>
                                </div>
                                <div>
                                    <h3><?php echo $view['translator']->trans('le.campaign.event.goal.header'); ?></h3>
                                    <p><?php echo $view['translator']->trans('le.campaign.event.goal.description'); ?></p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div style="height: 120px;" class="panel panel-success">
                            <a class="campaign-event-type-anchor" href="javascript: void(0);" onclick="Mautic.selectCampaignType('delay')">
                                <div class="campaign-event-type-icon" style="background: #f22446">
                                    <svg viewBox="0 0 14 16" version="1.1" width="14" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M8 8h3v2H7c-.55 0-1-.45-1-1V4h2v4zM7 2.3c3.14 0 5.7 2.56 5.7 5.7s-2.56 5.7-5.7 5.7A5.71 5.71 0 0 1 1.3 8c0-3.14 2.56-5.7 5.7-5.7zM7 1C3.14 1 0 4.14 0 8s3.14 7 7 7 7-3.14 7-7-3.14-7-7-7z"></path></svg>
                                </div>
                                <div>
                                    <h3><?php echo $view['translator']->trans('le.campaign.event.delay.header'); ?></h3>
                                    <p><?php echo $view['translator']->trans('le.campaign.event.delay.description'); ?></p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="height: 120px;" class="panel panel-success">
                            <a class="campaign-event-type-anchor" href="javascript: void(0);" onclick="Mautic.selectCampaignType('exit')">
                                <div class="campaign-event-type-icon" style="background: #f992a3">
                                    <svg viewBox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M12 9V7H8V5h4V3l4 3-4 3zm-2 3H6V3L2 1h8v3h1V1c0-.55-.45-1-1-1H1C.45 0 0 .45 0 1v11.38c0 .39.22.73.55.91L6 16.01V13h4c.55 0 1-.45 1-1V8h-1v4z"></path></svg>
                                </div>
                                <div>
                                    <h3><?php echo $view['translator']->trans('le.campaign.event.exit.header'); ?></h3>
                                    <p><?php echo $view['translator']->trans('le.campaign.event.exit.description'); ?></p>
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
<div class="clearfix"></div>
