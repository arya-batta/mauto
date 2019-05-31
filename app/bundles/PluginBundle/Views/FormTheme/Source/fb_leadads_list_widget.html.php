
<?php
$pageID         =$form['fbpage']->vars['value'];
$isFbAuthorized = $form->vars['isauthorized'];
$addhideClass   ='';
if ($pageID == '-1') {
    $addhideClass='hide';
}
?>
<div class="form-group col-xs-12 ">
    <div id="fb_leadads_properties" class="<?php echo $isFbAuthorized ? '' : 'hide' ?>">
        <div class="row">
            <div class="form-group col-xs-12  <?php echo (count($form['fbpage']->vars['errors'])) ? ' has-error' : ''; ?>" id="fbldads-page">
                <?php echo $view['form']->label($form['fbpage']); ?>
                <?php echo $view['form']->widget($form['fbpage']); ?>
                <?php echo $view['form']->errors($form['fbpage']); ?>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-xs-12 <?php echo $addhideClass; echo (count($form['leadgenform']->vars['errors'])) ? ' has-error' : ''; ?>" id="fbldads-leadgenform">
                <?php echo $view['form']->label($form['leadgenform']); ?>
                <?php echo $view['form']->widget($form['leadgenform']); ?>
                <?php echo $view['form']->errors($form['leadgenform']); ?>
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
                            Head to your <a style="text-decoration: underline;font-weight: bold;" href="<?php echo $view['router']->generate('le_integrations_config', ['name'=>'facebook_lead_ads']); ?>">integration settings</a> to wire up your account.
                        </p>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>