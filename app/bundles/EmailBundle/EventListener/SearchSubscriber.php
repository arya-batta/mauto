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

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event as MauticEvents;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Helper\UserHelper;
use Mautic\EmailBundle\Model\DripEmailModel;
use Mautic\EmailBundle\Model\EmailModel;

/**
 * Class SearchSubscriber.
 */
class SearchSubscriber extends CommonSubscriber
{
    /**
     * @var EmailModel
     */
    protected $emailModel;

    /**
     * @var UserHelper
     */
    protected $userHelper;

    /**
     * @var DripEmailModel
     */
    protected $dripModel;

    /**
     * SearchSubscriber constructor.
     *
     * @param UserHelper     $userHelper
     * @param EmailModel     $emailModel
     * @param DripEmailModel $dripModel
     */
    public function __construct(UserHelper $userHelper, EmailModel $emailModel, DripEmailModel $dripModel)
    {
        $this->userHelper = $userHelper;
        $this->emailModel = $emailModel;
        $this->dripModel  = $dripModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::GLOBAL_SEARCH      => ['onGlobalSearch', 0],
            CoreEvents::BUILD_COMMAND_LIST => ['onBuildCommandList', 0],
        ];
    }

    /**
     * @param MauticEvents\GlobalSearchEvent $event
     */
    public function onGlobalSearch(MauticEvents\GlobalSearchEvent $event)
    {
        $str = $event->getSearchString();
        if (empty($str)) {
            return;
        }

        $filter      = ['string' => $str, 'force' => []];
        $permissions = $this->security->isGranted(
            ['email:emails:viewown', 'email:emails:viewother'],
            'RETURN_ARRAY'
        );
        if ($permissions['email:emails:viewown'] || $permissions['email:emails:viewother']) {
            if (!$permissions['email:emails:viewother']) {
                $filter['force'][] = [
                    'column' => 'IDENTITY(e.createdBy)',
                    'expr'   => 'eq',
                    'value'  => $this->userHelper->getUser()->getId(),
                ];
                /*$filter['force']=[
                    ['column' => 'IDENTITY(e.createdBy)', 'expr' => 'eq', 'value' => $this->userHelper->getUser()->getId()],
                    ['column' => 'e.emailType', 'expr' => 'neq', 'value' => 'dripemail'],
                ];*/
            }
            $filter['force'][] = [
                'column' => 'e.emailType',
                'expr'   => 'neq',
                'value'  => 'dripemail',
            ];
            $emailgroups = $this->emailModel->getEntities(
                [
                    'limit'  => 5,
                    'filter' => $filter,
                ]);

            $lists = [];
            $templates = [];

            foreach($emailgroups as $key => $emailgroup) {

                if($emailgroup->getEmailType() == 'list'){
                    $lists[$key] = $emailgroup;
                }else{
                    $templates[$key] = $emailgroup;
                }
            }

            if (count($lists) > 0) {
                $emailResults = [];

                foreach ($lists as $email) {
                    $emailResults[] = $this->templating->renderResponse(
                        'MauticEmailBundle:SubscribedEvents\Search:global.html.php',
                        ['email' => $email,'type'=> 'list']
                    )->getContent();
                }
                if (count($lists) > 5) {
                    $emailResults[] = $this->templating->renderResponse(
                        'MauticEmailBundle:SubscribedEvents\Search:global.html.php',
                        [
                            'showMore'     => true,
                            'searchString' => $str,
                            'remaining'    => (count($lists) - 5),
                            'type'         => 'list'
                        ]
                    )->getContent();
                }
                $emailResults['count'] = count($lists);
                $event->addResults('le.email.emails', $emailResults);
            }

            if (count($templates) > 0) {
                $emailResults = [];

                foreach ($templates as $email) {
                    $emailResults[] = $this->templating->renderResponse(
                        'MauticEmailBundle:SubscribedEvents\Search:global.html.php',
                        ['email' => $email,'type'=> 'template']
                    )->getContent();
                }
                if (count($templates) > 5) {
                    $emailResults[] = $this->templating->renderResponse(
                        'MauticEmailBundle:SubscribedEvents\Search:global.html.php',
                        [
                            'showMore'     => true,
                            'searchString' => $str,
                            'remaining'    => (count($templates) - 5),
                            'type'         => 'template'
                        ]
                    )->getContent();
                }
                $emailResults['count'] = count($templates);
                $event->addResults('le.email.notification_email', $emailResults);
            }

            $dripfilter      = ['string' => $str, 'force' => []];
            $drips           = $this->dripModel->getEntities(
                [
                    'limit'  => 5,
                    'filter' => $dripfilter,
                ]);

            if (count($drips) > 0) {
                $dripResults = [];

                foreach ($drips as $drip) {
                    $dripResults[] = $this->templating->renderResponse(
                        'MauticEmailBundle:SubscribedEvents\DripSearch:global.html.php',
                        ['drip' => $drip]
                    )->getContent();
                }
                if (count($drips) > 5) {
                    $dripResults[] = $this->templating->renderResponse(
                        'MauticEmailBundle:SubscribedEvents\DripSearch:global.html.php',
                        [
                            'showMore'     => true,
                            'searchString' => $str,
                            'remaining'    => (count($drips) - 5),
                        ]
                    )->getContent();
                }
                $dripResults['count'] = count($drips);
                $event->addResults('le.email.drip.email', $dripResults);
            }
        }
    }

    /**
     * @param MauticEvents\CommandListEvent $event
     */
    public function onBuildCommandList(MauticEvents\CommandListEvent $event)
    {
        if ($this->security->isGranted(['email:emails:viewown', 'email:emails:viewother'], 'MATCH_ONE')) {
            $event->addCommands(
                'le.email.emails',
                $this->emailModel->getCommandList()
            );
        }
    }
}
