<?php
?>
<div class="col-md-8 col-md-offset-2 mt-md bluprint" style="white-space: normal;">
    <p class="drip-col-stats"><h1 style="text-align: center;"><?php echo $view['translator']->trans('le.drip.email.blueprint.desc')?></h1></p>
    <div class="row drip-col-stats" style="height: 300px;">
        <div class="col-md-6 fl-left" style="height:100%;width:50%;">
            <div style="margin-top: 40px;">
                <img height="140px" width="auto" src="<?php echo $view['assets']->getUrl('media/images/blueprint.png')?>"/>
                <h2 class="drip-col-stats" style="margin-left: -4%"><?php echo $view['translator']->trans('le.drip.email.choose.blueprint')?></h2>
                <br>
                <p style="margin-left:30px;width:80%;">Our campaign blueprints are a great way to get started. Just pick a blueprint,
                    fill in the placeholders, and have a fully functioning drip campaign in minutes.
                </p>
                <br>
                <a class="btn btn-default text-primary le-btn-default custom-preview-button blue-theme-bg" onclick="Le.openBluePrintPage();" style="padding-top: 5px;border-radius:0px;z-index:1003;padding:10px;" data-toggle="ajax">
                    <span>
                        <span class="" id="change-template-span" style="font-size:15px;"><?php echo $view['translator']->trans('le.drip.email.browse.blueprint'); ?></span>
                    </span>
                </a>
                <br>
                <br>
            </div>
        </div>
        <div class="col-md-6 fl-left" style="height:120%;width:50%;">
            <div style="margin-top: 40px;">
                <img height="140px" width="auto" src="<?php echo $view['assets']->getUrl('media/images/startfromscratch.png')?>"/>
                <h2 class="drip-col-stats"><?php echo $view['translator']->trans('le.drip.email.start.from.scratch')?></h2>
                <br>
                <!--<a class="btn btn-default text-primary le-btn-default" onclick="Le.TriggerNewClick();" style="background-color: #ec407a;color:#ffffff;padding-top: 5px;border-radius:4px;z-index:1003;" data-toggle="ajax">onclick="Le.openDripEmailEditor();"
                    <span>
                        <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.drip.email.create.your.own'); ?></span>
                    </span>
                </a>-->
                <p style="margin-left:30px;width:80%;">Already have your emails planned out or know exactly what content you need?
                    Then you're ready to start building out your campaign.</p>
                <br>
                <div class="newbutton-container">
                    <li class="dropdown dropdown-menu-right" style="display: block;">

                        <a class="btn btn-nospin le-btn-default custom-preview-button blue-theme-bg"  onclick="Le.TriggerNewClick();" style="padding-top: 5px;border-radius:0px;z-index:1003;padding:10px;" data-toggle="dropdown" href="#">
                            <span><span class="" style="font-size:15px;"> <?php echo $view['translator']->trans('le.drip.email.create.your.own')?></span></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" style="margin-top: -90%;margin-right:-71%;">
                            <div class="insert-drip-options">
                                <div class='drip-options-panel'>
                                    <h1 style='font-size:16px;font-weight:bold;'><?php echo $view['translator']->trans('Which email builder would you like to use?')?></h1>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-6 editor_layout fl-left <?php echo $ismobile ? 'hide' : ''?>"  style="margin-left:10px;"><!--onclick="Le.setValueforNewButton('advance_editor',this);"-->
                                            <a href="<?php echo $view['router']->path('le_dripemail_email_action', ['objectId' => $entity->getId(), 'subobjectAction' => 'new', 'subobjectId' => 1]); ?>">
                                                <img height="100px" width="auto" src="<?php echo $view['assets']->getUrl('media/images/drag-drop.png')?>"/>
                                                <h4><?php echo $view['translator']->trans('le.email.editor.advance.header')?></h4>
                                                <br>
                                            </a>
                                        </div>
                                        <div class="col-md-6 editor_layout fl-left editor_select" style="margin-left:20px;"> <!--onclick="Le.setValueforNewButton('basic_editor',this);"-->
                                            <a href="<?php echo $view['router']->path('le_dripemail_email_action', ['objectId' => $entity->getId(), 'subobjectAction' => 'new', 'subobjectId' => 0]); ?>">
                                                <img height="100px" width="auto" src="<?php echo $view['assets']->getUrl('media/images/rich-text.png')?>"/>
                                                <h4><?php echo $view['translator']->trans('le.email.editor.basic.header.drip')?></h4>
                                                <br>
                                            </a>
                                        </div>
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

