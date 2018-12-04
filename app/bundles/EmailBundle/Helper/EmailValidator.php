<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Helper;

use Aws\Exception\AwsException;
use Aws\Iam\IamClient;
use Aws\Ses\Exception\SesException;
use Aws\Ses\SesClient;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Entity\AwsConfig;
use Mautic\EmailBundle\Event\EmailValidationEvent;
use Mautic\EmailBundle\Exception\InvalidEmailException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class EmailValidator.
 */
class EmailValidator
{
    /**
     * @var MauticFactory
     */
    private $factory;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * EmailValidator constructor.
     *
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $dispatcher
     * @param MauticFactory            $factory
     */
    public function __construct(TranslatorInterface $translator, EventDispatcherInterface $dispatcher, MauticFactory $factory)
    {
        $this->translator = $translator;
        $this->dispatcher = $dispatcher;
        $this->factory    = $factory;
    }

    /**
     * Validate that an email is the correct format, doesn't have invalid characters, a MX record is associated with the domain, and
     * leverage integrations to validate.
     *
     * @param $address
     * @param bool $doDnsCheck
     *
     * @throws InvalidEmailException
     */
    public function validate($address, $doDnsCheck = false)
    {
        if (!$this->isValidFormat($address)) {
            throw new InvalidEmailException(
                $address,
                $this->translator->trans(
                    'le.email.address.invalid_format',
                    [
                        '%email%' => $address ?: '?',
                    ]
                )
            );
        }

        if ($this->hasValidCharacters($address)) {
            throw new InvalidEmailException(
                $address,
                $this->translator->trans('le.email.address.invalid_characters', ['%email%' => $address])
            );
        }

        if ($doDnsCheck && !$this->hasValidDomain($address)) {
            throw new InvalidEmailException(
                $address,
                $this->translator->trans('le.email.address.invalid_domain', ['%email%' => $address])
            );
        }

        $this->doPluginValidation($address);
    }

    /**
     * Validates that email is in an acceptable format.
     *
     * @param $address
     *
     * @returns bool
     */
    public function isValidFormat($address)
    {
        return !empty($address) && filter_var($address, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validates that email does not have invalid characters.
     *
     * @param $address
     *
     * @returns bool
     */
    public function hasValidCharacters($address)
    {
        $invalidChar = strpbrk($address, '\'^&*%');

        return $invalidChar ? substr($invalidChar, 0, 1) : $invalidChar;
    }

    /**
     * Validates if the domain of an email.
     *
     * @param $address
     *
     * @returns bool
     */
    public function hasValidDomain($address)
    {
        list($user, $domain) = explode('@', $address);

        return checkdnsrr($domain, 'MX');
    }

    /**
     * Validate using 3rd party integrations.
     *
     * @param $address
     *
     * @throws InvalidEmailException
     */
    public function doPluginValidation($address)
    {
        $event = $this->dispatcher->dispatch(
            EmailEvents::ON_EMAIL_VALIDATION,
            new EmailValidationEvent($address)
        );

        if (!$event->isValid()) {
            throw new InvalidEmailException(
                $address,
                $event->getInvalidReason()
            );
        }
    }

    public function getEmailVerificationStatus($key, $secret, $region, $email)
    {
        $regionname            = explode('.', $region);
        $regionname            = $regionname[1];
        try {
            $sesclient             = $this->getSesClient($key, $secret, $regionname);
            $checkifdomainverified = $this->checkDomainVerification($sesclient, $email);
            if ($checkifdomainverified != true) {
                $result = $sesclient->listVerifiedEmailAddresses([
               ]);
                if (in_array($email, $result['VerifiedEmailAddresses'])) {
                    return true;
                } else {
                    $result = $sesclient->getIdentityVerificationAttributes([
                          'Identities' => [
                             $email,
                         ],
                    ]);
                    if (sizeof($result['VerificationAttributes']) > 0) {
                        if ($result['VerificationAttributes'][$email]['VerificationStatus'] != 'Success') {
                            return false;
                        } else {
                            return true;
                        }
                    }
                }
            } else {
                return true;
            }
        } catch (SesException $e) {
            return 'Policy not written';
        }
    }

    public function sendVerificationMail($key, $secret, $region, $email, $awscallbackurl)
    {
        $regionname = explode('.', $region);
        $regionname = $regionname[1];
        /** @var \Mautic\EmailBundle\Model\EmailModel $emailModel */
        $emailModel       = $this->factory->getModel('email');
        // $repo             =$emailModel->getAwsConfigRepository();
        try {
            $sesclient =$this->getSesClient($key, $secret, $regionname);
            //$snsclient =$this->getSnsClient($key, $secret, $regionname);

            $sesclient->verifyEmailAddress([
                         'EmailAddress' => $email,
             ]);
        } catch (SesException $e) {
            return 'Policy not written';
        }
//        try {
//            $bounceArnValue = $this->createBounceTopic($snsclient, $email, $sesclient, $awscallbackurl);
//            $comptopicArn   =  $this->createComplaintTopic($snsclient, $email, $sesclient, $awscallbackurl);
//        } catch (SnsException $e) {
//            return 'Sns Policy not written';
//        }
//
//        $entity = new AwsConfig();
//        if ($entity->getBounceArnValue() != $bounceArnValue) {
//            $entity->setBounceArnValue($bounceArnValue);
//        }
//        if ($entity->getComplaintArnValue() != $comptopicArn) {
//            $entity->setComplaintArnValue($comptopicArn);
//        }
//        $repo->saveEntity($entity);
    }

    public function createBounceTopic($snsclient, $email, $sesclient, $awscallbackurl)
    {
        $bounceResult = $snsclient->createTopic([
            'Name' => 'LE_BOUNCE',
        ]);
        $bouncetopicArn = $bounceResult['TopicArn'];

        $snsclient->subscribe([
            'TopicArn' => $bouncetopicArn,
            'Protocol' => 'HTTP',
            'Endpoint' => $awscallbackurl,
        ]);

        $sesclient->setIdentityNotificationTopic([
            'Identity'         => $email,
            'NotificationType' => 'Bounce',
            'SnsTopic'         => $bouncetopicArn,
        ]);

        return $bouncetopicArn;
    }

    public function createComplaintTopic($snsclient, $email, $sesclient, $awscallbackurl)
    {
        $compResult = $snsclient->createTopic([
              'Name' => 'LE_COMP',
          ]);

        $comptopicArn = $compResult['TopicArn'];

        $snsclient->subscribe([
              'TopicArn' => $comptopicArn,
              'Protocol' => 'HTTP',
              'Endpoint' => $awscallbackurl,
          ]);

        $sesclient->setIdentityNotificationTopic([
              'Identity'         => $email,
              'NotificationType' => 'Complaint',
              'SnsTopic'         => $comptopicArn,
          ]);

        return $comptopicArn;
    }

    public function getVerifiedEmailAddressDetails($key, $secret, $region, $email)
    {
        $regionname = explode('.', $region);
        $regionname = $regionname[1];
        try {
            $sesclient             =$this->getSesClient($key, $secret, $regionname);
            $checkifdomainverified = $this->checkDomainVerification($sesclient, $email);
            if ($checkifdomainverified != true) {
                $result = $sesclient->listVerifiedEmailAddresses([
             ]);
                if (in_array($email, $result['VerifiedEmailAddresses'])) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } catch (SesException $e) {
            return 'Policy not written';
        }
    }

    public function checkDomainVerification($sesclient, $email)
    {
        $domainname       = substr(strrchr($email, '@'), 1);
        try {
            $result = $sesclient->getIdentityVerificationAttributes([
                'Identities' => [
                    $domainname,
                ],
            ]);

            if (sizeof($result['VerificationAttributes']) > 0) {
                if ($result['VerificationAttributes'][$domainname]['VerificationStatus'] != 'Success') {
                    return false;
                } else {
                    return true;
                }
            } else {
                return false;
            }
        } catch (SesException $e) {
            return 'Policy not written';
        }
    }

    public function getSendingStatistics($key, $secret, $region)
    {
        $regionname = explode('.', $region);
        $regionname = $regionname[1];
        try {
            $sesclient = $this->getSesClient($key, $secret, $regionname);
            $result    = $sesclient->getSendQuota([
            ]);
        } catch (SesException $e) {
            return;
        }

        return $result;
    }

    public function getSesClient($key, $secret, $regionname)
    {
        $sesclient     = SesClient::factory([
            'credentials' => ['key' => $key, 'secret' => $secret],
            'region'      => $regionname,
            'version'     => 'latest',
        ]);

        return $sesclient;
    }

    public function getSnsClient($key, $secret, $regionname)
    {
        $snsclient     = SnsClient::factory([
            'credentials' => ['key' => $key, 'secret' => $secret],
            'region'      => $regionname,
            'version'     => 'latest',
        ]);

        return $snsclient;
    }

    public function getVerifiedEmailList($key, $secret, $region)
    {
        $regionname = explode('.', $region);
        $regionname = $regionname[1];
        try {
            $sesclient  =$this->getSesClient($key, $secret, $regionname);
            $result     = $sesclient->listVerifiedEmailAddresses([
        ]);
        } catch (SesException $e) {
            return $e->getMessage();
        }

        return $result['VerifiedEmailAddresses'];
    }

    public function getEmailListAndStatus($key, $secret, $region, $email)
    {
        $regionname = explode('.', $region);
        $regionname = $regionname[1];
        try {
            $sesclient = $this->getSesClient($key, $secret, $regionname);
            $result    = $sesclient->getIdentityVerificationAttributes([
                'Identities' => [
                    $email,
                ],
            ]);
        } catch (SesException $e) {
            return 'Policy not written';
        }

        if (sizeof($result['VerificationAttributes']) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getAwsAccountStatus($key, $secret, $region)
    {
        $regionname = explode('.', $region);
        $regionname = $regionname[1];
        $iAmClient  = IamClient::factory([
            'credentials' => ['key' => $key, 'secret' => $secret],
            'region'      => $regionname,
            'version'     => 'latest',
        ]);
        try {
            $result = $iAmClient->listAccessKeys();
            if ($result['AccessKeyMetadata'][0]['Status'] != 'Active') {
                return false;
            } else {
                return true;
            }
        } catch (AwsException $e) {
            return false;
        }
    }
}
