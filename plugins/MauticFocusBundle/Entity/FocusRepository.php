<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticFocusBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

class FocusRepository extends CommonRepository
{
    /**
     * @param $formId
     *
     * @return array
     */
    public function findByForm($formId)
    {
        return $this->findBy(
            [
                'form' => (int) $formId,
            ]
        );
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
        $alias = $this->getTableAlias();

        $q = $this->_em
            ->createQueryBuilder()
            ->select($alias)
            ->from('MauticFocusBundle:Focus', $alias, $alias.'.id');

        if (empty($args['iterator_mode'])) {
            $q->leftJoin($alias.'.category', 'c');
        }

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $q
     * @param                                                              $filter
     *
     * @return array
     */
    protected function addCatchAllWhereClause($q, $filter)
    {
        return $this->addStandardCatchAllWhereClause($q, $filter, ['f.name', 'f.website']);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $q
     * @param                                                              $filter
     *
     * @return array
     */
    protected function addSearchCommandWhereClause($q, $filter)
    {
        return $this->addStandardSearchCommandWhereClause($q, $filter);
    }

    /**
     * @return array
     */
    public function getSearchCommands()
    {
        return $this->getStandardSearchCommands();
    }

    /**
     * @return string
     */
    protected function getDefaultOrder()
    {
        return [
            [$this->getTableAlias().'.name', 'ASC'],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTableAlias()
    {
        return 'f';
    }

    /**
     * @return array
     */
    public function getFocusList($currentId)
    {
        $q = $this->createQueryBuilder('f');
        $q->select('partial f.{id, name, description}')->orderBy('f.name');

        return $q->getQuery()->getArrayResult();
    }

    public function getTotalPopupCount($viewOthers = false)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();

        $q->select('count(*) as totalcount')
            ->from(MAUTIC_TABLE_PREFIX.'focus', 'f');

        if (!$viewOthers) {
            $q->andWhere($q->expr()->eq('f.created_by', ':currentUserId'))
                ->setParameter('currentUserId', $this->currentUser->getId());
        }

        if ($this->currentUser->getId() != 1) {
            $q->andWhere($q->expr()->neq('f.created_by', ':id'))
                ->setParameter('id', '1');
        }

        $result = $q->execute()->fetchAll();

        return $result[0]['totalcount'];
    }

    public function getTotalActivePopups($viewOthers = false)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();

        $q->select('count(*) as activefocus')
            ->from(MAUTIC_TABLE_PREFIX.'focus', 'f')
            ->andWhere('f.is_published = 1 ');

        if (!$viewOthers) {
            $q->andWhere($q->expr()->eq('f.created_by', ':currentUserId'))
                ->setParameter('currentUserId', $this->currentUser->getId());
        }

        if ($this->currentUser->getId() != 1) {
            $q->andWhere($q->expr()->neq('f.created_by', ':id'))
                ->setParameter('id', '1');
        }

        $result = $q->execute()->fetchAll();

        return $result[0]['activefocus'];
    }
}
