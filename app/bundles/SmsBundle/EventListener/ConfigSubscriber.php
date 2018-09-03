<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SmsBundle\EventListener;

use Mautic\ConfigBundle\ConfigEvents;
use Mautic\ConfigBundle\Event\ConfigBuilderEvent;
use Mautic\ConfigBundle\Event\ConfigEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;

/**
 * Class ConfigSubscriber.
 */
class ConfigSubscriber extends CommonSubscriber
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ConfigEvents::CONFIG_ON_GENERATE => ['onConfigGenerate', 2],
            ConfigEvents::CONFIG_PRE_SAVE    => ['onConfigSave', 0],
        ];
    }

    public function onConfigGenerate(ConfigBuilderEvent $event)
    {
        $event->addForm([
            'bundle'     => 'SmsBundle',
            'formAlias'  => 'smsconfig',
            'formTheme'  => 'MauticSmsBundle:FormTheme\Config',
            'parameters' => $event->getParametersFromConfig('MauticSmsBundle'),
        ]);
    }

    public function onConfigSave(ConfigEvent $event)
    {
        $data = $event->getConfig('smsconfig');
        if ($data['sms_transport'] == 'mautic.sms.transport.solutioninfini') {
            if (empty($data['account_url'])) {
                $event->setError('le.sms.solution.account.url.invalid', [], 'smsconfig', 'account_url');
            }
            if (empty($data['account_api_key'])) {
                $event->setError('le.sms.solution.account.api.invalid', [], 'smsconfig', 'account_api_key');
            }
            if (empty($data['account_sender_id'])) {
                $event->setError('le.sms.solution.account.sid.invalid', [], 'smsconfig', 'account_sender_id');
            }
        } elseif ($data['sms_transport'] == 'mautic.sms.transport.twilio') {
            if (empty($data['account_auth_token'])) {
                $event->setError('le.sms.twilo.authentication.invalid', [], 'smsconfig', 'account_auth_token');
            }
            if (empty($data['account_sid'])) {
                $event->setError('le.sms.twilo.account.sid.invalid', [], 'smsconfig', 'account_sid');
            }
            if (empty($data['sms_from_number'])) {
                $event->setError('le.sms.twilo.from.number.invalid', [], 'smsconfig', 'sms_from_number');
            }
        }

        $event->setConfig($data, 'smsconfig');
    }
}
