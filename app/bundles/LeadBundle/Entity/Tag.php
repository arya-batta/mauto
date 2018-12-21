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

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Helper\InputHelper;

class Tag
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $tag;
    /**
     * @var boolean
     */
    private $is_published;
    /**
     * @param string
     */
    private $alias;
    public function __construct($tag = null)
    {
        $this->tag = $this->validateTag($tag);
    }

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('lead_tags')
            ->setCustomRepositoryClass(TagRepository::class)
            ->addIndex(['tag'], 'lead_tag_search');

        $builder->addId();
        $builder->addField('tag', Type::STRING);
        $builder->addField('is_published', Type::BOOLEAN);
        $builder->addField('alias',Type::STRING);
    }

    /**
     * @param ApiMetadataDriver $metadata
     */
    public static function loadApiMetadata(ApiMetadataDriver $metadata)
    {
        $metadata->setGroupPrefix('tag')
            ->addListProperties(
                [
                    'id',
                    'tag',
                    'is_published',
                    'alias',
                ]
            )
            ->build();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     *
     * @return Tag
     */
    public function setTag($tag)
    {
        $this->tag = $this->validateTag($tag);

        return $this;
    }

    /**
     * @param string $tag
     *
     * @return Tag
     */
    protected function validateTag($tag)
    {
        return InputHelper::clean($tag);
    }
    /**
     * @return mixed
     */
    public function getisPublished()
    {
        return $this->is_published;
    }

    /**
     * @param mixed $is_published
     */
    public function setIsPublished($is_published)
    {
        $this->is_published = $is_published;
    }

    /**
     * Check the publish status of an entity based on publish up and down datetimes.
     *
     * @return string early|expired|published|unpublished
     *
     * @throws \BadMethodCallException
     */
    public function getPublishStatus()
    {
       if (!$this->getisPublished(false)) {
            return 'unpublished';
        }

        $status = 'published';

        return $status;
    }

    /**
     * @return mixed
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param mixed $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

}
