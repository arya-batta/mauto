<?php
?>
<div class="col-md-6 col-md-offset-3 mt-md" style="white-space: normal;">
    <p class="drip-col-stats"><h3 style="text-align: center;"><?php echo $view['translator']->trans('le.drip.email.blueprint.desc')?></h3></p>
    <div class="row drip-col-stats" style="border:1px solid;margin-top: 20px;">
        <div class="col-md-6" style="border-right:1px solid;">
            <div>
                <img height="125px" width="auto" src="<?php echo $view['assets']->getUrl('media/images/blueprint.png')?>"/>
                <h2 class="drip-col-stats"><?php echo $view['translator']->trans('le.drip.email.choose.blueprint')?></h2>
                <br>
                <a class="btn btn-default text-primary le-btn-default" onclick="Le.openBluePrintPage();" style="background-color: #ec407a;color:#ffffff;padding-top: 5px;border-radius:4px;z-index:1003;" data-toggle="ajax">
                    <span>
                        <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.drip.email.browse.blueprint'); ?></span>
                    </span>
                </a>
                <br>
                <br>
            </div>
        </div>
        <div class="col-md-6">
            <div>
                <img height="125px" width="auto" src="<?php echo $view['assets']->getUrl('media/images/startfromscratch.png')?>"/>
                <h2 class="drip-col-stats"><?php echo $view['translator']->trans('mautic.campaign.type.blanktemplate.header')?></h2>
                <br>
                <!--<a class="btn btn-default text-primary le-btn-default" onclick="Le.TriggerNewClick();" style="background-color: #ec407a;color:#ffffff;padding-top: 5px;border-radius:4px;z-index:1003;" data-toggle="ajax">onclick="Le.openDripEmailEditor();"
                    <span>
                        <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.drip.email.create.your.own'); ?></span>
                    </span>
                </a>-->
                <div class="newbutton-container">
                    <li class="dropdown dropdown-menu-right" style="display: block;">
                        <a class="btn btn-nospin hidden-xs le-btn-default"  onclick="Le.TriggerNewClick();" style="background-color: #ec407a;color:#ffffff;padding-top: 5px;border-radius:4px;z-index:1003;" data-toggle="dropdown" href="#">
                            <span><span class="hidden-xs hidden-sm"> <?php echo $view['translator']->trans('le.drip.email.create.your.own')?></span></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" style="margin-top: -117%;margin-right:-118%;">
                            <div class="insert-drip-options">
                                <div style="background:#fff;padding:24px 30px;;width:450px;height:auto;">
                                    <h1 style='font-size:16px;font-weight:bold;'><?php echo $view['translator']->trans('Which email builder would you like to use?')?></h1>
                                    <br>
                                    <div class="row"
                                    >
                                        <a href="<?php echo $view['router']->path('le_dripemail_email_action', ['objectId' => $entity->getId(), 'subobjectAction' => 'new', 'subobjectId' => 1]); ?>">
                                            <div class="col-md-6 editor_layout"  style="margin-left:10px;"><!--onclick="Le.setValueforNewButton('advance_editor',this);"-->
                                                <img height="100px" width="auto" src="<?php echo $view['assets']->getUrl('media/images/drag-drop.png')?>"/>
                                                <h4><?php echo $view['translator']->trans('le.email.editor.advance.header')?></h4>
                                                <br>
                                            </div>
                                        </a>
                                        <a href="<?php echo $view['router']->path('le_dripemail_email_action', ['objectId' => $entity->getId(), 'subobjectAction' => 'new', 'subobjectId' => 0]); ?>">
                                            <div class="col-md-6 editor_layout editor_select" style="margin-left:20px;"> <!--onclick="Le.setValueforNewButton('basic_editor',this);"-->
                                                <img height="100px" width="auto" src="<?php echo $view['assets']->getUrl('media/images/rich-text.png')?>"/>
                                                <h4><?php echo $view['translator']->trans('le.email.editor.basic.header')?></h4>
                                                <br>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </ul>
                    </li>
                </div>
                <br>
            </div>
        </div>
    </div>
</div>

