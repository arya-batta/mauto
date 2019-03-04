<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($tmpl == 'index') {
    $view->extend('MauticEmailBundle:DripEmail:index.html.php');
}
$isAdmin=$view['security']->isAdmin();
?>

<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered email-list">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'          => 'true',
                        'actionRoute'       => $actionRoute,
                        'templateButtons'   => [
                            'delete' => $permissions['dripemail:emails:deleteown'] || $permissions['dripemail:emails:deleteother'],
                        ],
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'mautic.core.update.heading.status',
                        'class'      => 'col-status-name',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => 'e.name',
                        'text'       => 'mautic.core.name',
                        'class'      => 'col-email-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.drip.email.lead.stat.count',
                        'class'      => 'col-email-stats drip-col-stats',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.email.graph.line.stats.sent',
                        'class'      => 'col-email-stats drip-col-stats',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.email.label.list.reads',
                        'class'      => 'col-email-stats drip-col-stats',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.email.report.hits_count',
                        'class'      => 'col-email-stats drip-col-stats',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.email.token.unsubscribes_text',
                        'class'      => 'col-email-stats drip-col-stats',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.email.config.monitored_email.bounce_folder',
                        'class'      => 'col-email-stats drip-col-stats',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.email.email.spams',
                        'class'      => 'col-email-stats drip-col-stats',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.email.stat.failed',
                        'class'      => 'col-email-stats drip-col-stats',
                        'default'    => true,
                    ]
                );

                if ($isAdmin):
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => 'e.id',
                        'text'       => 'mautic.core.id',
                        'class'      => 'visible-md visible-lg col-email-id',
                    ]
                );
                endif;

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'email',
                    'orderBy'    => '',
                    'text'       => 'mautic.core.actions',
                    'class'      => 'col-lead-location visible-md visible-lg col-lead-actions',
                ]);
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <?php
                ?>
                <tr class="drip-email-col-stats" data-stats="<?php echo $item->getId(); ?>">
                    <td>
                        <?php
                        $edit = $view['security']->hasEntityAccess(
                            $permissions['dripemail:emails:editown'],
                            $permissions['dripemail:emails:editother'],
                            $item->getCreatedBy()
                        );

                        echo $view->render(
                            'MauticCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit'   => $edit,
                                    'clone'  => $permissions['dripemail:emails:create'],
                                    'delete' => $view['security']->hasEntityAccess(
                                        $permissions['dripemail:emails:deleteown'],
                                        $permissions['dripemail:emails:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'actionRoute'       => $actionRoute,
                                'translationBase'   => $translationBase,
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <?php echo $view->render('MauticCoreBundle:Helper:publishstatus_icon.html.php', ['item' => $item, 'model' => 'email.dripemail']); ?>
                    </td>
                    <td class="table-description" style="text-align: left;">
                        <div>
                            <?php $category = $item->getCategory(); ?>
                            <?php $catName  = ($category) ? $category->getTitle() : $view['translator']->trans('mautic.core.form.uncategorized'); ?>
                            <?php $color    = ($category) ? '#'.$category->getColor() : 'inherit'; ?>
                            <a href="<?php echo $view['router']->path(
                                $actionRoute,
                                ['objectAction' => 'view', 'objectId' => $item->getId()]
                            ); ?>" data-toggle="ajax">
                                <?php echo $item->getName(); ?> <b>(<?php echo empty($EmailsCount) ? '0' : empty($EmailsCount[$item->getId()]) ? '0' : $EmailsCount[$item->getId()]; ?> Emails)</b>
                            </a>
                            <div style="white-space: nowrap;">
                            <span class="label label-default pa-4" style="border: 1px solid #d5d5d5; background: <?php echo $color; ?>;"> </span> <span><?php echo $catName; ?></span>
                            </div>
                        </div>
                    </td>
                    <td class="visible-sm visible-md visible-lg drip-email-col-stats" data-stats="<?php echo $item->getId(); ?>">
                    <span class="mt-xs has-click-event clickable-stat"
                          id="drip-lead-count-<?php echo $item->getId(); ?>">
                            <a data-toggle="tooltip"
                               href="<?php echo $view['router']->path(
                                   'le_contact_index',
                                   ['search' => $view['translator']->trans('le.lead.drip.searchcommand.lead').':'.$item->getId()]
                               ); ?>"
                               title="<?php echo $view['translator']->trans('le.email.drip.stat.lead.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg drip-email-col-stats" data-stats="<?php echo $item->getId(); ?>">
                    <span class="mt-xs has-click-event clickable-stat"
                          id="drip-sent-count-<?php echo $item->getId(); ?>">
                            <a data-toggle="tooltip"
                               href="<?php echo $view['router']->path(
                                   'le_contact_index',
                                   ['search' => $view['translator']->trans('le.lead.drip.searchcommand.sent').':'.$item->getId()]
                               ); ?>"
                               title="<?php echo $view['translator']->trans('le.email.drip.stat.sent.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg drip-email-col-stats" data-stats="<?php echo $item->getId(); ?>">
                     <span class="mt-xs has-click-event clickable-stat"
                           id="drip-read-count-<?php echo $item->getId(); ?>">
                            <a data-toggle="tooltip"
                               href="<?php echo $view['router']->path(
                                   'le_contact_index',
                                   ['search' => $view['translator']->trans('le.lead.drip.searchcommand.read').':'.$item->getId()]
                               ); ?>"
                               title="<?php echo $view['translator']->trans('le.email.drip.stat.read.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg drip-email-col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs has-click-event clickable-stat"
                            id="drip-click-count-<?php echo $item->getId(); ?>">
                            <a data-toggle="tooltip"
                               href="<?php echo $view['router']->path(
                                   'le_contact_index',
                                   ['search' => $view['translator']->trans('le.lead.drip.searchcommand.click').':'.$item->getId()]
                               ); ?>"
                               title="<?php echo $view['translator']->trans('le.email.drip.stat.clicks.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg drip-email-col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs has-click-event clickable-stat"
                            id="drip-unsubscribe-count-<?php echo $item->getId(); ?>">
                            <a data-toggle="tooltip"
                               href="<?php echo $view['router']->path(
                                   'le_contact_index',
                                   ['search' => $view['translator']->trans('le.lead.drip.searchcommand.unsubscribe').':'.$item->getId()]
                               ); ?>"
                               title="<?php echo $view['translator']->trans('le.email.drip.stat.unsub.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg drip-email-col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs has-click-event clickable-stat"
                            id="drip-bounce-count-<?php echo $item->getId(); ?>">
                            <a data-toggle="tooltip"
                               href="<?php echo $view['router']->path(
                                   'le_contact_index',
                                   ['search' => $view['translator']->trans('le.lead.drip.searchcommand.bounce').':'.$item->getId()]
                               ); ?>"
                               title="<?php echo $view['translator']->trans('le.email.drip.stat.bounce.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg drip-email-col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs has-click-event clickable-stat"
                            id="drip-spam-count-<?php echo $item->getId(); ?>">
                            <a data-toggle="tooltip"
                               href="<?php echo $view['router']->path(
                                   'le_contact_index',
                                   ['search' => $view['translator']->trans('le.lead.drip.searchcommand.spam').':'.$item->getId()]
                               ); ?>"
                               title="<?php echo $view['translator']->trans('le.email.drip.stat.spam.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg drip-email-col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs has-click-event clickable-stat"
                            id="drip-failed-count-<?php echo $item->getId(); ?>">
                            <a data-toggle="tooltip"
                               href="<?php echo $view['router']->path(
                                   'le_contact_index',
                                   ['search' => $view['translator']->trans('le.lead.drip.searchcommand.failed').':'.$item->getId()]
                               ); ?>"
                               title="<?php echo $view['translator']->trans('le.email.drip.stat.failed.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <?php if ($isAdmin) : ?>
                        <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                    <?php endif; ?>
                    <td>

                        <?php $hasEditAccess = $view['security']->hasEntityAccess($permissions['dripemail:emails:editown'], $permissions['dripemail:emails:editother'], $item->getCreatedBy());
                        $hasDeleteAccess     = $view['security']->hasEntityAccess($permissions['dripemail:emails:deleteown'], $permissions['dripemail:emails:deleteother'], $item->getCreatedBy());
                        $hasCloneAccess      = $permissions['dripemail:emails:create']; ?>
                        <div style="position: relative;" class="fab-list-container">
                            <div class="md-fab-wrapper">
                                <div class="md-fab md-fab-toolbar md-fab-small md-fab-primary" id="mainClass-<?php echo $item->getId(); ?>" style="">
                                    <i class="material-icons" onclick="Le.showActionButtons('<?php echo $item->getId(); ?>');"></i>
                                    <div tabindex="0" class="md-fab-toolbar-actions toolbar-actions-<?php echo $item->getId(); ?>">
                                        <?php if ($hasEditAccess): ?>
                                            <a class="hidden-xs-sm -nospin" title="<?php echo $view['translator']->trans('mautic.core.form.edit'); ?>" href="<?php echo $view['router']->path('le_dripemail_campaign_action', ['objectAction' => 'edit', 'objectId' => $item->getId()]); ?>" data-toggle="ajax">
                                                <span><i class="material-icons md-color-white">  </i></span></a>
                                        <?php endif; ?>
                                        <?php if ($hasCloneAccess) : ?>
                                            <a class="hidden-xs" title="<?php echo $view['translator']->trans('mautic.core.form.clone'); ?>" href="<?php echo $view['router']->path('le_dripemail_campaign_action', ['objectId' => $item->getId(), 'objectAction' => 'clone']); ?>" data-toggle="ajax" data-uk-tooltip="">
                                                <i class="material-icons md-color-white">  </i> </a>
                                        <?php endif; ?>
                                        <?php if ($hasDeleteAccess):?>
                                            <a data-toggle="confirmation" href="<?php echo $view['router']->path('le_dripemail_campaign_action', ['objectAction' => 'delete', 'objectId' => $item->getId()]); ?>" data-message="<?php echo $view->escape($view['translator']->trans('le.dripemail.form.confirmdelete', ['%name%'=> $item->getName()])); ?>" data-confirm-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.delete')); ?>" data-confirm-callback="executeAction" title="<?php echo $view['translator']->trans('mautic.core.form.delete'); ?>" data-cancel-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.cancel')); ?>">
                                                <span><i class="material-icons md-color-white">  </i></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <?php echo $view->render(
            'MauticCoreBundle:Helper:pagination.html.php',
            [
                'totalItems' => $totalItems,
                'page'       => $page,
                'limit'      => $limit,
                'baseUrl'    => $view['router']->path($indexRoute),
                'sessionVar' => 'email',
            ]
        ); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
