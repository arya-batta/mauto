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
use Mautic\CoreBundle\Entity\FormEntity;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class Account.
 */
class Account extends FormEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $accountname;

    /**
     * @var string
     */
    private $domainname;

    /**
     * @var
     */
    private $email;

    /**
     * @var
     */
    private $phonenumber;

    /**
     * @var
     */
    private $currencysymbol;

    /**
     * @var
     */
    private $timezone;

    /**
     * @var
     */
    private $accountid;

    /**
     * @var
     */
    private $needpoweredby = 1;

    /**
     * @var
     */
    private $website;

    /**
     * @var
     */
    private $mobileverified = 0;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('accountinfo')
                ->setCustomRepositoryClass('Mautic\SubscriptionBundle\Entity\AccountRepository');

        $builder->createField('id', 'integer')
            ->isPrimaryKey()
            ->generatedValue()
            ->build();

        $builder->createField('accountname', 'string')
            ->columnName('accountname')
            ->nullable()
            ->build();

        $builder->createField('domainname', 'string')
            ->columnName('domainname')
            ->nullable()
            ->build();

        $builder->createField('email', 'string')
            ->columnName('email')
            ->nullable()
            ->build();

        $builder->createField('phonenumber', 'string')
            ->columnName('phonenumber')
            ->nullable()
            ->build();

        $builder->createField('currencysymbol', 'string')
            ->columnName('currencysymbol')
            ->nullable()
            ->build();

        $builder->createField('timezone', 'string')
            ->columnName('timezone')
            ->nullable()
            ->build();

        $builder->createField('accountid', 'string')
            ->columnName('accountid')
            ->nullable()
            ->build();

        $builder->createField('needpoweredby', 'integer')
            ->columnName('needpoweredby')
            ->nullable()
            ->build();

        $builder->createField('website', 'string')
            ->columnName('website')
            ->nullable()
            ->build();
        $builder->createField('mobileverified', 'integer')
            ->columnName('mobileverified')
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
    public function getAccountname()
    {
        return $this->accountname;
    }

    /**
     * @param string $accountname
     *
     * @return Account
     */
    public function setAccountname($accountname)
    {
        $this->accountname = $accountname;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDomainname()
    {
        return $this->domainname;
    }

    /**
     * @param string $domainname
     *
     * @return Account
     */
    public function setDomainname($domainname)
    {
        $this->domainname = $domainname;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhonenumber()
    {
        return $this->phonenumber;
    }

    /**
     * @param mixed $phonenumber
     *
     * @return Account
     */
    public function setPhonenumber($phonenumber)
    {
        $this->phonenumber = $phonenumber;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrencysymbol()
    {
        return $this->currencysymbol;
    }

    /**
     * @param mixed $currenysymbol
     *
     * @return Account
     */
    public function setCurrencysymbol($currenysymbol)
    {
        $this->currencysymbol = $currenysymbol;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     *
     * @return Account
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     *
     * @return Account
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountid()
    {
        return $this->accountid;
    }

    /**
     * @param mixed $accountid
     *
     * @return Account
     */
    public function setAccountid($accountid)
    {
        $this->accountid = $accountid;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNeedpoweredby()
    {
        return $this->needpoweredby;
    }

    /**
     * @param mixed $needpoweredby
     *
     * @return Account
     */
    public function setNeedpoweredby($needpoweredby)
    {
        $this->needpoweredby = $needpoweredby;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param mixed $website
     *
     * @return Account
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMobileverified()
    {
        return $this->mobileverified;
    }

    /**
     * @param mixed $mobileverified
     *
     * @return Billing
     */
    public function setMobileverified($mobileverified)
    {
        $this->mobileverified = $mobileverified;

        return $this;
    }

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint(
            'phonenumber',
            new NotBlank(
                [
                    'message' => 'le.subscription.account.phone.notblank',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'accountname',
            new NotBlank(
                [
                    'message' => 'le.subscription.account.account.notblank',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'domainname',
            new NotBlank(
                [
                    'message' => 'le.subscription.account.domain.notblank',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'timezone',
            new NotBlank(
                [
                    'message' => 'le.subscription.account.timezone.notblank',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'website',
            new NotBlank(
                [
                    'message' => 'le.subscription.account.website.notblank',
                ]
            )
        );
    }
}
