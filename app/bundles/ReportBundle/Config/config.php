<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

return [
    'routes' => [
        'main' => [
            'le_report_index' => [
                'path'       => '/reports/{page}',
                'controller' => 'MauticReportBundle:Report:index',
            ],
            'le_report_export' => [
                'path'       => '/reports/view/{objectId}/export/{format}',
                'controller' => 'MauticReportBundle:Report:export',
                'defaults'   => [
                    'format' => 'csv',
                ],
            ],
            'le_report_view' => [
                'path'       => '/reports/view/{objectId}/{reportPage}',
                'controller' => 'MauticReportBundle:Report:view',
                'defaults'   => [
                    'reportPage' => 1,
                ],
                'requirements' => [
                    'reportPage' => '\d+',
                ],
            ],
            'le_report_schedule_preview' => [
                'path'       => '/reports/schedule/preview/{isScheduled}/{scheduleUnit}/{scheduleDay}/{scheduleMonthFrequency}/{scheduleDate}',
                'controller' => 'MauticReportBundle:Schedule:index',
                'defaults'   => [
                    'isScheduled'             => 0,
                    'scheduleUnit'            => '',
                    'scheduleDay'             => '',
                    'scheduleMonthFrequency'  => '',
                    'scheduleDate'            => date('h:i:s'),
                ],
            ],
            'le_report_action' => [
                'path'       => '/reports/{objectAction}/{objectId}',
                'controller' => 'MauticReportBundle:Report:execute',
            ],
        ],
        'api' => [
            'mautic_api_getreports' => [
                'path'       => '/reports',
                'controller' => 'MauticReportBundle:Api\ReportApi:getEntities',
            ],
            'mautic_api_getreport' => [
                'path'       => '/reports/{id}',
                'controller' => 'MauticReportBundle:Api\ReportApi:getReport',
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'items' => [
            'mautic.report.reports.menu.1' => [
                'route'     => 'le_report_action',
                'routeParameters' => ['objectAction' => 'view','objectId' => '32'],
                'iconClass' => 'fa-line-chart',
                'access'    => ['report:reports:viewown', 'report:reports:viewother',],
                'parent' =>'mautic.report.reports',
                'id'=>'le_report_action_1',
                'priority' =>25,
            ],
            'mautic.report.reports.menu.2' => [
                'route'     => 'le_report_action',
                'routeParameters' => ['objectAction' => 'view','objectId' => '28'],
                'iconClass' => 'fa-line-chart',
                'access'    => ['report:reports:viewown', 'report:reports:viewother',],
                'parent' =>'mautic.report.reports',
                'id'=>'le_report_action_2',
                'priority' => 24,
            ],
            'mautic.report.reports.menu.3' => [
                'route'     => 'le_report_action',
                'routeParameters' => ['objectAction' => 'view','objectId' => '27'],
                'iconClass' => 'fa-line-chart',
                'access'    => ['report:reports:viewown', 'report:reports:viewother',],
                'parent' =>'mautic.report.reports',
                'id'=>'le_report_action_3',
                'priority' => 23,
            ],
            'mautic.report.reports.menu.4' => [
                'route'     => 'le_report_action',
                'routeParameters' => ['objectAction' => 'view','objectId' => '30'],
                'iconClass' => 'fa-line-chart',
                'access'    => ['report:reports:viewown', 'report:reports:viewother',],
                'parent' =>'mautic.report.reports',
                'id'=>'le_report_action_4',
                'priority' => 22,
            ],
            'mautic.report.reports.menu.5' => [
                'route'     => 'le_report_action',
                'routeParameters' => ['objectAction' => 'view','objectId' => '31'],
                'iconClass' => 'fa-line-chart',
                'access'    => ['report:reports:viewown', 'report:reports:viewother',],
                'parent' =>'mautic.report.reports',
                'id'=>'le_report_action_5',
                'priority' => 21,
            ],
        ],
       ],
    ],

    'services' => [
        'events' => [
            'mautic.report.search.subscriber' => [
                'class'     => 'Mautic\ReportBundle\EventListener\SearchSubscriber',
                'arguments' => [
                    'mautic.helper.user',
                    'mautic.report.model.report',
                ],
            ],
            'mautic.report.report.subscriber' => [
                'class'     => 'Mautic\ReportBundle\EventListener\ReportSubscriber',
                'arguments' => [
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                ],
            ],
            'mautic.report.dashboard.subscriber' => [
                'class'     => 'Mautic\ReportBundle\EventListener\DashboardSubscriber',
                'arguments' => [
                    'mautic.report.model.report',
                    'mautic.security',
                ],
            ],
            'mautic.report.scheduler.report_scheduler_subscriber' => [
                'class'     => \Mautic\ReportBundle\Scheduler\EventListener\ReportSchedulerSubscriber::class,
                'arguments' => [
                    'mautic.report.model.scheduler_planner',
                ],
            ],
            'mautic.report.report.schedule_subscriber' => [
                'class'     => \Mautic\ReportBundle\EventListener\SchedulerSubscriber::class,
                'arguments' => [
                    'mautic.report.model.send_schedule',
                ],
            ],
        ],
        'forms' => [
            'mautic.form.type.report' => [
                'class'     => \Mautic\ReportBundle\Form\Type\ReportType::class,
                'arguments' => [
                    'mautic.report.model.report',
                    'translator',
                ],
            ],
            'mautic.form.type.filter_selector' => [
                'class' => 'Mautic\ReportBundle\Form\Type\FilterSelectorType',
                'alias' => 'filter_selector',
            ],
            'mautic.form.type.table_order' => [
                'class'     => 'Mautic\ReportBundle\Form\Type\TableOrderType',
                'arguments' => 'mautic.factory',
                'alias'     => 'table_order',
            ],
            'mautic.form.type.report_filters' => [
                'class'     => 'Mautic\ReportBundle\Form\Type\ReportFiltersType',
                'arguments' => 'mautic.factory',
                'alias'     => 'report_filters',
            ],
            'mautic.form.type.report_dynamic_filters' => [
                'class' => 'Mautic\ReportBundle\Form\Type\DynamicFiltersType',
                'alias' => 'report_dynamicfilters',
            ],
            'mautic.form.type.report_widget' => [
                'class'     => 'Mautic\ReportBundle\Form\Type\ReportWidgetType',
                'alias'     => 'report_widget',
                'arguments' => 'mautic.report.model.report',
            ],
            'mautic.form.type.aggregator' => [
                'class'     => 'Mautic\ReportBundle\Form\Type\AggregatorType',
                'alias'     => 'aggregator',
                'arguments' => 'translator',
            ],
            'mautic.form.type.report.settings' => [
                'class' => \Mautic\ReportBundle\Form\Type\ReportSettingsType::class,
                'alias' => 'report_settings',
            ],
        ],
        'helpers' => [
            'mautic.report.helper.report' => [
                'class' => \Mautic\ReportBundle\Helper\ReportHelper::class,
                'alias' => 'report',
            ],
        ],
        'models' => [
            'mautic.report.model.report' => [
                'class'     => \Mautic\ReportBundle\Model\ReportModel::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.helper.templating',
                    'mautic.channel.helper.channel_list',
                    'mautic.lead.model.field',
                    'mautic.report.helper.report',
                    'mautic.report.model.csv_exporter',
                    'mautic.report.model.excel_exporter',
                ],
            ],
            'mautic.report.model.csv_exporter' => [
                'class'     => \Mautic\ReportBundle\Model\CsvExporter::class,
                'arguments' => [
                    'mautic.helper.template.formatter',
                ],
            ],
            'mautic.report.model.excel_exporter' => [
                'class'     => \Mautic\ReportBundle\Model\ExcelExporter::class,
                'arguments' => [
                    'mautic.helper.template.formatter',
                ],
            ],
            'mautic.report.model.scheduler_builder' => [
                'class'     => \Mautic\ReportBundle\Scheduler\Builder\SchedulerBuilder::class,
                'arguments' => [
                    'mautic.helper.user',
                    'mautic.report.model.scheduler_template_factory',
                ],
            ],
            'mautic.report.model.scheduler_template_factory' => [
                'class'     => \Mautic\ReportBundle\Scheduler\Factory\SchedulerTemplateFactory::class,
                'arguments' => [],
            ],
            'mautic.report.model.scheduler_date_builder' => [
                'class'     => \Mautic\ReportBundle\Scheduler\Date\DateBuilder::class,
                'arguments' => [
                    'mautic.report.model.scheduler_builder',
                ],
            ],
            'mautic.report.model.scheduler_planner' => [
                'class'     => \Mautic\ReportBundle\Scheduler\Model\SchedulerPlanner::class,
                'arguments' => [
                    'mautic.report.model.scheduler_date_builder',
                    'doctrine.orm.default_entity_manager',
                ],
            ],
            'mautic.report.model.send_schedule' => [
                'class'     => \Mautic\ReportBundle\Scheduler\Model\SendSchedule::class,
                'arguments' => [
                    'mautic.helper.mailer',
                    'mautic.report.model.message_schedule',
                ],
            ],
            'mautic.report.model.message_schedule' => [
                'class'     => \Mautic\ReportBundle\Scheduler\Model\MessageSchedule::class,
                'arguments' => [
                    'translator',
                    'mautic.helper.file_properties',
                    'mautic.helper.core_parameters',
                    'router',
                ],
            ],
            'mautic.report.model.report_exporter' => [
                'class'     => \Mautic\ReportBundle\Model\ReportExporter::class,
                'arguments' => [
                    'mautic.report.model.schedule_model',
                    'mautic.report.model.report_data_adapter',
                    'mautic.report.model.report_export_options',
                    'mautic.report.model.report_file_writer',
                    'event_dispatcher',
                ],
            ],
            'mautic.report.model.schedule_model' => [
                'class'     => \Mautic\ReportBundle\Model\ScheduleModel::class,
                'arguments' => [
                    'doctrine.orm.default_entity_manager',
                    'mautic.report.model.scheduler_planner',
                ],
            ],
            'mautic.report.model.report_data_adapter' => [
                'class'     => \Mautic\ReportBundle\Adapter\ReportDataAdapter::class,
                'arguments' => [
                    'mautic.report.model.report',
                ],
            ],
            'mautic.report.model.report_export_options' => [
                'class'     => \Mautic\ReportBundle\Model\ReportExportOptions::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                ],
            ],
            'mautic.report.model.report_file_writer' => [
                'class'     => \Mautic\ReportBundle\Model\ReportFileWriter::class,
                'arguments' => [
                    'mautic.report.model.csv_exporter',
                    'mautic.report.model.export_handler',
                ],
            ],
            'mautic.report.model.export_handler' => [
                'class'     => \Mautic\ReportBundle\Model\ExportHandler::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.helper.file_path_resolver',
                ],
            ],
        ],
        'validator' => [
            'mautic.report.validator.schedule_is_valid_validator' => [
                'class'     => \Mautic\ReportBundle\Scheduler\Validator\ScheduleIsValidValidator::class,
                'arguments' => [
                    'mautic.report.model.scheduler_builder',
                ],
                'tag' => 'validator.constraint_validator',
            ],
        ],
        'command' => [
            'mautic.report.command.export_scheduler' => [
                'class'     => \Mautic\ReportBundle\Scheduler\Command\ExportSchedulerCommand::class,
                'arguments' => [
                    'mautic.report.model.report_exporter',
                    'translator',
                ],
                'tag' => 'console.command',
            ],
        ],
    ],

    'parameters' => [
        'report_temp_dir'                     => '%kernel.root_dir%/../media/files/temp',
        'report_export_batch_size'            => 1000,
        'report_export_max_filesize_in_bytes' => 5000000,
    ],
];
