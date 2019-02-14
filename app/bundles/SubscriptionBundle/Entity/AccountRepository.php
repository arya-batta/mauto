<?php

/*
 * @copyright   2014 Mautic Contributorcomp. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SubscriptionBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * Class AccountRepository.
 */
class AccountRepository extends CommonRepository
{
    /**
     * {@inhertidoc}.
     *
     * @param array $args
     *
     * @return Paginator
     */
    public function getEntities(array $args = [])
    {
        $q = $this
            ->createQueryBuilder('a')
            ->select('a');
        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTableAlias()
    {
        return 'a';
    }

    public function getTotalSentCounts()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count( e.id) as sentcount')
            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'es')
            ->leftJoin('es', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = es.email_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('es.is_failed', ':false')
                )
            )->setParameter('false', false, 'boolean');

        //get a total number of sent emails
        $results = $q->execute()->fetchAll();

        return $results[0]['sentcount'];
    }

    public function getTotalUniqueOpenCounts()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count( e.id) as readcount')
            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'es')
            ->leftJoin('es', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = es.email_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('es.is_failed', ':false')
                )
            )->setParameter('false', false, 'boolean');
        $q->andWhere(
            $q->expr()->eq('es.is_read', 1)
        );
        $q->andWhere(
            $q->expr()->isNotNull('es.email_id')
        );

        //get a total number of sent emails
        $results = $q->execute()->fetchAll();

        return $results[0]['readcount'];
    }

    public function getTotalOpenCounts()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('es.open_count as opencount')
            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'es')
            ->leftJoin('es', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = es.email_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('es.is_failed', ':false')
                )
            )->setParameter('false', false, 'boolean');
        $q->andWhere(
            $q->expr()->eq('es.is_read', 1)
        );
        $q->andWhere(
            $q->expr()->isNotNull('es.email_id')
        );

        //get a total number of sent emails
        $results = $q->execute()->fetchAll();

        $count = 0;
        for ($i = 0; $i < sizeof($results); ++$i) {
            $count += $results[$i]['opencount'];
        }

        return $count;
    }

    public function getEmailClickCounts()
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $q->select('t.unique_hits')
            ->from(MAUTIC_TABLE_PREFIX.'page_redirects', 'r')
            ->leftJoin('r', MAUTIC_TABLE_PREFIX.'channel_url_trackables', 't',
                $q->expr()->andX(
                    $q->expr()->eq('r.id', 't.redirect_id'),
                    $q->expr()->eq('t.channel', ':channel')
                )
            )
            ->setParameter('channel', 'email')
            ->leftJoin('t', MAUTIC_TABLE_PREFIX.'email_stats', 'es',
                $q->expr()->andX(
                    $q->expr()->eq('t.channel_id', 'es.id')
                ))
            ->leftJoin('es', MAUTIC_TABLE_PREFIX.'emails', 'e',
                $q->expr()->andX(
                    $q->expr()->eq('es.email_id', 'e.id')
                ))
            ->andWhere(
                $q->expr()->isNotNull('es.email_id')
            )
            ->orderBy('r.url');

        $results = $q->execute()->fetchAll();
        $count   = 0;
        for ($i = 0; $i < sizeof($results); ++$i) {
            $count += $results[$i]['unique_hits'];
        }

        return $count;
    }

    public function getTotalUnsubscribedCounts()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count( e.id) as unsubscribecount')
            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'es')
            ->leftJoin('es', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = es.email_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('es.is_failed', ':false')
                )
            )->setParameter('false', false, 'boolean');
        $q->andWhere(
            $q->expr()->eq('es.is_unsubscribe', 1)
        );
        $q->andWhere(
            $q->expr()->isNotNull('es.email_id')
        );
        //get a total number of sent emails
        $results = $q->execute()->fetchAll();

        return $results[0]['unsubscribecount'];
    }

    public function getTotalBounceCounts()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count( e.id) as bouncecount')
            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'es')
            ->leftJoin('es', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = es.email_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('es.is_failed', ':false')
                )
            )->setParameter('false', false, 'boolean');
        $q->andWhere(
            $q->expr()->eq('es.is_bounce', 1)
        );
        $q->andWhere(
            $q->expr()->isNotNull('es.email_id')
        );

        //get a total number of sent emails
        $results = $q->execute()->fetchAll();

        return $results[0]['bouncecount'];
    }

    public function getTotalSpamCounts()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count( e.id) as spamcount')
            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'es')
            ->leftJoin('es', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = es.email_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('es.is_failed', ':false')
                )
            )->setParameter('false', false, 'boolean');
        $q->andWhere(
            $q->expr()->eq('es.is_spam', 1)
        );

        //get a total number of sent emails
        $results = $q->execute()->fetchAll();

        return $results[0]['spamcount'];
    }

    public function getTotalAllLeads()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();

        $q->select('count( l.id) as leadcount')
            ->from(MAUTIC_TABLE_PREFIX.'leads', 'l');
        $results = $q->execute()->fetchAll();

        return $results[0]['leadcount'];
    }

    public function getAllActiveLeads()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count( l.id) as activeleads')
            ->from(MAUTIC_TABLE_PREFIX.'leads', 'l')
            ->leftJoin('l', MAUTIC_TABLE_PREFIX.'lead_donotcontact', 'd', 'd.lead_id = l.id');
        $q->andWhere(
            $q->expr()->isNotNull('d.lead_id')
        );
        //get a total number of sent emails
        $results = $q->execute()->fetchAll();

        return $results[0]['activeleads'];
    }

    public function getRecentlyAddedLeadsCount()
    {
        $q                   = $this->_em->getConnection()->createQueryBuilder();
        $last7daysAddedLeads = date('Y-m-d', strtotime('-6 days'));

        $q->select('count(*) as recentlyadded')
            ->from(MAUTIC_TABLE_PREFIX.'leads', 'l')
            ->andWhere($q->expr()->gte('l.date_added', ':dateAdded'))
            ->setParameter('dateAdded', $last7daysAddedLeads);

        $results = $q->execute()->fetchAll();

        return $results[0]['recentlyadded'];
    }

    public function getRecentActiveLeadCount()
    {
        $last7daysActiveLeads = date('Y-m-d', strtotime('-6 days'));

        $q = $this->_em->getConnection()->createQueryBuilder();

        $q->select('count(*) as activeleads')
            ->from(MAUTIC_TABLE_PREFIX.'leads', 'l')
            ->andWhere($q->expr()->gte('l.last_active', ':last7daysActive'))
            ->setParameter('last7daysActive', $last7daysActiveLeads);

        $results = $q->execute()->fetchAll();

        return $results[0]['activeleads'];
    }

    public function getActiveWorkflows()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count( c.id) as activeworkflow')
            ->from(MAUTIC_TABLE_PREFIX.'campaigns', 'c');
        $q->andWhere(
        $q->expr()->andX(
            $q->expr()->eq('c.is_published', 1)
        )
        );
        //get a total number of sent emails
        $results = $q->execute()->fetchAll();

        return $results[0]['activeworkflow'];
    }

    public function getActiveForms()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count( f.id) as activeforms')
            ->from(MAUTIC_TABLE_PREFIX.'forms', 'f');
        $q->andWhere(
        $q->expr()->andX(
            $q->expr()->eq('f.is_published', 1)
        )
        );
        //get a total number of sent emails
        $results = $q->execute()->fetchAll();

        return $results[0]['activeforms'];
    }

    public function getActiveAssets()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count( a.id) as activeasset')
            ->from(MAUTIC_TABLE_PREFIX.'assets', 'a');
        $q->andWhere(
        $q->expr()->andX(
            $q->expr()->eq('a.is_published', 1)
        )
        );
        //get a total number of sent emails
        $results = $q->execute()->fetchAll();

        return $results[0]['activeasset'];
    }

    public function getGoalsAchived()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count( l.id) as goalsachived')
            ->from(MAUTIC_TABLE_PREFIX.'leads', 'l');
        $q->innerJoin('l', MAUTIC_TABLE_PREFIX.'campaign_lead_event_log', 'cl', 'l.id = cl.lead_id');
        $q->innerJoin('cl', MAUTIC_TABLE_PREFIX.'campaign_events', 'ce', 'cl.event_id = ce.id');
        $q->andWhere(
        $q->expr()->andX(
            $q->expr()->eq('ce.trigger_mode', ':triggerMode')
            )
        );
        $q->setParameter('triggerMode', 'interrupt');
        $results = $q->execute()->fetchAll();

        return $results[0]['goalsachived'];
    }

    public function getFormSubmissionCount()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count( fs.id) as submissions')
            ->from(MAUTIC_TABLE_PREFIX.'form_submissions', 'fs');

        $results = $q->execute()->fetchAll();

        return $results[0]['submissions'];
    }

    public function getAssetDownloadCount()
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count( ad.id) as downloads')
            ->from(MAUTIC_TABLE_PREFIX.'asset_downloads', 'ad');

        $results = $q->execute()->fetchAll();

        return $results[0]['downloads'];
    }
}
