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
    $view->extend('MauticEmailBundle:Email:index.html.php');
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
                            'delete' => $permissions['email:emails:deleteown'] || $permissions['email:emails:deleteother'],
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
                        'text'       => 'mautic.email.graph.line.stats.pending',
                        'class'      => 'col-email-stats',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'mautic.email.graph.line.stats.sent',
                        'class'      => 'col-email-stats',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'mautic.email.label.list.reads',
                        'class'      => 'col-email-stats',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'mautic.email.report.hits_count',
                        'class'      => 'col-email-stats',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'mautic.email.token.unsubscribes_text',
                        'class'      => 'col-email-stats',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'mautic.email.config.monitored_email.bounce_folder',
                        'class'      => 'col-email-stats',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.email.email.spams',
                        'class'      => 'col-email-stats',
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
                $hasVariants                = $item->isVariant();
                $hasTranslations            = $item->isTranslation();
                $type                       = $item->getEmailType();
                $mauticTemplateVars['item'] = $item;
                ?>
                <tr>
                    <td>
                        <?php
                        $edit = $view['security']->hasEntityAccess(
                            $permissions['email:emails:editown'],
                            $permissions['email:emails:editother'],
                            $item->getCreatedBy()
                        );
                        $customButtons = ($type == 'list') ? [
                            [
                                'attr' => [
                                    'data-toggle' => 'ajax',
                                    'href'        => $view['router']->path(
                                        $actionRoute,
                                        ['objectAction' => 'send', 'objectId' => $item->getId()]
                                    ),
                                ],
                                'iconClass' => 'fa fa-send-o',
                                'btnText'   => 'mautic.email.send',
                            ],
                        ] : [];
                        echo $view->render(
                            'MauticCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit'   => $edit,
                                    'clone'  => $permissions['email:emails:create'],
                                    'delete' => $view['security']->hasEntityAccess(
                                        $permissions['email:emails:deleteown'],
                                        $permissions['email:emails:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                    'abtest' => (!$hasVariants && $edit && $permissions['email:emails:create']),
                                ],
                                'actionRoute'       => $actionRoute,
                                'customButtons'     => $customButtons,
                                'translationBase'   => $translationBase,
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <?php echo $view->render('MauticCoreBundle:Helper:publishstatus_icon.html.php', ['item' => $item, 'model' => 'email']); ?>
                    </td>
                    <td class="table-description">
                        <div>
                            <?php $category = $item->getCategory(); ?>
                            <?php $catName  = ($category) ? $category->getTitle() : $view['translator']->trans('mautic.core.form.uncategorized'); ?>
                            <?php $color    = ($category) ? '#'.$category->getColor() : 'inherit'; ?>
                            <a href="<?php echo $view['router']->path(
                                $actionRoute,
                                ['objectAction' => 'view', 'objectId' => $item->getId()]
                            ); ?>" data-toggle="ajax">
                                <?php echo $item->getName(); ?>
                                <?php if ($hasVariants): ?>
                                    <span data-toggle="tooltip" title="<?php echo $view['translator']->trans('mautic.core.icon_tooltip.ab_test'); ?>">
                                    <i class="fa fa-fw fa-sitemap"></i>
                                </span>
                                <?php endif; ?>
                                <?php if ($hasTranslations): ?>
                                    <span data-toggle="tooltip" title="<?php echo $view['translator']->trans(
                                        'mautic.core.icon_tooltip.translation'
                                    ); ?>">
                                    <i class="fa fa-fw fa-language"></i>
                                </span>
                                <?php endif; ?>
                                <?php if ($type !== 'list'): ?>
                                    <span data-toggle="tooltip" title="<?php echo $view['translator']->trans(
                                        'mautic.email.icon_tooltip.list_email'
                                    ); ?>">
                                    <i class="fa fa-fw fa-pie-chart"></i>
                                </span>
                                <?php endif; ?>
                                <?php echo $view['content']->getCustomContent('email.name', $mauticTemplateVars); ?>
                            </a>
                            <div style="white-space: nowrap;">
                            <span class="label label-default pa-4" style="border: 1px solid #d5d5d5; background: <?php echo $color; ?>;"> </span> <span><?php echo $catName; ?></span>
                            </div>
                        </div>
                        <?php if ($description = $item->getDescription()): ?>
                            <div class="text-muted mt-4">
                                <small><?php echo $description; ?></small>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs has-click-event clickable-stat"
                            id="pending-<?php echo $item->getId(); ?>">
                            <a href="<?php echo $view['router']->path(
                                'mautic_contact_index',
                                ['search' => $view['translator']->trans('mautic.lead.lead.searchcommand.email_pending').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('mautic.email.stat.click.percentage.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                   <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                    <span class="mt-xs has-click-event clickable-stat"
                          id="sent-count-<?php echo $item->getId(); ?>">
                            <a href="<?php echo $view['router']->path(
                                'mautic_contact_index',
                                ['search' => $view['translator']->trans('mautic.lead.lead.searchcommand.email_sent').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('mautic.email.stat.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                   </td>
                   <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                     <span class="mt-xs has-click-event clickable-stat"
                           id="read-count-<?php echo $item->getId(); ?>">
                            <a href="<?php echo $view['router']->path(
                                'mautic_contact_index',
                                ['search' => $view['translator']->trans('mautic.lead.lead.searchcommand.email_read').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('mautic.email.stat.read.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs has-click-event clickable-stat"
                            id="read-percent-<?php echo $item->getId(); ?>">
                            <a href="<?php echo $view['router']->path(
                                'mautic_contact_index',
                                ['search' => $view['translator']->trans('mautic.lead.lead.searchcommand.email_read').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('mautic.email.stat.click.percentage.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs has-click-event clickable-stat"
                            id="unsubscribe-count-<?php echo $item->getId(); ?>">
                            <a href="<?php echo $view['router']->path(
                                'mautic_contact_index',
                                ['search' => $view['translator']->trans('mautic.lead.lead.searchcommand.email_unsubscribe').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('mautic.email.stat.unsubscribe.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats">
                           <span class="mt-xs has-click-event clickable-stat"
                                 id="bounce-count-<?php echo $item->getId(); ?>">
                            <a href="<?php echo $view['router']->path(
                                'mautic_contact_index',
                                ['search' => $view['translator']->trans('mautic.lead.lead.searchcommand.email_bounce').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('mautic.email.stat.bounce.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats">
                       <span class="mt-xs has-click-event clickable-stat"
                             id="spam-count-<?php echo $item->getId(); ?>"  >
                            <a href="<?php echo $view['router']->path(
                                'mautic_contact_index',
                                ['search' => $view['translator']->trans('mautic.lead.lead.searchcommand.email_spam').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('mautic.email.stat.spam.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="hide" data-stats="<?php echo $item->getId(); ?>">
                     <span class="mt-xs has-click-event clickable-stat"
                           id="failure-count-<?php echo $item->getId(); ?>">
                            <a href="<?php echo $view['router']->path(
                                'mautic_contact_index',
                                ['search' => $view['translator']->trans('mautic.lead.lead.searchcommand.email_failure').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('mautic.email.stat.failure.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <?php if ($isAdmin):?>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                    <?php endif; ?>
                    <td>

                        <?php $hasEditAccess = $view['security']->hasEntityAccess($permissions['email:emails:editown'], $permissions['email:emails:editother'], $item->getCreatedBy());
                        $hasDeleteAccess     = $view['security']->hasEntityAccess($permissions['email:emails:deleteown'], $permissions['email:emails:deleteother'], $item->getCreatedBy());
                        $hasCloneAccess      = $permissions['email:emails:create']; ?>
                        <div style="position: relative;" class="fab-list-container">
                            <div class="md-fab-wrapper">
                                <div class="md-fab md-fab-toolbar md-fab-small md-fab-primary" id="mainClass-<?php echo $item->getId(); ?>" style="">
                                    <i class="material-icons" onclick="Mautic.showActionButtons('<?php echo $item->getId(); ?>')"></i>
                                    <div tabindex="0" class="md-fab-toolbar-actions">
                                        <?php if ($hasEditAccess): ?>
                                            <a class="hidden-xs-sm -nospin" title="<?php echo $view['translator']->trans('mautic.core.form.edit'); ?>" href="<?php echo $view['router']->path('mautic_email_campaign_action', ['objectAction' => 'edit', 'objectId' => $item->getId()]); ?>" data-toggle="ajax">
                                                <span><i class="material-icons md-color-white">  </i></span></a>
                                        <?php endif; ?>
                                        <?php if ($hasCloneAccess) : ?>
                                            <a class="hidden-xs" title="<?php echo $view['translator']->trans('mautic.core.form.clone'); ?>" href="<?php echo $view['router']->path('mautic_email_campaign_action', ['objectId' => $item->getId(), 'objectAction' => 'clone']); ?>" data-toggle="ajax" data-uk-tooltip="">
                                                <i class="material-icons md-color-white">  </i> </a>
                                        <?php endif; ?>
                                        <?php if ($hasDeleteAccess):?>
                                            <a data-toggle="confirmation" href="<?php echo $view['router']->path('mautic_email_campaign_action', ['objectAction' => 'delete', 'objectId' => $item->getId()]); ?>" data-message="<?php echo $view->escape($view['translator']->trans('mautic.email.form.confirmdelete', ['%name%'=> $item->getName()])); ?>" data-confirm-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.delete')); ?>" data-confirm-callback="executeAction" title="<?php echo $view['translator']->trans('mautic.core.form.delete'); ?>" data-cancel-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.cancel')); ?>">
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
