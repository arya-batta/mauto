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

use Mautic\EmailBundle\Model\TransportCallback;
use Mautic\LeadBundle\Entity\DoNotContact;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ElasticEmailTransport.
 */
class ElasticemailTransport extends \Swift_SmtpTransport implements CallbackTransportInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TransportCallback
     */
    private $transportCallback;

    /**
     * ElasticemailTransport constructor.
     *
     * @param TranslatorInterface $translator
     * @param LoggerInterface     $logger
     * @param TransportCallback   $transportCallback
     */
    public function __construct(TranslatorInterface $translator, LoggerInterface $logger, TransportCallback $transportCallback)
    {
        $this->translator        = $translator;
        $this->logger            = $logger;
        $this->transportCallback = $transportCallback;

        parent::__construct('smtp.elasticemail.com', 2525, null);

        $this->setAuthMode('login');
    }

    /**
     * @param \Swift_Mime_Message $message
     * @param null                $failedRecipients
     *
     * @return int|void
     *
     * @throws \Exception
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null)
    {
        // IsTransactional header for all non bulk messages
        // https://elasticemail.com/support/guides/unsubscribe/
        if ($message->getHeaders()->get('Precedence') != 'Bulk') {
            $message->getHeaders()->addTextHeader('IsTransactional', 'True');
        }
        $fromAddress= $message->getFrom();
        $fromAddress=array_keys($fromAddress);
        $bodyContent=$this->alterElasticEmailBodyContent($message->getBody());
        $message->setBody($bodyContent);
        $senderdetails=$this->transportCallback->getLicenseHelper()->getValidSendingDomain($fromAddress[0]);
        $message->setSender([$senderdetails[0]=>$senderdetails[1]]);
//        if (array_key_exists('support@lemailer3.com', $fromAdreess)) {
//            $message->setSender(['mailer@lemailer3.com'=>'LeadsEngage Mailer']);
//        } else {
//            $message->setSender(['mailer@lemailer2.com'=>'LeadsEngage Mailer']);
//        }
        parent::send($message, $failedRecipients);
    }

    /**
     * Returns a "transport" string to match the URL path /mailer/{transport}/callback.
     *
     * @return mixed
     */
    public function getCallbackPath()
    {
        return 'elasticemail';
    }

    /**
     * Handle bounces & complaints from ElasticEmail.
     *
     * @param Request $request
     */
    public function processCallbackRequest(Request $request)
    {
        //https://help.elasticemail.com/account-settings/how-to-manage-http-web-notifications-webhooks
        // just hard bounces https://help.elasticemail.com/activity/what-are-the-bounce-error-categories-and-filters
        $this->logger->debug('Receiving webhook from ElasticEmail');
        $email    = rawurldecode($request->get('to'));
        $status   = rawurldecode($request->get('status'));
        $category = rawurldecode($request->get('category'));
        $this->logger->debug('To:'.$email);
        $this->logger->debug('Status:'.$status);
        $this->logger->debug('Bounce:'.$category);
        // https://elasticemail.com/support/delivery/http-web-notification
        if (in_array($status, ['AbuseReport', 'Unsubscribed'])) {
            $this->transportCallback->addFailureByAddress($email, $status, DoNotContact::UNSUBSCRIBED);
        } elseif ('Spam' === $category) {
            $this->transportCallback->addFailureByAddress($email, $status, DoNotContact::SPAM);
        } elseif (in_array($category, ['NoMailboxes', 'DNSProblem', 'Unknown', 'ConnectionTerminated'])) {
            $category = 'Bounce';
            $this->transportCallback->addFailureByAddress($email, $category);
        } elseif (in_array($category, ['SPFProblem', 'AccountProblem', 'Ignore', 'BlackListed', 'GreyListed', 'Throttled', 'Timeout', 'ConnectionProblem', 'WhitelistingProblem', 'CodeError', 'ManualCancel', 'NotDelivered', 'Unknown', 'ContentFilter'])) {
            $this->transportCallback->addFailureByAddress($email, $this->translator->trans('le.email.complaint.reason.failed', ['%CATEGORY%'=> $category]), DoNotContact::IS_CONTACTABLE);
        } elseif ($status == 'Error') {
            $this->transportCallback->addFailureByAddress($email, $this->translator->trans('le.email.complaint.reason.unknown'), DoNotContact::IS_CONTACTABLE);
        }
    }

    /**
     * Alter Elastic Email Body Content to hide their own subscription url and account address.
     *
     * @param string $bodyContent
     */
    public function alterElasticEmailBodyContent($bodyContent)
    {
        $doc                      = new \DOMDocument();
        $doc->strictErrorChecking = false;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$bodyContent);
        // Get body tag.
        $body = $doc->getElementsByTagName('body');
        if ($body and $body->length > 0) {
            $body = $body->item(0);
            //create the div element to append to body element
            $divelement = $doc->createElement('div');
            $ptag1      = $doc->createElement('span', '{unsubscribe}');
            $ptag1->setAttribute('style', 'display:none;');
            $divelement->appendChild($ptag1);
            $ptag2 = $doc->createElement('span', '{accountaddress}');
            $ptag2->setAttribute('style', 'display:none;');
            $divelement->appendChild($ptag2);
            //actually append the element
            $body->appendChild($divelement);
            $bodyContent = $doc->saveHTML();
        }
        libxml_clear_errors();

        return $bodyContent;
    }

    public function removeContactStatus($curlhttp, $apikey, $emailid)
    {
        $parameters          =[];
        $parameters['apikey']=$apikey;
        $parameters['status']='Active';
        $parameters['emails']=$emailid;
        $result              = $curlhttp->post('https://api.elasticemail.com/v2/contact/changestatus', $parameters);
        $presponse           = json_decode($result->body, true);
        if (isset($presponse['success']) && $presponse['success']) {
            $this->logger->debug('Contact Status ('.$emailid.') Changed Successfully in Elastic Email.');

            return true;
        } else {
            $this->logger->debug('Contact Status ('.$emailid.') Change Request Failed in Elastic Email.Error:'.$presponse['error']);

            return false;
        }
    }
}
