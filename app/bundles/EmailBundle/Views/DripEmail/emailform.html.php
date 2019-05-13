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
            'class'   => 'btn btn-default btn-dnd btn-nospin text-primary btn-beeditor le-btn-default m_down blue-theme-bg drip-btn-beeditor',
            'onclick' => "Le.launchBeeEditor('dripemail', 'email');",
            'style'   => 'color:#ffffff;border-radius:4px;z-index:499;margin-top:-31px;right:-156px;',
        ],
    ],
];
$buttonpanelcss = 'margin-top: -60px;';
$marginRight    = 'margin-right: 208px;';
if (!$isBeeEditor) {
    $buttonpanelcss = 'margin-top: -60px;';
    $marginRight    = 'margin-right: 21px;';
}
$customHtml = $entity->getCustomHtml();
?>
<div class="row" style="position: fixed;z-index: 520;right: 0;<?php echo $marginRight; ?>">
        <div class="toolbar-form-buttons drip-fixed-header" style="<?php echo $buttonpanelcss; ?>">
            <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
            <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                <button type="button" class="btn btn-default btn-nospin  dropdown-toggle" data-toggle="dropdown"
                        aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
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
<?php echo $view['form']->start($form); ?>
    <div class="box-layout le-email-border">
        <div class="col-md-12 bg-auto height-auto">
            <div class="align-tab-center">
                <div class="row le-border" style="margin-top: 20px;">
                    <div class="col-md-8" style="top: 6px;">
                        <div class="col-md-10 <?php echo (count($form['subject']->vars['errors'])) ? ' has-error' : ''; ?>" id="DripEmail_Subject" style="right:15px;">
                            <?php echo $view['form']->row($form['subject'],
                                ['attr' => ['style' =>'background-color: #fff;']]
                            ); ?>
                        </div>
                        <div class="col-md-2" style="right:25px;">
                            <li class="dropdown dropdown-menu-right" style="display: block;">
                                <a class="btn btn-nospin btn-primary btn-sm hidden-xs " style="position: relative;font-size: 14px;top: 25px;right:5px;vertical-align: super;" data-toggle="dropdown" href="#">
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

                    <div class="col-md-4" style="top: 9px;">
                        <?php echo $view['form']->row($form['previewText'],
                            ['attr' => ['style' =>'background-color: #fff;']]
                        ); ?>
                    </div>
                </div>
                <br>
                <div class="row <?php echo $isBeeEditor ? 'hide' : 'le-border'; ?>">
                    <div class="col-md-12">
                        <div class="tab-pane fade in bdr-w-0 dripemail_content <?php echo $isBeeEditor ? 'hide' : ''; ?>" style="margin-top: 11px;margin-bottom: 14px;" id="dripemail_basic_editor">
                            <?php echo $view['form']->widget($form['customHtml']); ?>
                        </div>
                    </div>
                </div>
                <div class="row <?php echo ($isBeeEditor) ? 'le-border' : 'hide'; ?>">
                    <div class="col-md-12">
                        <div class="tab-pane fade in bdr-w-0 dripemail_content <?php echo ($isBeeEditor && $customHtml == '') ? '' : 'hide'; ?>" id="dripemail_advance_editor" style="margin-top:-30px;">
                            <br>
                            <div id="block_container" style="margin-top: 17px;margin-left: -4px;">
                                <div class="le-category-filter alert alert-info le-alert-info" style="background-color: #FFFFFF;" >
                                    <p class="info-box-text" style="margin-top: -8px;"><?php echo $view['translator']->trans('le.email.category.notification'); ?></p>
                                    <?php if (!empty($filters)): ?>
                                        <?php echo $view->render('MauticCoreBundle:Helper:list_filters.html.php', [
                                            'filters' => $filters,
                                            'target'  => (empty($target)) ? null : $target,
                                            'tmpl'    => (empty($tmpl)) ? 'form' : $tmpl,
                                            'screen'  => 'email',
                                        ]); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="alert alert-info le-alert-info" id="form-action-placeholder" style="width:66.5%;margin-left:29px;background-color: #FFFFFF;">
                                    <p><?php echo $view['translator']->trans('le.email.notification'); ?></p>
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
                            <div style="margin-top:10px;">
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
                        <a class="btn btn-default text-primary hide le-btn-default blue-theme-bg" onclick="Le.showDripEmailTemplateview();" style="color:#ffffff;padding-top: 7px;float: right;margin-right: 3%;border-radius:4px;z-index:1003;" data-toggle="ajax">
                                <span>
                                <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.core.change.template'); ?></span>
                                </span>
                        </a>
                        <a class="btn btn-default text-primary hide le-btn-default blue-theme-bg" onclick="Le.launchBeeEditor('dripemail', 'email');" style="color:#ffffff;padding-top: 7px;float: right;margin-right: 5px;border-radius:4px;z-index:1003;" data-toggle="ajax">
                                <span>
                                <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.core.edit'); ?></span>
                                </span>
                        </a>
                    </div>
                    <div class="<?php echo ($isBeeEditor && $customHtml != '') ? '' : 'hide'; ?>" id="email-content-preview" style="padding:10px;width:95%;margin-left:3%;">
                        <?php echo $customHtml; ?>
                    </div>
                </div>
                <div class="hide">
                    <?php echo $view['form']->rest($form); ?>
                </div>
            </div>
        </div>
    </div>
    </div>
<?php echo $view['form']->end($form); ?>

<?php echo $view->render('MauticEmailBundle:Email:beeeditor.html.php', ['objectId'      => $entity->getSessionId(), 'type'          => 'email']); ?>