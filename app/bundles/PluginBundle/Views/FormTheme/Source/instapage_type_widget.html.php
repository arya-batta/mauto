
<?php

?>
<div class="form-group col-xs-12 ">
    <div id="instapage_properties">
        <div class="row">
            <div class="form-group col-xs-12">
                <?php echo $view['form']->label($form['page_name']); ?>
                <?php echo $view['form']->widget($form['page_name']); ?>
                <?php echo $view['form']->errors($form['page_name']); ?>
                <p style="text-align: right;"><?php echo $view['translator']->trans('le.integration.instapage.pagename.desc'); ?></p>
            </div>
        </div>
    </div>
</div>