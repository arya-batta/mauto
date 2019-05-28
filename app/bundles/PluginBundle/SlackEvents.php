<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle;

/**
 * Class SlackEvents
 * Events available for PluginBundle.
 */
final class SlackEvents
{
    /**
     * The mautic.slack_pre_save event is thrown right before a slack is persisted.
     *
     * The event listener receives a
     * Mautic\PluginBundle\Event\SlackEvent instance.
     *
     * @var string
     */
    const SLACK_PRE_SAVE = 'mautic.slack_pre_save';

    /**
     * The mautic.slack_post_save event is thrown right after a slack is persisted.
     *
     * The event listener receives a
     * Mautic\PluginBundle\Event\SlackEvent instance.
     *
     * @var string
     */
    const SLACK_POST_SAVE = 'mautic.slack_post_save';

    /**
     * The mautic.slack_pre_delete event is thrown prior to when a slack is deleted.
     *
     * The event listener receives a
     * Mautic\PluginBundle\Event\SlackEvent instance.
     *
     * @var string
     */
    const SLACK_PRE_DELETE = 'mautic.slack_pre_delete';

    /**
     * The mautic.slack_post_delete event is thrown after a slack is deleted.
     *
     * The event listener receives a
     * Mautic\PluginBundle\Event\SlackEvent instance.
     *
     * @var string
     */
    const SLACK_POST_DELETE = 'mautic.slack_post_delete';
}
