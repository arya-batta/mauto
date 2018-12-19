<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use Mautic\EmailBundle\Entity\DripEmail;
use Mautic\EmailBundle\Entity\Email;

/**
 * Class DripEmailEvent.
 */
class DripEmailEvent extends CommonEvent
{
    /**
     * @param DripEmail $email
     * @param bool      $isNew
     */
    public function __construct(DripEmail &$email, $isNew = false)
    {
        $this->entity = &$email;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the DripEmail entity.
     *
     * @return DripEmail
     */
    public function getDrip()
    {
        return $this->entity;
    }

    /**
     * Sets the DripEmail entity.
     *
     * @param DripEmail $email
     */
    public function setDrip(Email $email)
    {
        $this->entity = $email;
    }
}
