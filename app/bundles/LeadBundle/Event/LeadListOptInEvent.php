<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadListOptIn;

/**
 * Class LeadListOptInEvent.
 */
class LeadListOptInEvent extends CommonEvent
{
    private $lead;
    private $listId;
    private $bulkLeads      =[];
    private $isBulkOperation=false;

    /**
     * @param LeadListOptIn $list
     * @param bool          $isNew
     * @param Lead          $lead
     */
    public function __construct(LeadListOptIn $list, $isNew = false, Lead $lead, $listId)
    {
        $this->lead   = $lead;
        $this->entity = $list;
        $this->isNew  = $isNew;
        $this->listId = $listId;
    }

    /**
     * @return mixed
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * @param mixed $listId
     */
    public function setListId($listId)
    {
        $this->listId = $listId;
    }

    /**
     * @return Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param Lead $lead
     */
    public function setLead($lead)
    {
        $this->lead = $lead;
    }

    /**
     * @param $flag
     */
    public function setIsBulkOperation($flag)
    {
        $this->isBulkOperation=$flag;
    }

    public function isBulkOperation()
    {
        return $this->isBulkOperation;
    }

    /**
     * @return array
     */
    public function getBulkLeads()
    {
        return $this->bulkLeads;
    }

    /**
     * @param $leads
     */
    public function setBulkLeads($leads)
    {
        $this->bulkLeads = $leads;
    }

    /**
     * Returns the List entity.
     *
     * @return LeadListOptIn
     */
    public function getList()
    {
        return $this->entity;
    }

    /**
     * Sets the List entity.
     *
     * @param LeadListOptIn $list
     */
    public function setList(LeadListOptIn $list)
    {
        $this->entity = $list;
    }

    /**
     * Returns array with lead fields and owner ID if exist.
     *
     * @return array
     */
    public function getLeadFields()
    {
        $lead         = $this->getLead();
        $isLeadEntity = ($lead instanceof Lead);

        // In case Lead is a scalar value:
        if (!$isLeadEntity && !is_array($lead)) {
            $lead = [];
        }

        $leadFields             = $isLeadEntity ? $lead->getProfileFields() : $lead;
        $leadFields['owner_id'] = $isLeadEntity && ($owner = $lead->getOwner()) ? $owner->getId() : 0;

        return $leadFields;
    }
}
