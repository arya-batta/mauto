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

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;

/**
 * Class ListLeadOptIn.
 */
class ListLeadOptIn
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var LeadListOptIn
     **/
    private $list;

    /**
     * @var Lead
     */
    private $lead;

    /**
     * @var \DateTime
     */
    private $dateAdded;

    /**
     * @var bool
     */
    private $manuallyRemoved = false;

    /**
     * @var bool
     */
    private $manuallyAdded = false;

    /*
     *@var bool
     */
    private $confirmedLead;

    /*
     *@var bool
     */
    private $unconfirmedLead;

    /*
     *@var bool
     */
    private $unsubscribedLead;

    /*
     *@var bool
     */
    private $isrescheduled;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('lead_listoptin_leads')
            ->setCustomRepositoryClass('Mautic\LeadBundle\Entity\ListLeadOptInRepository');

        $builder->addId();

        $builder->createManyToOne('list', 'LeadListOptIn')
            ->inversedBy('leads')
            ->addJoinColumn('leadlist_id', 'id', false, false, 'CASCADE')
            ->build();

        $builder->addLead(false, 'CASCADE', false);

        $builder->addDateAdded();

        $builder->createField('manuallyRemoved', 'boolean')
            ->columnName('manually_removed')
            ->build();

        $builder->createField('manuallyAdded', 'boolean')
            ->columnName('manually_added')
            ->build();

        $builder->createField('confirmedLead', 'boolean')
            ->columnName('confirmed_lead')
            ->build();

        $builder->createField('unconfirmedLead', 'boolean')
            ->columnName('unconfirmed_lead')
            ->build();

        $builder->createField('unsubscribedLead', 'boolean')
            ->columnName('unsubscribed_lead')
            ->build();

        $builder->createField('isrescheduled', 'boolean')
            ->columnName('isrescheduled')
            ->build();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @param \DateTime $date
     */
    public function setDateAdded($date)
    {
        $this->dateAdded = $date;
    }

    /**
     * @return mixed
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param mixed $lead
     */
    public function setLead($lead)
    {
        $this->lead = $lead;
    }

    /**
     * @return LeadListOptIn
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @param LeadListOptIn $leadList
     */
    public function setList($leadList)
    {
        $this->list = $leadList;
    }

    /**
     * @return bool
     */
    public function getManuallyRemoved()
    {
        return $this->manuallyRemoved;
    }

    /**
     * @param bool $manuallyRemoved
     */
    public function setManuallyRemoved($manuallyRemoved)
    {
        $this->manuallyRemoved = $manuallyRemoved;
    }

    /**
     * @return bool
     */
    public function wasManuallyRemoved()
    {
        return $this->manuallyRemoved;
    }

    /**
     * @return bool
     */
    public function getManuallyAdded()
    {
        return $this->manuallyAdded;
    }

    /**
     * @param bool $manuallyAdded
     */
    public function setManuallyAdded($manuallyAdded)
    {
        $this->manuallyAdded = $manuallyAdded;
    }

    /**
     * @return bool
     */
    public function wasManuallyAdded()
    {
        return $this->manuallyAdded;
    }

    /**
     * @return mixed
     */
    public function getConfirmedLead()
    {
        return $this->confirmedLead;
    }

    /**
     * @param mixed $confirmedLead
     */
    public function setConfirmedLead($confirmedLead)
    {
        $this->confirmedLead = $confirmedLead;
    }

    /**
     * @return mixed
     */
    public function getUnconfirmedLead()
    {
        return $this->unconfirmedLead;
    }

    /**
     * @param mixed $unconfirmedLead
     */
    public function setUnconfirmedLead($unconfirmedLead)
    {
        $this->unconfirmedLead = $unconfirmedLead;
    }

    /**
     * @return mixed
     */
    public function getUnsubscribedLead()
    {
        return $this->unsubscribedLead;
    }

    /**
     * @param mixed $unsubscribedLead
     */
    public function setUnsubscribedLead($unsubscribedLead)
    {
        $this->unsubscribedLead = $unsubscribedLead;
    }

    /**
     * @return mixed
     */
    public function getIsrescheduled()
    {
        return $this->isrescheduled;
    }

    /**
     * @param mixed $isrescheduled
     */
    public function setIsrescheduled($isrescheduled)
    {
        $this->isrescheduled = $isrescheduled;
    }
}
