<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$isAdmin=$view['security']->isAdmin();
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $view['translator']->trans('le.config.tab.webhookconfig'); ?></h3>
    </div>
    <div class="panel-body">
        <?php // foreach ($form->children as $f):?>
            <div class="row">
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['queue_mode']); ?>
                </div>
            </div>
            <div class="row <?php echo $isAdmin ? '' : 'hide' ?>">
                <div class="col-md-6">
                    <?php echo $view['form']->row($form['events_orderby_dir']); ?>
                </div>
            </div>
        <?// php endforeach; ?>
    </div>
</div>