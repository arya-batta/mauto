<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Model;

use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\CoreBundle\Helper\LicenseInfoHelper;
use Mautic\EmailBundle\Entity\Stat;
use Mautic\EmailBundle\Entity\StatRepository;
use Mautic\EmailBundle\MonitoredEmail\Search\ContactFinder;
use Mautic\LeadBundle\Entity\DoNotContact as DNC;
use Mautic\LeadBundle\Model\DoNotContact;

class TransportCallback
{
    /**
     * @var DoNotContact
     */
    private $dncModel;

    /**
     * @var ContactFinder
     */
    private $finder;

    /**
     * @var StatRepository
     */
    private $statRepository;

    /**
     * @var LicenseInfoHelper
     */
    private $licenseInfoHelper;

    /**
     * TransportCallback constructor.
     *
     * @param DoNotContact      $dncModel
     * @param ContactFinder     $finder
     * @param StatRepository    $statRepository
     * @param LicenseInfoHelper $licenseInfoHelper
     */
    public function __construct(DoNotContact $dncModel, ContactFinder $finder, StatRepository $statRepository, LicenseInfoHelper $licenseInfoHelper)
    {
        $this->dncModel          = $dncModel;
        $this->finder            = $finder;
        $this->statRepository    = $statRepository;
        $this->licenseInfoHelper = $licenseInfoHelper;
    }

    /**
     * @param string $hashId
     * @param string $comments
     * @param int    $dncReason
     */
    public function addFailureByHashId($hashId, $comments, $dncReason = DNC::BOUNCED)
    {
        $result = $this->finder->findByHash($hashId);

        if ($contacts = $result->getContacts()) {
            $stat = $result->getStat();
            $this->updateStatDetails($stat, $comments, $dncReason);

            $email   = $stat->getEmail();
            $channel = ($email) ? ['email' => $email->getId()] : 'email';
            foreach ($contacts as $contact) {
                $this->dncModel->addDncForContact($contact->getId(), $channel, $dncReason, $comments);
            }
        }
    }

    /**
     * @param string   $address
     * @param string   $comments
     * @param int      $dncReason
     * @param int|null $channelId
     */
    public function addFailureByAddress($address, $comments, $dncReason = DNC::BOUNCED, $channelId = null)
    {
        $result     = $this->finder->findByAddress($address);
        $resultstat = $this->finder->findByAddressandId($address);
        $stat       = $resultstat->getStat();
        if ($contacts = $result->getContacts()) {
            foreach ($contacts as $contact) {
                if ($stat != null && $stat->getEmail() != null && $stat->getEmail()->getId() != null && $stat->getLead() != null && $stat->getLead()->getId() != null && $stat->getLead()->getId() == $contact->getId()) {
                    $channel = ['email' => $stat->getEmail()->getId()];
                } else {
                    $this->updateStatDetails($stat, $comments, $dncReason);
                    $channel = ($channelId) ? ['email' => $channelId] : 'email';
                    if ($dncReason == DNC::SPAM) {
                        $this->licenseInfoHelper->intSpamCount('1');
                    } elseif ($dncReason = DNC::BOUNCED) {
                        $this->licenseInfoHelper->intBounceCount('1');
                    }
                }
                $this->dncModel->addDncForContact($contact->getId(), $channel, $dncReason, $comments);
            }
        }
        if ($stat != null && $stat->getEmail() != null && $stat->getEmail()->getId() != null) {
            $this->updateStatDetails($stat, $comments, $dncReason);
            $this->statRepository->updateEmailStatcount($stat->getEmail()->getId(), $dncReason);
            if ($dncReason == DNC::SPAM) {
                $this->licenseInfoHelper->intSpamCount('1');
            } elseif ($dncReason = DNC::BOUNCED) {
                $this->licenseInfoHelper->intBounceCount('1');
            }
        }
    }

    /**
     * @param          $id
     * @param          $comments
     * @param int      $dncReason
     * @param int|null $channelId
     */
    public function addFailureByContactId($id, $comments, $dncReason = DNC::BOUNCED, $channelId = null)
    {
        $channel = ($channelId) ? ['email' => $channelId] : 'email';
        $this->dncModel->addDncForContact($id, $channel, $dncReason, $comments);
    }

    /**
     * @param Stat $stat
     * @param      $comments
     */
    private function updateStatDetails(Stat $stat, $comments, $dncReason)
    {
        if (DNC::BOUNCED === $dncReason) {
            $stat->setIsBounce(true);
        } elseif (DNC::UNSUBSCRIBED === $dncReason) {
            $stat->setIsUnsubscribe(true);
        } elseif (DNC::SPAM === $dncReason) {
            $stat->setIsSpam(true);
        } elseif (DNC::IS_CONTACTABLE === $dncReason) {
            $stat->setIsFailed(true);
        }

        $openDetails = $stat->getOpenDetails();
        if (!isset($openDetails['bounces'])) {
            $openDetails['bounces'] = [];
        }

        $dtHelper                 = new DateTimeHelper();
        $openDetails['bounces'][] = [
            'datetime' => $dtHelper->toUtcString(),
            'reason'   => $comments,
        ];
        $stat->setOpenDetails($openDetails);
        $this->statRepository->saveEntity($stat);
    }

    public function getLicenseHelper()
    {
        return $this->licenseInfoHelper;
    }
}
