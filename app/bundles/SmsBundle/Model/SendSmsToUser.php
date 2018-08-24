<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SmsBundle\Model;

use Mautic\CoreBundle\Helper\LicenseInfoHelper;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\NotificationBundle\Helper\NotificationHelper;
use Mautic\SmsBundle\Exception\SmsCouldNotBeSentException;
use Mautic\SmsBundle\OptionsAccessor\SmsToUserAccessor;
use Mautic\UserBundle\Model\UserModel;

class SendSmsToUser
{
    /** @var SmsModel */
    private $smsModel;

    /**
     * @var LicenseInfoHelper
     */
    private $licenseInfoHelper;

    /*
     * @var NotificationHelper
     */
    private $notificationHelper;

    /**
     * @var UserModel
     */
    protected $userModel;

    public function __construct(SmsModel $emailModel, LicenseInfoHelper $licenseInfoHelper, NotificationHelper $notificationHelper, UserModel $userModel)
    {
        $this->smsModel           = $emailModel;
        $this->licenseInfoHelper  = $licenseInfoHelper;
        $this->notificationHelper = $notificationHelper;
        $this->userModel          = $userModel;
    }

    /**
     * @param array             $config
     * @param Lead              $lead
     * @param LicenseInfoHelper $licenseInfoHelper
     *
     * @throws EmailCouldNotBeSentException
     */
    public function sendSmsToUsers(array $config, Lead $lead, $event)
    {
        $smsToUserAccessor = new SmsToUserAccessor($config);

        $sms = $this->smsModel->getEntity($smsToUserAccessor->getSmsID());

        if (!$sms || !$sms->isPublished()) {
            throw new SmsCouldNotBeSentException('Email not found or published');
        }
        $owner  = $lead->getOwner();
        $users  = $smsToUserAccessor->getUserIdsToSend($owner);
        $result = $this->smsModel->sendSmstoUser($sms, $users, $lead, ['channel' => ['campaign.event', $event->getEvent()['id']]])[$lead->getId()];

        if ($result['errorResult'] != 'Success') {
            $this->notificationHelper->sendNotificationonFailure(false, false);
        }
        if ('Authenticate' === $result['status']) {
            // Don't fail the event but reschedule it for later
            return $event->setResult(false);
        }

        if (!empty($result['sent'])) {
            $event->setChannel('sms', $sms->getId());
            $event->setResult($result);
        } else {
            $result['failed'] = true;
            $result['reason'] = $result['status'];
            $event->setResult($result);
        }

        return $event;
    }
}
