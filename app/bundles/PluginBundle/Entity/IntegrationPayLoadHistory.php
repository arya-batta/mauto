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
 * Class IntegrationPayLoadHistory.
 */
class IntegrationPayLoadHistory extends CommonEntity
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
    private $payload;

    /**
     * @var null|\DateTime
     */
    private $createdOn;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('integration_payload_history')
            ->setCustomRepositoryClass('Mautic\PluginBundle\Entity\IntegrationRepository');

        $builder->createField('id', 'integer')
            ->isPrimaryKey()
            ->generatedValue()
            ->build();

        $builder->createManyToOne('integration', 'Integration')
            ->inversedBy('payload')
            ->addJoinColumn('integration_id', 'id', true, false, 'CASCADE')
            ->build();

        $builder->createField('payload', 'text')
            ->columnName('payload')
            ->nullable()
            ->build();

        $builder->createField('createdOn', 'datetime')
            ->columnName('createdOn')
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
     * @param $payload
     */
    public function setPayLoad($payload)
    {
        $this->payload=   $payload;
    }

    /**
     * @return mixed
     */
    public function getPayLoad()
    {
        return $this->payload;
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
     * @param $createdon
     */
    public function setCreatedOn($createdon)
    {
        $this->createdOn=$createdon;
    }

    /**
     * @return mixed
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }
}
