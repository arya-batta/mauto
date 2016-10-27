<?php
/**
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticCitrixBundle\EventListener;

use FOS\OAuthServerBundle\Propel\Token;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailBuilderEvent;
use Mautic\EmailBundle\Event\EmailSendEvent;
use MauticPlugin\MauticCitrixBundle\CitrixEvents;
use MauticPlugin\MauticCitrixBundle\Event\TokenGenerateEvent;
use MauticPlugin\MauticCitrixBundle\Helper\CitrixHelper;
use MauticPlugin\MauticCitrixBundle\Helper\CitrixProducts;

/**
 * Class EmailSubscriber
 */
class EmailSubscriber extends CommonSubscriber
{

    /**
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return array(
            CitrixEvents::ON_TOKEN_GENERATE => ['onTokenGenerate', 254],
            EmailEvents::EMAIL_ON_BUILD => ['onEmailBuild', 0],
            EmailEvents::EMAIL_ON_SEND => array('decodeTokensSend', 254),
            EmailEvents::EMAIL_ON_DISPLAY => array('decodeTokensDisplay', 254),
        );
    }

    /**
     * @param TokenGenerateEvent $event
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function onTokenGenerate(TokenGenerateEvent $event)
    {
        // inject product details in $event->params on email send
    }

    /**
     * @param EmailBuilderEvent $event
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function onEmailBuild(EmailBuilderEvent $event)
    {
        // register tokens
        $tokens = [
            '{meeting_button}' => $this->translator->trans('plugin.citrix.token.meeting_button'),
            '{training_button}' => $this->translator->trans('plugin.citrix.token.training_button'),
            '{assist_button}' => $this->translator->trans('plugin.citrix.token.assist_button'),
        ];

        if ($event->tokensRequested(array_keys($tokens))) {
            $event->addTokens(
                $event->filterTokens($tokens)
            );
        }

    }

    /**
     * Search and replace tokens with content
     *
     * @param EmailSendEvent $event
     * @throws \RuntimeException
     */
    public function decodeTokensDisplay(EmailSendEvent $event)
    {
        $this->decodeTokens($event);
    }

    /**
     * Search and replace tokens with content
     *
     * @param EmailSendEvent $event
     * @throws \RuntimeException
     */
    public function decodeTokensSend(EmailSendEvent $event)
    {
        $this->decodeTokens($event, true);
    }

    /**
     * Search and replace tokens with content
     *
     * @param EmailSendEvent $event
     * @throws \RuntimeException
     */
    public function decodeTokens(EmailSendEvent $event, $triggerEvent = false)
    {
        // Get content
        $content = $event->getContent();

        // Search and replace tokens

        $products = [
            CitrixProducts::GOTOMEETING,
            CitrixProducts::GOTOTRAINING,
            CitrixProducts::GOTOASSIST,
        ];

        foreach ($products as $product) {
            if (CitrixHelper::isAuthorized('Goto'.$product)) {
                $params = [
                    'product' => $product,
                ];

                // trigger event to replace the links in the tokens
                if ($triggerEvent && $this->dispatcher->hasListeners(CitrixEvents::ON_TOKEN_GENERATE)) {
                    $tokenEvent = new TokenGenerateEvent($params);
                    $this->dispatcher->dispatch(CitrixEvents::ON_TOKEN_GENERATE, $tokenEvent);
                    $params = $tokenEvent->getParams();
                    unset($tokenEvent);
                }

                $button = $this->templating->render(
                    'MauticCitrixBundle:SubscribedEvents\EmailToken:token.html.php',
                    $params
                );
                $content = str_replace('{'.$product.'_button}', $button, $content);
            } else {
                // remove the token
                $content = str_replace('{'.$product.'_button}', '', $content);
            }
        }

        // Set updated content
        $event->setContent($content);
    }
}