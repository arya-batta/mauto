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
$view['slots']->set('leContent', 'dripemail');

$header = $view['translator']->trans('le.drip.email.header.edit', ['%name%' => $entity->getName()]);

$view['slots']->set('headerTitle', $header);
$activatebasiceditor  = 'hide';
$activateadvanceeditor= 'hide';
$hidebasiceditor      = 'hide';
$hideadvanceeditor    = 'hide';
$isAdmin              = $view['security']->isAdmin();
$activatebuttonlabel  = 'le.drip.pause.button.text';
$activateConfirm      = '';
$hrefvalue            = 'pause';
$activateTitle        = 'le.drip.email.pause.title';
if (!$entity->isPublished()) {
    $activateTitle       = 'le.drip.email.activate.title';
    $hrefvalue           = 'activate';
    $activateConfirm     = 'data-toggle="confirmation" data-confirm-text="Activate" data-confirm-callback="activateDripAction" data-cancel-text="Cancel" data-cancel-callback="dismissConfirmation"';
    $activatebuttonlabel = 'le.drip.activate.button.text';
} else {
    $activateConfirm = "onclick=Le.activateDripAction('pause');";
    $hrefvalue       = '#';
}
$confirmationMsg = 'le.drip.email.publish.without.email';
if (count($items) > 0) {
    $confirmationMsg = 'le.drip.email.publish.with.email';
}
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
$filter_addconditionbtn="<button type=\"button\" class=\"btn btn-default lead-list btn-filter-group add-contition\" data-filter-group='and'>Add a condition</button>";
?>
<?php echo $view['form']->start($form); ?>
<div id="page-wrap" class="align-tab-center">
    <div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all tab-pane fade in active bdr-rds-0 bdr-w-0">
        <ul class="nav nav-pills nav-wizard ui-helper-reset ui-helper-clearfix ui-widget-header">
            <!--<li class="ui-state-default ui-corner-top btn-group modal-footer ui-state-active hide" role = "tab" id = "ui-tab-header1" rel = 1>
                <a id="info_tab" class="text-start">
                    <div class="content-wrapper-first">
                        <div><span class="small-xx">Step 01</span></div>
                        <label><?php echo $view['translator']->trans('le.core.email.name'); ?></label>
                    </div></a>
            </li>-->
            <li class="ui-state-default ui-corner-top btn-group ui-state-active" role = "tab" id = "ui-tab-header2" rel = 2>
                <a class="text-start"> <div class="content-wrapper-first">
                        <div><span class="small-xx">Step 01</span></div>
                        <label><?php echo $view['translator']->trans('le.core.drip.compose'); ?></label>
                    </div></a>

            </li>
            <li class="ui-state-default ui-corner-top btn-group" role = "tab" id = "ui-tab-header3" rel = 3>
                <a class="text-start" style="padding: 0px 35px;"> <div class="content-wrapper-first">
                        <div><span class="small-xx">Step 02</span></div>
                        <label><?php echo $view['translator']->trans('le.core.email.recipients'); ?></label>
                    </div></a>
            </li>
            <li class="ui-state-default ui-corner-top btn-group" role = "tab" id = "ui-tab-header4" rel = 4>
                <a class="text-start" style="padding: 0px 34px;"><div class="content-wrapper-first">
                        <div><span class="small-xx">Step 03</span></div>
                        <label><?php echo $view['translator']->trans('le.core.email.setup'); ?></label>
                    </div></a>
            </li>
        </ul>
        <div id="fragment-1" class="ui-tabs-panel ui-tabs-hide">
            <div class="fragment-drip-1-buttons fixed-header">
                <a href="<?php echo $view['router']->path('le_email_campaign_index')?>" id="cancel-tab-1" class="cancel-tab hide mover btn btn-default btn-cancel le-btn-default btn-copy"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
                <a href="#" id="next-tab-1" style="margin-top: -306px;" class="waves-effect next-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="3"><?php echo $view['translator']->trans('le.email.wizard.next'); ?></a>
                <div class="toolbar-form-buttons" style="margin-top: -179px;margin-right: 107px;">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-nospin  dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                </div>
            </div>


        </div>
        <div id="fragment-2" class="ui-tabs-panel ">
            <div class="fragment-drip-2-buttons fixed-header">
                <a href="#" id="#previous-button" style="margin-top: -307px;" class="waves-effect prev-tab hide mover btn btn-default btn-cancel le-btn-default btn-copy" rel="1"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
                <a href="<?php echo $view['router']->path('le_email_campaign_index')?>" id="cancel-tab-2" data-toggle="ajax" class="cancel-tab hide mover btn btn-default btn-cancel le-btn-default btn-copy"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
                <a href="#" id="next-tab-2" style="margin-top: -280px;margin-left:67%;" class="waves-effect <?php echo $ismobile ? 'hide' : ''?> next-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="3"><?php echo $view['translator']->trans('le.email.wizard.next'); ?></a><br>
                <div class="toolbar-form-buttons" style="margin-top: -165px; <?php echo !$ismobile ? 'margin-right: 124px;' : ''?>">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-main waves-effect">
                            </button>
                        <button type="button" class="btn btn-default btn-nospin dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                </div>
            </div>

            <!--<input type="text" style="height:1px;width:1px;border:0px solid;" tabindex="-1" id="builder_url_text" value="" />-->
            <div class="drip-email-button-container" style="margin-top:-65px;float:right;">
                <div class="newbutton-container">
                    <li class="dropdown dropdown-menu-right" style="display: block;float:right;">
                        <a class="btn btn-nospin le-btn-default" style="position: relative;font-size: 14px;top: 0;vertical-align: super;" data-toggle="dropdown" href="#">
                            <span><i class="fa fa-plus"></i><span class=""> <?php echo $view['translator']->trans('le.drip.email.new.email')?></span></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" style="margin-top: 21px;">
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

                    <a class="btn hide btn-default le-btn-default btn-nospin" id="new-drip-email" value="basic_editor" onclick="Le.openDripEmailEditor();" style="float:right;z-index:10000;">
                        <span><i class="fa fa-plus"></i><span class="hidden-xs hidden-sm"> <?php echo $view['translator']->trans('le.drip.email.new.email')?></span></span>
                    </a>
                </div>
                <div class="saveclose-container hide">
                    <a class="btn btn-default le-btn-default btn-nospin" id="save-drip-email" value="basic_editor" onclick="Le.saveDripEmail(<?php echo $entity->getId(); ?>);" style="float:right;z-index:10000;">
                    <span><i class="fa fa-save"></i><span class="hidden-xs hidden-sm"> <?php echo $view['translator']->trans('le.drip.email.save.close')?></span></span>
                    </a>
                </div>
                <div class="cancel-container hide">
                    <a class="btn btn-default le-btn-default btn-nospin" id="save-drip-email" value="basic_editor" onclick="Le.saveDripEmail(<?php echo $entity->getId(); ?>);" style="float:right;z-index:10000;">
                        <span><i class="fa fa-save"></i><span class="hidden-xs hidden-sm"> <?php echo $view['translator']->trans('le.drip.email.cancel')?></span></span>
                    </a>
                </div>
                <div class="update-container hide">
                    <a class="btn btn-default le-btn-default btn-nospin" id="update-drip-email" value="" onclick="Le.updateDripEmail();" style="float:right;z-index:10000;">
                        <span><i class="fa fa-save"></i><span class="hidden-xs hidden-sm"> <?php echo $view['translator']->trans('le.drip.email.update')?></span></span>
                    </a>
                </div>
            </div>
            <div class="dripemail-body" style="margin-top:-14px;">
                <div class="hide" id="drip-email-container">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="col-md-10" id="DripEmail_Subject" style="right:15px;">
                                <?php echo $view['form']->label($emailform['subject']); ?>
                                <?php echo $view['form']->widget($emailform['subject']); ?>
                                <div class="help-block custom-help"></div>
                            </div>
                            <div class="col-md-2" style="right:25px;">
                                <li class="dropdown dropdown-menu-right" style="display: block;">
                                    <a class="btn btn-nospin btn-primary btn-sm hidden-xs " style="position: relative;font-size: 14px;top: 25px;vertical-align: super;" data-toggle="dropdown" href="#">
                                        <span><?php echo $view['translator']->trans('le.core.personalize.button'); ?></span> </span><span><i class="caret" ></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-left" style="margin-top: 21px;">
                                        <li>
                                            <div class="insert-tokens" style="background-color: whitesmoke;width: 190px;overflow-y: scroll;max-height: 154px;">
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                            </div>
                        </div>

                        <div class="col-md-4" style="z-index:10000;">
                            <?php echo $view['form']->row($emailform['previewText']); ?>
                        </div>
                    </div>
                    <div class="row">

                    </div>
                    <br>
                    <div class="tab-pane fade in bdr-w-0 dripemail_content hide" style="margin-top:-8px;" id="dripemail_basic_editor">
                        <?php echo $view['form']->widget($emailform['customHtml']); ?>
                    </div>

                    <div class="tab-pane fade in bdr-w-0 dripemail_content hide" id="dripemail_advance_editor" style="margin-top:-30px;">
                        <div class="col-md-6" style="width:100%;">
                            <div style="width: 70%">
                                <?php if (!empty($template_filters)): ?>
                                    <?php echo $view->render('MauticCoreBundle:Helper:list_filters.html.php', [
                                        'filters' => $template_filters,
                                        'target'  => (empty($target)) ? null : $target,
                                        'tmpl'    => (empty($tmpl)) ? 'form' : $tmpl,
                                    ]); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 hide">
                                <?php echo $view['form']->row($emailform['template']); ?>
                            </div>
                            <div class="col-md-12 hide">
                                <?php echo $view['form']->row($emailform['beeJSON']); ?>
                            </div>
                        </div>
                        <?php echo $view->render('MauticEmailBundle:Email:bee_template_select.html.php', [
                            'beetemplates' => $beetemplates,
                            'active'       => '', //$emailform['template']->vars['value'],
                            'route'        => 'drip',
                        ]); ?>
                    </div>
                    <div class="tab-pane fade in bdr-w-0 hide" style="width:100%;" id="email-preview-container">
                        <div id="builder_btn">
                            <a class="btn btn-default text-primary le-btn-default blue-theme-bg" onclick="Le.showDripEmailTemplateview();" style="color:#ffffff;padding-top: 7px;float: right;margin-right: 3%;border-radius:4px;z-index:1003;" data-toggle="ajax">
                                <span>
                                <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.core.change.template'); ?></span>
                                </span>
                            </a>
                            <a class="btn btn-default text-primary le-btn-default blue-theme-bg" onclick="Le.launchBeeEditor('dripemail', 'email');" style="color:#ffffff;padding-top: 7px;float: right;margin-right: 5px;border-radius:4px;z-index:1003;" data-toggle="ajax">
                                <span>
                                <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.core.edit'); ?></span>
                                </span>
                            </a>
                        </div>
                        <div class="hide" id="email-content-preview" style="padding:10px;width:95%;margin-left:3%;border: 1px solid #000000;">

                        </div>
                    </div>

                </div>
                <br>
                <br>
                <div class="" id="drip-email-list-container" style="margin-top: -36px">
                    <?php echo $view->render('MauticEmailBundle:DripEmail:emaillist.html.php', [
                        'items'           => $items,
                        'permissions'     => $permissions,
                        'actionRoute'     => $actionRoute,
                        'translationBase' => $translationBase,
                        'entity'          => $entity,
                        'ismobile'        => $ismobile,
                    ]); ?>
                </div>
            </div>

        </div>
        <div id="fragment-3" class="ui-tabs-panel ui-tabs-hide">
            <div class="fragment-drip-3-buttons fixed-header">
                <a href="#" style="margin-left:-18%;" id="#previous-button" class="waves-effect <?php echo $ismobile ? 'hide' : ''?> prev-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="2"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
                <a href="<?php echo $view['router']->path('le_email_campaign_index')?>" id="cancel-tab-2" class="cancel-tab hide mover btn btn-default btn-cancel le-btn-default btn-copy"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
                <a href="#" style="margin-left:65.5%;" id="next-tab-2" class="waves-effect <?php echo $ismobile ? 'hide' : ''?> next-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="4"><?php echo $view['translator']->trans('le.email.wizard.next'); ?></a>
                <div class="toolbar-form-buttons email-toolbar-buttons" style="<?php echo $ismobile ? 'margin-top: -144px;margin-right:0px;' : ''?>">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-main waves-effect">
                        </button>
                        <button type="button" class="btn btn-default btn-nospin dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                </div>
            </div>
            <div class="alert alert-info le-alert-info" id="form-action-placeholder">
                <p><?php echo $view['translator']->trans('le.drip.email.wizard.notification'); ?></p>
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
                    <button type="button" class="btn btn-default lead-list btn-filter-group add-contition" data-filter-group='or'>Add another set of conditions</button>
                </div>
            </div>
            <div class="hide" id="templates">
                <?php foreach ($filter_templates as $dataKey => $filter_template): ?>
                    <?php $attr = ($dataKey == 'tags') ? ' data-placeholder="'.$view['translator']->trans('le.lead.tags.select_or_create').'" data-no-results-text="'.$view['translator']->trans('le.lead.tags.enter_to_create').'" data-allow-add="true" onchange="Le.createLeadTag(this)"' : ''; ?>
                    <select class="form-control not-chosen <?php echo $filter_template; ?>" name="dripemailform[recipients][filters][__name__][filter]" id="dripemailform_recipients_filters___name___filter"<?php echo $attr; ?> disabled>
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
        <div id="fragment-4" class="ui-tabs-panel ui-tabs-hide">
            <div class="fragment-drip-4-buttons fixed-header">
                <a href="#" style="margin-left:-70px;margin-top: -280px;" class="waves-effect prev-tab mover btn btn-default btn-cancel le-btn-default btn-copy <?php echo $ismobile ? 'hide' : ''?>" rel="3"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
                <div class="toolbar-form-buttons" style="<?php echo $ismobile ? 'margin-top: -144px;margin-right:74px;' : 'margin-top: -165px;margin-right: 124px;'?>">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm"></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-main waves-effect">
                        </button>
                        <button type="button" class="btn btn-default btn-nospin dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                </div>
                <a title="<?php echo $view['translator']->trans($activateTitle); ?>" class="waves-effect drip-activate mover btn btn-default btn-cancel le-btn-default btn-copy" style="margin-top: -56px;padding:7px;margin-left:67%;" href="<?php echo $hrefvalue; ?>" data-precheck="" data-message="<?php echo $view['translator']->trans($confirmationMsg); ?>" <?php echo $activateConfirm; ?> ><?php echo $view['translator']->trans($activatebuttonlabel); ?></a>
            </div>
            <div id="email-other-container">
                <div class="row">
                    <div class="col-md-6 drip-email-box-shadow fl-left" style="width:49%;margin-left:0.5%;margin-right:0.5%;">
                        <header class="drip-settings-header">
                            <h1><?php echo $view['translator']->trans('le.drip.settings.heading1'); ?></h1>
                        </header>
                        <div style="padding-top: 18px;">
                            <div class="row">
                                <div class="col-md-12 <?php echo (count($form['name']->vars['errors'])) ? ' has-error' : ''; ?>" id="dripEmail_PublicName">
                                    <?php echo $view['form']->label($form['name']); ?>
                                    <?php echo $view['form']->widget($form['name']); ?>
                                    <?php echo $view['form']->errors($form['name']); ?>
                                    <div class="help-block custom-help"></div>
                                </div>
                            </div>
                            <br>
                            <div class="row hide">
                                <div class="col-md-11 <?php echo (count($form['subject']->vars['errors'])) ? ' has-error' : ''; ?>" style="width: 88.11111%">
                                    <?php echo $view['form']->label($form['subject']); ?>
                                    <?php echo $view['form']->widget($form['subject']); ?>
                                    <?php echo $view['form']->errors($form['subject']); ?>
                                    <div class="help-block custom-help"></div>
                                </div>
                                <div>
                                    <li class="dropdown dropdown-menu-right" style="display: block;">
                                        <a class="btn btn-nospin btn-primary btn-sm hidden-xs " style="position: relative;font-size: 14px;top: 25px;vertical-align: super;" data-toggle="dropdown" href="#">
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
                            <div class="row">
                                <div class="col-md-12">
                                    <?php echo $view['form']->row($form['fromName']); ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12 <?php echo (count($form['fromAddress']->vars['errors'])) ? ' has-error' : ''; ?>" id="email_FromAddress">
                                    <div class="pull-left" id="email_FromAddress"><!-- style="max-width:70%;"-->
                                        <?php echo $view['form']->row($form['fromAddress']); //,
                                           // ['attr' => ['tabindex' => '-1', 'style' =>'pointer-events: none;background-color: #ebedf0;opacity: 1;']]);?>
                                    </div>
                                    <?php //echo $view['form']->widget($form['fromAddress']);?>
<!--                                    <li class="dropdown" name="verifiedemails" id="verifiedemails" style="display: block;margin-left: 191px;">-->
<!--                                        <a class="btn btn-nospin btn-primary btn-sm hidden-xs" style="font-size:13px;margin-top:25px;" data-toggle="dropdown" href="#">-->
<!--                                            <span>--><?php //echo $view['translator']->trans('le.core.button.aws.load');?><!--</span> </span><span><i class="caret" ></i>-->
<!--                                        </a>-->
<!--                                        <ul class="dropdown-menu dropdown-menu-right" id="verifiedemails">-->
<!--                                            --><?php //foreach ($verifiedemail as $key=> $value):?>
<!--                                                <li >-->
<!--                                                    <a style="text-transform: none" class="verified-emails" id="data-verified-emails" data-verified-emails="--><?php //echo $value;?><!--" data-verified-fromname="--><?php //echo $key;?><!--">--><?php //echo $value;?><!--</a>-->
<!--                                                </li>-->
<!--                                            --><?php //endforeach;?>
<!--                                            <li >-->
<!--                                                <a style="text-transform: none" href="--><?php //echo $view['router']->generate('le_config_action', ['objectAction' => 'edit']);?><!--" class="verified-emails" >--><?php //echo $view['translator']->trans('le.email.add.new.profile');?><!--</a>-->
<!--                                            </li>-->
<!--                                        </ul>-->
<!--                                    </li>-->
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php echo $view['form']->row($form['replyToAddress']); ?>
                                </div>
                            </div>
                            <div class="row hide">
                                <div class="col-md-12">
                                    <?php echo $view['form']->row($form['bccAddress']); ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php echo $view['form']->row($form['category']); ?>
                                </div>
                            </div>
                            <br>
                            <div class="row <?php echo !$view['security']->isAdmin() ? 'hide' : ''; ?>">
                                <div class="col-md-12">
                                    <?php echo $view['form']->row($form['google_tags']); ?>
                                </div>
                            </div>
                            <div class="row hide">
                                <div class="col-md-12">
                                    <?php echo $view['form']->row($form['isPublished']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 fl-left" style="width:50%;">
                        <div class="drip-email-box-shadow">
                            <header class="drip-settings-header">
                                <h1><?php echo $view['translator']->trans('le.drip.settings.heading2'); ?></h1>
                            </header>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="m-b-15">
                                        <div class="bootstrap-timepicker">
                                            <?php echo $view['form']->row($form['scheduleDate']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 <?php echo (count($form['daysEmailSend']->vars['errors'])) ? ' has-error' : ''; ?>" id="drip_daysEmailSend" >
                                    <?php echo $view['form']->row($form['daysEmailSend']); ?>
                                </div>
                            </div>
                            <div class="row hide">
                                <div class="col-md-12">
                                    <?php echo $view['form']->row($form['previewText']); ?>
                                </div>
                            </div>
                            <div class="row <?php echo $isAdmin ? '' : 'hide'; ?>">
                                <div class="col-md-12">
                                    <?php echo $view['form']->row($form['description']); ?>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="drip-email-box-shadow">
                            <header class="drip-settings-header">
                                <h1><?php echo $view['translator']->trans('le.drip.settings.heading3'); ?></h1>
                            </header>
                            <div class="row">
                                <div class="col-md-12">
                                    <br>
                                    <?php echo $view['form']->label($form['postal_address']); ?>
                                    <?php echo $view['form']->widget($form['postal_address']); ?>
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-12" id="unsubscribe_text_div">
                                    <?php echo $view['form']->label($form['unsubscribe_text']); ?>
                                    <?php echo $view['form']->widget($form['unsubscribe_text']); ?>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br>
            <br>
        </div>
    </div>
</div>
<?php echo $view['form']->end($form); ?>

<?php echo $view->render('MauticEmailBundle:Email:beeeditor.html.php', ['objectId'      => $emailEntity->getSessionId(), 'type'          => 'email']); ?>

<drip class="drip-blue-prints builder-active hide" style="overflow-y: scroll;overflow-x:scroll !important;position:fixed;min-width:1024px;">
    <?php echo $view->render('MauticEmailBundle:DripEmail:blueprintlist.html.php', [
        'items'           => $bluePrints,
        'entity'          => $entity,
        'drips'           => $drips,
    ]); ?>
</drip>
