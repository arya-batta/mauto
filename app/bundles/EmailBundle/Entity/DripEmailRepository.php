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
            $q->expr()->neq('e.dripemail_id','"NULL"'))
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
            $q->expr()->neq('e.dripemail_id','"NULL"'))
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
            $q->expr()->neq('e.dripemail_id','"NULL"'))
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
    public function getDripUnsubscribeCounts($viewOthers = false){
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count(e.id) as unsubscribecount')
            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'es')
            ->leftJoin('es', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = es.email_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('es.is_failed', ':false')
                )
            )->setParameter('false', false, 'boolean');

        $q->andWhere('e.email_type = :emailType')
            ->setParameter('emailType', 'dripemail');
        $q->andWhere(
            $q->expr()->eq('es.is_unsubscribe', 1),
            $q->expr()->neq('e.dripemail_id','"NULL"')
        );
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
}
