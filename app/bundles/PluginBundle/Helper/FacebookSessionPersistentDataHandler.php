<?php

namespace Mautic\PluginBundle\Helper;

use Facebook\PersistentData\PersistentDataInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FacebookSessionPersistentDataHandler implements PersistentDataInterface
{
    const SESSION_PREFIX = 'FBRLH_';
    private $session;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->session->get(self::SESSION_PREFIX.$key);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->session->set(self::SESSION_PREFIX.$key, $value);
    }

    public function getSessionInterface()
    {
        return $this->session;
    }
}
