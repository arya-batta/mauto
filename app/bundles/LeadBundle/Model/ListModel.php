<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Model;

use Mautic\CoreBundle\Helper\Chart\BarChart;
use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\CoreBundle\Helper\Chart\PieChart;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\CoreBundle\Helper\ProgressBarHelper;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadList;
use Mautic\LeadBundle\Entity\ListLead;
use Mautic\LeadBundle\Entity\OperatorListTrait;
use Mautic\LeadBundle\Event\LeadListEvent;
use Mautic\LeadBundle\Event\LeadListFiltersChoicesEvent;
use Mautic\LeadBundle\Event\ListChangeEvent;
use Mautic\LeadBundle\Event\ListPreProcessListEvent;
use Mautic\LeadBundle\Helper\FormFieldHelper;
use Mautic\LeadBundle\LeadEvents;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class ListModel
 * {@inheritdoc}
 */
class ListModel extends FormModel
{
    use OperatorListTrait;

    /**
     * @var CoreParametersHelper
     */
    protected $coreParametersHelper;
    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;

    /**
     * ListModel constructor.
     *
     * @param CoreParametersHelper $coreParametersHelper
     * @param IntegrationHelper    $integrationHelper
     */
    public function __construct(CoreParametersHelper $coreParametersHelper, IntegrationHelper $integrationHelper)
    {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->integrationHelper    = $integrationHelper;
    }

    /**
     * Used by addLead and removeLead functions.
     *
     * @var array
     */
    private $leadChangeLists = [];

    /**
     * {@inheritdoc}
     *
     * @return \Mautic\LeadBundle\Entity\LeadListRepository
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function getRepository()
    {
        /** @var \Mautic\LeadBundle\Entity\LeadListRepository $repo */
        $repo = $this->em->getRepository('MauticLeadBundle:LeadList');

        $repo->setDispatcher($this->dispatcher);
        $repo->setTranslator($this->translator);

        return $repo;
    }

    /**
     * Returns the repository for the table that houses the leads associated with a list.
     *
     * @return \Mautic\LeadBundle\Entity\ListLeadRepository
     */
    public function getListLeadRepository()
    {
        return $this->em->getRepository('MauticLeadBundle:ListLead');
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getPermissionBase()
    {
        return 'lead:lists';
    }

    /**
     * {@inheritdoc}
     *
     * @param      $entity
     * @param bool $unlock
     *
     * @return mixed|void
     */
    public function saveEntity($entity, $unlock = true)
    {
        $isNew = ($entity->getId()) ? false : true;

        //set some defaults
        $this->setTimestamps($entity, $isNew, $unlock);

        $alias = $entity->getAlias();
        if (empty($alias)) {
            $alias = $entity->getName();
        }
        $alias = $this->cleanAlias($alias, '', false, '-');

        //make sure alias is not already taken
        $repo      = $this->getRepository();
        $testAlias = $alias;
        $existing  = $repo->getLists($this->userHelper->getUser(), $testAlias, $entity->getId());
        $count     = count($existing);
        $aliasTag  = $count;

        while ($count) {
            $testAlias = $alias.$aliasTag;
            $existing  = $repo->getLists($this->userHelper->getUser(), $testAlias, $entity->getId());
            $count     = count($existing);
            ++$aliasTag;
        }
        if ($testAlias != $alias) {
            $alias = $testAlias;
        }
        $entity->setAlias($alias);

        $event = $this->dispatchEvent('pre_save', $entity, $isNew);
        $repo->saveEntity($entity);
        $this->dispatchEvent('post_save', $entity, $isNew, $event);
    }

    /**
     * {@inheritdoc}
     *
     * @param       $entity
     * @param       $formFactory
     * @param null  $action
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof LeadList) {
            throw new MethodNotAllowedHttpException(['LeadList'], 'Entity must be of class LeadList()');
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('leadlist', $entity, $options);
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return null|object
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            return new LeadList();
        }

        $entity = parent::getEntity($id);

        return $entity;
    }

    /**
     * {@inheritdoc}
     *
     * @param $action
     * @param $event
     * @param $entity
     * @param $isNew
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof LeadList) {
            throw new MethodNotAllowedHttpException(['LeadList'], 'Entity must be of class LeadList()');
        }

        switch ($action) {
            case 'pre_save':
                $name = LeadEvents::LIST_PRE_SAVE;
                break;
            case 'post_save':
                $name = LeadEvents::LIST_POST_SAVE;
                break;
            case 'pre_delete':
                $name = LeadEvents::LIST_PRE_DELETE;
                break;
            case 'post_delete':
                $name = LeadEvents::LIST_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new LeadListEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }
            $this->dispatcher->dispatch($name, $event);

            return $event;
        } else {
            return null;
        }
    }

    /**
     * Get a list of field choices for filters.
     *
     * @return array
     */
    public function getChoiceFields()
    {
        //field choices
        $choices=[];

        // Add custom choices
        /*if ($this->dispatcher->hasListeners(LeadEvents::LIST_FILTERS_CHOICES_ON_GENERATE)) {
            $event = new LeadListFiltersChoicesEvent($choices, $this->getOperatorsForFieldType(), $this->translator);
            $this->dispatcher->dispatch(LeadEvents::LIST_FILTERS_CHOICES_ON_GENERATE, $event);
            $choices = $event->getChoices();
        }*/
        $filter['force']=[
            ['column' => 'f.isPublished', 'expr' => 'eq', 'value' => '1'],
            ['column' => 'f.isListable', 'expr' => 'eq', 'value' => '1'],
        ];
        if (!$this->security->isAdmin()) {
            $filter['force'][]= ['column' => 'f.object', 'expr' => 'neq', 'value' => 'company'];
            $filter['force'][]= ['column' => 'f.alias', 'expr' => 'notIn', 'value' => ['company']];
        }
        $choices['list_listoptin'] = [
            'listoptin' => [
                'label'      => $this->translator->trans('le.lead.list.optin.filter.lists'),
                'properties' => [
                    'type' => 'listoptin',
                ],
                'operators' => $this->getOperatorsForFieldType('multiselect'),
                'object'    => 'lead',
            ],
        ];
        $choices['list_leadlist'] = [
            'leadlist' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lists'),
                'properties' => [
                    'type' => 'leadlist',
                ],
                'operators' => $this->getOperatorsForFieldType('multiselect'),
                'object'    => 'lead',
            ],
        ];
        $choices['list_tags'] = [
            'tags' => [
                'label'      => $this->translator->trans('le.lead.list.filter.tags'),
                'properties' => [
                    'type' => 'tags',
                ],
                'operators' => $this->getOperatorsForFieldType('multiselect'),
                'object'    => 'lead',
            ],
        ];
        $choices['list_points'] = [
            'points' => [
                'label'      => $this->translator->trans('le.lead.lead.event.points'),
                'properties' => ['type' => 'number'],
                'operators'  => $this->getOperatorsForFieldType('number'),
                'object'     => 'lead',
            ],
            'lead_score' => [
                'label'      => $this->translator->trans('le.lead.list.event.score'),
                'properties' => [
                    'type' => 'score_list',
                ],
                'operators'  => $this->getOperatorsForFieldType('score_type'),
                'object'     => 'lead',
            ],
        ];
        $choices['date_activity'] =[
            /*'date_added' => [
                'label'      => $this->translator->trans('mautic.core.date.added'),
                'properties' => ['type' => 'date'],
                'operators'  => $this->getOperatorsForFieldType('default'),
                'object'     => 'lead',
            ],*/
            'date_identified' => [
                'label'      => $this->translator->trans('le.lead.list.filter.date_identified'),
                'properties' => ['type' => 'datetime'],
                'operators'  => $this->getOperatorsForFieldType('custmdate'),
                'object'     => 'lead',
            ],
            'last_active' => [
                'label'      => $this->translator->trans('le.lead.list.filter.last_active'),
                'properties' => ['type' => 'datetime'],
                'operators'  => $this->getOperatorsForFieldType('custmdate'),
                'object'     => 'lead',
            ],
            'date_modified' => [
                'label'      => $this->translator->trans('le.lead.list.filter.date_modified'),
                'properties' => ['type' => 'datetime'],
                'operators'  => $this->getOperatorsForFieldType('custmdate'),
                'object'     => 'lead',
            ],
        ];

        if ($this->security->isAdmin()) {
            $choices['list_categories'] = [
                'globalcategory' => [
                    'label'      => $this->translator->trans('le.lead.list.filter.categories'),
                    'properties' => [
                        'type' => 'globalcategory',
                    ],
                    'operators' => $this->getOperatorsForFieldType('multiselect'),
                    'object'    => 'lead',
                ],
            ];
        }
        $choices['emails']= [
            'lead_email_activity' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lead_email_activity'),
                'properties' => [
                    'type' => 'boolean',
                    'list' => $this->getEmailActivityCounts(),
                ],
                'operators'  => $this->getOperatorsForFieldType('emailactivity'),
                'object'     => 'lead',
            ],
            'lead_email_read_count' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lead_email_read_count'),
                'properties' => ['type' => 'number'],
                'operators'  => $this->getOperatorsForFieldType('number'),
                'object'     => 'lead',
            ],
            'lead_email_read_date' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lead_email_read_date'),
                'properties' => ['type' => 'date'],
                'operators'  => $this->getOperatorsForFieldType('custmdate'),
                'object'     => 'lead',
            ],
            'lead_email_click_count' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lead_email_click_count'),
                'properties' => ['type' => 'number'],
                'operators'  => $this->getOperatorsForFieldType('number'),
                'object'     => 'lead',
            ],
            'lead_email_click_date' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lead_email_click_date'),
                'properties' => ['type' => 'date'],
                'operators'  => $this->getOperatorsForFieldType('custmdate'),
                'object'     => 'lead',
            ],
        ];
        if ($this->security->isAdmin()) {
            $choices['emails'] = [
                'dnc_bounced' => [
                    'label'      => $this->translator->trans('le.lead.list.filter.dnc_bounced'),
                    'properties' => [
                        'type' => 'boolean',
                        'list' => [
                            0 => $this->translator->trans('mautic.core.form.no'),
                            1 => $this->translator->trans('mautic.core.form.yes'),
                        ],
                    ],
                    'operators' => $this->getOperatorsForFieldType('email_dnc'),
                    'object'    => 'lead',
                ],
                'dnc_unsubscribed' => [
                    'label'      => $this->translator->trans('le.lead.list.filter.dnc_unsubscribed'),
                    'properties' => [
                        'type' => 'boolean',
                        'list' => [
                            0 => $this->translator->trans('mautic.core.form.no'),
                            1 => $this->translator->trans('mautic.core.form.yes'),
                        ],
                    ],
                    'operators' => $this->getOperatorsForFieldType('email_dnc'),
                    'object'    => 'lead',
                ],
            ];
        }
        $choices['one_of_campaign']= [
            'lead_email_sent' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lead_email_sent'),
                'properties' => ['type' => 'lead_email_received'],
                'operators'  => $this->getOperatorsForFieldType('selecttemplate'),
                'object'     => 'lead',
            ],
            'lead_email_received' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lead_email_received'),
                'properties' => [
                    'type' => 'lead_email_received',
                ],
                'operators' => $this->getOperatorsForFieldType('selecttemplate'),
                'object'    => 'lead',
            ],
            'lead_email_click' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lead_email_click'),
                'properties' => ['type' => 'lead_email_received'],
                'operators'  => $this->getOperatorsForFieldType('selecttemplate'),
                'object'     => 'lead',
            ],
            'lead_email_failed' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lead_email_failed'),
                'properties' => ['type' => 'lead_email_received'],
                'operators'  => $this->getOperatorsForFieldType('selecttemplate'),
                'object'     => 'lead',
            ],
        ];

        $choices['drip_campaign']= [
            'drip_email_subscribed' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lead_drip_campaign'),
                'properties' => ['type' => 'drip_email_list'],
                'operators'  => $this->getOperatorsForFieldType('selecttemplate'),
                'object'     => 'lead',
            ],
            'drip_email_completed' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lead_drip_campaign_completed'),
                'properties' => ['type' => 'drip_email_list'],
                'operators'  => $this->getOperatorsForFieldType('selecttemplate'),
                'object'     => 'lead',
            ],
            'drip_email_sent' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lead_drip_email_sent'),
                'properties' => ['type' => 'drip_email_received'],
                'operators'  => $this->getOperatorsForFieldType('selecttemplate'),
                'object'     => 'lead',
            ],
            'drip_email_read' => [
                'label'      => $this->translator->trans('le.lead.list.filter.drip_email_read'),
                'properties' => [
                    'type' => 'drip_email_received',
                ],
                'operators' => $this->getOperatorsForFieldType('selecttemplate'),
                'object'    => 'lead',
            ],
            'drip_email_click' => [
                'label'      => $this->translator->trans('le.lead.list.filter.drip_email_click'),
                'properties' => ['type' => 'drip_email_received'],
                'operators'  => $this->getOperatorsForFieldType('selecttemplate'),
                'object'     => 'lead',
            ],
            'drip_email_failed' => [
                'label'      => $this->translator->trans('le.lead.list.filter.drip_email_failed'),
                'properties' => ['type' => 'drip_email_received'],
                'operators'  => $this->getOperatorsForFieldType('selecttemplate'),
                'object'     => 'lead',
            ],
        ];

        $choices['pages']= [
            'hit_url' => [
                'label'      => $this->translator->trans('le.lead.list.filter.visited_url'),
                'properties' => [
                    'type' => 'text',
                ],
                'operators' => $this->getOperatorsForFieldType('pages'),
                'object'    => 'lead',
            ],
            'hit_url_count' => [
                'label'      => $this->translator->trans('le.lead.list.filter.visited_url_count'),
                'properties' => ['type' => 'number'],
                'operators'  => $this->getOperatorsForFieldType('number'),
                'object'     => 'lead',
            ],
            'hit_url_date' => [
                'label'      => $this->translator->trans('le.lead.list.filter.visited_url_date'),
                'properties' => ['type' => 'date'],
                'operators'  => $this->getOperatorsForFieldType('custmdate'),
                'object'     => 'lead',
            ],
        ];
        $choices['forms']= [
            'lead_form_submit' => [
                'label'      => $this->translator->trans('le.lead.list.filter.lead_form_submit'),
                'properties' => [
                    'type' => 'formsubmit_list',
                ],
                'operators'  => $this->getOperatorsForFieldType('landingpage_list'),
                'object'     => 'lead',
            ],
        ];
        $choices['assets']= [
            'asset_downloads' => [
                'label'      => $this->translator->trans('mautic.asset.asset.submitaction.downloadfile'),
                'properties' => [
                    'type' => 'asset_downloads_list',
                ],
                'operators'  => $this->getOperatorsForFieldType('landingpage_list'),
                'object'     => 'lead',
            ],
        ];

        //get list of custom fields
        $fields = $this->em->getRepository('MauticLeadBundle:LeadField')->getEntities(
            [
                'filter' => $filter,

                'orderBy' => 'f.order',
            ]
        );
        foreach ($fields as $field) {
            if ($field->getAlias() == 'points' || $field->getAlias() == 'score') {
                continue;
            }
            $type               = $field->getType();
            $properties         = $field->getProperties();
            $properties['type'] = $type;
            if (in_array($type, ['lookup', 'multiselect', 'boolean'])) {
                if ($type == 'boolean') {
                    //create a lookup list with ID
                    $properties['list'] = [
                        0 => $properties['no'],
                        1 => $properties['yes'],
                    ];
                } else {
                    $properties['callback'] = 'activateLeadFieldTypeahead';
                    $properties['list']     = (isset($properties['list'])) ? FormFieldHelper::formatList(
                        FormFieldHelper::FORMAT_ARRAY,
                        FormFieldHelper::parseList($properties['list'])
                    ) : '';
                }
            }
            $choices[$field->getObject()][$field->getAlias()] = [
                'label'      => $field->getLabel(),
                'properties' => $properties,
                'object'     => $field->getObject(),
            ];

            $choices[$field->getObject()][$field->getAlias()]['operators'] = $this->getOperatorsForFieldType($type);
        }

        $choices['lead']['owner_id'] = [
                'label'      => $this->translator->trans('le.lead.list.filter.owner'),
                'properties' => [
                    'type'     => 'owner_id',
                    'callback' => 'activateSegmentFilterTypeahead1',
                ],
                'operators' => $this->getOperatorsForFieldType('owner_id'),
                'object'    => 'lead',
            ];
        //Check if onesignal plugin is published or not
        $integration = $this->integrationHelper->getIntegrationObject('OneSignal');
        if ($integration->getIntegrationSettings()->getIsPublished()) {
            $choices['lead']['notification'] = [
                'label'      => $this->translator->trans('le.lead.list.filter.notification'),
                'properties' => [
                    'type' => 'boolean',
                    'list' => [
                        0 => $this->translator->trans('mautic.core.form.no'),
                        1 => $this->translator->trans('mautic.core.form.yes'),
                    ],
                ],
                'operators' => $this->getOperatorsForFieldType('bool'),
                'object'    => 'lead',
            ];
        }

        //Device Filter Hided not in used as of now
        /*  $choices['device_activity'] =[
              'device_type' => [
                  'label'      => $this->translator->trans('le.lead.list.filter.device_type'),
                  'properties' => [
                      'type' => 'device_type',
                  ],
                  'operators' => $this->getOperatorsForFieldType('multiselect'),
                  'object'    => 'lead',
              ],
              'device_brand' => [
                  'label'      => $this->translator->trans('le.lead.list.filter.device_brand'),
                  'properties' => [
                      'type' => 'device_brand',
                  ],
                  'operators' => $this->getOperatorsForFieldType('multiselect'),
                  'object'    => 'lead',
              ],
              'device_os' => [
                  'label'      => $this->translator->trans('le.lead.list.filter.device_os'),
                  'properties' => [
                      'type' => 'device_os',
                  ],
                  'operators' => $this->getOperatorsForFieldType('multiselect'),
                  'object'    => 'lead',
              ],
              'device_model' => [
                  'label'      => $this->translator->trans('le.lead.list.filter.device_model'),
                  'properties' => [
                      'type' => 'text',
                  ],
                  'operators' => $this->getOperatorsForFieldType(
                      [
                          'include' => [
                              '=',
                              'like',
                              'regexp',
                          ],
                      ]
                  ),
                  'object' => 'lead',
              ],
          ];*/
        if ($this->security->isAdmin()) {
            $choices['pages'] = [
                'url_title' => [
                    'label'      => $this->translator->trans('le.lead.list.filter.url_title'),
                    'properties' => [
                        'type' => 'landingpage_list',
                    ],
                    'operators' => $this->getOperatorsForFieldType('landingpage_list'),
                    'object'    => 'lead',
                ],
                'sessions' => [
                    'label'      => $this->translator->trans('le.lead.list.filter.session'),
                    'properties' => ['type' => 'number'],
                    'operators'  => $this->getOperatorsForFieldType('number'),
                    'object'     => 'lead',
                ],
                'source' => [
                    'label'      => $this->translator->trans('le.lead.list.filter.source'),
                    'properties' => [
                        'type' => 'text',
                    ],
                    'operators' => $this->getOperatorsForFieldType('pages'),
                    'object'    => 'lead',
                ],
                'source_id' => [
                    'label'      => $this->translator->trans('le.lead.list.filter.source.id'),
                    'properties' => [
                        'type' => 'number',
                    ],
                    'operators'  => $this->getOperatorsForFieldType('number'),
                    'object'     => 'lead',
                ],
                'referer' => [
                    'label'      => $this->translator->trans('le.lead.list.filter.referer'),
                    'properties' => [
                        'type' => 'text',
                    ],
                    'operators' => $this->getOperatorsForFieldType('pages'),
                    'object'    => 'lead',
                ],
            ];
            $choices['lead']['page_id']= [
                'label'      => $this->translator->trans('le.lead.list.filter.page_id'),
                'properties' => [
                    'type' => 'boolean',
                    'list' => [
                        0 => $this->translator->trans('mautic.core.form.no'),
                        1 => $this->translator->trans('mautic.core.form.yes'),
                    ],
                ],
                'operators' => $this->getOperatorsForFieldType('bool'),
                'object'    => 'lead',
            ];
            $choices['lead']['email_id']=[
                'label'      => $this->translator->trans('le.lead.list.filter.email_id'),
                'properties' => [
                    'type' => 'boolean',
                    'list' => [
                        0 => $this->translator->trans('mautic.core.form.no'),
                        1 => $this->translator->trans('mautic.core.form.yes'),
                    ],
                ],
                'operators' => $this->getOperatorsForFieldType('bool'),
                'object'    => 'lead',
            ];
            $choices['lead']['redirect_id']=[
                'label'      => $this->translator->trans('le.lead.list.filter.redirect_id'),
                'properties' => [
                    'type' => 'boolean',
                    'list' => [
                        0 => $this->translator->trans('mautic.core.form.no'),
                        1 => $this->translator->trans('mautic.core.form.yes'),
                    ],
                ],
                'operators' => $this->getOperatorsForFieldType('bool'),
                'object'    => 'lead',
            ];
            $choices['lead']['stage'] = [
                'label'      => $this->translator->trans('le.lead.lead.field.stage'),
                'properties' => [
                    'type' => 'stage',
                ],
                'operators' => $this->getOperatorsForFieldType(
                    [
                        'include' => [
                            '=',
                            '!=',
                            'empty',
                            '!empty',
                        ],
                    ]
                ),
                'object' => 'lead',
            ];
            $choices['lead']['dnc_bounced_sms'] = [
                'label'      => $this->translator->trans('mautic.lead.list.filter.dnc_bounced_sms'),
                'properties' => [
                    'type' => 'boolean',
                    'list' => [
                        0 => $this->translator->trans('mautic.core.form.no'),
                        1 => $this->translator->trans('mautic.core.form.yes'),
                    ],
                ],
                'operators' => $this->getOperatorsForFieldType('bool'),
                'object'    => 'lead',
            ];
            $choices['lead']['dnc_unsubscribed_sms'] = [
                'label'      => $this->translator->trans('mautic.lead.list.filter.dnc_unsubscribed_sms'),
                'properties' => [
                    'type' => 'boolean',
                    'list' => [
                        0 => $this->translator->trans('mautic.core.form.no'),
                        1 => $this->translator->trans('mautic.core.form.yes'),
                    ],
                ],
                'operators' => $this->getOperatorsForFieldType('bool'),
                'object'    => 'lead',
            ];
        }
        foreach ($choices as $key => $choice) {
            $cmp = function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            };
            // uasort($choice, $cmp);
            $choices[$key] = $choice;
        }

        return $choices;
    }

    /**
     * @param string $alias
     *
     * @return array
     */
    public function getUserLists($alias = '')
    {
        $user = (!$this->security->isGranted('lead:lists:viewother')) ?
            $this->userHelper->getUser() : false;
        $leadlistrepo=$this->em->getRepository('MauticLeadBundle:LeadList');
        $leadlistrepo->setCurrentUser($this->userHelper->getUser());
        $lists = $leadlistrepo->getLists($user, $alias);

        return $lists;
    }

    public function getEmailActivityCounts()
    {
        return $list = [
            '1'  => 1,
            '2'  => 2,
            '3'  => 3,
            '4'  => 4,
            '5'  => 5,
            '6'  => 6,
            '7'  => 7,
            '8'  => 8,
            '9'  => 9,
            '10' => 10,
            '11' => 11,
            '12' => 12,
            '13' => 13,
            '14' => 14,
            '15' => 15,
            '16' => 16,
            '17' => 17,
            '18' => 18,
            '19' => 19,
            '20' => 20,
        ];
    }

    /**
     * Get a list of global lead lists.
     *
     * @return mixed
     */
    public function getGlobalLists()
    {
        $leadlistrepo=$this->em->getRepository('MauticLeadBundle:LeadList');
        $leadlistrepo->setCurrentUser($this->userHelper->getUser());
        $lists = $leadlistrepo->getGlobalLists();

        return $lists;
    }

    /**
     * Rebuild lead lists.
     *
     * @param LeadList        $entity
     * @param int             $limit
     * @param bool            $maxLeads
     * @param OutputInterface $output
     *
     * @return int
     */
    public function rebuildListLeads(LeadList $entity, $limit = 1000, $maxLeads = false, OutputInterface $output = null)
    {
        defined('MAUTIC_REBUILDING_LEAD_LISTS') or define('MAUTIC_REBUILDING_LEAD_LISTS', 1);

        $id       = $entity->getId();
        $list     = ['id' => $id, 'filters' => $entity->getFilters()];
        $dtHelper = new DateTimeHelper();

        $batchLimiters = [
            'dateTime' => $dtHelper->toUtcString(),
        ];

        $localDateTime = $dtHelper->getLocalDateTime();

        $this->dispatcher->dispatch(
            LeadEvents::LIST_PRE_PROCESS_LIST,
            new ListPreProcessListEvent($list, false)
        );

        // Get a count of leads to add
        $newLeadsCount = $this->getLeadsByList(
            $list,
            true,
            [
                'countOnly'      => true,
                'newOnly'        => true,
                'batchLimiters'  => $batchLimiters,
                'removeAction'   => false,
            ]
        );

        // Ensure the same list is used each batch
        $batchLimiters['maxId'] = (int) $newLeadsCount[$id]['maxId'];

        // Number of total leads to process
        $leadCount = (int) $newLeadsCount[$id]['count'];

        if ($output) {
            $output->writeln($this->translator->trans('le.lead.list.rebuild.to_be_added', ['%leads%' => $leadCount, '%batch%' => $limit]));
        }

        // Handle by batches
        $start = $lastRoundPercentage = $leadsProcessed = 0;

        // Try to save some memory
        gc_enable();

        if ($leadCount) {
            $maxCount = ($maxLeads) ? $maxLeads : $leadCount;

            if ($output) {
                $progress = ProgressBarHelper::init($output, $maxCount);
                $progress->start();
            }

            // Add leads
            while ($start < $leadCount) {
                //skip if a segment not exist
                $segment = $this->getEntity($entity->getId());
                if ($segment == null) {
                    break;
                }
                // Keep CPU down for large lists; sleep per $limit batch
                $this->batchSleep();

                $newLeadList = $this->getLeadsByList(
                    $list,
                    true,
                    [
                        'newOnly' => true,
                        // No start set because of newOnly thus always at 0
                        'limit'          => $limit,
                        'batchLimiters'  => $batchLimiters,
                        'removeAction'   => false,
                    ]
                );

                if (empty($newLeadList[$id])) {
                    // Somehow ran out of leads so break out
                    break;
                }
                $list_id=false;
                if (isset($newLeadList[$id]['list_id'])) {
                    $list_id = $newLeadList[$id]['list_id'];
                    unset($newLeadList[$id]['list_id']);
                }

                $processedLeads = [];
                foreach ($newLeadList[$id] as $l) {
                    //skip if a segment not exist
                    $listEntity = $this->getEntity($entity->getId());
                    if ($listEntity == null) {
                        break;
                    }

                    if ($list_id) {
                        $already_not_inserted = $this->getRepository()->isAlreadyInserted($l, $list_id);
                        if ($already_not_inserted) {
                            $this->addLead($l, $entity, false, true, -1, $localDateTime, $output);
                            $processedLeads[] = $l;
                            unset($l);

                            ++$leadsProcessed;
                            if ($output && $leadsProcessed < $maxCount) {
                                $progress->setProgress($leadsProcessed);
                            }

                            if ($maxLeads && $leadsProcessed >= $maxLeads) {
                                break;
                            }
                        }
                    } else {
                        $this->addLead($l, $entity, false, true, -1, $localDateTime, $output);
                        $processedLeads[] = $l;
                        unset($l);

                        ++$leadsProcessed;
                        if ($output && $leadsProcessed < $maxCount) {
                            $progress->setProgress($leadsProcessed);
                        }

                        if ($maxLeads && $leadsProcessed >= $maxLeads) {
                            break;
                        }
                    }
                }
                $start += $limit;

                // Dispatch batch event
                if (count($processedLeads) && $this->dispatcher->hasListeners(LeadEvents::LEAD_LIST_BATCH_CHANGE)) {
                    $this->dispatcher->dispatch(
                        LeadEvents::LEAD_LIST_BATCH_CHANGE,
                        new ListChangeEvent($processedLeads, $entity, true)
                    );
                    $processed = [];
                    foreach ($processedLeads as $key => $leadId) {
                        $lead        = $this->factory->getModel('lead')->getEntity($leadId);
                        $processed[] =  $lead;
                    }
                    $this->dispatcher->dispatch(
                        LeadEvents::LEAD_LIST_ADD,
                        new ListChangeEvent($processed, $entity, true)
                    );
                }

                unset($newLeadList);

                // Free some memory
                gc_collect_cycles();

                if ($maxLeads && $leadsProcessed >= $maxLeads) {
                    if ($output) {
                        $progress->finish();
                        $output->writeln('');
                    }

                    return $leadsProcessed;
                }
            }

            if ($output) {
                $progress->finish();
                $output->writeln('');
            }
        }

        // Unset max ID to prevent capping at newly added max ID
        unset($batchLimiters['maxId']);

        // Get a count of leads to be removed
        $removeLeadCount = $this->getLeadsByList(
            $list,
            true,
            [
                'countOnly'       => true,
                'nonMembersOnly'  => true,
                'batchLimiters'   => $batchLimiters,
                'removeAction'    => true,
            ]
        );

        // Ensure the same list is used each batch
        $batchLimiters['maxId'] = (int) $removeLeadCount[$id]['maxId'];

        // Restart batching
        $start     = $lastRoundPercentage     = 0;
        $leadCount = $removeLeadCount[$id]['count'];

        if ($output) {
            $output->writeln($this->translator->trans('le.lead.list.rebuild.to_be_removed', ['%leads%' => $leadCount, '%batch%' => $limit]));
        }

        if ($leadCount) {
            $maxCount = ($maxLeads) ? $maxLeads : $leadCount;

            if ($output) {
                $progress = ProgressBarHelper::init($output, $maxCount);
                $progress->start();
            }

            // Remove leads
            while ($start < $leadCount) {
                //skip if a segment not exist
                $segment = $this->getEntity($entity->getId());
                if ($segment == null) {
                    break;
                }

                // Keep CPU down for large lists; sleep per $limit batch
                $this->batchSleep();

                $removeLeadList = $this->getLeadsByList(
                    $list,
                    true,
                    [
                        // No start because the items are deleted so always 0
                        'limit'           => $limit,
                        'nonMembersOnly'  => true,
                        'batchLimiters'   => $batchLimiters,
                        'removeAction'    => true,
                    ]
                );

                if (empty($removeLeadList[$id])) {
                    // Somehow ran out of leads so break out
                    break;
                }

                $processedLeads = [];
                foreach ($removeLeadList[$id] as $l) {
                    //skip if a segment not exist
                    $segment = $this->getEntity($entity->getId());
                    if ($segment == null) {
                        break;
                    }

                    $this->removeLead($l, $entity, false, true, true, $output);
                    $processedLeads[] = $l;
                    ++$leadsProcessed;
                    if ($output && $leadsProcessed < $maxCount) {
                        $progress->setProgress($leadsProcessed);
                    }

                    if ($maxLeads && $leadsProcessed >= $maxLeads) {
                        break;
                    }
                }

                // Dispatch batch event
                if (count($processedLeads) && $this->dispatcher->hasListeners(LeadEvents::LEAD_LIST_BATCH_CHANGE)) {
                    $this->dispatcher->dispatch(
                        LeadEvents::LEAD_LIST_BATCH_CHANGE,
                        new ListChangeEvent($processedLeads, $entity, false)
                    );
                }

                $start += $limit;

                unset($removeLeadList);

                // Free some memory
                gc_collect_cycles();

                if ($maxLeads && $leadsProcessed >= $maxLeads) {
                    if ($output) {
                        $progress->finish();
                        $output->writeln('');
                    }

                    return $leadsProcessed;
                }
            }

            if ($output) {
                $progress->finish();
                $output->writeln('');
            }
        }

        return $leadsProcessed;
    }

    /**
     * Add lead to lists.
     *
     * @param array|Lead     $lead
     * @param array|LeadList $lists
     * @param bool           $manuallyAdded
     * @param bool           $batchProcess
     * @param int            $searchListLead  0 = reference, 1 = yes, -1 = known to not exist
     * @param \DateTime      $dateManipulated
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function addLead($lead, $lists, $manuallyAdded = false, $batchProcess = false, $searchListLead = 1, $dateManipulated = null, $output=null)
    {
        if ($dateManipulated == null) {
            $dateManipulated = new \DateTime();
        }

        if (!$lead instanceof Lead) {
            $leadId = (is_array($lead) && isset($lead['id'])) ? $lead['id'] : $lead;
            $lead   = $this->em->getReference('MauticLeadBundle:Lead', $leadId);
        } else {
            $leadId = $lead->getId();
        }

        if (!$lists instanceof LeadList) {
            //make sure they are ints
            $searchForLists = [];
            foreach ($lists as $k => &$l) {
                $l = (int) $l;
                if (!isset($this->leadChangeLists[$l])) {
                    $searchForLists[] = $l;
                }
            }

            if (!empty($searchForLists)) {
                $listEntities = $this->getEntities([
                    'filter' => [
                        'force' => [
                            [
                                'column' => 'l.id',
                                'expr'   => 'in',
                                'value'  => $searchForLists,
                            ],
                        ],
                    ],
                ]);

                foreach ($listEntities as $list) {
                    $this->leadChangeLists[$list->getId()] = $list;
                }
            }

            unset($listEntities, $searchForLists);
        } else {
            $this->leadChangeLists[$lists->getId()] = $lists;

            $lists = [$lists->getId()];
        }

        if (!is_array($lists)) {
            $lists = [$lists];
        }

        $persistLists   = [];
        $dispatchEvents = [];

        foreach ($lists as $listId) {
            if (!isset($this->leadChangeLists[$listId])) {
                // List no longer exists in the DB so continue to the next
                continue;
            }

            //Skip if a segment not exisits
            $listentity =$this->getEntity($listId);
            if ($listentity == null) {
                continue;
            }

            if ($searchListLead == -1) {
                $listLead = null;
            } elseif ($searchListLead) {
                $listLead = $this->getListLeadRepository()->findOneBy(
                    [
                        'lead' => $lead,
                        'list' => $this->leadChangeLists[$listId],
                    ]
                );
            } else {
                $listLead = $this->em->getReference('MauticLeadBundle:ListLead',
                    [
                        'lead' => $leadId,
                        'list' => $listId,
                    ]
                );
            }

            if ($listLead != null) {
                if ($manuallyAdded && $listLead->wasManuallyRemoved()) {
                    $listLead->setManuallyRemoved(false);
                    $listLead->setManuallyAdded($manuallyAdded);

                    $persistLists[]   = $listLead;
                    $dispatchEvents[] = $listId;
                } else {
                    // Detach from Doctrine
                    $this->em->detach($listLead);

                    continue;
                }
            } else {
                $listLead = new ListLead();
                $listLead->setList($this->leadChangeLists[$listId]);
                $listLead->setLead($lead);
                $listLead->setManuallyAdded($manuallyAdded);
                $listLead->setDateAdded($dateManipulated);

                $persistLists[]   = $listLead;
                $dispatchEvents[] = $listId;
            }
        }
        try {
            if (!empty($persistLists)) {
                $this->getRepository()->saveEntities($persistLists);
            }

            // Clear ListLead entities from Doctrine memory
            $this->em->clear('Mautic\LeadBundle\Entity\ListLead');

            if ($batchProcess) {
                // Detach for batch processing to preserve memory
                $this->em->detach($lead);
            } elseif (!empty($dispatchEvents) && ($this->dispatcher->hasListeners(LeadEvents::LEAD_LIST_CHANGE))) {
                foreach ($dispatchEvents as $listId) {
                    $event = new ListChangeEvent($lead, $this->leadChangeLists[$listId]);
                    $this->dispatcher->dispatch(LeadEvents::LEAD_LIST_CHANGE, $event);
                    $this->dispatcher->dispatch(LeadEvents::LEAD_LIST_ADD, $event);

                    unset($event);
                }
            }
            unset($lead, $persistLists, $lists);
        } catch (\Exception $e) {
            if ($output != null) {
                $output->writeln('<info>'.'Exception Occured:'.$e->getMessage().'</info>');
            }
        }
    }

    /**
     * Remove a lead from lists.
     *
     * @param      $lead
     * @param      $lists
     * @param bool $manuallyRemoved
     * @param bool $batchProcess
     * @param bool $skipFindOne
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function removeLead($lead, $lists, $manuallyRemoved = false, $batchProcess = false, $skipFindOne = false, $output=null)
    {
        if (!$lead instanceof Lead) {
            $leadId = (is_array($lead) && isset($lead['id'])) ? $lead['id'] : $lead;
            $lead   = $this->em->getReference('MauticLeadBundle:Lead', $leadId);
        } else {
            $leadId = $lead->getId();
        }

        if (!$lists instanceof LeadList) {
            //make sure they are ints
            $searchForLists = [];
            foreach ($lists as $k => &$l) {
                $l = (int) $l;
                if (!isset($this->leadChangeLists[$l])) {
                    $searchForLists[] = $l;
                }
            }

            if (!empty($searchForLists)) {
                $listEntities = $this->getEntities([
                    'filter' => [
                        'force' => [
                            [
                                'column' => 'l.id',
                                'expr'   => 'in',
                                'value'  => $searchForLists,
                            ],
                        ],
                    ],
                ]);

                foreach ($listEntities as $list) {
                    $this->leadChangeLists[$list->getId()] = $list;
                }
            }

            unset($listEntities, $searchForLists);
        } else {
            $this->leadChangeLists[$lists->getId()] = $lists;

            $lists = [$lists->getId()];
        }

        if (!is_array($lists)) {
            $lists = [$lists];
        }

        $persistLists   = [];
        $deleteLists    = [];
        $dispatchEvents = [];

        foreach ($lists as $listId) {
            if (!isset($this->leadChangeLists[$listId])) {
                // List no longer exists in the DB so continue to the next
                continue;
            }

            $listLead = (!$skipFindOne) ?
                $this->getListLeadRepository()->findOneBy([
                    'lead' => $lead,
                    'list' => $this->leadChangeLists[$listId],
                ]) :
                $this->em->getReference('MauticLeadBundle:ListLead', [
                    'lead' => $leadId,
                    'list' => $listId,
                ]);

            if ($listLead == null) {
                // Lead is not part of this list
                continue;
            }

            if (($manuallyRemoved && $listLead->wasManuallyAdded()) || (!$manuallyRemoved && !$listLead->wasManuallyAdded())) {
                //lead was manually added and now manually removed or was not manually added and now being removed
                $deleteLists[]    = $listLead;
                $dispatchEvents[] = $listId;
            } elseif ($manuallyRemoved && !$listLead->wasManuallyAdded()) {
                $listLead->setManuallyRemoved(true);

                $persistLists[]   = $listLead;
                $dispatchEvents[] = $listId;
            }

            unset($listLead);
        }
        try {
            if (!empty($persistLists)) {
                $this->getRepository()->saveEntities($persistLists);
            }

            if (!empty($deleteLists)) {
                $this->getRepository()->deleteEntities($deleteLists);
            }

            // Clear ListLead entities from Doctrine memory
            $this->em->clear('Mautic\LeadBundle\Entity\ListLead');

            if ($batchProcess) {
                // Detach for batch processing to preserve memory
                $this->em->detach($lead);
            } elseif (!empty($dispatchEvents) && ($this->dispatcher->hasListeners(LeadEvents::LEAD_LIST_CHANGE))) {
                foreach ($dispatchEvents as $listId) {
                    $event = new ListChangeEvent($lead, $this->leadChangeLists[$listId], false);
                    $this->dispatcher->dispatch(LeadEvents::LEAD_LIST_CHANGE, $event);

                    unset($event);
                }
            }

            unset($lead, $deleteLists, $persistLists, $lists);
        } catch (\Exception $e) {
            if ($output != null) {
                $output->writeln('<info>'.'Exception Occured:'.$e->getMessage().'</info>');
            }
        }
    }

    /**
     * @param       $lists
     * @param bool  $idOnly
     * @param array $args
     *
     * @return mixed
     */
    public function getLeadsByList($lists, $idOnly = false, $args = [])
    {
        $args['idOnly'] = $idOnly;

        return $this->getRepository()->getLeadsByList($lists, $args);
    }

    /**
     * Batch sleep according to settings.
     */
    protected function batchSleep()
    {
        $leadSleepTime = $this->coreParametersHelper->getParameter('batch_lead_sleep_time', false);
        if ($leadSleepTime === false) {
            $leadSleepTime = $this->coreParametersHelper->getParameter('batch_sleep_time', 1);
        }

        if (empty($leadSleepTime)) {
            return;
        }

        if ($leadSleepTime < 1) {
            usleep($leadSleepTime * 1000000);
        } else {
            sleep($leadSleepTime);
        }
    }

    /**
     * Get a list of top (by leads added) lists.
     *
     * @param int    $limit
     * @param string $dateFrom
     * @param string $dateTo
     * @param array  $filters
     *
     * @return array
     */
    public function getTopLists($limit = 10, $dateFrom = null, $dateTo = null, $filters = [])
    {
        $q = $this->em->getConnection()->createQueryBuilder();
        $q->select('COUNT(t.date_added) AS leads, ll.id, ll.name, ll.alias')
            ->from(MAUTIC_TABLE_PREFIX.'lead_lists_leads', 't')
            ->join('t', MAUTIC_TABLE_PREFIX.'lead_lists', 'll', 'll.id = t.leadlist_id')
            ->orderBy('leads', 'DESC')
            ->where($q->expr()->eq('ll.is_published', ':published'))
            ->setParameter('published', true)
            ->groupBy('ll.id')
            ->setMaxResults($limit);

        if (!empty($options['canViewOthers'])) {
            $q->andWhere('ll.created_by = :userId')
                ->setParameter('userId', $this->userHelper->getUser()->getId());
        }

        $chartQuery = new ChartQuery($this->em->getConnection(), $dateFrom, $dateTo);
        $chartQuery->applyFilters($q, $filters);
        $chartQuery->applyDateFilters($q, 'date_added');

        $results = $q->execute()->fetchAll();

        return $results;
    }

    /**
     * Get a list of top (by leads added) lists.
     *
     * @param int    $limit
     * @param string $dateFrom
     * @param string $dateTo
     * @param array  $filters
     *
     * @return array
     */
    public function getLifeCycleSegments($limit, $dateFrom, $dateTo, $filters, $segments)
    {
        if (!empty($segments)) {
            $segmentlist = "'".implode("','", $segments)."'";
        }
        $q = $this->em->getConnection()->createQueryBuilder();
        $q->select('COUNT(t.date_added) AS leads, ll.id, ll.name as name,ll.alias as alias')
            ->from(MAUTIC_TABLE_PREFIX.'lead_lists_leads', 't')
            ->join('t', MAUTIC_TABLE_PREFIX.'lead_lists', 'll', 'll.id = t.leadlist_id')
            ->join('t', MAUTIC_TABLE_PREFIX.'leads', 'l', 'l.id = t.lead_id')
            ->orderBy('leads', 'DESC')
            ->where($q->expr()->eq('ll.is_published', ':published'))
            ->setParameter('published', true)
            ->groupBy('ll.id');

        if ($limit) {
            $q->setMaxResults($limit);
        }
        if (!empty($segments)) {
            $q->andWhere('ll.id IN ('.$segmentlist.')');
        }
        if (!empty($dateFrom)) {
            $q->andWhere("l.date_added >= '".$dateFrom->format('Y-m-d')."'");
        }
        if (!empty($dateTo)) {
            $q->andWhere("l.date_added <= '".$dateTo->format('Y-m-d')." 23:59:59'");
        }
        if (!empty($options['canViewOthers'])) {
            $q->andWhere('ll.created_by = :userId')
                ->setParameter('userId', $this->userHelper->getUser()->getId());
        }

        $results = $q->execute()->fetchAll();

        if (in_array(0, $segments)) {
            $qAll = $this->em->getConnection()->createQueryBuilder();
            $qAll->select('COUNT(t.date_added) AS leads, 0 as id, "All Contacts" as name, "" as alias')
                ->from(MAUTIC_TABLE_PREFIX.'leads', 't');

            if (!empty($options['canViewOthers'])) {
                $qAll->andWhere('ll.created_by = :userId')
                    ->setParameter('userId', $this->userHelper->getUser()->getId());
            }
            if (!empty($dateFrom)) {
                $qAll->andWhere("t.date_added >= '".$dateFrom->format('Y-m-d')."'");
            }
            if (!empty($dateTo)) {
                $qAll->andWhere("t.date_added <= '".$dateTo->format('Y-m-d')." 23:59:59'");
            }
            $resultsAll = $qAll->execute()->fetchAll();
            $results    = array_merge($results, $resultsAll);
        }

        return $results;
    }

    /**
     * @param           $unit
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param           $dateFormat
     * @param           $filter
     * @param           $canViewOthers
     * @param           $listName
     *
     * @return array
     */
    public function getLifeCycleSegmentChartData($unit, \DateTime $dateFrom, \DateTime $dateTo, $dateFormat, $filter, $canViewOthers, $listName)
    {
        $chart = new PieChart();
        $query = new ChartQuery($this->em->getConnection(), $dateFrom, $dateTo);

        if (!$canViewOthers) {
            $filter['owner_id'] = $this->userHelper->getUser()->getId();
        }

        if (isset($filter['flag'])) {
            unset($filter['flag']);
        }

        $allLists = $query->getCountQuery('leads', 'id', 'date_added', null);

        $lists = $query->count('leads', 'id', 'date_added', $filter, null);

        $all        = $query->fetchCount($allLists);
        $identified = $lists;

        $chart->setDataset($listName, $identified);

        if (isset($filter['leadlist_id']['value'])) {
            $chart->setDataset(
                $this->translator->trans('le.lead.lifecycle.graph.pie.all.lists'),
                $all
            );
        }

        return $chart->render(false);
    }

    /**
     * @param           $unit
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param null      $dateFormat
     * @param array     $filter
     *
     * @return array
     */
    public function getStagesBarChartData($unit, \DateTime $dateFrom, \DateTime $dateTo, $dateFormat = null, $filter = [])
    {
        $data['values'] = [];
        $data['labels'] = [];

        $q = $this->em->getConnection()->createQueryBuilder();

        $q->select('count(l.id) as leads, s.name as stage')
            ->from(MAUTIC_TABLE_PREFIX.'lead_lists_leads', 't')
            ->join('t', MAUTIC_TABLE_PREFIX.'leads', 'l', 'l.id = t.lead_id')
            ->join('t', MAUTIC_TABLE_PREFIX.'stages', 's', 's.id=l.stage_id')
            ->orderBy('leads', 'DESC')
            ->where($q->expr()->eq('s.is_published', ':published'))

            ->andWhere($q->expr()->gte('t.date_added', ':date_from'))
            ->setParameter('date_from', $dateFrom->format('Y-m-d'))
            ->andWhere($q->expr()->lte('t.date_added', ':date_to'))
            ->setParameter('date_to', $dateTo->format('Y-m-d'.' 23:59:59'))
            ->setParameter('published', true);

        if (isset($filter['leadlist_id']['value'])) {
            $q->andWhere($q->expr()->eq('t.leadlist_id', ':leadlistid'))->setParameter('leadlistid', $filter['leadlist_id']['value']);
        }

        $q->groupBy('s.name');

        if (!empty($options['canViewOthers'])) {
            $q->andWhere('s.created_by = :userId')
                ->setParameter('userId', $this->userHelper->getUser()->getId());
        }

        $results = $q->execute()->fetchAll();

        foreach ($results as $result) {
            $percentage       = $result['leads'];
            $data['labels'][] = substr($result['stage'], 0, 12);
            $data['values'][] = $result['leads'];
        }
        $data['xAxes'][] = ['display' => true];
        $data['yAxes'][] = ['display' => true];

        $baseData = [
            'label' => $this->translator->trans('le.lead.leads'),
            'data'  => $data['values'],
        ];

        $chart = new BarChart($data['labels']);

        $datasetId  = count($data['values']);
        $datasets[] = array_merge($baseData, $chart->generateColors(3));

        $chartData = [
            'labels'   => $data['labels'],
            'datasets' => $datasets,
            'options'  => [
                'xAxes' => $data['xAxes'],
                'yAxes' => $data['yAxes'],
            ], ];

        return $chartData;
    }

    /**
     * @param           $unit
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param null      $dateFormat
     * @param array     $filter
     *
     * @return array
     */
    public function getDeviceGranularityData($unit, \DateTime $dateFrom, \DateTime $dateTo, $dateFormat = null, $filter = [])
    {
        $data['values'] = [];
        $data['labels'] = [];

        $q = $this->em->getConnection()->createQueryBuilder();

        $q->select('count(l.id) as leads, ds.device')
            ->from(MAUTIC_TABLE_PREFIX.'lead_lists_leads', 't')
            ->join('t', MAUTIC_TABLE_PREFIX.'leads', 'l', 'l.id = t.lead_id')
            ->join('t', MAUTIC_TABLE_PREFIX.'page_hits', 'h', 'h.lead_id=l.id')
            ->join('h', MAUTIC_TABLE_PREFIX.'lead_devices', 'ds', 'ds.id = h.device_id')
            ->orderBy('ds.device', 'DESC')
            ->andWhere($q->expr()->gte('t.date_added', ':date_from'))
            ->setParameter('date_from', $dateFrom->format('Y-m-d'))
            ->andWhere($q->expr()->lte('t.date_added', ':date_to'))
            ->setParameter('date_to', $dateTo->format('Y-m-d'.' 23:59:59'));

        if (isset($filter['leadlist_id']['value'])) {
            $q->andWhere($q->expr()->eq('t.leadlist_id', ':leadlistid'))->setParameter(
                'leadlistid',
                $filter['leadlist_id']['value']
            );
        }

        $q->groupBy('ds.device');

        if (!empty($options['canViewOthers'])) {
            $q->andWhere('l.created_by = :userId')
                ->setParameter('userId', $this->userHelper->getUser()->getId());
        }

        $results = $q->execute()->fetchAll();

        foreach ($results as $result) {
            $data['labels'][] = substr(empty($result['device']) ? $this->translator->trans('mautic.core.no.info') : $result['device'], 0, 12);
            $data['values'][] = $result['leads'];
        }

        $data['xAxes'][] = ['display' => true];
        $data['yAxes'][] = ['display' => true];

        $baseData = [
            'label' => $this->translator->trans('mautic.core.device'),
            'data'  => $data['values'],
        ];

        $chart = new BarChart($data['labels']);

        $datasets[] = array_merge($baseData, $chart->generateColors(2));

        $chartData = [
            'labels'   => $data['labels'],
            'datasets' => $datasets,
            'options'  => [
                'xAxes' => $data['xAxes'],
                'yAxes' => $data['yAxes'],
            ],
        ];

        return $chartData;
    }

    /**
     * Get line chart data of hits.
     *
     * @param string    $unit       {@link php.net/manual/en/function.date.php#refsect1-function.date-parameters}
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param string    $dateFormat
     * @param array     $filter
     *
     * @return array
     */
    public function getSegmentContactsLineChartData($unit, \DateTime $dateFrom, \DateTime $dateTo, $dateFormat = null, $filter = [])
    {
        $chart    = new LineChart($unit, $dateFrom, $dateTo, $dateFormat);
        $query    = new ChartQuery($this->em->getConnection(), $dateFrom, $dateTo);
        $contacts = $query->fetchTimeData('lead_lists_leads', 'date_added', $filter);
        $chart->setDataset($this->translator->trans('le.lead.segments.contacts'), $contacts);

        return $chart->render();
    }

    public function getSegmentBlocks()
    {
        $totalSegment =  [$this->translator->trans('le.form.display.color.blocks.blue'), 'mdi mdi-group', $this->translator->trans('le.lead.lead.lists.all'),
                       $this->getRepository()->getTotalSegmentCount($viewOthers = $this->factory->get('mautic.security')->isGranted('lead:lists:viewother')),
                       ];
        $activeSegment = [$this->translator->trans('le.form.display.color.blocks.green'), 'mdi mdi-group', $this->translator->trans('le.lead.lead.lists.active'),
                       $this->getRepository()->getTotalActiveSegmentCount($viewOthers = $this->factory->get('mautic.security')->isGranted('lead:lists:viewother')),
                       ];
        $inactiveSegment = [$this->translator->trans('le.form.display.color.blocks.red'), 'mdi mdi-group', $this->translator->trans('le.lead.lead.lists.inactive'),
                       $this->getRepository()->getTotalInactiveSegmentCount($viewOthers = $this->factory->get('mautic.security')->isGranted('lead:lists:viewother')),
                       ];

        $allBlockDetails[] = $totalSegment;
        $allBlockDetails[] = $activeSegment;
        $allBlockDetails[] = $inactiveSegment;

        return $allBlockDetails;
    }
}
