<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class LeadListOptIn.
 */
class LeadListOptIn extends FormEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $listtype;

    /**
     * @var bool
     */
    private $thankyou;

    /**
     * @var bool
     */
    private $goodbye;

    /**
     * @var string
     */
    private $doubleoptinemail;

    /**
     * @var string
     */
    private $thankyouemail;

    /**
     * @var string
     */
    private $goodbyeemail;

    /**
     * @var string
     */
    private $footerText;

    /**
     * @var ArrayCollection
     */
    private $leads;

    /**
     * Construct.
     */
    public function __construct()
    {
        $this->leads = new ArrayCollection();
    }

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('lead_listoptin')
            ->setCustomRepositoryClass('Mautic\LeadBundle\Entity\LeadListOptInRepository');

        $builder->addIdColumns();

        $builder->createOneToMany('leads', 'ListLeadOptIn')
            ->setIndexBy('id')
            ->mappedBy('list')
            ->fetchExtraLazy()
            ->build();

        $builder->createField('listtype', 'text')
            ->nullable()
            ->build();

        $builder->createField('thankyou', 'boolean')
            ->columnName('thankyou')
            ->build();

        $builder->createField('goodbye', 'boolean')
            ->columnName('goodbye')
            ->build();

        $builder->createField('doubleoptinemail', 'string')
            ->columnName('doubleoptinemail')
            ->nullable()
            ->build();

        $builder->createField('thankyouemail', 'string')
            ->columnName('thankyouemail')
            ->nullable()
            ->build();

        $builder->createField('goodbyeemail', 'string')
            ->columnName('goodbyeemail')
            ->nullable()
            ->build();

        $builder->createField('footerText', 'string')
            ->columnName('footerText')
            ->nullable()
            ->build();
    }

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Assert\NotBlank(
            ['message' => 'mautic.core.name.required']
        ));
    }

    /**
     * Prepares the metadata for API usage.
     *
     * @param $metadata
     */
    public static function loadApiMetadata(ApiMetadataDriver $metadata)
    {
        $metadata->setGroupPrefix('leadList')
            ->addListProperties(
                [
                    'id',
                    'name',
                    'description',
                ]
            )
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
     * Set name.
     *
     * @param int $name
     *
     * @return LeadList
     */
    public function setName($name)
    {
        $this->isChanged('name', $name);
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return int
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return LeadList
     */
    public function setDescription($description)
    {
        $this->isChanged('description', $description);
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get leads.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLeads()
    {
        return $this->leads;
    }

    /**
     * @return mixed
     */
    public function getListtype()
    {
        return $this->listtype;
    }

    /**
     * @param mixed $listtype
     */
    public function setListtype($listtype)
    {
        $this->listtype = $listtype;
    }

    /**
     * @return mixed
     */
    public function getThankyou()
    {
        return $this->thankyou;
    }

    /**
     * @param mixed $thankyou
     */
    public function setThankyou($thankyou)
    {
        $this->thankyou = $thankyou;
    }

    /**
     * @return bool
     */
    public function isGoodbye()
    {
        return $this->goodbye;
    }

    /**
     * @param bool $goodbye
     */
    public function setGoodbye($goodbye)
    {
        $this->goodbye = $goodbye;
    }

    /**
     * @return string
     */
    public function getDoubleoptinemail()
    {
        return $this->doubleoptinemail;
    }

    /**
     * @param string $doubleoptinemail
     */
    public function setDoubleoptinemail($doubleoptinemail)
    {
        $this->doubleoptinemail = $doubleoptinemail;
    }

    /**
     * @return mixed
     */
    public function getThankyouemail()
    {
        return $this->thankyouemail;
    }

    /**
     * @param mixed $thankyouemail
     */
    public function setThankyouemail($thankyouemail)
    {
        $this->thankyouemail = $thankyouemail;
    }

    /**
     * @return string
     */
    public function getGoodbyeemail()
    {
        return $this->goodbyeemail;
    }

    /**
     * @param string $goodbyeemail
     */
    public function setGoodbyeemail($goodbyeemail)
    {
        $this->goodbyeemail = $goodbyeemail;
    }

    /**
     * @return mixed
     */
    public function getFooterText()
    {
        return $this->footerText;
    }

    /**
     * @param mixed $footerText
     */
    public function setFooterText($footerText)
    {
        $this->footerText = $footerText;
    }

    /**
     * Clone entity with empty contact list.
     */
    public function __clone()
    {
        parent::__clone();

        $this->id    = null;
        $this->leads = new ArrayCollection();
        $this->setIsPublished(false);
    }
}
