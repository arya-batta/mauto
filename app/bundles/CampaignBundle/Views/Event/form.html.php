<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$isCondition =$eventType == 'condition' ? true : false;
$isSource    =$eventType == 'source' ? true : false;
$isAction    =$eventType == 'action' ? true : false;
$isDelayEvent= ($type == 'campaign.defaultdelay') ? true : false;
$header      ='';
$description ='';
$headerStyle = 'margin-top: 20px;';
if ($isSource) {
    $header     =$view['translator']->trans('mautic.point.trigger.header.edit');
    $description=$view['translator']->trans('le.campaign.source.trigger.desc');
} elseif ($isCondition) {
    $header     =$view['translator']->trans('le.campaign.edit.decision');
    $description=$view['translator']->trans('le.campaign.decision.desc');
    $headerStyle='margin-top: 5px;';
} elseif ($isAction && !$isDelayEvent) {
    $header     =$view['translator']->trans('le.campaign.edit.action');
    $description=$view['translator']->trans('le.campaign.action.desc');
} elseif ($isDelayEvent) {
    $header=$view['translator']->trans('le.campaign.edit.delay');
}
if ($isCondition) {
    $fields    = $form['properties']->vars['fields'];
    $index     = count($form['properties']['filters']->vars['value']) ? max(array_keys($form['properties']['filters']->vars['value'])) : 0;
    $templates = [
        'countries'         => 'country-template',
        'regions'           => 'region-template',
        'timezones'         => 'timezone-template',
        'select'            => 'select-template',
        'lists'             => 'leadlist-template',
        'deviceTypes'       => 'device_type-template',
        'deviceBrands'      => 'device_brand-template',
        'deviceOs'          => 'device_os-template',
        'emails'            => 'lead_email_received-template',
        'tags'              => 'tags-template',
        'stage'             => 'stage-template',
        'locales'           => 'locale-template',
        'globalcategory'    => 'globalcategory-template',
        'landingpage_list'  => 'landingpage_list-template',
        'score_list'        => 'score_list-template',
        'users'             => 'owner_id-template',
        'forms'             => 'formsubmit_list-template',
        'assets'            => 'asset_downloads_list-template',
        'drip_campaign'     => 'drip_email_received-template',
        'drip_campaign_list'=> 'drip_email_list-template',
        'listoptin'         => 'listoptin-template',
    ];
}
$addconditionbtn="<button type=\"button\" class=\"btn btn-default lead-list btn-filter-group\" data-filter-group='and'>Add a condition</button>";
?>

<div class="bundle-form">
    <div class="bundle-form-header mb-10">
        <h3><?php echo $header; ?></h3>
        <?php if (!empty($description)): ?>
        <h6 class="info-box-text" style="<?php echo $headerStyle; ?>"><?php echo $description; ?></h6>
        <?php endif; ?>
    </div>

    <?php echo $view['form']->start($form); ?>
    <div <?php echo ($isCondition || $isDelayEvent) ? 'class="hide"' : ''?> id="campaigneventgroup" style="display: flex" data-href="<?php echo $accessurl?>">
        <div style="width: 30%;">
        <?php echo $view['form']->widget($form['group']); ?>
        </div>
        <div style="width: 70%;margin-bottom: 20px;">
        <?php echo $view['form']->widget($form['subgroup']); ?>
        </div>
    </div>
    <?php if ($isCondition):?>
    <div class="form-group hide">
        <div style="margin-top:18px;" class="available-filters pl-0 col-md-6" data-prototype="<?php echo $view->escape($view['form']->widget($form['properties']['filters']->vars['prototype'], ['filterfields'=> $fields, 'addconditionbtn'=>$addconditionbtn])); ?>" data-index="<?php echo $index + 1; ?>">
            <select class="chosen form-control" id="available_filters" data-placeholder="Choose filter...">
                <option value=""></option>
                <?php
                foreach ($fields as $object => $field):
                    $header = $object;
                    $icon   = ($object == 'company') ? 'building' : 'user';

                    ?>
                    <optgroup label="<?php echo $view['translator']->trans('le.leadlist.'.$header); ?>">
                        <?php foreach ($field as $value => $params):
                            $list      = (!empty($params['properties']['list'])) ? $params['properties']['list'] : [];
                            $choices   = \Mautic\LeadBundle\Helper\FormFieldHelper::parseList($list, true, ('boolean' === $params['properties']['type']));
                            $list      = json_encode($choices);
                            $callback  = (!empty($params['properties']['callback'])) ? $params['properties']['callback'] : '';
                            $operators = (!empty($params['operators'])) ? $view->escape(json_encode($params['operators'])) : '{}';
                            ?>
                            <option value="<?php echo $view->escape($value); ?>"
                                    id="available_<?php echo $value; ?>"
                                    data-field-object="<?php echo $object; ?>"
                                    data-field-type="<?php echo $params['properties']['type']; ?>"
                                    data-field-list="<?php echo $view->escape($list); ?>"
                                    data-field-callback="<?php echo $callback; ?>"
                                    data-field-operators="<?php echo $operators; ?>"
                                    data-field-customobject="<?php echo $params['object']; ?>"
                                    class="segment-filter <?php echo $icon; ?>">

                                <?php echo $view['translator']->trans($params['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="clearfix"></div>
    </div>
    <div style="margin-bottom: 30px;" class="selected-filters" id="leadlist_filters">
        <div class='filter-group-template leadlist-filter-group filter-and-group'>
            <div class='filter-panel-holder'>
            </div>
            <?php echo $addconditionbtn?>
        </div>
        <div class="filter-and-group-holder">
            <?php echo $view['form']->widget($form['properties']['filters'], ['filterfields'=> $fields, 'addconditionbtn'=>$addconditionbtn]); ?>
        </div>
        <div class="leadlist-filter-group filter-or-group">
            <button type="button" class="btn btn-default lead-list btn-filter-group" data-filter-group='or'>Add another set of conditions</button>
        </div>
    </div>
    <?php endif; ?>
    <?php echo $view['form']->widget($form['canvasSettings']['droppedX']); ?>
    <?php echo $view['form']->widget($form['canvasSettings']['droppedY']); ?>

    <?php echo $view['form']->row($form['name']); ?>

    <?php if (isset($form['triggerMode'])): ?>
    <div<?php echo $hideTriggerMode ? ' class="hide"' : ''; ?>>
        <?php echo $view['form']->row($form['triggerMode']); ?>

        <div<?php echo ($form['triggerMode']->vars['data'] != 'date') ? ' class="hide"' : ''; ?> id="triggerDate">
            <label class="control-label"><?php echo $view['translator']->trans('le.campaign.delay.event.wait.till'); ?></label>
            <?php echo $view['form']->row($form['triggerDate']); ?>
        </div>

        <div<?php echo ($form['triggerMode']->vars['data'] != 'interval') ? ' class="hide"' : ''; ?> id="triggerInterval">
            <label class="control-label"><?php echo $view['translator']->trans('le.campaign.delay.event.wait.for'); ?></label>
            <div class="row">
                <div class="col-sm-4" style="margin-top:5px;">
                    <?php echo $view['form']->row($form['triggerInterval']); ?>
                </div>
                <div class="col-sm-2" style="margin-left: -170px;">
                    <?php echo $view['form']->row($form['triggerIntervalUnit']); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php echo $view['form']->end($form); ?>
</div>
<?php if ($isCondition): ?>
<div class="hide" id="templates">
    <?php foreach ($templates as $dataKey => $template): ?>
        <?php $attr = ($dataKey == 'tags') ? ' data-placeholder="'.$view['translator']->trans('le.lead.tags.select_or_create').'" data-no-results-text="'.$view['translator']->trans('le.lead.tags.enter_to_create').'" data-allow-add="true" onchange="Le.createLeadTag(this)"' : ''; ?>
        <select class="form-control not-chosen <?php echo $template; ?>" name="campaignevent[properties][filters][__name__][filter]" id="campaignevent_properties_filters___name___filter"<?php echo $attr; ?> disabled>
            <?php
            if (isset($form['properties']->vars[$dataKey])):
                foreach ($form['properties']->vars[$dataKey] as $value => $label):
                    if (is_array($label)):
                        echo "<optgroup label=\"$value\">\n";
                        foreach ($label as $optionValue => $optionLabel):
                            echo "<option value=\"$optionValue\">$optionLabel</option>\n";
                        endforeach;
                        echo "</optgroup>\n";
                    else:
                        if ($dataKey == 'lists' && (isset($currentListId) && (int) $value === (int) $currentListId)) {
                            continue;
                        }
                        echo "<option value=\"$value\">$label</option>\n";
                    endif;
                endforeach;
            endif;
            ?>
        </select>
    <?php endforeach; ?>
</div>
<?php endif; ?>