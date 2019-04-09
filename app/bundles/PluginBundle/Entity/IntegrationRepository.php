<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * IntegrationRepository.
 */
class IntegrationRepository extends CommonRepository
{
    public function getIntegrations()
    {
        $services = $this->createQueryBuilder('i')
            ->join('i.plugin', 'p')
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($services as $s) {
            $results[$s->getName()] = $s;
        }

        return $results;
    }

    /**
     * Get core (no plugin) integrations.
     */
    public function getCoreIntegrations()
    {
        $services = $this->createQueryBuilder('i')
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($services as $s) {
            $results[$s->getName()] = $s;
        }

        return $results;
    }

    public function getAllFieldMapping($group, $integration)
    {
        // file_put_contents("/var/www/mauto/payload.txt","Group:".$group."\n",FILE_APPEND);
        //file_put_contents("/var/www/mauto/payload.txt","Integration:".$integration."\n",FILE_APPEND);
        $q = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ifm')
            ->from('MauticPluginBundle:IntegrationFieldMapping', 'ifm', 'ifm.id');
        $q->where(
            $q->expr()->eq('IDENTITY(ifm.integration)', ':integration'),
            $q->expr()->eq('ifm.groupname', ':group'));
        $q->setParameter('integration', $integration);
        $q->setParameter('group', $group);

        return $q->getQuery()->getResult();
    }
}
