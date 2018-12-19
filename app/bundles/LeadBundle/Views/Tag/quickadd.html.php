<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

?>
<?php echo $view['form']->start($form); ?>
<div class="row">
    <div class="col-md-12">
        <?php echo $view['form']->row($form['tag']); ?>
    </div>
    <div class="col-md-12" style="margin-top: 15px;">
        <?php echo $view['form']->row($form['is_published']); ?>
    </div>
</div>
<?php echo $view['form']->end($form); ?>
