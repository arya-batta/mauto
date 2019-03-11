
<div id="fixed-header">
<div class="page-header <?php echo $enableHeader; ?>">
    <div class="box-layout" style="position: relative;top: 6px;">
        <div class="col-xs-5 col-sm-6 col-md-8 va-m">
            <h3 class="pull-left header_text <?php $view['slots']->output('hideHeaderCss'); ?>"><?php $view['slots']->output('headerTitle'); ?></h3>
            <div class="col-xs-2 text-right pull-left">
                <?php $view['slots']->output('publishStatus'); ?>
            </div>
            <?php echo $view['content']->getCustomContent('page.header.left', $mauticTemplateVars); ?>
        </div>
        <div class="col-xs-7 col-sm-6 col-md-7 va-m">
            <div class="toolbar text-right" id="toolbar">
                <?php $view['slots']->output('actions'); ?>

                <div class="toolbar-bundle-buttons pull-left"><?php $view['slots']->output('toolbar'); ?></div>
                <div class="toolbar-form-buttons hide pull-right" style="margin-left: -35px">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-main waves-effect"></button>
                        <button type="button" class="btn btn-default btn-nospin  dropdown-toggle waves-effect" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                </div>
                <?php echo $view['content']->getCustomContent('page.header.right', $mauticTemplateVars); ?>
            </div>
            <div class="clearfix"></div>

        </div>

    </div>
</div>
</div>