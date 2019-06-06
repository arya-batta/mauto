<?php
$isFbAuthorized = $form->vars['isauthorized'];
?>
<div class="form-group col-xs-12 ">
    <div id="slack_message_properties" class="<?php echo $isFbAuthorized ? '' : 'hide' ?>">
        <div class="row">
            <div class="form-group col-xs-12  <?php echo (count($form['channellist']->vars['errors'])) ? ' has-error' : ''; ?>" id="fca-channellist">
                <?php echo $view['form']->label($form['channellist']); ?>
                <?php echo $view['form']->widget($form['channellist']); ?>
                <?php echo $view['form']->errors($form['channellist']); ?>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-xs-12  <?php echo (count($form['slacklist']->vars['errors'])) ? ' has-error' : ''; ?>" id="fca-slacklist">
                <?php echo $view['form']->label($form['slacklist']); ?>
                <?php echo $view['form']->widget($form['slacklist']); ?>
                <?php echo $view['form']->errors($form['slacklist']); ?>
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
                            <?php echo $view['translator']->trans('le.integration.source.slack.help.heading'); ?></h4>
                        <p>
                            Head to your <a style="text-decoration: underline;font-weight: bold;" href="<?php echo $view['router']->generate('le_integrations_config', ['name'=>'slack']); ?>">integration settings</a> to wire up your account.
                        </p>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>