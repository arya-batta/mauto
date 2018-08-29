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
$subheader     = ($variantParent) ? '<div><span class="small">'.$view['translator']->trans('mautic.core.variant_of', [
        '%name%'   => $email->getName(),
        '%parent%' => $variantParent->getName(),
    ]).'</span></div>' : '';

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
if ($formcontainserror) {
    $activatebasiceditor  ='';
    $activateadvanceeditor='';
    $activateotherconfig  ='active in';
}
$hideawsemailoptions = '';
$style               ='80%';
$tabindex            ='-1';
$pointereventstyle   = 'pointer-events: none;background-color: #ebedf0;opacity: 1;';
if ($mailertransport != 'mautic.transport.amazon') {
    $hideawsemailoptions  = 'hide';
    $style                = '';
    $pointereventstyle    = '';
    $tabindex             = '';
}
$custombutton = [
        [
            'name'    => 'beeeditor',
            'btnText' => 'mautic.core.beeeditor',
            'attr'    => [
                'class'   => 'btn btn-default btn-dnd btn-nospin text-primary btn-beeditor le-btn-default',
                'onclick' => "Mautic.launchBeeEditor('emailform', 'email');",
            ],
            'iconClass' => 'fa fa-cube',
        ],
];
?>
<?php echo $view['form']->start($form, ['attr' => $attr]); ?>
<div id="page-wrap" class="tab-content">
    <div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all tab-pane fade in active bdr-rds-0 bdr-w-0">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
            <li class="ui-state-default ui-corner-top btn btn-default btn-group" role = "tab" id = "ui-tab-header1" rel = 1><a id="info_tab">INFO</a></li>
            <li class="ui-state-default ui-corner-top btn btn-default btn-group" role = "tab" id = "ui-tab-header2" rel = 2><a>CONTENT</a></li>
            <li class="ui-state-default ui-corner-top btn btn-default btn-group" role = "tab" id = "ui-tab-header3" rel = 3><a>SETTINGS</a></li>
        </ul>
        <div id="fragment-1" class="ui-tabs-panel">
            <a href="<?php echo $view['router']->path('mautic_email_index')?>" id="cancel-tab-1" class="cancel-tab mover btn btn-default btn-cancel le-btn-default btn-copy"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
            <a href="#" id="next-tab-1" class="next-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="2"><?php echo $view['translator']->trans('le.email.wizard.next'); ?></a>
            <br>
            <br>
            <div class="row">
                <div class="col-md-12" id="Email_TemplateName">
                    <?php echo $view['form']->label($form['name']); ?>
                    <?php echo $view['form']->widget($form['name']); ?>
                    <div class="help-block"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-10" id="Email_Subject">
                    <?php echo $view['form']->label($form['subject']); ?>
                    <?php echo $view['form']->widget($form['subject']); ?>
                    <div class="help-block"></div>
                </div>
                <div>
                    <li class="dropdown dropdown-menu-right" style="display: block;">
                        <a class="btn btn-nospin btn-primary btn-sm hidden-xs" style="position: relative;font-size: 13px;top: 22px;" data-toggle="dropdown" href="#">
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
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['category']); ?>
                </div>
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['isPublished']); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                <?php if ($isVariant): ?>
                <?php echo $view['form']->row($form['variantSettings']); ?>

                <?php if ($isAdmin): ?>
                    <?php echo $view['form']->row($form['publishUp']); ?>
                    <?php echo $view['form']->row($form['publishDown']); ?>
                <?php endif; ?>
                <?php else: ?>
                    <div id="leadList"<?php echo ($emailType == 'template') ? ' class="hide"' : ''; ?>>
                        <?php echo $view['form']->row($form['lists']); ?>
                    </div>

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
                    <h5><?php echo $view['translator']->trans('mautic.email.utm_tags'); ?></h5>
                    <br />
                    <?php
                    foreach ($form['utmTags'] as $i => $utmTag):?>
                        <div class="col-sm-6"><?php echo $view['form']->row($utmTag); ?></div>
                    <?php endforeach;
                    ?>
            </div>

        </div>
    </div>
    <div id="fragment-2" class="ui-tabs-panel ui-tabs-hide">
        <a href="#" id="#previous-button" class="prev-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="1"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
        <a href="<?php echo $view['router']->path('mautic_email_index')?>" id="cancel-tab-2" data-toggle="ajax" class="cancel-tab mover btn btn-default btn-cancel le-btn-default btn-copy"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
        <a href="#" id="next-tab-2" class="next-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="3"><?php echo $view['translator']->trans('le.email.wizard.next'); ?></a>
        <br>
        <br>
        <div class="uk-float-right <?php echo $activateadvanceeditor; echo $hideadvanceeditor; ?>" style="margin-top: -5.7%;margin-right: 24.7%;">
            <?php echo $view->render(
                'MauticCoreBundle:Helper:page_actions.html.php',
                [
                    'routeBase'     => 'email',
                    'langVar'       => 'email',
                    'customButtons' => $custombutton,
                ]
            ); ?>
        </div>
        <div class="<?php echo $activateadvanceeditor; echo $hideadvanceeditor; ?>" style="width:64%;">
            <?php if (!empty($filters)): ?>
                <?php echo $view->render('MauticCoreBundle:Helper:list_filters.html.php', [
                    'filters' => $filters,
                    'target'  => (empty($target)) ? null : $target,
                    'tmpl'    => (empty($tmpl)) ? null : $tmpl,
                ]); ?>
            <?php endif; ?>
        </div>
        <div class="tab-pane fade in bdr-w-0 <?php echo $activatebasiceditor; echo $hidebasiceditor; ?>" id="email-basic-container">
            <?php echo $view['form']->widget($form['customHtml']); ?>
        </div>
        <div class="tab-pane fade in bdr-w-0 <?php echo $activateadvanceeditor; echo $hideadvanceeditor; ?>" id="email-advance-container">
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
    </div>
    <div id="fragment-3" class="ui-tabs-panel ui-tabs-hide">
        <a href="#" class="prev-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="2"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
        <div class="toolbar-form-buttons pull-right">
                <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                    <button type="button" class="btn btn-default btn-nospin  dropdown-toggle" data-toggle="dropdown"
                            aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                </div>
            </div>
        <br>
        <br>
        <div id="email-other-container">
            <div class="row">
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['fromName']); ?>
                </div>
                <div class="col-md-6">
                    <div class="pull-left" style="max-width:<?php echo $style; ?>;">
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
            <?php if ($isAdmin):?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="pull-left">
                            <?php echo $view['form']->label($form['assetAttachments']); ?>
                        </div>
                        <div class="text-right pr-10">
                            <span class="label label-info" id="attachment-size"><?php echo $attachmentSize; ?></span>
                        </div>
                        <div class="clearfix"></div>
                        <?php echo $view['form']->widget($form['assetAttachments']); ?>
                    </div>
                </div>
            <?php endif; ?>
            <br>
            <div class="row">
                <div class="col-md-12" id="unsubscribe_text_div">
                    <?php echo $view['form']->label($form['unsubscribe_text']); ?>
                    <?php echo $view['form']->widget($form['unsubscribe_text']); ?>
                </div>
                <div class="col-md-12">
                    <br>
                    <?php echo $view['form']->label($form['postal_address']); ?>
                    <?php echo $view['form']->widget($form['postal_address']); ?>
                </div>
                <div class="col-md-12">
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
            'cancelUrl'           => 'mautic_email_index',
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
