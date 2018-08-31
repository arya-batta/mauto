<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($tmpl == 'index') {
    $view->extend('MauticFocusBundle:Focus:index.html.php');
}
$isAdmin=$view['security']->isAdmin();
?>

<?php if (count($items)): ?>
    <div class="table-responsive page-list">
        <table class="table table-hover table-striped table-bordered focus-list" id="focusTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#focusTable',
                        'routeBase'       => 'focus',
                        'templateButtons' => [
                            'delete' => $permissions['plugin:focus:items:delete'],
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
                        'sessionVar' => 'focus',
                        'orderBy'    => 'f.name',
                        'text'       => 'mautic.core.name',
                        'class'      => 'col-focus-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'focus',
                        'orderBy'    => 'c.title',
                        'text'       => 'mautic.core.category',
                        'class'      => 'visible-md visible-lg col-focus-category',
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'focus',
                        'orderBy'    => 'f.type',
                        'text'       => 'mautic.focus.thead.type',
                        'class'      => 'visible-md visible-lg col-focus-type',
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'focus',
                        'orderBy'    => 'f.style',
                        'text'       => 'mautic.focus.thead.style',
                        'class'      => 'visible-md visible-lg col-focus-style',
                    ]
                );
                if ($isAdmin):
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'focus',
                        'orderBy'    => 'f.id',
                        'text'       => 'mautic.core.id',
                        'class'      => 'visible-md visible-lg col-focus-id',
                    ]
                );
                endif;
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'focus',
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
                                    'edit' => $view['security']->hasEntityAccess(
                                        $permissions['plugin:focus:items:editown'],
                                        $permissions['plugin:focus:items:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone'  => $permissions['plugin:focus:items:create'],
                                    'delete' => $view['security']->hasEntityAccess(
                                        $permissions['plugin:focus:items:deleteown'],
                                        $permissions['plugin:focus:items:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'routeBase' => 'focus',
                            ]
                        );
                        ?>
                    </td> <td><?php echo $view->render('MauticCoreBundle:Helper:publishstatus_icon.html.php', ['item' => $item, 'model' => 'focus']); ?> </td>
                    <td class="table-description">
                        <div>

                            <a data-toggle="ajax" href="<?php echo $view['router']->path(
                                'mautic_focus_action',
                                ['objectId' => $item->getId(), 'objectAction' => 'view']
                            ); ?>">
                                <?php echo $item->getName(); ?>
                            </a>
                        </div>
                        <?php if ($description = $item->getDescription()): ?>
                            <div class="text-muted mt-4">
                                <small><?php echo $description; ?></small>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="visible-md visible-lg">
                        <?php $category = $item->getCategory(); ?>
                        <?php $catName  = ($category) ? $category->getTitle() : $view['translator']->trans('mautic.core.form.uncategorized'); ?>
                        <?php $color    = ($category) ? '#'.$category->getColor() : 'inherit'; ?>
                        <span style="white-space: nowrap;"><span class="label label-default pa-4" style="border: 1px solid #d5d5d5; background: <?php echo $color; ?>;"> </span> <span><?php echo $catName; ?></span></span>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $view['translator']->trans('mautic.focus.type.'.$item->getType()); ?></td>
                    <td class="visible-md visible-lg"><?php echo $view['translator']->trans('mautic.focus.style.'.$item->getStyle()); ?></td>
                    <?php  if ($isAdmin): ?>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                    <?php  endif; ?>
                    <td>
                        <?php
                        $hasEditAccess   = $view['security']->hasEntityAccess($permissions['plugin:focus:items:editown'], $permissions['plugin:focus:items:editother'], $item->getCreatedBy());
                        $hasDeleteAccess = $view['security']->hasEntityAccess($permissions['plugin:focus:items:deleteown'], $permissions['plugin:focus:items:deleteother'], $item->getCreatedBy());
                        $hasCloneAccess  = $permissions['plugin:focus:items:create']; ?>
                        <div style="position: relative;" class="fab-list-container">
                            <div class="md-fab-wrapper">
                                <div class="md-fab md-fab-toolbar md-fab-small md-fab-primary" id="mainClass-<?php echo $item->getId(); ?>" style="">
                                    <i class="material-icons" onclick="Mautic.showActionButtons('<?php echo $item->getId(); ?>')"></i>
                                    <div tabindex="0" class="md-fab-toolbar-actions">
                                        <?php if ($hasEditAccess): ?>
                                            <a class="hidden-xs-sm -nospin" title="<?php echo $view['translator']->trans('mautic.core.form.edit'); ?>" href="<?php echo $view['router']->path('mautic_focus_action', ['objectAction' => 'edit', 'objectId' => $item->getId()]); ?>" data-toggle="ajax">
                                                <span><i class="material-icons md-color-white">  </i></span></a>
                                        <?php endif; ?>
                                        <?php if ($hasCloneAccess) : ?>
                                            <a class="hidden-xs" title="<?php echo $view['translator']->trans('mautic.core.form.clone'); ?>" href="<?php echo $view['router']->path('mautic_focus_action', ['objectId' => $item->getId(), 'objectAction' => 'clone']); ?>" data-toggle="ajax" data-uk-tooltip="">
                                                <i class="material-icons md-color-white">  </i> </a>
                                        <?php endif; ?>
                                        <?php if ($hasDeleteAccess):?>
                                            <a data-toggle="confirmation" href="<?php echo $view['router']->path('mautic_focus_action', ['objectAction' => 'delete', 'objectId' => $item->getId()]); ?>" data-message="<?php echo $view->escape($view['translator']->trans('mautic.focus.form.confirmdelete', ['%name%'=> $item->getName()])); ?>" data-confirm-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.delete')); ?>" data-confirm-callback="executeAction" title="<?php echo $view['translator']->trans('mautic.core.form.delete'); ?>" data-cancel-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.cancel')); ?>">
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
                'totalItems' => count($items),
                'page'       => $page,
                'limit'      => $limit,
                'baseUrl'    => $view['router']->path('mautic_focus_index'),
                'sessionVar' => 'focus',
            ]
        ); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php', ['tip' => 'mautic.focus.noresults.tip']); ?>
<?php endif; ?>
