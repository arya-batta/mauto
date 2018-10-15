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
    <div class="modal-dialog le-gradient-align" style="width:770px;" >
        <div class="modal-content le-modal-content">
            <div class="modal-header">
                <a href="javascript: void(0);" onclick="Mautic.closeCampaignTypeModel()" class="close" ><span aria-hidden="true">&times;</span></a>
                <h4 class="modal-title" style="color: #000;">
                    <?php echo $view['translator']->trans('le.campaignevent.type.header'); ?>
                </h4>
                <div class="modal-loading-bar"></div>
            </div>
            <div class="modal-body form-select-modal" style="padding: 20px;
">
              <div class="row">
                  <div class="col-md-4">
                      <div class="panel panel-success campaign-event-type-anchor">
                          <a href="javascript: void(0);" onclick="Mautic.selectCampaignType('action')">
                              <div>
                                  <div class="campaign-event-type-header">
                                      <h3><?php echo $view['translator']->trans('mautic.campaign.event.action.header'); ?></h3>
                                     <div class="campaign-event-type-icon-holder campaign-event-type-anchor-action">
                                         <svg class="campaign-event-type-icon" viewBox="0 0 32 32">
                                             <path d="M12 0l-12 16h12l-8 16 28-20h-16l12-12z"></path>
                                         </svg>
                                     </div>
                                  </div>
                              </div>
                              <div class="campaign-event-type-description">
                                  <?php echo $view['translator']->trans('mautic.campaign.event.action.descr'); ?>
                              </div>
                          </a>
                      </div>
                  </div>
                  <div class="col-md-4">
                      <div class="panel panel-success campaign-event-type-anchor">
                          <a href="javascript: void(0);" onclick="Mautic.selectCampaignType('decision')">
                              <div>
                                  <div class="campaign-event-type-header">
                                      <h3><?php echo $view['translator']->trans('mautic.campaign.event.decision.header'); ?></h3>
                                      <div class="campaign-event-type-icon-holder campaign-event-type-anchor-decision">
                                          <svg class="campaign-event-type-icon" viewBox="0 0 32 32">
                                              <path d="M14 22h4v4h-4zM22 8c1.105 0 2 0.895 2 2v6l-6 4h-4v-2l6-4v-2h-10v-4h12zM16 3c-3.472 0-6.737 1.352-9.192 3.808s-3.808 5.72-3.808 9.192c0 3.472 1.352 6.737 3.808 9.192s5.72 3.808 9.192 3.808c3.472 0 6.737-1.352 9.192-3.808s3.808-5.72 3.808-9.192c0-3.472-1.352-6.737-3.808-9.192s-5.72-3.808-9.192-3.808zM16 0v0c8.837 0 16 7.163 16 16s-7.163 16-16 16c-8.837 0-16-7.163-16-16s7.163-16 16-16z"></path>
                                          </svg>
                                      </div>
                                  </div>
                              </div>
                              <div class="campaign-event-type-description">
                                  <?php echo $view['translator']->trans('mautic.campaign.event.decision.descr'); ?>
                              </div>
                          </a>
                      </div>
                  </div>
                  <div class="col-md-4">
                      <div class="panel panel-success campaign-event-type-anchor">
                          <a href="javascript: void(0);" onclick="Mautic.selectCampaignType('goal')">
                              <div>
                                  <div class="campaign-event-type-header">
                                      <h3><?php echo $view['translator']->trans('le.campaign.event.goal.header'); ?></h3>
                                      <div class="campaign-event-type-icon-holder campaign-event-type-anchor-goal">
                                          <svg class="campaign-event-type-icon" viewBox="0 0 32 32">
                                              <path d="M26 6v-4h-20v4h-6v4c0 3.314 2.686 6 6 6 0.627 0 1.232-0.096 1.801-0.275 1.443 2.063 3.644 3.556 6.199 4.075v6.2h-2c-2.209 0-4 1.791-4 4h16c0-2.209-1.791-4-4-4h-2v-6.2c2.555-0.519 4.756-2.012 6.199-4.075 0.568 0.179 1.173 0.275 1.801 0.275 3.314 0 6-2.686 6-6v-4h-6zM6 13.625c-1.999 0-3.625-1.626-3.625-3.625v-2h3.625v2c0 1.256 0.232 2.457 0.655 3.565-0.213 0.039-0.431 0.060-0.655 0.060zM29.625 10c0 1.999-1.626 3.625-3.625 3.625-0.224 0-0.442-0.021-0.655-0.060 0.423-1.107 0.655-2.309 0.655-3.565v-2h3.625v2z"></path>
                                          </svg>
                                      </div>
                                  </div>
                              </div>
                              <div class="campaign-event-type-description">
                                  <?php echo $view['translator']->trans('le.campaign.event.goal.description'); ?>
                              </div>
                          </a>
                      </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="panel panel-success campaign-event-type-anchor">
                            <a href="javascript: void(0);" onclick="Mautic.selectCampaignType('fork')">
                                <div>
                                    <div class="campaign-event-type-header">
                                        <h3><?php echo $view['translator']->trans('le.campaign.event.fork.header'); ?></h3>
                                        <div class="campaign-event-type-icon-holder campaign-event-type-anchor-fork">
                                            <svg class="campaign-event-type-icon" viewBox="0 0 20 20" version="1.1" aria-hidden="true" class="icon-button-fork">
                                                <path d="M19.9,15.7L17.1,16C16.9,9.5,14.4,7.5,13,5.6C11.6,3.7,11.7,0,11.7,0h0H8.3h0c0,0,0.1,3.7-1.3,5.6C5.6,7.5,3.1,9.5,2.9,16 l-2.7-0.2C0,15.7,0,15.8,0,15.9l2.2,2l2.2,2c0.1,0.1,0.2,0.1,0.3,0l2.2-2l2.2-2c0.1-0.1,0-0.2-0.1-0.2L6.2,16 c0.2-5.9,2.4-7.2,3.8-8.9c1.4,1.6,3.6,3,3.8,8.9l-2.9-0.3c-0.1,0-0.2,0.1-0.1,0.2l2.2,2l2.2,2c0.1,0.1,0.2,0.1,0.3,0l2.2-2l2.2-2 C20,15.8,20,15.7,19.9,15.7z" fill-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="campaign-event-type-description">
                                    <?php echo $view['translator']->trans('le.campaign.event.fork.description'); ?>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-success campaign-event-type-anchor">
                            <a href="javascript: void(0);" onclick="Mautic.selectCampaignType('delay')">
                                <div>
                                    <div class="campaign-event-type-header">
                                        <h3><?php echo $view['translator']->trans('le.campaign.event.delay.header'); ?></h3>
                                        <div class="campaign-event-type-icon-holder campaign-event-type-anchor-delay">
                                            <svg class="campaign-event-type-icon" viewBox="0 0 32 32">
                                                <path d="M20.586 23.414l-6.586-6.586v-8.828h4v7.172l5.414 5.414zM16 0c-8.837 0-16 7.163-16 16s7.163 16 16 16 16-7.163 16-16-7.163-16-16-16zM16 28c-6.627 0-12-5.373-12-12s5.373-12 12-12c6.627 0 12 5.373 12 12s-5.373 12-12 12z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="campaign-event-type-description">
                                    <?php echo $view['translator']->trans('le.campaign.event.delay.description'); ?>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-success campaign-event-type-anchor">
                            <a href="javascript: void(0);" onclick="Mautic.selectCampaignType('exit')">
                                <div>
                                    <div class="campaign-event-type-header">
                                        <h3><?php echo $view['translator']->trans('le.campaign.event.exit.header'); ?></h3>
                                        <div class="campaign-event-type-icon-holder campaign-event-type-anchor-exit">
                                            <svg class="campaign-event-type-icon" aria-hidden="true" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 512 512"><path d="M497 273L329 441c-15 15-41 4.5-41-17v-96H152c-13.3 0-24-10.7-24-24v-96c0-13.3 10.7-24 24-24h136V88c0-21.4 25.9-32 41-17l168 168c9.3 9.4 9.3 24.6 0 34zM192 436v-40c0-6.6-5.4-12-12-12H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h84c6.6 0 12-5.4 12-12V76c0-6.6-5.4-12-12-12H96c-53 0-96 43-96 96v192c0 53 43 96 96 96h84c6.6 0 12-5.4 12-12z"/></svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="campaign-event-type-description">
                                    <?php echo $view['translator']->trans('le.campaign.event.exit.description'); ?>
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
