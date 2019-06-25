<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\EventListener;

use Mautic\CampaignBundle\Event\CampaignLeadChangeEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\LeadBundle\Entity\Tag;
use Mautic\LeadBundle\Event\ChannelSubscriptionChange;
use Mautic\LeadBundle\Event\LeadEvent;
use Mautic\LeadBundle\Event\ListChangeEvent;
use Mautic\LeadBundle\Event\ListOptInChangeEvent;
use Mautic\LeadBundle\Event\PointsChangeEvent;
use Mautic\LeadBundle\LeadEvents;
use Mautic\PageBundle\Event\PageHitEvent;
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
            WebhookEvents::WEBHOOK_ON_BUILD          => ['onWebhookBuild', 10],
            LeadEvents::LEAD_POST_SAVE               => ['onLeadNewUpdate', 0],
            LeadEvents::LEAD_POINTS_CHANGE           => ['onLeadPointChange', 0],
            LeadEvents::LEAD_POST_DELETE             => ['onLeadDelete', 0],
            LeadEvents::CHANNEL_SUBSCRIPTION_CHANGED => ['onChannelSubscriptionChange', 0],
            LeadEvents::ADD_TAG_EVENT                => ['onLeadTagAdded', 0],
            LeadEvents::REMOVE_TAG_EVENT             => ['onLeadTagRemoved', 0],
            LeadEvents::LEAD_UNSUBSCRIBED_CHANNEL    => ['onLeadUnsubscribed', 0],
            LeadEvents::LEAD_SUBSCRIBED_CHANNEL      => ['onLeadResubscribed', 0],
            LeadEvents::LEAD_EMAIL_BOUNCED           => ['onLeadEmailBounced', 0],
            LeadEvents::LEAD_MARKED_SPAM             => ['onLeadMarkedSpam', 0],
            LeadEvents::CLICK_EMAIL_EVENT            => ['onEmailClick', 0],
            LeadEvents::LEAD_LIST_ADD                => ['onLeadListAdded', 0],
            LeadEvents::LEAD_LIST_OPT_IN_ADD         => ['onLeadListoptinAdded', 0],
            LeadEvents::LEAD_COMPLETED_DRIP_CAMPAIGN => ['onLeadCompletedDrip', 0],
            LeadEvents::LEAD_DRIP_CAMPAIGN_ADD       => ['onLeadDripAdd', 0],
            LeadEvents::LEAD_WORKFLOW_ADD            => ['onLeadWorkflowAdd', 0],
            LeadEvents::LEAD_COMPLETED_WORKFLOW      => ['onLeadCompletedWorkflow', 0],
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
        $event->addEvent(
            LeadEvents::LEAD_POST_SAVE.'_new',
            [
                'label'       => 'le.lead.webhook.event.lead.new',
                'description' => 'le.lead.webhook.event.lead.new_desc',
            ]
        );

        // checkbox for lead updates
        $event->addEvent(
            LeadEvents::LEAD_POST_SAVE.'_update',
            [
                'label'       => 'le.lead.webhook.event.lead.update',
                'description' => 'le.lead.webhook.event.lead.update_desc',
            ]
        );

        // lead deleted checkbox label & desc
        $event->addEvent(
            LeadEvents::LEAD_POST_DELETE,
            [
                'label'       => 'le.lead.webhook.event.lead.deleted',
                'description' => 'le.lead.webhook.event.lead.deleted_desc',
            ]
        );

        // add a checkbox for lead unsubscribed
        $event->addEvent(
            LeadEvents::LEAD_UNSUBSCRIBED_CHANNEL,
            [
                'label'       => 'le.lead.webhook.event.lead.unsubscribed',
                'description' => 'le.lead.webhook.event.lead.unsubscribed_desc',
            ]
        );

        // add a checkbox for lead resubscribed
        $event->addEvent(
            LeadEvents::LEAD_SUBSCRIBED_CHANNEL,
            [
                'label'       => 'le.lead.webhook.event.lead.resubscribed',
                'description' => 'le.lead.webhook.event.lead.resubscribed_desc',
            ]
        );

        // add a checkbox for lead email bounced
        $event->addEvent(
            LeadEvents::LEAD_EMAIL_BOUNCED,
            [
                'label'       => 'le.lead.webhook.event.lead.bounced',
                'description' => 'le.lead.webhook.event.lead.bounced_desc',
            ]
        );

        // add a checkbox for lead issued spam complaint
        $event->addEvent(
            LeadEvents::LEAD_MARKED_SPAM,
            [
                'label'       => 'le.lead.webhook.event.lead.spam',
                'description' => 'le.lead.webhook.event.lead.spam_desc',
            ]
        );

        // add a checkbox for points
        $event->addEvent(
            LeadEvents::LEAD_POINTS_CHANGE,
            [
                'label'       => 'le.lead.webhook.event.lead.points',
                'description' => 'le.lead.webhook.event.lead.points_desc',
            ]
        );

        // add a checkbox for lead added to listoptin
        $event->addEvent(
            LeadEvents::LEAD_LIST_OPT_IN_ADD,
            [
                'label'       => 'le.lead.webhook.event.lead.listoptin.added',
                'description' => 'le.lead.webhook.event.lead.listoptin.added_desc',
            ]
        );

        // add a checkbox for lead added to segment
        $event->addEvent(
            LeadEvents::LEAD_LIST_ADD,
            [
                'label'       => 'le.lead.webhook.event.lead.list.added',
                'description' => 'le.lead.webhook.event.lead.list.added_desc',
            ]
        );

        // add a checkbox for lead tag added
        $event->addEvent(
            LeadEvents::ADD_TAG_EVENT,
            [
                'label'       => 'le.lead.webhook.event.lead.tag.modified',
                'description' => 'le.lead.webhook.event.lead.tag.modified_desc',
            ]
        );

        // add a checkbox for lead tag removed
        $event->addEvent(
            LeadEvents::REMOVE_TAG_EVENT,
            [
                'label'       => 'le.lead.webhook.event.lead.tag.removed',
                'description' => 'le.lead.webhook.event.lead.tag.removed_desc',
            ]
        );

        // add a checkbox for lead add to a drip
        $event->addEvent(
            LeadEvents::LEAD_DRIP_CAMPAIGN_ADD,
            [
                'label'       => 'le.lead.webhook.event.lead.drip.added',
                'description' => 'le.lead.webhook.event.lead.drip.added_desc',
            ]
        );

        // add a checkbox for lead completed a drip
        $event->addEvent(
            LeadEvents::LEAD_COMPLETED_DRIP_CAMPAIGN,
            [
                'label'       => 'le.lead.webhook.event.lead.drip.completed',
                'description' => 'le.lead.webhook.event.lead.drip.completed_desc',
            ]
        );

        // add a checkbox for lead add to a workflow
        $event->addEvent(
            LeadEvents::LEAD_WORKFLOW_ADD,
            [
                'label'       => 'le.lead.webhook.event.lead.workflow.added',
                'description' => 'le.lead.webhook.event.lead.workflow.added_desc',
            ]
        );

        // add a checkbox for lead completed a workflow
        $event->addEvent(
            LeadEvents::LEAD_COMPLETED_WORKFLOW,
            [
                'label'       => 'le.lead.webhook.event.lead.workflow.completed',
                'description' => 'le.lead.webhook.event.lead.workflow.completed_desc',
            ]
        );

        // add a checkbox for lead clicked an email
        $event->addEvent(
            LeadEvents::CLICK_EMAIL_EVENT,
            [
                'label'       => 'le.lead.webhook.event.lead.email.clicked',
                'description' => 'le.lead.webhook.event.lead.email.clicked_desc',
            ]
        );

        if ($this->security->isAdmin()) {
            // add a checkbox for do not contact changes
            $event->addEvent(
                LeadEvents::CHANNEL_SUBSCRIPTION_CHANGED,
                [
                    'label'       => 'le.lead.webhook.event.lead.dnc',
                    'description' => 'le.lead.webhook.event.lead.dnc_desc',
                ]
            );
        }
    }

    /**
     * @param LeadEvent $event
     */
    public function onLeadNewUpdate(LeadEvent $event)
    {
        $lead = $event->getLead();
        if ($lead->isAnonymous()) {
            // Ignore this contact
            return;
        }

        $changes = $lead->getChanges(true);

        $this->webhookModel->queueWebhooksByType(
        // Consider this a new contact if it was just identified, otherwise consider it updated
            !empty($changes['dateIdentified']) ? LeadEvents::LEAD_POST_SAVE.'_new' : LeadEvents::LEAD_POST_SAVE.'_update',
            [
                'lead'    => $event->getLead(),
                //'contact' => $event->getLead(),
            ],
            [
                'leadDetails',
                'userList',
                'publishDetails',
                'ipAddress',
                'tagList',
            ]
        );
    }

    /**
     * @param PointsChangeEvent $event
     */
    public function onLeadPointChange(PointsChangeEvent $event)
    {
        $lead      = $event->getLead();
        $newpoints = $event->getNewPoints();
        $lead->setPoints($newpoints);
        $lead->getFields()['core']['points']['value'] =$newpoints;
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::LEAD_POINTS_CHANGE,
            [
                'lead'    => $event->getLead(),
                //'contact' => $event->getLead(),
                'points'  => [
                    'old_points' => $event->getOldPoints(),
                    'new_points' => $event->getNewPoints(),
                ],
            ],
            [
                'leadDetails',
                'userList',
                'publishDetails',
                'ipAddress',
                'tagList',
            ]
        );
    }

    /**
     * @param LeadEvent $event
     */
    public function onLeadTagAdded(LeadEvent $event)
    {
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::ADD_TAG_EVENT,
            [
                'lead'          => $event->getLead(),
                'added_tags'    => $event->getAddedTags(),
            ],
            [
                'leadDetails',
                'userList',
                'publishDetails',
                'ipAddress',
                'tagList',
            ]
        );
    }

    /**
     * @param LeadEvent $event
     */
    public function onLeadTagRemoved(LeadEvent $event)
    {
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::REMOVE_TAG_EVENT,
            [
                'lead'         => $event->getLead(),
                'removed_tags' => $event->getRemovedTags(),
            ],
            [
                'leadDetails',
                'userList',
                'publishDetails',
                'ipAddress',
                'tagList',
            ]
        );
    }

    /**
     * @param LeadEvent $event
     */
    public function onLeadDelete(LeadEvent $event)
    {
        $lead = $event->getLead();
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::LEAD_POST_DELETE,
            [
                'id'      => $lead->deletedId,
                'lead'    => $lead,
                //'contact' => $lead,
            ],
            [
                'leadDetails',
                'userList',
                'publishDetails',
                'ipAddress',
                'tagList',
            ]
        );
    }

    /**
     * @param ChannelSubscriptionChange $event
     */
    public function onChannelSubscriptionChange(ChannelSubscriptionChange $event)
    {
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::CHANNEL_SUBSCRIPTION_CHANGED,
            [
                'lead'       => $event->getLead(),
                'channel'    => $event->getChannel(),
                'old_status' => $event->getOldStatusVerb(),
                'new_status' => $event->getNewStatusVerb(),
            ],
            [
                'leadDetails',
                'userList',
                'publishDetails',
                'ipAddress',
                'tagList',
            ]
        );
    }

    /**
     * @param ChannelSubscriptionChange $event
     */
    public function onLeadUnsubscribed(ChannelSubscriptionChange $event)
    {
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::LEAD_UNSUBSCRIBED_CHANNEL,
            [
                'lead'       => $event->getLead(),
                'channel'    => $event->getChannel(),
                'old_status' => $event->getOldStatusVerb(),
                'new_status' => $event->getNewStatusVerb(),
            ],
            [
                'leadDetails',
                'userList',
                'publishDetails',
                'ipAddress',
                'tagList',
            ]
        );
    }

    /**
     * @param ChannelSubscriptionChange $event
     */
    public function onLeadResubscribed(ChannelSubscriptionChange $event)
    {
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::LEAD_SUBSCRIBED_CHANNEL,
            [
                'lead'       => $event->getLead(),
                'channel'    => $event->getChannel(),
                'old_status' => $event->getOldStatusVerb(),
                'new_status' => $event->getNewStatusVerb(),
            ],
            [
                'leadDetails',
                'userList',
                'publishDetails',
                'ipAddress',
                'tagList',
            ]
        );
    }

    /**
     * @param ChannelSubscriptionChange $event
     */
    public function onLeadEmailBounced(ChannelSubscriptionChange $event)
    {
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::LEAD_EMAIL_BOUNCED,
            [
                'lead'       => $event->getLead(),
                'channel'    => $event->getChannel(),
                'old_status' => $event->getOldStatusVerb(),
                'new_status' => $event->getNewStatusVerb(),
            ],
            [
                'leadDetails',
                'userList',
                'publishDetails',
                'ipAddress',
                'tagList',
            ]
        );
    }

    /**
     * @param ChannelSubscriptionChange $event
     */
    public function onLeadMarkedSpam(ChannelSubscriptionChange $event)
    {
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::LEAD_MARKED_SPAM,
            [
                'lead'       => $event->getLead(),
                'channel'    => $event->getChannel(),
                'old_status' => $event->getOldStatusVerb(),
                'new_status' => $event->getNewStatusVerb(),
            ],
            [
                'leadDetails',
                'userList',
                'publishDetails',
                'ipAddress',
                'tagList',
            ]
        );
    }

    /**
     * @param PageHitEvent $event
     */
    public function onEmailClick(PageHitEvent $event)
    {
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::CLICK_EMAIL_EVENT,
            [
                'hit' => $event->getHit(),
            ],
            [
                'hitDetails',
                'emailDetails',
                'pageList',
                'leadList',
                'ipAddress',
            ]
        );
    }

    /**
     * @param ListChangeEvent $event
     */
    public function onLeadListAdded(ListChangeEvent $event)
    {
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::LEAD_LIST_ADD,
            [
                'leads'  => !empty($event->getLead()) ? $event->getLead() : $event->getLeads(),
                'segment'=> $event->getList(),
            ],
            [
                'leadListDetails',
                'publishDetails',
                'leadDetails',
                'tagList',
            ]
        );
    }

    /**
     * @param ListOptInChangeEvent $event
     */
    public function onLeadListoptinAdded(ListOptInChangeEvent $event)
    {
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::LEAD_LIST_OPT_IN_ADD,
            [
                'leads' => !empty($event->getLead()) ? $event->getLead() : $event->getLeads(),
                'list'  => $event->getList(),
            ],
            [
                'leadListDetails',
                'publishDetails',
                'leadDetails',
                'tagList',
            ]
        );
    }

    /**
     * @param LeadEvent $event
     */
    public function onLeadCompletedDrip(LeadEvent $event)
    {
        $completeddrips = $event->getCompletedDripsIds();
        foreach ($completeddrips as $drip => $leads) {
            $dripcampaign = $this->webhookModel->getEntityManager()->getReference('MauticEmailBundle:DripEmail', $drip);
            foreach ($leads as $lead) {
                $lead = $this->webhookModel->getEntityManager()->getReference('MauticLeadBundle:Lead', $lead);
                $this->webhookModel->queueWebhooksByType(
                    LeadEvents::LEAD_COMPLETED_DRIP_CAMPAIGN,
                    [
                        'leads' => $lead,
                        'drip'  => $dripcampaign,
                    ],
                    [
                        'dripemailDetails',
                        'publishDetails',
                        'leadDetails',
                        'tagList',
                    ]
                );
                unset($lead);
            }
            unset($dripcampaign);
        }
    }

    /**
     * @param LeadEvent $event
     */
    public function onLeadDripAdd(LeadEvent $event)
    {
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::LEAD_DRIP_CAMPAIGN_ADD,
            [
                'leads' => $event->getLead(),
                'drip'  => $event->getDrip(),
            ],
            [
                'dripemailDetails',
                'publishDetails',
                'leadDetails',
                'tagList',
            ]
        );
    }

    /**
     * @param CampaignLeadChangeEvent $event
     */
    public function onLeadWorkflowAdd(CampaignLeadChangeEvent $event)
    {
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::LEAD_WORKFLOW_ADD,
            [
                'leads'     => !empty($event->getLead()) ? $event->getLead() : $event->getLeads(),
                'workflow'  => $event->getCampaign(),
            ],
            [
                'campaignDetails',
                'publishDetails',
                'leadDetails',
                'tagList',
            ]
        );
    }

    /**
     * @param LeadEvent $event
     */
    public function onLeadCompletedWorkflow(LeadEvent $event)
    {
        $this->webhookModel->queueWebhooksByType(
            LeadEvents::LEAD_COMPLETED_WORKFLOW,
            [
                'leads'     => $event->getLead(),
                'workflow'  => $event->getWorkflow(),
            ],
            [
                'campaignDetails',
                'publishDetails',
                'leadDetails',
                'tagList',
            ]
        );
    }
}
