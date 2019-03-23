
<?php
$pageID      =$form['fbpage']->vars['value'];
$addhideClass='';
if ($pageID == '-1') {
    $addhideClass='hide';
}
?>
<div class="form-group col-xs-12 ">
    <div id="fb_leadads_properties">
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
</div>