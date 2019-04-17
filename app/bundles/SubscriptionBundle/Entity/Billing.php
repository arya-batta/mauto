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
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class Billing.
 */
class Billing extends FormEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $companyname;

    /**
     * @var string
     */
    private $companyaddress;

    /**
     * @var string
     */
    private $accountingemail;

    /**
     * @var
     */
    private $postalcode;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $gstnumber;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('billinginfo')
                ->setCustomRepositoryClass('Mautic\SubscriptionBundle\Entity\BillingRepository');

        $builder->createField('id', 'integer')
            ->isPrimaryKey()
            ->generatedValue()
            ->build();

        $builder->createField('companyname', 'string')
            ->columnName('companyname')
            ->nullable()
            ->build();

        $builder->createField('companyaddress', 'string')
            ->columnName('companyaddress')
            ->nullable()
            ->build();

        $builder->createField('accountingemail', 'string')
            ->columnName('accountingemail')
            ->nullable()
            ->build();

        $builder->createField('postalcode', 'integer')
            ->columnName('postalcode')
            ->nullable()
            ->build();

        $builder->createField('city', 'string')
            ->columnName('city')
            ->nullable()
            ->build();

        $builder->createField('state', 'string')
            ->columnName('state')
            ->nullable()
            ->build();

        $builder->createField('country', 'string')
            ->columnName('country')
            ->nullable()
            ->build();

        $builder->createField('gstnumber', 'string')
            ->columnName('gstnumber')
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
    public function getCompanyname()
    {
        return $this->companyname;
    }

    /**
     * @param string $accountname
     *
     * @return Account
     */
    public function setCompanyname($accountname)
    {
        $this->companyname = $accountname;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompanyaddress()
    {
        return $this->companyaddress;
    }

    /**
     * @param string $companyaddress
     *
     * @return Account
     */
    public function setCompanyaddress($companyaddress)
    {
        $this->companyaddress = $companyaddress;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountingemail()
    {
        return $this->accountingemail;
    }

    /**
     * @param mixed $email
     *
     * @return Account
     */
    public function setAccountingemail($email)
    {
        $this->accountingemail = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostalcode()
    {
        return $this->postalcode;
    }

    /**
     * @param mixed $postalcode
     *
     * @return Account
     */
    public function setPostalcode($postalcode)
    {
        $this->postalcode = $postalcode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     *
     * @return Billing
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     *
     * @return Billing
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     *
     * @return Billing
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGstnumber()
    {
        return $this->gstnumber;
    }

    /**
     * @param mixed $gstnumber
     *
     * @return Billing
     */
    public function setGstnumber($gstnumber)
    {
        $this->gstnumber = $gstnumber;

        return $this;
    }

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint(
            'companyname',
            new NotBlank(
                [
                    'message' => 'le.subscription.billing.companyname.notblank',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'companyaddress',
            new NotBlank(
                [
                    'message' => 'le.subscription.billing.companyaddress.notblank',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'accountingemail',
            new NotBlank(
                [
                    'message' => 'le.subscription.billing.accountingemail.notblank',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'accountingemail',
            new Email(
                [
                    'message' => 'le.core.email.required',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'postalcode',
            new NotBlank(
                [
                    'message' => 'le.subscription.billing.postalcode.notblank',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'city',
            new NotBlank(
                [
                    'message' => 'le.subscription.billing.city.notblank',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'state',
            new NotBlank(
                [
                    'message' => 'le.subscription.billing.state.notblank',
                ]
            )
        );

        $metadata->addPropertyConstraint(
            'country',
            new NotBlank(
                [
                    'message' => 'le.subscription.billing.country.notblank',
                ]
            )
        );
    }
}
