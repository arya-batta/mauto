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
$view['slots']->set('mauticContent', 'email');

$dynamicContentPrototype = $form['dynamicContent']->vars['prototype'];

if (empty($form['dynamicContent']->children[0]['filters']->vars['prototype'])) {
    $filterBlockPrototype = null;
} else {
    $filterBlockPrototype = $form['dynamicContent']->children[0]['filters']->vars['prototype'];
}

if (empty($form['dynamicContent']->children[0]['filters']->children[0]['filters']->vars['prototype'])) {
    $filterSelectPrototype = null;
} else {
    $filterSelectPrototype = $form['dynamicContent']->children[0]['filters']->children[0]['filters']->vars['prototype'];
}

$variantParent = $email->getVariantParent();
$isExisting    = $email->getId();
$isCloneOp     =!empty($isClone) && $isClone;
$subheader     = ($variantParent) ? $view['translator']->trans('mautic.core.variant_of', [
    '%name%'   => $email->getName(),
    '%parent%' => $variantParent->getName(),
]) : '';

$header = $isExisting ?
    $view['translator']->trans('mautic.email.header.edit',
        ['%name%' => $email->getName()]) :
    $view['translator']->trans('mautic.email.header.new');

$view['slots']->set('headerTitle', $header.$subheader);

$emailType = $form['emailType']->vars['data'];

if (!isset($attachmentSize)) {
    $attachmentSize = 0;
}

$templates = [
    'select'    => 'select-template',
    'countries' => 'country-template',
    'regions'   => 'region-template',
    'timezones' => 'timezone-template',
    'stages'    => 'stage-template',
    'locales'   => 'locale-template',
];

$attr                 = $form->vars['attr'];
$isAdmin              =$view['security']->isAdmin();
$isCodeMode           = ($email->getTemplate() === 'mautic_code_mode');
$isbasiceditor        =$email->getBeeJSON() == null || $email->getBeeJSON() == '';
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
$hideawsemailoptions = '';
$style               ='80%';
$tabindex            ='-1';
$pointereventstyle   = 'pointer-events: none;background-color: #ebedf0;opacity: 1;';
if ($mailertransport != 'mautic.transport.amazon' && $mailertransport != 'mautic.transport.sparkpost') {
    $hideawsemailoptions  = 'hide';
    $style                = '';
    $pointereventstyle    = '';
    $tabindex             = '';
}
if ($mailertransport != 'mautic.transport.amazon') {
    $hideawsemailoptions ='hide';
    $style               ='';
}
$custombutton = [
    [
        'name'    => 'beeeditor',
        'btnText' => 'le.core.edit',
        'attr'    => [
            'class'   => 'btn btn-default btn-dnd btn-nospin text-primary btn-beeditor le-btn-default m_down',
            'onclick' => "Mautic.launchBeeEditor('emailform', 'email');",
            'style'   => 'background-color: #ec407a;color:#ffffff;padding-top: 7px;float: right;margin-left: 26%;border-radius:4px;z-index:499;margin-top:105px;',
        ],
    ],
];
$isgoogletags= false; //$email->getGoogletags();
?>
<?php echo $view['form']->start($form, ['attr' => $attr]); ?>
<div id="page-wrap" class="tab-content align-tab-center">
    <div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all tab-pane fade in active bdr-rds-0 bdr-w-0">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
            <li class="ui-state-default ui-corner-top btn btn-default btn-group <?php echo $infoulactive; ?>" role = "tab" id = "ui-tab-header1" rel = 1><a id="info_tab">INFO</a></li>
            <li class="ui-state-default ui-corner-top btn btn-default btn-group" role = "tab" id = "ui-tab-header2" rel = 2><a>CONTENT</a></li>
            <li class="ui-state-default ui-corner-top btn btn-default btn-group <?php echo $settingsulactive; ?>" role = "tab" id = "ui-tab-header3" rel = 3><a>SETTINGS</a></li>
            <div class="le-builder-btn col-md-6<?php  echo $hideadvanceeditor; ?>" style="width: 65%;float: right;">
                <div id="builder_btn" class="hide" style="margin-left: 385px;">
                    <?php echo $view->render(
                        'MauticCoreBundle:Helper:page_actions.html.php',
                        [
                            'routeBase'     => 'email',
                            'langVar'       => 'email',
                            'customButtons' => $custombutton,
                        ]
                    ); ?>
                    <a class="btn btn-default text-primary btn-beeditor le-btn-default" onclick="Mautic.showTemplateview();" style="background-color: #ec407a;color:#ffffff;padding-top: 7px;float: right;margin-right: 10%;border-radius:4px;z-index:1003;margin-right: 40px;margin-top: 105px" data-toggle="ajax">
                        <span>
                            <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.core.change.template'); ?></span>
                        </span>
                    </a>
                </div>
            </div>
        </ul>
        <div id="fragment-1" class="ui-tabs-panel <?php echo $infohide?>">
            <div class="fragment-1-buttons fixed-header">
                <a href="<?php echo $view['router']->path('mautic_email_campaign_index')?>" id="cancel-tab-1" class="cancel-tab hide mover btn btn-default btn-cancel le-btn-default btn-copy"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
                <a href="#" id="next-tab-1" class="next-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="2"><?php echo $view['translator']->trans('le.email.wizard.next'); ?></a>
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
                        <a class="btn btn-nospin btn-primary btn-sm hidden-xs " style="position: relative;font-size: 13px;top: 22px;vertical-align: super;" data-toggle="dropdown" href="#">
                            <span><?php echo $view['translator']->trans('le.core.personalize.button'); ?></span> </span><span><i class="caret" ></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" style="margin-top: 21px;">
                            <li>
                                <div class="insert-tokens" style="background-color: whitesmoke;/*width: 350px;*/overflow-y: scroll;max-height: 154px;">
                                </div
                            </li>
                        </ul>
                    </li>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php echo $view['form']->row($form['previewText']); ?>
                </div>
                <?php if (!$isVariant): ?>
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
                    <div class="col-md-6" id="Email_trafficweight">
                        <?php echo $view['form']->label($form['variantSettings']['weight']); ?>
                        <?php echo $view['form']->widget($form['variantSettings']['weight']); ?>
                        <div class="help-block" ></div>
                    </div>
                    <div class="col-md-6" id="Email_winnercriteria">
                        <?php echo $view['form']->label($form['variantSettings']['winnerCriteria']); ?>
                        <?php echo $view['form']->widget($form['variantSettings']['winnerCriteria']); ?>
                        <div class="help-block" ></div>
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
                    <h5 class="gtags <?php  echo $isgoogletags ? '' : 'hide'; ?> "><?php echo $view['translator']->trans('mautic.email.utm_tags'); ?></h5>
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
                <a href="#" id="#previous-button" class="prev-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="1"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
                <a href="<?php echo $view['router']->path('mautic_email_campaign_index')?>" id="cancel-tab-2" data-toggle="ajax" class="cancel-tab hide mover btn btn-default btn-cancel le-btn-default btn-copy"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
                <a href="#" id="next-tab-2" class="next-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="3"><?php echo $view['translator']->trans('le.email.wizard.next'); ?></a><br>
                <div class="toolbar-form-buttons" style="margin-top: -177px;margin-right: 128px;">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-nospin  dropdown-toggle" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                </div>
            </div>
            <br>
            <div class="tab-pane fade in bdr-w-0 <?php echo $activatebasiceditor; echo $hidebasiceditor; ?>" style="margin-top:-8px;" id="email-basic-container">
                <?php echo $view['form']->widget($form['customHtml']); ?>
            </div>

            <div class="tab-pane fade in bdr-w-0 <?php echo $activateadvanceeditor; echo $hideadvanceeditor; ?>" id="email-advance-container" style="margin-top:-30px;">
                <input type="text" style="height:1px;width:1px;border:0px solid;" tabindex="-1" id="builder_url_text" value="" />
                <div class="col-md-6 <?php echo $activateadvanceeditor; echo $hideadvanceeditor; ?>" style="width:100%;">
                    <div style="width: 70%">
                        <?php if (!empty($filters)): ?>
                            <?php echo $view->render('MauticCoreBundle:Helper:list_filters.html.php', [
                                'filters' => $filters,
                                'target'  => (empty($target)) ? null : $target,
                                'tmpl'    => (empty($tmpl)) ? null : $tmpl,
                            ]); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 hide">
                        <?php echo $view['form']->row($form['template']); ?>
                    </div>
                </div>
                <?php echo $view->render('MauticEmailBundle:Email:bee_template_select.html.php', [
                    'beetemplates' => $beetemplates,
                    'active'       => $form['template']->vars['value'],
                ]); ?>
            </div>
            <div class="tab-pane fade in bdr-w-0 " style="margin-top:-25px;width:100%;" id="email-preview-container">
                <div class="hide" id="email-content-preview" style="padding:10px;width:95%;margin-left:3%;border: 1px solid #000000;">

                </div>
            </div>
        </div>
        <div id="fragment-3" class="ui-tabs-panel <?php echo $settingshide?>">
            <div class="fragment-3-buttons fixed-header">
                <a href="#" style="margin-left:-112px;" class="prev-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="2"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
                <div class="toolbar-form-buttons" style="margin-top: -177px;margin-right: 30px;">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-nospin  dropdown-toggle" data-toggle="dropdown"
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
                        <div class="pull-left" id="email_FromAddress" style="max-width:<?php echo $style; ?>;">
                            <?php echo $view['form']->row($form['fromAddress'],
                                ['attr' => ['tabindex' => $tabindex, 'style' =>$pointereventstyle]]); ?>
                        </div>
                        <?php echo $view['form']->widget($form['fromAddress']); ?>
                        <li class="dropdown <?php echo $hideawsemailoptions; ?>" name="verifiedemails" id="verifiedemails" style="display: block;margin-left: 191px;">
                            <a class="btn btn-nospin btn-primary btn-sm hidden-xs" style="font-size:13px;margin-top:23px;" data-toggle="dropdown" href="#">
                                <span><?php echo $view['translator']->trans('le.core.button.aws.load'); ?></span> </span><span><i class="caret" ></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right" id="verifiedemails">
                                <li>
                                    <?php foreach ($verifiedemail as $key=> $value): ?>
                                <li >
                                    <a class="verified-emails" id="data-verified-emails" data-verified-emails="<?php echo $value; ?>"><?php echo $value; ?></a>
                                </li>
                                <?php endforeach; ?>
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
                    <div class="col-md-6" id="Emailasset_Attachments">
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
                    <div class="col-md-12 <?php echo $activatebasiceditor; echo $hidebasiceditor; ?>" id="unsubscribe_text_div">
                        <br>
                        <br>
                        <?php echo $view['form']->label($form['unsubscribe_text']); ?>
                        <?php echo $view['form']->widget($form['unsubscribe_text']); ?>
                    </div>
                    <div class="col-md-12 hide">
                        <br>
                        <div class="pull-left">
                            <?php echo $view['form']->label($form['plainText']); ?>
                        </div>
                        <div class="text-right pr-10">
                            <i class="fa fa-spinner fa-spin ml-2 plaintext-spinner hide"></i>
                            <a class="small" onclick="Mautic.autoGeneratePlaintext();"><?php echo $view['translator']->trans('mautic.email.plaintext.generate'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <?php echo $view['form']->widget($form['plainText']); ?>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade bdr-w-0 <?php echo $isAdmin ? '' : 'hide'?>" id="dynamic-content-container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <?php
                            $tabHtml = '<div class="col-xs-3 dynamicContentFilterContainer">';
                            $tabHtml .= '<ul class="nav nav-tabs tabs-left" id="dynamicContentTabs">';
                            $tabHtml .= '<li><a href="javascript:void(0);" role="tab" class="btn btn-primary" id="addNewDynamicContent"><i class="fa fa-plus text-success"></i> '.$view['translator']->trans('mautic.core.form.new').'</a></li>';
                            $tabContentHtml = '<div class="tab-content pa-md col-xs-9" id="dynamicContentContainer">';

                            foreach ($form['dynamicContent'] as $i => $dynamicContent) {
                                $linkText = $dynamicContent['tokenName']->vars['value'] ?: $view['translator']->trans('mautic.core.dynamicContent').' '.($i + 1);

                                $tabHtml .= '<li class="'.($i === 0 ? ' active' : '').'"><a role="tab" data-toggle="tab" href="#'.$dynamicContent->vars['id'].'">'.$linkText.'</a></li>';

                                $tabContentHtml .= $view['form']->widget($dynamicContent);
                            }

                            $tabHtml .= '</ul></div>';
                            $tabContentHtml .= '</div>';

                            echo $tabHtml;
                            echo $tabContentHtml;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
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


<div id="dynamicContentPrototype" data-prototype="<?php echo $view->escape($view['form']->widget($dynamicContentPrototype)); ?>"></div>
<?php if ($filterBlockPrototype instanceof FormView) : ?>
    <div id="filterBlockPrototype" data-prototype="<?php echo $view->escape($view['form']->widget($filterBlockPrototype)); ?>"></div>
<?php endif; ?>
<?php if ($filterSelectPrototype instanceof FormView) : ?>
    <div id="filterSelectPrototype" data-prototype="<?php echo $view->escape($view['form']->widget($filterSelectPrototype)); ?>"></div>
<?php endif; ?>

<div class="hide" id="templates">
    <?php foreach ($templates as $dataKey => $template): ?>
        <?php $attr = ($dataKey == 'tags') ? ' data-placeholder="'.$view['translator']->trans('mautic.lead.tags.select_or_create').'" data-no-results-text="'.$view['translator']->trans('mautic.lead.tags.enter_to_create').'" data-allow-add="true" onchange="Mautic.createLeadTag(this)"' : ''; ?>
        <select class="form-control not-chosen <?php echo $template; ?>" name="emailform[dynamicContent][__dynamicContentIndex__][filters][__dynamicContentFilterIndex__][filters][__name__][filter]" id="emailform_dynamicContent___dynamicContentIndex___filters___dynamicContentFilterIndex___filters___name___filter"<?php echo $attr; ?>>
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
            'mauticLang' => [
                'newListEmail'     => 'mautic.email.type.list.header',
                'newTemplateEmail' => 'mautic.email.type.template.header',
            ],
            'typePrefix'          => 'email',
            'cancelUrl'           => $type == 'template' ? 'mautic_email_index' : 'mautic_email_campaign_index',
            'header'              => 'mautic.email.type.header',
            'typeOneHeader'       => 'mautic.email.type.template.header',
            'typeOneIconClass'    => 'fa-cube',
            'typeOneDescription'  => 'mautic.email.type.template.description',
            'typeOneOnClick'      => "Mautic.selectEmailType('template');",
            'typeTwoHeader'       => 'mautic.email.type.list.header',
            'typeTwoIconClass'    => 'fa-pie-chart',
            'typeTwoDescription'  => 'mautic.email.type.list.description',
            'typeTwoOnClick'      => "Mautic.selectEmailType('list');",
            'typeThreeHeader'     => 'mautic.email.editor.codeeditor.header',
            'typeThreeIconClass'  => 'fas fa-code',
            'typeThreeOnClick'    => "Mautic.selectEmailEditor('code');",
            'typeThreeDescription'=> 'mautic.email.editor.codeeditor.description',
        ]);
endif;
?>
<?php
$type    = $email->getEmailType();
if (empty($updateSelect) && !$isCloneOp && !$isExisting && !$formcontainserror && !$variantParent && !$isMobile):
    echo $view->render('MauticEmailBundle:Email:email_selecttype.html.php',
        [
            'item'                => $email,
            'mauticLang'          => [],
            'typePrefix'          => 'email',
            'cancelUrl'           => $type == 'template' ? 'mautic_email_index' : 'mautic_email_campaign_index',
            'header'              => 'mautic.email.editor.header',
            'typeOneHeader'       => 'mautic.email.editor.basic.header',
            'typeOneIconClass'    => 'fa-cube',
            'typeOneDescription'  => 'mautic.email.editor.basic.description',
            'typeOneOnClick'      => "Mautic.selectEmailEditor('basic');",
            'typeTwoHeader'       => 'mautic.email.editor.advance.header',
            'typeTwoIconClass'    => 'fa-pie-chart',
            'typeTwoDescription'  => 'mautic.email.editor.advance.description',
            'typeTwoOnClick'      => "Mautic.selectEmailEditor('advance');",
            'typeThreeHeader'     => 'mautic.email.editor.codeeditor.header',
            'typeThreeIconClass'  => 'fas fa-code',
            'typeThreeOnClick'    => "Mautic.selectEmailEditor('code');",
            'typeThreeDescription'=> 'mautic.email.editor.codeeditor.description',
        ]);
endif;
?>
