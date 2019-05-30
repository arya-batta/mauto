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
 * Class StateMachineRepository.
 */
class StateMachineRepository extends CommonRepository
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTableAlias()
    {
        return 'sm';
    }

    public function updateActiveStatesAsInActive($states)
    {
        $stateList = array_map(function ($states) {
            return "'$states'";
        }, $states);
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $qb->update(MAUTIC_TABLE_PREFIX.'statemachine')
            ->set('isalive', ':isalive')
            ->setParameter('isalive', false)
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->in('state', $stateList),
                    $qb->expr()->eq('isalive', true)
                )
            )->execute();
    }
}
