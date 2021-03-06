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
            'le_plugin_timeline_index' => [
                'path'         => '/plugin/{integration}/timeline/{page}',
                'controller'   => 'MauticLeadBundle:Timeline:pluginIndex',
                'requirements' => [
                    'integration' => '.+',
                ],
            ],
            'le_plugin_timeline_view' => [
                'path'         => '/plugin/{integration}/timeline/view/{leadId}/{page}',
                'controller'   => 'MauticLeadBundle:Timeline:pluginView',
                'requirements' => [
                    'integration' => '.+',
                    'leadId'      => '\d+',
                ],
            ],
            'le_featuresandideas_index' => [
                'path'       => '/featuresandideas/{page}',
                'controller' => 'MauticLeadBundle:Lead:featuresAndIdeas',
            ],
            'le_segment_index' => [
                'path'       => '/segments/{page}',
                'controller' => 'MauticLeadBundle:List:index',
            ],
            'le_segment_action' => [
                'path'       => '/segments/{objectAction}/{objectId}',
                'controller' => 'MauticLeadBundle:List:execute',
            ],
            'le_contactfield_index' => [
                'path'       => '/leads/fields/{page}',
                'controller' => 'MauticLeadBundle:Field:index',
            ],
            'le_contactfield_action' => [
                'path'       => '/leads/fields/{objectAction}/{objectId}',
                'controller' => 'MauticLeadBundle:Field:execute',
            ],
            'le_contact_index' => [
                'path'       => '/leads/{page}',
                'controller' => 'MauticLeadBundle:Lead:index',
            ],
            'le_tags_index' => [
                'path'       => '/tags/{page}',
                'controller' => 'MauticLeadBundle:Tag:index',
            ],
            'le_tags_action' => [
                'path'       => '/tags/{objectAction}/{objectId}',
                'controller' => 'MauticLeadBundle:Tag:execute',
            ],
            'le_contactnote_index' => [
                'path'       => '/leads/notes/{leadId}/{page}',
                'controller' => 'MauticLeadBundle:Note:index',
                'defaults'   => [
                    'leadId' => 0,
                ],
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'le_contactnote_action' => [
                'path'         => '/leads/notes/{leadId}/{objectAction}/{objectId}',
                'controller'   => 'MauticLeadBundle:Note:executeNote',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'le_contacttimeline_action' => [
                'path'         => '/leads/timeline/{leadId}/{page}',
                'controller'   => 'MauticLeadBundle:Timeline:index',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'le_contact_timeline_export_action' => [
                'path'         => '/leads/timeline/batchExport/{leadId}',
                'controller'   => 'MauticLeadBundle:Timeline:batchExport',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'le_contact_auditlog_action' => [
                'path'         => '/leads/auditlog/{leadId}/{page}',
                'controller'   => 'MauticLeadBundle:Auditlog:index',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            'le_contact_auditlog_export_action' => [
                'path'         => '/leads/auditlog/batchExport/{leadId}',
                'controller'   => 'MauticLeadBundle:Auditlog:batchExport',
                'requirements' => [
                    'leadId' => '\d+',
                ],
            ],
            // @deprecated 2.9.1 to be removed in 3.0. Use f instead.
            'le_contact_import_index' => [
                'path'       => '/{object}/import/{page}',
                'controller' => 'MauticLeadBundle:Import:index',
                'defaults'   => [
                    'object' => 'leads',
                ],
            ],
            // @deprecated 2.9.1 to be removed in 3.0. Use le_import_action instead.
            'le_contact_import_action' => [
                'path'       => '/{object}/import/{objectAction}/{objectId}',
                'controller' => 'MauticLeadBundle:Import:execute',
                'defaults'   => [
                    'object' => 'leads',
                ],
            ],
            'le_import_index' => [
                'path'       => '/{object}/imports/{page}',
                'controller' => 'MauticLeadBundle:Import:list',
            ],
            'le_import_action' => [
                'path'       => '/import/{objectAction}/{objectId}',
                'controller' => 'MauticLeadBundle:Import:execute',
            ],
            'le_contact_action' => [
                'path'       => '/leads/{objectAction}/{objectId}',
                'controller' => 'MauticLeadBundle:Lead:execute',
            ],
            'le_company_index' => [
                'path'       => '/companies/{page}',
                'controller' => 'MauticLeadBundle:Company:index',
            ],
            'le_company_action' => [
                'path'       => '/companies/{objectAction}/{objectId}',
                'controller' => 'MauticLeadBundle:Company:execute',
            ],
            'le_segment_contacts' => [
                'path'       => '/segment/view/{objectId}/contact/{page}',
                'controller' => 'MauticLeadBundle:List:contacts',
            ],
            'le_listoptin_index' => [
                'path'       => '/list/{page}',
                'controller' => 'MauticLeadBundle:ListOptIn:index',
            ],
            'le_listoptin_action' => [
                'path'       => '/list/{objectAction}/{objectId}',
                'controller' => 'MauticLeadBundle:ListOptIn:execute',
            ],
        ],
        'public' => [
            'le_confirm_list' => [
                'path'       => '/listconfirm/{idhash}',
                'controller' => 'MauticLeadBundle:Public:listconfirm',
            ],
            'le_subscribe_list' => [
                'path'       => '/listsubscribe/{idhash}',
                'controller' => 'MauticLeadBundle:Public:listsubscribe',
            ],
            'le_unsubscribe_list' => [
                'path'       => '/listunsubscribe/{idhash}',
                'controller' => 'MauticLeadBundle:Public:listunsubscribe',
            ],
            'le_resubscribe_list' => [
                'path'       => '/listresubscribe/{idhash}',
                'controller' => 'MauticLeadBundle:Public:listresubscribe',
            ],
        ],
        'api' => [
            'mautic_api_contactsstandard' => [
                'standard_entity' => true,
                'name'            => 'leads',
                'path'            => '/leads',
                'controller'      => 'MauticLeadBundle:Api\LeadApi',
            ],
            'mautic_api_dncaddcontact' => [
                'path'       => '/leads/dnc/{channel}/add',
                'controller' => 'MauticLeadBundle:Api\LeadApi:addDnc',
                'method'     => 'POST',
                'defaults'   => [
                    'channel' => 'email',
                ],
            ],
            'mautic_api_dncremovecontact' => [
                'path'       => '/leads/dnc/{channel}/remove',
                'controller' => 'MauticLeadBundle:Api\LeadApi:removeDnc',
                'method'     => 'POST',
                'defaults'   => [
                    'channel' => 'email',
                ],
            ],
            'mautic_api_batchdncaddcontact' => [
                'path'       => '/leads/dnc/email/batch/add',
                'controller' => 'MauticLeadBundle:Api\LeadApi:addBatchDnc',
                'method'     => 'POST',
            ],
            'mautic_api_getcontactevents' => [
                'path'       => '/leads/{id}/activity',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getActivity',
                'method'     => 'POST', //POST
            ],
            'mautic_api_getcontactsevents' => [
                'path'       => '/leads/activity',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getAllActivity',
            ],
            'mautic_api_getcontacts' => [
                'path'       => '/leads/get',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getLeadEntity',
                'method'     => 'POST',
            ],
            'mautic_api_deletecontacts' => [
                'path'       => '/leads/delete',
                'controller' => 'MauticLeadBundle:Api\LeadApi:deleteLeadEntity',
                'method'     => 'DELETE',
            ],
            'mautic_api_getcontactnotes' => [
                'path'       => '/leads/{id}/notes',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getNotes',
            ],
            'mautic_api_getcontactdevices' => [
                'path'       => '/leads/{id}/devices',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getDevices',
            ],
            'mautic_api_getcontactcampaigns' => [
                'path'       => '/leads/workflows',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getCampaigns',
                'method'     => 'POST',
            ],
            'mautic_api_getcontactssegments' => [
                'path'       => '/leads/segments',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getLists',
                'method'     => 'POST',
            ],
            'mautic_api_getcontactstags' => [
                'path'       => '/leads/tags',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getLeadTags',
                'method'     => 'POST',
            ],
            'mautic_api_getcontactdrips' => [
                'path'       => '/leads/drips',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getLeadDrip',
                'method'     => 'POST',
            ],
            'mautic_api_getcontactslists' => [
                'path'       => '/leads/lists',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getLeadListOptin',
                'method'     => 'POST',
            ],
            'mautic_api_getcontactscompanies' => [
                'path'       => '/leads/{id}/companies',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getCompanies',
            ],
            'mautic_api_utmcreateevent' => [
                'path'       => '/leads/{id}/utm/add',
                'controller' => 'MauticLeadBundle:Api\LeadApi:addUtmTags',
                'method'     => 'POST',
            ],
            'mautic_api_utmremoveevent' => [
                'path'       => '/leads/{id}/utm/{utmid}/remove',
                'controller' => 'MauticLeadBundle:Api\LeadApi:removeUtmTags',
                'method'     => 'POST',
            ],
            'mautic_api_getcontactowners' => [
                'path'       => '/leads/list/owners',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getOwners',
            ],
            'mautic_api_getcontactfields' => [
                'path'       => '/leads/list/fields',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getFields',
            ],
            'mautic_api_getcontactsegments' => [
                'path'       => '/leads/list/segments',
                'controller' => 'MauticLeadBundle:Api\ListApi:getLists',
            ],
            'mautic_api_segmentsstandard' => [
                'standard_entity' => true,
                'name'            => 'lists',
                'path'            => '/segments',
                'controller'      => 'MauticLeadBundle:Api\ListApi',
            ],
            'mautic_api_segmentaddcontact' => [
                'path'       => '/segments/{id}/lead/add',
                'controller' => 'MauticLeadBundle:Api\ListApi:addLead',
                'method'     => 'POST',
            ],
            'mautic_api_segmentremovecontact' => [
                'path'       => '/segments/{id}/lead/remove',
                'controller' => 'MauticLeadBundle:Api\ListApi:removeLead',
                'method'     => 'POST',
            ],
            'mautic_api_segmentaddbatchcontact' => [
                'path'       => '/segments/lead/batch/add',
                'controller' => 'MauticLeadBundle:Api\ListApi:addOrRemoveBatchLeads',
                'method'     => 'POST',
            ],
            'mautic_api_segmentremovebatchcontact' => [
                'path'       => '/segments/lead/batch/remove',
                'controller' => 'MauticLeadBundle:Api\ListApi:addOrRemoveBatchLeads',
                'method'     => 'POST',
            ],
            'mautic_api_segmentgetcontact' => [
                'path'       => '/segments/{id}/leads',
                'controller' => 'MauticLeadBundle:Api\ListApi:getLeadsByList',
                'method'     => 'POST',
            ],
            'mautic_api_taggetcontact' => [
                'path'       => '/tags/{id}/leads',
                'controller' => 'MauticLeadBundle:Api\TagApi:getLeadsByTag',
                'method'     => 'POST',
            ],
            'mautic_api_tagaddcontact' => [
                'path'       => '/tags/{id}/lead/add',
                'controller' => 'MauticLeadBundle:Api\TagApi:addOrRemoveTag',
                'method'     => 'POST',
            ],
            'mautic_api_tagremovecontact' => [
                'path'       => '/tags/{id}/lead/remove',
                'controller' => 'MauticLeadBundle:Api\TagApi:addOrRemoveTag',
                'method'     => 'POST',
            ],
            'mautic_api_tagaddbatchtag' => [
                'path'       => '/tags/lead/batch/add',
                'controller' => 'MauticLeadBundle:Api\TagApi:addOrRemoveBatchTags',
                'method'     => 'POST',
            ],
            'mautic_api_tagremovebatchtag' => [
                'path'       => '/tags/lead/batch/remove',
                'controller' => 'MauticLeadBundle:Api\TagApi:addOrRemoveBatchTags',
                'method'     => 'POST',
            ],
            'mautic_api_companiesstandard' => [
                'standard_entity' => true,
                'name'            => 'companies',
                'path'            => '/companies',
                'controller'      => 'MauticLeadBundle:Api\CompanyApi',
            ],
            'mautic_api_companyaddcontact' => [
                'path'       => '/companies/{companyId}/contact/{contactId}/add',
                'controller' => 'MauticLeadBundle:Api\CompanyApi:addContact',
                'method'     => 'POST',
            ],
            'mautic_api_companyremovecontact' => [
                'path'       => '/companies/{companyId}/contact/{contactId}/remove',
                'controller' => 'MauticLeadBundle:Api\CompanyApi:removeContact',
                'method'     => 'POST',
            ],
            'mautic_api_fieldsstandard' => [
                'standard_entity' => true,
                'name'            => 'fields',
                'path'            => '/fields/{object}',
                'controller'      => 'MauticLeadBundle:Api\FieldApi',
                'defaults'        => [
                    'object' => 'contact',
                ],
            ],
            'mautic_api_notesstandard' => [
                'standard_entity' => true,
                'name'            => 'notes',
                'path'            => '/notes',
                'controller'      => 'MauticLeadBundle:Api\NoteApi',
            ],
            'mautic_api_devicesstandard' => [
                'standard_entity' => true,
                'name'            => 'devices',
                'path'            => '/devices',
                'controller'      => 'MauticLeadBundle:Api\DeviceApi',
            ],
            'mautic_api_tagsstandard' => [
                'standard_entity' => true,
                'name'            => 'tags',
                'path'            => '/tags',
                'controller'      => 'MauticLeadBundle:Api\TagApi',
            ],
            'mautic_api_listsstandard' => [
                'standard_entity' => true,
                'name'            => 'listoptins',
                'path'            => '/lists',
                'controller'      => 'MauticLeadBundle:Api\ListOptinApi',
            ],
            'mautic_api_listaddcontact' => [
                'path'       => '/lists/{id}/lead/add',
                'controller' => 'MauticLeadBundle:Api\ListOptinApi:addOrRemoveLead',
                'method'     => 'POST',
            ],
            'mautic_api_listremovecontact' => [
                'path'       => '/lists/{id}/lead/remove',
                'controller' => 'MauticLeadBundle:Api\ListOptinApi:addOrRemoveLead',
                'method'     => 'POST',
            ],
            'mautic_api_listaddbatchcontact' => [
                'path'       => '/lists/lead/batch/add',
                'controller' => 'MauticLeadBundle:Api\ListOptinApi:addOrRemoveBatchLeads',
                'method'     => 'POST',
            ],
            'mautic_api_listremovebatchcontact' => [
                'path'       => '/lists/lead/batch/remove',
                'controller' => 'MauticLeadBundle:Api\ListOptinApi:addOrRemoveBatchLeads',
                'method'     => 'POST',
            ],
            'mautic_api_listgetconfirmedcontact' => [
                'path'       => '/lists/{id}/lead/confirmed',
                'controller' => 'MauticLeadBundle:Api\ListOptinApi:getEntityByStatus',
                'method'     => 'POST',
            ],
            'mautic_api_listgetpendingcontact' => [
                'path'       => '/lists/{id}/lead/unconfirmed',
                'controller' => 'MauticLeadBundle:Api\ListOptinApi:getEntityByStatus',
                'method'     => 'POST',
            ],
            'mautic_api_listgetunsubscribedcontact' => [
                'path'       => '/lists/{id}/lead/unsubscribed',
                'controller' => 'MauticLeadBundle:Api\ListOptinApi:getEntityByStatus',
                'method'     => 'POST',
            ],

            // @deprecated 2.6.0 to be removed in 3.0
            'bc_mautic_api_segmentaddcontact' => [
                'path'       => '/segments/{id}/lead/add/{leadId}',
                'controller' => 'MauticLeadBundle:Api\ListApi:addLead',
                'method'     => 'POST',
            ],
            'bc_mautic_api_segmentremovecontact' => [
                'path'       => '/segments/{id}/lead/remove/{leadId}',
                'controller' => 'MauticLeadBundle:Api\ListApi:removeLead',
                'method'     => 'POST',
            ],
            'bc_mautic_api_companyaddcontact' => [
                'path'       => '/companies/{companyId}/contact/add/{contactId}',
                'controller' => 'MauticLeadBundle:Api\CompanyApi:addContact',
                'method'     => 'POST',
            ],
            'bc_mautic_api_companyremovecontact' => [
                'path'       => '/companies/{companyId}/contact/remove/{contactId}',
                'controller' => 'MauticLeadBundle:Api\CompanyApi:removeContact',
                'method'     => 'POST',
            ],
            'bc_mautic_api_dncaddcontact' => [
                'path'       => '/leads/{id}/dnc/add/{channel}',
                'controller' => 'MauticLeadBundle:Api\LeadApi:addDnc',
                'method'     => 'POST',
                'defaults'   => [
                    'channel' => 'email',
                ],
            ],
            'bc_mautic_api_dncremovecontact' => [
                'path'       => '/leads/{id}/dnc/remove/{channel}',
                'controller' => 'MauticLeadBundle:Api\LeadApi:removeDnc',
                'method'     => 'POST',
            ],
            // @deprecated 2.10.0 to be removed in 3.0
            'bc_mautic_api_getcontactevents' => [
                'path'       => '/leads/{id}/events',
                'controller' => 'MauticLeadBundle:Api\LeadApi:getEvents',
            ],
        ],
    ],
    'menu' => [
        'main' => [
            'items' => [
                'le.lead.leads' => [
                    'iconClass' => 'fa-user',
                    'access'    => ['lead:leads:viewown', 'lead:leads:viewother'],
                    'route'     => 'le_contact_index',
                    'priority'  => 70,
                    'parent'    => 'le.core.leads',
                ],
                /*   'le.companies.menu.index' => [
                      'route'     => 'le_company_index',
                      'iconClass' => 'fa-building-o',
                      'access'    => ['lead:leads:viewother'],
                      'priority'  => 75,
                  ],*/
                'le.lead.list.menu.index' => [
                    'iconClass' => 'fa-pie-chart',
                    'access'    => ['lead:leads:viewown', 'lead:leads:viewother'],
                    'route'     => 'le_segment_index',
                    'priority'  => 60,
                    'parent'    => 'le.core.leads',
                ],
                'le.lead.tags.menu.index' => [
                    'iconClass' => 'fa-tags',
                    'access'    => ['lead:tags:full'],
                    'route'     => 'le_tags_index',
                    'priority'  => 55,
                    'parent'    => 'le.core.leads',
                ],
              /* 'le.lead.field.menu.index' => [
                    'iconClass'  => 'fa-cog',
                    'route'      => 'le_contactfield_index',
                    'access'     => 'lead:fields:full',
                    'priority'   => 45,
                  'parent'       => 'le.core.leads',
               ],

                'le.lead.import.menu.index' => [
                     'iconClass'  => 'fa-cloud-upload',
                     'route'      => 'le_import_action',
                     'access'     => 'lead:imports:create',
                     'priority'   => 40,
                     'parent'     => 'le.core.leads',
                ],*/

               'mautic.point.menu.index' => [
                    'route'      => 'le_point_index',
                    'iconClass'  => 'fa fa-sliders',
                    'access'     => 'point:points:view',
                    'priority'   => 50,
                    'parent'     => 'le.core.leads',
                ],
               'le.lead.list.optin.menu.index' => [
                    'iconClass' => 'fa-list-ul',
                    'access'    => ['lead:listoptin:viewown', 'lead:listoptin:viewother'],
                    'route'     => 'le_listoptin_index',
                    'priority'  => 65,
                    'parent'    => 'le.core.leads',
               ],
               /*
                'mautic.point.trigger.menu.index' => [
                    'route'    => 'le_pointtrigger_index',
                    'access'   => 'point:triggers:view',
                    'parent'   => 'le.segments.root',
                ],*/
            ],
        ],
        'admin' => [
         /*   'priority' => 450,
            'items'    => [
                'le.lead.field.menu.index' => [
                    'id'         => 'mautic_lead_field',
                    'iconClass'  => 'fa-list',
                    'route'      => 'le_contactfield_index',
                     'access'    => 'lead:fields:full',
                ],
            ],   */
            'le.beamer.menu.index' => [
                'iconClass'       => 'fa  fa-bell',
                'priority'        => 450,
                'id'              => 'le_beamer_index',
            ],

            'le.feauturesandideas.menu.index' => [
                'route'           => 'le_featuresandideas_index',
                'iconClass'       => 'fa fa-question-circle',
                'priority'        => 450,
                'id'              => 'le_feauturesandideas_index',
            ],
        ],
    ],
    'services' => [
        'events' => [
            'mautic.lead.subscriber' => [
                'class'     => 'Mautic\LeadBundle\EventListener\LeadSubscriber',
                'arguments' => [
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                    'mautic.lead.event.dispatcher',
                ],
                'methodCalls' => [
                    'setModelFactory' => ['mautic.model.factory'],
                ],
            ],
            'mautic.lead.subscriber.company' => [
                'class'     => 'Mautic\LeadBundle\EventListener\CompanySubscriber',
                'arguments' => [
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                ],
            ],
            'mautic.lead.emailbundle.subscriber' => [
                'class' => 'Mautic\LeadBundle\EventListener\EmailSubscriber',
            ],
            'mautic.lead.formbundle.subscriber' => [
                'class'     => 'Mautic\LeadBundle\EventListener\FormSubscriber',
                'arguments' => [
                    'mautic.email.model.email',
                    'mautic.lead.model.lead',
                ],
            ],
            'mautic.lead.campaignbundle.subscriber' => [
                'class'     => 'Mautic\LeadBundle\EventListener\CampaignSubscriber',
                'arguments' => [
                    'mautic.helper.ip_lookup',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.field',
                    'mautic.lead.model.list',
                    'mautic.campaign.model.campaign',
                    'mautic.helper.licenseinfo',
                    'mautic.lead.model.listoptin',
                ],
            ],
            'mautic.lead.reportbundle.subscriber' => [
                'class'     => \Mautic\LeadBundle\EventListener\ReportSubscriber::class,
                'arguments' => [
                    'mautic.lead.model.lead',
                    'mautic.stage.model.stage',
                    'mautic.campaign.model.campaign',
                    'mautic.lead.model.company',
                    'mautic.lead.model.company_report_data',
                    'mautic.lead.reportbundle.fields_builder',
                ],
            ],
            'mautic.lead.reportbundle.segment_subscriber' => [
                'class'     => \Mautic\LeadBundle\EventListener\SegmentReportSubscriber::class,
                'arguments' => [
                    'mautic.lead.reportbundle.fields_builder',
                ],
            ],
            'mautic.lead.reportbundle.list_subscriber' => [
                'class'     => \Mautic\LeadBundle\EventListener\ListReportSubscriber::class,
                'arguments' => [
                    'mautic.lead.reportbundle.fields_builder',
                ],
            ],
            'mautic.lead.calendarbundle.subscriber' => [
                'class' => 'Mautic\LeadBundle\EventListener\CalendarSubscriber',
            ],
            'mautic.lead.pointbundle.subscriber' => [
                'class' => 'Mautic\LeadBundle\EventListener\PointSubscriber',
            ],
            'mautic.lead.search.subscriber' => [
                'class'     => \Mautic\LeadBundle\EventListener\SearchSubscriber::class,
                'arguments' => [
                    'mautic.lead.model.lead',
                    'mautic.lead.model.tag',
                    'mautic.lead.model.list',
                    'mautic.lead.model.listoptin',
                    'doctrine.orm.entity_manager',
                    'mautic.campaign.model.campaign',
                    'mautic.factory',
                ],
            ],
            'mautic.webhook.subscriber' => [
                'class'       => 'Mautic\LeadBundle\EventListener\WebhookSubscriber',
                'methodCalls' => [
                    'setWebhookModel' => ['mautic.webhook.model.webhook'],
                ],
            ],
            'mautic.lead.dashboard.subscriber' => [
                'class'     => 'Mautic\LeadBundle\EventListener\DashboardSubscriber',
                'arguments' => [
                    'mautic.lead.model.lead',
                    'mautic.lead.model.list',
                ],
            ],
            'mautic.lead.maintenance.subscriber' => [
                'class'     => 'Mautic\LeadBundle\EventListener\MaintenanceSubscriber',
                'arguments' => [
                    'doctrine.dbal.default_connection',
                ],
            ],
            'mautic.lead.stats.subscriber' => [
                'class'     => \Mautic\LeadBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                ],
            ],
            'mautic.lead.button.subscriber' => [
                'class'     => \Mautic\LeadBundle\EventListener\ButtonSubscriber::class,
                'arguments' => [
                    'mautic.factory',
                ],
            ],
            'mautic.lead.import.subscriber' => [
                'class'     => Mautic\LeadBundle\EventListener\ImportSubscriber::class,
                'arguments' => [
                    'mautic.helper.ip_lookup',
                    'mautic.core.model.auditlog',
                ],
            ],
            'mautic.lead.configbundle.subscriber' => [
                'class' => Mautic\LeadBundle\EventListener\ConfigSubscriber::class,
            ],
            'mautic.lead.timeline_events.subscriber' => [
                'class'     => \Mautic\LeadBundle\EventListener\TimelineEventLogSubscriber::class,
                'arguments' => [
                    'translator',
                    'mautic.lead.repository.lead_event_log',
                ],
            ],
        ],
        'forms' => [
            'mautic.form.type.lead' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\LeadType',
                'arguments' => [
                    'mautic.factory',
                    'mautic.lead.model.company',
                    'mautic.lead.model.list',
                    'mautic.lead.model.listoptin',
                ],
                'alias'     => 'lead',
            ],
            'mautic.form.type.leadlist' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\ListType',
                'arguments' => [
                    'translator',
                    'mautic.lead.model.list',
                    'mautic.email.model.email',
                    'mautic.security',
                    'mautic.lead.model.lead',
                    'mautic.stage.model.stage',
                    'mautic.category.model.category',
                    'mautic.helper.user',
                    'mautic.page.model.page',
                    'mautic.user.model.user',
                    'mautic.form.model.form',
                    'mautic.asset.model.asset',
                    'mautic.email.model.dripemail',
                    'mautic.lead.model.listoptin',
                ],
                'alias' => 'leadlist',
            ],
            'mautic.form.type.leadlistoptin' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\ListOptInType',
                'arguments' => [
                    'translator',
                    'mautic.factory',
                ],
                'alias' => 'leadlistoptin',
            ],
            'mautic.form.type.campaign_list_filter' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\CampaignListFilterType',
                'arguments' => [
                    'translator',
                    'mautic.lead.model.list',
                    'mautic.email.model.email',
                    'mautic.security',
                    'mautic.lead.model.lead',
                    'mautic.stage.model.stage',
                    'mautic.category.model.category',
                    'mautic.helper.user',
                    'mautic.page.model.page',
                    'mautic.user.model.user',
                    'mautic.form.model.form',
                    'mautic.asset.model.asset',
                    'mautic.email.model.dripemail',
                    'mautic.lead.model.listoptin',
                ],
                'alias' => 'campaignlistfilter',
            ],
            'mautic.form.type.email_receipient_filter' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\EmailRecipientFilterType',
                'arguments' => [
                    'translator',
                    'mautic.lead.model.list',
                    'mautic.email.model.email',
                    'mautic.security',
                    'mautic.lead.model.lead',
                    'mautic.stage.model.stage',
                    'mautic.category.model.category',
                    'mautic.helper.user',
                    'mautic.page.model.page',
                    'mautic.user.model.user',
                    'mautic.form.model.form',
                    'mautic.asset.model.asset',
                    'mautic.email.model.dripemail',
                    'mautic.lead.model.listoptin',
                ],
                'alias' => 'emailrecipientsfilter',
            ],
            'mautic.form.type.dripemail_receipient_filter' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\DripEmailRecipientFilterType',
                'arguments' => [
                    'translator',
                    'mautic.lead.model.list',
                    'mautic.email.model.email',
                    'mautic.security',
                    'mautic.lead.model.lead',
                    'mautic.stage.model.stage',
                    'mautic.category.model.category',
                    'mautic.helper.user',
                    'mautic.page.model.page',
                    'mautic.user.model.user',
                    'mautic.form.model.form',
                    'mautic.asset.model.asset',
                    'mautic.email.model.dripemail',
                    'mautic.lead.model.listoptin',
                ],
                'alias' => 'dripemailrecipientsfilter',
            ],
            'mautic.form.type.leadlist_choices' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\LeadListType',
                'arguments' => ['mautic.factory'],
                'alias'     => 'leadlist_choices',
            ],
            'mautic.form.type.leadlistoptin_choices' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\LeadListOptInType',
                'arguments' => ['mautic.factory'],
                'alias'     => 'listoptin_choices',
            ],
            'mautic.form.type.leadlist_filter' => [
                'class'       => 'Mautic\LeadBundle\Form\Type\FilterType',
                'alias'       => 'leadlist_filter',
                'arguments'   => ['mautic.factory'],
                'methodCalls' => [
                    'setConnection' => [
                        'database_connection',
                    ],
                ],
            ],
            'mautic.form.type.leadfield' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\FieldType',
                'arguments' => ['mautic.factory'],
                'alias'     => 'leadfield',
            ],
            'mautic.form.type.lead.submitaction.pointschange' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\FormSubmitActionPointsChangeType',
                'arguments' => ['mautic.factory'],
                'alias'     => 'lead_submitaction_pointschange',
            ],
            'mautic.form.type.lead.submitaction.addutmtags' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\ActionAddUtmTagsType',
                'arguments' => 'mautic.factory',
                'alias'     => 'lead_action_addutmtags',
            ],
            'mautic.form.type.lead.submitaction.removedonotcontact' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\ActionRemoveDoNotContact',
                'arguments' => 'mautic.factory',
                'alias'     => 'lead_action_removedonotcontact',
            ],
            'mautic.form.type.lead.submitaction.changelist' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\EventListType',
                'arguments' => ['mautic.factory'],
                'alias'     => 'leadlist_action_type',
            ],
            'mautic.form.type.leadpoints_trigger' => [
                'class' => 'Mautic\LeadBundle\Form\Type\PointTriggerType',
                'alias' => 'leadpoints_trigger',
            ],
            'mautic.form.type.leadpoints_action' => [
                'class' => 'Mautic\LeadBundle\Form\Type\PointActionType',
                'alias' => 'leadpoints_action',
            ],
            'mautic.form.type.leadscore_action' => [
                'class' => 'Mautic\LeadBundle\Form\Type\ScoreActionType',
                'alias' => 'leadscore_action',
            ],
            'mautic.form.type.leadlist_trigger' => [
                'class' => 'Mautic\LeadBundle\Form\Type\ListTriggerType',
                'alias' => 'leadlist_trigger',
            ],
            'mautic.form.type.leadlist_action' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\ListActionType',
                'alias'     => 'leadlist_action',
                'arguments' => ['mautic.factory'],
            ],
            'mautic.form.type.leadlistoptin_action' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\ListOptInActionType',
                'alias'     => 'leadlistoptin_action',
                'arguments' => ['mautic.factory'],
            ],
            'mautic.form.type.updatelead_action' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\UpdateLeadActionType',
                'arguments' => ['mautic.factory'],
                'alias'     => 'updatelead_action',
            ],
            'mautic.form.type.leadnote' => [
                'class' => Mautic\LeadBundle\Form\Type\NoteType::class,
                'alias' => 'leadnote',
            ],
            'mautic.form.type.leaddevice' => [
                'class' => Mautic\LeadBundle\Form\Type\DeviceType::class,
                'alias' => 'leaddevice',
            ],
            'mautic.form.type.lead_import' => [
                'class' => 'Mautic\LeadBundle\Form\Type\LeadImportType',
                'alias' => 'lead_import',
            ],
            'mautic.form.type.lead_field_import' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\LeadImportFieldType',
                'arguments' => ['mautic.factory'],
                'alias'     => 'lead_field_import',
            ],
            'mautic.form.type.lead_quickemail' => [
                'class'     => \Mautic\LeadBundle\Form\Type\EmailType::class,
                'arguments' => ['mautic.factory'],
                'alias'     => 'lead_quickemail',
            ],
            'mautic.form.type.lead_tags' => [
                'class'     => \Mautic\LeadBundle\Form\Type\TagListType::class,
                'alias'     => 'lead_tags',
                'arguments' => ['translator'],
            ],
            'mautic.form.type.lead_tag' => [
                'class'     => \Mautic\LeadBundle\Form\Type\TagType::class,
                'alias'     => 'lead_tag',
                'arguments' => ['doctrine.orm.entity_manager'],
            ],
            'mautic.form.type.modify_lead_tags' => [
                'class'     => \Mautic\LeadBundle\Form\Type\ModifyLeadTagsType::class,
                'alias'     => 'modify_lead_tags',
                'arguments' => ['translator'],
            ],
            'mautic.form.type.lead_entity_tag' => [
                'class' => \Mautic\LeadBundle\Form\Type\TagEntityType::class,
                'alias' => \Mautic\LeadBundle\Form\Type\TagEntityType::class,
            ],
            'mautic.form.type.lead_batch' => [
                'class' => 'Mautic\LeadBundle\Form\Type\BatchType',
                'alias' => 'lead_batch',
            ],
            'mautic.form.type.export' => [
                'class' => 'Mautic\LeadBundle\Form\Type\ExportType',
                'alias' => 'export',
            ],
            'mautic.form.type.lead_batch_dnc' => [
                'class' => 'Mautic\LeadBundle\Form\Type\DncType',
                'alias' => 'lead_batch_dnc',
            ],
            'mautic.form.type.lead_batch_stage' => [
                'class' => 'Mautic\LeadBundle\Form\Type\StageType',
                'alias' => 'lead_batch_stage',
            ],
            'mautic.form.type.lead_batch_owner' => [
                'class' => 'Mautic\LeadBundle\Form\Type\OwnerType',
                'alias' => 'lead_batch_owner',
            ],
            'mautic.form.type.lead_merge' => [
                'class' => 'Mautic\LeadBundle\Form\Type\MergeType',
                'alias' => 'lead_merge',
            ],
            'mautic.form.type.lead_contact_frequency_rules' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\ContactFrequencyType',
                'arguments' => [
                    'mautic.helper.core_parameters',
                ],
                'alias' => 'lead_contact_frequency_rules',
            ],
            'mautic.form.type.campaignevent_lead_field_value' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\CampaignEventLeadFieldValueType',
                'arguments' => [
                    'translator',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.field',
                ],
                'alias' => 'campaignevent_lead_field_value',
            ],
            'mautic.form.type.campaignevent_lead_device' => [
                'class' => 'Mautic\LeadBundle\Form\Type\CampaignEventLeadDeviceType',
                'alias' => 'campaignevent_lead_device',
            ],
            'mautic.form.type.campaignevent_lead_tags' => [
                'class'     => Mautic\LeadBundle\Form\Type\CampaignEventLeadTagsType::class,
                'arguments' => ['translator'],
                'alias'     => 'campaignevent_lead_tags',
            ],
            'mautic.form.type.campaignevent_lead_segments' => [
                'class' => 'Mautic\LeadBundle\Form\Type\CampaignEventLeadSegmentsType',
                'alias' => 'campaignevent_lead_segments',
            ],
            'mautic.form.type.campaignsource_lists' => [
                'class' => 'Mautic\LeadBundle\Form\Type\CampaignSourceLeadSegmentsType',
                'alias' => 'campaignsource_lists',
            ],
            'mautic.form.type.campaignevent_lead_listoptin' => [
                'class' => 'Mautic\LeadBundle\Form\Type\CampaignEventLeadListOptinType',
                'alias' => 'campaignevent_lead_listoptin',
            ],
            'mautic.form.type.campaignsource_listoptin' => [
                'class' => 'Mautic\LeadBundle\Form\Type\CampaignSourceLeadListOptinType',
                'alias' => 'campaignsource_listoptin',
            ],
            'mautic.form.type.campaignevent_lead_campaigns' => [
                'class'     => Mautic\LeadBundle\Form\Type\CampaignEventLeadCampaignsType::class,
                'alias'     => 'campaignevent_lead_campaigns',
                'arguments' => ['mautic.lead.model.list'],
            ],
            'mautic.form.type.campaignevent_lead_owner' => [
                'class' => 'Mautic\LeadBundle\Form\Type\CampaignEventLeadOwnerType',
                'alias' => 'campaignevent_lead_owner',
            ],
            'mautic.form.type.lead_fields' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\LeadFieldsType',
                'arguments' => ['mautic.factory'],
                'alias'     => 'leadfields_choices',
            ],
            'mautic.form.type.lead_dashboard_leads_in_time_widget' => [
                'class' => 'Mautic\LeadBundle\Form\Type\DashboardLeadsInTimeWidgetType',
                'alias' => 'lead_dashboard_leads_in_time_widget',
            ],
            'mautic.form.type.lead_dashboard_leads_lifetime_widget' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\DashboardLeadsLifetimeWidgetType',
                'arguments' => ['mautic.factory'],
                'alias'     => 'lead_dashboard_leads_lifetime_widget',
            ],
            'mautic.company.type.form' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\CompanyType',
                'arguments' => ['doctrine.orm.entity_manager', 'mautic.security', 'router', 'translator'],
                'alias'     => 'company',
            ],
            'mautic.company.campaign.action.type.form' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\AddToCompanyActionType',
                'arguments' => ['router'],
                'alias'     => 'addtocompany_action',
            ],
            'mautic.lead.events.changeowner.type.form' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\ChangeOwnerType',
                'arguments' => ['mautic.user.model.user'],
            ],
            'mautic.company.list.type.form' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\CompanyListType',
                'arguments' => [
                    'mautic.lead.model.company',
                    'mautic.helper.user',
                    'translator',
                    'router',
                    'database_connection',
                ],
                'alias' => 'company_list',
            ],
            'mautic.form.type.lead_categories' => [
                'class'     => 'Mautic\LeadBundle\Form\Type\LeadCategoryType',
                'arguments' => ['mautic.category.model.category'],
                'alias'     => 'leadcategory_choices',
            ],
            'mautic.company.merge.type.form' => [
                'class' => 'Mautic\LeadBundle\Form\Type\CompanyMergeType',
                'alias' => 'company_merge',
            ],
            'mautic.form.type.company_change_score' => [
                'class' => 'Mautic\LeadBundle\Form\Type\CompanyChangeScoreActionType',
                'alias' => 'scorecontactscompanies_action',
            ],
            'mautic.form.type.config.form' => [
                'class' => Mautic\LeadBundle\Form\Type\ConfigType::class,
                'alias' => 'leadconfig',
            ],
        ],
        'other' => [
            'mautic.lead.doctrine.subscriber' => [
                'class'     => 'Mautic\LeadBundle\EventListener\DoctrineSubscriber',
                'tag'       => 'doctrine.event_subscriber',
                'arguments' => ['monolog.logger.mautic'],
            ],
            'mautic.validator.leadlistaccess' => [
                'class'     => 'Mautic\LeadBundle\Form\Validator\Constraints\LeadListAccessValidator',
                'arguments' => ['mautic.factory'],
                'tag'       => 'validator.constraint_validator',
                'alias'     => 'leadlist_access',
            ],
            \Mautic\LeadBundle\Form\Validator\Constraints\FieldAliasKeywordValidator::class => [
                'class'     => \Mautic\LeadBundle\Form\Validator\Constraints\FieldAliasKeywordValidator::class,
                'tag'       => 'validator.constraint_validator',
                'arguments' => [
                    'mautic.lead.model.list',
                    'mautic.helper.field.alias',
                ],
            ],
            'mautic.lead.constraint.alias' => [
                'class'     => 'Mautic\LeadBundle\Form\Validator\Constraints\UniqueUserAliasValidator',
                'arguments' => ['mautic.factory'],
                'tag'       => 'validator.constraint_validator',
                'alias'     => 'uniqueleadlist',
            ],
            'mautic.lead.event.dispatcher' => [
                'class'     => \Mautic\LeadBundle\Helper\LeadChangeEventDispatcher::class,
                'arguments' => [
                    'event_dispatcher',
                ],
            ],
            'mautic.validator.emailcontent' => [
                'class'     => 'Mautic\LeadBundle\Form\Validator\Constraints\EmailContentVerifierValidator',
                'arguments' => ['mautic.factory', 'translator'],
                'tag'       => 'validator.constraint_validator',
                'alias'     => 'emailcontent_verifier',
            ],
        ],
        'repositories' => [
            'mautic.lead.repository.dnc' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Mautic\LeadBundle\Entity\DoNotContact::class,
                ],
            ],
            'mautic.lead.repository.lead' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Mautic\LeadBundle\Entity\Lead::class,
                ],
            ],
            'mautic.lead.repository.lead_event_log' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Mautic\LeadBundle\Entity\LeadEventLog::class,
                ],
            ],
            'mautic.lead.repository.lead_device' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Mautic\LeadBundle\Entity\LeadDevice::class,
                ],
            ],
            'mautic.lead.repository.merged_records' => [
                'class'     => Doctrine\ORM\EntityRepository::class,
                'factory'   => ['@doctrine.orm.entity_manager', 'getRepository'],
                'arguments' => [
                    \Mautic\LeadBundle\Entity\MergeRecord::class,
                ],
            ],
        ],
        'helpers' => [
            'mautic.helper.template.avatar' => [
                'class'     => 'Mautic\LeadBundle\Templating\Helper\AvatarHelper',
                'arguments' => ['mautic.factory'],
                'alias'     => 'lead_avatar',
            ],
            'mautic.helper.field.alias' => [
                'class'     => \Mautic\LeadBundle\Helper\FieldAliasHelper::class,
                'arguments' => ['mautic.lead.model.field'],
            ],
        ],
        'models' => [
            'mautic.lead.model.lead' => [
                'class'     => \Mautic\LeadBundle\Model\LeadModel::class,
                'arguments' => [
                    'request_stack',
                    'mautic.helper.cookie',
                    'mautic.helper.ip_lookup',
                    'mautic.helper.paths',
                    'mautic.helper.integration',
                    'mautic.lead.model.field',
                    'mautic.lead.model.list',
                    'form.factory',
                    'mautic.lead.model.company',
                    'mautic.category.model.category',
                    'mautic.channel.helper.channel_list',
                    'mautic.helper.core_parameters',
                    'mautic.validator.email',
                    'mautic.user.provider',
                    'mautic.tracker.contact',
                    'mautic.tracker.device',
                    'mautic.lead.model.listoptin',
                ],
            ],
            'mautic.lead.model.field' => [
                'class'     => 'Mautic\LeadBundle\Model\FieldModel',
                'arguments' => [
                    'mautic.schema.helper.index',
                    'mautic.schema.helper.column',
                ],
            ],
            'mautic.lead.model.list' => [
                'class'     => 'Mautic\LeadBundle\Model\ListModel',
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.helper.integration',
                ],
            ],
            'mautic.lead.model.listoptin' => [
                'class'     => 'Mautic\LeadBundle\Model\ListOptInModel',
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'mautic.helper.integration',
                ],
            ],
            'mautic.lead.model.note' => [
                'class' => 'Mautic\LeadBundle\Model\NoteModel',
            ],
            'mautic.lead.model.device' => [
                'class'     => Mautic\LeadBundle\Model\DeviceModel::class,
                'arguments' => [
                    'mautic.lead.repository.lead_device',
                ],
            ],
            'mautic.lead.model.company' => [
                'class'     => 'Mautic\LeadBundle\Model\CompanyModel',
                'arguments' => [
                    'mautic.lead.model.field',
                    'session',
                    'mautic.validator.email',
                ],
            ],
            'mautic.lead.model.import' => [
                'class'     => Mautic\LeadBundle\Model\ImportModel::class,
                'arguments' => [
                    'mautic.helper.paths',
                    'mautic.lead.model.lead',
                    'mautic.core.model.notification',
                    'mautic.helper.core_parameters',
                    'mautic.lead.model.company',
                    'mautic.helper.licenseinfo',
                ],
            ],
            'mautic.lead.model.tag' => [
                'class' => \Mautic\LeadBundle\Model\TagModel::class,
            ],
            'mautic.lead.model.company_report_data' => [
                'class'     => \Mautic\LeadBundle\Model\CompanyReportData::class,
                'arguments' => [
                    'mautic.lead.model.field',
                    'translator',
                    'mautic.security',
                ],
            ],
            'mautic.lead.reportbundle.fields_builder' => [
                'class'     => \Mautic\LeadBundle\Report\FieldsBuilder::class,
                'arguments' => [
                    'mautic.lead.model.field',
                    'mautic.lead.model.list',
                    'mautic.user.model.user',
                    'mautic.lead.model.listoptin',
                ],
            ],
            'mautic.lead.model.dnc' => [
                'class'     => \Mautic\LeadBundle\Model\DoNotContact::class,
                'arguments' => [
                    'mautic.lead.model.lead',
                    'mautic.lead.repository.dnc',
                ],
            ],
            'mautic.lead.factory.device_detector_factory' => [
                'class' => \Mautic\LeadBundle\Tracker\Factory\DeviceDetectorFactory\DeviceDetectorFactory::class,
            ],
            'mautic.lead.service.contact_tracking_service' => [
                'class'     => \Mautic\LeadBundle\Tracker\Service\ContactTrackingService\ContactTrackingService::class,
                'arguments' => [
                    'mautic.helper.cookie',
                    'mautic.lead.repository.lead_device',
                    'mautic.lead.repository.lead',
                    'mautic.lead.repository.merged_records',
                    'request_stack',
                ],
            ],
            'mautic.lead.service.device_creator_service' => [
                'class' => \Mautic\LeadBundle\Tracker\Service\DeviceCreatorService\DeviceCreatorService::class,
            ],
            'mautic.lead.service.device_tracking_service' => [
                'class'     => \Mautic\LeadBundle\Tracker\Service\DeviceTrackingService\DeviceTrackingService::class,
                'arguments' => [
                    'mautic.helper.cookie',
                    'doctrine.orm.entity_manager',
                    'mautic.lead.repository.lead_device',
                    'mautic.helper.random',
                    'request_stack',
                ],
            ],
            'mautic.tracker.contact' => [
                'class'     => \Mautic\LeadBundle\Tracker\ContactTracker::class,
                'arguments' => [
                    'mautic.lead.repository.lead',
                    'mautic.lead.service.contact_tracking_service',
                    'mautic.tracker.device',
                    'mautic.security',
                    'monolog.logger.mautic',
                    'mautic.helper.ip_lookup',
                    'request_stack',
                    'mautic.helper.core_parameters',
                    'event_dispatcher',
                ],
            ],
            'mautic.tracker.device' => [
                'class'     => \Mautic\LeadBundle\Tracker\DeviceTracker::class,
                'arguments' => [
                    'mautic.lead.service.device_creator_service',
                    'mautic.lead.factory.device_detector_factory',
                    'mautic.lead.service.device_tracking_service',
                    'monolog.logger.mautic',
                ],
            ],
        ],
    ],
    'parameters' => [
        'parallel_import_limit'               => 1,
        'background_import_if_more_rows_than' => 0,
    ],
];
