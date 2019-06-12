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
$view['slots']->set('headerTitle', $view['translator']->trans('le.drip.email.send.list.header', ['%name%' => $entity->getName()]));
$isAdmin=$view['security']->isAdmin();
$style  = [];
if (!$isAdmin) {
    $style =  ['attr' => ['tabindex' => '-1', 'style' => 'pointer-events: none;background-color: #ebedf0;opacity: 1;display:none;']];
}
$btnclass = 'btn btn-primary '.((!$emailcount || !$pending) ? ' disabled' : '');
$btnstyle = 'pointer-events: none;background-color: #80bbea;opacity: 1;';
?>
 <div class="row" style="margin-top: 60px;">
     <div style="text-align:center;width:100%;">
         <div style="display:flex;width:90%;margin-left:5%;">
             <div class="box1" style="margin-left: 270px;">
                 <div class="send-head">
                     <h4 class="header_text"><?php echo $view['translator']->trans('le.drip.email.send.list.header', ['%name%' => $entity->getName()]); ?>
                     </h4>
                 </div>
                 <div style="color:#555555;font-family:Open Sans, Helvetica, Roboto, Arial;line-height:200%; padding-right: 10px; padding-left: 10px; padding-top: 40px; padding-bottom: 38px;">
                     <div style="font-size:12px;line-height:24px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;"><img style="margin-top: 25px;" src="<?php echo $view['assets']->getUrl('media/images/arrow.png'); ?>"></div>
                 </div>

                 <div align="center" class="button-container center " style="padding-right: 50px; padding-left: 50px; padding-top:10px; padding-bottom:10px;">
                     <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-spacing: 0; border-collapse: collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;"><tr><td style="padding-right: 40px; padding-left: 40px; padding-top:10px; padding-bottom:10px;" align="center"><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="" style="height:39pt; v-text-anchor:middle; width:119pt;" arcsize="8%" strokecolor="#F61488" fillcolor="#F61488"><w:anchorlock/><v:textbox inset="0,0,0,0"><center style="color:#ffffff; font-family:Open Sans, Helvetica, Roboto, Arial; font-size:16px;"><![endif]-->
                     <div style="font-size:12px;margin-top:5px;line-height:10px;font-family:Open Sans, Helvetica, Roboto, Arial;text-align:center;color:#555555;">
                         <span style="font-size:16px; line-height:20px;">
                            <strong>
                                <h6 class="send-body-detail <?php echo !$emailcount ? 'hide' : ''; ?>">
                                    <?php echo $view['translator']->trans('le.drip.email.send.instructions', ['%pending%' => $pending, '%email_count%' => $emailcount]); ?>
                                </h6>
                                <h6 class="send-body-detail <?php echo !$emailcount ? '' : 'hide'; ?>">
                                    <?php echo $view['translator']->trans('le.drip.email.send.instructions.noemail'); ?>
                                </h6>
                            </strong>
                         </span>
                     </div>
                     <br>
                     <div class="same">
                         <form novalidate="" autocomplete="false" data-toggle="ajax" role="form" name="batch_send" method="post" action="<?php echo $view['router']->path($actionRoute, ['objectAction' => 'send', 'objectId' => $entity->getId()])?>">
                             <div>
                             <button class="btn btn-default custom-preview-button blue-theme-bg" style="height:40px;float:left;font-weight: normal;border-radius:5px;width:125px !important;<?php echo !$emailcount || !$pending || $entity->getisScheduled() ? $btnstyle : ''?>">
                                <span style="padding:4px;font-size: 16px;"><i class="fa fa-send-o"></i> Schedule</span>
                             </button>
                             <a class="btn btn-default custom-preview-button blue-theme-bg" style="height:40px;color:#ffffff;font-weight: normal;border-radius:5px;margin-left:5px;" href="<?php echo $view['router']->path($actionRoute, ['objectAction' => 'view', 'objectId' => $entity->getId()])?>" data-toggle="ajax">
                                 <span style="padding:4px;font-size: 16px;">Cancel</span>
                             </a>
                             </div>
                         </form>
                     </div>
                 </div>
         </div>
 </div>

