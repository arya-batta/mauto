<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<div class="email-type-modal-backdrop" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #000000; opacity: 0.7; z-index: 9000"></div>

<div class="modal fade in email-type-modal" style="display: block; z-index: 9999;margin-top:5%;">
    <div class="le-modal-gradient " style="margin-left: 25%;">
        <div class="modal-dialog le-gradient-align" >
            <div class="modal-contentle-modal-content" style="height: 373px">
                <div class="modal-header"style="background-color: white;">
                <a href="javascript: void(0);" onclick="Mautic.closePluginModel('bee-plugin-video-embed')" class="close" ><span aria-hidden="true">&times;</span></a>
                <h4 class="modal-title">
                    <?php echo $view['translator']->trans('le.plugins.page.video.embed'); ?>
                </h4>
                <div class="modal-loading-bar"></div>
            </div>
            <div class="modal-body form-select-modal" style="background-color: #fff;height: 317px;">
                <div class="row">
                    <div class="col-md-8" id="youtube_u">
                        <label class="control-label required">Youtube URL</label>
                        <input type="text" id="youtube_url" class="form-control le-input " autocomplete="false"  >
                        <div id="youtube"class="help-block"></div>
                    </div>
                    <div class="col-md-4">
                        <br>
                        <a class="btn btn-primary btn-bee-show-preview" onclick="Mautic.ConvertURLtoEmbed();">
                            <?php echo $view['translator']->trans('le.plugins.page.generate.video.embed'); ?>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div>
                        <div class="modal-body">
                            <h3 class="pt-lg"><?php echo $view['translator']->trans(
                                    'le.plugins.page.generate.html.code'
                                ); ?></h3>
                            <textarea style="height: 113px;max-height: 113px;" id="iframe_textarea_videopage" class="form-control" readonly onclick="Mautic.copytoClipboardforms(this);"></textarea>
                            <a id="iframe_textarea_atag" onclick="Mautic.copytoClipboardforms('iframe_textarea_videopage');"><i aria-hidden="true" class="fa fa-clipboard"></i>
                                <?php echo $view['translator']->trans(
                                    'leadsengage.subs.clicktocopy'
                                ); ?></a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
