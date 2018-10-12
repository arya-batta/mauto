<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

//dump($campaignEvents);
$sourcefound=false;
foreach ($campaignEvents as $event):
    if ($event['eventType'] == 'source') {
        $sourcefound=true;
        break;
    }
endforeach;
$isAdmin=$view['security']->isAdmin();
?>
<div class="hide builder campaign-builder live">
    <div class="btns-builders custom-campaign-builder">
        <?php echo $view['form']->start($form); ?>
        <!-- start: box layout -->
            <!-- container -->
            <div class="col-md-6 height-auto">
                    <div class="row" style="width:170%;">
                        <div class="col-md-6">
                            <?php echo $view['form']->row($form['name']); ?>
                        </div>
                        <div class="col-md-4">
                            <?php echo $view['form']->row($form['category']); ?>
                        </div>
                    </div>
                    <div class="row hide">
                        <div class="col-md-12">
                            <?php echo $view['form']->row($form['description']); ?>
                        </div>
                    </div>
                </div>
            <div class="col-md-3 bg-white height-auto hide">
                <div class="pr-lg pl-lg pt-md pb-md">
                    <div class="hide">
                        <?php
                        echo $view['form']->row($form['isPublished']);
                        echo $view['form']->row($form['publishUp']);
                        echo $view['form']->row($form['publishDown']);
                        ?>
                    </div>
                </div>
            </div>
        <div class="campaign-custom-button-div">
            <?php echo $view['form']->end($form); ?>
        <!--<button type="button" class="btn btn-primary btn-close-campaign-builder campaign-custom-close-button"
                onclick="Mautic.closeCampaignBuilder();">
            <?php echo $view['translator']->trans('mautic.core.close.builder'); ?>
        </button>-->
            <button type="button" class="btn btn-primary btn-save-builder campaign-custom-save-button" onclick="Mautic.saveCampaignFromBuilder();">
                <?php echo $view['translator']->trans('mautic.core.form.saveandclose'); ?>
            </button>
            <button type="button" class="btn btn-primary btn-apply-builder campaign-custom-apply-button" onclick="Mautic.applyCampaignFromBuilder();">
                <?php echo $view['translator']->trans('mautic.email.beeeditor.save'); ?>
            </button>
            <div class="custom-fields">
            <button type="button"  data-toggle="tooltip" title="<?php echo $view['translator']->trans('le.campaign.startcampaign.tooltip'); ?>" data-placement="bottom" id="campaignPublishButton" class="campaign-custom-btn background-orange" value="publish" onclick="Mautic.publishCampaign();">
                <?php echo $view['translator']->trans('Start Workflow'); ?>
            </button>
                <div id="flash">
                    <span></span>
                </div>
        </div>
        </div>
    </div>
    <div id="builder-errors" class="alert alert-danger" role="alert" style="display: none;">test</div>
    <div  id='campaign-request-url' data-href="<?php echo $view['router']->path(
        'mautic_campaignevent_action',
        [
            'objectAction' => 'objectAction',
        ]
    ); ?>"></div>
    <div class="builder-content">
        <div class="workflow-canvas">
        </div>
    </div>
    <div class="campaign-statistics minimized">
        <?php if ($actions || $decisions || $conditions): ?>
            <div class="active tab-pane fade in bdr-w-0" id="actions-container" style="">
                <div class="modal-header campaign-model-header" style="height:68px;">
                    <?php if ($actions):?>
                        <p style="margin-top: -10px;font-weight: bold;"><?php echo $view['translator']->trans('mautic.core.stats'); ?></p>
                    <ul class=" ui-corner-top btn btn-default btn-group ui-tabs-selected" role = "tab" id = "ui-tab-stat-header1">
                     <p style="float:left;font-size:14px;font-weight: bold;"><?php echo $view['translator']->trans('mautic.core.actions'); ?></p>
                    </ul>
                    <ul class=" ui-corner-top btn btn-default btn-group ui-tabs-selected hide" role = "tab" id = "ui-tab-stat-header1">
                            <p style="float:left;font-size:14px;font-weight: bold;"><?php echo $view['translator']->trans('mautic.campaign.event.decisions.header'); ?></p>
                    </ul>
                    <?php endif; ?>
                    <?php if ($decisions || $conditions):?>
                    <ul class=" ui-corner-top btn btn-group <?php if (empty($actions)) {
        echo 'ui-tabs-selected';
    }?>" role = "tab" id = "ui-tab-stat-header2">
                    <p style="float:left;font-size:14px;font-weight: bold;"><?php echo $view['translator']->trans('le.campaign.decisions.stat'); ?></p>
                    </ul>
                    <?php endif; ?>
                    <a href="#" onclick="Mautic.CloseStatisticsWidget();">
                        <span aria-hidden="true" id="campaignStatistics"
                              style="float: right;font-size: 27px;background-color: #ec407a;padding-left: 8px;padding-right: 8px;margin-top: -11px;margin-right: -10px;" value="open">
                            <i id="campaginStatClass" style="margin-bottom: 10px;font-size: 21px;" class="fa fa-angle-double-right"></i></span></a>
                </div>
                <div id="fragment-stat-1" class="ui-tabs-panel">
                    <?php echo $actions; ?>
                </div>
                <div id="fragment-stat-2" class="ui-tabs-panel <?php if (!empty($actions)) {
        echo 'hide';
    }?>">
                    <?php echo $decisions; ?>
                    <?php echo $conditions; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
<!-- dropped coordinates -->
<input type="hidden" value="" id="droppedX"/>
<input type="hidden" value="" id="droppedY"/>
<input type="hidden" value="<?php echo $view->escape($campaignId); ?>" id="campaignId"/>

<?php echo $view->render(
    'MauticCoreBundle:Helper:modal.html.php',
    [
        'id'            => 'CampaignEventModal',
        'header'        => false,
        'footerButtons' => true,
        'dismissible'   => false,
        'size'          => 'clg',
    ]
);
echo $view->render('MauticCampaignBundle:Campaign\Builder:steps_model.html.php',
    []
);
?>
<script>

    <?php if (!empty($canvasSettings)): ?>
    Mautic.campaignBuilderCanvasSettings =
    <?php echo $canvasSettings ?>;//json_encode($canvasSettings, JSON_PRETTY_PRINT);
    <?php endif; ?>
    <?php
$acions      =$eventSettings['action'];
$eventoptions=[];
foreach ($acions as $key => $value) {
    if ($key != 'campaign.defaultaction' && $key != 'campaign.defaultdelay' && $key != 'campaign.defaultexit') {
        $options             =[];
        $options['label']    =$value['label'];
        $options['desc']     =$value['description'];
        $options['eventtype']='action';
        $options['category'] =$key;
        $options['order']    =$value['order'];
        $options['group']    =$value['group'];
        $eventoptions[]      =$options;
    }
}
     $campaigngroupoptions = [
        ['label'=> 'LeadsEngage', 'order'=> 1],
/*        ['label'=> 'Drip', 'order'=> 2],
        ['label'=> 'Facebook', 'order'=> 3],*/
    ];
    $sources      =$eventSettings['source'];
    $sourceoptions=[];
    foreach ($sources as $key => $value) {
        if ($key != 'campaign.defaultsource') {
            $options             =[];
            $options['label']    =$value['label'];
            $options['desc']     =$value['description'];
            $options['eventtype']='source';
            $options['category'] =$key;
            $options['order']    =$value['order'];
            $options['group']    =$value['group'];
            $sourceoptions[]     =$options;
        }
    }

?>
    Mautic.campaignBuilderEventOptions =
    <?php echo json_encode($eventoptions, JSON_PRETTY_PRINT); ?>;
    Mautic.campaignBuilderSourceOptions =
    <?php echo json_encode($sourceoptions, JSON_PRETTY_PRINT); ?>;
    Mautic.campaignBuilderGroupOptions =
    <?php echo json_encode($campaigngroupoptions, JSON_PRETTY_PRINT); ?>;
</script>
