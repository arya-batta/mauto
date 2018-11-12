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
$view['slots']->set('mauticContent', 'form');
$isadmin    =$view['security']->isAdmin();
$hidepanel  =$view['security']->isAdmin() ? '' : 'display: none;';
$header     = ($activeForm->getId())
    ?
    $view['translator']->trans(
        'mautic.form.form.header.edit',
        ['%name%' => $view['translator']->trans($activeForm->getName())]
    )
    :
    $view['translator']->trans('mautic.form.form.header.new');
$view['slots']->set('headerTitle', $header);

$formId = $form['sessionId']->vars['data'];

if (!isset($inBuilder)) {
    $inBuilder = false;
}
$hidetemplate  = '';
$hideformpanel = '';
if (($activeForm->getName() == '' || $activeForm->getName() == null) && $objectID == null) {
    $hideformpanel = 'hide';
    $hidetemplate  = '';
} elseif ($objectID == 'scratch') {
    $hideformpanel = '';
    $hidetemplate  = 'hide';
} else {
    $hideformpanel = '';
    $hidetemplate  = 'hide';
}

?>
<?php /** echo $view['form']->start($form); ?>
<div class="box-layout">
<div class="col-md-9 height-auto bg-white bdr-r pa-md">
<div class="row">
<div class="col-xs-12 <?php echo $hidetemplate; ?>">
<?php echo $view->render('MauticFormBundle:Builder:form_template_select.html.php', [
'formTemplates' => $formItems,
'entity'        => $activeForm,
'newFormURL'    => $newFormURL,
]); ?>
</div>
<div class="col-xs-12 <?php echo $hideformpanel?>">
<!-- tabs controls -->
<ul class="bg-auto nav nav-tabs pr-md pl-md">
<li class="active"><a href="#details-container" role="tab" data-toggle="tab"><?php echo $view['translator']->trans(
'mautic.core.details'
); ?></a></li>
<li id="fields-tab"><a href="#fields-container" role="tab" data-toggle="tab"><?php echo $view['translator']->trans(
'mautic.form.tab.fields'
); ?></a></li>
<li id="actions-tab"><a href="#actions-container" role="tab" data-toggle="tab"><?php echo $view['translator']->trans(
'mautic.form.tab.actions'
); ?></a></li>
</ul>
<!--/ tabs controls -->
<div class="tab-content pa-md">
<div class="tab-pane fade in active bdr-w-0" id="details-container">
<div class="row">
<div class="col-md-6">
<?php
echo $view['form']->row($form['name']);
echo $view['form']->row($form['description']);
?>
</div>
<div class="col-md-6">
<?php
echo $view['form']->row($form['postAction']);
echo $view['form']->row($form['postActionProperty']);
?>
</div>
</div>
</div>
<div class="tab-pane fade bdr-w-0" id="fields-container">
<?php echo $view->render('MauticFormBundle:Builder:style.html.php'); ?>
<div id="mauticforms_fields">
<div class="row">
<div class="available-fields mb-md col-sm-4">
<select class="chosen form-builder-new-component" data-placeholder="<?php echo $view['translator']->trans('mautic.form.form.component.fields'); ?>">
<option value=""></option>
<?php foreach ($fields as $fieldType => $field): ?>
<?php if (!$isadmin && ($fieldType == 'captcha' || $fieldType == 'plugin.loginSocial')): continue; endif; ?>
<option data-toggle="ajaxmodal"
data-target="#formComponentModal"
data-href="<?php echo $view['router']->path(
'mautic_formfield_action',
[
'objectAction' => 'new',
'type'         => $fieldType,
'tmpl'         => 'field',
'formId'       => $formId,
'inBuilder'    => $inBuilder,
]
); ?>">
<?php echo $field; ?>
</option>
<?php endforeach; ?>

</select>
</div>
</div>
<div class="drop-here">
<?php foreach ($formFields as $field): ?>
<?php if (!in_array($field['id'], $deletedFields)) : ?>
<?php if (!empty($field['isCustom'])):
$params   = $field['customParameters'];
$template = $params['template'];
else:
$template = 'MauticFormBundle:Field:'.$field['type'].'.html.php';
endif; ?>
<?php echo $view->render(
'MauticFormBundle:Builder:fieldwrapper.html.php',
[
'template'      => $template,
'field'         => $field,
'inForm'        => true,
'id'            => $field['id'],
'formId'        => $formId,
'contactFields' => $contactFields,
'companyFields' => $companyFields,
'inBuilder'     => $inBuilder,
]
); ?>
<?php endif; ?>
<?php endforeach; ?>
</div>
<?php if (!count($formFields)): ?>
<div class="alert alert-info" id="form-field-placeholder">
<p><?php echo $view['translator']->trans('mautic.form.form.addfield'); ?></p>
</div>
<?php endif; ?>
</div>
</div>
<div class="tab-pane fade bdr-w-0" id="actions-container">
<div id="mauticforms_actions">
<div class="row">
<div class="available-actions mb-md col-sm-4">
<select class="chosen form-builder-new-component" data-placeholder="<?php echo $view['translator']->trans('mautic.form.form.component.submitactions'); ?>">
<option value=""></option>
<?php foreach ($actions as $group => $groupActions): ?>
<?php
$campaignActionFound = false;
$actionOptions       = '';
foreach ($groupActions as $k => $e):
$actionOptions .= $view->render(
'MauticFormBundle:Action:option.html.php',
[
'action'       => $e,
'type'         => $k,
'isStandalone' => $activeForm->isStandalone(),
'formId'       => $form['sessionId']->vars['data'],
]
)."\n\n";
if (!empty($e['allowCampaignForm'])) {
$campaignActionFound = true;
}
endforeach;
$class = (empty($campaignActionFound)) ? ' action-standalone-only' : '';
if (!$campaignActionFound && !$activeForm->isStandalone()) {
$class .= ' hide';
}
?>
<optgroup class=<?php echo $class; ?> label="<?php echo $view['translator']->trans($group); ?>"></optgroup>
<?php echo $actionOptions; ?>
<?php endforeach; ?>
</select>
</div>
</div>
<div class="drop-here">
<?php foreach ($formActions as $action): ?>
<?php if (!in_array($action['id'], $deletedActions)) : ?>
<?php $template = (isset($actionSettings[$action['type']]['template']))
? $actionSettings[$action['type']]['template']
:
'MauticFormBundle:Action:generic.html.php';
$action['settings'] = $actionSettings[$action['type']];
echo $view->render(
$template,
[
'action' => $action,
'inForm' => true,
'id'     => $action['id'],
'formId' => $formId,
]
); ?>
<?php endif; ?>
<?php endforeach; ?>
</div>
<?php if (!count($formActions)): ?>
<div class="alert alert-info" id="form-action-placeholder">
<p><?php echo $view['translator']->trans('mautic.form.form.addaction'); ?></p>
</div>
<?php endif; ?>
</div>
</div>
</div>
</div>
</div>
</div>
<div class="col-md-3 bg-white height-auto bdr-l">
<div class="pr-lg pl-lg pt-md pb-md">
<?php
echo $view['form']->row($form['category']);
echo $view['form']->row($form['isPublished']);
echo $view['form']->row($form['publishUp']);
echo $view['form']->row($form['publishDown']);
echo $view['form']->row($form['inKioskMode']);
echo $view['form']->row($form['renderStyle']);
?>
</div>
<div class="pr-lg pl-lg pt-md pb-m <?php echo $isadmin ? '' : 'hide' ?>">
<?php echo $view['form']->row($form['template']); ?>
</div>
</div>
</div>
<?php

echo $view['form']->end($form); */

/*if (($activeForm->getFormType() === null || !empty($forceTypeSelection)) && ($activeForm->getName() == '' || $activeForm->getName() == null) && $objectID == null):
    echo $view->render(
        'MauticCoreBundle:Helper:form_selecttype.html.php',
        [
            'item'       => $activeForm,
            'mauticLang' => [
                'newStandaloneForm' => 'mautic.form.type.standalone.header',
                'newCampaignForm'   => 'mautic.form.type.campaign.header',
            ],
            'typePrefix'         => 'form',
            'cancelUrl'          => 'mautic_form_index',
            'header'             => 'mautic.form.type.header',
            'typeOneHeader'      => 'mautic.form.type.campaign.header',
            'typeOneIconClass'   => 'fa-cubes',
            'typeOneDescription' => 'mautic.form.type.campaign.description',
            'typeOneOnClick'     => "Mautic.selectFormType('campaign');",
            'typeTwoHeader'      => 'mautic.form.type.standalone.header',
            'typeTwoIconClass'   => 'fa-list',
            'typeTwoDescription' => 'mautic.form.type.standalone.description',
            'typeTwoOnClick'     => "Mautic.selectFormType('standalone');",
            'typeThreeHeader'    => 'le.email.editor.codeeditor.header',
        ]
    );
endif;*/ ?>
<?php echo $view['form']->start($form); ?>
<div class="page-wrap  tab-content" >
    <div  id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all tab-pane fade in active bdr-rds-0 bdr-w-0">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
            <li class="ui-state-default ui-corner-top btn btn-default btn-group" role = "tab" id = "ui-tab-header1" rel = 1><a class="info_tab">Details</a></li>
            <li class="ui-state-default ui-corner-top btn btn-default btn-group" role = "tab" id = "ui-tab-header2" rel = 2><a>Fields</a></li>
            <li class="ui-state-default ui-corner-top btn btn-default btn-group" role = "tab" id = "ui-tab-header3" rel = 3><a>Actions</a></li>
        </ul>
        <div id="fragment-1" class="ui-tabs-panel">
            <div class="fragment-1-buttons fixed-header">
                <a href="<?php echo $view['router']->path('mautic_form_index')?>" id="cancel-tab-1" class="cancel-tab hide mover btn btn-default btn-cancel le-btn-default btn-copy"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
                <a href="#" id="next-page-1" class="next-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="2"><?php echo $view['translator']->trans('le.email.wizard.next'); ?></a>
                <div class="toolbar-form-buttons" style="margin-top: -177px;margin-right: 128px;">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-nospin  dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 <?php echo (count($form['name']->vars['errors'])) ? ' has-error' : ''; ?>" id="Form_Name">
                    <?php echo $view['form']->label($form['name']); ?>
                    <?php echo $view['form']->widget($form['name']); ?>
                    <?php echo $view['form']->errors($form['name']); ?>
                    <div class="help-block custom-help"></div>
                </div>
                <div class="col-md-6">
                    <?php echo $view['form']->label($form['isPublished']); ?>
                    <?php echo $view['form']->widget($form['isPublished']); ?>
                </div>
            </div>
            <div class="row">
                <br>
                <div class="col-md-6">
                    <?php echo $view['form']->label($form['postAction']); ?>
                    <?php echo $view['form']->widget($form['postAction']); ?>
                </div>
                <div class="col-md-6 <?php echo (count($form['postActionProperty']->vars['errors'])) ? ' has-error' : ''; ?> " id="Form_post_action">
                    <?php echo $view['form']->label($form['postActionProperty']); ?>
                    <?php echo $view['form']->widget($form['postActionProperty']); ?>
                    <?php echo $view['form']->errors($form['postActionProperty']); ?>
                    <div class="help-block custom-help"></div>
                </div>
            </div>
            <div class="row hide">
                <div class="col-md-6">
                    <?php echo $view['form']->label($form['inKioskMode']); ?>
                    <?php echo $view['form']->widget($form['inKioskMode']); ?>
                </div>
                <div class="col-md-6">
                    <?php echo $view['form']->label($form['renderStyle']); ?>
                    <?php echo $view['form']->widget($form['renderStyle']); ?>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <?php echo $view['form']->label($form['category']); ?>
                    <?php echo $view['form']->widget($form['category']); ?>
                </div>
                <div class="col-md-12" >
                    <br>
                    <?php echo $view['form']->label($form['description']); ?>
                    <?php echo $view['form']->widget($form['description']); ?>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <?php echo $view['form']->label($form['publishUp']); ?>
                    <?php echo $view['form']->widget($form['publishUp']); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php echo $view['form']->label($form['publishDown']); ?>
                    <?php echo $view['form']->widget($form['publishDown']); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 <?php echo $isadmin ? '' : 'hide' ?>">
                    <?php echo $view['form']->label($form['template']); ?>
                    <?php echo $view['form']->widget($form['template']); ?>
                </div>
            </div>
        </div>
        <div id="fragment-2"  class="ui-tabs-panel ui-tabs-hide" style="overflow: hidden;">
            <div class="fragment-2-buttons fixed-header">
                <a href="#" id="#previous-button" class="prev-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="1"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
                <a href="<?php echo $view['router']->path('mautic_form_index')?>" id="cancel-tab-2" data-toggle="ajax" class="cancel-tab hide mover btn btn-default btn-cancel le-btn-default btn-copy"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
                <a href="#" id="next-tab-2" class="next-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="3"><?php echo $view['translator']->trans('le.email.wizard.next'); ?></a>
                <div class="toolbar-form-buttons" style="margin-top: -177px;margin-right: 128px;">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-nospin  dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                </div>
            </div>
            <?php  echo $view->render('MauticFormBundle:Builder:style.html.php'); ?>
           <div>
            <div id="mauticforms_fields" class="col-md-8">
                <div class="drop-here">
                    <?php foreach ($formFields as $field): ?>
                        <?php if (!in_array($field['id'], $deletedFields)) : ?>
                            <?php if (!empty($field['isCustom'])):
                                $params   = $field['customParameters'];
                                $template = $params['template'];
                            else:
                                $template = 'MauticFormBundle:Field:'.$field['type'].'.html.php';
                            endif; ?>
                            <?php echo $view->render(
                                'MauticFormBundle:Builder:fieldwrapper.html.php',
                                [
                                    'template'      => $template,
                                    'field'         => $field,
                                    'inForm'        => true,
                                    'id'            => $field['id'],
                                    'formId'        => $formId,
                                    'contactFields' => $contactFields,
                                    'companyFields' => $companyFields,
                                    'inBuilder'     => $inBuilder,
                                ]
                            ); ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-4" >
                <div class="available-fields">
                    <div class="alert alert-info" id="form-field-placeholder" style="width: 92%;margin-left: 14px;">
                        <p><?php echo $view['translator']->trans('mautic.form.form.addfield'); ?></p>
                    </div>
                    <center>
                        <div class="form_fragment2_tite" >Add a New Field</div><br></center>
                    <div style="margin-left: 11px">
                        <?php foreach ($fields as $fieldType => $field): ?>
                            <?php if (!$isadmin && ($fieldType == 'captcha' || $fieldType == 'plugin.loginSocial')): continue; endif; ?>

                            <div class=" form_fragment2_data" data-toggle="ajaxmodal"
                                 data-target="#formComponentModal"
                                 data-href="<?php echo $view['router']->path(
                                     'mautic_formfield_action',
                                     [
                                         'objectAction' => 'new',
                                         'type'         => $fieldType,
                                         'tmpl'         => 'field',
                                         'formId'       => $formId,
                                         'inBuilder'    => $inBuilder,
                                     ]
                                 ); ?>" >
                                <a  style="color:#ffffff">
                                    <?php echo $field; ?>
                                </a></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                </div>
            </div>


        </div>
        <div id="fragment-3" class=" ui-tabs-panel ui-tabs-hide">
            <div class="fragment-2-buttons fixed-header">
                <a href="#" style="margin-left:-112px;" class="prev-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="2"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
                <div class="toolbar-form-buttons" style="margin-top: -177px;margin-right: 15px;">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-nospin  dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                </div>
            </div>

            <div id="mauticforms_actions" class="col-md-8">
                <div class="drop-here">
                    <?php foreach ($formActions as $action): ?>
                        <?php if (!in_array($action['id'], $deletedActions)) : ?>
                            <?php $template = (isset($actionSettings[$action['type']]['template']))
                                ? $actionSettings[$action['type']]['template']
                                :
                                'MauticFormBundle:Action:generic.html.php';
                            $action['settings'] = $actionSettings[$action['type']];
                            echo $view->render(
                                $template,
                                [
                                    'action' => $action,
                                    'inForm' => true,
                                    'id'     => $action['id'],
                                    'formId' => $formId,
                                ]
                            ); ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="available-actions col-md-4" style="margin-top: -35px;"> <br><br>
                <div class="alert alert-info" id="form-action-placeholder" style="width: 92%;">
                    <p><?php echo $view['translator']->trans('mautic.form.form.addaction'); ?></p>
                </div>
                <center>
                    <div class="form_fragment3_tite col-md-11" >Add A New Submit Action</div><br></center>
                <?php foreach ($actions as $group => $groupActions): ?>
                    <?php
                    $campaignActionFound = false;
                    $actionOptions       = '';
                    foreach ($groupActions as $k => $e):
                        $actionOptions .= $view->render(
                                'MauticFormBundle:Action:option.html.php',
                                [
                                    'action'       => $e,
                                    'type'         => $k,
                                    'isStandalone' => $activeForm->isStandalone(),
                                    'formId'       => $form['sessionId']->vars['data'],
                                ]
                            )."\n\n";
                        if (!empty($e['allowCampaignForm'])) {
                            $campaignActionFound = true;
                        }
                    endforeach;
                    $class = (empty($campaignActionFound)) ? ' action-standalone-only' : '';
                    if (!$campaignActionFound && !$activeForm->isStandalone()) {
                        $class .= ' hide';
                    }
                    /*
                ?>
                    <optgroup class=<?php echo $class; ?> label="<?php echo $view['translator']->trans($group);  "></optgroup> */?>
                    <?php echo $actionOptions; ?>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</div>
</div>

<?php echo $view['form']->end($form); ?>
<?php
$view['slots']->append(
    'modal',
    $this->render(
        'MauticCoreBundle:Helper:modal.html.php',
        [
            'id'            => 'formComponentModal',
            'header'        => false,
            'footerButtons' => true,
        ]
    )
);
?>
