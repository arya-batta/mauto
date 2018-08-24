<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<div class="row">
    <div class="col-xs-8">
        <?php echo $view['form']->row($form['sms']); ?>
    </div>
    <div class="col-xs-4 mt-lg">
        <div class="mt-3">
            <?php echo $view['form']->row($form['newSmsButton']); ?>
        </div>
    </div>
</div>
<?php if (isset($form['user_id'])):?>
<div class="row" >
    <div class="col-md-6">
        <?php echo $view['form']->row($form['user_id']); ?>
    </div>
    <div class="col-md-6">
        <?php echo $view['form']->row($form['to_owner']); ?>
    </div>

</div>
<?php endif; ?>