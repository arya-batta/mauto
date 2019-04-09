<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;

/**
 * Class IntegrationEvent.
 */
class IntegrationEvent extends CommonEvent
{
    /**
     * @var
     */
    private $integrationName;

    /**
     * @var
     */
    private $payload;

    /**
     * @var
     */
    private $isSuccess;

    /**
     * @param string $integrationName
     * @param mixed  $payload
     */
    public function __construct($integrationName, $payload)
    {
        $this->integrationName = $integrationName;
        $this->payload         = $payload;
    }

    /**
     * @return mixed
     */
    public function getIntegrationName()
    {
        return $this->integrationName;
    }

    /**
     * @param mixed $integrationName
     */
    public function setIntegrationName($integrationName)
    {
        $this->integrationName = $integrationName;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param mixed $payload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return mixed
     */
    public function isSuccess()
    {
        return $this->isSuccess;
    }

    /**
     * @param mixed $isSuccess
     */
    public function setIsSuccess($isSuccess)
    {
        $this->isSuccess = $isSuccess;
    }
}
