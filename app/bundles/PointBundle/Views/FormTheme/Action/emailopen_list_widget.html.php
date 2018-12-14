<div class="form-group col-xs-12 ">
    <div id="point_properties">
        <div class="row">
            <div class="form-group col-xs-12 <?php echo (count($form['campaigntype']->vars['errors'])) ? ' has-error' : ''; ?>" id="campaignType">
                <?php echo $view['form']->label($form['campaigntype']); ?>
                <?php echo $view['form']->widget($form['campaigntype']); ?>
                <?php echo $view['form']->errors($form['campaigntype']); ?>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-xs-12 hide <?php echo (count($form['emails']->vars['errors'])) ? ' has-error' : ''; ?>" id="pointemailaction">
                <?php echo $view['form']->label($form['emails']); ?>
                <?php echo $view['form']->widget($form['emails']); ?>
                <?php echo $view['form']->errors($form['emails']); ?>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-xs-12 hide <?php echo (count($form['dripemail']->vars['errors'])) ? ' has-error' : ''; ?>" id="pointdripemailaction">
                <?php echo $view['form']->label($form['dripemail']); ?>
                <?php echo $view['form']->widget($form['dripemail']); ?>
                <?php echo $view['form']->errors($form['dripemail']); ?>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-xs-12 hide <?php echo (count($form['driplist']->vars['errors'])) ? ' has-error' : ''; ?>" id="dripemaillist">
                <?php echo $view['form']->label($form['driplist']); ?>
                <?php echo $view['form']->widget($form['driplist']); ?>
                <?php echo $view['form']->errors($form['driplist']); ?>
            </div>
        </div>
    </div>
</div>