<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\EmojiHelper;
use Mautic\CoreBundle\Helper\IpLookupHelper;
use Mautic\CoreBundle\Model\AuditLogModel;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event as Events;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\QueueBundle\Event\QueueConsumerEvent;
use Mautic\QueueBundle\Queue\QueueConsumerResults;
use Mautic\QueueBundle\QueueEvents;

/**
 * Class EmailSubscriber.
 */
class EmailSubscriber extends CommonSubscriber
{
    /**
     * @var AuditLogModel
     */
    protected $auditLogModel;

    /**
     * @var IpLookupHelper
     */
    protected $ipLookupHelper;

    /**
     * @var EmailModel
     */
    protected $emailModel;

    /**
     * EmailSubscriber constructor.
     *
     * @param IpLookupHelper $ipLookupHelper
     * @param AuditLogModel  $auditLogModel
     * @param EmailModel     $emailModel
     */
    public function __construct(IpLookupHelper $ipLookupHelper, AuditLogModel $auditLogModel, EmailModel $emailModel)
    {
        $this->ipLookupHelper = $ipLookupHelper;
        $this->auditLogModel  = $auditLogModel;
        $this->emailModel     = $emailModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_POST_SAVE       => ['onEmailPostSave', 0],
            EmailEvents::EMAIL_POST_DELETE     => ['onEmailDelete', 0],
            EmailEvents::EMAIL_FAILED          => ['onEmailFailed', 0],
            EmailEvents::EMAIL_RESEND          => ['onEmailResend', 0],
            QueueEvents::EMAIL_HIT             => ['onEmailHit', 0],
            EmailEvents::DRIPEMAIL_POST_SAVE   => ['onDripEmailPostSave', 0],
            EmailEvents::DRIPEMAIL_POST_DELETE => ['onDripEmailDelete', 0],
        ];
    }

    /**
     * Add an entry to the audit log.
     *
     * @param Events\EmailEvent $event
     */
    public function onEmailPostSave(Events\EmailEvent $event)
    {
        $email = $event->getEmail();
        if ($details = $event->getChanges()) {
            $log = [
                'bundle'    => 'email',
                'object'    => 'email',
                'objectId'  => $email->getId(),
                'action'    => ($event->isNew()) ? 'create' : 'update',
                'details'   => $details,
                'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
            ];
            $this->auditLogModel->writeToLog($log);
        }
    }

    /**
     * Add a delete entry to the audit log.
     *
     * @param Events\EmailEvent $event
     */
    public function onEmailDelete(Events\EmailEvent $event)
    {
        $email = $event->getEmail();
        $log   = [
            'bundle'    => 'email',
            'object'    => 'email',
            'objectId'  => $email->deletedId,
            'action'    => 'delete',
            'details'   => ['name' => $email->getName()],
            'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
        ];
        $this->auditLogModel->writeToLog($log);
    }

    /**
     * Process if an email has failed.
     *
     * @param Events\QueueEmailEvent $event
     */
    public function onEmailFailed(Events\QueueEmailEvent $event)
    {
        $message = $event->getMessage();

        if (isset($message->leadIdHash)) {
            $stat = $this->emailModel->getEmailStatus($message->leadIdHash);

            if ($stat !== null) {
                $reason = $this->translator->trans('le.email.dnc.failed', [
                    '%subject%' => EmojiHelper::toShort($message->getSubject()),
                ]);
                $this->emailModel->setDoNotContact($stat, $reason, DoNotContact::IS_CONTACTABLE);
            }
        }
    }

    /**
     * Process if an email is resent.
     *
     * @param Events\QueueEmailEvent $event
     */
    public function onEmailResend(Events\QueueEmailEvent $event)
    {
        $message = $event->getMessage();

        if (isset($message->leadIdHash)) {
            $stat = $this->emailModel->getEmailStatus($message->leadIdHash);
            if ($stat !== null) {
                $stat->upRetryCount();

                $retries = $stat->getRetryCount();
                if (true || $retries > 3) {
                    //tried too many times so just fail
                    $reason = $this->translator->trans('le.email.dnc.retries', [
                        '%subject%' => EmojiHelper::toShort($message->getSubject()),
                    ]);
                //$this->emailModel->setDoNotContact($stat, $reason);
                } else {
                    //set it to try again
                    $event->tryAgain();
                }

                $this->em->persist($stat);
                $this->em->flush();
            }
        }
    }

    /**
     * @param QueueConsumerEvent $event
     */
    public function onEmailHit(QueueConsumerEvent $event)
    {
        $payload = $event->getPayload();
        $request = $payload['request'];
        $idHash  = $payload['idHash'];
        $this->emailModel->hitEmail($idHash, $request, false, false);
        $event->setResult(QueueConsumerResults::ACKNOWLEDGE);
    }

    /**
     * Add an entry to the audit log.
     *
     * @param Events\DripEmailEvent $event
     */
    public function onDripEmailPostSave(Events\DripEmailEvent $event)
    {
        $email = $event->getDrip();
        if ($details = $event->getChanges()) {
            $log = [
                'bundle'    => 'email',
                'object'    => 'dripemail',
                'objectId'  => $email->getId(),
                'action'    => ($event->isNew()) ? 'create' : 'update',
                'details'   => $details,
                'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
            ];
            $this->auditLogModel->writeToLog($log);
        }
    }

    /**
     * Add a delete entry to the audit log.
     *
     * @param Events\DripEmailEvent $event
     */
    public function onDripEmailDelete(Events\DripEmailEvent $event)
    {
        $email = $event->getDrip();
        $log   = [
            'bundle'    => 'email',
            'object'    => 'dripemail',
            'objectId'  => $email->deletedId,
            'action'    => 'delete',
            'details'   => ['name' => $email->getName()],
            'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
        ];
        $this->auditLogModel->writeToLog($log);
    }
}
