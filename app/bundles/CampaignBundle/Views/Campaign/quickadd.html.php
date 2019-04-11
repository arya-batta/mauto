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
<p><h4 style="font-size: 14px;margin-top: -32px;margin-bottom: 24px;font-weight: normal;"><?php echo $view['translator']->trans('le.campaign.new.add.desc'); ?></h4></p>
<?php echo $view['form']->start($form); ?>
    <div class="row">
    <div class="col-md-12">
        <?php echo $view['form']->row($form['name']); ?>
    </div>
    <div class="col-md-12" style="margin-top: 15px;">
        <?php echo $view['form']->row($form['category']); ?>
    </div>
</div>
<?php echo $view['form']->end($form); ?>
