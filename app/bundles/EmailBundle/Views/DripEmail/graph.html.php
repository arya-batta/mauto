<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$label       = 'le.drip.email.lead.comparison';
$type        = 'line';
$dateFrom    = $dateRangeForm->children['date_from']->vars['data'];
$dateTo      = $dateRangeForm->children['date_to']->vars['data'];
$actionRoute = $view['router']->path('le_dripemail_campaign_action',
    [
        'objectAction' => 'view',
        'objectId'     => $entity->getId(),
        'daterange'    => [
            'date_to'   => $dateTo,
            'date_from' => $dateFrom,
        ],
    ]
);

?>
<div class="pa-md">
    <div class="row">
        <div class="col-sm-12">
            <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.email.stat.graph.name'); ?> </h2>
            <br>
            <div class="panel">
                <div class="panel-body box-layout">
                    <div class="col-xs-4 va-m">
                        <h5 class="text-white hide dark-md fw-sb mb-xs">
                            <span class="fa fa-envelope"></span>
                            <?php  echo $view['translator']->trans($label); ?>
                        </h5>
                    </div>
                    <div class="col-xs-8 va-m">
                        <?php echo $view->render('MauticCoreBundle:Helper:graph_dateselect.html.php', ['dateRangeForm' => $dateRangeForm, 'class' => 'pull-right']); ?>
                    </div>
                </div>
                <div class="row">
                    <?php if (!empty($stats)): ?>
                    <div class="pt-0 pl-15 pb-10 pr-15">
                        <?php echo $view->render('MauticCoreBundle:Helper:chart.html.php', ['chartData' => $stats, 'chartType' => $type, 'chartHeight' => 500]); ?>
                    </div>
                    <?php else: ?>
                        <div class="pt-0 pl-15 pb-10 pr-15">
                            <div style="height:200px;">

                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ some stats -->
