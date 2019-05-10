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
 * LeadListOptInRepository.
 */
class LeadListOptInRepository extends CommonRepository
{
    use OperatorListTrait;
    use ExpressionHelperTrait;
    use RegexTrait;

    /**
     * {@inheritdoc}
     *
     * @param int $id
     *
     * @return mixed|null
     */
    public function getEntity($id = 0)
    {
        try {
            $entity = $this
                ->createQueryBuilder('l')
                ->where('l.id = :listId')
                ->setParameter('listId', $id)
                ->getQuery()
                ->getSingleResult();
        } catch (\Exception $e) {
            $entity = null;
        }

        return $entity;
    }

    /**
     * Get a count of leads that belong to the list.
     *
     * @param $listIds
     *
     * @return array
     */
    public function getLeadCount($listIds, $status = null)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $q->select('count(ll.lead_id) as thecount, ll.leadlist_id')
            ->from(MAUTIC_TABLE_PREFIX.'lead_listoptin_leads', 'll')
            ->leftJoin('ll', MAUTIC_TABLE_PREFIX.'leads', 'l', 'll.lead_id = l.id');

        $returnArray = (is_array($listIds));

        if (!$returnArray) {
            $listIds = [$listIds];
        }

        $q->where(
            $q->expr()->in('ll.leadlist_id', $listIds),
            $q->expr()->eq('ll.manually_removed', ':false')
        )
            ->setParameter('false', false, 'boolean')
            ->groupBy('ll.leadlist_id');

        if ($status != null) {
            if ($status == 'Active') {
                $q->where(
                    $q->expr()->in('l.status', ['1', '2'])
                );
            } else {
                $q->where(
                    $q->expr()->in('l.status', ['3', '4', '5', '6'])
                );
            }
        }

        $result = $q->execute()->fetchAll();

        $return = [];
        foreach ($result as $r) {
            $return[$r['leadlist_id']] = $r['thecount'];
        }

        // Ensure lists without leads have a value
        foreach ($listIds as $l) {
            if (!isset($return[$l])) {
                $return[$l] = 0;
            }
        }

        return ($returnArray) ? $return : $return[$listIds[0]];
    }

    /**
     * Get a count of leads that belong to the list.
     *
     * @param $listIds
     * @param $fieldName
     *
     * @return array
     */
    public function getStatusWiseLeadCount($listIds, $fieldName)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $q->select('count(l.lead_id) as thecount, l.leadlist_id')
            ->from(MAUTIC_TABLE_PREFIX.'lead_listoptin_leads', 'l');

        $returnArray = (is_array($listIds));

        if (!$returnArray) {
            $listIds = [$listIds];
        }

        $q->where(
            $q->expr()->in('l.leadlist_id', $listIds),
            $q->expr()->eq('l.manually_removed', ':false'),
            $q->expr()->eq('l.'.$fieldName, 1)
        )
            ->setParameter('false', false, 'boolean')
            ->groupBy('l.leadlist_id');

        $result = $q->execute()->fetchAll();

        $return = [];
        foreach ($result as $r) {
            $return[$r['leadlist_id']] = $r['thecount'];
        }

        // Ensure lists without leads have a value
        foreach ($listIds as $l) {
            if (!isset($return[$l])) {
                $return[$l] = 0;
            }
        }

        return ($returnArray) ? $return : $return[$listIds[0]];
    }

    /**
     * Return a list of global lists.
     *
     * @return array
     */
    public function getListsOptIn()
    {
        $q = $this->getEntityManager()->createQueryBuilder()
            ->from('MauticLeadBundle:LeadListOptIn', 'l', 'l.id');

        $q->select("l.id as id , l.name as name,(case when l.listtype <> 0 THEN 'Double-OptIn' ELSE 'Single-OptIn' END) as listtype")
            ->where($q->expr()->eq('l.isPublished', 1))
            ->orderBy('l.name');
        if ($this->currentUser != null && !$this->currentUser->isAdmin()) {
            $q->andWhere('l.createdBy != 1');
        }
        $results = $q->getQuery()->getArrayResult();

        return $results;
    }

    /**
     * Check Lead segments by ids.
     *
     * @param Lead $lead
     * @param $ids
     *
     * @return bool
     */
    public function checkLeadListsByIds(Lead $lead, $ids)
    {
        if (empty($ids)) {
            return false;
        }

        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $q->select('l.id')
            ->from(MAUTIC_TABLE_PREFIX.'leads', 'l');
        $q->join('l', MAUTIC_TABLE_PREFIX.'lead_listoptin_leads', 'x', 'l.id = x.lead_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->in('x.leadlist_id', $ids),
                    $q->expr()->eq('l.id', ':leadId')
                )
            )
            ->setParameter('leadId', $lead->getId());

        return  (bool) $q->execute()->fetchColumn();
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'l';
    }

    public function getTotalListCount($viewOthers = false)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count(*) as totalsegment')
            ->from(MAUTIC_TABLE_PREFIX.'lead_listoptin', 'l');

        if (!$viewOthers) {
            $q->andWhere($q->expr()->eq('l.created_by', ':currentUserId'))
                ->setParameter('currentUserId', $this->currentUser->getId());
        }

        if ($this->currentUser->getId() != 1) {
            $q->andWhere($q->expr()->neq('l.created_by', ':id'))
                ->setParameter('id', '1');
        }

        $results = $q->execute()->fetchAll();

        return $results[0]['totalsegment'];
    }

    public function getTotalActiveListCount($viewOthers = false)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();

        $q->select('count(*) as activesegment')
            ->from(MAUTIC_TABLE_PREFIX.'lead_listoptin', 'l')
            ->andWhere('l.is_published = 1 ');

        if (!$viewOthers) {
            $q->andWhere($q->expr()->eq('l.created_by', ':currentUserId'))
                ->setParameter('currentUserId', $this->currentUser->getId());
        }

        if ($this->currentUser->getId() != 1) {
            $q->andWhere($q->expr()->neq('l.created_by', ':id'))
                ->setParameter('id', '1');
        }

        $results = $q->execute()->fetchAll();

        return $results[0]['activesegment'];
    }

    public function getTotalInactiveListCount($viewOthers = false)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count(*) as inactivesegment')
            ->from(MAUTIC_TABLE_PREFIX.'lead_listoptin', 'l')
            ->andWhere('l.is_published != 1 ');

        if (!$viewOthers) {
            $q->andWhere($q->expr()->eq('l.created_by', ':currentUserId'))
                ->setParameter('currentUserId', $this->currentUser->getId());
        }

        if ($this->currentUser->getId() != 1) {
            $q->andWhere($q->expr()->neq('l.created_by', ':id'))
                ->setParameter('id', '1');
        }

        $results = $q->execute()->fetchAll();

        return $results[0]['inactivesegment'];
    }
}
