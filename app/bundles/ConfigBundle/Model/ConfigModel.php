<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\ConfigBundle\Model;

use Mautic\CoreBundle\Model\AbstractCommonModel;

/**
 * Class ConfigModel.
 *
 * @deprecated 2.12.0; to be removed in 3.0 as this is pointless
 */
class ConfigModel extends AbstractCommonModel
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionBase()
    {
        return 'config:config';
    }

    /**
     * Creates the appropriate form per the model.
     *
     * @param array                                        $data
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param array                                        $options
     *
     * @return \Symfony\Component\Form\Form
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createForm($data, $formFactory, $options = [])
    {
        return $formFactory->create('config', $data, $options);
    }

    public function getSettingsMenuValues()
    {
        $instapage    = $this->translator->trans('le.integration.name.instapage');
        $calendly     = $this->translator->trans('le.integration.name.calendly');
        $unbounce     = $this->translator->trans('le.integration.name.unbounce');
        $slack        = $this->translator->trans('le.integration.name.slack');

        $settingsMenu = [];

        $settingsMenu[] = [
            'Account settings'=> [
                'Account' => [
                    'name'=> 'Account',
                    'img' => 'Accounts.png',
                    'url' => $this->router->generate('le_accountinfo_action', ['objectAction'=>'edit']),
                ],
                'Sender Reputation' => [
                    'name'=> 'Sender Reputation',
                    'img' => 'Sender-Reputation.png',
                    'url' => $this->router->generate('le_accountinfo_action', ['objectAction' => 'senderreputation']),
                ],
                'Billing' => [
                    'name'=> 'Billing',
                    'img' => 'Billing.png',
                    'url' => $this->router->generate('le_accountinfo_action', ['objectAction'=>'billing']),
                ],
                'Payment History' => [
                    'name'=> 'Payment History',
                    'img' => 'Payment-History.png',
                    'url' => $this->router->generate('le_accountinfo_action', ['objectAction'=>'payment']),
                ],
                'Users' => [
                    'name'=> 'Users',
                    'img' => 'Users.png',
                    'url' => $this->router->generate('le_user_index'),
                ],
            ],
            'Configurations' => [
                'Sending Domain' => [
                    'name'=> 'Sending Domain',
                    'img' => 'Sending-Domain.png',
                    'url' => $this->router->generate('le_config_action', ['objectAction' => 'edit', 'objectId'=> 'sendingdomain_config']),
                ],
                'Email Settings' => [
                    'name'=> 'Email Settings',
                    'img' => 'Email-Settings.png',
                    'url' => $this->router->generate('le_config_action', ['objectAction' => 'edit', 'objectId'=> 'emailconfig']),
                ],
                'UTM Tracking' => [
                    'name'=> 'UTM Tracking',
                    'img' => 'UTM-Tracking.png',
                    'url' => $this->router->generate('le_config_action', ['objectAction' => 'edit', 'objectId'=> 'analyticsconfig']),
                ],
                'Text Messages Settings' => [
                    'name'=> 'Text Messages Settings',
                    'img' => 'Text-Messages-Settings.png',
                    'url' => $this->router->generate('le_config_action', ['objectAction' => 'edit', 'objectId'=> 'smsconfig']),
                ],
                'Website Tracking' => [
                    'name'=> 'Website Tracking',
                    'img' => 'Website-Tracking.png',
                    'url' => $this->router->generate('le_config_action', ['objectAction' => 'edit', 'objectId'=> 'trackingconfig']),
                ],
                'Group' => [
                    'name'=> 'Group',
                    'img' => 'Category.png',
                    'url' => $this->router->generate('le_category_index'),
                ],
            ],
            'Templates'=> [
                'Notification Email' => [
                    'name'=> 'Notification Emails',
                    'img' => 'Notification-Email-Templates.png',
                    'url' => $this->router->generate('le_email_index'),
                ],
                'Text Message' => [
                    'name'=> 'Text Messages',
                    'img' => 'Text-Messages-Templates.png',
                    'url' => $this->router->generate('le_sms_index'),
                ],
                'Slack Messages' => [
                    'name'=> 'Slack Messages',
                    'img' => 'slack.png',
                    'url' => $this->router->generate('le_slack_index'),
                ],
                'Files/ Lead Magnets' => [
                    'name'=> 'Files/ Lead Magnets',
                    'img' => 'Files-Lead-Magnets.png',
                    'url' => $this->router->generate('le_asset_index'),
                ],
            ],

            'Integrations' => [
                'API Key & Doc' => [
                    'name'=> 'API Key & Doc',
                    'img' => 'Developer-API.png',
                    'url' => $this->router->generate('le_config_action', ['objectAction' => 'edit', 'objectId'=> 'apiconfig']),
                ],
                'Webhooks' => [
                    'name'=> 'Webhooks',
                    'img' => 'Webhooks.png',
                    'url' => $this->router->generate('le_webhook_index'),
                ],
                'Zapier' => [
                    'name'=> 'Zapier',
                    'img' => 'Zapier.png',
                    'url' => $this->router->generate('le_integrations_config', ['name'=>'zapier']),
                ],
            ],

            '3rd Party Apps' => [
                'Facebook Lead Ad' => [
                    'name'=> 'Facebook Lead Ad',
                    'img' => 'fb-Ad.png',
                    'url' => $this->router->generate('le_integrations_config', ['name'=>'facebook_lead_ads']),
                ],
                'Facebook Custom Audience ' => [
                    'name'=> 'Facebook Custom Audience',
                    'img' => 'fb-Custom-Audience.png',
                    'url' => $this->router->generate('le_integrations_config', ['name'=>'facebook_custom_audiences']),
                ],
                'Instapage' => [
                    'name'=> 'Instapage',
                    'img' => 'Instapage.png',
                    'url' => $this->router->generate('le_integrations_config', ['name'=>$instapage]),
                ],
                'Unbounce' => [
                    'name'=> 'Unbounce',
                    'img' => 'Unbounce.png',
                    'url' => $this->router->generate('le_integrations_config', ['name'=>$unbounce]),
                ],
                'Calendly' => [
                    'name'=> 'Calendly',
                    'img' => 'Calendly.png',
                    'url' => $this->router->generate('le_integrations_config', ['name'=>$calendly]),
                ],
                'Slack' => [
                    'name'=> 'Slack',
                    'img' => 'slack.png',
                    'url' => $this->router->generate('le_integrations_config', ['name'=>$slack]),
                ],
            ],
        ];

        $paymentrepository  =$this->factory->get('le.subscription.repository.payment');
        $lastpayment        = $paymentrepository->getLastPayment();
        if ($lastpayment == null) {
            unset($settingsMenu[0]['Account settings']['Sender Reputation']);
        }

        return $settingsMenu[0];
    }
}
