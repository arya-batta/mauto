<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * Class ListLeadOptInRepository.
 */
class ListLeadOptInRepository extends CommonRepository
{
    /**
     * Select Segments by lead ID.
     *
     * @param $leadID
     */
    public function getListIDbyLeads($leadID)
    {
        $qb = $this->_em->getConnection()->createQueryBuilder();

        $qb->select('l.leadlist_id')
            ->from(MAUTIC_TABLE_PREFIX.'lead_listoptin_leads', 'l');
        $qb->andWhere('l.lead_id = :lead')
            ->setParameter('lead', $leadID);

        return $qb->execute()->fetchAll();
    }

    public function getlistnameByID($id)
    {
        $qb = $this->_em->getConnection()->createQueryBuilder();

        $qb->select('l.name')
            ->from(MAUTIC_TABLE_PREFIX.'lead_listoptin', 'l')
            ->andWhere('l.id = :lead')
            ->setParameter('lead', $id);

        return $qb->execute()->fetch();
    }

    public function getListEntityByid($leadid, $listid)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('l.*')
            ->from(MAUTIC_TABLE_PREFIX.'lead_listoptin_leads', 'l');
        $q->where(
            $q->expr()->eq('l.leadlist_id', ':listId'),
            $q->expr()->eq('l.lead_id', ':leadId')
        );
        $q->setParameter('listId', $listid);
        $q->setParameter('leadId', $leadid);

        //$result = $this->getEntities(['qb' => $q, 'ignore_paginator' => true]);
        $result = $this->getEntities(
            [
                'filter' => [
                    'force' => [
                        [
                            'column' => 'l.list',
                            'expr'   => 'eq',
                            'value'  => $listid,
                        ],
                        [
                            'column' => 'l.lead',
                            'expr'   => 'eq',
                            'value'  => $leadid,
                        ],
                    ],
                ],
                'ignore_paginator' => true,
            ]
        );
        if (sizeof($result) > 0) {
            foreach ($result as $email) {
                return $email;
            }
        }

        return null;
    }

    public function getResendLists($dateInterval, $limit)
    {
        //$result = $this->getEntities(['qb' => $q, 'ignore_paginator' => true]);
        $result = $this->getEntities(
            [
                'limit'  => $limit,
                'filter' => [
                    'force' => [
                        [
                            'column' => 'l.isrescheduled',
                            'expr'   => 'eq',
                            'value'  => 1,
                        ],
                        [
                            'column' => 'l.unconfirmedLead',
                            'expr'   => 'eq',
                            'value'  => 1,
                        ],
                        [
                            'column' => 'l.dateAdded',
                            'expr'   => 'lte',
                            'value'  => $dateInterval,
                        ],
                    ],
                ],
                'ignore_paginator' => true,
            ]
        );

        return $result;
    }

    public function getResendListsCount($dateInterval)
    {
        //$result = $this->getEntities(['qb' => $q, 'ignore_paginator' => true]);
        $result = $this->getEntities(
            [
                'filter' => [
                    'force' => [
                        [
                            'column' => 'l.isrescheduled',
                            'expr'   => 'eq',
                            'value'  => 1,
                        ],
                        [
                            'column' => 'l.unconfirmedLead',
                            'expr'   => 'eq',
                            'value'  => 1,
                        ],
                        [
                            'column' => 'l.dateAdded',
                            'expr'   => 'lte',
                            'value'  => $dateInterval,
                        ],
                    ],
                ],
                'ignore_paginator' => true,
            ]
        );

        return count($result);
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'l';
    }
}
