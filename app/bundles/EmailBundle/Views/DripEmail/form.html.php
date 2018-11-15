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

?>
<?php echo $view['form']->start($form); ?>
<div id="page-wrap" class="tab-content align-tab-center">
    <div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all tab-pane fade in active bdr-rds-0 bdr-w-0">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
            <li class="ui-state-default ui-corner-top btn btn-default btn-group ui-tabs-selected ui-state-active" role = "tab" id = "ui-tab-header1" rel = 1><a id="info_tab">INFO</a></li>
            <li class="ui-state-default ui-corner-top btn btn-default btn-group" role = "tab" id = "ui-tab-header2" rel = 2><a>EMAILS</a></li>
            <li class="ui-state-default ui-corner-top btn btn-default btn-group" role = "tab" id = "ui-tab-header3" rel = 3><a>SETTINGS</a></li>
        </ul>
        <div id="fragment-1" class="ui-tabs-panel">
            <div class="fragment-1-buttons fixed-header">
                <a href="<?php echo $view['router']->path('le_email_campaign_index')?>" id="cancel-tab-1" class="cancel-tab hide mover btn btn-default btn-cancel le-btn-default btn-copy"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
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
                <div class="col-md-6 <?php echo (count($form['name']->vars['errors'])) ? ' has-error' : ''; ?>" id="dripEmail_PublicName">
                    <?php echo $view['form']->label($form['name']); ?>
                    <?php echo $view['form']->widget($form['name']); ?>
                    <?php echo $view['form']->errors($form['name']); ?>
                    <div class="help-block custom-help"></div>
                </div>
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['category']); ?>
                </div>
            </div>
            <div class="row hide">
                <div class="col-md-11 <?php echo (count($form['subject']->vars['errors'])) ? ' has-error' : ''; ?>" style="width: 88.11111%">
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
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['isPublished']); ?>
                </div>
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['google_tags']); ?>
                </div>
            </div>
            <div class="row hide">
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['previewText']); ?>
                </div>
            </div>
            <div class="row <?php echo $isAdmin ? '' : 'hide'; ?>">
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['description']); ?>
                </div>
            </div>

        </div>
        <div id="fragment-2" class="ui-tabs-panel ui-tabs-hide">
            <div class="fragment-2-buttons fixed-header">
                <a href="#" id="#previous-button" class="prev-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="1"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
                <a href="<?php echo $view['router']->path('le_email_campaign_index')?>" id="cancel-tab-2" data-toggle="ajax" class="cancel-tab hide mover btn btn-default btn-cancel le-btn-default btn-copy"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
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

            <input type="text" style="height:1px;width:1px;border:0px solid;" tabindex="-1" id="builder_url_text" value="" />
            <div class="drip-email-button-container" style="margin-top:-65px;float:right;">
                <div class="newbutton-container">
                    <li class="dropdown dropdown-menu-right" style="display: block;float:right;">
                        <a class="btn btn-nospin hidden-xs le-btn-default" style="position: relative;font-size: 13px;top: 0;vertical-align: super;" data-toggle="dropdown" href="#">
                            <span><i class="fa fa-caret-down"></i></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right" style="margin-top: 21px;">
                            <div class="insert-drip-options">
                                <div style="background:#fff;padding:24px 30px;;width:450px;height:auto;">
                                    <h1 style='font-size:16px;font-weight:bold;'><?php echo $view['translator']->trans('Which email builder would you like to use?')?></h1>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-6 editor_layout" onclick="Le.setValueforNewButton('advance_editor',this);" style="margin-left:10px;">
                                            <img height="100px" width="auto" src="<?php echo $view['assets']->getUrl('media/images/drag-drop.png')?>"/>
                                            <h4><?php echo $view['translator']->trans('le.email.editor.advance.header')?></h4>
                                            <br>
                                        </div>
                                        <div class="col-md-6 editor_layout editor_select" onclick="Le.setValueforNewButton('basic_editor',this);" style="margin-left:20px;">
                                            <img height="100px" width="auto" src="<?php echo $view['assets']->getUrl('media/images/rich-text.png')?>"/>
                                            <h4><?php echo $view['translator']->trans('le.email.editor.basic.header')?></h4>
                                            <br>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </ul>
                    </li>

                    <a class="btn btn-default le-btn-default btn-nospin" id="new-drip-email" value="basic_editor" onclick="Le.openDripEmailEditor();" style="float:right;z-index:10000;">
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
            <div class="dripemail-body" style="margin-top:-50px;">
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
                                    <a class="btn btn-nospin btn-primary btn-sm hidden-xs " style="position: relative;font-size: 13px;top: 22px;vertical-align: super;" data-toggle="dropdown" href="#">
                                        <span><?php echo $view['translator']->trans('le.core.personalize.button'); ?></span> </span><span><i class="caret" ></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-left" style="margin-top: 21px;">
                                        <li>
                                            <div class="insert-tokens" style="background-color: whitesmoke;/*width: 350px;*/overflow-y: scroll;max-height: 154px;">
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                            </div>
                        </div>

                        <div class="col-md-4">
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
                                <?php echo $view['form']->row($emailform['template']); ?>
                            </div>
                            <div class="col-md-12 hide">
                                <?php echo $view['form']->row($emailform['beeJSON']); ?>
                            </div>
                        </div>
                        <?php echo $view->render('MauticEmailBundle:Email:bee_template_select.html.php', [
                            'beetemplates' => $beetemplates,
                            'active'       => $emailform['template']->vars['value'],
                        ]); ?>
                    </div>
                    <div class="tab-pane fade in bdr-w-0 hide" style="width:100%;" id="email-preview-container">
                        <div id="builder_btn">
                            <a class="btn btn-default text-primary le-btn-default" onclick="Le.showDripEmailTemplateview();" style="background-color: #ec407a;color:#ffffff;padding-top: 7px;float: right;margin-right: 3%;border-radius:4px;z-index:1003;" data-toggle="ajax">
                                <span>
                                <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.core.change.template'); ?></span>
                                </span>
                            </a>
                            <a class="btn btn-default text-primary le-btn-default" onclick="Le.launchBeeEditor('dripemail', 'email');" style="background-color: #ec407a;color:#ffffff;padding-top: 7px;float: right;margin-right: 5px;border-radius:4px;z-index:1003;" data-toggle="ajax">
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
                <div class="" id="drip-email-list-container">
                    <?php echo $view->render('MauticEmailBundle:DripEmail:emaillist.html.php', [
                        'items'           => $items,
                        'permissions'     => $permissions,
                        'actionRoute'     => $actionRoute,
                        'translationBase' => $translationBase,
                        'entity'          => $entity,
                    ]); ?>
                </div>
            </div>
            <drip class="drip-blue-prints hide">
                <?php echo $view->render('MauticEmailBundle:DripEmail:blueprintlist.html.php', [
                    'items'           => $bluePrints,
                    'entity'          => $entity,
                    'drips'           => $drips,
                ]); ?>
            </drip>
        </div>
        <div id="fragment-3" class="ui-tabs-panel ui-tabs-hide">
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
                        <?php echo $view['form']->row($form['scheduleDate']); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="pull-left" id="email_FromAddress" >
                            <?php echo $view['form']->row($form['fromAddress']); ?>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?php echo $view['form']->row($form['replyToAddress']); ?>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?php echo $view['form']->row($form['bccAddress']); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?php echo $view['form']->row($form['daysEmailSend']); ?>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-6">
                        <br>
                        <?php echo $view['form']->label($form['postal_address']); ?>
                        <?php echo $view['form']->widget($form['postal_address']); ?>
                    </div>
                    <div class="col-md-6" id="unsubscribe_text_div">
                        <?php echo $view['form']->label($form['unsubscribe_text']); ?>
                        <?php echo $view['form']->widget($form['unsubscribe_text']); ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php echo $view['form']->end($form); ?>

<?php echo $view->render('MauticEmailBundle:Email:beeeditor.html.php', ['objectId'      => $emailEntity->getSessionId(), 'type'          => 'email']); ?>
