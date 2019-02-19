<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if (isset($tmpl) && $tmpl == 'index') {
    $view->extend('MauticLeadBundle:Timeline:index.html.php');
}

$baseUrl = $view['router']->path(
    'le_contacttimeline_action',
    [
        'leadId' => $lead->getId(),
    ]
);
?>
<?php /** ?>
<!-- timeline -->
<div class="table-responsive hide">
    <table class="table table-hover table-bordered" id="contact-timeline">
        <thead>
        <tr>
            <th class="timeline-icon">
                <a class="btn btn-sm btn-nospin btn-default" data-activate-details="all" data-toggle="tooltip" title="<?php echo $view['translator']->trans(
                    'le.lead.timeline.toggle_all_details'
                ); ?>">
                    <span class="fa fa-fw fa-level-down"></span>
                </a>
            </th>
            <?php
            echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                'orderBy'    => 'eventType',
                'text'       => 'le.lead.timeline.event_type',
                'class'      => 'visible-md visible-lg timeline-type',
                'sessionVar' => 'lead.'.$lead->getId().'.timeline',
                'baseUrl'    => $baseUrl,
                'target'     => '#timeline-table',
            ]);

            echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                'orderBy'    => 'eventLabel',
                'text'       => 'le.lead.timeline.event_name',
                'class'      => 'timeline-name',
                'sessionVar' => 'lead.'.$lead->getId().'.timeline',
                'baseUrl'    => $baseUrl,
                'target'     => '#timeline-table',
            ]);

            echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                'orderBy'    => 'timestamp',
                'text'       => 'le.lead.timeline.event_timestamp',
                'class'      => 'visible-md visible-lg timeline-timestamp',
                'sessionVar' => 'lead.'.$lead->getId().'.timeline',
                'baseUrl'    => $baseUrl,
                'target'     => '#timeline-table',
            ]);
            ?>
        </tr>
        <tbody>
        <?php foreach ($events['events'] as $counter => $event): ?>
            <?php
            $counter += 1; // prevent 0
            $icon       = (isset($event['icon'])) ? $event['icon'] : 'fa-history';
            $eventLabel = (isset($event['eventLabel'])) ? $event['eventLabel'] : $event['eventType'];
            if (is_array($eventLabel)):
                $linkType   = empty($eventLabel['isExternal']) ? 'data-toggle="ajax"' : 'target="_new"';
                if(isset($eventLabel['href'])){
                    $eventLabel = "<a href=\"{$eventLabel['href']}\" $linkType>{$eventLabel['label']}</a>";
                }else{
                    $eventLabel = "{$eventLabel['label']}";
                }
            endif;

            $details = '';
            if (isset($event['contentTemplate']) && $view->exists($event['contentTemplate'])):
                $details = trim($view->render($event['contentTemplate'], ['event' => $event, 'lead' => $lead]));
            endif;

            $rowStripe = ($counter % 2 === 0) ? ' timeline-row-highlighted' : '';
            ?>
            <tr class="timeline-row<?php  echo $rowStripe;  ?><?php if (!empty($event['featured'])) {
                echo ' timeline-featured';
            } ?>">
                <td class="timeline-icon">
                    <a href="javascript:void(0);" data-activate-details="<?php echo $counter; ?>" class="btn btn-sm btn-nospin btn-default<?php if (empty($details)) {
                echo ' disabled';
            } ?>" data-toggle="tooltip" title="<?php echo $view['translator']->trans('le.lead.timeline.toggle_details'); ?>">
                        <span class="fa fa-fw <?php echo $icon ?>"></span>
                    </a>
                </td>
                <td class="timeline-type"><?php if (isset($event['eventType'])) {
                echo $event['eventType'];
            } ?></td>
                <td class="timeline-name"><?php echo $eventLabel; ?></td>
                <td class="timeline-timestamp"><?php echo $view['date']->toText($event['timestamp'], 'local', 'Y-m-d H:i:s', true); ?></td>
            </tr>
            <?php if (!empty($details)): ?>
                <tr class="timeline-row<?php echo $rowStripe; ?> timeline-details hide" id="timeline-details-<?php echo $counter; ?>">
                    <td colspan="4">
                        <?php echo $details ?>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php */ ?>
<div class="pa-md">
    <div class="row">
        <div class="col-sm-12" style="width: 106%;margin-left: -16px;margin-top: -33px;">
            <div class="panel">
                <div class="panel-body box-layout">
                    <div>
                        <h5 class="text-white dark-md fw-b mb-xs">
                            <?php echo $view['translator']->trans('le.lead.timeline.label.recentactivity'); ?>
                        </h5>
                    </div>
                    <br>
                    <div class="le-timeline">
                        <div class="le-timeline-event-wrapper">
                            <div class="le-timeline-event-start hide">
                                <div class="le-label"><?php echo $view['translator']->trans('le.lead.timeline.label.recent'); ?></div>
                            </div>
                            <?php
                            $showall=false;
                            if (count($events['events']) > 5) {
                                $showall=true;
                            }
                            foreach ($events['events'] as $counter => $event): ?>
                                <?php
                                $counter += 1; // prevent 0
                                $icon       = (isset($event['icon'])) ? $event['icon'] : 'fa-history';
                                $eventLabel = (isset($event['eventLabel'])) ? $event['eventLabel'] : $event['eventType'];

                                if (is_array($eventLabel)):
                                    $linkType   = empty($eventLabel['isExternal']) ? 'data-toggle="ajax"' : 'target="_new"';
                                if (isset($eventLabel['href'])) {
                                    $eventLabel = "<a href=\"{$eventLabel['href']}\" $linkType>{$eventLabel['label']}</a>";
                                } else {
                                    $eventLabel = "{$eventLabel['label']}";
                                }
                                endif;

                                $details = '';
                                if (isset($event['contentTemplate']) && $view->exists($event['contentTemplate'])):
                                    $details = trim($view->render($event['contentTemplate'], ['event' => $event, 'lead' => $lead]));
                                endif;
                                $hide = '';

                                if ($counter > 5) {
                                    $hide = 'hide';
                                }
                                //$rowStripe = ($counter % 2 === 0) ? ' timeline-row-highlighted' : '';
                                ?>
                                <div class="le-timeline-event <?php echo $hide; ?>">
                                    <span class="le-timeline-event-icon" data-toggle="tooltip" title="<?php /** echo $view['translator']->trans('le.lead.timeline.toggle_details');*/ ?>">
                                        <i class=" fa <?php echo $icon ?>"></i>
                                    </span>
                                    <div class="timeline-event-title mg-0">
                                        <div class="le-text-ellipsis">
                                            <a style="color: #47535f !important;text-decoration:none;font-weight:700;font-size:13px;cursor:text;">
                                                <div style="    margin-top: -5px;z-index: 1000000000;">
                                                    <?php if (isset($event['eventType'])) {
                                    echo $event['eventType'];
                                } ?>
                                                </div>
                                            </a>
                                        </div>
                                        <span class="">
                                                <?php echo $eventLabel; ?>
                                            </span><br>
                                         <span class="le-bullet"><i class="fa fa-circle"></i> </span>
                                        <small class="le-text-muted" style="min-height: 10px;">
                                            <span>
                                                <?php echo $view['date']->toText($event['timestamp'], 'local', 'Y-m-d H:i:s', true); ?>
                                            </span>
                                        </small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php if ($showall): ?>
                                <div class="le-timeline-event-end">
                                    <a class="le-label" onclick="Le.showAllEvents()">
                                        <?php echo $view['translator']->trans('le.lead.timeline.label.viewall'); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php /** ?>
<?php echo $view->render(
    'MauticCoreBundle:Helper:pagination.html.php',
    [
        'page'       => $events['page'],
        'fixedPages' => $events['maxPages'],
        'fixedLimit' => true,
        'baseUrl'    => $baseUrl,
        'target'     => '#timeline-table',
        'totalItems' => $events['total'],
    ]
); ?>
<?php */ ?>
<!--/ timeline -->