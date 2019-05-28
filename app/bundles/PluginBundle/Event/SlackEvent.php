<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use Mautic\PluginBundle\Entity\Slack;

/**
 * Class SlackEvent.
 */
class SlackEvent extends CommonEvent
{
    /**
     * @param Slack $slack
     * @param bool  $isNew
     */
    public function __construct(Slack $slack, $isNew = false)
    {
        $this->entity = $slack;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Slack entity.
     *
     * @return Slack
     */
    public function getSlack()
    {
        return $this->entity;
    }

    /**
     * Sets the Slack entity.
     *
     * @param Slack $slack
     */
    public function setSlack(Slack $slack)
    {
        $this->entity = $slack;
    }
}
