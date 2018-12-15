<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\LeadBundle\Report\FieldsBuilder;
use Mautic\ReportBundle\Event\ReportBuilderEvent;
use Mautic\ReportBundle\Event\ReportGeneratorEvent;
use Mautic\ReportBundle\ReportEvents;

class ListReportSubscriber extends CommonSubscriber
{
    const LIST_MEMBERSHIP = 'list.membership';

    /**
     * @var FieldsBuilder
     */
    private $fieldsBuilder;

    /**
     * @param FieldsBuilder $fieldsBuilder
     */
    public function __construct(FieldsBuilder $fieldsBuilder)
    {
        $this->fieldsBuilder = $fieldsBuilder;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ReportEvents::REPORT_ON_BUILD    => ['onReportBuilder', 0],
            ReportEvents::REPORT_ON_GENERATE => ['onReportGenerate', 0],
        ];
    }

    /**
     * Add available tables and columns to the report builder lookup.
     *
     * @param ReportBuilderEvent $event
     */
    public function onReportBuilder(ReportBuilderEvent $event)
    {
        if (!$event->checkContext([self::LIST_MEMBERSHIP])) {
            return;
        }

        $columns = $this->fieldsBuilder->getLeadFieldsColumns('l.');

        $filters = $this->fieldsBuilder->getLeadListOptinFilter('l.', 'lll.');

        $listColumns = [
            'lll.manually_removed' => [
                'label' => 'le.lead.report.lists.manually_removed',
                'type'  => 'bool',
            ],
            'lll.manually_added' => [
                'label' => 'le.lead.report.lists.manually_added',
                'type'  => 'bool',
            ],
            'lll.confirmed_lead' => [
                'label' => 'le.lead.report.lists.confirmed_lead',
                'type'  => 'bool',
            ],
            'lll.unconfirmed_lead' => [
                'label' => 'le.lead.report.lists.unconfirmed_lead',
                'type'  => 'bool',
            ],
            'lll.unsubscribed_lead' => [
                'label' => 'le.lead.report.lists.unsubscribed_lead',
                'type'  => 'bool',
            ],
        ];

        $data = [
            'display_name' => 'le.lead.report.lists.membership',
            'columns'      => array_merge($columns, $listColumns, $event->getStandardColumns('s.', ['publish_up', 'publish_down'])),
            'filters'      => $filters,
        ];
        $event->addTable(self::LIST_MEMBERSHIP, $data, ReportSubscriber::GROUP_CONTACTS);

        unset($columns, $filters, $listColumns, $data);
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     *
     * @param ReportGeneratorEvent $event
     */
    public function onReportGenerate(ReportGeneratorEvent $event)
    {
        if (!$event->checkContext([self::LIST_MEMBERSHIP])) {
            return;
        }

        $qb = $event->getQueryBuilder();
        $qb->from(MAUTIC_TABLE_PREFIX.'lead_listoptin_leads', 'lll')
            ->leftJoin('lll', MAUTIC_TABLE_PREFIX.'leads', 'l', 'l.id = lll.lead_id')
            ->leftJoin('lll', MAUTIC_TABLE_PREFIX.'lead_listoptin', 's', 's.id = lll.leadlist_id');

        if ($event->hasColumn(['u.first_name', 'u.last_name']) || $event->hasFilter(['u.first_name', 'u.last_name'])) {
            $qb->leftJoin('l', MAUTIC_TABLE_PREFIX.'users', 'u', 'u.id = l.owner_id');
        }

        if ($event->hasColumn('i.ip_address') || $event->hasFilter('i.ip_address')) {
            $event->addLeadIpAddressLeftJoin($qb);
        }

        $event->setQueryBuilder($qb);
    }
}
