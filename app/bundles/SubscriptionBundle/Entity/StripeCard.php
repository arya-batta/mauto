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
 * Class StripeCard.
 */
class StripeCard
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $customerid;

    /**
     * @var string
     */
    private $last4digit;

    /**
     * @var string
     */
    private $fingerprint;

    /**
     * @var string
     */
    private $brand;

    /**
     * @var string
     */
    private $updatedBy;

    /**
     * @var string
     */
    private $updatedByUser;

    /**
     * @var string
     */
    private $updatedOn;

    /**
     * @var int
     */
    private $expMonth;

    /**
     * @var int
     */
    private $expYear;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('stripecard')
                ->setCustomRepositoryClass('Mautic\SubscriptionBundle\Entity\StripeCardRepository');

        $builder->createField('id', 'integer')
            ->isPrimaryKey()
            ->generatedValue()
            ->build();
        $builder->createField('customerid', 'string')
            ->columnName('customerid')
            ->nullable()
            ->build();
        $builder->createField('last4digit', 'string')
            ->columnName('last4digit')
            ->nullable()
            ->build();
        $builder->createField('fingerprint', 'string')
            ->columnName('fingerprint')
            ->nullable()
            ->build();
        $builder->createField('brand', 'string')
            ->columnName('brand')
            ->nullable()
            ->build();
        $builder->createField('updatedBy', 'integer')
            ->columnName('updatedBy')
            ->nullable()
            ->build();
        $builder->createField('updatedByUser', 'string')
            ->columnName('updatedByUser')
            ->nullable()
            ->build();
        $builder->createField('updatedOn', 'datetime')
            ->columnName('updatedOn')
            ->nullable()
            ->build();
        $builder->createField('expMonth', 'integer')
            ->columnName('expMonth')
            ->nullable()
            ->build();
        $builder->createField('expYear', 'integer')
            ->columnName('expYear')
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
    public function getCustomerID()
    {
        return $this->customerid;
    }

    /**
     * @param string $customerid
     *
     * @return StripeCard
     */
    public function setCustomerID($customerid)
    {
        $this->customerid = $customerid;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getlast4digit()
    {
        return $this->last4digit;
    }

    /**
     * @param string $last4digit
     *
     * @return StripeCard
     */
    public function setlast4digit($last4digit)
    {
        $this->last4digit = $last4digit;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getfingerprint()
    {
        return $this->fingerprint;
    }

    /**
     * @param string $fingerprint
     *
     * @return StripeCard
     */
    public function setfingerprint($fingerprint)
    {
        $this->fingerprint = $fingerprint;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getbrand()
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     *
     * @return StripeCard
     */
    public function setbrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getupdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param string $updatedBy
     *
     * @return StripeCard
     */
    public function setupdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getupdatedByUser()
    {
        return $this->updatedByUser;
    }

    /**
     * @param string $updatedByUser
     *
     * @return StripeCard
     */
    public function setupdatedByUser($updatedByUser)
    {
        $this->updatedByUser = $updatedByUser;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getupdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     * @param string $updatedOn
     *
     * @return StripeCard
     */
    public function setupdatedOn($updatedOn)
    {
        $this->updatedOn = $updatedOn;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpMonth()
    {
        return $this->expMonth;
    }

    /**
     * @param int $month
     *
     * @return StripeCard
     */
    public function setExpMonth($month)
    {
        $this->expMonth = $month;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpYear()
    {
        return $this->expYear;
    }

    /**
     * @param int $year
     *
     * @return StripeCard
     */
    public function setExpYear($year)
    {
        $this->expYear = $year;

        return $this;
    }
}
