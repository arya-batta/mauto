<?php
/**
 * Created by PhpStorm.
 * User: prabhu
 * Date: 30/1/18
 * Time: 11:56 AM.
 */
$isAdmin    =$view['security']->isAdmin();
$hideplugin = ($type == 'page') ? '' : 'hide';
?>


<div id="bee-plugin-container" class="hide">
    <div class="builder-content">
        <input type="hidden" id="builder_url" value="<?php echo $view['router']->path('mautic_'.$type.'_action', ['objectAction' => 'builder', 'objectId' => $objectId]); ?>" />
    </div>
     <div id="bee-plugin-btnpanel">
         <div class="row pull-left" style="margin-left: 2%;width: 40%">
             <h3 id = "BeeEditor_Header"></h3>
         </div>
         <div class="row pull-right" style="margin-right: 2%;">
            <div class="col-xs-12">
                <a class="btn btn-primary btn-bee-show-preview <?php echo $hideplugin; ?>" onclick="Mautic.openPluginModel('bee-plugin-model');">
                    <?php echo $view['translator']->trans('Plugins'); ?>
                </a>
                <button type="button" class="btn btn-primary btn-bee-show-preview" onclick="javascript:bee.preview();">
                    <?php echo $view['translator']->trans('mautic.email.beeeditor.showpreview'); ?>
                </button>
                <button type="button" class="btn btn-primary btn-bee-show-structure hide" onclick="javascript:bee.toggleStructure();">
                    <?php echo $view['translator']->trans('mautic.email.beeeditor.showstructure'); ?>
                </button>
                <?php if ($isAdmin):?>
                <button type="button" class="btn btn-primary btn-bee-close-downloadjson" onclick="javascript:bee.saveAsTemplate();">
                    <?php echo $view['translator']->trans('mautic.email.beeeditor.downloadjson'); ?>
                </button>
                <button type="button" class="btn btn-primary btn-bee-close-downloadhtml" onclick="javascript:bee.send();">
                    <?php echo $view['translator']->trans('mautic.email.beeeditor.downloadhtml'); ?>
                </button>
                <?php endif; ?>
                <button type="button" class="btn btn-primary btn-bee-save" onclick="javascript:bee.save();">
                    <?php echo $view['translator']->trans('mautic.core.form.saveandclose'); ?>
                </button>
                <button type="button" class="btn btn-primary btn-bee-close-editor" onclick="Mautic.closeBeeEditor();">
                    <?php echo $view['translator']->trans('mautic.core.form.cancel'); ?>
                </button>

            </div>
        </div>
    </div>
    <div id="bee-plugin-viewpanel">

    </div>
    <div class="hide" id="bee-plugin-model">
        <?php echo $view->render('MauticPageBundle:Page:model.html.php'); ?>
    </div>
    <div class="hide" id="bee-plugin-form-creator">
        <?php echo $view->render('MauticPageBundle:Page:form_creator.html.php'); ?>
    </div>
    <div class="hide" id="bee-plugin-video-embed">
        <?php echo $view->render('MauticPageBundle:Page:video_creator.html.php'); ?>
    </div>

</div>
