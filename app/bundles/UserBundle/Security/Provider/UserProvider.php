<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\UserBundle\Security\Provider;

use Mautic\CoreBundle\Helper\EncryptionHelper;
use Mautic\CoreBundle\Helper\LicenseInfoHelper;
use Mautic\UserBundle\Entity\PermissionRepository;
use Mautic\UserBundle\Entity\User;
use Mautic\UserBundle\Entity\UserRepository;
use Mautic\UserBundle\Event\UserEvent;
use Mautic\UserBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserProvider.
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var PermissionRepository
     */
    protected $permissionRepository;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var EncoderFactory
     */
    protected $encoder;

    /**
     * @var LicenseInfoHelper
     */
    protected $licenseInfoHelper;

    /**
     * @param UserRepository           $userRepository
     * @param PermissionRepository     $permissionRepository
     * @param Session                  $session
     * @param EventDispatcherInterface $dispatcher
     * @param EncoderFactory           $encoder
     * @param LicenseInfoHelper        $licenseInfoHelper
     */
    public function __construct(
        UserRepository $userRepository,
        PermissionRepository $permissionRepository,
        Session $session,
        EventDispatcherInterface $dispatcher,
        EncoderFactory $encoder,
        LicenseInfoHelper  $licenseInfoHelper
    ) {
        $this->userRepository       = $userRepository;
        $this->permissionRepository = $permissionRepository;
        $this->session              = $session;
        $this->dispatcher           = $dispatcher;
        $this->encoder              = $encoder;
        $this->licenseInfoHelper    = $licenseInfoHelper;
    }

    /**
     * @param string $username
     *
     * @return User
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadUserByUsername($username)
    {
        $q = $this->userRepository
            ->createQueryBuilder('u')
            ->select('u, r')
            ->leftJoin('u.role', 'r')
            ->where('u.username = :username OR u.email = :username')
            ->andWhere('u.isPublished = :true')
            ->setParameter('true', true, 'boolean')
            ->setParameter('username', $username);

        $user = $q->getQuery()->getOneOrNullResult();

        if (empty($user)) {
            $message = sprintf(
                'Unable to find an active admin MauticUserBundle:User object identified by "%s".',
                $username
            );
            throw new UsernameNotFoundException($message, 0);
        }

        //load permissions
        if ($user->getId()) {
            $permissions = $this->permissionRepository->getPermissionsByRole($user->getRole());
            $user->setActivePermissions($permissions);
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        $licenseRemDays = $this->licenseInfoHelper->getLicenseRemainingDays();

        $getAppStatus = $this->licenseInfoHelper->getAppStatus();

        //if ($getAppStatus == 'Active') {
        //if ($licenseRemDays > 0) {
        if ($getAppStatus == 'Suspended' || $getAppStatus == 'InActive') {
            $this->session->set(Security::AUTHENTICATION_ERROR, 'Oops! There seems your account has been disabled since you have not configured your ESP or you haven\'t signed into your account for a while. Please contact us at support@anyfunnels.com to re-activate your account.');
            throw new AuthenticationException();
        } else {
            return $this->loadUserByUsername($user->getUsername());
        }
        //} else {
        //    $this->session->set(Security::AUTHENTICATION_ERROR, 'License Expired Please Contact Support Team');
        //    throw new AuthenticationException();
        //}
        /*} else {
            $this->session->set(Security::AUTHENTICATION_ERROR, 'Your Account Suspended. Please Contact Support');
            throw new AuthenticationException();
        }*/
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $this->userRepository->getClassName() === $class
        || is_subclass_of($class, $this->userRepository->getClassName());
    }

    /**
     * Create/update user from authentication plugins.
     *
     * @param User      $user
     * @param bool|true $createIfNotExists
     *
     * @return User
     *
     * @throws BadCredentialsException
     */
    public function saveUser(User $user, $createIfNotExists = true)
    {
        $isNew = !$user->getId();

        if ($isNew) {
            $user = $this->findUser($user);
            if (!$user->getId() && !$createIfNotExists) {
                throw new BadCredentialsException();
            }
        }

        // Validation for User objects returned by a plugin
        if (!$user->getRole()) {
            throw new AuthenticationException('mautic.integration.sso.error.no_role');
        }

        if (!$user->getUsername()) {
            throw new AuthenticationException('mautic.integration.sso.error.no_username');
        }

        if (!$user->getEmail()) {
            throw new AuthenticationException('mautic.integration.sso.error.no_email');
        }

        if (!$user->getFirstName() || !$user->getLastName()) {
            throw new AuthenticationException('mautic.integration.sso.error.no_name');
        }

        // Check for plain password
        $plainPassword = $user->getPlainPassword();
        if ($plainPassword) {
            // Encode plain text
            $user->setPassword(
                $this->encoder->getEncoder($user)->encodePassword($plainPassword, $user->getSalt())
            );
        } elseif (!$password = $user->getPassword()) {
            // Generate and encode a random password
            $user->setPassword(
                $this->encoder->getEncoder($user)->encodePassword(EncryptionHelper::generateKey(), $user->getSalt())
            );
        }

        $event = new UserEvent($user, $isNew);

        if ($this->dispatcher->hasListeners(UserEvents::USER_PRE_SAVE)) {
            $event = $this->dispatcher->dispatch(UserEvents::USER_PRE_SAVE, $event);
        }

        $this->userRepository->saveEntity($user);

        if ($this->dispatcher->hasListeners(UserEvents::USER_POST_SAVE)) {
            $this->dispatcher->dispatch(UserEvents::USER_POST_SAVE, $event);
        }

        return $user;
    }

    /**
     * @param User $user
     *
     * @return User
     */
    public function findUser(User $user)
    {
        try {
            // Try by username
            $user = $this->loadUserByUsername($user->getUsername());

            return $user;
        } catch (UsernameNotFoundException $exception) {
            // Try by email
            try {
                $user = $this->loadUserByUsername($user->getEmail());

                return $user;
            } catch (UsernameNotFoundException $exception) {
            }
        }

        return $user;
    }
}
