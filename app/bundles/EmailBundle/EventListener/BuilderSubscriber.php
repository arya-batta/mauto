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
use Mautic\CoreBundle\Form\Type\SlotTextType;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\EmojiHelper;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailBuilderEvent;
use Mautic\EmailBundle\Event\EmailSendEvent;
use Mautic\EmailBundle\Model\EmailModel;
use Mautic\PageBundle\Entity\Trackable;
use Mautic\PageBundle\Model\RedirectModel;
use Mautic\PageBundle\Model\TrackableModel;

/**
 * Class BuilderSubscriber.
 */
class BuilderSubscriber extends CommonSubscriber
{
    /**
     * @var CoreParametersHelper
     */
    protected $coreParametersHelper;

    /**
     * @var EmailModel
     */
    protected $emailModel;

    /**
     * @var TrackableModel
     */
    protected $pageTrackableModel;

    /**
     * @var RedirectModel
     */
    protected $pageRedirectModel;

    /**
     * BuilderSubscriber constructor.
     *
     * @param CoreParametersHelper $coreParametersHelper
     * @param EmailModel           $emailModel
     * @param TrackableModel       $trackableModel
     * @param RedirectModel        $redirectModel
     */
    public function __construct(
        CoreParametersHelper $coreParametersHelper,
        EmailModel $emailModel,
        TrackableModel $trackableModel,
        RedirectModel $redirectModel
    ) {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->emailModel           = $emailModel;
        $this->pageTrackableModel   = $trackableModel;
        $this->pageRedirectModel    = $redirectModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_ON_BUILD => ['onEmailBuild', 0],
            EmailEvents::EMAIL_ON_SEND  => [
                ['onEmailGenerate', 0],
                // Ensure this is done last in order to catch all tokenized URLs
                ['convertUrlsToTokens', -9999],
            ],
            EmailEvents::EMAIL_ON_DISPLAY => [
                ['onEmailGenerate', 0],
                // Ensure this is done last in order to catch all tokenized URLs
                ['convertUrlsToTokens', -9999],
            ],
        ];
    }

    /**
     * @param EmailBuilderEvent $event
     */
    public function onEmailBuild(EmailBuilderEvent $event)
    {
        if ($event->abTestWinnerCriteriaRequested()) {
            //add AB Test Winner Criteria
            $openRate = [
                'group'    => 'le.email.stats',
                'label'    => 'le.email.abtest.criteria.open',
                'callback' => '\Mautic\EmailBundle\Helper\AbTestHelper::determineOpenRateWinner',
            ];
            $event->addAbTestWinnerCriteria('email.openrate', $openRate);

            $clickThrough = [
                'group'    => 'le.email.stats',
                'label'    => 'le.email.abtest.criteria.clickthrough',
                'callback' => '\Mautic\EmailBundle\Helper\AbTestHelper::determineClickthroughRateWinner',
            ];
            $event->addAbTestWinnerCriteria('email.clickthrough', $clickThrough);
        }

        $tokens = [
            '{lead_owner_name}'                                         => $this->translator->trans('le.email.token.lead_owner_name'),
            '{lead_owner_mobile}'                                       => $this->translator->trans('le.email.token.lead_owner_mobile'),
            '{lead_owner_email}'                                        => $this->translator->trans('le.email.token.lead_owner_email'),
            '{from_email}'                                              => $this->translator->trans('le.email.token.from_email'),
            '{postal_address}'                                          => $this->translator->trans('le.email.token.postal_address'),
            '{{global_unsubscribe_link}}'                               => $this->translator->trans('le.email.token.unsubscribe_text'),
            '<a href=\'{{list_unsubscribe_link}}\'>Unsubscribe</a>'     => $this->translator->trans('le.lead.list.unsubscribe.text'),
            '{update_your_profile_link}'                                => $this->translator->trans('le.email.token.updatelead_text'),
            '<a href=\'{{confirmation_link}}\'>Confirm Here</a>'        => $this->translator->trans('le.lead.list.confirm.text'),
            '{webview_link}'                                            => $this->translator->trans('le.email.token.webview_text'),
            //'{signature}'        => $this->translator->trans('le.email.token.signature'),
            //'{subject}'          => $this->translator->trans('le.email.subject'),
        ];

        if ($event->tokensRequested(array_keys($tokens))) {
            $event->addTokens(
                $event->filterTokens($tokens),
                true
            );
        }

        // these should not allow visual tokens
        //$tokens = [
        //    '{unsubscribe_url}' => $this->translator->trans('le.email.token.unsubscribe_url'),
        //    '{webview_url}'     => $this->translator->trans('le.email.token.webview_url'),
        //];
        if ($event->tokensRequested(array_keys($tokens))) {
            $event->addTokens(
                $event->filterTokens($tokens)
            );
        }

        if ($event->slotTypesRequested()) {
            $event->addSlotType(
                'text',
                $this->translator->trans('mautic.core.slot.label.text'),
                'font',
                'MauticCoreBundle:Slots:text.html.php',
                SlotTextType::class,
                1000
            );
            $event->addSlotType(
                'image',
                $this->translator->trans('mautic.core.slot.label.image'),
                'image',
                'MauticCoreBundle:Slots:image.html.php',
                'slot_image',
                900
            );
            $event->addSlotType(
                'imagecard',
                $this->translator->trans('mautic.core.slot.label.imagecard'),
                'id-card-o',
                'MauticCoreBundle:Slots:imagecard.html.php',
                'slot_imagecard',
                870
            );
            $event->addSlotType(
                'imagecaption',
                $this->translator->trans('mautic.core.slot.label.imagecaption'),
                'image',
                'MauticCoreBundle:Slots:imagecaption.html.php',
                'slot_imagecaption',
                850
            );
            $event->addSlotType(
                'button',
                $this->translator->trans('mautic.core.slot.label.button'),
                'external-link',
                'MauticCoreBundle:Slots:button.html.php',
                'slot_button',
                800
            );
            $event->addSlotType(
                'socialfollow',
                $this->translator->trans('mautic.core.slot.label.socialfollow'),
                'twitter',
                'MauticCoreBundle:Slots:socialfollow.html.php',
                'slot_socialfollow',
                600
            );
            $event->addSlotType(
                'codemode',
                $this->translator->trans('mautic.core.slot.label.codemode'),
                'code',
                'MauticCoreBundle:Slots:codemode.html.php',
                'slot_codemode',
                500
            );
            $event->addSlotType(
                'separator',
                $this->translator->trans('mautic.core.slot.label.separator'),
                'minus',
                'MauticCoreBundle:Slots:separator.html.php',
                'slot_separator',
                400
            );

            $event->addSlotType(
                'dynamicContent',
                $this->translator->trans('mautic.core.slot.label.dynamiccontent'),
                'tag',
                'MauticCoreBundle:Slots:dynamiccontent.html.php',
                'slot_dynamiccontent',
                300
            );
        }

        if ($event->sectionsRequested()) {
            $event->addSection(
                'one-column',
                $this->translator->trans('mautic.core.slot.label.onecolumn'),
                'file-text-o',
                'MauticCoreBundle:Sections:one-column.html.php',
                null,
                1000
            );
            $event->addSection(
                'two-column',
                $this->translator->trans('mautic.core.slot.label.twocolumns'),
                'columns',
                'MauticCoreBundle:Sections:two-column.html.php',
                null,
                900
            );
            $event->addSection(
                'three-column',
                $this->translator->trans('mautic.core.slot.label.threecolumns'),
                'th',
                'MauticCoreBundle:Sections:three-column.html.php',
                null,
                800
            );
        }
    }

    /**
     * @param EmailSendEvent $event
     */
    public function onEmailGenerate(EmailSendEvent $event)
    {
        $idHash = $event->getIdHash();
        $lead   = $event->getLead();
        $email  = $event->getEmail();
        $helper = $event->getHelper();
        $type   = 'broadcast';
        if ($email != null) {
            $emailtype = $email->getEmailType();
            if ($emailtype == 'template') {
                $type = 'notificationemail';
            }
        }

        if ($idHash == null) {
            // Generate a bogus idHash to prevent errors for routes that may include it
            $idHash = uniqid();
        }

        $unsubscribeText = $this->coreParametersHelper->getParameter('unsubscribe');
        if (!$unsubscribeText) {
            $unsubscribeText = $this->translator->trans('le.email.unsubscribe.text', ['%link%' => '|URL|']);
        }
        $unsubscribeText = str_replace('|URL|', $this->emailModel->buildUrl('le_email_subscribe', ['idHash' => $idHash]), $unsubscribeText);
        $event->addToken('{{global_unsubscribe_link}}', EmojiHelper::toHtml($unsubscribeText));
        $event->addToken('{unsubscribe_link}', EmojiHelper::toHtml($unsubscribeText));

        $event->addToken('{unsubscribe_url}', $this->emailModel->buildUrl('le_email_subscribe', ['idHash' => $idHash]));

        $updateLead = $this->coreParametersHelper->getParameter('updatelead');
        if (!$updateLead) {
            $updateLead = $this->translator->trans('le.email.updatelead.text', ['%link%' => '|URL|']);
        }
        $updateLead = str_replace('|URL|', $this->emailModel->buildUrl('le_email_updatelead', ['idHash' => $idHash]), $updateLead);

        $event->addToken('{update_your_profile_link}', EmojiHelper::toHtml($updateLead));

        $event->addToken('{updatelead_url}', $this->emailModel->buildUrl('le_email_updatelead', ['idHash' => $idHash]));

        $webviewText = false; //$this->coreParametersHelper->getParameter('webview_text');
        if (!$webviewText) {
            $webviewText = $this->translator->trans('le.email.webview.text', ['%link%' => '|URL|']);
        }
        $webviewText = str_replace('|URL|', $this->emailModel->buildUrl('le_email_webview', ['idHash' => $idHash]), $webviewText);
        $event->addToken('{webview_link}', EmojiHelper::toHtml($webviewText));

        // Show public email preview if the lead is not known to prevent 404
        if (empty($lead['id']) && $email) {
            $event->addToken('{webview_url}', $this->emailModel->buildUrl('le_email_preview', ['objectId' => $email->getId(), 'type' => $type]));
        } else {
            $event->addToken('{webview_url}', $this->emailModel->buildUrl('le_email_webview', ['idHash' => $idHash, 'type' => $type]));
        }

        $signatureText = $this->coreParametersHelper->getParameter('default_signature_text');
        $fromName      = ''; //$this->coreParametersHelper->getParameter('mailer_from_name');
        $fromEmail     ='';
        $defaultsender =$this->emailModel->getDefaultSenderProfile();
        if (sizeof($defaultsender) > 0) {
            $fromName =$defaultsender[0];
            $fromEmail=$defaultsender[1];
        }
        $signatureText = str_replace('|FROM_NAME|', $fromName, nl2br($signatureText));
        $event->addToken('{signature}', EmojiHelper::toHtml($signatureText));

        $event->addToken('{subject}', EmojiHelper::toHtml($event->getSubject()));

        $postal_address = $this->coreParametersHelper->getParameter('postal_address');
        if ($email != null && $email->getPostalAddress() != '') {
            $postal_address = $email->getPostalAddress();
        }
        if ($postal_address != '') {
            $event->addToken('{postal_address}', EmojiHelper::toHtml($postal_address));
        }

        if ($helper != null && !empty($helper->message->getFrom())) {
            foreach ($helper->message->getFrom() as $fromemail => $fromname) {
                $event->addToken('{from_email}', $fromemail);
            }
        }

        $footerText = $this->coreParametersHelper->getParameter('footer_text');
        if (!$footerText) {
            $footerText = $this->translator->trans('le.email.default.footer');
        }
        if ($email != null && $email->getUnsubscribeText() != '') {
            $footerText = $email->getUnsubscribeText();
        }
        if ($footerText != '') {
            $footerText = str_replace('{{global_unsubscribe_link}}', "<a href='|URL|'>Unsubscribe</a>", $footerText);
            $footerText = str_replace('{unsubscribe_link}', "<a href='|URL|'>Unsubscribe</a>", $footerText);
            $footerText = str_replace('|URL|', $this->emailModel->buildUrl('le_email_subscribe', ['idHash' => $idHash]), $footerText);

            $footerText = str_replace('{update_your_profile_link}', "<a href='|URL|'>Update Your Profile</a>", $footerText);
            $footerText = str_replace('|URL|', $this->emailModel->buildUrl('le_email_updatelead', ['idHash' => $idHash]), $footerText);

            if ($helper != null && !empty($helper->message->getFrom())) {
                foreach ($helper->message->getFrom() as $fromemail => $fromname) {
                    $footerText = str_replace('{from_email}', $fromemail, $footerText);
                }
            } else {
                $fromAddress = $fromEmail; //$this->coreParametersHelper->getParameter('mailer_from_email');
                $footerText  = str_replace('{from_email}', $fromAddress, $footerText);
            }
            $webviewText = false; //$this->coreParametersHelper->getParameter('webview_text');
            if (!$webviewText) {
                $webviewText = $this->translator->trans('le.email.webview.text', ['%link%' => '|URL|']);
            }
            $webviewText = str_replace('|URL|', $this->emailModel->buildUrl('le_email_webview', ['idHash' => $idHash]), $webviewText);
            $footerText  = str_replace('{webview_link}', $webviewText, $footerText);
            if ($postal_address != '') {
                $footerText = str_replace('{postal_address}', $postal_address, $footerText);
            }
            $event->addToken('{footer_text}', EmojiHelper::toHtml($footerText));
        }
    }

    /**
     * @param EmailSendEvent $event
     *
     * @return array
     */
    public function convertUrlsToTokens(EmailSendEvent $event)
    {
        if ($event->isInternalSend() || $this->coreParametersHelper->getParameter('disable_trackable_urls')) {
            // Don't convert urls
            return;
        }

        $email   = $event->getEmail();
        $emailId = ($email) ? $email->getId() : null;
        if (!$email instanceof Email) {
            $email = $this->emailModel->getEntity($emailId);
        }

        $utmTags          = $email->getUtmTags();
        $clickthrough     = $event->generateClickthrough();
        $trackables       = $this->parseContentForUrls($event, $emailId);
        $emailType        = $email->getEmailType();
        if ($emailType == 'dripemail') {
            $emailType = 'drip';
        }
        $utmsource        = $this->coreParametersHelper->getParameter($emailType.'_source');
        $utmmedium        = $this->coreParametersHelper->getParameter($emailType.'_medium');
        $utmcampaign      = $this->coreParametersHelper->getParameter($emailType.'_campaignname');
        $utmcontent       = $this->coreParametersHelper->getParameter($emailType.'_content');
        $analyticsstatus  = $this->coreParametersHelper->getParameter('analytics_status');
        $utmcampaignvalue = $email->getSubject();
        $utmcontentvalue  = $email->getSubject();
        if ($utmcampaign == '{campaign_name}') {
            $utmcampaignvalue = $email->getName();
        }
        if ($utmcontent == '{campaign_name}') {
            $utmcontentvalue = $email->getName();
        }
        if ($analyticsstatus) {
            $utmTags['utmSource']   = $utmsource;
            $utmTags['utmMedium']   = $utmmedium;
            $utmTags['utmCampaign'] = $utmcampaignvalue;
            $utmTags['utmContent']  = $utmcontentvalue;
        } else {
            unset($utmTags['utmSource']);
            unset($utmTags['utmMedium']);
            unset($utmTags['utmCampaign']);
            unset($utmTags['utmContent']);
        }
        /**
         * @var string
         * @var Trackable $trackable
         */
        foreach ($trackables as $token => $trackable) {
            if ($trackable instanceof Trackable) {
                if (strpos($trackable->getRedirect()->getUrl(), 'anyfunnels.com/?utm-src=email-footer-link') !== false) {
                    continue;
                }
            }
            $url = ($trackable instanceof Trackable)
                ?
                $this->pageTrackableModel->generateTrackableUrl($trackable, $clickthrough, false, $utmTags)
                :
                $this->pageRedirectModel->generateRedirectUrl($trackable, $clickthrough, false, $utmTags);

            $event->addToken($token, $url);
        }
    }

    /**
     * Parses content for URLs and tokens.
     *
     * @param EmailSendEvent $event
     * @param                $emailId
     *
     * @return mixed
     */
    protected function parseContentForUrls(EmailSendEvent $event, $emailId)
    {
        static $convertedContent = [];

        // Prevent parsing the exact same content over and over
        if (!isset($convertedContent[$event->getContentHash()])) {
            $html = $event->getContent();
            $text = $event->getPlainText();

            $contentTokens = $event->getTokens();

            list($content, $trackables) = $this->pageTrackableModel->parseContentForTrackables(
                [$html, $text],
                $contentTokens,
                ($emailId) ? 'email' : null,
                $emailId
            );

            list($html, $text) = $content;
            unset($content);

            if ($html) {
                $event->setContent($html);
            }
            if ($text) {
                $event->setPlainText($text);
            }

            $convertedContent[$event->getContentHash()] = $trackables;

            // Don't need to preserve Trackable or Redirect entities in memory
            $this->em->clear('Mautic\PageBundle\Entity\Redirect');
            $this->em->clear('Mautic\PageBundle\Entity\Trackable');

            unset($html, $text, $trackables);
        }

        return $convertedContent[$event->getContentHash()];
    }
}
