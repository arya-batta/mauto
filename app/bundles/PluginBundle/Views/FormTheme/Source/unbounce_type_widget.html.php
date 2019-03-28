
<?php

?>
<div class="form-group col-xs-12 ">
    <div id="instapage_properties">
        <div class="row">
            <div class="form-group col-xs-12">
                <?php echo $view['form']->label($form['pagename']); ?>
                <?php echo $view['form']->widget($form['pagename']); ?>
                <?php echo $view['form']->errors($form['pagename']); ?>
                <p style="text-align: right;"><?php echo $view['translator']->trans('le.integration.unbounce.pagename.desc'); ?></p>
            </div>
        </div>
    </div>
</div>