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

<div class="modal fade in email-type-modal" style="display: block; z-index: 9999;margin-top:10%;">
    <div class="le-modal-gradient " style="margin-left: 25%;">
    <div class="modal-dialog le-gradient-align" >
        <div class="modal-content le-modal-content" style="height: 260px">
            <div class="modal-header">
                <a href="javascript: void(0);" onclick="Mautic.closePluginModel('bee-plugin-model');" class="close" ><span aria-hidden="true">&times;</span></a>
                <h4 class="modal-title">
                    <?php echo $view['translator']->trans('le.plugins.page.title'); ?>
                </h4>
                <div class="modal-loading-bar"></div>
            </div>
            <div class="modal-body form-select-modal" style="background-color: #eee;height: 207px;">
                <div class="row" style="margin-top: 6%;">
                    <div class="col-md-6" style="width:50%;cursor:pointer;" onclick="Mautic.openFormCreator();">
                        <div class="panel panel-success">
                            <div class="white-block">
                                <div>
                                    <h3 class="panel-title le-email-editor-header"><?php echo $view['translator']->trans('le.plugins.page.form.embed'); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6" style="width:50%;cursor:pointer;" onclick="Mautic.openVideoEmbedModel();">
                        <div class="panel panel-success">
                            <div class="white-block">
                                <div>
                                    <h3 class="panel-title le-email-editor-header"><?php echo $view['translator']->trans('le.plugins.page.video.embed'); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
