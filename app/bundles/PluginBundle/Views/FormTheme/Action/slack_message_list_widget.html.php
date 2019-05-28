<div class="form-group col-xs-12 ">
    <div id="slack_message_properties">
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
</div>