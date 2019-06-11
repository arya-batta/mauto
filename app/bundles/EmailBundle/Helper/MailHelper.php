<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Helper;

use Doctrine\ORM\ORMException;
use Mautic\AssetBundle\Entity\Asset;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\coreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\EmojiHelper;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Entity\Stat;
use Mautic\EmailBundle\Event\EmailSendEvent;
use Mautic\EmailBundle\Swiftmailer\Amazon\SimpleEmailService;
use Mautic\EmailBundle\Swiftmailer\Exception\BatchQueueMaxException;
use Mautic\EmailBundle\Swiftmailer\Message\MauticMessage;
use Mautic\EmailBundle\Swiftmailer\Transport\AmazonApiTransport;
use Mautic\EmailBundle\Swiftmailer\Transport\SendgridApiTransport;
use Mautic\EmailBundle\Swiftmailer\Transport\SparkpostTransport;
use Mautic\EmailBundle\Swiftmailer\Transport\TokenTransportInterface;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\SubscriptionBundle\Entity\Account;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MailHelper.
 */
class MailHelper
{
    const QUEUE_RESET_TO          = 'RESET_TO';
    const QUEUE_FULL_RESET        = 'FULL_RESET';
    const QUEUE_DO_NOTHING        = 'DO_NOTHING';
    const QUEUE_NOTHING_IF_FAILED = 'IF_FAILED';
    const QUEUE_RETURN_ERRORS     = 'RETURN_ERRORS';
    /**
     * @var MauticFactory
     */
    protected $factory;

    /**
     * @var
     */
    protected $mailer;

    /**
     * @var
     */
    protected $transport;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine
     */
    protected $templating = null;

    /**
     * @var null
     */
    protected $dispatcher = null;

    /**
     * @var \Swift_Plugins_Loggers_ArrayLogger
     */
    protected $logger;

    /**
     * @var bool|MauticMessage
     */
    public $message;

    /**
     * @var null
     */
    protected $from;

    /**
     * @var
     */
    protected $systemFrom;

    /**
     * @var string
     */
    protected $returnPath;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array|Lead
     */
    protected $lead = null;

    /**
     * @var bool
     */
    protected $internalSend = false;

    /**
     * @var null
     */
    protected $idHash = null;

    /**
     * @var bool
     */
    protected $idHashState = true;

    /**
     * @var bool
     */
    protected $appendTrackingPixel = false;

    /**
     * @var array
     */
    protected $source = [];

    /**
     * @var Email|null
     */
    protected $email = null;

    /**
     * @var array
     */
    protected $globalTokens = [];

    /**
     * @var array
     */
    protected $eventTokens = [];

    /**
     * Tells the helper that the transport supports tokenized emails (likely HTTP API).
     *
     * @var bool
     */
    protected $tokenizationEnabled = false;

    /**
     * Use queue mode when sending email through this mailer; this requires a transport that supports tokenization and the use of queue/flushQueue.
     *
     * @var bool
     */
    protected $queueEnabled = false;

    /**
     * @var array
     */
    protected $queuedRecipients = [];

    /**
     * @var array
     */
    public $metadata = [];

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $plainText = '';

    /**
     * @var bool
     */
    protected $plainTextSet = false;

    /**
     * @var array
     */
    protected $assets = [];

    /**
     * @var array
     */
    protected $attachedAssets = [];

    /**
     * @var array
     */
    protected $assetStats = [];

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $body = [
        'content'     => '',
        'contentType' => 'text/html',
        'charset'     => null,
    ];

    /**
     * Cache for lead owners.
     *
     * @var array
     */
    protected static $leadOwners = [];

    /**
     * @var bool
     */
    protected $fatal = false;

    /**
     * Flag whether to use only the globally set From email and name or whether to switch to mailer is owner.
     *
     * @var bool
     */
    protected $useGlobalFrom = false;

    /**
     * Large batch mail sends may result on timeouts with SMTP servers. This will will keep track of the number of sends and restart the connection once met.
     *
     * @var int
     */
    private $messageSentCount = 0;

    /**
     * Large batch mail sends may result on timeouts with SMTP servers. This will will keep track of when a transport was last started and force a restart after set number of minutes.
     *
     * @var int
     */
    private $transportStartTime;

    /**
     * Simply a md5 of the content so that event listeners can easily determine if the content has been changed.
     *
     * @var string
     */
    private $contentHash;

    /**
     * @var array
     */
    private $copies = [];
    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param MauticFactory        $factory
     * @param                      $mailer
     * @param null                 $from
     * @param CoreParametersHelper $coreParametersHelper
     */
    public function __construct(MauticFactory $factory, \Swift_Mailer $mailer, CoreParametersHelper $coreParametersHelper, ContainerInterface $container, $from = null)
    {
        $this->factory              = $factory;
        $this->mailer               = $mailer;
        $this->transport            = $mailer->getTransport();
        $this->coreParametersHelper = $coreParametersHelper;
        $this->container            = $container;

        try {
            $this->logger = new \Swift_Plugins_Loggers_ArrayLogger();
            $this->mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($this->logger));
        } catch (\Exception $e) {
            $this->logError($e);
        }
        $fromname         ='';
        $fromemail        ='';
        $this->from       = $this->systemFrom       = (!empty($from)) ? $from : [$fromemail => $fromname]; //$factory->getParameter('mailer_from_email') //$factory->getParameter('mailer_from_name')
        $this->returnPath = $factory->getParameter('mailer_return_path');

        // Check if batching is supported by the transport
        if ($this->factory->getParameter('mailer_spool_type') == 'memory' && $this->transport instanceof TokenTransportInterface) {
            $this->tokenizationEnabled = true;
        }

        // Set factory if supported
        if (method_exists($this->transport, 'setMauticFactory')) {
            $this->transport->setMauticFactory($factory);
        }

        $this->message = $this->getMessageInstance();
    }

    /**
     * Mirrors previous MauticFactory functionality.
     *
     * @param bool $cleanSlate
     *
     * @return $this
     */
    public function getMailer($cleanSlate = true)
    {
        $this->reset($cleanSlate);

        return $this;
    }

    /**
     * Mirrors previous MauticFactory functionality.
     *
     * @param bool $cleanSlate
     *
     * @return $this
     */
    public function getSampleMailer($cleanSlate = true)
    {
        $queueMode = $this->factory->getParameter('mailer_spool_type');
        if ($queueMode != 'file') {
            return $this->getMailer($cleanSlate);
        }
        // @todo - need a creative way to pass this service to this helper when factory use is removed
        // the service is only available when queue mode is enabled so likely we'll need to use a cache compiler
        // pass to ensure it is set regardless
        $transport  = $this->factory->get('swiftmailer.transport.real');
        $mailer     = new \Swift_Mailer($transport);
        $mailHelper = new self($this->factory, $mailer, $this->coreParametersHelper, $this->container, $this->from);

        return $mailHelper->getMailer($cleanSlate);
    }

    /**
     * Send the message.
     *
     * @param bool $dispatchSendEvent
     * @param bool $isQueueFlush      (a tokenized/batch send via API such as Mandrill)
     * @param bool $useOwnerAsMailer
     *
     * @return bool
     */
    public function send($dispatchSendEvent = false, $isQueueFlush = false, $useOwnerAsMailer = true)
    {
        if ($this->tokenizationEnabled && !empty($this->queuedRecipients) && !$isQueueFlush) {
            // This transport uses tokenization and queue()/flushQueue() was not used therefore use them in order
            // properly populate metadata for this transport

            if ($result = $this->queue($dispatchSendEvent)) {
                $result = $this->flushQueue(['To', 'Cc', 'Bcc'], $useOwnerAsMailer);
            }

            return $result;
        }

        // Set from email
        $ownerSignature = false;
        if (!$isQueueFlush) {
            if ($useOwnerAsMailer) {
                if ($owner = $this->getContactOwner($this->lead)) {
                    $this->setFrom($owner['email'], $owner['first_name'].' '.$owner['last_name'], null);
                    $ownerSignature = $this->getContactOwnerSignature($owner);
                } else {
                    $this->setFrom($this->from, null, null);
                }
            } elseif (!$from = $this->message->getFrom()) {
                $this->setFrom($this->from, null, null);
            }
        } // from is set in flushQueue

        // Set system return path if applicable
        if (!$isQueueFlush && ($bounceEmail = $this->generateBounceEmail())) {
            $this->message->setReturnPath($bounceEmail);
        } elseif (!empty($this->returnPath)) {
            $this->message->setReturnPath($this->returnPath);
        }
        $this->addUnsubscribeHeader();

        if (empty($this->fatal)) {
            if (!$isQueueFlush) {
                // Only add unsubscribe header to one-off sends as tokenized sends are built by the transport

                // Search/replace tokens if this is not a queue flush
                if ($dispatchSendEvent && !empty($this->body['content'])) {
                    $bodycontent           = $this->alterEmailBodyContent($this->body['content']);
                    $this->body['content'] = $bodycontent;
                }

                // Generate tokens from listeners
                if ($dispatchSendEvent) {
                    $this->dispatchSendEvent();
                }

                // Queue an asset stat if applicable
                $this->queueAssetDownloadEntry();
            }

            $this->message->setSubject($this->subject);
            // Only set body if not empty or if plain text is empty - this ensures an empty HTML body does not show for
            // messages only with plain text
            if (!empty($this->body['content']) || empty($this->plainText)) {
                $this->message->setBody($this->body['content'], $this->body['contentType'], $this->body['charset']);
            }
            $this->setMessagePlainText();

            if (!$isQueueFlush) {
                // Replace token content
                $tokens = $this->getTokens();
                if ($ownerSignature) {
                    $tokens['{signature}'] = $ownerSignature;
                }

                // Set metadata if applicable
                if (method_exists($this->message, 'addMetadata')) {
                    foreach ($this->queuedRecipients as $email => $name) {
                        $this->message->addMetadata(
                            $email,
                            [
                                'leadId'      => (!empty($this->lead)) ? $this->lead['id'] : null,
                                'emailId'     => (!empty($this->email)) ? $this->email->getId() : null,
                                'hashId'      => $this->idHash,
                                'hashIdState' => $this->idHashState,
                                'source'      => $this->source,
                                'tokens'      => $tokens,
                            ]
                        );
                    }
                } elseif (!empty($tokens)) {
                    // Replace tokens
                    $search  = array_keys($tokens);
                    $replace = $tokens;

                    self::searchReplaceTokens($search, $replace, $this->message);
                }
            }

            // Attach assets
            if (!empty($this->assets)) {
                /** @var \Mautic\AssetBundle\Entity\Asset $asset */
                foreach ($this->assets as $asset) {
                    if (!in_array($asset->getId(), $this->attachedAssets)) {
                        $this->attachedAssets[] = $asset->getId();
                        $this->attachFile(
                            $asset->getFilePath(),
                            $asset->getOriginalFileName(),
                            $asset->getMime()
                        );
                    }
                }
            }

            // Set custom headers
            if (!empty($this->headers)) {
                $headers = $this->message->getHeaders();
                foreach ($this->headers as $headerKey => $headerValue) {
                    if ($headers->has($headerKey)) {
                        $header = $headers->get($headerKey);
                        $header->setFieldBodyModel($headerValue);
                    } else {
                        $headers->addTextHeader($headerKey, $headerValue);
                    }
                }
            }

            try {
                $failures = [];

                if (!$this->transport->isStarted()) {
                    $this->transportStartTime = time();
                }
                $this->mailer->send($this->message, $failures);

                if (!empty($failures)) {
                    $this->errors['failures'] = $failures;
                    $this->logError('Sending failed for one or more recipients');
                }

                // Clear the log so that previous output is not associated with new errors
                $this->logger->clear();
            } catch (\Exception $e) {
                // Exception encountered when sending so all recipients are considered failures
                $this->errors['failures'] = array_merge(
                    array_keys((array) $this->message->getTo()),
                    array_keys((array) $this->message->getCc()),
                    array_keys((array) $this->message->getBcc())
                );
                //  $mailer = $this->factory->get('le.transport.elasticemail.transactions');
                // $this->failedSMTPEmailtoUser($mailer);
                $configurator   = $this->factory->get('mautic.configurator');
                $configurator->mergeParameters(['email_status' => 'InActive']);
                $configurator->write();
                $this->logError($e, 'send');
            }
        }

        ++$this->messageSentCount;
        $this->checkIfTransportNeedsRestart();

        $error = empty($this->errors);

        if (!$isQueueFlush) {
            $this->createAssetDownloadEntries();
        } // else handled in flushQueue

        return $error;
    }

    /**
     * Alter Elastic Email Body Content to hide their own subscription url and account address.
     *
     * @param \Swift_Message $bodyContent
     */
    public function alterEmailBodyContent($bodyContent, $Type = null)
    {
        $doc                      = new \DOMDocument();
        $doc->strictErrorChecking = false;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$bodyContent);
        // Get body tag.
        $body = $doc->getElementsByTagName('body');
        if ($body and $body->length > 0) {
            $body = $body->item(0);
            if ($this->email != null && $this->email->getPreviewText() != null && $this->email->getPreviewText() != '') {
                $previewtext = $doc->createElement('span', $this->email->getPreviewText());
                $previewtext->setAttribute('class', 'preheader');
                $previewtext->setAttribute('style', 'display: none !important; visibility: hidden; opacity: 0; color: transparent; height: 0; width: 0;');
                //$body->appendChild($previewtext);
                $body->insertBefore($previewtext, $body->firstChild);
            }
            if (strpos($this->body['content'], 'list_footerText') !== false) {
                $bodyContent = $doc->saveHTML();

                return $bodyContent;
            }

            if ($this->getEmail() instanceof Email) {
                if (($this->getEmail()->getEmailType() != 'template')) {
                    $emailtype=true;
                } else {
                    $emailtype=false;
                }
            } else {
                if ($Type != 'template') {
                    $emailtype = true;
                } else {
                    $emailtype = false;
                }
            }
            $accountmodel  = $this->factory->getModel('subscription.accountinfo');
            $accrepo       = $accountmodel->getRepository();
            $accountentity = $accrepo->findAll();
            if (sizeof($accountentity) > 0) {
                $account = $accountentity[0]; //$model->getEntity(1);
            } else {
                $account = new Account();
            }
            if ($emailtype && (strpos($bodyContent, '{footer_text}') === false) && ((strpos($bodyContent, '{unsubscribe_link}') === false) && (strpos($bodyContent, '{update_profile_link}') === false))) {
                //create the div element to append to body element
                $divelement = $doc->createElement('div');
                $divelement->setAttribute('style', 'margin-top:30px;background-color:#ffffff;border-top:1px solid #d0d0d0;font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            font-weight: normal;');
                $ptag1 = $doc->createElement('span', '{footer_text}');
                $divelement->appendChild($ptag1);

                if (false) { //$account->getNeedpoweredby()
                    $ptag1->setAttribute('style', 'display:block;padding-top:20px;width:60%;float:left;text-align:right;');
                    $powerspan = $doc->createElement('span');
                    $powerspan->setAttribute('style', 'width:40%;float:right;text-align:left;');

                    $imgtag = $doc->createElement('img');
                    $imgtag->setAttribute('src', 'https://s3-eu-west-1.amazonaws.com/cdn.supporthero.io/account/1829/00854641-cbd0-4e19-a4db-9ac4370d6b89.png');
                    $imgtag->setAttribute('style', 'height:100px;width:250px;');

                    $powerspan->appendChild($imgtag);
                    $divelement->appendChild($powerspan);
                } else {
                    $ptag1->setAttribute('style', 'display:block;padding-top:20px;');
                }
                //actually append the element
                $body->appendChild($divelement);
            }

            if ($account->getNeedpoweredby() && (strpos($bodyContent, 'email-footer-link') === false)) {
                $br          = $doc->createElement('br');
                $brandfooter = $doc->createElement('div');
                $brandfooter->setAttribute('style', 'background-color:#ffffff;text-align:center;');
                $url  = 'https://anyfunnels.com/?utm-src=email-footer-link&utm-med='.$account->getDomainname();
                $atag = $doc->createElement('a');
                $atag->setAttribute('href', $url);
                $atag->setAttribute('target', '_blank');

                $imgtag = $doc->createElement('img');
                $icon   = 'https://anyfunnels.com/wp-content/uploads/leproduct/anyfunnels-footer2.png'; //$this->factory->get('templating.helper.assets')->getUrl('media/images/le_branding.png');
                $imgtag->setAttribute('src', $icon);
                $imgtag->setAttribute('style', 'height:35px;width:160px;margin-top:10px;margin-bottom:5px;');
                $imgtag->setAttribute('title', 'Free Marketing Automation Software');
                $atag->appendChild($imgtag);
                $brandfooter->appendChild($atag);
                $body->appendChild($br);
                $body->appendChild($brandfooter);
                $content = $doc->saveHTML();
            }
            $bodyContent = $doc->saveHTML();
        }
        libxml_clear_errors();

        return $bodyContent;
    }

    /**
     * If batching is supported and enabled, the message will be queued and will on be sent upon flushQueue().
     * Otherwise, the message will be sent to the transport immediately.
     *
     * @param bool   $dispatchSendEvent
     * @param string $returnMode        What should happen post send/queue to $this->message after the email send is attempted.
     *                                  Options are:
     *                                  RESET_TO           resets the to recipients and resets errors
     *                                  FULL_RESET         creates a new MauticMessage instance and resets errors
     *                                  DO_NOTHING         leaves the current errors array and MauticMessage instance intact
     *                                  NOTHING_IF_FAILED  leaves the current errors array MauticMessage instance intact if it fails, otherwise reset_to
     *                                  RETURN_ERROR       return an array of [success, $errors]; only one applicable if message is queued
     *
     * @return bool|array
     */
    public function queue($dispatchSendEvent = false, $returnMode = self::QUEUE_RESET_TO)
    {
        if ($this->tokenizationEnabled) {
            if ($dispatchSendEvent && !empty($this->body['content'])) {
                $bodycontent           = $this->alterEmailBodyContent($this->body['content']);
                $this->body['content'] = $bodycontent;
            }
            // Dispatch event to get custom tokens from listeners
            if ($dispatchSendEvent) {
                $this->dispatchSendEvent();
            }

            // Metadata has to be set for each recipient
            foreach ($this->queuedRecipients as $email => $name) {
                $fromKey = 'default';
                $tokens  = $this->getTokens();

                if ($owner = $this->getContactOwner($this->lead)) {
                    $fromKey = $owner['email'];

                    // Override default signature with owner
                    if ($ownerSignature = $this->getContactOwnerSignature($owner)) {
                        $tokens['{signature}'] = $ownerSignature;
                    }
                }

                if (!isset($this->metadata[$fromKey])) {
                    $this->metadata[$fromKey] = [
                        'from'     => $owner,
                        'contacts' => [],
                    ];
                }

                $this->metadata[$fromKey]['contacts'][$email] =
                    [
                        'name'        => $name,
                        'leadId'      => (!empty($this->lead)) ? $this->lead['id'] : null,
                        'emailId'     => (!empty($this->email)) ? $this->email->getId() : null,
                        'hashId'      => $this->idHash,
                        'hashIdState' => $this->idHashState,
                        'source'      => $this->source,
                        'tokens'      => $tokens,
                    ];
            }

            // Reset recipients
            $this->queuedRecipients = [];

            // Assume success
            return (self::QUEUE_RETURN_ERRORS) ? [true, []] : true;
        } else {
            $success = $this->send($dispatchSendEvent);

            // Reset the message for the next
            $this->queuedRecipients = [];

            // Reset message
            switch (ucwords($returnMode)) {
                case self::QUEUE_RESET_TO:
                    $this->message->setTo([]);
                    $this->clearErrors();
                    break;
                case self::QUEUE_NOTHING_IF_FAILED:
                    if ($success) {
                        $this->message->setTo([]);
                        $this->clearErrors();
                    }

                    break;
                case self::QUEUE_FULL_RESET:
                    $this->message        = $this->getMessageInstance();
                    $this->attachedAssets = [];
                    $this->clearErrors();
                    break;
                case self::QUEUE_RETURN_ERRORS:
                    $this->message->setTo([]);
                    $errors = $this->getErrors();

                    $this->clearErrors();

                    return [$success, $errors];
                case self::QUEUE_DO_NOTHING:
                default:
                    // Nada

                    break;
            }

            return $success;
        }
    }

    /**
     * Send batched mail to mailer.
     *
     * @param array $resetEmailTypes  Array of email types to clear after flusing the queue
     * @param bool  $useOwnerAsMailer
     *
     * @return bool
     */
    public function flushQueue($resetEmailTypes = ['To', 'Cc', 'Bcc'], $useOwnerAsMailer = true)
    {
        // Assume true unless there was a fatal error configuring the mailer because if tokenizationEnabled is false, the send happened in queue()
        $flushed = empty($this->fatal);
        if ($this->tokenizationEnabled && count($this->metadata) && $flushed) {
            $errors             = $this->errors;
            $errors['failures'] = [];
            $flushed            = false;

            foreach ($this->metadata as $fromKey => $metadatum) {
                // Whatever is in the message "to" should be ignored as we will send to the contacts grouped by from addresses
                // This prevents mailers such as sparkpost from sending duplicates to contacts
                $this->message->setTo([]);

                $this->errors = [];

                if (!$this->useGlobalFrom && $useOwnerAsMailer && 'default' !== $fromKey) {
                    $this->setFrom($metadatum['from']['email'], $metadatum['from']['first_name'].' '.$metadatum['from']['last_name'], null);
                } else {
                    $this->setFrom($this->from, null, null);
                }

                foreach ($metadatum['contacts'] as $email => $contact) {
                    $this->message->addMetadata($email, $contact);

                    // Add asset stats if applicable
                    if (!empty($contact['leadId'])) {
                        $this->queueAssetDownloadEntry($email, $contact);
                    }

                    $this->message->addTo($email, $contact['name']);
                }

                $flushed = $this->send(false, true);

                // Merge errors
                if (isset($this->errors['failures'])) {
                    $errors['failures'] = array_merge($errors['failures'], $this->errors['failures']);
                    unset($this->errors['failures']);
                }

                if (!empty($this->errors)) {
                    $errors = array_merge($errors, $this->errors);
                }

                // Clear metadata for the previous recipients
                $this->message->clearMetadata();
            }

            $this->errors = $errors;

            // Clear queued to recipients
            $this->queuedRecipients = [];
            $this->metadata         = [];
        }

        foreach ($resetEmailTypes as $type) {
            $type = ucfirst($type);
            $this->message->{'set'.$type}([]);
        }

        return $flushed;
    }

    /**
     * Resets the mailer.
     *
     * @param bool $cleanSlate
     */
    public function reset($cleanSlate = true)
    {
        $this->eventTokens      = [];
        $this->queuedRecipients = [];
        $this->errors           = [];
        $this->lead             = null;
        $this->idHash           = null;
        $this->contentHash      = null;
        $this->internalSend     = false;
        $this->fatal            = false;
        $this->idHashState      = true;
        $this->useGlobalFrom    = false;

        $this->logger->clear();

        if ($cleanSlate) {
            $this->appendTrackingPixel = false;
            $this->queueEnabled        = false;
            $this->from                = $this->systemFrom;
            $this->headers             = [];
            $this->source              = [];
            $this->assets              = [];
            $this->globalTokens        = [];
            $this->assets              = [];
            $this->attachedAssets      = [];
            $this->email               = null;
            $this->copies              = [];
            $this->message             = $this->getMessageInstance();
            $this->subject             = '';
            $this->plainText           = '';
            $this->plainTextSet        = false;
            $this->body                = [
                'content'     => '',
                'contentType' => 'text/html',
                'charset'     => null,
            ];
        }
    }

    /**
     * Search and replace tokens
     * Adapted from \Swift_Plugins_DecoratorPlugin.
     *
     * @param array          $search
     * @param array          $replace
     * @param \Swift_Message $message
     */
    public static function searchReplaceTokens($search, $replace, \Swift_Message &$message)
    {
        // Body
        $body         = $message->getBody();
        $bodyReplaced = str_ireplace($search, $replace, $body, $updated);
        if ($updated) {
            $message->setBody($bodyReplaced);
        }
        unset($body, $bodyReplaced);

        // Subject
        $subject      = $message->getSubject();
        $bodyReplaced = str_ireplace($search, $replace, $subject, $updated);

        if ($updated) {
            $message->setSubject($bodyReplaced);
        }
        unset($subject, $bodyReplaced);

        // Headers
        /** @var \Swift_Mime_Header $header */
        foreach ($message->getHeaders()->getAll() as $header) {
            $headerBody = $header->getFieldBodyModel();
            $updated    = false;
            if (is_array($headerBody)) {
                $bodyReplaced = [];
                foreach ($headerBody as $key => $value) {
                    $count1             = $count2             = 0;
                    $key                = is_string($key) ? str_ireplace($search, $replace, $key, $count1) : $key;
                    $value              = is_string($value) ? str_ireplace($search, $replace, $value, $count2) : $value;
                    $bodyReplaced[$key] = $value;
                    if (($count1 + $count2)) {
                        $updated = true;
                    }
                }
            } else {
                $bodyReplaced = str_ireplace($search, $replace, $headerBody, $updated);
            }

            if (!empty($updated)) {
                $header->setFieldBodyModel($bodyReplaced);
            }

            unset($headerBody, $bodyReplaced);
        }

        // Parts (plaintext)
        $children = (array) $message->getChildren();
        /** @var \Swift_Mime_MimeEntity $child */
        foreach ($children as $child) {
            $childType  = $child->getContentType();
            list($type) = sscanf($childType, '%[^/]/%s');

            if ($type == 'text') {
                $childBody = $child->getBody();

                $bodyReplaced = str_ireplace($search, $replace, $childBody);
                if ($childBody != $bodyReplaced) {
                    $childBody = strip_tags($bodyReplaced);
                    $child->setBody($childBody);
                }
            }

            unset($childBody, $bodyReplaced);
        }
    }

    /**
     * @return string
     */
    public static function getBlankPixel()
    {
        return 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=';
    }

    /**
     * Get a MauticMessage/Swift_Message instance.
     *
     * @return bool|MauticMessage
     */
    public function getMessageInstance()
    {
        try {
            $message = ($this->tokenizationEnabled) ? MauticMessage::newInstance() : \Swift_Message::newInstance();

            return $message;
        } catch (\Exception $e) {
            $this->logError($e);

            return false;
        }
    }

    /**
     * Add an attachment to email.
     *
     * @param string $filePath
     * @param string $fileName
     * @param string $contentType
     * @param bool   $inline
     */
    public function attachFile($filePath, $fileName = null, $contentType = null, $inline = false)
    {
        if ($this->tokenizationEnabled) {
            // Stash attachment to be processed by the transport
            $this->message->addAttachment($filePath, $fileName, $contentType, $inline);
        } else {
            if (file_exists($filePath) && is_readable($filePath)) {
                try {
                    $attachment = \Swift_Attachment::fromPath($filePath);

                    if (!empty($fileName)) {
                        $attachment->setFilename($fileName);
                    }

                    if (!empty($contentType)) {
                        $attachment->setContentType($contentType);
                    }

                    if ($inline) {
                        $attachment->setDisposition('inline');
                    }

                    $this->message->attach($attachment);
                } catch (\Exception $e) {
                    error_log($e);
                }
            }
        }
    }

    /**
     * @param int|Asset $asset
     */
    public function attachAsset($asset)
    {
        $model = $this->factory->getModel('asset');

        if (!$asset instanceof Asset) {
            $asset = $model->getEntity($asset);

            if ($asset == null) {
                return;
            }
        }

        if ($asset->isPublished()) {
            $asset->setUploadDir($this->factory->getParameter('upload_dir'));
            $this->assets[$asset->getId()] = $asset;
        }
    }

    /**
     * Use a template as the body.
     *
     * @param string $template
     * @param array  $vars
     * @param bool   $returnContent
     * @param null   $charset
     *
     * @return void|string
     */
    public function setTemplate($template, $vars = [], $returnContent = false, $charset = null)
    {
        if ($this->templating == null) {
            $this->templating = $this->factory->getTemplating();
        }

        $content = $this->templating->renderResponse($template, $vars)->getContent();

        unset($vars);

        if ($returnContent) {
            return $content;
        }

        $this->setBody($content, 'text/html', $charset);
        unset($content);
    }

    /**
     * Set subject.
     *
     * @param $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set a plain text part.
     *
     * @param $content
     */
    public function setPlainText($content)
    {
        $this->plainText = $content;

        // Update the identifier for the content
        $this->contentHash = md5($this->body['content'].$this->plainText);
    }

    /**
     * @return string
     */
    public function getPlainText()
    {
        return $this->plainText;
    }

    /**
     * Set plain text for $this->message, replacing if necessary.
     */
    protected function setMessagePlainText()
    {
        if ($this->tokenizationEnabled && $this->plainTextSet) {
            // No need to find and replace since tokenization happens at the transport level

            return;
        }

        if ($this->plainTextSet) {
            $children = (array) $this->message->getChildren();

            /** @var \Swift_Mime_MimeEntity $child */
            foreach ($children as $child) {
                $childType = $child->getContentType();
                if ($childType == 'text/plain' && $child instanceof \Swift_MimePart) {
                    $child->setBody($this->plainText);

                    break;
                }
            }
        } else {
            $this->message->addPart($this->plainText, 'text/plain');
            $this->plainTextSet = true;
        }
    }

    /**
     * @param        $content
     * @param string $contentType
     * @param null   $charset
     * @param bool   $ignoreTrackingPixel
     * @param bool   $ignoreEmbedImageConversion
     */
    public function setBody($content, $contentType = 'text/html', $charset = null, $ignoreTrackingPixel = false, $ignoreEmbedImageConversion = false)
    {
        if (!$ignoreEmbedImageConversion && $this->factory->getParameter('mailer_convert_embed_images')) {
            $matches = [];
            if (preg_match_all('/<img.+?src=[\"\'](.+?)[\"\'].*?>/i', $content, $matches)) {
                $replaces = [];
                foreach ($matches[1] as $match) {
                    if (strpos($match, 'cid:') === false) {
                        $replaces[$match] = $this->message->embed(\Swift_Image::fromPath($match));
                    }
                }
                $content = strtr($content, $replaces);
            }
        }

        if (!$ignoreTrackingPixel && $this->factory->getParameter('mailer_append_tracking_pixel')) {
            // Append tracking pixel
            $trackingImg = '<img height="1" width="1" src="{tracking_pixel}" alt="" />';
            if (strpos($content, '</body>') !== false) {
                $content = str_replace('</body>', $trackingImg.'</body>', $content);
            } else {
                $content .= $trackingImg;
            }
        }
        // Update the identifier for the content
        $this->contentHash = md5($content.$this->plainText);

        $this->body = [
            'content'     => $content,
            'contentType' => $contentType,
            'charset'     => $charset,
        ];
    }

    /**
     * Get a copy of the raw body.
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->body['content'];
    }

    /**
     * Return the content identifier.
     *
     * @return string
     */
    public function getContentHash()
    {
        return $this->contentHash;
    }

    /**
     * Set to address(es).
     *
     * @param $addresses
     * @param $name
     *
     * @return bool
     */
    public function setTo($addresses, $name = null)
    {
        if (!is_array($addresses)) {
            $name      = $this->cleanName($name);
            $addresses = [$addresses => $name];
        }

        $this->checkBatchMaxRecipients(count($addresses));

        try {
            $this->message->setTo($addresses);
            $this->queuedRecipients = array_merge($this->queuedRecipients, $addresses);

            return true;
        } catch (\Exception $e) {
            $this->logError($e, 'to');

            return false;
        }
    }

    /**
     * Add to address.
     *
     * @param string $address
     * @param null   $name
     *
     * @return bool
     */
    public function addTo($address, $name = null)
    {
        $this->checkBatchMaxRecipients();

        try {
            $name = $this->cleanName($name);
            $this->message->addTo($address, $name);
            $this->queuedRecipients[$address] = $name;

            return true;
        } catch (\Exception $e) {
            $this->logError($e, 'to');

            return false;
        }
    }

    /**
     * Set CC address(es).
     *
     * @param mixed  $addresses
     * @param string $name
     *
     * @return bool
     */
    public function setCc($addresses, $name = null)
    {
        $this->checkBatchMaxRecipients(count($addresses), 'cc');

        try {
            $name = $this->cleanName($name);
            $this->message->setCc($addresses, $name);

            return true;
        } catch (\Exception $e) {
            $this->logError($e, 'cc');

            return false;
        }
    }

    /**
     * Add cc address.
     *
     * @param mixed $address
     * @param null  $name
     *
     * @return bool
     */
    public function addCc($address, $name = null)
    {
        $this->checkBatchMaxRecipients(1, 'cc');

        try {
            $name = $this->cleanName($name);
            $this->message->addCc($address, $name);

            return true;
        } catch (\Exception $e) {
            $this->logError($e, 'cc');

            return false;
        }
    }

    /**
     * Set BCC address(es).
     *
     * @param mixed  $addresses
     * @param string $name
     *
     * @return bool
     */
    public function setBcc($addresses, $name = null)
    {
        $this->checkBatchMaxRecipients(count($addresses), 'bcc');

        try {
            $name = $this->cleanName($name);
            $this->message->setBcc($addresses, $name);

            return true;
        } catch (\Exception $e) {
            $this->logError($e, 'bcc');

            return false;
        }
    }

    /**
     * Add bcc address.
     *
     * @param string $address
     * @param null   $name
     *
     * @return bool
     */
    public function addBcc($address, $name = null)
    {
        $this->checkBatchMaxRecipients(1, 'bcc');

        try {
            $name = $this->cleanName($name);
            $this->message->addBcc($address, $name);

            return true;
        } catch (\Exception $e) {
            $this->logError($e, 'bcc');

            return false;
        }
    }

    /**
     * @param int    $toBeAdded
     * @param string $type
     *
     * @throws BatchQueueMaxException
     */
    protected function checkBatchMaxRecipients($toBeAdded = 1, $type = 'to')
    {
        if ($this->queueEnabled) {
            // Check if max batching has been hit
            $maxAllowed = $this->transport->getMaxBatchLimit();

            if ($maxAllowed > 0) {
                $currentCount = $this->transport->getBatchRecipientCount($this->message, $toBeAdded, $type);

                if ($currentCount > $maxAllowed) {
                    throw new BatchQueueMaxException();
                }
            }
        }
    }

    /**
     * Set reply to address(es).
     *
     * @param $addresses
     * @param $name
     */
    public function setReplyTo($addresses, $name = null)
    {
        try {
            $name = $this->cleanName($name);
            $this->message->setReplyTo($addresses, $name);
        } catch (\Exception $e) {
            $this->logError($e, 'reply to');
        }
    }

    /**
     * Set a custom return path.
     *
     * @param $address
     */
    public function setReturnPath($address)
    {
        try {
            $this->message->setReturnPath($address);
        } catch (\Exception $e) {
            $this->logError($e, 'return path');
        }
    }

    /**
     * Set from email address and name (defaults to determining automatically unless isGlobal is true).
     *
     * @param string|array $fromEmail
     * @param string       $fromName
     * @param bool|null    $isGlobal
     */
    public function setFrom($fromEmail, $fromName = null, $isGlobal = true)
    {
        if (null !== $isGlobal) {
            if ($isGlobal) {
                if (is_array($fromEmail)) {
                    $this->from = $fromEmail;
                } else {
                    $this->from = [$fromEmail => $fromName];
                }
            } else {
                // Reset the default to the system from
                $this->from = $this->systemFrom;
            }

            $this->useGlobalFrom = $isGlobal;
        }

        try {
            $fromName = $this->cleanName($fromName);
            $this->message->setFrom($fromEmail, $fromName);
        } catch (\Exception $e) {
            $this->logError($e, 'from');
        }
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string|null
     */
    public function getIdHash()
    {
        return $this->idHash;
    }

    /**
     * @param null $idHash
     * @param bool $statToBeGenerated Pass false if a stat entry is not to be created
     */
    public function setIdHash($idHash = null, $statToBeGenerated = true)
    {
        if ($idHash === null) {
            $idHash = uniqid();
        }

        $this->idHash      = $idHash;
        $this->idHashState = $statToBeGenerated;

        // Append pixel to body before send
        $this->appendTrackingPixel = true;

        // Add the trackingID to the $message object in order to update the stats if the email failed to send
        $this->message->leadIdHash = $idHash;
    }

    /**
     * @return array|Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param array|Lead $lead
     * @param bool       $internalSend Set to true if the email is not being sent to this lead
     */
    public function setLead($lead, $interalSend = false)
    {
        $this->lead         = $lead;
        $this->internalSend = $interalSend;
    }

    /**
     * Check if this is not being send directly to the lead.
     *
     * @return bool
     */
    public function isInternalSend()
    {
        return $this->internalSend;
    }

    /**
     * @return array
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param array $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return Email|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param Email $email
     * @param bool  $allowBcc            Honor BCC if set in email
     * @param array $slots               Slots configured in theme
     * @param array $assetAttachments    Assets to send
     * @param bool  $ignoreTrackingPixel Do not append tracking pixel HTML
     *
     * @return bool Returns false if there were errors with the email configuration
     */
    public function setEmail(Email $email, $allowBcc = true, $slots = [], $assetAttachments = [], $ignoreTrackingPixel = false)
    {
        $this->email = $email;

        $subject = $email->getSubject();

        // Convert short codes to emoji
        $subject = EmojiHelper::toEmoji($subject, 'short');

        // Set message settings from the email
        $this->setSubject($subject);

        $fromEmail = $email->getFromAddress();
        $fromName  = $email->getFromName();
        if (!empty($fromEmail) || !empty($fromName)) {
            if (empty($fromName)) {
                $fromName = array_values($this->from)[0];
            } elseif (empty($fromEmail)) {
                $fromEmail = key($this->from);
            }

            $this->setFrom($fromEmail, $fromName, null);
            $this->from = [$fromEmail => $fromName];
        } else {
            $this->from = $this->systemFrom;
        }

        $replyTo = $email->getReplyToAddress();
        if (!empty($replyTo)) {
            $addresses = explode(',', $replyTo);

            // Only a single email is supported
            $this->setReplyTo($addresses[0]);
        }

        if ($allowBcc) {
            $bccAddress = $email->getBccAddress();
            if (!empty($bccAddress)) {
                $addresses = array_fill_keys(array_map('trim', explode(',', $bccAddress)), null);
                foreach ($addresses as $bccAddress => $name) {
                    $this->addBcc($bccAddress, $name);
                }
            }
        }

        if ($plainText = $email->getPlainText()) {
            $this->setPlainText($plainText);
        }

        $BCcontent  = $email->getContent();
        $customHtml = $email->getCustomHtml();
        // Process emails created by Mautic v1
        if (empty($customHtml) && !empty($BCcontent)) {
            $template = $email->getTemplate();
            if (empty($slots)) {
                $template = $email->getTemplate();
                $slots    = $this->factory->getTheme($template)->getSlots('email');
            }

            if (isset($slots[$template])) {
                $slots = $slots[$template];
            }

            $this->processSlots($slots, $email);

            $logicalName = $this->factory->getHelper('theme')->checkForTwigTemplate(':'.$template.':email.html.php');

            $customHtml = $this->setTemplate($logicalName, [
                'slots'    => $slots,
                'content'  => $email->getContent(),
                'email'    => $email,
                'template' => $template,
            ], true);
        }

        // Convert short codes to emoji
        $customHtml = EmojiHelper::toEmoji($customHtml, 'short');

        $this->setBody($customHtml, 'text/html', null, $ignoreTrackingPixel);

        // Reset attachments
        $this->assets = $this->attachedAssets = [];
        if (empty($assetAttachments)) {
            if ($assets = $email->getAssetAttachments()) {
                foreach ($assets as $asset) {
                    $this->attachAsset($asset);
                }
            }
        } else {
            foreach ($assetAttachments as $asset) {
                $this->attachAsset($asset);
            }
        }

        return empty($this->errors);
    }

    /**
     * Set custom headers.
     *
     * @param array $headers
     */
    public function setCustomHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @param $name
     * @param $value
     */
    public function addCustomHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * @return array
     */
    public function getCustomHeaders()
    {
        return $this->headers;
    }

    /**
     * Generate and insert List-Unsubscribe header.
     */
    private function addUnsubscribeHeader()
    {
        if (isset($this->idHash)) {
            $unsubscribeLink                   = $this->factory->getRouter()->generate('le_email_unsubscribe', ['idHash' => $this->idHash], true);
            $this->headers['List-Unsubscribe'] = "<$unsubscribeLink>";
        }
    }

    /**
     * Append tokens.
     *
     * @param array $tokens
     */
    public function addTokens(array $tokens)
    {
        $this->globalTokens = array_merge($this->globalTokens, $tokens);
    }

    /**
     * Set tokens.
     *
     * @param array $tokens
     */
    public function setTokens(array $tokens)
    {
        $this->globalTokens = $tokens;
    }

    /**
     * Get tokens.
     *
     * @return array
     */
    public function getTokens()
    {
        $tokens = array_merge($this->globalTokens, $this->eventTokens);

        // Include the tracking pixel token as it's auto appended to the body
        if ($this->appendTrackingPixel) {
            $tokens['{tracking_pixel}'] = $this->factory->getRouter()->generate(
                'le_email_tracker',
                [
                    'idHash' => $this->idHash,
                ],
                true
            );
        } else {
            $tokens['{tracking_pixel}'] = self::getBlankPixel();
        }

        return $tokens;
    }

    /**
     * @return array
     */
    public function getGlobalTokens()
    {
        return $this->globalTokens;
    }

    /**
     * Parses html into basic plaintext.
     *
     * @param string $content
     */
    public function parsePlainText($content = null)
    {
        if ($content == null) {
            if (!$content = $this->message->getBody()) {
                $content = $this->body['content'];
            }
        }

        $request = $this->factory->getRequest();
        $parser  = new PlainTextHelper([
            'base_url' => $request->getSchemeAndHttpHost().$request->getBasePath(),
        ]);

        $this->plainText = $parser->setHtml($content)->getText();
    }

    /**
     * Tell the mailer to use batching/tokenized emails if available.  It's up to the function calling to execute flushQueue to send the mail.
     *
     * @deprecated 2.1.1 - to be removed in 3.0; use enableQueue() instead
     *
     * @param bool $tokenizationEnabled
     *
     * @return bool Returns true if batching/tokenization is supported by the mailer
     */
    public function useMailerTokenization($tokenizationEnabled = true)
    {
        @trigger_error('useMailerTokenization() is now deprecated. Use enableQueue() instead.', E_USER_DEPRECATED);

        $this->enableQueue($tokenizationEnabled);
    }

    /**
     * Enables queue mode if the transport supports tokenization.
     *
     * @param bool $enabled
     */
    public function enableQueue($enabled = true)
    {
        if ($this->tokenizationEnabled) {
            $this->queueEnabled = $enabled;
        }
    }

    /**
     * Dispatch send event to generate tokens.
     *
     * @return array
     */
    public function dispatchSendEvent()
    {
        if ($this->dispatcher == null) {
            $this->dispatcher = $this->factory->getDispatcher();
        }

        $event  = new EmailSendEvent($this);

        $leadId = $event->getLead()['id'];
        if ($leadId != 0) {
            $lead = $this->factory->getModel('lead')->getEntity($leadId);
            $event->setLead($lead);

            $this->dispatcher->dispatch(EmailEvents::EMAIL_ON_SEND, $event);

            $this->eventTokens = array_merge($this->eventTokens, $event->getTokens(false));

            unset($event, $lead);
        }
    }

    /**
     * Log exception.
     *
     * @param      $error
     * @param null $context
     */
    protected function logError($error, $context = null)
    {
        if ($error instanceof \Exception) {
            $errorMessage = $error->getMessage();
            $error        = ('dev' === MAUTIC_ENV) ? (string) $error : $errorMessage;

            // Clean up the error message
            $errorMessage = trim(preg_replace('/(.*?)Log data:(.*)$/is', '$1', $errorMessage));

            $this->fatal = true;
        } else {
            $errorMessage = trim($error);
        }

        $logDump = $this->logger->dump();
        if (!empty($logDump) && strpos($error, $logDump) === false) {
            $error .= " Log data: $logDump";
        }

        if ($context) {
            $error .= " ($context)";

            if ('send' === $context) {
                $error .= '; '.implode(', ', $this->errors['failures']);
            }
        }

        $this->errors[] = $errorMessage;

        $this->logger->clear();

        $this->factory->getLogger()->log('error', '[MAIL ERROR] '.$error);
    }

    /**
     * Get list of errors.
     *
     * @param bool $reset Resets the error array in preparation for the next mail send or else it'll fail
     *
     * @return array
     */
    public function getErrors($reset = true)
    {
        $errors = $this->errors;

        if ($reset) {
            $this->clearErrors();
        }

        return $errors;
    }

    /**
     * Clears the errors from a previous send.
     */
    public function clearErrors()
    {
        $this->errors = [];
        $this->fatal  = false;
    }

    /**
     * Return transport.
     *
     * @return \Swift_Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Creates a download stat for the asset.
     */
    protected function createAssetDownloadEntries()
    {
        // Nothing was sent out so bail
        if ($this->fatal || empty($this->assetStats)) {
            return;
        }

        if (isset($this->errors['failures'])) {
            // Remove the failures from the asset queue
            foreach ($this->errors['failures'] as $failed) {
                unset($this->assetStats[$failed]);
            }
        }

        // Create a download entry if there is an Asset attachment
        if (!empty($this->assetStats) && false) {
            /** @var \Mautic\AssetBundle\Model\AssetModel $assetModel */
            $assetModel = $this->factory->getModel('asset');
            foreach ($this->assets as $asset) {
                foreach ($this->assetStats as $stat) {
                    $assetModel->trackDownload(
                        $asset,
                        null,
                        200,
                        $stat
                    );
                }

                $assetModel->upDownloadCount($asset, count($this->assetStats), true);
            }
        }

        // Reset the stat
        $this->assetStats = [];
    }

    /**
     * Queues the details to note if a lead received an asset if no errors are generated.
     *
     * @param null $contactEmail
     * @param null $metadata
     */
    protected function queueAssetDownloadEntry($contactEmail = null, array $metadata = null)
    {
        if ($this->internalSend || empty($this->assets)) {
            return;
        }

        if (null === $contactEmail) {
            if (!$this->lead) {
                return;
            }

            $contactEmail = $this->lead['email'];
            $contactId    = $this->lead['id'];
            $emailId      = $this->email->getId();
            $idHash       = $this->idHash;
        } else {
            $contactId = $metadata['leadId'];
            $emailId   = $metadata['emailId'];
            $idHash    = $metadata['hashId'];
        }

        $this->assetStats[$contactEmail] = [
            'lead'        => $contactId,
            'email'       => $emailId,
            'source'      => ['email', $emailId],
            'tracking_id' => $idHash,
        ];
    }

    /**
     * Returns if the mailer supports and is in tokenization mode.
     *
     * @return bool
     */
    public function inTokenizationMode()
    {
        return $this->tokenizationEnabled;
    }

    /**
     * @param $url
     *
     * @return \Mautic\PageBundle\Entity\Redirect|null|object
     */
    public function getTrackableLink($url)
    {
        // Ensure a valid URL and that it has not already been found
        if (substr($url, 0, 4) !== 'http' && substr($url, 0, 3) !== 'ftp') {
            return null;
        }

        if ($this->email) {
            // Get a Trackable which is channel aware
            /** @var \Mautic\PageBundle\Model\TrackableModel $trackableModel */
            $trackableModel = $this->factory->getModel('page.trackable');
            $trackable      = $trackableModel->getTrackableByUrl($url, 'email', $this->email->getId());

            return $trackable->getRedirect();
        }

        /** @var \Mautic\PageBundle\Model\RedirectModel $redirectModel */
        $redirectModel = $this->factory->getModel('page.redirect');

        return $redirectModel->getRedirectByUrl($url);
    }

    /**
     * Create an email stat.
     *
     * @param bool|true   $persist
     * @param string|null $emailAddress
     * @param null        $listId
     * @param int         $is_failed
     *@param int         $is_unsubscribe
     * @param int $is_bounce
     *
     * @return Stat
     */
    public function createEmailStat($persist = true, $emailAddress = null, $listId = null, $is_failed = 0, $is_unsubscribe=0, $is_bounce=0)
    {
        //create a stat
        $stat = new Stat();
        $stat->setDateSent(new \DateTime());
        $stat->setEmail($this->email);

        // Note if a lead
        if (null !== $this->lead) {
            try {
                $stat->setLead($this->factory->getEntityManager()->getReference('MauticLeadBundle:Lead', $this->lead['id']));
            } catch (ORMException $exception) {
                // keep IDE happy
            }
            $emailAddress = $this->lead['email'];
        }

        // Find email if applicable
        if (null === $emailAddress) {
            // Use the last address set
            $emailAddresses = $this->message->getTo();

            if (count($emailAddresses)) {
                end($emailAddresses);
                $emailAddress = key($emailAddresses);
            }
        }
        $stat->setEmailAddress($emailAddress);

        // Note if sent from a lead list
        if (null !== $listId) {
            try {
                $stat->setList($this->factory->getEntityManager()->getReference('MauticLeadBundle:LeadList', $listId));
            } catch (ORMException $exception) {
                // keep IDE happy
            }
        }

        $stat->setTrackingHash($this->idHash);
        if (!empty($this->source)) {
            $stat->setSource($this->source[0]);
            $stat->setSourceId($this->source[1]);
        }

        $stat->setTokens($this->getTokens());

        $stat->setIsFailed($is_failed);
        $stat->setIsUnsubscribe($is_unsubscribe);
        $stat->setIsBounce($is_bounce);

        /** @var \Mautic\EmailBundle\Model\EmailModel $emailModel */
        $emailModel = $this->factory->getModel('email');

        // Save a copy of the email - use email ID if available simply to prevent from having to rehash over and over
        $id = (null !== $this->email) ? $this->email->getId() : md5($this->subject.$this->body['content']);
        if (!isset($this->copies[$id])) {
            $hash = (strlen($id) !== 32) ? md5($this->subject.$this->body['content']) : $id;

            $copy        = $emailModel->getCopyRepository()->findByHash($hash);
            $copyCreated = false;
            if (null === $copy) {
                if (!$emailModel->getCopyRepository()->saveCopy($hash, $this->subject, $this->body['content'])) {
                    // Try one more time to find the ID in case there was overlap when creating
                    $copy = $emailModel->getCopyRepository()->findByHash($hash);
                } else {
                    $copyCreated = true;
                }
            }

            if ($copy || $copyCreated) {
                $this->copies[$id] = $hash;
            }
        }

        if (isset($this->copies[$id])) {
            try {
                $stat->setStoredCopy($this->factory->getEntityManager()->getReference('MauticEmailBundle:Copy', $this->copies[$id]));
            } catch (ORMException $exception) {
                // keep IDE happy
            }
        }

        if ($persist) {
            $emailModel->getStatRepository()->saveEntity($stat);
        }

        return $stat;
    }

    /**
     * Check to see if a monitored email box is enabled and configured.
     *
     * @param $bundleKey
     * @param $folderKey
     *
     * @return bool|array
     */
    public function isMontoringEnabled($bundleKey, $folderKey)
    {
        /** @var \Mautic\EmailBundle\MonitoredEmail\Mailbox $mailboxHelper */
        $mailboxHelper = $this->factory->getHelper('mailbox');

        if ($mailboxHelper->isConfigured($bundleKey, $folderKey)) {
            return $mailboxHelper->getMailboxSettings();
        }

        return false;
    }

    /**
     * Generate bounce email for the lead.
     *
     * @param null $idHash
     *
     * @return bool|string
     */
    public function generateBounceEmail($idHash = null)
    {
        $monitoredEmail = false;

        if ($settings = $this->isMontoringEnabled('EmailBundle', 'bounces')) {
            // Append the bounce notation
            list($email, $domain) = explode('@', $settings['address']);
            $email .= '+bounce';
            if ($idHash || $this->idHash) {
                $email .= '_'.($idHash ?: $this->idHash);
            }
            $monitoredEmail = $email.'@'.$domain;
        }

        return $monitoredEmail;
    }

    /**
     * Generate an unsubscribe email for the lead.
     *
     * @param null $idHash
     *
     * @return bool|string
     */
    public function generateUnsubscribeEmail($idHash = null)
    {
        $monitoredEmail = false;

        if ($settings = $this->isMontoringEnabled('EmailBundle', 'unsubscribes')) {
            // Append the bounce notation
            list($email, $domain) = explode('@', $settings['address']);
            $email .= '+unsubscribe';
            if ($idHash || $this->idHash) {
                $email .= '_'.($idHash ?: $this->idHash);
            }
            $monitoredEmail = $email.'@'.$domain;
        }

        return $monitoredEmail;
    }

    /**
     * A large number of mail sends may result on timeouts with SMTP servers. This checks for the number of email sends and restarts the transport if necessary.
     *
     * @param bool $force
     */
    public function checkIfTransportNeedsRestart($force = false)
    {
        // Check if we should restart the SMTP transport
        if ($this->transport instanceof \Swift_SmtpTransport) {
            $maxNumberOfMessages = (method_exists($this->transport, 'getNumberOfMessagesTillRestart'))
                ? $this->transport->getNumberOfMessagesTillRestart() : 50;

            $maxNumberOfMinutes = (method_exists($this->transport, 'getNumberOfMinutesTillRestart'))
                ? $this->transport->getNumberOfMinutesTillRestart() : 2;

            $numberMinutesRunning = floor(time() - $this->transportStartTime) / 60;

            if ($force || $this->messageSentCount >= $maxNumberOfMessages || $numberMinutesRunning >= $maxNumberOfMinutes) {
                // Stop the transport
                $this->transport->stop();
                $this->messageSentCount = 0;
            }
        }
    }

    /**
     * @param $slots
     * @param Email $entity
     */
    public function processSlots($slots, $entity)
    {
        /** @var \Mautic\CoreBundle\Templating\Helper\SlotsHelper $slotsHelper */
        $slotsHelper = $this->factory->getHelper('template.slots');

        $content = $entity->getContent();

        foreach ($slots as $slot => $slotConfig) {
            if (is_numeric($slot)) {
                $slot       = $slotConfig;
                $slotConfig = [];
            }

            $value = isset($content[$slot]) ? $content[$slot] : '';
            $slotsHelper->set($slot, $value);
        }
    }

    /**
     * Clean the name - if empty, set as null to ensure pretty headers.
     *
     * @param $name
     *
     * @return null|string
     */
    protected function cleanName($name)
    {
        if (null !== $name) {
            $name = trim($name);

            // If empty, replace with null so that email clients do not show empty name because of To: '' <email@domain.com>
            if (empty($name)) {
                $name = null;
            }
        }

        return $name;
    }

    /**
     * @param $contact
     *
     * @return bool|array
     */
    protected function getContactOwner(&$contact)
    {
        $owner = false;

        if ($this->factory->getParameter('mailer_is_owner') && is_array($contact) && isset($contact['id'])) {
            if (!isset($contact['owner_id'])) {
                $contact['owner_id'] = 0;
            } elseif (isset($contact['owner_id'])) {
                if (isset(self::$leadOwners[$contact['owner_id']])) {
                    $owner = self::$leadOwners[$contact['owner_id']];
                } elseif ($owner = $this->factory->getModel('lead')->getRepository()->getLeadOwner($contact['owner_id'])) {
                    self::$leadOwners[$owner['id']] = $owner;
                }
            }
        }

        return $owner;
    }

    /**
     * @param $owner
     *
     * @return mixed
     */
    protected function getContactOwnerSignature($owner)
    {
        return empty($owner['signature'])
            ? false
            : EmojiHelper::toHtml(
                str_replace('|FROM_NAME|', $owner['first_name'].' '.$owner['last_name'], nl2br($owner['signature']))
            );
    }

    /**
     * Validates a given address to ensure RFC 2822, 3.6.2 specs.
     *
     * @deprecated 2.11.0 to be removed in 3.0; use Mautic\EmailBundle\Helper\EmailValidator
     *
     * @param $address
     *
     * @throws \Swift_RfcComplianceException
     */
    public static function validateEmail($address)
    {
        $invalidChar = strpbrk($address, '\'^&*%');

        if ($invalidChar !== false) {
            throw new \Swift_RfcComplianceException(
                'Email address ['.$address.
                '] contains this invalid character: '.substr($invalidChar, 0, 1)
            );
        }

        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw new \Swift_RfcComplianceException(
                'Email address ['.$address.'] is invalid'
            );
        }
    }

    public function emailstatus($sendEmail = true)
    {
        $cacheHelper = $this->factory->get('mautic.helper.cache');
        $cacheHelper->clearContainerFile();
        $config         = $this->coreParametersHelper->getParameter('email_status');
        $configurator   = $this->factory->get('mautic.configurator');
        // $fromname       ='';
        // $fromemail      ='';
        //$emailmodel     =$this->factory->getModel('email');
        //   $defaultsender  =$emailmodel->getDefaultSenderProfile();
        //  if (sizeof($defaultsender) > 0) {
        // $fromname =$defaultsender[0];
        // $fromemail=$defaultsender[1];
        //  }
        $settings       = [
            'amazon_region'     => $this->coreParametersHelper->getParameter('mailer_amazon_region'),
            'api_key'           => $this->coreParametersHelper->getParameter('mailer_api_key'),
            'authMode'          => $this->coreParametersHelper->getParameter('mailer_auth_mode'),
            'encryption'        => $this->coreParametersHelper->getParameter('mailer_encryption'),
            'from_email'        => $this->coreParametersHelper->getParameter('mailer_from_email'),
            'from_name'         => $this->coreParametersHelper->getParameter('mailer_from_name'),
            'host'              => $this->coreParametersHelper->getParameter('mailer_host'),
            'password'          => $this->coreParametersHelper->getParameter('mailer_password'),
            'port'              => $this->coreParametersHelper->getParameter('mailer_port'),
            'send_test'         => true,
            'transport'         => $this->coreParametersHelper->getParameter('mailer_transport'),
            'user'              => $this->coreParametersHelper->getParameter('mailer_user'),
            'toemail'           => 'abc@abc.com',
            'trackingcode'      => '',
            'additionalinfo'    => '',
        ];
        if ($config == 'Active') {
            if (!$sendEmail) {
                return true;
            }
            $cacheHelper = $this->factory->get('mautic.helper.cache');
            $cacheHelper->clearContainerFile();
            $result=$this->testEmailServerConnection($settings, false);
            if ($result['success']) {
                return true;
            } else {
                // $mailer = $this->container->get('le.transport.elasticemail.transactions');
                //$this->failedSMTPEmailtoUser($mailer);
                $configurator->mergeParameters(['email_status' => 'InActive']);
                $configurator->write();

                return false;
            }
        } else {
            if (!$sendEmail) {
                return false;
            }
            $cacheHelper = $this->factory->get('mautic.helper.cache');
            $cacheHelper->clearContainerFile();
            $result=$this->testEmailServerConnection($settings, false);
            if ($result['success']) {
                $configurator->mergeParameters(['email_status' => 'Active']);
                $configurator->write();

                return true;
            } else {
                return false;
            }
        }
    }

    public function testEmailServerConnection($settings, $default=false)
    {
        $dataArray    = ['success' => 0, 'message' => '', 'to_address_empty'=>false];
        $transport    = $settings['transport'];
        $user         = $this->factory->get('mautic.helper.user')->getUser();
        $emailmodel   = $this->factory->getModel('email');
        // $translator   = $this->factory->get('translator');
//        $defaultsender=$emailmodel->getDefaultSenderProfile();
//        if (sizeof($defaultsender) > 0 && $settings['from_email'] == '') {
//            $settings['from_name'] =$defaultsender[0];
//            $settings['from_email']=$defaultsender[1];
//        }
        switch ($transport) {
            case 'gmail':
                $mailer = new \Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl');
                break;
            case 'smtp':
                $mailer = new \Swift_SmtpTransport($settings['host'], $settings['port'], $settings['encryption']);
                break;
            default:
                if ($this->container->has($transport)) {
                    $mailer = $this->container->get($transport);
                    if ('le.transport.amazon' == $transport) {
                        if (!$mailer instanceof AmazonApiTransport) {
                            $mailer->setHost($settings['amazon_region']);
                        } elseif ($mailer instanceof AmazonApiTransport) {
                            if (empty($settings['password'])) {
                                $settings['password'] = $this->factory->get('mautic.helper.core_parameters')->getParameter('mailer_password');
                            }
                            $sesmailer = new SimpleEmailService($settings['user'], $settings['password'], $settings['amazon_region']);
                            $mailer->setSimpleemailservice($sesmailer);
                        }
                    } elseif ('le.transport.sendgrid_api' == $transport) {
                        if (empty($settings['api_key'])) {
                            $settings['api_key'] = $this->factory->get('mautic.helper.core_parameters')->getParameter('mailer_api_key');
                        }
                        $sendgrid = new \SendGrid($settings['api_key']);
                        if ($mailer instanceof SendgridApiTransport) {
                            $mailer->getSendGridApiFacade()->getSendGridWrapper()->setSendGrid($sendgrid);
                        }
                    } elseif ('le.transport.sparkpost' == $transport) {
                        if ($mailer instanceof SparkpostTransport) {
                            $mailer->setApiKey($settings['api_key']);
                        }
                    }
                }
        }

        if (method_exists($mailer, 'setMauticFactory')) {
            $mailer->setMauticFactory($this->factory);
        }

        if (!empty($mailer)) {
            try {
                if (method_exists($mailer, 'setApiKey')) {
                    if (empty($settings['api_key'])) {
                        $settings['api_key'] = $this->coreParametersHelper->getParameter('mailer_api_key');
                    }
                    $mailer->setApiKey($settings['api_key']);
                }
            } catch (\Exception $exception) {
                // Transport had magic method defined and threw an exception
            }

            try {
                if (is_callable([$mailer, 'setUsername']) && is_callable([$mailer, 'setPassword'])) {
                    if (empty($settings['password'])) {
                        $settings['password'] = $this->factory->get('mautic.helper.core_parameters')->getParameter('mailer_password');
                    }
                    $mailer->setUsername($settings['user']);
                    $mailer->setPassword($settings['password']);
                }
            } catch (\Exception $exception) {
                // Transport had magic method defined and threw an exception
            }

            $logger = new \Swift_Plugins_Loggers_ArrayLogger();
            $mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));

            try {
                $translator = $this->factory->get('translator');
                if ($settings['send_test'] == 'true' || $settings['toemail'] != '') {
                    $email = '';
                    if ($settings['toemail'] != '' && $default) {
                        $lemailer = $this->factory->get('le.transport.elasticemail.transactions');
                        $lemailer->start();
                        $trackingcode = $settings['trackingcode'];
                        $mailbody     = $translator->trans('le.email.website_tracking.body');
                        $mailbody     = str_replace('|FROM_EMAIL|', $settings['from_email'], nl2br($mailbody));
                        $mailbody     = str_replace('|Tracking|', $trackingcode, nl2br($mailbody));
                        if ($settings['additionalinfo'] != '') {
                            $additioninfo = $settings['additionalinfo'];
                            $mailbody     = str_replace('|USER_CONTENT|', $additioninfo, nl2br($mailbody));
                            //$mailbody .= "$additioninfo<br>";
                        }
                        $mailbody .= '</body></html>';
                        $message = \Swift_Message::newInstance()
                            ->setSubject($translator->trans('le.email.config.mailer.transport.tracking_send.subject'));
                        $message->setBody($mailbody, 'text/html');
                        $message->setTo([$settings['toemail']]);
                        $message->setFrom(['notifications@'.$translator->trans('le.core.email.utm.source').'.io' => $translator->trans('le.core.email.utm.source')]);
                        $message->setReplyTo($this->factory->getUser()->getEmail());
                        $lemailer->send($message);
                    } else {
                        $mailer->start();
                        $message = \Swift_Message::newInstance()
                                ->setSubject($translator->trans('le.email.config.mailer.transport.test_send.subject'));
                        $userFullName = trim($user->getFirstName().' '.$user->getLastName());
                        $mailbody     = $translator->trans('le.email.config.mailer.transport.test_send.body', ['%name%' => $userFullName]);
                        $message->setBody($mailbody, 'text/html');
                        if (empty($userFullName)) {
                            $userFullName = null;
                        }
                        $message->setFrom([$settings['from_email'] => $settings['from_name']]);
                        if ($settings['toemail'] != '') {
                            $email = $settings['toemail'];
                            $message->setTo([$settings['toemail']]);
                        } else {
                            $email = $user->getEmail();
                            $message->setTo([$user->getEmail() => $userFullName]);
                        }
                        $mailer->send($message);
                    }
                    $dataArray['success'] = 1;
                    if ($settings['send_test'] == 'true') {
                        $message= $translator->trans('mautic.core.success', ['%email%'=>$email]);
                    } else {
                        $message = $translator->trans('mautic.core.success.tracking', ['%email%'=>$email]);
                    }
                    $dataArray['message'] =$message;
                } else {
                    $dataArray['success']         = 0;
                    $dataArray['to_address_empty']=true;
                    $dataArray['message']         = $translator->trans('mautic.core.failed');
                }
            } catch (\Exception $e) {
                $dataArray['success'] = 0;
                //$dataArray['message'] = $e->getMessage().'<br />'.$logger->dump();
                $geterror             = $e->getMessage();
                $dataArray['message'] = $this->geterrormsg($geterror);
            }
        }
        if ($dataArray['success']) {
            $emailmodel->updateDefaultSenderProfile($settings['from_email']);
        }

        return $dataArray;
    }

    public function geterrormsg($geterror)
    {
        $translator = $this->factory->get('translator');
        $errors     = [
            ' with RFC 2822',
            'Unauthorized',
            'API key is required',
            'Forbidden',
            'Invalid domain',
            'Failed to authenticate on SMTP',
            '421 Error:',
            '530 Authentication required',
            'invalid, expired, or revoked',
            'Email address is not verified',
            ' in the request is invalid',
            'AWS Secret Access Key',
            'is not authorized to perform',
            'Request is missing Authentication Token',
            'Authenticated user is not authorized to send mail',
            'Name or service not known',
       ];
        $errormsg = [
            'le.email.config.invalidemail.error',
            'le.email.config.IP_Restrcited.error',
            'le.email.config.API_Key.required.error',
            'le.email.config.Invalid_API_Key.error',
            'le.email.config.Invalid_Domain.error',
            'le.email.config.Invalid_Credentials.error',
            'le.email.config.Permission_Denied.error',
            'le.email.config.username.required.error',
            'le.email.config.sendgrid.invalid.error',
            'le.email.config.aws.not.verified.error',
            'le.email.config.aws.username.invalid.error',
            'le.email.config.aws.passwd.invalid.error',
            'le.email.config.aws.permission.denie.error',
            'le.email.config.aws.missing.error',
            'le.email.config.sendgrid.user.not.authorized',
            'le.email.config.other.smtp',
       ];
        for ($i = 0; $i < sizeof($errors); ++$i) {
            if (strpos($geterror, $errors[$i]) !== false) {
                $specificerror = $errormsg[$i];
                $specificerror = $translator->trans($specificerror);
            }
        }
        $message = isset($specificerror) ? $specificerror : $geterror;

        return $message;
    }

    public function appendHeadTag($content)
    {
        $finalContent='';
        $contentarr  = explode('<html>', $content);
        $headtag     = '<head><title></title></head>';

        if (!empty($contentarr)) {
            $contentarr[0] .= $headtag;
            if (sizeof($contentarr) > 1) {
                $finalContent = $contentarr[0].$contentarr[1];
            } else {
                $finalContent = $contentarr[0];
            }
        }

        return $finalContent;
    }

    public function replaceTitleinContent($title, $content)
    {
        $title   = "<title>$title</title>";
        $content = preg_replace("/(<title>)(.*?)(<\/title>)/i", $title, $content);

        return $content;
    }

    public function replaceLinkinContent($content)
    {
        $doc                      = new \DOMDocument();
        $doc->strictErrorChecking = false;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$content);
        $head=$doc->getElementsByTagName('head');

        $favicon = $this->factory->get('templating.helper.assets')->getUrl('media/images/favicon.ico');
        $head    = $head->item(0);

        $l1 = $doc->createElement('link');
        $l1->setAttribute('rel', 'icon');
        $l1->setAttribute('type', 'image/x-icon');
        $l1->setAttribute('href', $favicon);
        $l2 = $doc->createElement('link');
        $l2->setAttribute('rel', 'icon');
        $l2->setAttribute('sizes', '192x192');
        $l2->setAttribute('href', $favicon);
        $l3 = $doc->createElement('link');
        $l3->setAttribute('rel', 'apple-touch-icon');
        $l3->setAttribute('href', $favicon);
        if ($head) {
            $head->appendChild($l1);
            $head->appendChild($l2);
            $head->appendChild($l3);
        }
        $headContent=$doc->saveHTML();

        libxml_clear_errors();

        return $headContent;
    }

    public function failedSMTPEmailtoUser($mailer)
    {
        $mailer->start();
        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $model */
        $model               = $this->factory->getModel('subscription.accountinfo');
        $usermodel           = $this->factory->getModel('user.user');
        $licenseHelper       = $this->factory->getHelper('licenseinfo');
        $emailprovider       = $licenseHelper->getEmailProvider();
        $accrepo             = $model->getRepository();
        $accountentity       = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0];
        } else {
            $account = new Account();
        }
        $name       = $account->getAccountname();
        $useremail  = $account->getEmail();
        $domain     = $account->getDomainname();
        $message    = \Swift_Message::newInstance();
        $currentuser=$usermodel->getCurrentUserEntity();
        $email      =$currentuser->getEmail();
        $message->setTo([$useremail => $name]);
        $message->setFrom(['notifications@anyfunnels.io' => 'AnyFunnels']);
        $message->setReplyTo(['support@anyfunnels.com' => 'AnyFunnels']);
        $message->setSubject($this->factory->getTranslator()->trans('le.email.smtp.failed.msg'));
        $text = "<!DOCTYPE html>
        <html>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>

	<head>
		<title></title>
	</head>
	<body>
		<div>
        Hey, $name!
        <br>
       
        <br>There seems to be an error in your email service ($emailprovider) configuration.
        <br>Kindly take necessary steps and update your settings in your account to reactivate your email sending.
        </div>
		
	</body>
</html>";
        $message->setBody($text, 'text/html');
        $mailer->send($message);
    }
}
