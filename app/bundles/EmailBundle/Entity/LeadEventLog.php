<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\LeadBundle\Entity\Lead as LeadEntity;

/**
 * Class LeadEventLog.
 */
class LeadEventLog
{
    /**
     * @var
     */
    private $id;

    /**
     * @var LeadEntity
     */
    private $lead;

    /**
     * @var DripEmail
     */
    private $campaign;

    /**
     * @var Email
     */
    private $email;

    /**
     * @var \DateTime
     **/
    private $dateTriggered;

    /**
     * @var bool
     */
    private $isScheduled = false;

    /**
     * @var null|\string
     */
    private $triggerDate;

    /**
     * @var bool
     */
    private $systemTriggered = false;

    /**
     * @var int
     */
    private $rotation = 1;

    /**
     * @var string
     */
    private $failedReason;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('dripemail_lead_event_log')
            ->setCustomRepositoryClass('Mautic\EmailBundle\Entity\LeadEventLogRepository')
            ->addIndex(['lead_id', 'dripemail_id'], 'dripemail_leads');

        $builder->addId();

        $builder->addLead(false, 'CASCADE');

        $builder->addField('rotation', 'integer');

        $builder->createManyToOne('email', 'Email')
            ->addJoinColumn('email_id', 'id')
            ->build();

        $builder->createManyToOne('campaign', 'DripEmail')
            ->addJoinColumn('dripemail_id', 'id')
            ->build();

        $builder->createField('dateTriggered', 'datetime')
            ->columnName('date_triggered')
            ->nullable()
            ->build();

        $builder->createField('isScheduled', 'boolean')
            ->columnName('is_scheduled')
            ->build();

        $builder->createField('triggerDate', 'string')
            ->columnName('trigger_date')
            ->nullable()
            ->build();

        $builder->createField('systemTriggered', 'boolean')
            ->columnName('system_triggered')
            ->build();

        $builder->createField('failedReason', 'string')
            ->columnName('failedReason')
            ->nullable()
            ->build();
    }

    /**
     * Prepares the metadata for API usage.
     *
     * @param $metadata
     */
    public static function loadApiMetadata(ApiMetadataDriver $metadata)
    {
        $metadata->setGroupPrefix('dripEmailLog')
                 ->addProperties(
                     [
                         'dateTriggered',
                         'isScheduled',
                         'triggerDate',
                         'rotation',
                     ]
                 )

                // Add standalone groups
                 ->setGroupPrefix('dripEmailStandaloneLog')
                 ->addProperties(
                     [
                         'lead',
                         'campaign',
                         'dateTriggered',
                         'isScheduled',
                         'triggerDate',
                         'rotation',
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
     * @return \DateTime
     */
    public function getDateTriggered()
    {
        return $this->dateTriggered;
    }

    /**
     * @param \DateTime|null $dateTriggered
     *
     * @return $this
     */
    public function setDateTriggered(\DateTime $dateTriggered = null)
    {
        $this->dateTriggered = $dateTriggered;
        if (null !== $dateTriggered) {
            $this->setIsScheduled(false);
        }

        return $this;
    }

    /**
     * @return LeadEntity
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param LeadEntity $lead
     *
     * @return $this
     */
    public function setLead(LeadEntity $lead)
    {
        $this->lead = $lead;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsScheduled()
    {
        return $this->isScheduled;
    }

    /**
     * @param $isScheduled
     *
     * @return $this
     */
    public function setIsScheduled($isScheduled)
    {
        $this->isScheduled = $isScheduled;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTriggerDate()
    {
        return $this->triggerDate;
    }

    /**
     * @param $triggerDate
     *
     * @return $this
     */
    public function setTriggerDate($triggerDate = null)
    {
        $this->triggerDate = $triggerDate;
        $this->setIsScheduled(true);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param DripEmail $campaign
     *
     * @return $this
     */
    public function setCampaign(DripEmail $campaign)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * @return Email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param Email $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function getSystemTriggered()
    {
        return $this->systemTriggered;
    }

    /**
     * @param $systemTriggered
     *
     * @return $this
     */
    public function setSystemTriggered($systemTriggered)
    {
        $this->systemTriggered = $systemTriggered;

        return $this;
    }

    /**
     * @return int
     */
    public function getRotation()
    {
        return $this->rotation;
    }

    /**
     * @param int $rotation
     *
     * @return LeadEventLog
     */
    public function setRotation($rotation)
    {
        $this->rotation = (int) $rotation;

        return $this;
    }

    /**
     * @return string
     */
    public function getFailedReason()
    {
        return $this->failedReason;
    }

    /**
     * @param string $failedReason
     */
    public function setFailedReason($failedReason)
    {
        $this->failedReason = $failedReason;
    }
}
