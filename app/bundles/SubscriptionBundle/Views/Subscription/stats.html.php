<?php

/*
 * @copyright   2019 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$openPercentage         = 0;
$clickpercentage        = 0;
$churnpercentage        = 0;
$sentPercentage         = 0;
if ($sentcount != 0 && $allleads != 0) {
    $openPercentage        = round(($uniqueopen / $sentcount * 100), 2);
    $clickpercentage       = round(($click / $sentcount * 100), 2);
    $churnpercentage       = round((($unsubscribecount + $bouncecount + $spamcount) / $sentcount * 100), 2);
}
$activepercentage = 0;
$last7daysactive  = 0;
$last7daysadded   = 0;
if ($allleads != 0) {
    $activepercentage  = round(($activeleads / $allleads * 100), 2);
    $last7daysactive   = round(($recentactive / $allleads * 100), 2);
    $last7daysadded    = round(($recentadded / $allleads * 100), 2);
}
?>
<br>
<div class="box-layout">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6">
                <div class="email-campaign-stats bg-white lead-email-campaign-stats">
                    <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.dashboard.stat.lead.name'); ?> </h2>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-orange"><?php echo number_format($allleads)?></span>
                        <?php echo $view['translator']->trans('le.dashboard.total.leads'); ?>
                    </h3>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-green"><?php echo $activepercentage?>%</span>
                        <?php echo $view['translator']->trans('le.dashboard.total.active'); ?>
                        <span class="stat-label"><?php echo $view['translator']->trans('le.dashboard.total.active.label', ['%COUNT%' => number_format($activeleads)]); ?></span>
                    </h3>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-pink"><?php echo $last7daysadded?>%</span>
                        <?php echo $view['translator']->trans('le.dashboard.recent.added'); ?>
                        <span class="stat-label"><?php echo $view['translator']->trans('le.dashboard.recent.added.label', ['%COUNT%' => number_format($recentadded)]); ?></span>
                    </h3>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-dark-blue"><?php echo $last7daysactive?>%</span>
                        <?php echo $view['translator']->trans('le.dashboard.recent.active'); ?>
                        <span class="stat-label"><?php echo $view['translator']->trans('le.dashboard.recent.active.label', ['%COUNT%' => number_format($recentactive)]); ?></span>
                    </h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="email-campaign-stats bg-white lead-email-campaign-stats">
                    <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.dashboard.stat.email.name'); ?> </h2>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-orange"><?php echo $sentcount?></span>
                        <?php echo $view['translator']->trans('le.dashboard.email.sent'); ?>
                    </h3>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-green"><?php echo $openPercentage?>%</span>
                        <?php echo $view['translator']->trans('le.dashboard.email.opened'); ?>
                        <span class="stat-label"><?php echo $view['translator']->trans('le.dashboard.email.stat.label.open', ['%UNIQUE%' => $uniqueopen, '%OPEN%' => $totalopen]); ?></span>
                    </h3>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-unsubscribe"><?php echo $clickpercentage?>%</span>
                        <?php echo $view['translator']->trans('le.dashboard.email.clicked'); ?>
                        <span class="stat-label"><?php echo $view['translator']->trans('le.dashboard.email.stat.label.clicked', ['%CLICK%' => $click]); ?></span>
                    </h3>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-red"><?php echo $churnpercentage?>%</span>
                        <?php echo $view['translator']->trans('le.dashboard.email.churn'); ?>
                        <span class="stat-label"><?php echo $view['translator']->trans('le.dashboard.email.stat.label.Churn', ['%UNSUBSCRIBE%' => $unsubscribecount, '%BOUNCE%' => $bouncecount, '%SPAM%' => $spamcount]); ?></span>
                    </h3>
                </div>
            </div>
        </div>
    </div>
</div>
