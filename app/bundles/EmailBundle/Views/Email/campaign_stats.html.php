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
if ($sentcount != 0) {
    $openPercentage        = round(($uniqueopen / $sentcount * 100), 2);
    $notopenpercentage     = round(($notopencount / $sentcount * 100), 2);
    $clickpercentage       = round(($click / $sentcount * 100), 2);
    $unsubscribepercentage = round(($unsubscribecount / $sentcount * 100), 2);
    $bouncepercentage      = round(($bouncecount / $sentcount * 100), 2);
    $spampercentage        = round(($spamcount / $sentcount * 100), 2);
}
?>
<br>
<div class="box-layout">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6 bg-white">
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
            <div class="col-md-6 bg-white">
                <div class="email-campaign-stats">
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-unsubscribe"><?php echo $unsubscribepercentage?>%</span>
                        <?php echo $view['translator']->trans('le.email.stats.unsubscribed'); ?>
                        <span class="stat-label"><?php echo $view['translator']->trans('le.email.stat.label.unsubscribed', ['%COUNT%' => $unsubscribecount]); ?></span>
                    </h3>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-bounce"><?php echo $bouncepercentage?>%</span>
                        <?php echo $view['translator']->trans('le.email.stats.bounced'); ?>
                        <span class="stat-label"><?php echo $view['translator']->trans('le.email.stat.label.bounced', ['%COUNT%' => $bouncecount]); ?></span>
                    </h3>
                    <br>
                    <h3 class="h3-stat">
                        <span class="email-badge email-badge-spam"><?php echo $spampercentage?>%</span>
                        <?php echo $view['translator']->trans('le.email.stats.spamed'); ?>
                        <span class="stat-label"><?php echo $view['translator']->trans('le.email.stat.label.spam', ['%COUNT%' => $spamcount]); ?></span>
                    </h3>
                </div>
            </div>
        </div>
    </div>
</div>
