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
    $view->extend('MauticReportBundle:Report:index.html.php');
}
$isAdmin=$view['security']->isAdmin();
?>
<?php if (count($items)): ?>
    <div class="table-responsive panel-collapse pull out page-list">
        <table class="table table-hover table-striped table-bordered report-list" id="reportTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#reportTable',
                        'langVar'         => 'report.report',
                        'routeBase'       => 'report',
                        'templateButtons' => [
                            'delete' => $permissions['report:reports:deleteown'] || $permissions['report:reports:deleteother'],
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
                        'sessionVar' => 'report',
                        'orderBy'    => 'r.name',
                        'text'       => 'mautic.core.name',
                        'class'      => 'col-report-name',
                        'default'    => true,
                    ]
                );
                if ($isAdmin):
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'report',
                        'orderBy'    => 'r.id',
                        'text'       => 'mautic.core.id',
                        'class'      => 'col-report-id visible-md visible-lg',
                    ]
                );
                endif;
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'sms',
                    'orderBy'    => '',
                    'text'       => 'mautic.core.actions',
                    'class'      => 'col-lead-location visible-md visible-lg col-lead-actions',
                ]);
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <?php
                        echo $view->render(
                            'MauticCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit' => $security->hasEntityAccess(
                                        $permissions['report:reports:editown'],
                                        $permissions['report:reports:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone'  => $permissions['report:reports:create'],
                                    'delete' => $security->hasEntityAccess(
                                        $permissions['report:reports:deleteown'],
                                        $permissions['report:reports:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'routeBase' => 'report',
                                'langVar'   => 'report.report',
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <?php echo $view->render(
                            'MauticCoreBundle:Helper:publishstatus_icon.html.php',
                            ['item' => $item, 'model' => 'report.report']
                        ); ?>
                    </td>
                    <td>
                        <div>
                            <a href="<?php echo $view['router']->path('mautic_report_view', ['objectId' => $item->getId()]); ?>" data-toggle="ajax">
                                <?php echo $item->getName(); ?>
                            </a>
                        </div>
                        <?php if ($description = $item->getDescription()): ?>
                            <div class="text-muted mt-4">
                                <small><?php echo $description; ?></small>
                            </div>
                        <?php endif; ?>
                    </td>
                    <?php if ($isAdmin): ?>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                    <?php endif; ?>
                    <td>
                        <?php $hasEditAccess = $security->hasEntityAccess($permissions['report:reports:editown'], $permissions['report:reports:editother'], $item->getCreatedBy());
                        $hasDeleteAccess     = $security->hasEntityAccess($permissions['report:reports:deleteown'], $permissions['report:reports:deleteother'], $item->getCreatedBy());
                        $hasCloneAccess      = $permissions['report:reports:create']; ?>
                        <div style="position: relative;" class="fab-list-container">
                            <div class="md-fab-wrapper">
                                <div class="md-fab md-fab-toolbar md-fab-small md-fab-primary" id="mainClass-<?php echo $item->getId(); ?>" style="">
                                    <i class="material-icons" onclick="Mautic.showActionButtons('<?php echo $item->getId(); ?>')"></i>
                                    <div tabindex="0" class="md-fab-toolbar-actions toolbar-actions-<?php echo $item->getId(); ?>">
                                        <?php if ($hasEditAccess): ?>
                                            <a class="hidden-xs-sm -nospin" title="<?php echo $view['translator']->trans('mautic.core.form.edit'); ?>" href="<?php echo $view['router']->path('mautic_report_action', ['objectAction' => 'edit', 'objectId' => $item->getId()]); ?>" data-toggle="ajax">
                                                <span><i class="material-icons md-color-white">  </i></span></a>
                                        <?php endif; ?>
                                        <?php if ($hasCloneAccess) : ?>
                                            <a class="hidden-xs" title="<?php echo $view['translator']->trans('mautic.core.form.clone'); ?>" href="<?php echo $view['router']->path('mautic_report_action', ['objectId' => $item->getId(), 'objectAction' => 'clone']); ?>" data-toggle="ajax" data-uk-tooltip="">
                                                <i class="material-icons md-color-white">  </i> </a>
                                        <?php endif; ?>
                                        <?php if ($hasDeleteAccess):?>
                                            <a data-toggle="confirmation" href="<?php echo $view['router']->path('mautic_report_action', ['objectAction' => 'delete', 'objectId' => $item->getId()]); ?>" data-message="<?php echo $view->escape($view['translator']->trans('mautic.report.report.form.confirmdelete', ['%name%'=> $item->getName()])); ?>" data-confirm-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.delete')); ?>" data-confirm-callback="executeAction" title="<?php echo $view['translator']->trans('mautic.core.form.delete'); ?>" data-cancel-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.cancel')); ?>">
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
        <div class="panel-footer">
            <?php echo $view->render(
                'MauticCoreBundle:Helper:pagination.html.php',
                [
                    'totalItems' => $totalItems,
                    'page'       => $page,
                    'limit'      => $limit,
                    'menuLinkId' => 'mautic_report_index',
                    'baseUrl'    => $view['router']->path('mautic_report_index'),
                    'sessionVar' => 'report',
                ]
            ); ?>
        </div>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
