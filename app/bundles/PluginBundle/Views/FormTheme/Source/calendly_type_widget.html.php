
<?php
$iscalendlyAuthorized = $form->vars['isauthorized'];
?>
<div class="form-group col-xs-12 ">
    <div id="instapage_properties" class="<?php echo $iscalendlyAuthorized ? '' : 'hide' ?>">
        <div class="row">
            <div class="form-group col-xs-12">
                <?php echo $view['form']->label($form['event_name']); ?>
                <?php echo $view['form']->widget($form['event_name']); ?>
                <?php echo $view['form']->errors($form['event_name']); ?>
                <p style="text-align: right;"><?php echo $view['translator']->trans('le.integration.calendly.pagename.desc'); ?></p>
            </div>
        </div>
    </div>
    <div id="calendly-connection" class="<?php echo $iscalendlyAuthorized ? 'hide' : '' ?>">
        <div class="row">
            <div class="form-group col-xs-12">
                <div class="calendly-integration-connection">
                    <span>
                        <h4>
                            <i class="mdi mdi-rotate-3d" style="font-size:24px;"></i>
                            <?php echo $view['translator']->trans('le.integration.source.calendly.help.heading'); ?></h4>
                        <p>
                            Head to your <a style="text-decoration: underline;font-weight: bold;" href="<?php echo $view['router']->generate('le_integrations_config', ['name'=>'calendly']); ?>">integration settings</a> to wire up your account.
                        </p>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>