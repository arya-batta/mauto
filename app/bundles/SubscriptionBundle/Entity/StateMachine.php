<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SubscriptionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;

/**
 * Class StateMachine.
 */
class StateMachine
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var bool
     */
    private $isalive=false;

    /**
     * @var string
     */
    private $updatedOn;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('statemachine')
                ->setCustomRepositoryClass('Mautic\SubscriptionBundle\Entity\StateMachineRepository');

        $builder->createField('id', 'integer')
            ->isPrimaryKey()
            ->generatedValue()
            ->build();
        $builder->createField('state', 'string')
            ->length(100)
            ->columnName('state')
            ->nullable()
            ->build();
        $builder->createField('reason', 'string')
            ->length(500)
            ->columnName('reason')
            ->nullable()
            ->build();
        $builder->createField('isalive', 'boolean')
            ->columnName('isalive')
            ->nullable()
            ->build();
        $builder->createField('updatedOn', 'datetime')
            ->columnName('updatedOn')
            ->nullable()
            ->build();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param $reason
     *
     * @return $this
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     * @param $updatedOn
     *
     * @return $this
     */
    public function setUpdatedOn($updatedOn)
    {
        $this->updatedOn = $updatedOn;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsAlive()
    {
        return $this->isalive;
    }

    /**
     * @param $flag
     *
     * @return $this
     */
    public function setIsAlive($flag)
    {
        $this->isalive = $flag;

        return $this;
    }
}
