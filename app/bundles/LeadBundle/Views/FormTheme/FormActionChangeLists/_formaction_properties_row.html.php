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
    <div class="col-xs-12">
        <?php echo $view['form']->row($form['addToLists']); ?>
        <p style="font-size: 12px;"><?php echo $view['translator']->trans('le.lead.list.optin.selection.info'); ?></p>
    </div>
</div>
<div class="row hide">
    <div class="col-xs-6">
        <?php echo $view['form']->row($form['removeFromLists']); ?>
    </div>
</div>

