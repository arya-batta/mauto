<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Symfony\Component\Form\FormView;

$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('leContent', 'email');

//$dynamicContentPrototype = $form['dynamicContent']->vars['prototype'];

//if (empty($form['dynamicContent']->children[0]['filters']->vars['prototype'])) {
//    $filterBlockPrototype = null;
//} else {
//    $filterBlockPrototype = $form['dynamicContent']->children[0]['filters']->vars['prototype'];
//}

//if (empty($form['dynamicContent']->children[0]['filters']->children[0]['filters']->vars['prototype'])) {
//    $filterSelectPrototype = null;
//} else {
//    $filterSelectPrototype = $form['dynamicContent']->children[0]['filters']->children[0]['filters']->vars['prototype'];
//}

$variantParent = $email->getVariantParent();
$isExisting    = $email->getId();
$isCloneOp     =!empty($isClone) && $isClone;
$subheader     = ($variantParent) ? $view['translator']->trans('mautic.core.variant_of', [
    '%name%'   => $email->getName(),
    '%parent%' => $variantParent->getName(),
]) : '';

$header = $isExisting ?
    $view['translator']->trans('le.email.header.edit',
        ['%name%' => $email->getName()]) :
    $view['translator']->trans('le.email.header.new');

$view['slots']->set('headerTitle', $header.$subheader);

$emailType = $form['emailType']->vars['data'];

if (!isset($attachmentSize)) {
    $attachmentSize = 0;
}

//$templates = [
//    'select'    => 'select-template',
//    'countries' => 'country-template',
//    'regions'   => 'region-template',
//    'timezones' => 'timezone-template',
//    'stages'    => 'stage-template',
//    'locales'   => 'locale-template',
//];

$attr                 = $form->vars['attr'];
$isAdmin              =$view['security']->isAdmin();
$isCodeMode           = ($email->getTemplate() === 'le_code_mode');
$isbasiceditor        = ($email->getTemplate() == null || $email->getTemplate() == '');
$formcontainserror    =$view['form']->containsErrors($form);
$activatebasiceditor  =($formcontainserror || $isCloneOp || $isMobile) && $isbasiceditor ? 'active' : '';
$activateadvanceeditor=($formcontainserror || $isCloneOp || !$isMobile) && !$isbasiceditor ? 'active' : '';
$hidebasiceditor      =($formcontainserror || $isCloneOp || !$isMobile) && !$isbasiceditor ? 'hide' : '';
$hideadvanceeditor    =($formcontainserror || $isCloneOp || $isMobile) && $isbasiceditor ? 'hide' : '';
$activateotherconfig  ='';
$infoulactive         = 'ui-tabs-selected ui-state-active';
$settingsulactive     = '';
$infohide             = '';
$settingshide         = 'ui-tabs-hide';
if ($formcontainserror) {
    $activatebasiceditor  ='';
    $activateadvanceeditor='';
    $activateotherconfig  ='active in';
    if ((!count($form['name']->vars['errors'])) && !(count($form['subject']->vars['errors'])) && (count($form['fromAddress']->vars['errors']))) {
        $settingsulactive = 'ui-tabs-selected ui-state-active';
        $infoulactive     = '';
        $settingshide     = '';
        $infohide         = 'ui-tabs-hide';
    }
}
$custombutton = [
    [
        'name'    => 'beeeditor',
        'btnText' => 'le.drip.email.open.editor',
        'attr'    => [
            'class'   => 'btn btn-default btn-save le-btn-default btn-copy m_down waves-effect',
            'onclick' => "Le.launchBeeEditor('emailform', 'email');",
        ],
    ],
];
$isgoogletags= false; //$email->getGoogletags();

$filter_fields    = $form['recipients']->vars['fields'];
$filter_index     = count($form['recipients']['filters']->vars['value']) ? max(array_keys($form['recipients']['filters']->vars['value'])) : 0;
$filter_templates = [
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
$customHtml            = $email->getCustomHtml();
$filter_addconditionbtn="<button type=\"button\" class=\"btn btn-default lead-list btn-filter-group waves-effect add-contition\" data-filter-group='and'>Add a condition</button>";
?>
<?php echo $view['form']->start($form, ['attr' => $attr]); ?>
<div id="page-wrap" class="align-tab-center">
    <div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all tab-pane fade in active bdr-rds-0 bdr-w-0">
        <ul class="nav nav-pills nav-wizard ui-helper-reset ui-helper-clearfix ui-widget-header">
            <li class="ui-state-default ui-corner-top btn-group modal-footer <?php echo $infoulactive; ?>" role = "tab" id = "ui-tab-header1" rel = 1>
                <a class="text-start" id="info_tab"><div class="content-wrapper-first">
                        <div><span class="small-xx">Step 01</span></div>
                        <label><?php echo $view['translator']->trans('le.core.email.name'); ?></label>
                    </div></a>
            </li>
            <li class="ui-state-default ui-corner-top btn-group modal-footer" role = "tab" id = "ui-tab-header2" rel = 2>
                <a class="text-start" style="padding: 0px 30px;"><div class="content-wrapper-first">
                        <div><span class="small-xx">Step 02</span></div>
                        <label><?php echo $view['translator']->trans('le.core.email.compose'); ?></label>
                    </div></a>
            </li>
            <li class="ui-state-default ui-corner-top btn-group modal-footer <?php echo $isVariant ? 'disable_ele' : '' ?>" role = "tab" id = "ui-tab-header3" rel = 3>
                <a class="text-start" style="padding: 0px 35px;"> <div class="content-wrapper-first">
                        <div><span class="small-xx">Step 03</span></div>
                        <label><?php echo $view['translator']->trans('le.core.email.recipients'); ?></label>
                    </div></a>
            </li>
            <li class="ui-state-default ui-corner-top btn-group modal-footer <?php echo $settingsulactive; ?>" role = "tab" id = "ui-tab-header4" rel = 4>
                <a class="text-start" style="padding: 0px 35px;"> <div class="content-wrapper-first">
                        <div><span class="small-xx">Step 04</span></div>
                        <label><?php echo $view['translator']->trans('le.core.email.setup'); ?></label>
                    </div></a>
            </li>
        </ul>
            <div class="le-builder-btn hide col-md-6<?php  echo $hideadvanceeditor; ?>" style="width: 65%;float: right;">
                <div id="builder_btn" class="hide" style="margin-left: 385px;position: absolute;top: -37px;right: 0;">

                    <a class="btn btn-default text-primary btn-beeditor le-btn-default blue-theme-bg waves-effect" onclick="Le.showTemplateview();" style="color:#ffffff;padding-top: 7px;float: right;margin-right: 10%;border-radius:4px;z-index:1003;margin-right: 145px;" data-toggle="ajax">
                        <span>
                            <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.core.change.template'); ?></span>
                        </span>
                    </a>
                </div>
            </div>
        <div id="fragment-1" class="ui-tabs-panel <?php echo $infohide?>">
            <div class="fragment-1-buttons fixed-header">
                <a href="<?php echo $view['router']->path('le_email_campaign_index')?>" id="cancel-tab-1" class="cancel-tab hide mover btn btn-default btn-cancel le-btn-default btn-copy waves-effect "><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
                <a href="#" id="next-tab-1" class=" waves-effect next-tab mover btn btn-default btn-cancel le-btn-default btn-copy waves-effect" rel="2"><?php echo $view['translator']->trans('le.email.wizard.next'); ?></a>
                <div class="toolbar-form-buttons email-toolbar-buttons">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-nospin  dropdown-toggle waves-effect" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 <?php echo (count($form['name']->vars['errors'])) ? ' has-error' : ''; ?>" id="Email_TemplateName">
                    <?php echo $view['form']->label($form['name']); ?>
                    <?php echo $view['form']->widget($form['name']); ?>
                    <?php echo $view['form']->errors($form['name']); ?>
                    <div class="help-block custom-help"></div>
                </div>
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['category']); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-11 <?php echo (count($form['subject']->vars['errors'])) ? ' has-error' : ''; ?>" id="Email_Subject" style="width: 88.11111%">
                    <?php echo $view['form']->label($form['subject']); ?>
                    <?php echo $view['form']->widget($form['subject']); ?>
                    <?php echo $view['form']->errors($form['subject']); ?>
                    <div class="help-block custom-help"></div>
                </div>
                <div>
                    <li class="dropdown dropdown-menu-right" style="display: block;">
                        <a class="btn btn-nospin btn-primary btn-sm hidden-xs " style="position: relative;font-size: 13px;top: 30px;vertical-align: super;" data-toggle="dropdown" href="#">
                            <span><?php echo $view['translator']->trans('le.core.personalize.button'); ?></span> </span><span><i class="caret" ></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" style="margin-top: 21px;">
                            <li>
                                <div class="insert-tokens" style="background-color: whitesmoke;width: 190px;overflow-y: scroll;max-height: 154px;">
                                </div
                            </li>
                        </ul>
                    </li>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <?php echo $view['form']->row($form['previewText']); ?>
                </div>
                <?php if (!$isVariant && false): ?>
                    <div id="leadList " class="col-md-12" <?php echo ($emailType == 'template') ? ' class="hide"' : ''; ?>>
                        <div class=" " id="leadlists">
                            <?php echo $view['form']->label($form['lists']); ?>
                            <?php echo $view['form']->widget($form['lists']); ?>
                            <div class="help-block"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($isVariant): ?>
                <div class="row">
                    <div class="col-md-6 <?php echo (count($form['variantSettings']['weight']->vars['errors'])) ? ' has-error' : ''; ?>" id="Email_trafficweight">
                        <?php echo $view['form']->label($form['variantSettings']['weight']); ?>
                        <?php echo $view['form']->widget($form['variantSettings']['weight']); ?>
                        <div class="help-block " ><?php echo (count($form['variantSettings']['weight']->vars['errors'])) ? ' Traffic Weight can\'t be empty' : ''; ?></div>
                    </div>
                    <div class="col-md-6 <?php echo (count($form['variantSettings']['winnerCriteria']->vars['errors'])) ? ' has-error' : ''; ?>" id="Email_winnercriteria">
                        <?php echo $view['form']->label($form['variantSettings']['winnerCriteria']); ?>
                        <?php echo $view['form']->widget($form['variantSettings']['winnerCriteria']); ?>
                        <div class="help-block" ><?php echo (count($form['variantSettings']['weight']->vars['errors'])) ? ' Winner Criteria can\'t be empty' : ''; ?></div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['isPublished']); ?>
                </div>
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['google_tags']); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php if ($isVariant): ?>
                        <?php if ($isAdmin): ?>
                            <?php echo $view['form']->row($form['publishUp']); ?>
                            <?php echo $view['form']->row($form['publishDown']); ?>
                        <?php endif; ?>
                    <?php else: ?>

                        <div class="hide">
                            <?php echo $view['form']->row($form['language']); ?>
                            <div id="segmentTranslationParent"<?php echo ($emailType == 'template') ? ' class="hide"' : ''; ?>>
                                <?php echo $view['form']->row($form['segmentTranslationParent']); ?>
                            </div>
                            <div id="templateTranslationParent"<?php echo ($emailType == 'list') ? ' class="hide"' : ''; ?>>
                                <?php echo $view['form']->row($form['templateTranslationParent']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($isAdmin):?>
                        <?php if (!$isVariant): ?>
                            <?php echo $view['form']->row($form['isPublished']); ?>
                            <?php echo $view['form']->row($form['publishUp']); ?>
                            <?php echo $view['form']->row($form['publishDown']); ?>
                        <?php endif; ?>

                        <?php echo $view['form']->row($form['unsubscribeForm']); ?>
                        <?php if (!(empty($permissions['page:preference_center:viewown']) &&
                            empty($permissions['page:preference_center:viewother']))): ?>
                            <?php echo $view['form']->row($form['preferenceCenter']); ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!$isVariant): ?>
                        <?php echo $view['form']->row($form['isPublished']); ?>
                    <?php endif; ?>
                    <hr />
                    <h5 class="gtags <?php  echo $isgoogletags ? '' : 'hide'; ?> "><?php echo $view['translator']->trans('le.email.utm_tags'); ?></h5>
                    <br />
                    <?php
                    foreach ($form['utmTags'] as $i => $utmTag):?>
                        <div class="col-sm-6 gtags <?php  echo $isgoogletags ? '' : 'hide'; ?>" ><?php echo $view['form']->row($utmTag); ?></div>
                    <?php endforeach;
                    ?>
                </div>

            </div>
        </div>
        <div id="fragment-2" class="ui-tabs-panel ui-tabs-hide">
            <div class="fragment-2-buttons fixed-header">
                <a href="#" id="previous-button-3" class=" waves-effect prev-tab mover btn btn-default btn-cancel le-btn-default btn-copy waves-effect" rel="1" style="<?php echo !$isbasiceditor ? 'margin-left:-16%;' : ''; ?>"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
                <a href="<?php echo $view['router']->path('le_email_campaign_index')?>" id="cancel-tab-3" data-toggle="ajax" class="cancel-tab hide mover btn btn-default btn-cancel le-btn-default btn-copy waves-effect"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
                <a href="#" id="next-tab-3" class=" waves-effect next-tab mover btn btn-default btn-cancel le-btn-default btn-copy waves-effect" rel="3" style="<?php echo !$isbasiceditor ? 'margin-left:75%;' : ''; ?>"><?php echo $view['translator']->trans('le.email.wizard.next'); ?></a><br>
                <div class="toolbar-form-buttons email-toolbar-buttons">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm" id="email-2-button-div" style="<?php echo !$isbasiceditor ? 'margin-right:148px;' : ''?>"></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-nospin  dropdown-toggle waves-effect" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                    <div id = "open_editor_email_button" style="position:relative;top:-136px;left:5px;  " class="<?php echo $isbasiceditor ? 'hide' : ''?>">
                        <?php echo $view->render(
                            'MauticCoreBundle:Helper:page_actions.html.php',
                            [
                                'routeBase'     => 'email',
                                'langVar'       => 'email',
                                'customButtons' => $custombutton,
                            ]
                        ); ?>
                    </div>
                </div>
            </div>
            <!--<input type="text" style="height:1px;width:1px;border:0px solid;" tabindex="-1" id="builder_url_text" value="" />-->
            <br>
            <div class="tab-pane fade in bdr-w-0 <?php echo $activatebasiceditor; echo $hidebasiceditor; ?>" style="margin-top:-8px;" id="email-basic-container">
                <?php echo $view['form']->widget($form['customHtml']); ?>
            </div>

            <div class="tab-pane fade in bdr-w-0 <?php echo $activateadvanceeditor; echo $hideadvanceeditor; ?> <?php echo ($isbasiceditor || $customHtml != '') ? 'hide' : ''; ?>" id="email-advance-container" style="margin-top:-10px;">
                <div class="col-md-6 <?php echo $activateadvanceeditor; echo $hideadvanceeditor; ?>" style="width:100%;">
                    <div id="block_container">
                        <div class="alert alert-info le-alert-info" id="form-action-placeholder" style="width:66.5%;">
                            <p><?php echo $view['translator']->trans('le.email.notification'); ?></p>
                        </div>
                        <div class="le-category-filter alert alert-info le-alert-info">
                            <p class="info-box-text" style="margin-top: -8px;"><?php echo $view['translator']->trans('le.email.category.notification'); ?></p>
                            <?php  if (!empty($template_filters)): ?>
                                <?php echo $view->render('MauticCoreBundle:Helper:list_filters.html.php', [
                                    'filters' => $template_filters,
                                    'target'  => (empty($target)) ? null : $target,
                                    'tmpl'    => (empty($tmpl)) ? null : $tmpl,
                                ]); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 hide">
                        <?php echo $view['form']->row($form['template']); ?>
                    </div>
                </div>
                <div style="margin-top:30px;">
                    <?php echo $view->render('MauticEmailBundle:Email:bee_template_select.html.php', [
                        'beetemplates' => $beetemplates,
                        'active'       => '', //$form['template']->vars['value'],
                        'route'        => 'oneoff',
                    ]); ?>
                </div>
            </div>
            <div class="tab-pane fade in bdr-w-0 " style="margin-top:-25px;width:100%;" id="email-preview-container">
                <div class="<?php echo (!$isbasiceditor && $customHtml != '') ? '' : 'hide'?>" id="email-content-preview" style="padding:10px;width:95%;margin-left:3%;border: 1px solid #2a323c;">
                    <?php echo $customHtml; ?>
                </div>
            </div>
            <br>
            <br>
        </div>
        <div id="fragment-3" class="ui-tabs-panel ui-tabs-hide">
            <div class="fragment-3-buttons fixed-header">
                <a href="#" id="#previous-button" class="waves-effect prev-tab mover btn btn-default btn-cancel le-btn-default btn-copy waves-effect" rel="2"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
                <a href="<?php echo $view['router']->path('le_email_campaign_index')?>" id="cancel-tab-2" class="cancel-tab hide mover btn btn-default btn-cancel le-btn-default btn-copy waves-effect"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
                <a href="#" id="next-tab-2" class="waves-effect next-tab mover btn btn-default btn-cancel le-btn-default btn-copy waves-effect" rel="4"><?php echo $view['translator']->trans('le.email.wizard.next'); ?></a>
                <div class="toolbar-form-buttons email-toolbar-buttons">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-nospin  dropdown-toggle waves-effect" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                </div>
            </div>
            <div class="alert alert-info le-alert-info" id="form-action-placeholder">
                <p><?php echo $view['translator']->trans('le.email.wizard.notification'); ?></p>
            </div>
            <div class="form-group hide">
                <div style="margin-top:18px;" class="available-filters pl-0 col-md-6" data-prototype="<?php echo $view->escape($view['form']->widget($form['recipients']['filters']->vars['prototype'], ['filterfields'=> $filter_fields, 'addconditionbtn'=>$filter_addconditionbtn])); ?>" data-index="<?php echo $filter_index + 1; ?>">
                    <select class="chosen form-control" id="available_filters" data-placeholder="Choose filter...">
                        <option value=""></option>
                        <?php
                        foreach ($filter_fields as $object => $field):
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
                    <?php echo $filter_addconditionbtn?>
                </div>
                <div class="filter-and-group-holder">
                    <?php echo $view['form']->widget($form['recipients']['filters'], ['filterfields'=> $filter_fields, 'addconditionbtn'=>$filter_addconditionbtn]); ?>
                </div>
                <div class="leadlist-filter-group filter-or-group">
                    <button type="button" class="btn btn-default lead-list btn-filter-group waves-effect add-contition" data-filter-group='or'>Add another set of conditions</button>
                </div>
            </div>
            <div class="hide" id="templates">
                <?php foreach ($filter_templates as $dataKey => $filter_template): ?>
                    <?php $attr = ($dataKey == 'tags') ? ' data-placeholder="'.$view['translator']->trans('le.lead.tags.select_or_create').'" data-no-results-text="'.$view['translator']->trans('le.lead.tags.enter_to_create').'" data-allow-add="true" onchange="Le.createLeadTag(this)"' : ''; ?>
                    <select class="form-control not-chosen <?php echo $filter_template; ?>" name="emailform[recipients][filters][__name__][filter]" id="emailform_recipients_filters___name___filter"<?php echo $attr; ?> disabled>
                        <?php
                        if (isset($form['recipients']->vars[$dataKey])):
                            foreach ($form['recipients']->vars[$dataKey] as $value => $label):
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
        </div>

        <div id="fragment-4" class="ui-tabs-panel <?php echo $settingshide?>">
            <div class="fragment-4-buttons fixed-header">
                <a href="#" style="margin-left:-72px;" class="waves-effect prev-tab mover btn btn-default btn-cancel le-btn-default btn-copy waves-effect" rel="3"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
                <div class="toolbar-form-buttons" style="margin-top: -165px;margin-right: 27px;">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-nospin  dropdown-toggle waves-effect" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                </div>
            </div>
            <div id="email-other-container">
                <div class="row">
                    <div class="col-md-6">
                        <?php echo $view['form']->row($form['fromName']); ?>
                    </div>
                    <div class="col-md-6">
                        <div class="pull-left" id="email_FromAddress" style="max-width:70%;">
                            <?php echo $view['form']->row($form['fromAddress'],
                                ['attr' => ['tabindex' => '-1', 'style' =>'pointer-events: none;background-color: #ebedf0;opacity: 1;']]); ?>
                        </div>
                        <?php //echo $view['form']->widget($form['fromAddress']);?>
                        <li class="dropdown" name="verifiedemails" id="verifiedemails" style="display: block;margin-left: 191px;">
                            <a class="btn btn-nospin btn-primary btn-sm hidden-xs" style="font-size:13px;margin-top:25px;padding:8px !important;" data-toggle="dropdown" href="#">
                                <span><?php echo $view['translator']->trans('le.core.button.aws.load'); ?></span> </span><span><i class="caret" ></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right" id="verifiedemails">
                                <?php foreach ($verifiedemail as $key=> $value): ?>
                                    <li >
                                        <a style="text-transform: none" class="verified-emails" id="data-verified-emails" data-verified-emails="<?php echo $value; ?>" data-verified-fromname="<?php echo $key; ?>"><?php echo $value; ?></a>
                                    </li>
                                <?php endforeach; ?>
                                <li >
                                    <a style="text-transform: none" href="<?php echo $view['router']->generate('le_config_action', ['objectAction' => 'edit']); ?>" class="verified-emails" ><?php echo $view['translator']->trans('le.email.add.new.profile'); ?></a>
                                </li>
                            </ul>
                        </li>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-6">
                        <?php echo $view['form']->row($form['replyToAddress']); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo $view['form']->row($form['bccAddress']); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 <?php echo $isAdmin ? '' : ' hide'?>" id="Emailasset_Attachments">
                        <div class="pull-left">
                            <?php echo $view['form']->label($form['assetAttachments']); ?>
                        </div>
                        <div class="text-right pr-10">
                            <span class="label label-info" id="attachment-size"><?php echo $attachmentSize; ?></span>
                        </div>
                        <div class="clearfix"></div>
                        <?php echo $view['form']->widget($form['assetAttachments']); ?>
                        <div class="help-block"></div>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <br>
                        <?php echo $view['form']->label($form['postal_address']); ?>
                        <?php echo $view['form']->widget($form['postal_address']); ?>
                    </div>
                    <br>
                    <div class="col-md-12 <?php echo $activatebasiceditor; echo $hidebasiceditor; ?>" id="unsubscribe_text_div">
                        <br>
                        <br>
                        <?php echo $view['form']->label($form['unsubscribe_text']); ?>
                        <?php echo $view['form']->widget($form['unsubscribe_text']); ?>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>

                    </div>
                    <div class="col-md-12 hide">
                        <br>
                        <div class="pull-left">
                            <?php echo $view['form']->label($form['plainText']); ?>
                        </div>
                        <div class="text-right pr-10">
                            <i class="fa fa-spinner fa-spin ml-2 plaintext-spinner hide"></i>
                            <a class="small" onclick="Le.autoGeneratePlaintext();"><?php echo $view['translator']->trans('le.email.plaintext.generate'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <?php echo $view['form']->widget($form['plainText']); ?>
                    </div>
                </div>
            </div>
<!--            <div class="tab-pane fade bdr-w-0 --><?php //echo $isAdmin ? '' : 'hide'?><!--" id="dynamic-content-container">-->
<!--                <div class="row">-->
<!--                    <div class="col-md-12">-->
<!--                        <div class="row">-->
<!--                            --><?php
//                            $tabHtml = '<div class="col-xs-3 dynamicContentFilterContainer">';
//                            $tabHtml .= '<ul class="nav nav-tabs tabs-left" id="dynamicContentTabs">';
//                            $tabHtml .= '<li><a href="javascript:void(0);" role="tab" class="btn btn-primary" id="addNewDynamicContent"><i class="fa fa-plus text-success"></i> '.$view['translator']->trans('mautic.core.form.new').'</a></li>';
//                            $tabContentHtml = '<div class="tab-content pa-md col-xs-9" id="dynamicContentContainer">';
//
//                            foreach ($form['dynamicContent'] as $i => $dynamicContent) {
//                                $linkText = $dynamicContent['tokenName']->vars['value'] ?: $view['translator']->trans('le.core.dynamicContent').' '.($i + 1);
//
//                                $tabHtml .= '<li class="'.($i === 0 ? ' active' : '').'"><a role="tab" data-toggle="tab" href="#'.$dynamicContent->vars['id'].'">'.$linkText.'</a></li>';
//
//                                $tabContentHtml .= $view['form']->widget($dynamicContent);
//                            }
//
//                            $tabHtml .= '</ul></div>';
//                            $tabContentHtml .= '</div>';
//
//                            echo $tabHtml;
//                            echo $tabContentHtml;
//?>
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
            <div class="col-md-3 bg-white height-auto bdr-l <?php echo $isAdmin ? '' : 'hide'?> <?php echo ($emailType == 'template') ? 'hide' : ''; ?>">
                <div class="pr-lg pl-lg pt-md pb-md ">
                    <div id="leadList"<?php echo ($emailType == 'template') ? ' hide' : ''; ?>>
                        <?php echo $view['form']->row($form['lists']); ?>
                    </div>
                    <?php echo $view['form']->row($form['category']); ?>
                    <div class="hide">
                        <?php echo $view['form']->row($form['language']); ?>
                        <div id="segmentTranslationParent"<?php echo ($emailType == 'template') ? ' class="hide"' : ''; ?>>
                            <?php echo $view['form']->row($form['segmentTranslationParent']); ?>
                        </div>
                        <div id="templateTranslationParent"<?php echo ($emailType == 'list') ? ' class="hide"' : ''; ?>>
                            <?php echo $view['form']->row($form['templateTranslationParent']); ?>
                        </div>
                    </div>
                    <div class="hide">
                        <?php echo $view['form']->rest($form); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $view['form']->end($form); ?>


<!--<div id="dynamicContentPrototype" data-prototype="--><?php //echo $view->escape($view['form']->widget($dynamicContentPrototype));?><!--"></div>-->
<?php //if ($filterBlockPrototype instanceof FormView) :?>
<!--    <div id="filterBlockPrototype" data-prototype="--><?php //echo $view->escape($view['form']->widget($filterBlockPrototype));?><!--"></div>-->
<?php //endif;?>
<?php //if ($filterSelectPrototype instanceof FormView) :?>
<!--    <div id="filterSelectPrototype" data-prototype="--><?php //echo $view->escape($view['form']->widget($filterSelectPrototype));?><!--"></div>-->
<?php //endif;?>

<!--<div class="hide" id="templates">
    <?php //foreach ($templates as $dataKey => $template):?>
        <?php// $attr = ($dataKey == 'tags') ? ' data-placeholder="'.$view['translator']->trans('le.lead.tags.select_or_create').'" data-no-results-text="'.$view['translator']->trans('le.lead.tags.enter_to_create').'" data-allow-add="true" onchange="Le.createLeadTag(this)"' : ''; ?>
        <select class="form-control not-chosen <?php //echo $template;?>" name="emailform[dynamicContent][__dynamicContentIndex__][filters][__dynamicContentFilterIndex__][filters][__name__][filter]" id="emailform_dynamicContent___dynamicContentIndex___filters___dynamicContentFilterIndex___filters___name___filter"<?php echo $attr; ?>>
            <?php
            //if (isset($form->vars[$dataKey])):
                //foreach ($form->vars[$dataKey] as $value => $label):
                   // if (is_array($label)):
                       // echo "<optgroup label=\"$value\">\n";
                      //  foreach ($label as $optionValue => $optionLabel):
                          //  echo "<option value=\"$optionValue\">$optionLabel</option>\n";
                        //endforeach;
                       // echo "</optgroup>\n";
                   // else:
                        //if ($dataKey == 'lists' && (isset($currentListId) && (int) $value === (int) $currentListId)) {
                           // continue;
                      //  }
                        //echo "<option value=\"$value\">$label</option>\n";
                  //  endif;
               // endforeach;
           // endif;
            ?>
        </select>
    <?php //endforeach;?>
</div>-->
<?php echo $view->render('MauticEmailBundle:Email:beeeditor.html.php', ['objectId'      => $email->getSessionId(), 'type'          => 'email']); ?>
<?php //builder disabled due to bee editor
//echo $view->render('MauticCoreBundle:Helper:builder.html.php', [
//    'type'          => 'email',
//    'isCodeMode'    => $isCodeMode,
//    'sectionForm'   => $sectionForm,
//    'builderAssets' => $builderAssets,
//    'slots'         => $slots,
//    'sections'      => $sections,
//    'objectId'      => $email->getSessionId(),
//]);?>
<?php
$type = $email->getEmailType();
if ((empty($updateSelect) && !$isExisting && !$formcontainserror && !$variantParent && empty($type)) || empty($type) || !empty($forceTypeSelection)):
    echo $view->render('MauticEmailBundle:Email:email_selecttype.html.php',
        [
            'item'       => $email,
            'leLang'     => [
                'newListEmail'     => 'le.email.type.list.header',
                'newTemplateEmail' => 'le.email.type.template.header',
            ],
            'typePrefix'          => 'email',
            'cancelUrl'           => $type == 'template' ? 'le_email_index' : 'le_email_campaign_index',
            'header'              => 'le.email.type.header',
            'typeOneHeader'       => 'le.email.type.template.header',
            'typeOneIconClass'    => 'fa-cube',
            'typeOneDescription'  => 'le.email.type.template.description',
            'typeOneOnClick'      => "Le.selectEmailType('template');",
            'typeTwoHeader'       => 'le.email.type.list.header',
            'typeTwoIconClass'    => 'fa-pie-chart',
            'typeTwoDescription'  => 'le.email.type.list.description',
            'typeTwoOnClick'      => "Le.selectEmailType('list');",
            'typeThreeHeader'     => 'le.email.editor.codeeditor.header',
            'typeThreeIconClass'  => 'fas fa-code',
            'typeThreeOnClick'    => "Le.selectEmailEditor('code');",
            'typeThreeDescription'=> 'le.email.editor.codeeditor.description',
        ]);
endif;
?>
<?php
$type    = $email->getEmailType();
if (empty($updateSelect) && !$isCloneOp && !$isExisting && !$formcontainserror && !$variantParent && !$isMobile):
    echo $view->render('MauticEmailBundle:Email:email_selecttype.html.php',
        [
            'item'                => $email,
            'leLang'              => [],
            'typePrefix'          => 'email',
            'cancelUrl'           => $type == 'template' ? 'le_email_index' : 'le_email_campaign_index',
            'header'              => 'le.email.editor.header',
            'typeOneHeader'       => 'le.email.editor.basic.header',
            'typeOneIconClass'    => 'fa-cube',
            'typeOneDescription'  => 'le.email.editor.basic.description',
            'typeOneOnClick'      => "Le.selectEmailEditor('basic');",
            'typeTwoHeader'       => 'le.email.editor.advance.header',
            'typeTwoIconClass'    => 'fa-pie-chart',
            'typeTwoDescription'  => 'le.email.editor.advance.description',
            'typeTwoOnClick'      => "Le.selectEmailEditor('advance');",
            'typeThreeHeader'     => 'le.email.editor.codeeditor.header',
            'typeThreeIconClass'  => 'fas fa-code',
            'typeThreeOnClick'    => "Le.selectEmailEditor('code');",
            'typeThreeDescription'=> 'le.email.editor.codeeditor.description',
        ]);
endif;
?>
