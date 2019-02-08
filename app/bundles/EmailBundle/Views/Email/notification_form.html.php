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
$view['slots']->set('leContent', 'email');

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
    $view['translator']->trans('le.email.notification.header.edit',
        ['%name%' => $email->getName()]) :
    $view['translator']->trans('le.email.notification.header.new');

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
$isCodeMode           = ($email->getTemplate() === 'le_code_mode');
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
$isgoogletags= false; //$email->getGoogletags();
?>
<?php /** ?>
<div class="fixed-header" style="margin-top: -75px;left: 75%;position: fixed;">
    <div class="toolbar-form-buttons  pull-right">
        <div class="btn-group toolbar-standard hidden-xs hidden-sm ">
            <button type="button" class="btn btn-default btn-cancel le-btn-default btn-copy" id="emailform_buttons_cancel_toolbar">
                Cancel</button>
            <button type="button" class="btn btn-default btn-save le-btn-default btn-copy" id="emailform_buttons_save_toolbar">
                <i class="fa fa-save "></i>
                Save &amp; Close</button>
        </div>
        <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
        </div>
    </div>
</div>
      <?php */ ?>
<?php echo $view['form']->start($form, ['attr' => $attr]); ?>
<div class="center-align-container">
    <div class="template-content" style="padding: 26px;display: block;border-width: 0;background: none;border: 1px solid #ccc;border-radius: 2px;min-height: 500px;max-width: 942px;align-items: center;margin-top: 12px;">
        <div class="row">
            <div class="col-md-12 <?php echo (count($form['name']->vars['errors'])) ? ' has-error' : ''; ?>" id="Email_TemplateName">
                <?php echo $view['form']->label($form['name']); ?>
                <?php echo $view['form']->widget($form['name']); ?>
                <?php echo $view['form']->errors($form['name']); ?>
                <div class="help-block custom-help"></div>
            </div>
        </div>
        <div class="row" style="width: 125%;">
            <div class="col-md-10">
                <?php echo $view['form']->row($form['fromName']); ?>
            </div>
        </div>
        <div class="row" style="width: 125%;">
            <div class="col-md-10">
                <div class="pull-left" id="email_FromAddress" style="width:80%;">
                    <?php echo $view['form']->row($form['fromAddress'],
                        ['attr' => ['tabindex' => '-1', 'style' =>'pointer-events: none;background-color: #ebedf0;opacity: 1;']]); ?>
                </div>
                <?php echo $view['form']->widget($form['fromAddress']); ?>
                <li class="dropdown" name="verifiedemails" id="verifiedemails" style="display: block;margin-left: 100px;">
                    <a class="btn btn-nospin btn-primary btn-sm hidden-xs" style="font-size:13px;margin-top:25px;" data-toggle="dropdown" href="#">
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
        <div class="row" style="width: 120%;">
            <div class="col-md-8 <?php echo (count($form['subject']->vars['errors'])) ? ' has-error' : ''; ?>" id="Email_Subject" style="width: 71%;">
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
                    <ul class="dropdown-menu dropdown-menu-right" style="margin-top: 23px;margin-right: 130px;">
                        <li>
                            <div class="insert-tokens" style="background-color: whitesmoke;/*width: 350px;*/overflow-y: scroll;max-height: 154px;">
                            </div
                        </li>
                    </ul>
                </li>
            </div>
        </div>
        <br>
        <div class="row" style="width: 125%;">
            <div class="tab-pane fade in bdr-w-0 col-md-10" style="margin-top:-8px;" id="email-basic-container">
                <?php echo $view['form']->widget($form['customHtml']); ?>
            </div>
        </div>
        <br>
    </div>
    <div class="hide">
        <div class="col-md-6">
            <?php echo $view['form']->row($form['category']); ?>
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
        <div id="email-other-container">



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
                            $linkText = $dynamicContent['tokenName']->vars['value'] ?: $view['translator']->trans('le.core.dynamicContent').' '.($i + 1);

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


