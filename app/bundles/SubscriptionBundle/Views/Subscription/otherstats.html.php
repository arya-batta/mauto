<?php

/*
 * @copyright   2019 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

?>
<br>
<div class="box-layout">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-4 fl-left" style="min-width: 300px;width:33%;">
                <div class="email-campaign-stats bg-white lead-email-campaign-stats">
                    <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.dashboard.workflow.stats'); ?> </h2>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-orange"><?php echo $workflow?></span>
                        <?php echo $view['translator']->trans('le.dashboard.active.workflow'); ?>
                    </h3>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-green"><?php echo $goals?></span>
                        <?php echo $view['translator']->trans('le.dashboard.goals.achived'); ?>
                    </h3>
                </div>
            </div>
            <div class="col-md-4 fl-left" style="min-width: 300px;width:33%;">
                <div class="email-campaign-stats bg-white lead-email-campaign-stats">
                    <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.dashboard.form.stats'); ?> </h2>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-orange"><?php echo $forms?></span>
                        <?php echo $view['translator']->trans('le.dashboard.active.forms'); ?>
                    </h3>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-green"><?php echo $submissions?></span>
                        <?php echo $view['translator']->trans('le.dashboard.form.submissions'); ?>
                    </h3>
                </div>
            </div>
            <div class="col-md-4 fl-left" style="min-width: 300px;width:33%;">
                <div class="email-campaign-stats bg-white lead-email-campaign-stats">
                    <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.dashboard.asset.stats'); ?> </h2>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-orange"><?php echo $asset?></span>
                        <?php echo $view['translator']->trans('le.dashboard.active.assets'); ?>
                    </h3>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-green"><?php echo $downloads?></span>
                        <?php echo $view['translator']->trans('le.dashboard.files.downloads'); ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>
</div>
