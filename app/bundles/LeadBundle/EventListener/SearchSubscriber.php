<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\EventListener;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Mautic\CampaignBundle\Model\CampaignModel;
use Mautic\ChannelBundle\Entity\MessageQueue;
use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event as MauticEvents;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Entity\EmailRepository;
use Mautic\LeadBundle\Entity\LeadRepository;
use Mautic\LeadBundle\Event\LeadBuildSearchEvent;
use Mautic\LeadBundle\LeadEvents;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Model\ListModel;
use Mautic\LeadBundle\Model\ListOptInModel;
use Mautic\LeadBundle\Model\TagModel;

/**
 * Class SearchSubscriber.
 */
class SearchSubscriber extends CommonSubscriber
{
    /**
     * @var LeadModel
     */
    protected $leadModel;
    /**
     * @var TagModel
     */
    protected $tagModel;
    /**
     * @var ListModel
     */
    protected $listModel;
    /**
     * @var ListOptInModel
     */
    protected $listOptInModel;
    /**
     * @var LeadRepository
     */
    private $leadRepo;

    /**
     * @var EmailRepository
     */
    private $emailRepository;
    /**
     * @var CampaignModel
     */
    protected $CampaignModel;

    /**
     * SearchSubscriber constructor.
     *
     * @param LeadModel      $leadModel
     * @param TagModel       $tagModel
     * @param ListModel      $listModel
     * @param ListOptInModel $listOptInModel
     * @param EntityManager  $entityManager
     * @param CampaignModel  $campaignmodel
     */
    public function __construct(LeadModel $leadModel,TagModel $tagModel,ListModel $listModel,ListOptInModel $listOptInModel, EntityManager $entityManager, CampaignModel $campaignModel)
    {
        $this->leadModel       = $leadModel;
        $this->tagModel        = $tagModel;
        $this->listModel       = $listModel;
        $this->listOptInModel  = $listOptInModel;
        $this->leadRepo        = $leadModel->getRepository();
        $this->emailRepository = $entityManager->getRepository(Email::class);
        $this->CampaignModel   = $campaignModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::GLOBAL_SEARCH              => ['onGlobalSearch', 0],
            CoreEvents::BUILD_COMMAND_LIST         => ['onBuildCommandList', 0],
            LeadEvents::LEAD_BUILD_SEARCH_COMMANDS => ['onBuildSearchCommands', 0],
        ];
    }

    /**
     * @param MauticEvents\GlobalSearchEvent $event
     */
    public function onGlobalSearch(MauticEvents\GlobalSearchEvent $event)
    {
        $str = $event->getSearchString();
        if (empty($str)) {
            return;
        }

        $anonymous = $this->translator->trans('le.lead.lead.searchcommand.isanonymous');
        $mine      = $this->translator->trans('mautic.core.searchcommand.ismine');
        $filter    = ['string' => $str, 'force' => ''];

        //only show results that are not anonymous so as to not clutter up things
        if (strpos($str, "$anonymous") === false) {
            $filter['force'] = " !$anonymous";
        }

        $permissions = $this->security->isGranted(
            [   'lead:leads:viewown',
                'lead:leads:viewother',
                'lead:tags:full',
                'lead:lists:viewother',
                'lead:listoptin:viewown',
                'lead:listoptin:viewother',
            ],
            'RETURN_ARRAY'
        );

        if ($permissions['lead:leads:viewown'] || $permissions['lead:leads:viewother']) {
            //only show own leads if the user does not have permission to view others
            if (!$permissions['lead:leads:viewother']) {
                $filter['force'] .= " $mine";
            }

            $results = $this->leadModel->getEntities(
                [
                    'limit'          => 5,
                    'filter'         => $filter,
                    'withTotalCount' => true,
                ]);

            $count = $results['count'];

            if ($count > 0) {
                $leads       = $results['results'];
                $leadResults = [];

                foreach ($leads as $lead) {
                    $leadResults[] = $this->templating->renderResponse(
                        'MauticLeadBundle:SubscribedEvents\Search:global.html.php',
                        ['lead' => $lead]
                    )->getContent();
                }

                if ($results['count'] > 5) {
                    $leadResults[] = $this->templating->renderResponse(
                        'MauticLeadBundle:SubscribedEvents\Search:global.html.php',
                        [
                            'showMore'     => true,
                            'searchString' => $str,
                            'remaining'    => ($results['count'] - 5),
                        ]
                    )->getContent();
                }
                $leadResults['count'] = $results['count'];
                $event->addResults('le.lead.leads', $leadResults);
            }
        }
        if($permissions['lead:tags:full']){
            $filter = [
                'string' => $str,
                'where'  => [
                    [
                        'expr' => 'like',
                        'col'  => 't.alias',
                        'val'  => '%'.$str.'%',
                    ],
                ],
            ];
            $results = $this->tagModel->getEntities(
                [
                    'filter'         => $filter,
                    'withTotalCount' => true,
                ]);
        foreach ($results as $result){
            $tags[]=$result;
         }

         $count = isset($tags) ? sizeof($tags): 0;

            if ($count > 0) {
                $tagResults = [];
                $itrate=0;
                foreach ($tags as $tag) {
                    if($itrate < 5) {
                        $tagResults[] = $this->templating->renderResponse(
                            'MauticLeadBundle:SubscribedEvents\Search:tagGlobal.html.php',
                            ['tag' => $tag]
                        )->getContent();
                        $itrate++;
                    }
                }

                if ($count > 5) {
                    $tagResults[] = $this->templating->renderResponse(
                        'MauticLeadBundle:SubscribedEvents\Search:tagGlobal.html.php',
                        [
                            'showMore'     => true,
                            'searchString' => $str,
                            'remaining'    => ($count - 5),
                        ]
                    )->getContent();
                }
                $tagResults['count'] = $count;
                $event->addResults('le.lead.tags', $tagResults);
            }
        }
        if($permissions['lead:lists:viewother']){
            $filter = [
                'string' => $str,
                'where'  => [
                    [
                        'expr' => 'like',
                        'col'  => 'l.name',
                        'val'  => '%'.$str.'%',
                    ],
                ],
            ];
            $results = $this->listModel->getEntities(
                [
                    'filter'         => $filter,
                    'withTotalCount' => true,
                ]);
          foreach ($results as $result){
                 $lists[]=$result;
                }

            $count = isset($lists) ? sizeof($lists): 0;

            if ($count > 0) {
                $listResults = [];
                $itrate=0;
                foreach ($lists as $list) {
                    if($itrate < 5) {
                        $listResults[] = $this->templating->renderResponse(
                            'MauticLeadBundle:SubscribedEvents\Search:listGlobal.html.php',
                            ['list' => $list]
                        )->getContent();
                        $itrate++;
                    }
                }

                if ($count > 5) {
                    $listResults[] = $this->templating->renderResponse(
                        'MauticLeadBundle:SubscribedEvents\Search:listGlobal.html.php',
                        [
                            'showMore'     => true,
                            'searchString' => $str,
                            'remaining'    => ($count - 5),
                        ]
                    )->getContent();
                }
                $listResults['count'] = $count;
                $event->addResults('le.lead.form.list', $listResults);
            }
        }
        if($permissions['lead:lists:viewother']){
            $filter = [
                'string' => $str,
                'where'  => [
                    [
                        'expr' => 'like',
                        'col'  => 'l.name',
                        'val'  => '%'.$str.'%',
                    ],
                ],
            ];
            $results = $this->listOptInModel->getEntities(
                [
                    'filter'         => $filter,
                    'withTotalCount' => true,
                ]);
            foreach ($results as $result){
                $lists[]=$result;
            }

            $count = isset($lists) ? sizeof($lists): 0;

            if ($count > 0) {
                $listResults = [];
                $itrate=0;
                foreach ($lists as $list) {
                    if($itrate < 5) {
                        $listResults[] = $this->templating->renderResponse(
                            'MauticLeadBundle:SubscribedEvents\Search:listOptinGlobal.html.php',
                            ['listOptin' => $list]
                        )->getContent();
                        $itrate++;
                    }
                }

                if ($count > 5) {
                    $listResults[] = $this->templating->renderResponse(
                        'MauticLeadBundle:SubscribedEvents\Search:listOptinGlobal.html.php',
                        [
                            'showMore'     => true,
                            'searchString' => $str,
                            'remaining'    => ($count - 5),
                        ]
                    )->getContent();
                }
                $listResults['count'] = $count;
                $event->addResults('le.lead.list.optin', $listResults);
            }
        }
    }

    /**
     * @param MauticEvents\CommandListEvent $event
     */
    public function onBuildCommandList(MauticEvents\CommandListEvent $event)
    {
        if ($this->security->isGranted(['lead:leads:viewown', 'lead:leads:viewother'], 'MATCH_ONE')) {
            $event->addCommands(
                'le.lead.leads',
                $this->leadModel->getCommandList()
            );
        }
    }

    /**
     * @param LeadBuildSearchEvent $event
     *
     * @throws \InvalidArgumentException
     */
    public function onBuildSearchCommands(LeadBuildSearchEvent $event)
    {
        switch ($event->getCommand()) {
            case $this->translator->trans('le.lead.lead.searchcommand.email_read'):
            case $this->translator->trans('le.lead.lead.searchcommand.email_read', [], null, 'en_US'):
                    $this->buildEmailReadQuery($event);
                break;
            case $this->translator->trans('le.lead.lead.searchcommand.email_click'):
            case $this->translator->trans('le.lead.lead.searchcommand.email_click', [], null, 'en_US'):
                    $this->buildEmailClickQuery($event);
                break;
            case $this->translator->trans('le.lead.lead.searchcommand.email_failure'):
            case $this->translator->trans('le.lead.lead.searchcommand.email_failure', [], null, 'en_US'):
                $this->buildEmailFailureQuery($event);
                break;
            case $this->translator->trans('le.lead.lead.searchcommand.email_unsubscribe'):
            case $this->translator->trans('le.lead.lead.searchcommand.email_unsubscribe', [], null, 'en_US'):
                $this->buildEmailUnsubscribeQuery($event);
                break;
            case $this->translator->trans('le.lead.lead.searchcommand.email_bounce'):
            case $this->translator->trans('le.lead.lead.searchcommand.email_bounce', [], null, 'en_US'):
                $this->buildEmailBounceQuery($event);
                break;
            case $this->translator->trans('le.lead.lead.searchcommand.email_sent'):
            case $this->translator->trans('le.lead.lead.searchcommand.email_sent', [], null, 'en_US'):
                    $this->buildEmailSentQuery($event);
                break;
            case $this->translator->trans('le.lead.lead.searchcommand.email_queued'):
            case $this->translator->trans('le.lead.lead.searchcommand.email_queued', [], null, 'en_US'):
                    $this->buildEmailQueuedQuery($event);
                break;
            case $this->translator->trans('le.lead.lead.searchcommand.email_pending'):
            case $this->translator->trans('le.lead.lead.searchcommand.email_pending', [], null, 'en_US'):
                    $this->buildEmailPendingQuery($event);
                break;
            case $this->translator->trans('le.lead.lead.searchcommand.sms_sent'):
            case $this->translator->trans('le.lead.lead.searchcommand.sms_sent', [], null, 'en_US'):
                    $this->buildSmsSentQuery($event);
                break;
            case $this->translator->trans('le.lead.lead.searchcommand.web_sent'):
            case $this->translator->trans('le.lead.lead.searchcommand.web_sent', [], null, 'en_US'):
                    $this->buildWebSentQuery($event);
                break;
            case $this->translator->trans('le.lead.lead.searchcommand.mobile_sent'):
            case $this->translator->trans('le.lead.lead.searchcommand.mobile_sent', [], null, 'en_US'):
                    $this->buildMobileSentQuery($event);
                break;
            case $this->translator->trans('le.lead.campaign.searchcommand.wf-progress'):
            case $this->translator->trans('le.lead.campaign.searchcommand.wf-progress', [], null, 'en_US'):
                    $this->buildWfProgressQuery($event);
                break;
            case $this->translator->trans('le.lead.campaign.searchcommand.wf-completed'):
            case $this->translator->trans('le.lead.campaign.searchcommand.wf-completed', [], null, 'en_US'):
                    $this->buildWfCompletedQuery($event);
                break;
            case $this->translator->trans('le.lead.campaign.searchcommand.wf-goal'):
            case $this->translator->trans('le.lead.campaign.searchcommand.wf-goal', [], null, 'en_US'):
                    $this->buildWfGoalQuery($event);
                break;
            case $this->translator->trans('le.lead.drip.searchcommand.lead'):
            case $this->translator->trans('le.lead.drip.searchcommand.lead', [], null, 'en_US'):
                    $this->buildDripLeadQuery($event);
                break;
            case $this->translator->trans('le.lead.drip.searchcommand.sent'):
            case $this->translator->trans('le.lead.drip.searchcommand.sent', [], null, 'en_US'):
                    $this->buildDripSentQuery($event);
                break;
            case $this->translator->trans('le.lead.drip.searchcommand.read'):
            case $this->translator->trans('le.lead.drip.searchcommand.read', [], null, 'en_US'):
                    $this->buildDripReadQuery($event);
                break;
            case $this->translator->trans('le.lead.drip.searchcommand.click'):
            case $this->translator->trans('le.lead.drip.searchcommand.click', [], null, 'en_US'):
                    $this->buildDripClickQuery($event);
                break;
            case $this->translator->trans('le.lead.drip.searchcommand.unsubscribe'):
            case $this->translator->trans('le.lead.drip.searchcommand.unsubscribe', [], null, 'en_US'):
                    $this->buildDripUnsubscribeQuery($event);
                break;
            case $this->translator->trans('le.lead.lead.searchcommand.drip_scheduled'):
            case $this->translator->trans('le.lead.lead.searchcommand.drip_scheduled', [], null, 'en_US'):
                $this->buildDripScheduledQuery($event);
                break;
            /*case $this->translator->trans('mautic.lead.lead.searchcommand.dripemail_sent'):
            case $this->translator->trans('mautic.lead.lead.searchcommand.dripemail_sent', [], null, 'en_US'):
                $this->buildDripEmailSentQuery($event);
                break;*/
        }
    }

    /**
     * @param LeadBuildSearchEvent $event
     */
    private function buildEmailPendingQuery(LeadBuildSearchEvent $event)
    {
        $q       = $event->getQueryBuilder();
        $emailId = (int) $event->getString();
        /** @var Email $email */
        $email = $this->emailRepository->getEntity($emailId);
        if (null !== $email) {
            $variantIds = $email->getRelatedEntityIds();
            $nq         = $this->emailRepository->getEmailPendingQuery($emailId, $variantIds);
            if (!$nq instanceof QueryBuilder) {
                return;
            }

            $nq->select('l.id'); // select only id
            $nsql = $nq->getSQL();
            foreach ($nq->getParameters() as $pk => $pv) { // replace all parameters
                if (!is_array($pv)) {
                    $nsql = preg_replace('/:'.$pk.'/', is_bool($pv) ? (int) $pv : "'".$pv."'", $nsql);
                } else {
                    $temp    =json_encode($pv);
                    $remov   = ['[', ']'];
                    $replc   = ['', ''];
                    $pv      = str_replace($remov, $replc, $temp);
                    $nsql    = preg_replace('/:'.$pk.'/', $pv, $nsql);
                }
            }
            $query = $q->expr()->in('l.id', sprintf('(%s)', $nsql));
            $event->setSubQuery($query);

            return;
        }

        $tables = [
            [
                'from_alias' => 'l',
                'table'      => 'message_queue',
                'alias'      => 'mq',
                'condition'  => 'l.id = mq.lead_id',
            ],
        ];

        $config = [
            'column' => 'mq.channel_id',
            'params' => [
                'mq.channel' => 'email',
                'mq.status'  => MessageQueue::STATUS_PENDING,
            ],
        ];

        $this->buildJoinQuery($event, $tables, $config);
    }

    /**
     * @param LeadBuildSearchEvent $event
     */
    private function buildEmailQueuedQuery(LeadBuildSearchEvent $event)
    {
        $tables = [
            [
                'from_alias' => 'l',
                'table'      => 'message_queue',
                'alias'      => 'mq',
                'condition'  => 'l.id = mq.lead_id',
            ],
        ];

        $config = [
            'column' => 'mq.channel_id',
            'params' => [
                'mq.channel' => 'email',
                'mq.status'  => MessageQueue::STATUS_PENDING,
            ],
        ];

        $this->buildJoinQuery($event, $tables, $config);
    }

    /**
     * @param LeadBuildSearchEvent $event
     */
    private function buildEmailSentQuery(LeadBuildSearchEvent $event)
    {
        $tables = [
            [
                'from_alias' => 'l',
                'table'      => 'email_stats',
                'alias'      => 'es',
                'condition'  => 'l.id = es.lead_id',
            ],
        ];

        $config = [
            'column' => 'es.email_id',
            'params' => [
                'es.is_failed' => 0,
            ],
        ];

        $this->buildJoinQuery($event, $tables, $config);
    }

    /**
     * @param LeadBuildSearchEvent $event
     */
    private function buildEmailReadQuery(LeadBuildSearchEvent $event)
    {
        $tables = [
            [
                'from_alias' => 'l',
                'table'      => 'email_stats',
                'alias'      => 'es',
                'condition'  => 'l.id = es.lead_id',
            ],
        ];

        $config = [
            'column' => 'es.email_id',
            'params' => [
                'es.is_read' => 1,
            ],
        ];

        $this->buildJoinQuery($event, $tables, $config);
    }

    private function buildEmailClickQuery(LeadBuildSearchEvent $event)
    {
        $tables = [
            [
                'from_alias' => 'l',
                'table'      => 'page_hits',
                'alias'      => 'ph',
                'condition'  => 'l.id = ph.lead_id',
            ],
        ];

        $config = [
            'column' => 'ph.email_id',
        ];

        $this->buildJoinQuery($event, $tables, $config);
    }

    /**
     * @param LeadBuildSearchEvent $event
     */
    private function buildEmailFailureQuery(LeadBuildSearchEvent $event)
    {
        $tables = [
            [
                'from_alias' => 'l',
                'table'      => 'email_stats',
                'alias'      => 'es',
                'condition'  => 'l.id = es.lead_id',
            ],
        ];
        $config = [
            'column' => 'es.email_id',
            'params' => [
                'es.is_failed' => 1,
            ],
        ];
        $this->buildJoinQuery($event, $tables, $config);
    }

    /**
     * @param LeadBuildSearchEvent $event
     */
    private function buildEmailUnsubscribeQuery(LeadBuildSearchEvent $event)
    {
        $tables = [
            [
                'from_alias' => 'l',
                'table'      => 'email_stats',
                'alias'      => 'es',
                'condition'  => 'l.id = es.lead_id',
            ],
        ];
        $config = [
            'column' => 'es.email_id',
            'params' => [
                'es.is_unsubscribe' => 1,
            ],
        ];
        $this->buildJoinQuery($event, $tables, $config);
    }

    /**
     * @param LeadBuildSearchEvent $event
     */
    private function buildEmailBounceQuery(LeadBuildSearchEvent $event)
    {
        $tables = [
            [
                'from_alias' => 'l',
                'table'      => 'email_stats',
                'alias'      => 'es',
                'condition'  => 'l.id = es.lead_id',
            ],
        ];
        $config = [
            'column' => 'es.email_id',
            'params' => [
                'es.is_bounce' => 1,
            ],
        ];
        $this->buildJoinQuery($event, $tables, $config);
    }

    /**
     * @param LeadBuildSearchEvent $event
     */
    private function buildSmsSentQuery(LeadBuildSearchEvent $event)
    {
        $tables = [
            [
                'from_alias' => 'l',
                'table'      => 'sms_message_stats',
                'alias'      => 'ss',
                'condition'  => 'l.id = ss.lead_id',
            ],
        ];

        $config = [
            'column' => 'ss.sms_id',
        ];

        $this->buildJoinQuery($event, $tables, $config);
    }

    /**
     * @param LeadBuildSearchEvent $event
     */
    private function buildWebSentQuery(LeadBuildSearchEvent $event)
    {
        $this->buildNotificationSentQuery($event);
    }

    /**
     * @param LeadBuildSearchEvent $event
     */
    private function buildMobileSentQuery(LeadBuildSearchEvent $event)
    {
        $this->buildNotificationSentQuery($event, true);
    }

    /**
     * @param LeadBuildSearchEvent $event
     * @param bool                 $isMobile
     */
    private function buildNotificationSentQuery(LeadBuildSearchEvent $event, $isMobile = false)
    {
        $tables = [
            [
                'from_alias' => 'l',
                'table'      => 'push_notification_stats',
                'alias'      => 'ns',
                'condition'  => 'l.id = ns.lead_id',
            ],
            [
                'from_alias' => 'ns',
                'table'      => 'push_notifications',
                'alias'      => 'pn',
                'condition'  => 'pn.id = ns.notification_id',
            ],
        ];

        $config = [
            'column' => 'pn.id',
            'params' => [
                'pn.mobile' => (int) $isMobile,
            ],
        ];

        $this->buildJoinQuery($event, $tables, $config);
    }

    private function buildDripScheduledQuery(LeadBuildSearchEvent $event)
    {
        $tables = [
            [
                'from_alias' => 'l',
                'table'      => 'dripemail_lead_event_log',
                'alias'      => 'el',
                'condition'  => 'l.id = el.lead_id',
            ],
        ];
        $config = [
            'column'  => 'el.email_id',
            'params'  => [
                'is_scheduled' => 1,
            ],
        ];

        $this->buildJoinQuery($event, $tables, $config);
    }

    /**
     * @param LeadBuildSearchEvent $event
     */
    private function buildDripEmailSentQuery(LeadBuildSearchEvent $event)
    {
        $qb = $this->emailRepository->getEntityManager()->createQueryBuilder();
        //$qb->select('es.lead_id');*/
        $dripModel  = $this->factory->get('mautic.email.model.dripemail');
        $dripId     = (int) $event->getString();
        $dripEntity = $dripModel->getEntity($dripId);
        $entities   = $this->emailRepository->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'e.dripEmail',
                            'expr'   => 'eq',
                            'value'  => $dripEntity,
                        ],
                    ],
                ],
                'orderBy'          => 'e.dripEmailOrder',
                'orderByDir'       => 'asc',
                'ignore_paginator' => true,
            ]
        );
        /*$nq->select('l.id'); // select only id
        $nsql = $nq->getSQL();
        foreach ($nq->getParameters() as $pk => $pv) { // replace all parameters
            $nsql = preg_replace('/:'.$pk.'/', is_bool($pv) ? (int) $pv : $pv, $nsql);
        }
        $query = $q->expr()->in('l.id', sprintf('(%s)', $nsql));*/
        //$event->setSubQuery($query);
        $ids   = [];
        foreach ($entities as $email) {
            $ids[] = $email->getId();
        }
        $q        = $event->getQueryBuilder();
        $joinType = 'join';
        $q->$joinType(
            'l',
            MAUTIC_TABLE_PREFIX.'email_stats',
            'es',
            'l.id = es.lead_id'
        );
        $qb->select('l.id');
        $qb->andWhere($qb->expr()->in('es.email_id', $ids));
        $query = $q->expr()->in('l.id', sprintf('(%s)', $qb));
        $event->setSubQuery($query);
        $config = [
            'column' => 'es.email_id',
            'params' => [
                'es.is_failed' => 0,
            ],
        ];
        //$this->buildJoinQuery($event, $tables, $config);
    }

    private function buildWfProgressQuery($event)
    {
        $campaignid    =$event->getString();
        $campaign      =$this->CampaignModel->getEntity($campaignid);
        $canvassettings=json_decode($campaign->getCanvasSettings());
        $exitevents    =$this->CampaignModel->getExitEvent($canvassettings);
        $completedleads= $this->CampaignModel->getRepository()->getWfCompletedLeads($campaignid, $exitevents);
        $tables        = [
            [
                'from_alias' => 'l',
                'table'      => 'campaign_lead_event_log',
                'alias'      => 'cl',
                'condition'  => 'l.id = cl.lead_id',
            ],
        ];
        $q     = $event->getQueryBuilder();

        $expr = $q->expr()->andX($q->expr()->eq('cl.campaign_id', $campaignid),
                     $q->expr()->notin('cl.lead_id', $completedleads));

        $this->leadRepo->applySearchQueryRelationship($q, $tables, false, $expr);
    }

    private function buildWfCompletedQuery($event)
    {
        $campaignid    =$event->getString();
        $campaign      =$this->CampaignModel->getEntity($campaignid);
        $canvassettings=json_decode($campaign->getCanvasSettings());
        $exitevents    =$this->CampaignModel->getExitEvent($canvassettings);
        $tables        = [
            [
                'from_alias' => 'l',
                'table'      => 'campaign_lead_event_log',
                'alias'      => 'cl',
                'condition'  => 'l.id = cl.lead_id',
            ],
        ];

        $q     = $event->getQueryBuilder();
        $expr  = $q->expr()->andX($q->expr()->in('cl.event_id', $exitevents));
        $this->leadRepo->applySearchQueryRelationship($q, $tables, true, $expr);
    }

    private function buildWfGoalQuery($event)
    {
        $campaignid    =$event->getString();
        $campaign      =$this->CampaignModel->getEntity($campaignid);
        $canvassettings=json_decode($campaign->getCanvasSettings());
        $exitevents    =$this->CampaignModel->getExitEvent($canvassettings);
        $tables        = [
            [
                'from_alias' => 'l',
                'table'      => 'campaign_lead_event_log',
                'alias'      => 'cl',
                'condition'  => 'l.id = cl.lead_id',
            ],
            [
                'from_alias' => 'cl',
                'table'      => 'campaign_events',
                'alias'      => 'ce',
                'condition'  => 'cl.event_id = ce.id',
            ],
        ];
        $q     = $event->getQueryBuilder();
        $expr  = $q->expr()->andX($q->expr()->eq('ce.trigger_mode', '"interrupt"'),
            $q->expr()->eq('cl.campaign_id', $campaignid));
        $this->leadRepo->applySearchQueryRelationship($q, $tables, true, $expr);
    }

    /**
     * @param $event
     */
    private function buildDripLeadQuery($event)
    {
        $tables = [
            [
              'from_alias'  => 'l',
              'table'       => 'dripemail_leads',
              'alias'       => 'dl',
              'condition'   => 'l.id = dl.lead_id',
            ],
        ];
        $config = [
            'column' => 'dl.dripemail_id',
        ];
        $this->buildJoinQuery($event, $tables, $config);
    }

    /**
     * @param $event
     */
    private function buildDripSentQuery($event)
    {
        $dripid   =$event->getString();
        $emailIds = $this->emailRepository->getEmailIdsByDripid($dripid);
        $tables   = [
            [
                'from_alias' => 'l',
                'table'      => 'email_stats',
                'alias'      => 'es',
                'condition'  => 'l.id = es.lead_id',
            ],
        ];

        $q     = $event->getQueryBuilder();
        $expr  = $q->expr()->andX($q->expr()->eq('es.is_failed', '"0"'),
            $q->expr()->in('es.email_id', $emailIds));
        $this->leadRepo->applySearchQueryRelationship($q, $tables, true, $expr);
        //$this->buildJoinQuery($event, $tables, $config);
    }

    /**
     * @param $event
     */
    private function buildDripReadQuery($event)
    {
        $dripid   =$event->getString();
        $emailIds = $this->emailRepository->getEmailIdsByDripid($dripid);
        $tables   = [
            [
                'from_alias' => 'l',
                'table'      => 'email_stats',
                'alias'      => 'es',
                'condition'  => 'l.id = es.lead_id',
            ],
        ];

        $q     = $event->getQueryBuilder();
        $expr  = $q->expr()->andX($q->expr()->eq('es.is_read', '"1"'),
            $q->expr()->in('es.email_id', $emailIds));
        $this->leadRepo->applySearchQueryRelationship($q, $tables, true, $expr);
    }

    /**
     * @param $event
     */
    private function buildDripClickQuery($event)
    {
        $dripid   =$event->getString();
        $emailIds = $this->emailRepository->getEmailIdsByDripid($dripid);
        $tables   = [
            [
                'from_alias' => 'l',
                'table'      => 'page_hits',
                'alias'      => 'ph',
                'condition'  => 'l.id = ph.lead_id',
            ],
        ];

        $q     = $event->getQueryBuilder();
        $expr  = $q->expr()->andX($q->expr()->in('ph.email_id', $emailIds));
        $this->leadRepo->applySearchQueryRelationship($q, $tables, true, $expr);
    }

    /**
     * @param $event
     */
    private function buildDripUnsubscribeQuery($event)
    {
        $dripid   =$event->getString();
        $emailIds = $this->emailRepository->getEmailIdsByDripid($dripid);
        $tables   = [
            [
                'from_alias' => 'l',
                'table'      => 'email_stats',
                'alias'      => 'es',
                'condition'  => 'l.id = es.lead_id',
            ],
        ];
        $q     = $event->getQueryBuilder();
        $expr  = $q->expr()->andX($q->expr()->eq('es.is_unsubscribe', '"1"'),
            $q->expr()->in('es.email_id', $emailIds));
        $this->leadRepo->applySearchQueryRelationship($q, $tables, true, $expr);
    }

    /**
     * @param LeadBuildSearchEvent $event
     * @param array                $tables
     * @param array                $config
     */
    private function buildJoinQuery(LeadBuildSearchEvent $event, array $tables, array $config)
    {
        if (!isset($config['column']) || 0 === count($tables)) {
            return;
        }

        $alias = $event->getAlias();
        $q     = $event->getQueryBuilder();
        $expr  = $q->expr()->andX(sprintf('%s = :%s', $config['column'], $alias));

        if (isset($config['params'])) {
            $params = (array) $config['params'];
            foreach ($params as $name => $value) {
                $param = $q->createNamedParameter($value);
                $expr->add(sprintf('%s = %s', $name, $param));
            }
        }

        $this->leadRepo->applySearchQueryRelationship($q, $tables, true, $expr);

        $event->setReturnParameters(true); // replace search string
        $event->setStrict(true);           // don't use like
        $event->setSearchStatus(true);     // finish searching
    }
}
