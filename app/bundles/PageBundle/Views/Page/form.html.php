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
$view['slots']->set('mauticContent', 'page');
$isExisting = $activePage->getId();
$variantParent = $activePage->getVariantParent();
$subheader     = '';
if ($variantParent) {
    $subheader = '<div><span class="small">'.$view['translator']->trans('mautic.core.variant_of', [
            '%name%'   => $activePage->getTitle(),
            '%parent%' => $variantParent->getTitle(),
        ]).'</span></div>';
} elseif ($activePage->isVariant(false)) {
    $subheader = '<div><span class="small">'.$view['translator']->trans('mautic.page.form.has_variants').'</span></div>';
}
$header = $isExisting ?
    $view['translator']->trans('mautic.page.header.edit',
        ['%name%' => $activePage->getTitle()]) :
    $view['translator']->trans('mautic.page.header.new');
$view['slots']->set('headerTitle', $header.$subheader);
$template = $form['template']->vars['data'];
$attr                               = $form->vars['attr'];
$attr['data-submit-callback-async'] = 'clearThemeHtmlBeforeSave';
$isCodeMode = ($activePage->getTemplate() === 'mautic_code_mode');
$isAdmin    =$view['security']->isAdmin();
$hidepanel  =$view['security']->isAdmin() ? '' : "style='display: none;'";
$custombuttons = [
 [
    'name'  => 'beeeditor',
    'label' => 'mautic.core.beeeditor',
    'attr'  => [
        'class'   => 'btn btn-default btn-dnd btn-nospin text-primary btn-beeditor le-btn-default',
        'icon'    => 'fa fa-cube',
        'onclick' => "Mautic.launchBeeEditor('pageform', 'page');",
    ],
 ],
];
$custombutton = [
    [
        'name'    => 'beeeditor',
        'btnText' => 'mautic.core.beeeditor',
        'attr'    => [
            'class'   => 'btn btn-default btn-dnd btn-nospin text-primary btn-beeditor le-btn-default m_down',
            'onclick' => "Mautic.launchBeeEditor('pageform', 'page');",
            'style'   => "color: #ffffff;background-color: #ec407a;padding-top: 8px;border-radius: 4px;"
        ],
    ],
];
?>

<?php echo $view['form']->start($form, ['attr' => $attr]); ?>
<!-- start: box layout -->
<div class="box-layout">
    <!-- container -->
    <div class="col-md-9 bg-white height-auto">
        <div class="row">
            <div class="col-xs-12">
                <!-- tabs controls -->
                <div class="ui-tabs ui-widget ui-widget-content ui-corner-all tab-pane fade in active bdr-rds-0 bdr-w-0">
                <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all ">
                    <li class="ui-state-default ui-corner-top btn btn-default btn-group ui-tabs-selected ui-state-active" rel = 1 id="ui-tab-page-header1"><a ><?php echo $view['translator']->trans('mautic.core.form.info'); ?></a></li>
                    <li class="ui-state-default ui-corner-top btn btn-default btn-group" id="ui-tab-page-header2" rel = 2><a ><?php echo $view['translator']->trans('mautic.core.form.theme'); ?></a></li>
                    <div class="le-builder-btn col-md-6" style="width: 50%;float: right;">
                        <div style="margin-left: 385px;">
                            <?php echo $view->render(
                                'MauticCoreBundle:Helper:page_actions.html.php',
                                [
                                    'routeBase'     => 'page',
                                    'langVar'       => 'page',
                                    'customButtons' => $custombutton,
                                ]
                            ); ?>
                        </div>
                    </div>
                </ul>
                <div id="fragment-page-1" class="pr-lg pl-lg pt-md pb-md ui-tabs-panel">
                    <div class="fragment-1-buttons">
                        <a href="<?php echo $view['router']->path('mautic_page_index')?>" id="cancel-page-1" class="cancel-tab mover btn btn-default btn-cancel le-btn-default btn-copy"><?php echo $view['translator']->trans('mautic.core.form.cancel'); ?></a>
                        <a href="#" id="next-page-1" class="next-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="2"><?php echo $view['translator']->trans('le.email.wizard.next'); ?></a>
                    </div>
                    <div class="row">
                        <div class="col-md-6" id="page_Title">
                            <?php echo $view['form']->label($form['title']); ?>
                            <?php echo $view['form']->widget($form['title']); ?>
                            <div class="help-block"></div>
                        </div>
                        <?php if (!$isVariant): ?>
                            <div class="col-md-6">
                                <?php echo $view['form']->row($form['alias']); ?>
                            </div>
                        <?php else: ?>
                            <div class="col-md-6">
                                <?php echo $view['form']->row($form['template']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <?php if ($isVariant): ?>
                            <div class="col-md-6">
                                <?php echo $view['form']->row($form['variantSettings']); ?>
                            </div>
                        <?php else: ?>
                            <div class="col-md-6">
                                <?php echo $view['form']->row($form['category']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <?php echo $view['form']->row($form['isPublished']); ?>
                        </div>
                    </div>
                        <?php if (($permissions['page:preference_center:editown'] ||
                                $permissions['page:preference_center:editother']) &&
                            !$activePage->isVariant()): ?>
                    <div class="row hide">
                            <div class="col-md-6">
                                <?php echo $view['form']->row($form['isPreferenceCenter']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="col-md-6">
                            <?php echo $view['form']->row($form['noIndex']); ?>
                        </div>
                        <?php if (($permissions['page:preference_center:editown'] ||
                            $permissions['page:preference_center:editother']) &&
                        !$activePage->isVariant()): ?>
                    </div>
                <?php endif; ?>
                    <div class="row">
                        <?php if (!$isVariant): ?>
                            <div class="col-md-6">
                                <?php echo $view['form']->row($form['redirectType']); ?>
                                <?php echo $view['form']->row($form['redirectUrl']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="col-md-6 template-fields<?php echo (!$template) ? ' hide"' : ''; ?>">
                            <?php echo $view['form']->row($form['metaDescription']); ?>
                        </div>
                    </div>
                    <div <?php echo ($isAdmin) ? '' : 'class="hide"' ?>>
                        <?php if (!$isVariant): ?>
                            <?php echo $view['form']->row($form['language']); ?>
                        <?php endif; ?>
                        <?php echo $view['form']->row($form['publishUp']); ?>
                        <?php echo $view['form']->row($form['publishDown']); ?>
                        <?php echo $view['form']->row($form['translationParent']); ?>
                    </div>
                    <br>
                    <div class="hide">
                        <?php echo $view['form']->rest($form); ?>
                    </div>
                </div>
                <div id="fragment-page-2" class="ui-tabs-panel hide">
                    <!--/ tabs controls -->
                    <div class="tab-content pa-md tab-pane bdr-w-0" id="theme-container"style="border-width: 10px;border-color: #808080">
                        <div class="fragment-3-buttons" style="margin-left: 60%;">
                            <a href="#" class="prevv-tab mover btn btn-default btn-cancel le-btn-default btn-copy" rel="1"><?php echo $view['translator']->trans('le.email.wizard.prev'); ?></a>
                            <div class="toolbar-form-buttons" style="margin-top: -165px;margin-left: 122px;">
                                <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                                <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                                    <button type="button" class="btn btn-default btn-nospin  dropdown-toggle" data-toggle="dropdown"
                                            aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div style="width:50%;margin-left: 20px;">
                                    <?php if (!empty($filters)): ?>
                                        <?php echo $view->render('MauticCoreBundle:Helper:list_filters.html.php', [
                                            'filters' => $filters,
                                            'target'  => (empty($target)) ? null : $target,
                                            'tmpl'    => (empty($tmpl)) ? null : $tmpl,
                                        ]); ?>
                                    <?php endif; ?>
                                </div>
                                <?php echo $view['form']->row($form['template']); ?>
                            </div>
                        </div>

                        <!--                        --><?php //echo $view->render('MauticCoreBundle:Helper:theme_select.html.php', [
                        //                            'type'   => 'page',
                        //                            'themes' => $themes,
                        //                            'active' => $form['template']->vars['value'],
                        //                        ]);?>
                        <?php echo $view->render('MauticEmailBundle:Email:bee_template_select.html.php', [
                            'beetemplates' => $beetemplates,
                            'active'       => $form['template']->vars['value'],
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<?php echo $view['form']->row($form['customHtml']); ?>
<?php echo $view['form']->end($form); ?>
<?php echo $view->render('MauticEmailBundle:Email:beeeditor.html.php', ['objectId'      => $activePage->getSessionId(), 'type'          => 'page']); ?>
<?php //echo $view->render('MauticCoreBundle:Helper:builder.html.php', [
//    'type'          => 'page',
//    'isCodeMode'    => $isCodeMode,
//    'sectionForm'   => $sectionForm,
//    'builderAssets' => $builderAssets,
//    'slots'         => $slots,
//    'sections'      => $sections,
//    'objectId'      => $activePage->getSessionId(),
//]);?>
