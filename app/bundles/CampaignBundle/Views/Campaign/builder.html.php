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
$isAdmin     =$view['security']->isAdmin();
$wfstatelabel='le.campaign.start.workflow.label';
$wfstateclass='btn-wf-state-start';
$wfstate     ='false';
if ($entity->isPublished()) {
    $wfstatelabel='le.campaign.stop.workflow.label';
    $wfstateclass='btn-wf-state-stop';
    $wfstate     ='true';
}
?>
<div class="hide builder campaign-builder live">
    <div class="btns-builders custom-campaign-builder">
        <?php echo $view['form']->start($form); ?>
        <!-- start: box layout -->
            <!-- container -->
            <div  style="float: left;" class="col-md-7">
                    <div class="row">
                        <div class="hide"style="width: 8%;float: left;margin-top: 10px;">
                            <?php echo $view['form']->label($form['name']); ?>
                        </div>
                        <div style="width: 63%;float: left;margin-top: 10px;margin-left: 10px;" class="<?php echo (count($form['name']->vars['errors'])) ? ' has-error' : ''; ?>">
                            <?php echo $view['form']->widget($form['name']); ?>
                            <?php echo $view['form']->errors($form['name']); ?>
                        </div>
                        <div  class="hide"style="width: 8%;float: left;margin-top: 10px;margin-left: 7px; ">
                            <?php echo $view['form']->label($form['category']); ?>                        </div>
                        <div style="width: 30%;float: left;margin-left: 22px;margin-top: 5px;">
                            <?php echo $view['form']->widget($form['category']); ?>
                        </div>
                       <!--<div style="float: left;margin-left: 15px;color:#fff !important;">
                            <div class="form-group">
                                <label class="control-label">Show Analytics</label>        <div class="choice-wrapper">
                                    <div class="btn-group btn-block" data-toggle="buttons">
                                        <label class="btn le-btn-published le-btn-small  btn-no btn-danger active">
                                            <input type="radio" id="campaign_showAnalytics_0" onchange="Le.toggleYesNoButtonClass(mQuery(this).attr('id'));" style="width: 1px; height: 1px; top: 0; left: 0; margin-top: 0;" autocomplete="false" value="0">            <span>No</span>
                                        </label>
                                        <label class="btn btn-default le-btn-published le-btn-small  btn-yes">
                                            <input type="radio" id="campaign_showAnalytics_1" name="campaign[isPublished]" onchange="Le.toggleYesNoButtonClass(mQuery(this).attr('id'));" style="width: 1px; height: 1px; top: 0; left: 0; margin-top: 0;" autocomplete="false" value="1" checked="checked">            <span>Yes</span>
                                        </label>
                                    </div>                    </div>
                            </div>
                        </div>-->
                    </div>
                    <div class="hide row">
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
                </div>4
            </div>
        <div class="campaign-custom-button-div ">
            <?php echo $view['form']->end($form); ?>
            <button type="button" class="waves-effect <?php echo $wfstateclass?>" onclick="Le.changeCampaignState(this);" data-wf-state="<?php echo $wfstate?>">
                <?php echo $view['translator']->trans($wfstatelabel); ?>
            </button>
            <button type="button" class="waves-effect btn-apply-builder" onclick="Le.applyCampaignFromBuilder();">
                <?php echo $view['translator']->trans('le.email.beeeditor.apply'); ?>
            </button>
            <button type="button" class="waves-effect btn-close-builder"
                onclick="Le.closeCampaignBuilder();">
            <?php echo $view['translator']->trans('mautic.core.close.builder'); ?>
            </button>
            <button type="button" class="waves-effect btn-save-builder" onclick="Le.saveCampaignFromBuilder();">
                <?php echo $view['translator']->trans('mautic.core.form.saveandclose'); ?>
            </button>
<!--            <div class="custom-fields">-->
<!--            <button type="button"  data-toggle="tooltip" title="--><?php //echo $view['translator']->trans('le.campaign.startcampaign.tooltip');?><!--" data-placement="bottom" id="campaignPublishButton" class="campaign-custom-btn --><?php //echo $entity->isPublished() ? 'background-pink' : 'background-orange'?><!--"  onclick="Le.publishCampaign();">-->
<!--                --><?php //echo $view['translator']->trans($entity->isPublished() ? 'Stop Workflow' : 'Start Workflow');?>
<!--            </button>-->
                <div id="flash">
                    <span></span>
                </div>
<!--        </div>-->
        </div>
    </div>
    <div id="builder-errors" class="alert alert-danger" role="alert" style="display: none;">test</div>
    <div  id='campaign-request-url' data-href="<?php echo $view['router']->path(
        'le_campaignevent_action',
        [
            'objectAction' => 'objectAction',
        ]
    ); ?>"></div>
    <div class="builder-content">
        <div class="workflow-canvas">
        </div>
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
    Le.campaignBuilderCanvasSettings =
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
    //allowedEvents 0 - both 1 - source 2 - action
     $campaigngroupoptions = [
         ['label'=> $view['content']->getProductBrandName(), 'order'=> 1, 'visibleFor' => 0],
         ['label'=> 'Facebook', 'order'=> 2, 'visibleFor' => 0],
         ['label'=> 'Instapage', 'order'=> 3, 'visibleFor' => 1],
         ['label'=> 'Unbounce', 'order'=> 4, 'visibleFor' => 1],
         ['label'=> 'Calendly', 'order'=> 5, 'visibleFor' => 1],
         /*        ['label'=> 'Drip', 'order'=> 2],*/
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
    Le.campaignBuilderStatistics =
    <?php echo json_encode($statistics, JSON_PRETTY_PRINT); ?>;
    Le.campaignBuilderEventOptions =
    <?php echo json_encode($eventoptions, JSON_PRETTY_PRINT); ?>;
    Le.campaignBuilderSourceOptions =
    <?php echo json_encode($sourceoptions, JSON_PRETTY_PRINT); ?>;
    Le.campaignBuilderGroupOptions =
    <?php echo json_encode($campaigngroupoptions, JSON_PRETTY_PRINT); ?>;
</script>
