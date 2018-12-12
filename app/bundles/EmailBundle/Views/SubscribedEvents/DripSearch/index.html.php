<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$item     = $event['extra']['log'];
$eventtxt = 'le.drip.campaign.timeline.event.completed.time';
if ($item['isScheduled']) {
    $eventtxt = 'le.drip.campaign.timeline.event.scheduled.time';
}
?>
<div class="mt-10">
    <p class="mt-0 mb-10 text-info" id="timeline-campaign-event-<?php echo $item['dripemail_id']; ?>">
        <span id="timeline-campaign-event-text-<?php echo $item['dripemail_id']; ?>">
            <i class="fa fa-clock-o"></i>
            <span class="timeline-campaign-event-scheduled-<?php echo $item['dripemail_id']; ?>">
                <?php echo $view['translator']->trans($eventtxt, ['%date%' => $view['date']->toFull($event['extra']['log']['triggerDate'])]); ?>
            </span>
            <br>
            <span class="timeline-campaign-event-scheduled-<?php echo $item['dripemail_id']; ?>">
                <?php echo $view['translator']->trans($item['failedReason']); ?>
            </span>
        </span>
    </p>
</div>
