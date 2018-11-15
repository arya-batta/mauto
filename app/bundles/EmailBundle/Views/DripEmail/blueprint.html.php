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
                <a class="btn btn-default text-primary le-btn-default" onclick="Le.openBluePrintPage();" style="background-color: #ec407a;color:#ffffff;padding-top: 5px;float: right;margin-right: 8%;border-radius:4px;z-index:1003;" data-toggle="ajax">
                    <span>
                        <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.drip.email.browse.blueprint'); ?></span>
                    </span>
                </a>
                <br>
                <br>
                <br>
            </div>
        </div>
        <div class="col-md-6">
            <div>
                <img height="125px" width="auto" src="<?php echo $view['assets']->getUrl('media/images/startfromscratch.png')?>"/>
                <h2 class="drip-col-stats"><?php echo $view['translator']->trans('mautic.campaign.type.blanktemplate.header')?></h2>
                <br>
                <a class="btn btn-default text-primary le-btn-default" onclick="Le.openDripEmailEditor();" style="background-color: #ec407a;color:#ffffff;padding-top: 5px;float: right;margin-right: 8%;border-radius:4px;z-index:1003;" data-toggle="ajax">
                    <span>
                        <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.drip.email.create.your.own'); ?></span>
                    </span>
                </a>
                <br>
                <br>
                <br>
            </div>
        </div>
    </div>
</div>

