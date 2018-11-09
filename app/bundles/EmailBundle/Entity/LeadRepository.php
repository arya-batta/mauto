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

/**
 * LeadRepository.
 */
class LeadRepository extends CommonRepository
{
    public function getEntities(array $args = [])
    {
        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('le')
            ->from('MauticEmailBundle:Lead', 'le', 'le.id');

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    /**
     * Get the details of leads added to a campaign.
     *
     * @param      $campaignId
     * @param null $leads
     *
     * @return array
     */
    public function getLeadDetails($campaignId, $leads = null)
    {
        $q = $this->getEntityManager()->createQueryBuilder()
            ->from('MauticEmailBundle:Lead', 'lc')
            ->select('le')
            ->leftJoin('le.campaign', 'c')
            ->leftJoin('le.lead', 'l');
        $q->where(
            $q->expr()->eq('e.id', ':campaign')
        )->setParameter('campaign', $campaignId);

        if (!empty($leads)) {
            $q->andWhere(
                $q->expr()->in('l.id', ':leads')
            )->setParameter('leads', $leads);
        }

        $results = $q->getQuery()->getArrayResult();

        $return = [];
        foreach ($results as $r) {
            $return[$r['lead_id']][] = $r;
        }

        return $return;
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'le';
    }

    public function checkisLeadLinked($lead, $dripemail)
    {
        return $this->getEntities(
            [
                'filter'           => [
                    'force' => [
                        [
                            'column' => 'le.campaign',
                            'expr'   => 'eq',
                            'value'  => $dripemail,
                        ],
                        [
                            'column' => 'le.lead',
                            'expr'   => 'eq',
                            'value'  => $lead,
                        ],
                    ],
                ],
                'ignore_paginator' => true,
            ]
        );
    }
}
