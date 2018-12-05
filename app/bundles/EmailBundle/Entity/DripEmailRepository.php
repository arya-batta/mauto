<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Entity;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * Class DripEmailRepository.
 */
class DripEmailRepository extends CommonRepository
{
    /**
     * Get a list of entities.
     *
     * @param array $args
     *
     * @return Paginator
     */
    public function getEntities(array $args = [])
    {
        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('d')
            ->from('MauticEmailBundle:DripEmail', 'd', 'd.id');
        if (empty($args['iterator_mode'])) {
            $q->leftJoin('d.category', 'c');
        }

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    /**
     * @return string
     */
    protected function getDefaultOrder()
    {
        return [
            ['d.name', 'ASC'],
        ];
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'd';
    }

    public function updateFromInfoinEmail(DripEmail $entity)
    {
        $dripId = $entity->getId();

        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $qb->update(MAUTIC_TABLE_PREFIX.'emails')
            ->set('from_name', ':fromName')
            ->set('from_address', ':fromAddress')
            ->set('reply_to_address', ':replyToAddress')
            ->set('bcc_address', ':BccAddress')
            ->set('unsubscribe_text', ':UnsubscribeText')
            ->set('postal_address', ':postalAddress')
            ->setParameter('fromName', $entity->getFromName())
            ->setParameter('fromAddress', $entity->getFromAddress())
            ->setParameter('replyToAddress', $entity->getReplyToAddress())
            ->setParameter('BccAddress', $entity->getBccAddress())
            ->setParameter('UnsubscribeText', $entity->getUnsubscribeText())
            ->setParameter('postalAddress', $entity->getPostalAddress())
            ->where(
                $qb->expr()->eq('dripemail_id', $dripId)
            )
            ->execute();
    }

    /**
     * Get sent counts based on date(Last 30 Days).
     *
     * @param array $emailIds
     *
     * @return array
     */
    public function getLast30DaysDripSentCounts($viewOthers =false)
    {
        $fromdate = date('Y-m-d', strtotime('-29 days'));

        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count(e.id) as sentcount')
            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'es')
            ->leftJoin('es', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = es.email_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('es.is_failed', ':false')
                )
            )->setParameter('false', false, 'boolean');

        $q->andWhere($q->expr()->eq('e.email_type', ':emailType'),
            $q->expr()->neq('e.dripemail_id', '"NULL"'))
            ->setParameter('emailType', 'dripemail');

        if ($fromdate !== null) {
            $q->andWhere(
                $q->expr()->gte('es.date_sent', $q->expr()->literal($fromdate))
            );
        }

        if (!$viewOthers) {
            $q->andWhere($q->expr()->eq('e.created_by', ':currentUserId'))
                ->setParameter('currentUserId', $this->currentUser->getId());
        }

        if ($this->currentUser->getId() != 1) {
            $q->andWhere($q->expr()->neq('e.created_by', ':id'))
                ->setParameter('id', '1');
        }

        //get a total number of sent emails
        $results = $q->execute()->fetchAll();

        return $results[0]['sentcount'];
    }

    /**
     * Get open counts based on date.
     *
     * @param array $emailIds
     *
     * @return array
     */
    public function getLast30DaysDripOpensCounts($viewOthers =false)
    {
        $fromdate = date('Y-m-d', strtotime('-29 days'));

        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count(e.id) as opencount')
            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'es')
            ->leftJoin('es', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = es.email_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('es.is_failed', ':false')
                )
            )->setParameter('false', false, 'boolean');

        $q->andWhere($q->expr()->eq('e.email_type', ':emailType'),
            $q->expr()->neq('e.dripemail_id', '"NULL"'))
            ->setParameter('emailType', 'dripemail');

        if ($fromdate !== null) {
            $q->andWhere(
                $q->expr()->gte('es.date_sent', $q->expr()->literal($fromdate))
            );
            $q->andWhere(
                $q->expr()->eq('es.is_read', 1)
            );
        }

        if (!$viewOthers) {
            $q->andWhere($q->expr()->eq('e.created_by', ':currentUserId'))
                ->setParameter('currentUserId', $this->currentUser->getId());
        }

        if ($this->currentUser->getId() != 1) {
            $q->andWhere($q->expr()->neq('e.created_by', ':id'))
                ->setParameter('id', '1');
        }

        //get a total number of sent emails
        $results = $q->execute()->fetchAll();

        return $results[0]['opencount'];
    }

    /**
     * Get open counts based on date.
     *
     * @param array $emailIds
     *
     * @return array
     */
    public function getLast30DaysDripClickCounts($viewOthers =false)
    {
        $dateinterval = date('Y-m-d', strtotime('-29 days'));
        $q            = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $q->select('count(e.id) as clickcount')
            ->from(MAUTIC_TABLE_PREFIX.'page_hits', 'ph')
            ->leftJoin('ph', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = ph.email_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->gte('ph.date_hit', ':clickdate')
                )
            )->setParameter('clickdate', $dateinterval);
        $q->andWhere($q->expr()->eq('e.email_type', ':emailType'),
            $q->expr()->neq('e.dripemail_id', '"NULL"'))
            ->setParameter('emailType', 'dripemail');
        if (!$viewOthers) {
            $q->andWhere($q->expr()->eq('e.created_by', ':currentUserId'))
                ->setParameter('currentUserId', $this->currentUser->getId());
        }

        if ($this->currentUser->getId() != 1) {
            $q->andWhere($q->expr()->neq('e.created_by', ':id'))
                ->setParameter('id', '1');
        }

        $results = $q->execute()->fetchAll();

        return $results[0]['clickcount'];
    }

    public function getDripUnsubscribeCounts($viewOthers = false)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count(e.id) as unsubscribecount')
            ->from(MAUTIC_TABLE_PREFIX.'lead_donotcontact', 'l')
            ->leftJoin('l', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = l.channel_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('l.reason', 1)
                )
            );

        $q->andWhere('e.email_type = :emailType')
            ->setParameter('emailType', 'dripemail');
        $q->andWhere($q->expr()->neq('e.dripemail_id','"NULL"'));

        if (!$viewOthers) {
            $q->andWhere($q->expr()->eq('e.created_by', ':currentUserId'))
                ->setParameter('currentUserId', $this->currentUser->getId());
        }

        if ($this->currentUser->getId() != 1) {
            $q->andWhere($q->expr()->neq('e.created_by', ':id'))
                ->setParameter('id', '1');
        }

        //get a total number of sent emails
        $results = $q->execute()->fetchAll();

        return $results[0]['unsubscribecount'];
    }

    /**
     * @param string $search
     * @param int    $limit
     * @param int    $start
     * @param bool   $viewOther
     * @param null   $emailType
     * @param array  $ignoreIds
     *
     * @return array
     */
    public function getDripEmailList($search = '', $limit = 10, $start = 0, $viewOther = false, array $ignoreIds = [])
    {
        $q = $this->createQueryBuilder('d');
        $q->select('partial d.{id, subject, name}');

        if (!empty($search)) {
            if (is_array($search)) {
                $search = array_map('intval', $search);
                $q->andWhere($q->expr()->in('d.id', ':search'))
                    ->setParameter('search', $search);
            } else {
                $q->andWhere($q->expr()->like('d.name', ':search'))
                    ->setParameter('search', "%{$search}%");
            }
        }
        $q->andWhere($q->expr()->eq('d.isPublished', ':val'))
            ->setParameter('val', '1');

        /*      if (!$viewOther) {
                  $q->andWhere($q->expr()->eq('d.createdBy', ':id'))
                      ->setParameter('id', $this->currentUser->getId());
              }*/
        if ($this->currentUser->getId() != 1) {
            $q->andWhere($q->expr()->neq('d.createdBy', ':id'))
                ->setParameter('id', '1');
        }

        if (!empty($ignoreIds)) {
            $q->andWhere($q->expr()->notIn('d.id', ':dripEmailIds'))
                ->setParameter('dripEmailIds', $ignoreIds);
        }

        $q->orderBy('d.name');

        if (!empty($limit)) {
            $q->setFirstResult($start)
                ->setMaxResults($limit);
        }

        return $q->getQuery()->getArrayResult();
    }

    public function getAllDripEmailList()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('e.id as id,e.subject as name,d.name as dripname')
            ->from(MAUTIC_TABLE_PREFIX.'emails', 'e')
            ->leftJoin('e', MAUTIC_TABLE_PREFIX.'dripemail', 'd', 'e.dripemail_id = d.id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('e.email_type', ':emailType')
                )
            )->setParameter('emailType', 'dripemail');

        $q->andWhere($q->expr()->eq('d.is_published', ':isPublished'))
            ->setParameter('isPublished', '1');

        $q->andWhere($q->expr()->isNotNull('e.dripemail_id'));

        $results = $q->execute()->fetchAll();

        return $results;
    }
}
