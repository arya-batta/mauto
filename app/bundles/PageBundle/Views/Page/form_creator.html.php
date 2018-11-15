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
        <div class="modal-contentle-modal-content" style="height: 360px">
            <div class="modal-header"style="background-color: white;">
                <a href="javascript: void(0);" onclick="Le.closePluginModel('bee-plugin-form-creator')" class="close" ><span aria-hidden="true">&times;</span></a>
                <h4 class="modal-title">
                    <?php echo $view['translator']->trans('le.plugins.page.form.embed'); ?>
                </h4>
                <div class="modal-loading-bar"></div>
            </div>
            <div class="modal-body form-select-modal" style="background-color: #fff;">
                <div class="row">
                    <div class="col-md-6 insert-tokens">

                    </div>

                </div>
                <div class="row">
                    <div>
                        <div class="modal-body">
                            <!--<p><?php echo $view['translator']->trans('mautic.form.form.help.automaticcopy'); ?></p>
                            <h3><?php echo $view['translator']->trans('mautic.form.form.help.automaticcopy.js'); ?></h3>
                            <textarea id="javascipt_textarea_page" class="form-control" readonly></textarea>
                            <a id="javascipt_textarea_atag" onclick="Le.copytoClipboardforms('javascipt_textarea_page');">
                                <i aria-hidden="true" class="fa fa-clipboard"></i>
                                <?php echo $view['translator']->trans(
                                    'leadsengage.subs.clicktocopy'
                                ); ?>
                            </a>-->
                            <h3 class="pt-lg"><?php echo $view['translator']->trans(
                                    'mautic.form.form.help.automaticcopy.iframe'
                                ); ?></h3>
                            <textarea style="height: 113px;max-height: 113px;" id="iframe_textarea_page" class="form-control" readonly onclick="Le.copytoClipboardforms(this);"></textarea>
                            <a id="iframe_textarea_atag" onclick="Le.copytoClipboardforms('iframe_textarea_page');"><i aria-hidden="true" class="fa fa-clipboard"></i>
                                <?php echo $view['translator']->trans(
                                    'leadsengage.subs.clicktocopy'
                                ); ?></a>
                            <br>
                            <br>
                            <i><?php echo $view['translator']->trans('mautic.form.form.help.automaticcopy.iframe.note'); ?></i>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
