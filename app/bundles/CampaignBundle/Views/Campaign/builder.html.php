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
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $view['form']->row($form['name']); ?>
                        </div>
                        <div class="col-md-6">
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
                <?php echo $view['translator']->trans('mautic.core.close'); ?>
            </button>
            <button type="button" class="btn btn-primary btn-apply-builder campaign-custom-apply-button" onclick="Mautic.applyCampaignFromBuilder();">
                <?php echo $view['translator']->trans('mautic.core.form.save'); ?>
            </button>
            <div class="custom-fields">
            <button type="button" id="campaignPublishButton" class="campaign-custom-btn background-orange" value="publish" onclick="Mautic.publishCampaign();">
                <?php echo $view['translator']->trans('Start Campaign'); ?>
            </button>

        </div>
        </div>
    </div>
    <div id="builder-errors" class="alert alert-danger" role="alert" style="display: none;">test</div>
    <div  id='campaign-new-request-url' data-href="<?php echo $view['router']->path(
        'mautic_campaignevent_action',
        [
            'objectAction' => 'new',
        ]
    ); ?>"></div>
    <div class="builder-content">
        <div id="CampaignCanvas">
            <div id="CampaignEvent_newsource<?php if ($sourcefound) {
        echo '_hide';
    } ?>" class="text-center list-campaign-source list-campaign-leadsource">
                <div class="campaign-event-content">
                    <div>
                        <span class="campaign-event-name ellipsis">
                            <i class="mr-sm fa fa-users"></i> <?php echo $view['translator']->trans('mautic.campaign.add_new_source'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php
//            foreach ($campaignSources as $source):
//                echo $view->render('MauticCampaignBundle:Source:index.html.php', $source);
//            endforeach;

            foreach ($campaignEvents as $event):
                echo $view->render('MauticCampaignBundle:Event:generic.html.php',
                    ['event' => $event, 'campaignId' => $campaignId]);
            endforeach;

            echo $view->render('MauticCampaignBundle:Campaign\Builder:index.html.php',
                [
                    'campaignSources' => $campaignSources,
                    'eventSettings'   => $eventSettings,
                    'campaignId'      => $campaignId,
                ]
            );
            ?>

        </div>
    </div>
    <div class="campaign-statistics minimized">
        <?php if ($actions): ?>
            <div class="active tab-pane fade in bdr-w-0" id="actions-container" style="">
                <div class="modal-header campaign-model-header" style="height:50px;">
                    <p style="float:left;font-size:14px;font-weight: bold;"><?php echo $view['translator']->trans('le.campaign.actions.stat'); ?></p>
                    <a href="#" onclick="Mautic.CloseStatisticsWidget();"><span aria-hidden="true" id="campaignStatistics" style="float:right;font-size:14px;background-color: #ec407a;padding-left: 3px;padding-right: 5px;" value="open">&gt;</span></a>
                </div>
                <?php echo $actions; ?>
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
    ]
);

?>
<script>
    <?php if (!empty($canvasSettings)): ?>
    Mautic.campaignBuilderCanvasSettings =
    <?php echo json_encode($canvasSettings, JSON_PRETTY_PRINT); ?>;
    Mautic.campaignBuilderCanvasSources =
    <?php echo json_encode($campaignSources, JSON_PRETTY_PRINT); ?>;
    Mautic.campaignBuilderCanvasEvents =
    <?php echo json_encode($campaignEvents, JSON_PRETTY_PRINT); ?>;
    <?php endif; ?>
    Mautic.campaignBuilderConnectionRestrictions =
    <?php echo json_encode($eventSettings['connectionRestrictions'], JSON_PRETTY_PRINT); ?>;
    <?php
$acions      =$eventSettings['action'];
$eventoptions=[];
foreach ($acions as $key => $value) {
    $options             =[];
    $options['label']    =$value['label'];
    $options['desc']     =$value['description'];
    $options['eventtype']='action';
    $options['category'] =$key;
    $options['order']    =$value['order'];
    $options['group']    =$value['group'];
    $eventoptions[]      =$options;
}
     $campaigngroupoptions = [
        ['label'=> 'LeadsEngage', 'order'=> 1],
        ['label'=> 'Drip', 'order'=> 2],
        ['label'=> 'Facebook', 'order'=> 3],
    ];
    $sources      =$eventSettings['source'];
    $sourceoptions=[];
    foreach ($sources as $key => $value) {
        $options             =[];
        $options['label']    =$value['label'];
        $options['desc']     =$value['description'];
        $options['eventtype']='source';
        $options['category'] =$key;
        $options['order']    =$value['order'];
        $options['group']    =$value['group'];
        $sourceoptions[]     =$options;
    }

?>
    Mautic.campaignBuilderEventOptions =
    <?php echo json_encode($eventoptions, JSON_PRETTY_PRINT); ?>;
    Mautic.campaignBuilderSourceOptions =
    <?php echo json_encode($sourceoptions, JSON_PRETTY_PRINT); ?>;
    Mautic.campaignBuilderGroupOptions =
    <?php echo json_encode($campaigngroupoptions, JSON_PRETTY_PRINT); ?>;
</script>
