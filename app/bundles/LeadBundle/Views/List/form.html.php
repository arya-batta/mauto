<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('leContent', 'leadlist');
$fields = $form->vars['fields'];
//dump($fields);
$id     = $form->vars['data']->getId();
$index  = count($form['filters']->vars['value']) ? max(array_keys($form['filters']->vars['value'])) : 0;

if (!empty($id)) {
    $name   = $form->vars['data']->getName();
    $header = $view['translator']->trans('le.lead.list.header.edit', ['%name%' => $name]);
} else {
    $header = $view['translator']->trans('le.lead.list.header.new');
}
$view['slots']->set('headerTitle', $header);

$templates = [
    'countries'        => 'country-template',
    'regions'          => 'region-template',
    'timezones'        => 'timezone-template',
    'select'           => 'select-template',
    'lists'            => 'leadlist-template',
    'deviceTypes'      => 'device_type-template',
    'deviceBrands'     => 'device_brand-template',
    'deviceOs'         => 'device_os-template',
    'emails'           => 'lead_email_received-template',
    'tags'             => 'tags-template',
    'stage'            => 'stage-template',
    'locales'          => 'locale-template',
    'globalcategory'   => 'globalcategory-template',
    'landingpage_list' => 'landingpage_list-template',
    'users'            => 'owner_id-template',
    'forms'            => 'formsubmit_list-template',
    'assets'           => 'asset_downloads_list-template',
];

$mainErrors     = ($view['form']->containsErrors($form, ['filters'])) ? 'class="text-danger"' : '';
$filterErrors   = ($view['form']->containsErrors($form['filters'])) ? 'class="text-danger"' : '';
$addconditionbtn="<button type=\"button\" class=\"btn btn-default btn-filter-group\" data-filter-group='and'>Add a condition</button>";
?>

<?php echo $view['form']->start($form); ?>
<ul class="bg-auto nav nav-pills nav-wizard pr-md pl-md" style="margin-left: -7px;">
    <li class="active detail" id="detailstab" onclick="Le.addHide()">
        <a href="#details" style="padding: 3px 47px;" role="tab" data-toggle="tab"<?php echo $mainErrors; ?>>
            <div class="content-wrapper-first">
                <div><span class="small-xx">Step 01</span></div>
                <label><?php echo $view['translator']->trans('le.core.segment.setup'); ?>
                    <?php if ($mainErrors): ?>
                        <i class="fa fa-warning"></i>
                    <?php endif; ?> </label>
            </div>
        </a>
    </li>
    <li id="filterstab" data-toggle="tooltip" title="" onclick="Le.removeHide()" data-placement="top" data-original-title="<?php echo $view['translator']->trans('le.lead.lead.segment.add.help'); ?>">
        <a href="#filters" style="padding: 3px 38px;" role="tab" data-toggle="tab"<?php echo $filterErrors; ?>>
            <div class="content-wrapper-first">
                <div><span class="small-xx">Step 02</span></div>
                <label>  <?php echo $view['translator']->trans('le.core.segment.filter'); ?>
                    <?php if ($filterErrors): ?>
                        <i class="fa fa-warning"></i>
                    <?php endif; ?> </label>
            </div>
        </a>
    </li>
</ul>
<div class="box-layout">
    <div class="col-md-8 bg-white height-auto">
        <div class="row">
            <div class="col-xs-12">
                <!-- start: tab-content -->
                <div class="tab-content pa-md">
                    <div class="tab-pane fade in active bdr-w-0" id="details">
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo $view['form']->row($form['name']); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo $view['form']->row($form['alias']); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo $view['form']->row($form['isGlobal']); ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo $view['form']->row($form['isPublished']); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <?php echo $view['form']->row($form['description']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade bdr-w-0" id="filters">
                        <div class="form-group hide">
                            <div class="available-filters mb-md pl-0 col-md-4" data-prototype="<?php echo $view->escape($view['form']->widget($form['filters']->vars['prototype'], ['filterfields'=> $fields, 'addconditionbtn'=>$addconditionbtn])); ?>" data-index="<?php echo $index + 1; ?>">
                                <select class="chosen form-control" id="available_filters">
                                    <option value="" id="available_default"></option>
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
                        <div class="selected-filters" style="margin-bottom: 35px;" id="leadlist_filters">
                            <div class='filter-group-template leadlist-filter-group filter-and-group'>
                                <div class='filter-panel-holder'>
                                </div>
                                <?php echo $addconditionbtn?>
                            </div>
                            <div class="filter-and-group-holder">
                            <?php echo $view['form']->widget($form['filters'], ['filterfields'=> $fields, 'addconditionbtn'=>$addconditionbtn]); ?>
                            </div>
                            <div class="leadlist-filter-group filter-or-group">
                                <button type="button" class="btn btn-default btn-filter-group" data-filter-group='or'>Add another set of conditions</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 <!--   <div class="col-md-3 bg-white height-auto bdr-l">
        <div class="pr-lg pl-lg pt-md pb-md">
            <?php /*echo $view['form']->row($form['isGlobal']); */?>
            <?php /*echo $view['form']->row($form['isPublished']); */?>
        </div>
    </div>-->


<!--<div class="pr-lg pl-lg pt-md pb-md " id="segment_filters">-->
<!--    <div class="alert alert-info " id="form-field-placeholder" style="width: 92%;margin-left: 14px;">-->
<!--        <p>--><?php //echo $view['translator']->trans('le.lead.list.filter.notes');?><!--</p>-->
<!--    </div>-->
<!--    <div class="available-filters  mb-md pl-0 col-md-4 " data-prototype="--><?php //echo $view->escape($view['form']->widget($form['filters']->vars['prototype']));?><!--" data-index="--><?php //echo $index + 1;?><!--">-->
<!--        --><?php
//        foreach ($fields as $object => $field):
//            $header = $object;
//             if ($object == 'lead'):
//                $icon   = 'fa fa-user';
//             elseif ($object == 'list_points'):
//                $icon   = 'fa fa-question-circle';
//             elseif ($object == 'list_tags'):
//                 $icon   = 'fa fa-tags';
//             elseif ($object == 'list_leadlist'):
//                 $icon   = 'fa fa-pie-chart';
//             elseif ($object == 'list_categories'):
//                 $icon   = 'fa fa-folder';
//             elseif ($object == 'date_activity'):
//                 $icon   = 'fa fa-clock-o';
//             elseif ($object == 'emails'):
//                 $icon   = 'fa fa fa-envelope';
//             elseif ($object == 'pages'):
//                 $icon   = 'fa fa fa-newspaper-o';
//             elseif ($object == 'forms'):
//                 $icon   = 'fa fa-edit';
//             elseif ($object == 'assets'):
//                 $icon   = 'fa fa-folder-open-o';
//             else:
//                $icon   = 'fa-user';
//             endif;
//                $closedata='#segment-filter-block_'.$header;
//?>
<!--            <div class="hr-segment-expand">-->
<!--                <a href="javascript:void(0)" onclick="Mautic.segment_filter(this.id)" id="--><?php //echo $header;?><!--"-->
<!--                   class="arrow segment-collapse" >-->
<!--                    <span style="font-size:12px"  <i class="--><?php //echo $icon?><!--"> </i>--><?php //echo $view['translator']->trans('le.leadlist.'.$header);?><!--</span><i class="caret" style="float: right !important;margin-top: 8px;"></i></a>-->
<!--                <div class=" filter_option"  style="overflow: hidden;height: auto;display:none;padding: 12px;margin-left: -19px;margin-right: -66px;"   id="segment-filter-block_--><?php //echo $header?><!--">-->
<!--                    --><?php //foreach ($field as $value => $params):
//                        $list      = (!empty($params['properties']['list'])) ? $params['properties']['list'] : [];
//
//                        $choices   = \Mautic\LeadBundle\Helper\FormFieldHelper::parseList($list, true, ('boolean' === $params['properties']['type']));
//                        $list      = json_encode($choices);
//                        $callback  = (!empty($params['properties']['callback'])) ? $params['properties']['callback'] : '';
//                        $operators = (!empty($params['operators'])) ? $view->escape(json_encode($params['operators'])) : '{}';
//?>
<!---->
<!--                            <div class="col-md-6">-->
<!--                        <a  onclick="Mautic.addSegementFilter(this)"-->
<!--                            value="--><?php //echo $view->escape($value);?><!--"-->
<!--                            id="--><?php //echo $value;?><!--"-->
<!--                            data-field-object="--><?php //echo $object;?><!--"-->
<!--                            data-field-type="--><?php //echo $params['properties']['type'];?><!--"-->
<!--                            data-field-list="--><?php //echo $view->escape($list);?><!--"-->
<!--                            data-field-callback="--><?php //echo $callback;?><!--"-->
<!--                            data-field-operators="--><?php //echo $operators;?><!--"-->
<!--                            data-field-customobject="--><?php //echo $params['object'];?><!--"-->
<!--                            class="segment-filter-badge ">-->
<!--                        <icon class="fa fa-plus" />-->
<!--                            --><?php //echo $view['translator']->trans($params['label']);?>
<!--                        </a></div>-->
<!---->
<!--                    --><?php //endforeach;?>
<!--                </div>-->
<!--            </div>-->
<!--        --><?php //endforeach;?>
<!--    </div>-->
<!--</div>-->
</div>
<?php echo $view['form']->end($form); ?>

<div class="hide" id="templates">
    <?php foreach ($templates as $dataKey => $template): ?>
        <?php $attr = ($dataKey == 'tags') ? ' data-placeholder="'.$view['translator']->trans('le.lead.tags.select_or_create').'" data-no-results-text="'.$view['translator']->trans('le.lead.tags.enter_to_create').'" data-allow-add="true" onchange="Le.createLeadTag(this)"' : ''; ?>
        <select class="form-control not-chosen <?php echo $template; ?>" name="leadlist[filters][__name__][filter]" id="leadlist_filters___name___filter"<?php echo $attr; ?> disabled>
            <?php
            if (isset($form->vars[$dataKey])):
                foreach ($form->vars[$dataKey] as $value => $label):
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
