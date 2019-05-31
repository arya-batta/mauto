
<?php

?>
<div class="form-group col-xs-12 ">
    <div id="instapage_properties">
        <div class="row">
            <div class="form-group col-xs-12">
                <?php echo $view['form']->label($form['pagename']); ?>
                <?php echo $view['form']->widget($form['pagename']); ?>
                <?php echo $view['form']->errors($form['pagename']); ?>
                <br>
                <h5><p><?php echo $view['translator']->trans('le.lead.lead.tab.notes'); ?></p></h5>
                <li><?php echo $view['translator']->trans('le.integration.unbounce.pagename.desc'); ?></li>
                <li>Make sure you set up the <a style="text-decoration: underline;font-weight: bold;" target="_new" href="<?php echo $view['router']->generate('le_integrations_config', ['name'=>'unbounce']); ?>">AnyFunnels-Unbounce</a> integration.</li>
            </div>
        </div>
    </div>
</div>