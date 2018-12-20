<?php

?>
<div class="smart-field-holder" style="display: flex;margin-bottom: 10px">
<div class="smartfield" style="width: 50%;padding: 10px;pointer-events: none;">
    <?php echo $view['form']->widget($form['smartfield']); ?>
</div>
<div class="leadfield" style="width: 50%;padding: 10px;">
    <?php echo $view['form']->widget($form['leadfield']); ?>
</div>
</div>