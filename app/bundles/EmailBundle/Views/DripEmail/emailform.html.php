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

$header = $view['translator']->trans('le.drip.email.edit.text',
        ['%NUM%' => $EmailCount]);

$view['slots']->set('headerTitle', $header);
$activatebasiceditor  = 'hide';
$activateadvanceeditor= 'hide';
$hidebasiceditor      = 'hide';
$hideadvanceeditor    = 'hide';
$isAdmin              = $view['security']->isAdmin();

$custombutton = [
    [
        'name'    => 'beeeditor',
        'btnText' => 'le.drip.email.open.editor',
        'attr'    => [
            'class'   => 'btn btn-default btn-dnd btn-nospin text-primary btn-beeditor le-btn-default m_down',
            'onclick' => "Le.launchBeeEditor('dripemail', 'email');",
            'style'   => 'background-color: #ec407a;color:#ffffff;float: right;margin-left: 26%;border-radius:4px;z-index:499;top:-137px;right:10px;padding-top:2px;padding-bottom:3px;',
        ],
    ],
];
$buttonpanelcss = 'margin-top: -80px;margin-right: 138px;';
if (!$isBeeEditor) {
    $buttonpanelcss = 'margin-top: -80px;margin-right: 10px;';
}
$customHtml = $entity->getCustomHtml();
?>
<?php echo $view['form']->start($form); ?>
    <div class="box-layout">
        <div class="col-md-12 bg-auto height-auto bdr-l">
            <div class="tab-content align-tab-center">
                <div id="fragment-1" class="ui-tabs-panel">
                    <div class="fragment-2-buttons fixed-header">
                        <div class="toolbar-form-buttons" style="<?php echo $buttonpanelcss; ?>">
                            <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                            <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                                <button type="button" class="btn btn-default btn-nospin  dropdown-toggle" data-toggle="dropdown"
                                        aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                            </div>

                        </div>
                        <div id="builder_btn">
                            <?php echo $isBeeEditor ? $view->render(
                                'MauticCoreBundle:Helper:page_actions.html.php',
                                [
                                    'routeBase'     => 'email',
                                    'langVar'       => 'email',
                                    'customButtons' => $custombutton,
                                ]
                            ) : ''; ?>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="col-md-10 <?php echo (count($form['subject']->vars['errors'])) ? ' has-error' : ''; ?>" id="DripEmail_Subject" style="right:15px;">
                            <?php echo $view['form']->label($form['subject']); ?>
                            <?php echo $view['form']->widget($form['subject']); ?>
                            <?php echo $view['form']->errors($form['subject']); ?>
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
                        <?php echo $view['form']->row($form['previewText']); ?>
                    </div>
                </div>
                <div class="row">

                </div>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="tab-pane fade in bdr-w-0 dripemail_content <?php echo $isBeeEditor ? 'hide' : ''; ?>" style="margin-top:-8px;" id="dripemail_basic_editor">
                            <?php echo $view['form']->widget($form['customHtml']); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="tab-pane fade in bdr-w-0 dripemail_content <?php echo ($isBeeEditor && $customHtml == '') ? '' : 'hide'; ?>" id="dripemail_advance_editor" style="margin-top:-30px;">
                            <br>
                            <div class="col-md-6" style="width:100%;">
                                <div style="width: 70%;margin-left:-40px;">
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
                                <div class="col-md-12 hide">
                                    <?php echo $view['form']->row($form['beeJSON']); ?>
                                </div>
                            </div>
                            <div style="margin-top:30px;">
                            <?php echo $view->render('MauticEmailBundle:Email:bee_template_select.html.php', [
                                'beetemplates' => $beetemplates,
                                'active'       => '', //$emailform['template']->vars['value'],
                                'route'        => 'drip',
                            ]); ?>
                            </div>
                        </div>
                    </div>
                <div class="tab-pane fade in bdr-w-0 <?php echo ($isBeeEditor && $customHtml != '') ? '' : 'hide'; ?>" style="width:100%;" id="email-preview-container">
                    <div id="builder_btn">
                        <a class="btn btn-default text-primary hide le-btn-default" onclick="Le.showDripEmailTemplateview();" style="background-color: #ec407a;color:#ffffff;padding-top: 7px;float: right;margin-right: 3%;border-radius:4px;z-index:1003;" data-toggle="ajax">
                                <span>
                                <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.core.change.template'); ?></span>
                                </span>
                        </a>
                        <a class="btn btn-default text-primary hide le-btn-default" onclick="Le.launchBeeEditor('dripemail', 'email');" style="background-color: #ec407a;color:#ffffff;padding-top: 7px;float: right;margin-right: 5px;border-radius:4px;z-index:1003;" data-toggle="ajax">
                                <span>
                                <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.core.edit'); ?></span>
                                </span>
                        </a>
                    </div>
                    <div class="<?php echo ($isBeeEditor && $customHtml != '') ? '' : 'hide'; ?>" id="email-content-preview" style="padding:10px;width:95%;margin-left:3%;border: 1px solid #000000;">
                        <?php echo $customHtml; ?>
                    </div>
                </div>
                <div class="hide">
                    <?php echo $view['form']->rest($form); ?>
                </div>
            </div>
        </div>
    </div>

<?php echo $view['form']->end($form); ?>

<?php echo $view->render('MauticEmailBundle:Email:beeeditor.html.php', ['objectId'      => $entity->getSessionId(), 'type'          => 'email']); ?>