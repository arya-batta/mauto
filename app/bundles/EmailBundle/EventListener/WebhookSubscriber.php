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
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailOpenEvent;
use Mautic\EmailBundle\Event\EmailSendEvent;
use Mautic\WebhookBundle\Event\WebhookBuilderEvent;
use Mautic\WebhookBundle\EventListener\WebhookModelTrait;
use Mautic\WebhookBundle\WebhookEvents;

/**
 * Class WebhookSubscriber.
 */
class WebhookSubscriber extends CommonSubscriber
{
    use WebhookModelTrait;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_ON_OPEN      => ['onEmailOpen', 0],
            EmailEvents::EMAIL_ON_SEND      => ['onEmailSend', 0],
            WebhookEvents::WEBHOOK_ON_BUILD => ['onWebhookBuild', 9],
        ];
    }

    /**
     * Add event triggers and actions.
     *
     * @param WebhookBuilderEvent $event
     */
    public function onWebhookBuild(WebhookBuilderEvent $event)
    {
        // add checkbox to the webhook form for new leads
        $mailOpen = [
            'label'       => 'le.email.webhook.event.open',
            'description' => 'le.email.webhook.event.open_desc',
        ];

        // add it to the list
        $event->addEvent(EmailEvents::EMAIL_ON_OPEN, $mailOpen);

        // add checkbox to the webhook form for new leads
        $mailSend = [
            'label'       => 'le.email.webhook.event.send',
            'description' => 'le.email.webhook.event.send_desc',
        ];

        // add it to the list
        $event->addEvent(EmailEvents::EMAIL_ON_SEND, $mailSend);
    }

    /**
     * @param EmailOpenEvent $event
     */
    public function onEmailOpen(EmailOpenEvent $event)
    {
        $this->webhookModel->queueWebhooksByType(
            EmailEvents::EMAIL_ON_OPEN,
            [
                'stat' => $event->getStat(),
            ],
            [
                'statDetails',
                'leadList',
                'emailDetails',
            ]
        );
    }

    /**
     * @param EmailSendEvent $event
     */
    public function onEmailSend(EmailSendEvent $event)
    {
        $this->webhookModel->queueWebhooksByType(
            EmailEvents::EMAIL_ON_SEND,
            [
                'Lead'    => $event->getLeadEntity(),
                'email'   => $event->getEmail(),
            ],
            [
                'statDetails',
                'leadDetails',
                'emailDetails',
                'statDetails',
                'userList',
                'tagList',
            ]
        );
    }
}
