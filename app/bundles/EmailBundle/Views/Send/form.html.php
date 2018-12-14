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

$view['slots']->set('leContent', 'emailSend');
$view['slots']->set('headerTitle', $view['translator']->trans('le.email.send.list', ['%name%' => $email->getName()]));
$isAdmin=$view['security']->isAdmin();
$style  = [];
if (!$isAdmin) {
    $style =  ['attr' => ['tabindex' => '-1', 'style' => 'pointer-events: none;background-color: #ebedf0;opacity: 1;display:none;']];
}
$btnclass = 'btn btn-primary '.((!$pending) ? ' disabled' : '');
?>
 <div class="row">
     <div style="text-align:center;width:100%;">
         <div style="display:flex;width:90%;margin-left:5%;">
             <div class="box1" style="margin-left: 270px;">
                 <div class="send-head"><h4><?php echo $view['translator']->trans('le.email.send.list.header', ['%name%' => $email->getName()]); ?></h4></div>
                 <div style="color:#555555;font-family:Open Sans, Helvetica, Roboto, Arial;line-height:200%; padding-right: 10px; padding-left: 10px; padding-top: 40px; padding-bottom: 38px;">
                     <div style="font-size:12px;line-height:24px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;"><img style="margin-top: 25px;" src="https://cops.leadsengage.com/media/images/arrow.png"></div>
                 </div>
                 <!--[if mso]></td></tr></table><![endif]-->

                 <div align="center" class="button-container center " style="padding-right: 50px; padding-left: 50px; padding-top:10px; padding-bottom:10px;">
                     <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-spacing: 0; border-collapse: collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;"><tr><td style="padding-right: 40px; padding-left: 40px; padding-top:10px; padding-bottom:10px;" align="center"><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="" style="height:39pt; v-text-anchor:middle; width:119pt;" arcsize="8%" strokecolor="#F61488" fillcolor="#F61488"><w:anchorlock/><v:textbox inset="0,0,0,0"><center style="color:#ffffff; font-family:Open Sans, Helvetica, Roboto, Arial; font-size:16px;"><![endif]-->
                     <div style="font-size:12px;margin-top:5px;line-height:10px;font-family:Open Sans, Helvetica, Roboto, Arial;text-align:center;color:#555555;"><span style="font-size:16px; line-height:20px;">
                            <strong><h6 class="send-body-detail"><?php echo $view['translator']->transChoice('le.email.send.instructions', $pending, ['%pending%' => $pending]); ?></h6></strong>
                         </span><span style="font-size:16px; line-height:32px;"></span>
                         <span style="font-size:16px; line-height:32px;"></span></div>
                     <br>
                     <div class="same">
                                 <?php echo $view['form']->start($form); ?>
                                       <?php echo $view['form']->widget($form['batchlimit'], $style); ?>
                                             <span style="text-align:center;line-height: 45px;">
                                    <!--<?php echo $view->render('MauticCoreBundle:Helper:confirm.html.php', [
                                        'message'         => $view['translator']->trans('le.email.form.confirmsend', ['%name%' => $email->getName()]),
                                        'confirmText'     => $view['translator']->trans('le.email.send'),
                                        'confirmCallback' => 'submitSendForm',
                                        'iconClass'       => 'fa fa-send-o',
                                        'btnText'         => $view['translator']->trans('le.email.send'),
                                        'btnClass'        => 'btn btn-primary send-btn'.((!$pending) ? ' disabled' : ''),
                                    ]);
                                    ?>-->
                                    <a class="butnew" style="margin-left:2px;border-radius: 3px;" href="javascript: void(0);" onclick="Le.submitSendForm();" ><span><i class="fa fa-send-o"></i><span style="padding:4px;font-size: 16px;"><?php echo $view['translator']->trans('le.email.send'); ?></span></span></a>
                                </span>
                                         <?php echo $view['form']->errors($form['batchlimit']); ?>
                                         <div class="text-center">
                                             <span class="label label-primary mt-lg hide"><?php echo $view['translator']->transChoice('le.email.send.pending', $pending, ['%pending%' => $pending]); ?></span>

                                         </div>
                                 <?php echo $view['form']->end($form); ?>
                         <div class="butnew" style="padding-top: 0px;padding-bottom: 0px;line-height: 36px;width: 92px !important;">
                             <a style="margin-left:10px;color:#FFF;" href="<?php echo $view['router']->path($actionRoute, ['objectAction' => 'view', 'objectId' => $email->getId()]); ?>" data-toggle="ajax"> <span style="font-family:Open Sans, Helvetica, Roboto, Arial;font-size:16px;line-height:32px;"><span style="font-size: 16px; line-height: 22px;    margin-left: -15px;font-family: Georgia, Times, 'Times New Roman', serif;text-align: center;">Cancel</span></span></div></div></a>
                     <div style="font-size:12px;margin-top:5px;line-height:10px;font-family:Open Sans, Helvetica, Roboto, Arial;text-align:center;color:#555555;">
                         <span style="font-size:14px; line-height:20px;">Or<br>
                             <a href="<?php echo $view['router']->path('le_email_campaign_action', ['objectAction' => 'sendExample', 'objectId' => $email->getId()]); ?>" data-toggle = "ajaxmodal" data-target="#leSharedModal" style="text-decoration: underline;"><?php echo $view['translator']->trans('le.email.send.an.example'); ?></a> before sending this campaign.</span><span style="font-size:16px; line-height:32px;"></span><span style="font-size:16px; line-height:32px;"></span></div>
                     <br><br>
                 </div></div>


             <!--[if mso]></td></tr></table><![endif]-->
         </div>

         <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
 </div>
</div>
<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
</div>
</div>
</div>
</div>

 </div>
<!-- CHange to New GUI<div class="row hide">
    <div class="col-sm-offset-3 col-sm-6">
        <div class="ml-lg mr-lg mt-md pa-lg">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">
                        <p><?php echo $view['translator']->transChoice('le.email.send.instructions', $pending, ['%pending%' => $pending]); ?></p>
                    </div>
                </div>
                <div class="panel-body">
                    <?php echo $view['form']->start($form); ?>
                    <div class="col-xs-8 col-xs-offset-2">
                        <div class="well mt-lg">
                            <div class="input-group">
                                <?php echo $view['form']->widget($form['batchlimit'], $style); ?>
                                <span class="input-group-btn" style="text-align:center;">
                                    <?php //echo $view->render('MauticCoreBundle:Helper:confirm.html.php', [
                                    //    'message'         => $view['translator']->trans('le.email.form.confirmsend', ['%name%' => $email->getName()]),
                                    //    'confirmText'     => $view['translator']->trans('le.email.send'),
                                    //    'confirmCallback' => 'submitSendForm',
                                    //    'iconClass'       => 'fa fa-send-o',
                                    //    'btnText'         => $view['translator']->trans('le.email.send'),
                                    //    'btnClass'        => 'btn btn-primary send-btn'.((!$pending) ? ' disabled' : ''),
                                    //]);
                                    ?>
                                    <a class="<?php echo $btnclass; ?>" style="margin-left:2px;border-radius: 3px;" href="javascript: void(0);" onclick="Le.submitSendForm();" ><span><i class="fa fa-send-o"></i><span style="padding:4px;"><?php echo $view['translator']->trans('le.email.send'); ?></span></span></a>
                                    <a class="btn btn-primary" style="margin-left:2px;border-radius: 3px;" href="<?php echo $view['router']->path($actionRoute, ['objectAction' => 'view', 'objectId' => $email->getId()]); ?>" data-toggle="ajax"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
                                </span>

                            </div>
                            <?php echo $view['form']->errors($form['batchlimit']); ?>
                            <div class="text-center">
                                <span class="label label-primary mt-lg hide"><?php echo $view['translator']->transChoice('le.email.send.pending', $pending, ['%pending%' => $pending]); ?></span>

                            </div>
                        </div>
                    </div>
                    <?php echo $view['form']->end($form); ?>
                </div>
            </div>
        </div>
    </div>
</div>-->