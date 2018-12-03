<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\LeadBundle\Entity\TimelineTrait;

/**
 * LeadEventLogRepository.
 */
class LeadEventLogRepository extends CommonRepository
{
    use TimelineTrait;

    public function getEntities(array $args = [])
    {
        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('dle')
            ->from('MauticEmailBundle:LeadEventLog', 'dle', 'dle.id');

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'dle';
    }

    /**
     * @param $campaignId
     * @param $leadId
     */
    public function removeScheduledEvents($campaignId, $leadId)
    {
        $conn = $this->_em->getConnection();
        $conn->delete(MAUTIC_TABLE_PREFIX.'dripemail_lead_event_log', [
            'lead_id'       => (int) $leadId,
            'dripemail_id'  => (int) $campaignId,
            'is_scheduled'  => 1,
        ]);
    }

    public function checkisLeadCompleted($lead, $dripemail, $email)
    {
        return $this->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'dle.campaign',
                            'expr'   => 'eq',
                            'value'  => $dripemail,
                        ],
                        [
                            'column' => 'dle.email',
                            'expr'   => 'eq',
                            'value'  => $email,
                        ],
                        [
                            'column' => 'dle.lead',
                            'expr'   => 'eq',
                            'value'  => $lead,
                        ],
                    ],
                ],
                'ignore_paginator' => true,
            ]
        );
    }

    public function getScheduledEvents($currentTime)
    {
        return $this->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'dle.triggerDate',
                            'expr'   => 'lte',
                            'value'  => $currentTime,
                        ],
                        [
                            'column' => 'dle.isScheduled',
                            'expr'   => 'eq',
                            'value'  => 1,
                        ],
                    ],
                ],
                'ignore_paginator' => true,
            ]
        );
    }

    public function getScheduledEventsbyDripEmail($email)
    {
        return $this->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'dle.email',
                            'expr'   => 'eq',
                            'value'  => $email,
                        ],
                        [
                            'column' => 'dle.isScheduled',
                            'expr'   => 'eq',
                            'value'  => 1,
                        ],
                    ],
                ],
                'ignore_paginator' => true,
            ]
        );
    }

    /**
     * @param $campaignId
     * @param $leadId
     */
    public function removeScheduledDripLead($campaignId, $leadId)
    {
        $conn = $this->_em->getConnection();

        $conn->delete(MAUTIC_TABLE_PREFIX.'dripemail_leads', [
            'lead_id'       => (int) $leadId,
            'dripemail_id'  => (int) $campaignId,
        ]);
    }

    /**
     * @param $campaignId
     * @param $leadId
     */
    public function restartScheduledEvents($campaignId, $leadId)
    {
        $q   = $this->_em->getConnection()->createQueryBuilder();
        $q->update(MAUTIC_TABLE_PREFIX.'dripemail_lead_event_log')
            ->set('is_scheduled', ':scheduled')
            ->set('failedReason', ':reason')
            ->setParameter('reason', 'cancelled')
            ->setParameter('scheduled', 0)
            ->where($q->expr()->eq('dripemail_id', $campaignId));

        $q->andWhere($q->expr()->eq('lead_id', ':leadId'))
            ->setParameter('leadId', $leadId);

        $q->andWhere($q->expr()->eq('is_scheduled', ':iSscheduled'))
            ->setParameter('iSscheduled', 1);

        $q->execute();
    }
}
