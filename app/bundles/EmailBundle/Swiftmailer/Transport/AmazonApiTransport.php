<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Swiftmailer\Transport;

use Joomla\Http\Http;
use Mautic\EmailBundle\Helper\PlainTextMassageHelper;
use Mautic\EmailBundle\Model\TransportCallback;
use Mautic\EmailBundle\Swiftmailer\Amazon\SimpleEmailService;
use Mautic\EmailBundle\Swiftmailer\Amazon\SimpleEmailServiceMessage;
use Mautic\LeadBundle\Entity\DoNotContact;
use Psr\Log\LoggerInterface;
use Swift_Events_EventListener;
use Swift_Mime_Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Translation\TranslatorInterface;

class AmazonApiTransport implements \Swift_Transport, TokenTransportInterface, CallbackTransportInterface
{
    /**
     * @var \Swift_Events_SimpleEventDispatcher
     */
    private $swiftEventDispatcher;
    /**
     * @var Http
     */
    private $httpClient;
    /**
     * @var bool
     */
    private $started = false;

    /**
     * @var SimpleEmailService
     */
    private $simpleemailservice;
    /**
     * @var TransportCallback
     */
    private $transportCallback;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(Http $httpClient, SimpleEmailService $simpleemailservice, LoggerInterface $logger, TranslatorInterface $translator, TransportCallback $transportCallback)
    {
        $this->httpClient           = $httpClient;
        $this->simpleemailservice   = $simpleemailservice;
        $this->logger               = $logger;
        $this->translator           = $translator;
        $this->transportCallback    = $transportCallback;
    }

    /**
     * Test if this Transport mechanism has started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Start this Transport mechanism.
     *
     * @throws \Swift_TransportException
     */
    public function start()
    {
        $this->started = true;
    }

    /**
     * Stop this Transport mechanism.
     */
    public function stop()
    {
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param Swift_Mime_Message $message
     * @param string[]           $failedRecipients An array of failures by-reference
     *
     * @return int
     *
     * @throws \Swift_TransportException
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        try {
            $this->simpleemailservice->sendEmail($this->getMessage($message), true, true);
        } catch (\Swift_TransportException $e) {
            throw new \Swift_TransportException($e->getMessage());
        }

        return count($message->getTo());
    }

    public function getMessage(Swift_Mime_Message $message)
    {
        $rawmessage = new SimpleEmailServiceMessage();
        $toAddress  =[];
        foreach ($message->getTo() as $recipientEmail => $recipientName) {
            $toAddress[]=$recipientEmail;
        }
        $fromaddress='';
        foreach ($message->getFrom() as $recipientEmail => $recipientName) {
            $fromaddress=$recipientName.' <'.$recipientEmail.'>';
        }
        $rawmessage->addTo($toAddress);
        $rawmessage->setFrom($fromaddress);
        $bccAddress=[];
        if ($message->getBcc()) {
            foreach ($message->getBcc() as $recipientEmail => $recipientName) {
                $bccAddress[]=$recipientEmail;
            }
        }
        $replyTo=[];
        if ($message->getReplyTo()) {
            foreach ($message->getReplyTo() as $recipientEmail => $recipientName) {
                $replyTo[]=$recipientEmail;
            }
        }
        if (count($bccAddress) > 0) {
            $rawmessage->addBCC($bccAddress);
        }
        if (count($replyTo) > 0) {
            $rawmessage->addReplyTo($replyTo);
        }
        $rawmessage->setSubject($message->getSubject());
        $rawmessage->setMessageFromString(PlainTextMassageHelper::getPlainTextFromMessage($message), $message->getBody());
        if ($message instanceof MauticMessage) {
            foreach ($message->getAttachments() as $emailAttachment) {
                $rawmessage->addAttachmentFromFile($emailAttachment['fileName'], $emailAttachment['filePath'], $emailAttachment['contentType']);
            }
        }
        $headers     = $message->getHeaders()->getAll();
        foreach ($headers as $headerKey => $headerValue) {
            if (strpos($headerValue, 'List-Unsubscribe:') !== false) {
                $rawmessage->addCustomHeader($headerValue);
            }
        }

        return $rawmessage;
    }

    /**
     * Register a plugin in the Transport.
     *
     * @param Swift_Events_EventListener $plugin
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->getDispatcher()->bindEventListener($plugin);
    }

    /**
     * @return \Swift_Events_SimpleEventDispatcher
     */
    private function getDispatcher()
    {
        if ($this->swiftEventDispatcher === null) {
            $this->swiftEventDispatcher = new \Swift_Events_SimpleEventDispatcher();
        }

        return $this->swiftEventDispatcher;
    }

    /**
     * Return the max number of to addresses allowed per batch.  If there is no limit, return 0.
     *
     * @return int
     */
    public function getMaxBatchLimit()
    {
        //Sengrid allows to include max 1000 email address into 1 batch
        return 1000;
    }

    /**
     * Get the count for the max number of recipients per batch.
     *
     * @param \Swift_Message $message
     * @param int            $toBeAdded Number of emails about to be added
     * @param string         $type      Type of emails being added (to, cc, bcc)
     *
     * @return int
     */
    public function getBatchRecipientCount(\Swift_Message $message, $toBeAdded = 1, $type = 'to')
    {
        //Sengrid counts all email address (to, cc and bcc)
        //https://sendgrid.com/docs/API_Reference/Web_API_v3/Mail/errors.html#message.personalizations
        return count($message->getTo()) + count($message->getCc()) + count($message->getBcc()) + $toBeAdded;
    }

    /**
     * Function required to check that $this->message is instanceof MauticMessage, return $this->message->getMetadata() if it is and array() if not.
     *
     * @throws \Exception
     */
    public function getMetadata()
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Returns a "transport" string to match the URL path /mailer/{transport}/callback.
     *
     * @return mixed
     */
    public function getCallbackPath()
    {
        return 'amazon';
    }

    /**
     * Processes the response.
     *
     * @param Request $request
     */
    public function processCallbackRequest(Request $request)
    {
        $this->logger->debug('Receiving webhook from Amazon');

        $payload = json_decode($request->getContent(), true);

        return $this->processJsonPayload($payload);
    }

    /**
     * Process json request from Amazon SES.
     *
     * http://docs.aws.amazon.com/ses/latest/DeveloperGuide/best-practices-bounces-complaints.html
     *
     * @param array $payload from Amazon SES
     */
    public function processJsonPayload(array $payload)
    {
        if (!isset($payload['Type'])) {
            throw new HttpException(400, "Key 'Type' not found in payload ");
        }

        if ($payload['Type'] == 'SubscriptionConfirmation') {
            // Confirm Amazon SNS subscription by calling back the SubscribeURL from the playload
            try {
                $response = $this->httpClient->get($payload['SubscribeURL']);
                if ($response->code == 200) {
                    $this->logger->info('Callback to SubscribeURL from Amazon SNS successfully');

                    return;
                }

                $reason = 'HTTP Code '.$response->code.', '.$response->body;
            } catch (UnexpectedResponseException $e) {
                $reason = $e->getMessage();
            }

            $this->logger->error('Callback to SubscribeURL from Amazon SNS failed, reason: '.$reason);

            return;
        }

        if ($payload['Type'] == 'Notification') {
            $message = json_decode($payload['Message'], true);

            // only deal with hard bounces
            if ($message['notificationType'] == 'Bounce' && $message['bounce']['bounceType'] == 'Permanent') {
                // Get bounced recipients in an array
                $bouncedRecipients = $message['bounce']['bouncedRecipients'];
                foreach ($bouncedRecipients as $bouncedRecipient) {
                    $this->transportCallback->addFailureByAddress($bouncedRecipient['emailAddress'], $bouncedRecipient['diagnosticCode']);
                    $this->logger->debug("Mark email '".$bouncedRecipient['emailAddress']."' as bounced, reason: ".$bouncedRecipient['diagnosticCode']);
                }

                return;
            }

            // unsubscribe customer that complain about spam at their mail provider
            if ($message['notificationType'] == 'Complaint') {
                foreach ($message['complaint']['complainedRecipients'] as $complainedRecipient) {
                    $reason = null;
                    if (isset($message['complaint']['complaintFeedbackType'])) {
                        // http://docs.aws.amazon.com/ses/latest/DeveloperGuide/notification-contents.html#complaint-object
                        switch ($message['complaint']['complaintFeedbackType']) {
                            case 'abuse':
                                $reason = $this->translator->trans('mautic.email.complaint.reason.abuse');
                                break;
                            case 'fraud':
                                $reason = $this->translator->trans('mautic.email.complaint.reason.fraud');
                                break;
                            case 'virus':
                                $reason = $this->translator->trans('mautic.email.complaint.reason.virus');
                                break;
                        }
                    }

                    if ($reason == null) {
                        $reason = $this->translator->trans('mautic.email.complaint.reason.unknown');
                    }

                    $this->transportCallback->addFailureByAddress($complainedRecipient['emailAddress'], $reason, DoNotContact::SPAM);

                    $this->logger->debug("Unsubscribe email '".$complainedRecipient['emailAddress']."'");
                }
            }
        }
    }
}
