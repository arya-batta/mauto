<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$view->extend('MauticCoreBundle:Default:content.html.php');

$view['slots']->set('leContent', 'dripemail');
$view['slots']->set('headerTitle', $entity->getName());
//dump($actionRoute);

$customButtons = [];
$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'item'            => $entity,
            'templateButtons' => [
                'edit' => $view['security']->hasEntityAccess(
                    $permissions['dripemail:emails:editown'],
                    $permissions['dripemail:emails:editother'],
                    $entity->getCreatedBy()
                ),
                'clone'  => $permissions['dripemail:emails:create'],
                'delete' => $view['security']->hasEntityAccess(
                    $permissions['dripemail:emails:deleteown'],
                    $permissions['dripemail:emails:deleteother'],
                    $entity->getCreatedBy()
                ),
                'close' => $view['security']->hasEntityAccess(
                    $permissions['dripemail:emails:viewown'],
                    $permissions['dripemail:emails:viewother'],
                    $entity->getCreatedBy()
                ),
            ],
            'actionRoute'   => $actionRoute,
            'indexRoute'    => $indexRoute,
            'customButtons' => $customButtons,
            'langVar'       => 'dripemail',
        ]
    )
);

$view['slots']->set(
    'publishStatus',
    $view->render('MauticCoreBundle:Helper:publishstatus_badge.html.php', ['entity' => $entity])
);
?>

<!-- start: box layout -->
<div class="box-layout">
    <!-- left section -->
    <div class="col-md-9 bg-white height-auto">
        <div class="bg-auto">
            <!-- email detail header -->
            <div class="pr-md pl-md hide pt-lg pb-lg">
                <div class="box-layout">
                    <div class="col-xs-10">
                        <div>
                            <?php echo \Mautic\CoreBundle\Helper\EmojiHelper::toHtml($entity->getName(), 'short'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <!--/ email detail header -->

            <!-- email detail collapseable -->
            <div class="collapse" id="dripemail-details">
                <div class="pr-md pl-md pb-md">
                    <div class="panel shd-none mb-0">
                        <table class="table table-bordered table-striped mb-0">
                            <tbody>
                            <?php echo $view->render(
                                'MauticCoreBundle:Helper:details.html.php',
                                ['entity' => $entity]
                            ); ?>
                            <?php if ($fromName = $entity->getFromName()): ?>
                                <tr>
                                    <td width="20%">
                                        <span class="fw-b"><?php echo $view['translator']->trans('le.email.from_name'); ?></span>
                                    </td>
                                    <td><?php echo $fromName; ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($fromEmail = $entity->getFromAddress()): ?>
                                <tr>
                                    <td width="20%">
                                        <span class="fw-b"><?php echo $view['translator']->trans('le.email.from_email'); ?></span>
                                    </td>
                                    <td><?php echo $fromEmail; ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($replyTo = $entity->getReplyToAddress()): ?>
                                <tr>
                                    <td width="20%">
                                        <span class="fw-b"><?php echo $view['translator']->trans('le.email.reply_to_email'); ?></span>
                                    </td>
                                    <td><?php echo $replyTo; ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($bccAddress = $entity->getBccAddress()): ?>
                                <tr>
                                    <td width="20%">
                                        <span class="fw-b"><?php echo $view['translator']->trans('le.email.bcc'); ?></span>
                                    </td>
                                    <td><?php echo $bccAddress; ?></td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--/ email detail collapseable -->
        </div>

        <div class="bg-auto bg-dark-xs">
            <!-- email detail collapseable toggler -->
            <div class="hr-expand hide nm">
                <span data-toggle="tooltip" title="<?php echo $view['translator']->trans('mautic.core.details'); ?>">
                    <a href="javascript:void(0)" class="arrow text-muted collapsed" data-toggle="collapse"
                       data-target="#dripemail-details">
                        <span class="caret"></span> <?php echo $view['translator']->trans('mautic.core.details'); ?>
                    </a>
                </span>
            </div>
            <!--/ email detail collapseable toggler -->
                <?php echo $view->render('MauticEmailBundle:Email:campaign_stats.html.php',
                    [
                        'sentcount'        => $emailStats['sent'],
                        'uniqueopen'       => $emailStats['uopen'],
                        'totalopen'        => $emailStats['topen'],
                        'notopencount'     => $emailStats['nopen'],
                        'click'            => $emailStats['click'],
                        'unsubscribecount' => $emailStats['unsubscribe'],
                        'bouncecount'      => $emailStats['bounce'],
                        'spamcount'        => $emailStats['spam'],
                        'failedcount'      => $emailStats['failed'],
                    ]
                ); ?>
            <?php echo $view->render(
                'MauticEmailBundle:DripEmail:graph.html.php',
                [
                    'stats'         => $stats,
                    'entity'        => $entity,
                    'dateRangeForm' => $dateRangeForm,
                ]
            ); ?>
            <div class="row list-panel-padding">
            <div class="col-md-12 bdr-w-0">
                <div>
                    <h2 class="email-dataview-stats stats-margin"><?php echo $view['translator']->trans('le.email.click.email.activity')?></h2>
                </div>
                <?php echo $view->render('MauticEmailBundle:DripEmail:emails.html.php', ['items' => $emailList, 'security' => $security]); ?>
            </div>
            <div class="col-md-12 bdr-w-0">
                <div>
                    <h2 class="email-dataview-stats stats-margin"><?php echo $view['translator']->trans('le.email.click.last10openleads')?><a class="" href="<?php echo $view['router']->path(
                            'le_contact_index',
                            ['search' => $view['translator']->trans('le.lead.drip.searchcommand.read').':'.$entity->getId()]
                        ); ?>" title="<?php echo $view['translator']->trans('le.email.click.viewmore')?>"><span> <i class="fa fa-th-list email-stat-icon"></i></span></a></h2>
                </div>
                <?php echo $view->render('MauticEmailBundle:Email:leads.html.php', ['items' => $openLeads, 'security' => $security]); ?>
            </div>
            <div class="col-md-12 bdr-w-0">
                <div>
                    <h2 class="email-dataview-stats stats-margin"><?php echo $view['translator']->trans('le.email.click.last10clickleads')?><a class="" href="<?php echo $view['router']->path(
                            'le_contact_index',
                            ['search' => $view['translator']->trans('le.lead.drip.searchcommand.click').':'.$entity->getId()]
                        ); ?>" title="<?php echo $view['translator']->trans('le.email.click.viewmore')?>"><span> <i class="fa fa-th-list email-stat-icon"></i></span></a></h2>
                </div>
                <?php echo $view->render('MauticEmailBundle:Email:leads.html.php', ['items' => $clickLeads, 'security' => $security]); ?>
            </div>
            <div class="col-md-12 bdr-w-0 hide">
                <div>
                    <h2 class="email-dataview-stats stats-margin"><?php echo $view['translator']->trans('le.email.click.last10unsubscribeleads')?><a class="" href="<?php echo $view['router']->path(
                            'le_contact_index',
                            ['search' => $view['translator']->trans('le.lead.drip.searchcommand.unsubscribe').':'.$entity->getId()]
                        ); ?>" title="<?php echo $view['translator']->trans('le.email.click.viewmore')?>"><span> <i class="fa fa-th-list email-stat-icon"></i></span></a></h2>
                </div>
                <?php echo $view->render('MauticEmailBundle:Email:leads.html.php', ['items' => $unsubscribeLeads, 'security' => $security]); ?>
            </div>
            <div class="col-md-12 bdr-w-0 hide">
                <div>
                    <h2 class="email-dataview-stats stats-margin"><?php echo $view['translator']->trans('le.email.click.last10bouncedleads')?><a class="" href="<?php echo $view['router']->path(
                            'le_contact_index',
                            ['search' => $view['translator']->trans('le.lead.drip.searchcommand.bounce').':'.$entity->getId()]
                        ); ?>" title="<?php echo $view['translator']->trans('le.email.click.viewmore')?>"><span> <i class="fa fa-th-list email-stat-icon"></i></span></a></h2>
                </div>
                <?php echo $view->render('MauticEmailBundle:Email:leads.html.php', ['items' => $bounceLeads, 'security' => $security]); ?>
            </div>
                <div class="col-md-12 bdr-w-0">
                    <div>
                        <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.email.click.last10churn')?><a class="" href="<?php echo $view['router']->path(
                                'le_contact_index',
                                ['search' => $view['translator']->trans('le.lead.drip.searchcommand.churn').':'.$entity->getId()]
                            ); ?>" title="<?php echo $view['translator']->trans('le.email.click.viewmore')?>"><span> <i class="fa fa-th-list email-stat-icon"></i></span></a></h2>
                        <?php echo $view->render('MauticEmailBundle:Email:leads.html.php', ['items' => $emailChurn, 'security' => $security]); ?>
                    </div>
                </div>
                <div class="col-md-12 bdr-w-0">
                    <div>
                        <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.email.click.last10failed')?><a class="" href="<?php echo $view['router']->path(
                                'le_contact_index',
                                ['search' => $view['translator']->trans('le.lead.drip.searchcommand.failed').':'.$entity->getId()]
                            ); ?>" title="<?php echo $view['translator']->trans('le.email.click.viewmore')?>"><span> <i class="fa fa-th-list email-stat-icon"></i></span></a></h2>
                        <?php echo $view->render('MauticEmailBundle:Email:leads.html.php', ['items' => $emailfailed, 'security' => $security]); ?>
                    </div>
                </div>
            </div>

            <!-- tabs controls -->
            <ul class="nav hide nav-tabs pr-md pl-md">
                <li class="active">
                    <a href="#contacts-container" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('le.email.associated.contacts'); ?>
                    </a>
                </li>
            </ul>
            <!--/ tabs controls -->

        </div>

        <!-- start: tab-content -->
        <div class="tab-content hide pa-md">
            <div class="tab-pane active bdr-w-0" id="contacts-container">
                <?php echo $contacts; ?>
            </div>
        </div>
    </div>
    <!--/ left section -->

    <!-- right section -->
    <div class="col-md-3 bg-white hide bdr-l height-auto">
        <!-- activity feed -->
        <?php echo $view->render('MauticCoreBundle:Helper:recentactivity.html.php', ['logs' => $logs]); ?>
    </div>
    <!--/ right section -->
    <input name="entityId" id="entityId" type="hidden" value="<?php echo $view->escape($entity->getId()); ?>"/>
</div>
