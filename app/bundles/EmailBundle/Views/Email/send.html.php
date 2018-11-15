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
$view['slots']->set('leContent', 'pricingplans');
$view['slots']->set('headerTitle', $view['translator']->trans($headerTitle));
$emailentity = $model->getEntity($id);
?>
<center>
    <div style="text-align:center;width:100%;margin-top: 80px;">
        <div >
            <div class="pricingtables">

                <div class="send-table">
                    <div class="send-head">
                        <h4>Send An Example</h4>
                        <div class="send-body">
                            <img style="margin-top: 25px;" src="<?php echo $view['assets']->getUrl('media/images/emailtick.png'); ?>" />
                            <br><br>
                            <div>
                                <strong><h6 class="send-body-detail">Send An Example Before Sending This Campaign</h6></strong>
                            </div>
                        </div>
                        <br>
                        <div class="send-foot">
                            <a class="btn send-btn"  href="<?php echo $view['router']->path('le_email_campaign_action', ['objectAction' => 'sendExample', 'objectId' => $emailentity->getId()]); ?>" data-toggle = "ajaxmodal" data-target="#leSharedModal" >Send Example</a>
                            <a style="margin-left: 20px;text-decoration: underline;color: #00bfff" href="<?php echo $view['router']->path('le_email_campaign_index'); ?>" >Cancel</a>
                        </div>
                    </div>
                </div>

                <div class="send-table">
                    <div class="send-head">
                        <h4>Send This Campaign</h4>
                        <div class="send-body">
                            <img style="margin-top: 25px;" src="<?php echo $view['assets']->getUrl('media/images/arrow.png'); ?>" />
                            <br><br>
                            <div>
                                <strong><h6 class="send-body-detail"><?php echo $view['translator']->transChoice('le.email.send.instructions', $pending, ['%pending%' => $pending]); ?></h6></strong>
                            </div>
                        </div>
                        <br><br>
                        <div style="border-color:green">
                            <div class="send-foot" style="    margin-bottom:20px;">
                                <a class="btn send-btn" href="<?php echo $view['router']->path('le_email_campaign_action', ['objectAction' => 'send', 'objectId' => $emailentity->getId()]); ?>" >Send Now</a>
                                <a style="margin-left: 20px;text-decoration: underline;color: #00bfff" href="<?php echo $view['router']->path('le_email_campaign_index'); ?>" >Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</center>

