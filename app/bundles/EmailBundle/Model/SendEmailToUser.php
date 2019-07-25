<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Model;

use Mautic\CoreBundle\Helper\LicenseInfoHelper;
use Mautic\EmailBundle\Exception\EmailCouldNotBeSentException;
use Mautic\EmailBundle\OptionsAccessor\EmailToUserAccessor;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\NotificationBundle\Helper\NotificationHelper;
use Mautic\SubscriptionBundle\Entity\PaymentRepository;
use Mautic\SubscriptionBundle\Helper\StateMachineHelper;
use Mautic\UserBundle\Hash\UserHash;

class SendEmailToUser
{
    /** @var EmailModel */
    private $emailModel;

    /**
     * @var LicenseInfoHelper
     */
    private $licenseInfoHelper;

    /*
     * @var NotificationHelper
     */
    private $notificationHelper;

    /*
     * @var StateMachineHelper
     */
    private $smHelper;

    /*
     * @var PaymentRepository
     */
    private $paymentRepo;

    public function __construct(EmailModel $emailModel, LicenseInfoHelper $licenseInfoHelper, NotificationHelper $notificationHelper, StateMachineHelper $stateMachineHelper, PaymentRepository $paymentRepository)
    {
        $this->emailModel         = $emailModel;
        $this->licenseInfoHelper  = $licenseInfoHelper;
        $this->notificationHelper = $notificationHelper;
        $this->smHelper           = $stateMachineHelper;
        $this->paymentRepo        = $paymentRepository;
    }

    /**
     * @param array             $config
     * @param Lead              $lead
     * @param LicenseInfoHelper $licenseInfoHelper
     *
     * @throws EmailCouldNotBeSentException
     */
    public function sendEmailToUsers(array $config, Lead $lead)
    {
        $emailToUserAccessor = new EmailToUserAccessor($config);

        $isValidEmailCount     = $this->licenseInfoHelper->isValidEmailCount();
        $isHavingEmailValidity = $this->licenseInfoHelper->isHavingEmailValidity();
        $accountStatus         = $this->licenseInfoHelper->getAccountStatus();
        $email                 = $this->emailModel->getEntity($emailToUserAccessor->getEmailID());
        $lastpayment           = $this->paymentRepo->getLastPayment();

        if (!$email || !$email->isPublished()) {
            throw new EmailCouldNotBeSentException('Email not found or published');
        }

        $leadCredentials = $lead->getProfileFields();

        $to  = $emailToUserAccessor->getToFormatted();
        $cc  = $emailToUserAccessor->getCcFormatted();
        $bcc = $emailToUserAccessor->getBccFormatted();

        $owner = $lead->getOwner();
        $users = $emailToUserAccessor->getUserIdsToSend($owner);

        $idHash         = UserHash::getFakeUserHash();
        $sendResultTest = 'Success';
        if (!$accountStatus) {
            $cancelState = $this->smHelper->isStateAlive('Customer_Inactive_Exit_Cancel');
            if ($isValidEmailCount || ($lastpayment != null && !$cancelState)) { //&& $isHavingEmailValidity
                $tokens = $this->emailModel->dispatchEmailSendEvent($email, $leadCredentials, $idHash)->getTokens();
                $errors = $this->emailModel->sendEmailToUser($email, $users, $leadCredentials, $tokens, [], true, $to, $cc, $bcc);
                $this->licenseInfoHelper->intEmailCount('1');

                if ($errors) {
                    $sendResultTest = 'Failed';
                    throw new EmailCouldNotBeSentException(implode(', ', $errors));
                }
            } else {
                $sendResultTest = 'InSufficient Email Count Please Contact Support';
                throw new EmailCouldNotBeSentException('InSufficient Email Count Please Contact Support');
            }
        } else {
            $sendResultTest = "Your Account Has Been Kept on REVIEW and You Can't Send Emails for Now. Please Contact Support Team.";
            throw new EmailCouldNotBeSentException('Your Account Has Been Kept on REVIEW and You Can\'t Send Emails for Now. Please Contact Support Team.');
        }
        if ($sendResultTest != 'Success') {
            $this->notificationHelper->sendNotificationonFailure(true, false, $sendResultTest);
        }
    }
}
