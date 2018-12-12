<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PageBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\PageBundle\Event as Events;
use Mautic\PageBundle\PageEvents;
use Mautic\PointBundle\Event\PointBuilderEvent;
use Mautic\PointBundle\Model\PointModel;
use Mautic\PointBundle\PointEvents;

/**
 * Class PointSubscriber.
 */
class PointSubscriber extends CommonSubscriber
{
    /**
     * @var PointModel
     */
    protected $pointModel;

    /**
     * @var \Mautic\CoreBundle\Security\Permissions\CorePermissions
     */
    protected $security;

    /**
     * PointSubscriber constructor.
     *
     * @param PointModel      $pointModel
     * @param CorePermissions $security
     */
    public function __construct(PointModel $pointModel, CorePermissions $security)
    {
        $this->pointModel = $pointModel;
        $this->security   = $security;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PointEvents::POINT_ON_BUILD => ['onPointBuild', 0],
            PageEvents::PAGE_ON_HIT     => ['onPageHit', 0],
        ];
    }

    /**
     * @param PointBuilderEvent $event
     */
    public function onPointBuild(PointBuilderEvent $event)
    {
        if (!$this->security->isGranted('page:pages:viewown')) {
           // return;
        }
        $action = [
            'group'       => 'le.page.point.action',
            'label'       => 'le.page.point.action.pagehit',
            'description' => 'le.page.point.action.pagehit_descr',
            'callback'    => ['\\Mautic\\PageBundle\\Helper\\PointActionHelper', 'validatePageHit'],
            'formType'    => 'pointaction_pagehit',
        ];

        if ($this->security->isAdmin()) {
            $event->addAction('page.hit', $action);
        }

        $action = [
            'group'       => 'le.page.point.action',
            'label'       => 'le.page.point.action.urlhit',
            'description' => 'le.page.point.action.urlhit_descr',
            'callback'    => ['\\Mautic\\PageBundle\\Helper\\PointActionHelper', 'validateUrlHit'],
            'formType'    => 'pointaction_urlhit',
            'formTheme'   => 'MauticPageBundle:FormTheme\Point',
        ];

        $event->addAction('url.hit', $action);
    }

    /**
     * Trigger point actions for page hits.
     *
     * @param Events\PageHitEvent $event
     */
    public function onPageHit(Events\PageHitEvent $event)
    {
        if ($event->getPage()) {
            // Mautic Landing Page was hit
            $this->pointModel->triggerAction('page.hit', $event->getHit());
        } else {
            // Mautic Tracking Pixel was hit
            $this->pointModel->triggerAction('url.hit', $event->getHit());
        }
    }
}
