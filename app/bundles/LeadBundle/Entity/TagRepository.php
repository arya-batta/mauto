<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * Class TagRepository.
 */
class TagRepository extends CommonRepository
{
    /**
     * Delete orphan tags that are not associated with any lead.
     */
    public function deleteOrphans()
    {
        $qb       = $this->_em->getConnection()->createQueryBuilder();
        $havingQb = $this->_em->getConnection()->createQueryBuilder();

        $havingQb->select('count(x.lead_id) as the_count')
            ->from(MAUTIC_TABLE_PREFIX.'lead_tags_xref', 'x')
            ->where('x.tag_id = t.id');

        $qb->select('t.id')
            ->from(MAUTIC_TABLE_PREFIX.'lead_tags', 't')
            ->having(sprintf('(%s)', $havingQb->getSQL()).' = 0');
        $delete = $qb->execute()->fetch();

        if (count($delete)) {
            $qb->resetQueryParts();
            $qb->delete(MAUTIC_TABLE_PREFIX.'lead_tags')
                ->where(
                    $qb->expr()->in('id', $delete)
                )
                ->execute();
        }
    }

    /**
     * Get tag entities by name.
     *
     * @param array $tags
     *
     * @return array
     */
    public function getTagsByName(array $tags)
    {
        if (empty($tags)) {
            return [];
        }

        $tags = $this->removeMinusFromTags($tags);
        $qb   = $this->createQueryBuilder('t', 't.tag');

        if ($tags) {
            $qb->where(
                $qb->expr()->in('t.tag', ':tags')
            )
                ->setParameter('tags', $tags);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Goes through each element in the array expecting it to be a tag label and removes the '-' character infront of it.
     * The minus character is used to identify that the tag should be removed.
     *
     * @param array $tags
     *
     * @return array
     */
    public function removeMinusFromTags(array $tags)
    {
        return array_map(function ($val) {
            return (strpos($val, '-') === 0) ? substr($val, 1) : $val;
        }, $tags);
    }

    /**
     * Check Lead tags by Ids.
     *
     * @param Lead $lead
     * @param $tags
     *
     * @return bool
     */
    public function checkLeadByTags(Lead $lead, $tags)
    {
        if (empty($tags)) {
            return false;
        }

        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('l.id')
            ->from(MAUTIC_TABLE_PREFIX.'leads', 'l')
            ->join('l', MAUTIC_TABLE_PREFIX.'lead_tags_xref', 'x', 'l.id = x.lead_id')
            ->join('l', MAUTIC_TABLE_PREFIX.'lead_tags', 't', 'x.tag_id = t.id')
            ->where(
                $q->expr()->andX(
                    $q->expr()->in('t.tag', ':tags'),
                    $q->expr()->eq('l.id', ':leadId')
                )
            )
            ->setParameter('tags', $tags, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
            ->setParameter('leadId', $lead->getId());

        return (bool) $q->execute()->fetchColumn();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function checkNumericTag($name)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('l.tag')
          ->from(MAUTIC_TABLE_PREFIX.'lead_tags', 'l')
          ->where($q->expr()->eq('l.tag', ':newtag'))
            ->setParameter('newtag', $name);

        return (bool) $q->execute()->fetchColumn();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function checkForExistingNumericId($name)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('l.id')
            ->from(MAUTIC_TABLE_PREFIX.'lead_tags', 'l')
            ->where($q->expr()->eq('l.id', ':newtagid'))
            ->setParameter('newtagid', $name);

        return (bool) $q->execute()->fetchColumn();
    }

    /**
     * @param string $name
     *
     * @return Tag
     */
    public function getTagByNameOrCreateNewOne($name)
    {
        $tag = $this->findOneBy(
            [
                'tag' => $name,
            ]
        );

        if (!$tag) {
            $tag = new Tag($name);
            $tag->setIsPublished(1);
            $alias=str_replace(' ', '_', $tag->getTag());
            $tag->setAlias($alias);
        }

        return $tag;
    }

    /**
     * @param $tagIds
     *
     * @return array|mixed
     */
    public function getLeadCount($tagIds)
    {
        $q = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $q->select('count(x.lead_id) as thecount, x.tag_id')
            ->from(MAUTIC_TABLE_PREFIX.'lead_tags_xref', 'x');
        $returnArray = (is_array($tagIds));
        if (!$returnArray) {
            $tagIds = [$tagIds];
        }
        $q->groupBy('x.tag_id');
        $result = $q->execute()->fetchAll();
        $return = [];
        foreach ($result as $r) {
            $return[$r['tag_id']] = $r['thecount'];
        }
        // Ensure lists without leads have a value
        foreach ($tagIds as $l) {
            if (!isset($return[$l])) {
                $return[$l] = 0;
            }
        }

        return ($returnArray) ? $return : $return[$tagIds[0]];
    }

    /**
     * @param bool $viewOthers
     *
     * @return mixed
     */
    public function getTotalTagsCount($viewOthers = false)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count(*) as totaltags')
            ->from(MAUTIC_TABLE_PREFIX.'lead_tags', 'l');
        /*  if (!$viewOthers) {
              $q->andWhere($q->expr()->eq('l.created_by', ':currentUserId'))
                  ->setParameter('currentUserId', $this->currentUser->getId());
          }
          if ($this->currentUser->getId() != 1) {
              $q->andWhere($q->expr()->neq('l.created_by', ':id'))
                  ->setParameter('id', '1');
          }*/
        $results = $q->execute()->fetchAll();

        return $results[0]['totaltags'];
    }

    /**
     * @param bool $viewOthers
     *
     * @return mixed
     */
    public function getTotalActiveTagsCount($viewOthers = false)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();

        $q->select('count(*) as activetags')
            ->from(MAUTIC_TABLE_PREFIX.'lead_tags', 'l')
            ->andWhere('l.is_published = 1 ');

        /*  if (!$viewOthers) {
              $q->andWhere($q->expr()->eq('l.created_by', ':currentUserId'))
                  ->setParameter('currentUserId', $this->currentUser->getId());
          }

          if ($this->currentUser->getId() != 1) {
              $q->andWhere($q->expr()->neq('l.created_by', ':id'))
                  ->setParameter('id', '1');
          }*/

        $results = $q->execute()->fetchAll();

        return $results[0]['activetags'];
    }

    /**
     * @param bool $viewOthers
     *
     * @return mixed
     */
    public function getTotalInactiveTagsCount($viewOthers = false)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('count(*) as inactivetags')
            ->from(MAUTIC_TABLE_PREFIX.'lead_tags', 'l')
            ->andWhere('l.is_published != 1 ');

        /* if (!$viewOthers) {
             $q->andWhere($q->expr()->eq('l.created_by', ':currentUserId'))
                 ->setParameter('currentUserId', $this->currentUser->getId());
         }

         if ($this->currentUser->getId() != 1) {
             $q->andWhere($q->expr()->neq('l.created_by', ':id'))
                 ->setParameter('id', '1');
         }*/

        $results = $q->execute()->fetchAll();

        return $results[0]['inactivetags'];
    }

    public function deleteRefLead($id)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->delete(MAUTIC_TABLE_PREFIX.'lead_tags_xref')
            ->andWhere($q->expr()->eq('tag_id', ':tag_id'))
            ->setParameter('tag_id', $id);
        $q->execute();
    }

    /**
     * Return a list of global lists.
     *
     * @return array
     */
    public function getTagsList()
    {
        $q = $this->getEntityManager()->createQueryBuilder()
            ->from('MauticLeadBundle:Tag', 't');

        $q->select('t.id as id, t.tag as name')
            ->where($q->expr()->eq('t.isPublished', 1))
            ->orderBy('t.tag');
        $results = $q->getQuery()->getArrayResult();

        return $results;
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 't';
    }
}
