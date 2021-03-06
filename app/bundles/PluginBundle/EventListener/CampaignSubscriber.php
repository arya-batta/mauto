<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\EventListener;

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\CoreBundle\Event\TokenReplacementEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\PluginBundle\Helper\FacebookAdsApiHelper;
use Mautic\PluginBundle\PluginEvents;
use Mautic\SmsBundle\SmsEvents;

/**
 * Class CampaignSubscriber.
 */
class CampaignSubscriber extends CommonSubscriber
{
    use PushToIntegrationTrait;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD        => ['onCampaignBuild', 0],
            PluginEvents::ON_CAMPAIGN_TRIGGER_ACTION => [
                ['onCampaignTriggerAction', 0],
            ['onCampaignTriggerActionAddorRemoveCustomAudience', 1],
            ['onCampaignTriggerActionPostSlackMessage', 2],
            ],
        ];
    }

    /**
     * @param CampaignBuilderEvent $event
     */
    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
        $action = [
            'label'           => 'mautic.plugin.actions.push_lead',
            'description'     => 'mautic.plugin.actions.tooltip',
            'formType'        => 'integration_list',
            'formTheme'       => 'MauticPluginBundle:FormTheme\Integration',
            'eventName'       => PluginEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'order'           => 21,
            'group'           => 'le.campaign.event.group.name.leadsengage',
        ];
        if ($this->security->isAdmin()) {
            $event->addAction('plugin.leadpush', $action);
        }

        $event->addAction('addFBCustomAudience', [
            'label'           => 'le.integration.action.add.fbcustomaudience.label',
            'description'     => 'le.integration.action.add.fbcustomaudience.desc',
            'formType'        => 'fb_custom_audience_list',
            'formTheme'       => 'MauticPluginBundle:FormTheme\Action',
            'eventName'       => PluginEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'order'           => 1,
            'group'           => 'le.campaign.event.group.name.facebook',
        ]);
        $event->addAction('removeFBCustomAudience', [
            'label'           => 'le.integration.action.remove.fbcustomaudience.label',
            'description'     => 'le.integration.action.remove.fbcustomaudience.desc',
            'formType'        => 'fb_custom_audience_list',
            'formTheme'       => 'MauticPluginBundle:FormTheme\Action',
            'eventName'       => PluginEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'order'           => 2,
            'group'           => 'le.campaign.event.group.name.facebook',
        ]);
        $event->addAction('postSlackMessage', [
            'label'           => 'le.slack.post.message.action.label',
            'description'     => 'le.slack.post.message.action.desc',
            'formType'        => 'slack_message_list',
            'formTheme'       => 'MauticPluginBundle:FormTheme\Action',
            'eventName'       => PluginEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'order'           => 1,
            'group'           => 'le.campaign.event.group.name.slack',
        ]);
        $event->addSources(
            'fbLeadAds',
            [
                'label'           => 'le.integration.source.fbleadads.label',
                'description'     => 'le.integration.source.fbleadads.desc',
                'sourcetype'      => 'fbLeadAds',
                'formTheme'       => 'MauticPluginBundle:FormTheme\Source',
                'formType'        => 'fb_leadads_list',
                'order'           => '1',
                'group'           => 'le.campaign.event.group.name.facebook',
            ]
        );
        $event->addSources(
            'instapage',
            [
                'label'           => 'le.integration.source.instapage.label',
                'description'     => 'le.integration.source.instapage.desc',
                'sourcetype'      => 'instapage',
                'formTheme'       => 'MauticPluginBundle:FormTheme\Source',
                'formType'        => 'instapage_type',
                'order'           => '1',
                'group'           => 'le.campaign.event.group.name.instapage',
            ]
        );
        $event->addSources(
            'unbounce',
            [
                'label'           => 'le.integration.source.unbounce.label',
                'description'     => 'le.integration.source.unbounce.desc',
                'sourcetype'      => 'unbounce',
                'formTheme'       => 'MauticPluginBundle:FormTheme\Source',
                'formType'        => 'unbounce_type',
                'order'           => '1',
                'group'           => 'le.campaign.event.group.name.unbounce',
            ]
        );
        $event->addSources(
            'invitee.created',
            [
                'label'           => 'le.integration.source.calendly.invitee.created.label',
                'description'     => 'le.integration.source.calendly.invitee.created.desc',
                'sourcetype'      => 'invitee.created',
                'formTheme'       => 'MauticPluginBundle:FormTheme\Source',
                'formType'        => 'calendly_type',
                'order'           => '1',
                'group'           => 'le.campaign.event.group.name.calendly',
            ]
        );
        $event->addSources(
            'invitee.canceled',
            [
                'label'           => 'le.integration.source.calendly.invitee.canceled.label',
                'description'     => 'le.integration.source.calendly.invitee.canceled.desc',
                'sourcetype'      => 'invitee.canceled',
                'formTheme'       => 'MauticPluginBundle:FormTheme\Source',
                'formType'        => 'calendly_type',
                'order'           => '2',
                'group'           => 'le.campaign.event.group.name.calendly',
            ]
        );
    }

    /**
     * @param CampaignExecutionEvent $event
     *
     * @return $this
     */
    public function onCampaignTriggerAction(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('plugin.leadpush')) {
            return;
        }

        $config  = $event->getConfig();
        $lead    = $event->getLead();
        $errors  = [];
        $success = $this->pushToIntegration($config, $lead, $errors);

        if (count($errors)) {
            $event->setFailed(implode('<br />', $errors));
        }

        return $event->setResult($success);
    }

    /**
     * @param CampaignExecutionEvent $event
     *
     * @return $this
     */
    public function onCampaignTriggerActionAddorRemoveCustomAudience(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('addFBCustomAudience') && !$event->checkContext('removeFBCustomAudience')) {
            return;
        }
        $isAddAction=true;
        if ($event->checkContext('removeFBCustomAudience')) {
            $isAddAction=false;
        }
        $lead              = $event->getLead();
        $adAccount         = $event->getConfig()['adaccount'];
        $customaudience    = $event->getConfig()['customaudience'];
        $integrationHelper = $this->factory->getHelper('integration');
        $fbapiHelper       = $this->factory->getHelper('fbapi');
        if (!empty($adAccount)) {
            $integrationsettings=$integrationHelper->getIntegrationSettingsbyName('facebook_custom_audiences');
            if (sizeof($integrationsettings) > 0) {
                $fbapiHelper->initFBAdsApi($integrationsettings['authtoken']);
                try {
                    $caObj=FacebookAdsApiHelper::getFBAudienceByID($customaudience, $adAccount);
                    if ($caObj) {
                        $user[]        =$lead->getFirstname();
                        $user[]        =$lead->getLastname();
                        $user[]        =$lead->getEmail();
                        $user[]        =$lead->getMobile();
                        $user[]        =$lead->getCountry();
                        $isErrorOccured=false;
                        $errorMessage  ='';
                        $response      =[];
                        if ($isAddAction) {
                            try {
                                $response=FacebookAdsApiHelper::addUsers($caObj, [$user]);
                                //sample response
                                ////{"audience_id":"23843351937250762","session_id":"4040431473997127992","num_received":1,"num_invalid_entries":0,"invalid_entry_samples":[]}
                            } catch (\Exception $ex) {
                                $isErrorOccured=true;
                                $errorMessage  =$ex->getMessage();
                            }
                        } else {
                            try {
                                $response=FacebookAdsApiHelper::removeUsers($caObj, [$user]);
                            } catch (\Exception $ex) {
                                $isErrorOccured=true;
                                $errorMessage  =$ex->getMessage();
                            }
                        }
                        if ($isErrorOccured) {
                            $event->setFailed($errorMessage);
                        } else {
                            $event->setResult(true);
                        }
                    } else {
                        $event->setFailed('given custom audience not found');
                    }
                } catch (\Exception $ex) {
                    $event->setFailed($ex->getMessage());
                }
            }
        } else {
            $event->setFailed('custom audience not found due to adaccount is empty');
        }

        return $event;
    }

    /**
     * @param CampaignExecutionEvent $event
     *
     * @return $this
     */
    public function onCampaignTriggerActionPostSlackMessage(CampaignExecutionEvent $event)
    {
        if (!$event->checkContext('postSlackMessage')) {
            return;
        }

        $lead              = $event->getLead();
        $channel           = $event->getConfig()['channellist'];
        $slack             = $event->getConfig()['slacklist'];
        $integrationHelper = $this->factory->getHelper('integration');
        $slackHelper       = $this->factory->getHelper('slack');
        $slackModel        = $this->factory->get('mautic.plugin.model.slack');
        if (!empty($channel) && !empty($slack)) {
            $slackEntity        = $slackModel->getEntity($slack);
            $integrationsettings=$integrationHelper->getIntegrationSettingsbyName('slack');
            $tokenEvent         = $this->dispatcher->dispatch(
                SmsEvents::TOKEN_REPLACEMENT,
                new TokenReplacementEvent(
                    $slackEntity->getMessage(),
                    $lead
                )
            );
            $message = $tokenEvent->getContent();
            if (sizeof($integrationsettings) > 0) {
                try {
                    $response = $slackHelper->sendSlackMessage($integrationsettings['authtoken'], $message, $channel);
                    if (!$response['success']) {
                        $event->setFailed($response['error']);
                    }
                } catch (\Exception $ex) {
                    $event->setFailed($ex->getMessage());
                }
            }
        } else {
            $event->setFailed('Slack Message failed because Channel or Message is Empty');
        }

        return $event;
    }
}
