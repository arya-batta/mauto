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

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\CommonEntity;

/**
 * Class IntegrationFieldMapping.
 */
class IntegrationFieldMapping extends CommonEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Integration
     */
    private $integration;

    /**
     * @var string
     */
    private $groupname;

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('integration_field_mapping')
            ->setCustomRepositoryClass('Mautic\PluginBundle\Entity\IntegrationRepository');

        $builder->createField('id', 'integer')
            ->isPrimaryKey()
            ->generatedValue()
            ->build();

        $builder->createManyToOne('integration', 'Integration')
            ->inversedBy('fieldmapping')
            ->addJoinColumn('integration_id', 'id', true, false, 'CASCADE')
            ->build();

        $builder->addField('groupname', 'string');

        $builder->createField('fields', 'array')
            ->columnName('fields')
            ->nullable()
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
     * @param $name
     */
    public function setGroup($name)
    {
        $this->groupname= $name;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->groupname;
    }

    /**
     * @param mixed $integration
     *
     * @return IntegrationFieldMapping
     */
    public function setIntegration($integration)
    {
        $this->integration = $integration;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param mixed $fields
     *
     * @return IntegrationFieldMapping
     */
    public function setFields($fields)
    {
        $this->isChanged('fields', $fields);

        $this->fields = $fields;

        return $this;
    }
}
