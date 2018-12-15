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

use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Entity\LeadListOptIn;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ListOptInChangeEvent.
 */
class ListOptInChangeEvent extends Event
{
    private $lead;
    private $leads;
    private $list;
    private $added;

    /**
     * ListChangeEvent constructor.
     *
     * @param               $leads
     * @param LeadListOptIn $list
     * @param bool          $added
     */
    public function __construct($leads, LeadListOptIn $list, $added = true)
    {
        if (is_array($leads)) {
            $this->leads = $leads;
        } else {
            $this->lead = $leads;
        }
        $this->list  = $list;
        $this->added = $added;
    }

    /**
     * Returns the Lead entity.
     *
     * @return Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * Returns batch array of leads.
     *
     * @return array
     */
    public function getLeads()
    {
        return $this->leads;
    }

    /**
     * @return LeadListOptIn
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * @return bool
     */
    public function wasAdded()
    {
        return $this->added;
    }

    /**
     * @return bool
     */
    public function wasRemoved()
    {
        return !$this->added;
    }
}
