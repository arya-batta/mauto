<?php

/*
 * @copyright   2019 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$openPercentage        = 0;
$notopenpercentage     = 0;
$clickpercentage       = 0;
$unsubscribepercentage = 0;
$bouncepercentage      = 0;
$spampercentage        = 0;
$spamandbounce         = $bouncecount + $spamcount;
$bspercentage          = 0;
$failedpercentage      = 0;
if ($sentcount != 0) {
    $openPercentage        = round(($uniqueopen / $sentcount * 100), 2);
    $notopenpercentage     = round(($notopencount / $sentcount * 100), 2);
    $clickpercentage       = round(($click / $sentcount * 100), 2);
    $unsubscribepercentage = round(($unsubscribecount / $sentcount * 100), 2);
    $bouncepercentage      = round(($bouncecount / $sentcount * 100), 2);
    $spampercentage        = round(($spamcount / $sentcount * 100), 2);
    $bspercentage          = round(($spamandbounce / $sentcount * 100), 2);
    $failedpercentage      = round(($failedcount / $sentcount * 100), 2);
}
?>
<div class="col-md-12">

<div class="row" style="margin-left:10px;margin-right:10px;">
    <h2 class="email-dataview-stats stats-margin" style="margin-left:12px;"><?php echo $view['translator']->trans('le.email.stat.name'); ?> </h2>
    <div class="col-md-6 bg-white" style="float:left;">
        <div class="email-campaign-stats">
            <h3 class="h3-stat">
                <span class="email-badge email-badge-open"><?php echo $openPercentage?>%</span>
                <?php echo $view['translator']->trans('le.email.stats.opened'); ?>
                <span class="stat-label"><?php echo $view['translator']->trans('le.email.stat.label.open', ['%SENT%' => $sentcount, '%UNIQUE%' => $uniqueopen, '%OPEN%' => $totalopen]); ?></span>
            </h3>
            <br>
            <h3 class="h3-stat">
                <span class="email-badge email-badge-notopen"><?php echo $notopenpercentage?>%</span>
                <?php echo $view['translator']->trans('le.email.stats.notopened'); ?>
                <span class="stat-label"><?php echo $view['translator']->trans('le.email.stat.label.notopened', ['%NOTOPENED%' => $notopencount]); ?></span>
            </h3>
            <br>
            <h3 class="h3-stat">
                <span class="email-badge email-badge-click"><?php echo $clickpercentage?>%</span>
                <?php echo $view['translator']->trans('le.email.stats.click'); ?>
                <span class="stat-label"><?php echo $view['translator']->trans('le.email.stat.label.click', ['%CLICK%' => $click]); ?></span>
            </h3>
        </div>
    </div>
    <div class="col-md-6 bg-white" style="float:right;">
        <div class="email-campaign-stats">
            <h3 class="h3-stat">
                <span class="email-badge email-badge-unsubscribe"><?php echo $unsubscribepercentage?>%</span>
                <?php echo $view['translator']->trans('le.email.stats.unsubscribed'); ?>
                <span class="stat-label"><?php echo $view['translator']->trans('le.email.stat.label.unsubscribed', ['%COUNT%' => $unsubscribecount]); ?></span>
            </h3>
            <br>
            <h3 class="h3-stat">
                <span class="email-badge email-badge-bounce"><?php echo $bouncepercentage?>%</span>
                <?php echo $view['translator']->trans('le.email.stats.bounce.and.spam'); ?>
                <span class="stat-label"><?php echo $view['translator']->trans('le.email.stat.label.spamandbounce', ['%COUNT%' => $bouncecount, '%SPAM%' => $spamcount]); ?></span>
            </h3>
            <br>
            <h3 class="h3-stat">
                <span class="email-badge email-badge-spam"><?php echo $failedpercentage?>%</span>
                <?php echo $view['translator']->trans('le.email.stats.failed'); ?>
                <span class="stat-label"><?php echo $view['translator']->trans('le.email.stat.label.failed', ['%COUNT%' => $failedcount]); ?></span>
            </h3>
        </div>
    </div>
</div>
</div>

