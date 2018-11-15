<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('leContent', 'payment-status');
$view['slots']->set('headerTitle', $view['translator']->trans('le.email.statistics.report'));

$styleclass = 'is-below-25';
if ($usagedetails['reputation'] < 50) {
    if ($usagedetails['reputation'] < 25) {
        $styleclass = 'is-below-25';
    } else {
        $styleclass = 'is-below-50';
    }
} elseif ($usagedetails['reputation'] > 50) {
    $styleclass = 'is-above-50';
}
?>
<div class="payment-status-holder emailusage">
    <div>
        <div>
            <p class="email_stat_header_1"><b>Account Email</b><br><?php echo $usagedetails['username']; ?></p>
            <p class="email_stat_header_2"><b>Status</b><br><?php echo $usagedetails['status']; ?></p>
        </div>
        <div>
            <p class="email_stat_col_1"><b>Total Email Sent:</b></p>
            <p class="email_stat_col_2"><?php echo $usagedetails['totalemail']; ?></p>
            <p class="email_stat_col_3"><b>Invalid:</b></p>
            <p class="email_stat_col_4"><?php echo $usagedetails['abusepercent']; ?>%</p>
            <br>
            <p class="email_stat_col_1"><b>Spam:</b></p>
            <p class="email_stat_col_2"><?php echo $usagedetails['spamscore']; ?></p>
            <p class="email_stat_col_3"><b>Open:</b></p>
            <p class="email_stat_col_4"><?php echo $usagedetails['openpercent']; ?>%<br></p>
            <br>
            <p class="email_stat_col_1"><b>Click Rate:</b></p>
            <p class="email_stat_col_2"><?php echo $usagedetails['clickcount']; ?>%</p>
            <br>
        </div>


    </div>
    <br>
    <div style="margin-top:8%;">
        <p style="margin-left:43%;float: left;"><b><abbr>REPUTATION:</abbr>&nbsp;&nbsp;</b><?php echo $usagedetails['reputation']; ?>%</p>
        <div class="meter-bar">
            <div class="meter-bar-fill <?php echo $styleclass; ?>" style="width: <?php echo $usagedetails['reputation']; ?>%;"></div>
        </div>
    </div>
</div>

