<div class="form-group col-xs-12 ">
    <div id="fb_customaudience_properties">
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
</div>