<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PageBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\CoreBundle\Helper\Chart\PieChart;
use Mautic\LeadBundle\Model\CompanyReportData;
use Mautic\ReportBundle\Event\ReportBuilderEvent;
use Mautic\ReportBundle\Event\ReportGeneratorEvent;
use Mautic\ReportBundle\Event\ReportGraphEvent;
use Mautic\ReportBundle\ReportEvents;

/**
 * Class ReportSubscriber.
 */
class ReportSubscriber extends CommonSubscriber
{
    const CONTEXT_PAGES     = 'pages';
    const CONTEXT_PAGE_HITS = 'page.hits';

    /**
     * @var CompanyReportData
     */
    private $companyReportData;

    public function __construct(CompanyReportData $companyReportData)
    {
        $this->companyReportData = $companyReportData;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ReportEvents::REPORT_ON_BUILD          => ['onReportBuilder', 0],
            ReportEvents::REPORT_ON_GENERATE       => ['onReportGenerate', 0],
            ReportEvents::REPORT_ON_GRAPH_GENERATE => ['onReportGraphGenerate', 0],
        ];
    }

    /**
     * Add available tables and columns to the report builder lookup.
     *
     * @param ReportBuilderEvent $event
     */
    public function onReportBuilder(ReportBuilderEvent $event)
    {
        if (!$this->security->isAdmin()) {
            return;
        }
        if (!$event->checkContext([self::CONTEXT_PAGES, self::CONTEXT_PAGE_HITS])) {
            return;
        }

        $prefix            = 'p.';
        $translationPrefix = 'tp.';
        $variantPrefix     = 'vp.';

        $columns = [
            $prefix.'title' => [
                'label' => 'mautic.core.title',
                'type'  => 'string',
            ],
            $prefix.'alias' => [
                'label' => 'mautic.core.alias',
                'type'  => 'string',
            ],
            $prefix.'revision' => [
                'label' => 'le.page.report.revision',
                'type'  => 'string',
            ],
            $prefix.'hits' => [
                'label' => 'le.page.field.hits',
                'type'  => 'int',
            ],
            $prefix.'unique_hits' => [
                'label' => 'le.page.field.unique_hits',
                'type'  => 'int',
            ],
            $translationPrefix.'id' => [
                'label' => 'le.page.report.translation_parent_id',
                'type'  => 'int',
            ],
            $translationPrefix.'title' => [
                'label' => 'le.page.report.translation_parent_title',
                'type'  => 'string',
            ],
            $variantPrefix.'id' => [
                'label' => 'le.page.report.variant_parent_id',
                'type'  => 'string',
            ],
            $variantPrefix.'title' => [
                'label' => 'le.page.report.variant_parent_title',
                'type'  => 'string',
            ],
            $prefix.'lang' => [
                'label' => 'mautic.core.language',
                'type'  => 'string',
            ],
            $prefix.'variant_start_date' => [
                'label'          => 'le.page.report.variant_start_date',
                'type'           => 'datetime',
                'groupByFormula' => 'DATE('.$prefix.'variant_start_date)',
            ],
            $prefix.'variant_hits' => [
                'label' => 'le.page.report.variant_hits',
                'type'  => 'int',
            ],
        ];
        $columns = array_merge(
            $columns,
            $event->getStandardColumns('p.', ['name', 'description'], 'mautic_page_action'),
            $event->getCategoryColumns()
        );
        $data = [
            'display_name' => 'le.page.pages',
            'columns'      => $columns,
        ];
        $event->addTable(self::CONTEXT_PAGES, $data);

        if ($event->checkContext(self::CONTEXT_PAGE_HITS)) {
            $hitPrefix   = 'ph.';
            $redirectHit = 'r.';
            $hitColumns  = [
                $hitPrefix.'date_hit' => [
                    'label'          => 'le.page.report.hits.date_hit',
                    'type'           => 'datetime',
                    'groupByFormula' => 'DATE('.$hitPrefix.'date_hit)',
                ],
                $hitPrefix.'date_left' => [
                    'label'          => 'le.page.report.hits.date_left',
                    'type'           => 'datetime',
                    'groupByFormula' => 'DATE('.$hitPrefix.'date_left)',
                ],
                $hitPrefix.'country' => [
                    'label' => 'le.page.report.hits.country',
                    'type'  => 'string',
                ],
                $hitPrefix.'region' => [
                    'label' => 'le.page.report.hits.region',
                    'type'  => 'string',
                ],
                $hitPrefix.'city' => [
                    'label' => 'le.page.report.hits.city',
                    'type'  => 'string',
                ],
                $hitPrefix.'isp' => [
                    'label' => 'le.page.report.hits.isp',
                    'type'  => 'string',
                ],
                $hitPrefix.'organization' => [
                    'label' => 'le.page.report.hits.organization',
                    'type'  => 'string',
                ],
                $hitPrefix.'code' => [
                    'label' => 'le.page.report.hits.code',
                    'type'  => 'int',
                ],
                $hitPrefix.'referer' => [
                    'label' => 'le.page.report.hits.referer',
                    'type'  => 'string',
                ],
                $hitPrefix.'url' => [
                    'label' => 'le.page.report.hits.url',
                    'type'  => 'url',
                ],
                $hitPrefix.'url_title' => [
                    'label' => 'le.page.report.hits.url_title',
                    'type'  => 'string',
                ],
                $hitPrefix.'user_agent' => [
                    'label' => 'le.page.report.hits.user_agent',
                    'type'  => 'string',
                ],
                $hitPrefix.'remote_host' => [
                    'label' => 'le.page.report.hits.remote_host',
                    'type'  => 'string',
                ],
                $hitPrefix.'browser_languages' => [
                    'label' => 'le.page.report.hits.browser_languages',
                    'type'  => 'array',
                ],
                $hitPrefix.'source' => [
                    'label' => 'mautic.report.field.source',
                    'type'  => 'string',
                ],
                $hitPrefix.'source_id' => [
                    'label' => 'mautic.report.field.source_id',
                    'type'  => 'int',
                ],
                $redirectHit.'url' => [
                    'label' => 'le.page.report.hits.redirect_url',
                    'type'  => 'url',
                ],
                $redirectHit.'hits' => [
                    'label' => 'le.page.report.hits.redirect_hit_count',
                    'type'  => 'int',
                ],
                $redirectHit.'unique_hits' => [
                    'label' => 'le.page.report.hits.redirect_unique_hits',
                    'type'  => 'string',
                ],
                'ds.device' => [
                    'label' => 'le.lead.device',
                    'type'  => 'string',
                ],
                'ds.device_brand' => [
                    'label' => 'le.lead.device_brand',
                    'type'  => 'string',
                ],
                'ds.device_model' => [
                    'label' => 'le.lead.device_model',
                    'type'  => 'string',
                ],
                'ds.device_os_name' => [
                    'label' => 'le.lead.device_os_name',
                    'type'  => 'string',
                ],
                'ds.device_os_shortname' => [
                    'label' => 'le.lead.device_os_shortname',
                    'type'  => 'string',
                ],
                'ds.device_os_version' => [
                    'label' => 'le.lead.device_os_version',
                    'type'  => 'string',
                ],
                'ds.device_os_platform' => [
                    'label' => 'le.lead.device_os_platform',
                    'type'  => 'string',
                ],
            ];

            $companyColumns = $this->companyReportData->getCompanyData();

            $pageHitsColumns = array_merge(
                $columns,
                $hitColumns,
                $event->getCampaignByChannelColumns(),
                $event->getLeadColumns(),
                $event->getIpColumn(),
                $companyColumns
            );

            $data = [
                'display_name' => 'le.page.hits',
                'columns'      => $pageHitsColumns,
            ];
            $event->addTable(self::CONTEXT_PAGE_HITS, $data, self::CONTEXT_PAGES);

            // Register graphs
            $context = self::CONTEXT_PAGE_HITS;
            $event->addGraph($context, 'line', 'le.page.graph.line.hits');
            $event->addGraph($context, 'line', 'le.page.graph.line.time.on.site');
            $event->addGraph($context, 'pie', 'le.page.graph.pie.time.on.site', ['translate' => false]);
            $event->addGraph($context, 'pie', 'le.page.graph.pie.new.vs.returning');
            $event->addGraph($context, 'pie', 'le.page.graph.pie.devices');
            $event->addGraph($context, 'pie', 'le.page.graph.pie.languages', ['translate' => false]);
            $event->addGraph($context, 'table', 'le.page.table.referrers');
            $event->addGraph($context, 'table', 'le.page.table.most.visited');
            $event->addGraph($context, 'table', 'le.page.table.most.visited.unique');
        }
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     *
     * @param ReportGeneratorEvent $event
     */
    public function onReportGenerate(ReportGeneratorEvent $event)
    {
        $context = $event->getContext();
        $qb      = $event->getQueryBuilder();

        switch ($context) {
            case self::CONTEXT_PAGES:
                $qb->from(MAUTIC_TABLE_PREFIX.'pages', 'p')
                    ->leftJoin('p', MAUTIC_TABLE_PREFIX.'pages', 'tp', 'p.id = tp.id')
                    ->leftJoin('p', MAUTIC_TABLE_PREFIX.'pages', 'vp', 'p.id = vp.id');
                $event->addCategoryLeftJoin($qb, 'p');
                break;
            case self::CONTEXT_PAGE_HITS:
                $event->applyDateFilters($qb, 'date_hit', 'ph');

                $qb->from(MAUTIC_TABLE_PREFIX.'page_hits', 'ph')
                    ->leftJoin('ph', MAUTIC_TABLE_PREFIX.'pages', 'p', 'ph.page_id = p.id')
                    ->leftJoin('p', MAUTIC_TABLE_PREFIX.'pages', 'tp', 'p.id = tp.id')
                    ->leftJoin('p', MAUTIC_TABLE_PREFIX.'pages', 'vp', 'p.id = vp.id')
                    ->leftJoin('ph', MAUTIC_TABLE_PREFIX.'page_redirects', 'r', 'r.id = ph.redirect_id')
                    ->leftJoin('ph', MAUTIC_TABLE_PREFIX.'lead_devices', 'ds', 'ds.id = ph.device_id');

                $event->addIpAddressLeftJoin($qb, 'ph');
                $event->addCategoryLeftJoin($qb, 'p');
                $event->addLeadLeftJoin($qb, 'ph');
                $event->addCampaignByChannelJoin($qb, 'p', 'page');

                if ($this->companyReportData->eventHasCompanyColumns($event)) {
                    $event->addCompanyLeftJoin($qb);
                }

                break;
        }

        $event->setQueryBuilder($qb);
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     *
     * @param ReportGraphEvent $event
     */
    public function onReportGraphGenerate(ReportGraphEvent $event)
    {
        // Context check, we only want to fire for Lead reports
        if (!$event->checkContext(self::CONTEXT_PAGE_HITS)) {
            return;
        }

        $graphs  = $event->getRequestedGraphs();
        $qb      = $event->getQueryBuilder();
        $hitRepo = $this->em->getRepository('MauticPageBundle:Hit');

        foreach ($graphs as $g) {
            $options      = $event->getOptions($g);
            $queryBuilder = clone $qb;

            /** @var ChartQuery $chartQuery */
            $chartQuery = clone $options['chartQuery'];
            $chartQuery->applyDateFilters($queryBuilder, 'date_hit', 'ph');

            switch ($g) {
                case 'le.page.graph.line.hits':
                    $chart = new LineChart(null, $options['dateFrom'], $options['dateTo']);
                    $chartQuery->modifyTimeDataQuery($queryBuilder, 'date_hit', 'ph');
                    $hits = $chartQuery->loadAndBuildTimeData($queryBuilder);
                    $chart->setDataset($options['translator']->trans($g), $hits);
                    $data         = $chart->render();
                    $data['name'] = $g;

                    $event->setGraph($g, $data);
                    break;

                case 'le.page.graph.line.time.on.site':
                    $chart = new LineChart(null, $options['dateFrom'], $options['dateTo']);
                    $queryBuilder->select('TIMESTAMPDIFF(SECOND, ph.date_hit, ph.date_left) as data, ph.date_hit as date');
                    $queryBuilder->andWhere($qb->expr()->isNotNull('ph.date_left'));

                    $hits = $chartQuery->loadAndBuildTimeData($queryBuilder);
                    $chart->setDataset($options['translator']->trans($g), $hits);
                    $data         = $chart->render();
                    $data['name'] = $g;

                    $event->setGraph($g, $data);
                    break;

                case 'le.page.graph.pie.time.on.site':
                    $timesOnSite = $hitRepo->getDwellTimeLabels();
                    $chart       = new PieChart();

                    foreach ($timesOnSite as $time) {
                        $q = clone $queryBuilder;
                        $chartQuery->modifyCountDateDiffQuery($q, 'date_hit', 'date_left', $time['from'], $time['till'], 'ph');
                        $data = $chartQuery->fetchCountDateDiff($q);
                        $chart->setDataset($time['label'], $data);
                    }

                    $event->setGraph(
                        $g,
                        [
                            'data'      => $chart->render(),
                            'name'      => $g,
                            'iconClass' => 'fa-clock-o',
                        ]
                    );
                    break;

                case 'le.page.graph.pie.new.vs.returning':
                    $chart   = new PieChart();
                    $allQ    = clone $queryBuilder;
                    $uniqueQ = clone $queryBuilder;
                    $chartQuery->modifyCountQuery($allQ, 'date_hit', [], 'ph');
                    $chartQuery->modifyCountQuery($uniqueQ, 'date_hit', ['getUnique' => true, 'selectAlso' => ['ph.page_id']], 'ph');
                    $all       = $chartQuery->fetchCount($allQ);
                    $unique    = $chartQuery->fetchCount($uniqueQ);
                    $returning = $all - $unique;
                    $chart->setDataset($this->translator->trans('le.page.unique'), $unique);
                    $chart->setDataset($this->translator->trans('le.page.graph.pie.new.vs.returning.returning'), $returning);

                    $event->setGraph(
                        $g,
                        [
                            'data'      => $chart->render(),
                            'name'      => $g,
                            'iconClass' => 'fa-bookmark-o',
                        ]
                    );
                    break;

                case 'le.page.graph.pie.languages':
                    $queryBuilder->select('ph.page_language, COUNT(distinct(ph.id)) as the_count')
                        ->groupBy('ph.page_language')
                        ->andWhere($qb->expr()->isNotNull('ph.page_language'));
                    $data  = $queryBuilder->execute()->fetchAll();
                    $chart = new PieChart();

                    foreach ($data as $lang) {
                        $chart->setDataset($lang['page_language'], $lang['the_count']);
                    }

                    $event->setGraph(
                        $g,
                        [
                            'data'      => $chart->render(),
                            'name'      => $g,
                            'iconClass' => 'fa-globe',
                        ]
                    );
                    break;
                case 'le.page.graph.pie.devices':
                    $queryBuilder->select('ds.device, COUNT(distinct(ph.id)) as the_count')
                        ->groupBy('ds.device');
                    $data  = $queryBuilder->execute()->fetchAll();
                    $chart = new PieChart();

                    foreach ($data as $device) {
                        $label = substr(empty($device['device']) ? $this->translator->trans('mautic.core.no.info') : $device['device'], 0, 12);
                        $chart->setDataset($label, $device['the_count']);
                    }

                    $event->setGraph(
                        $g,
                        [
                            'data'      => $chart->render(),
                            'name'      => $g,
                            'iconClass' => 'fa-globe',
                        ]
                    );
                    break;
                case 'le.page.table.referrers':
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $hitRepo->getReferers($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-sign-in';
                    $event->setGraph($g, $graphData);
                    break;

                case 'le.page.table.most.visited':
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $hitRepo->getMostVisited($queryBuilder, $limit, $offset);
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-eye';
                    $graphData['link']      = 'mautic_page_action';
                    $event->setGraph($g, $graphData);
                    break;

                case 'le.page.table.most.visited.unique':
                    $limit                  = 10;
                    $offset                 = 0;
                    $items                  = $hitRepo->getMostVisited($queryBuilder, $limit, $offset, 'p.unique_hits', 'sessions');
                    $graphData              = [];
                    $graphData['data']      = $items;
                    $graphData['name']      = $g;
                    $graphData['iconClass'] = 'fa-eye';
                    $graphData['link']      = 'mautic_page_action';
                    $event->setGraph($g, $graphData);
                    break;
            }

            unset($queryBuilder);
        }
    }
}
