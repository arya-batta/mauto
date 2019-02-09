<?php
$hasErrors     = count($form->vars['errors']);
$feedbackClass = (!empty($hasErrors)) ? ' has-error' : '';

//apply attributes to radios
$attr          = $form->vars['attr'];
$webhook= false;
if (isset($attr['class'])) {
    if (strpos($attr['class'], 'webhook') !== false) {
        $webhook = true;
    }
}
$class         = $webhook?'col-md-6':'check-col-md-4';

if (isset($attr['class'])) {
    if (strpos($attr['class'], 'sideorder') !== false) {
        $class= 'col-md-1 check-col-md-1';
    }
}
$attr['class'] = 'le-input';
?>
<?php if($webhook):?>
<div class="row">
    <div class="form-group col-xs-12 <?php echo $feedbackClass; ?>">
        <?php echo $view['form']->label($form, $label) ?>
        <div class="choice-wrapper">
            <?php if ($expanded && $multiple): ?>
            <?php $count=0; foreach ($form->children as $child): ?>
                    <?php $count++;
                    if($count % 2 == 0){
                        $row=true;
                    }else{
                        $row=false;
                    }
                     ?>
                    <?php if(!$row):?>
                       <div class= "row" >
                    <?php endif;?>
                    <div class= "<?php echo $class ; ?>" style="margin-top: 10px;" >
                        <div class="panel panel-default form-group mb-0">
                            <div class="panel-body">
                        <label class="checkboxcontroller checkboxcontroller-checkbox">
                            <?php echo $view['form']->widget($child, ['attr' => $attr]); ?>
                            <?php echo $view['translator']->trans($child->vars['label']); ?>
                            <br>
                            <span class="label label-default"><?php echo $view['translator']->trans($child->vars['value']); ?></span>
                            <div class="checkboxcontroller_indicator"></div>
                        </label>
                        </div>
                        </div>
                    </div>
                    <?php if($row):?>
                       </div>
                    <?php endif;?>

            <?php endforeach; ?>
            <?php else: ?>
            <?php echo $view['form']->widget($form); ?>
            <?php endif; ?>
            <?php echo $view['form']->errors($form); ?>
        </div>
    </div>
</div>
<?php else:?>
<div class="row ">
    <div class="form-group col-xs-12 <?php echo $feedbackClass; ?>">
        <?php echo $view['form']->label($form, $label) ?>
        <div class="choice-wrapper">
            <?php if ($expanded && $multiple): ?>
            <?php $count=0; foreach ($form->children as $child): ?>
                        <div class= "<?php echo $class ?>" >
                            <label class="checkboxcontroller checkboxcontroller-checkbox">
                                <?php echo $view['form']->widget($child, ['attr' => $attr]); ?>
                                <?php echo $view['translator']->trans($child->vars['label']); ?>
                                <div class="checkboxcontroller_indicator"></div>
                            </label>
                        </div>
            <?php endforeach; ?>
            <?php else: ?>
            <?php echo $view['form']->widget($form); ?>
            <?php endif; ?>
            <?php echo $view['form']->errors($form); ?>
        </div>
    </div>
</div>
<?php endif;?>