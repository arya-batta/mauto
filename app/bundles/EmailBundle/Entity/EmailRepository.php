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

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Mautic\ChannelBundle\Entity\MessageQueue;
use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\LeadBundle\Entity\DoNotContact;

/**
 * Class EmailRepository.
 */
class EmailRepository extends CommonRepository
{
    /**
     * Get an array of do not email emails.
     *
     * @param array $leadIds
     *
     * @return array
     */
    public function getDoNotEmailList($leadIds = [])
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $q->select('l.id, l.email')
            ->from(MAUTIC_TABLE_PREFIX.'lead_donotcontact', 'dnc')
            ->leftJoin('dnc', MAUTIC_TABLE_PREFIX.'leads', 'l', 'l.id = dnc.lead_id')
            ->where('dnc.channel = "email"')
            ->andWhere($q->expr()->neq('l.email', $q->expr()->literal('')));

        if ($leadIds) {
            $q->andWhere(
                $q->expr()->in('l.id', $leadIds)
            );
        }

        $results = $q->execute()->fetchAll();

        $dnc = [];
        foreach ($results as $r) {
            $dnc[$r['id']] = strtolower($r['email']);
        }

        return $dnc;
    }

    /**
     * Check to see if an email is set as do not contact.
     *
     * @param $email
     *
     * @return bool
     */
    public function checkDoNotEmail($email)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $q->select('dnc.*')
            ->from(MAUTIC_TABLE_PREFIX.'lead_donotcontact', 'dnc')
            ->leftJoin('dnc', MAUTIC_TABLE_PREFIX.'leads', 'l', 'l.id = dnc.lead_id')
            ->where('dnc.channel = "email"')
            ->andWhere('l.email = :email')
            ->setParameter('email', $email);

        $results = $q->execute()->fetchAll();
        $dnc     = count($results) ? $results[0] : null;

        if ($dnc === null) {
            return false;
        }

        $dnc['reason'] = (int) $dnc['reason'];

        return [
            'id'           => $dnc['id'],
            'unsubscribed' => ($dnc['reason'] === DoNotContact::UNSUBSCRIBED),
            'bounced'      => ($dnc['reason'] === DoNotContact::BOUNCED),
            'spam'         => ($dnc['reason'] === DoNotContact::SPAM),
            'manual'       => ($dnc['reason'] === DoNotContact::MANUAL),
            'comments'     => $dnc['comments'],
        ];
    }

    /**
     * Remove email from DNE list.
     *
     * @param $email
     */
    public function removeFromDoNotEmailList($email)
    {
        /** @var \Mautic\LeadBundle\Model\LeadModel $leadModel */
        $leadModel = $this->factory->getModel('lead.lead');

        /** @var \Mautic\LeadBundle\Entity\LeadRepository $leadRepo */
        $leadRepo = $this->getEntityManager()->getRepository('MauticLeadBundle:Lead');
        $leadId   = (array) $leadRepo->getLeadByEmail($email, true);

        /** @var \Mautic\LeadBundle\Entity\Lead[] $leads */
        $leads = [];

        foreach ($leadId as $lead) {
            $leads[] = $leadRepo->getEntity($lead['id']);
        }

        foreach ($leads as $lead) {
            $leadModel->removeDncForLead($lead, 'email');
        }
    }

    /**
     * Delete DNC row.
     *
     * @param $id
     */
    public function deleteDoNotEmailEntry($id)
    {
        $this->getEntityManager()->getConnection()->delete(MAUTIC_TABLE_PREFIX.'lead_donotcontact', ['id' => (int) $id]);
    }

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
            ->select('e')
            ->from('MauticEmailBundle:Email', 'e', 'e.id');
        if (empty($args['iterator_mode'])) {
            $q->leftJoin('e.category', 'c');

            if (empty($args['ignoreListJoin']) && (!isset($args['email_type']) || $args['email_type'] == 'list')) {
                $q->leftJoin('e.lists', 'l');
            }
        }

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    /**
     * Get amounts of sent and read emails.
     *
     * @return array
     */
    public function getSentReadCount()
    {
        $q = $this->getEntityManager()->createQueryBuilder();
        $q->select('SUM(e.sentCount) as sent_count, SUM(e.readCount) as read_count')
            ->from('MauticEmailBundle:Email', 'e');
        $results = $q->getQuery()->getSingleResult(Query::HYDRATE_ARRAY);

        if (!isset($results['sent_count'])) {
            $results['sent_count'] = 0;
        }
        if (!isset($results['read_count'])) {
            $results['read_count'] = 0;
        }

        return $results;
    }

    /**
     * @param      $emailId
     * @param null $variantIds
     * @param null $listIds
     * @param bool $countOnly
     * @param null $limit
     * @param int  $minContactId
     * @param int  $maxContactId
     * @param bool $countWithMaxMin
     *
     * @return QueryBuilder|int|array
     */
    public function getEmailPendingQuery(
        $emailId,
        $variantIds = null,
        $listIds = null,
        $countOnly = false,
        $limit = null,
        $minContactId = null,
        $maxContactId = null,
        $countWithMaxMin = false
    ) {
        // Do not include leads in the do not contact table
        $dncQb = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $dncQb->select('dnc.lead_id')
            ->from(MAUTIC_TABLE_PREFIX.'lead_donotcontact', 'dnc')
            ->where(
                $dncQb->expr()->eq('dnc.channel', $dncQb->expr()->literal('email'))
            );

        // Do not include contacts where the message is pending in the message queue
        $mqQb = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $mqQb->select('mq.lead_id')
            ->from(MAUTIC_TABLE_PREFIX.'message_queue', 'mq');

        $messageExpr = $mqQb->expr()->andX(
            $mqQb->expr()->eq('mq.channel', $mqQb->expr()->literal('email')),
            $mqQb->expr()->neq('mq.status', $mqQb->expr()->literal(MessageQueue::STATUS_SENT))
        );

        // Do not include leads that have already been emailed
        $statQb = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('stat.lead_id')
            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'stat');

        if ($variantIds) {
            if (!in_array($emailId, $variantIds)) {
                $variantIds[] = (int) $emailId;
            }
            $statQb->where($statQb->expr()->in('stat.email_id', $variantIds));
            $messageExpr->add(
                $mqQb->expr()->in('mq.channel_id', $variantIds)
            );
        } else {
            $statQb->where($statQb->expr()->eq('stat.email_id', (int) $emailId));
            $messageExpr->add(
                $mqQb->expr()->eq('mq.channel_id', (int) $emailId)
            );
        }
        $statQb->andWhere($statQb->expr()->isNotNull('stat.lead_id'));
        $mqQb->where($messageExpr);

        // Only include those who belong to the associated lead lists
//        if (is_null($listIds)) {
//            // Get a list of lists associated with this email
//            $lists = $this->getEntityManager()->getConnection()->createQueryBuilder()
//                ->select('el.leadlist_id')
//                ->from(MAUTIC_TABLE_PREFIX.'email_list_xref', 'el')
//                ->where('el.email_id = '.(int) $emailId)
//                ->execute()
//                ->fetchAll();
//
//            $listIds = [];
//            foreach ($lists as $list) {
//                $listIds[] = $list['leadlist_id'];
//            }
//
//            if (empty($listIds)) {
//                // Prevent fatal error
//                return ($countOnly) ? 0 : [];
//            }
//        } elseif (!is_array($listIds)) {
//            $listIds = [$listIds];
//        }

        // Main query
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();
        if ($countOnly) {
            // distinct with an inner join seems faster
            $q->select('count(distinct(l.id)) as count');
//            $q->innerJoin('l', MAUTIC_TABLE_PREFIX.'lead_lists_leads', 'll',
//                $q->expr()->andX(
//                    $q->expr()->in('ll.leadlist_id', $listIds),
//                    $q->expr()->eq('ll.lead_id', 'l.id'),
//                    $q->expr()->eq('ll.manually_removed', ':false')
//                )
//            );

            if ($countWithMaxMin) {
                $q->addSelect('MIN(l.id) as min_id');
                $q->addSelect('MAX(l.id) as max_id');
            }
        } else {
            $q->select('l.*');

            // use a derived table in order to retrieve distinct leads in case they belong to multiple
            // lead lists associated with this email
//            $listQb = $this->getEntityManager()->getConnection()->createQueryBuilder();
//            $listQb->select('distinct(ll.lead_id) lead_id')
//                ->from(MAUTIC_TABLE_PREFIX.'lead_lists_leads', 'll')
//                ->where(
//                    $listQb->expr()->andX(
//                        $listQb->expr()->in('ll.leadlist_id', $listIds),
//                        $listQb->expr()->eq('ll.manually_removed', ':false')
//                    )
//                );
//
//            $listQb = $this->setMinMaxIds($listQb, 'll.lead_id', $minContactId, $maxContactId);
//
//            $q->innerJoin('l', sprintf('(%s)', $listQb->getSQL()), 'in_list', 'l.id = in_list.lead_id');
        }

        $dncQb  = $this->setMinMaxIds($dncQb, 'dnc.lead_id', $minContactId, $maxContactId);
        $mqQb   = $this->setMinMaxIds($mqQb, 'mq.lead_id', $minContactId, $maxContactId);
        $statQb = $this->setMinMaxIds($statQb, 'stat.lead_id', $minContactId, $maxContactId);

        $email = $this->getEntity($emailId);
        if ($email instanceof Email) {
            if ($email->getEmailType() == 'list') {
                $leadlistRepo = $this->getEntityManager()->getRepository('MauticLeadBundle:LeadList');
                if (isset($email->getRecipients()['filters']) && !empty($email->getRecipients()['filters'])) {
                    $parameters   =[];
                    $expr         = $leadlistRepo->generateSegmentExpression($email->getRecipients()['filters'], $parameters, $q, null, null, false, 'l', null);
                    if ($expr->count()) {
                        $q->andWhere($expr);
                    }
                    unset($parameters);
                } else {
                    return ($countOnly) ? 0 : [];
                }
            } else {
                return ($countOnly) ? 0 : [];
            }
        }
        $q->from(MAUTIC_TABLE_PREFIX.'leads', 'l')
            ->andWhere(sprintf('l.id NOT IN (%s)', $dncQb->getSQL()))
            ->andWhere(sprintf('l.id NOT IN (%s)', $statQb->getSQL()))
            ->andWhere(sprintf('l.id NOT IN (%s)', $mqQb->getSQL()))
            ->setParameter('false', false, 'boolean');

        $q = $this->setMinMaxIds($q, 'l.id', $minContactId, $maxContactId);

        // Has an email
        $q->andWhere(
            $q->expr()->andX(
                $q->expr()->isNotNull('l.email'),
                $q->expr()->neq('l.email', $q->expr()->literal(''))
            )
        );

        if (!empty($limit)) {
            $q->setFirstResult(0)
                ->setMaxResults($limit);
        }

        return $q;
    }

    /**
     * @param      $emailId
     * @param null $variantIds
     * @param null $listIds
     * @param bool $countOnly
     * @param null $limit
     * @param int  $minContactId
     * @param int  $maxContactId
     * @param bool $countWithMaxMin
     *
     * @return array|int
     */
    public function getEmailPendingLeads(
        $emailId,
        $variantIds = null,
        $listIds = null,
        $countOnly = false,
        $limit = null,
        $minContactId = null,
        $maxContactId = null,
        $countWithMaxMin = false
    ) {
        $q = $this->getEmailPendingQuery(
            $emailId,
            $variantIds,
            $listIds,
            $countOnly,
            $limit,
            $minContactId,
            $maxContactId,
            $countWithMaxMin
        );

        if (!($q instanceof QueryBuilder)) {
            return $q;
        }

        $results = $q->execute()->fetchAll();

        if ($countOnly && $countWithMaxMin) {
            // returns array in format ['count' => #, ['min_id' => #, 'max_id' => #]]
            return $results[0];
        } elseif ($countOnly) {
            return (isset($results[0])) ? $results[0]['count'] : 0;
        } else {
            $leads = [];
            foreach ($results as $r) {
                $leads[$r['id']] = $r;
            }

            return $leads;
        }
    }

    /**
     * @param string $search
     * @param int    $limit
     * @param int    $start
     * @param bool   $viewOther
     * @param bool   $topLevel
     * @param null   $emailType
     * @param array  $ignoreIds
     * @param null   $variantParentId
     *
     * @return array
     */
    public function getEmailList($search = '', $limit = 10, $start = 0, $viewOther = false, $topLevel = false, $emailType = null, array $ignoreIds = [], $variantParentId = null)
    {
        $q = $this->createQueryBuilder('e');
        $q->select('partial e.{id, subject, name, language}');

        if (!empty($search)) {
            if (is_array($search)) {
                $search = array_map('intval', $search);
                $q->andWhere($q->expr()->in('e.id', ':search'))
                    ->setParameter('search', $search);
            } else {
                $q->andWhere($q->expr()->like('e.name', ':search'))
                    ->setParameter('search', "%{$search}%");
            }
        }
        $q->andWhere($q->expr()->eq('e.isPublished', ':val'))
            ->setParameter('val', '1');

        if (!$viewOther) {
            $q->andWhere($q->expr()->eq('e.createdBy', ':id'))
                ->setParameter('id', $this->currentUser->getId());
        }
        if ($this->currentUser->getId() != 1) {
            $q->andWhere($q->expr()->neq('e.createdBy', ':id'))
                ->setParameter('id', '1');
        }
        if ($topLevel) {
            if (true === $topLevel || $topLevel == 'variant') {
                $q->andWhere($q->expr()->isNull('e.variantParent'));
            } elseif ($topLevel == 'translation') {
                $q->andWhere($q->expr()->isNull('e.translationParent'));
            }
        }

        if ($variantParentId) {
            $q->andWhere(
                $q->expr()->andX(
                    $q->expr()->eq('IDENTITY(e.variantParent)', (int) $variantParentId),
                    $q->expr()->eq('e.id', (int) $variantParentId)
                )
            );
        }

        if (!empty($ignoreIds)) {
            $q->andWhere($q->expr()->notIn('e.id', ':emailIds'))
                ->setParameter('emailIds', $ignoreIds);
        }

        if (!empty($emailType)) {
            $q->andWhere(
                $q->expr()->eq('e.emailType', $q->expr()->literal($emailType))
            );
        }

        $q->orderBy('e.name');

        if (!empty($limit)) {
            $q->setFirstResult($start)
                ->setMaxResults($limit);
        }

        return $q->getQuery()->getArrayResult();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|QueryBuilder $q
     * @param                                         $filter
     *
     * @return array
     */
    protected function addCatchAllWhereClause($q, $filter)
    {
        return $this->addStandardCatchAllWhereClause($q, $filter, [
            'e.name',
            'e.subject',
            'c.title',
        ]);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|QueryBuilder $q
     * @param                                         $filter
     *
     * @return array
     */
    protected function addSearchCommandWhereClause($q, $filter)
    {
        list($expr, $parameters) = $this->addStandardSearchCommandWhereClause($q, $filter);
        if ($expr) {
            return [$expr, $parameters];
        }

        $command         = $filter->command;
        $unique          = $this->generateRandomParameterName();
        $returnParameter = false; //returning a parameter that is not used will lead to a Doctrine error

        switch ($command) {
            case $this->translator->trans('mautic.core.searchcommand.lang'):
                $langUnique      = $this->generateRandomParameterName();
                $langValue       = $filter->string.'_%';
                $forceParameters = [
                    $langUnique => $langValue,
                    $unique     => $filter->string,
                ];
                $expr = $q->expr()->orX(
                    $q->expr()->eq('e.language', ":$unique"),
                    $q->expr()->like('e.language', ":$langUnique")
                );
                $returnParameter = true;
                break;
        }

        if ($expr && $filter->not) {
            $expr = $q->expr()->not($expr);
        }

        if (!empty($forceParameters)) {
            $parameters = $forceParameters;
        } elseif ($returnParameter) {
            $string     = ($filter->strict) ? $filter->string : "%{$filter->string}%";
            $parameters = ["$unique" => $string];
        }

        return [$expr, $parameters];
    }

    /**
     * @return array
     */
    public function getSearchCommands()
    {
        $commands = [
            'mautic.core.searchcommand.ispublished',
            'mautic.core.searchcommand.isunpublished',
            'mautic.core.searchcommand.isuncategorized',
            'mautic.core.searchcommand.ismine',
            'mautic.core.searchcommand.category',
            'mautic.core.searchcommand.lang',
        ];

        return array_merge($commands, parent::getSearchCommands());
    }

    /**
     * @return string
     */
    protected function getDefaultOrder()
    {
        return [
            ['e.name', 'ASC'],
        ];
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'e';
    }

    /**
     * Resets variant_start_date, variant_read_count, variant_sent_count.
     *
     * @param $relatedIds
     * @param $date
     */
    public function resetVariants($relatedIds, $date)
    {
        if (!is_array($relatedIds)) {
            $relatedIds = [(int) $relatedIds];
        }

        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $qb->update(MAUTIC_TABLE_PREFIX.'emails')
            ->set('variant_read_count', 0)
            ->set('variant_sent_count', 0)
            ->set('variant_start_date', ':date')
            ->setParameter('date', $date)
            ->where(
                $qb->expr()->in('id', $relatedIds)
            )
            ->execute();
    }

    /**
     * Up the read/sent counts.
     *
     * @param            $id
     * @param string     $type
     * @param int        $increaseBy
     * @param bool|false $variant
     */
    public function upCount($id, $type = 'sent', $increaseBy = 1, $variant = false)
    {
        if (!$increaseBy) {
            return;
        }

        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $q->update(MAUTIC_TABLE_PREFIX.'emails')
            ->set($type.'_count', $type.'_count + '.(int) $increaseBy)
            ->where('id = '.(int) $id);

        if ($variant) {
            $q->set('variant_'.$type.'_count', 'variant_'.$type.'_count + '.(int) $increaseBy);
        }

        $q->execute();
    }

    /**
     * Up the read/sent counts.
     *
     * @param            $id
     * @param string     $type
     * @param int        $increaseBy
     * @param bool|false $variant
     */
    public function upDownSentCount($id, $type = 'sent', $increaseBy = 1, $variant = false)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $q->update(MAUTIC_TABLE_PREFIX.'emails')
            ->set($type.'_count', $type.'_count - '.(int) $increaseBy)
            ->where('id = '.(int) $id);

        if ($variant) {
            $q->set('variant_'.$type.'_count', 'variant_'.$type.'_count + '.(int) $increaseBy);
        }

        $q->execute();
    }

    /**
     * Up the failure counts.
     *
     * @param            $id
     * @param string     $type
     * @param int        $increaseBy
     * @param bool|false $variant
     */
    public function upFailureCount($id, $type = 'failure', $increaseBy = 1, $variant = false)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $q->update(MAUTIC_TABLE_PREFIX.'emails')
            ->set($type.'_count', $type.'_count + '.(int) $increaseBy)
            ->where('id = '.(int) $id);

        if ($variant) {
            $q->set('variant_'.$type.'_count', 'variant_'.$type.'_count + '.(int) $increaseBy);
        }

        $q->execute();
    }

    /**
     * Up the Unsubscribe counts.
     *
     * @param            $id
     * @param string     $type
     * @param int        $increaseBy
     * @param bool|false $variant
     */
    public function upUnsubscribeCount($id, $type = 'unsubscribe', $increaseBy = 1, $variant = false)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $q->update(MAUTIC_TABLE_PREFIX.'emails')
            ->set($type.'_count', $type.'_count + '.(int) $increaseBy)
            ->where('id = '.(int) $id);

        if ($variant) {
            $q->set('variant_'.$type.'_count', 'variant_'.$type.'_count + '.(int) $increaseBy);
        }

        $q->execute();
    }

    /**
     * Up the Unsubscribe counts.
     *
     * @param            $id
     * @param string     $type
     * @param int        $increaseBy
     * @param bool|false $variant
     */
    public function downUnsubscribeCount($id, $type = 'unsubscribe', $decreaseBy = 1, $variant = false)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $q->update(MAUTIC_TABLE_PREFIX.'emails')
            ->set($type.'_count', $type.'_count - '.(int) $decreaseBy)
            ->where('id = '.(int) $id);

        if ($variant) {
            $q->set('variant_'.$type.'_count', 'variant_'.$type.'_count + '.(int) $decreaseBy);
        }
        $q->execute();
    }

    public function downUnsubscribeStat($email_id, $leadid)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $q->update(MAUTIC_TABLE_PREFIX.'email_stats')
            ->set('is_unsubscribe', 0)
            ->andwhere($q->expr()->eq('email_id', $email_id),
                $q->expr()->eq('lead_id', $leadid));
        $q->execute();
    }

    /**
     * Up the Bounce counts.
     *
     * @param            $id
     * @param string     $type
     * @param int        $increaseBy
     * @param bool|false $variant
     */
    public function upBounceCount($id, $type = 'bounce', $increaseBy = 1, $variant = false)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $q->update(MAUTIC_TABLE_PREFIX.'emails')
            ->set($type.'_count', $type.'_count + '.(int) $increaseBy)
            ->where('id = '.(int) $id);

        if ($variant) {
            $q->set('variant_'.$type.'_count', 'variant_'.$type.'_count + '.(int) $increaseBy);
        }

        $q->execute();
    }

    /**
     * Up the Spam counts.
     *
     * @param            $id
     * @param string     $type
     * @param int        $increaseBy
     * @param bool|false $variant
     */
    public function upSpamCount($id, $type = 'spam', $increaseBy = 1, $variant = false)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $q->update(MAUTIC_TABLE_PREFIX.'emails')
            ->set($type.'_count', $type.'_count + '.(int) $increaseBy)
            ->where('id = '.(int) $id);

        if ($variant) {
            $q->set('variant_'.$type.'_count', 'variant_'.$type.'_count + '.(int) $increaseBy);
        }

        $q->execute();
    }

    /**
     * @param null $id
     *
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getPublishedBroadcasts($id = null)
    {
        $qb   = $this->createQueryBuilder($this->getTableAlias());
        $expr = $this->getPublishedByDateExpression($qb, null, true, true, false);

        $expr->add(
            $qb->expr()->eq($this->getTableAlias().'.emailType', $qb->expr()->literal('list'))
        );

        if (!empty($id)) {
            $expr->add(
                $qb->expr()->eq($this->getTableAlias().'.id', (int) $id)
            );
        }
        $qb->where($expr);

        return $qb->getQuery()->iterate();
    }

    /**
     * Set Max and/or Min ID where conditions to the query builder.
     *
     * @param QueryBuilder $q
     * @param string       $column
     * @param int          $minContactId
     * @param int          $maxContactId
     *
     * @return QueryBuilder
     */
    private function setMinMaxIds(QueryBuilder $q, $column, $minContactId, $maxContactId)
    {
        if ($minContactId && is_numeric($minContactId)) {
            $q->andWhere($column.' >= :minContactId');
            $q->setParameter('minContactId', $minContactId);
        }

        if ($maxContactId && is_numeric($maxContactId)) {
            $q->andWhere($column.' <= :maxContactId');
            $q->setParameter('maxContactId', $maxContactId);
        }

        return $q;
    }

    public function updateLeadDetails($FirstName, $LastName, $newEmailAddress, $leadId)
    {
        $q   = $this->_em->getConnection()->createQueryBuilder();
        $q->update(MAUTIC_TABLE_PREFIX.'leads')
            ->set('email', ':email')
            ->set('firstname', ':firstname')
            ->set('lastname', ':lastname')
            ->where('id = '.$leadId)
            ->setParameter('email', $newEmailAddress)
            ->setParameter('firstname', $FirstName)
            ->setParameter('lastname', $LastName)->execute();
    }

    /**
     * Get sent counts based on date(Last 30 Days).
     *
     * @param array $emailIds
     *
     * @return array
     */
    public function getLast30DaysSentCounts($viewOthers =false)
    {
        $fromdate = date('Y-m-d', strtotime('-29 days'));

        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count( e.id) as sentcount')
            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'es')
            ->leftJoin('es', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = es.email_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('es.is_failed', ':false')
                )
            )->setParameter('false', false, 'boolean');

        $q->andWhere('e.email_type = :emailType')
            ->setParameter('emailType', 'list');

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
    public function getLast30DaysOpensCounts($viewOthers =false)
    {
        $fromdate = date('Y-m-d', strtotime('-29 days'));

        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select(' count(e.id) as opencount')
            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'es')
            ->leftJoin('es', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = es.email_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('es.is_failed', ':false')
                )
            )->setParameter('false', false, 'boolean');

        $q->andWhere('e.email_type = :emailType')
            ->setParameter('emailType', 'list');

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

    public function getUnsubscribeCount($viewOthers = false)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count(e.id) as unsubscribecount')
            ->from(MAUTIC_TABLE_PREFIX.'email_stats', 'es')
            ->leftJoin('es', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = es.email_id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->eq('es.is_failed', ':false')
                )
            )->setParameter('false', false, 'boolean');

        $q->orWhere('e.email_type = :emailType')
            ->setParameter('emailType', 'list');

        $q->andWhere(
            $q->expr()->eq('es.is_unsubscribe', 1)
        );

        $q->orWhere(
            $q->expr()->eq('es.is_spam', 1)
        );

        $q->andWhere(
            $q->expr()->eq('es.is_bounce', 1)
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

    /**
     * Get open counts based on date.
     *
     * @param array $emailIds
     *
     * @return array
     */
    public function getLast30DaysClickCounts($viewOthers =false)
    {
        /*  $dateinterval = date('Y-m-d', strtotime('-29 days'));
          $q            = $this->getEntityManager()->getConnection()->createQueryBuilder();

          $q->select('SUM(t.hits)')
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
              ->andWhere($q->expr()->gte('r.date_added', ':dateAdded'))
              ->setParameter('dateAdded', $dateinterval)
              ->orderBy('r.url');

          if (!$viewOthers) {
              $q->andWhere($q->expr()->eq('r.created_by', ':currentUserId'))
                  ->setParameter('currentUserId', $this->currentUser->getId());
          }

          if ($this->currentUser->getId() != 1) {
              $q->andWhere($q->expr()->neq('r.created_by', ':id'))
                  ->setParameter('id', '1');
          }

          $results = $q->execute()->fetchAll();

          return (isset($results[0]['SUM(t.hits)'])) ? $results[0]['SUM(t.hits)'] : 0;*/
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
        $q->andWhere($q->expr()->eq('e.email_type', ':emailType'))
            ->setParameter('emailType', 'list');
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

    /**
     * @param $dripid
     *
     * @return array
     */
    public function getEmailIdsByDripid($dripid)
    {
        $q            = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $q->select('id')
            ->from(MAUTIC_TABLE_PREFIX.'emails', 'e')
            ->andWhere($q->expr()->eq('e.dripemail_id', ':dripemail_id'))
            ->setParameter('dripemail_id', $dripid);
        $emails = $q->execute()->fetchAll();
        foreach ($emails as $email) {
            $emailids[]=$email['id'];
        }

        return $emailids;
    }

    /**
     * Get amounts of emails for Drip.
     *
     * @return array
     */
    public function getDripEmailCount()
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $q->select('count(*) as emailCount, e.dripemail_id as DripEmail')
            ->from(MAUTIC_TABLE_PREFIX.'emails', 'e');
        $q->andWhere($q->expr()->isNotNull('e.dripemail_id'));
        $q->addGroupBy('e.dripemail_id');
        $results   = $q->execute()->fetchAll();
        $newResult = [];
        foreach ($results as $key => $value) {
            $newResult[$value['DripEmail']] = $value['emailCount'];
        }

        return $newResult;
    }

    public function getLinkedEmailsStatus($emailId)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $q->select('count(id) as totalCount')
            ->from(MAUTIC_TABLE_PREFIX.'emails', 'e')
            ->andWhere($q->expr()->eq('e.from_address', ':fromAddress'))
            ->setParameter('fromAddress', $emailId);

        $results = $q->execute()->fetchAll();

        $totalCount =  $results[0]['totalCount'];
        if ($totalCount > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getLinkedEmailsVerificationStatus($emailId)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $q->select('verification_status as verificationStatus')
            ->from(MAUTIC_TABLE_PREFIX.'awsverifiedemails', 'aws')
            ->andWhere($q->expr()->eq('aws.verified_emails', ':email'))
            ->setParameter('email', $emailId);

        $results = $q->execute()->fetchAll();

        return  $results[0]['verificationStatus'];
    }
}
