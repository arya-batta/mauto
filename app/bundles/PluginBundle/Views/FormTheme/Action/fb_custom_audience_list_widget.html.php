<?php
$isFbAuthorized = $form->vars['isauthorized'];
?>
<div class="form-group col-xs-12 ">
    <div id="fb_customaudience_properties" class="<?php echo $isFbAuthorized ? '' : 'hide' ?>">
        <div class="row">
            <div class="form-group col-xs-12  <?php echo (count($form['adaccount']->vars['errors'])) ? ' has-error' : ''; ?>" id="fca-adaccount">
                <?php echo $view['form']->label($form['adaccount']); ?>
                <?php echo $view['form']->widget($form['adaccount']); ?>
                <?php echo $view['form']->errors($form['adaccount']); ?>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-xs-12  <?php echo (count($form['customaudience']->vars['errors'])) ? ' has-error' : ''; ?>" id="fca-customaudience">
                <?php echo $view['form']->label($form['customaudience']); ?>
                <?php echo $view['form']->widget($form['customaudience']); ?>
                <?php echo $view['form']->errors($form['customaudience']); ?>
            </div>
        </div>
    </div>
    <div id="fb-connection" class="<?php echo $isFbAuthorized ? 'hide' : '' ?>">
        <div class="row">
            <div class="form-group col-xs-12">
                <div class="facebook-integration-connection">
                    <span>
                        <i class="mdi mdi-rotate-3d" style="font-size:28px;"></i>
                        <h4>
                            <?php echo $view['translator']->trans('le.integration.source.facebook.help.heading'); ?></h4>
                        <p>
                            Head to your <a style="text-decoration: underline;font-weight: bold;" href="<?php echo $view['router']->generate('le_integrations_config', ['name'=>'facebook_custom_audiences']); ?>">integration settings</a> to wire up your account.
                        </p>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>