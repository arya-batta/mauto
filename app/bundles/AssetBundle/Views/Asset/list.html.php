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
    $view->extend('MauticAssetBundle:Asset:index.html.php');
}
$isAdmin=$view['security']->isAdmin();
?>
<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered asset-list" id="assetTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#assetTable',
                        'langVar'         => 'asset.asset',
                        'routeBase'       => 'asset',
                        'templateButtons' => [
                            'delete' => $permissions['asset:assets:deleteown'] || $permissions['asset:assets:deleteother'],
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
                        'sessionVar' => 'asset',
                        'orderBy'    => 'a.title',
                        'text'       => 'mautic.core.title',
                        'class'      => 'col-asset-title',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'asset',
                        'orderBy'    => 'c.title',
                        'text'       => 'mautic.core.category',
                        'class'      => 'col-asset-category',
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'asset',
                        'text'       => 'mautic.asset.asset.url',
                        'class'      => 'col-asset-download-count',
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'asset',
                        'orderBy'    => 'a.downloadCount',
                        'text'       => 'mautic.asset.asset.thead.download.count',
                        'class'      => 'col-asset-download-count',
                    ]
                );
            if ($isAdmin):
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'asset',
                        'orderBy'    => 'a.id',
                        'text'       => 'mautic.core.id',
                        'class'      => 'col-asset-id',
                    ]
                );
            endif;
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'asset',
                    'orderBy'    => '',
                    'text'       => 'mautic.core.actions',
                    'class'      => 'col-lead-location col-asset-actions col-lead-actions',
                ]);
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $k => $item): ?>
                <tr>
                    <td>
                        <?php
                        $entity     =$model->getEntity($item->getId());
                        $downloadurl=$model->generateUrl($entity);
                        echo $view->render(
                            'MauticCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit' => $security->hasEntityAccess(
                                        $permissions['asset:assets:editown'],
                                        $permissions['asset:assets:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'delete' => $security->hasEntityAccess(
                                        $permissions['asset:assets:deleteown'],
                                        $permissions['asset:assets:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone' => $permissions['asset:assets:create'],
                                ],
                                'routeBase'     => 'asset',
                                'langVar'       => 'asset.asset',
                                'nameGetter'    => 'getTitle',
                                'customButtons' => [
                                    [
                                        'attr' => [
                                            'data-toggle' => 'ajaxmodal',
                                            'data-target' => '#AssetPreviewModal',
                                            'href'        => $view['router']->path(
                                                'le_asset_action',
                                                ['objectAction' => 'preview', 'objectId' => $item->getId()]
                                            ),
                                        ],
                                        'btnText'   => $view['translator']->trans('mautic.asset.asset.preview'),
                                        'iconClass' => 'fa fa-image',
                                    ],
                                ],
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <?php echo $view->render(
                            'MauticCoreBundle:Helper:publishstatus_icon.html.php',
                            [
                                'item'  => $item,
                                'model' => 'asset.asset',
                            ]
                        ); ?>
                    </td>
                    <td class="table-description">
                        <div>
                            <a href="<?php echo $view['router']->path(
                                'le_asset_action',
                                ['objectAction' => 'edit', 'objectId' => $item->getId()]
                            ); ?>"
                               data-toggle="ajax">
                                <?php echo $item->getTitle(); ?> (<?php echo $item->getAlias(); ?>)
                            </a>
                            <i class="<?php echo $item->getIconClass(); ?>"></i>
                        </div>
                        <?php if ($description = $item->getDescription()): ?>
                            <div class="text-muted mt-4">
                                <small><?php echo $description; ?></small>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="">
                        <?php $category = $item->getCategory(); ?>
                        <?php $catName  = ($category) ? $category->getTitle() : $view['translator']->trans('mautic.core.form.uncategorized'); ?>
                        <?php $color    = ($category) ? '#'.$category->getColor() : 'inherit'; ?>
                        <span style="white-space: nowrap;"><span class="label label-default pa-4" style="border: 1px solid #d5d5d5; background: <?php echo $color; ?>;"> </span> <span><?php echo $catName; ?></span></span>
                    </td>
                    <td class="col-stats" style="text-align: centre;">
                        <span onclick="Le.copyAssetUrl(this)" id="assetUrlBtn" value="<?php echo $downloadurl; ?>" url="<?php echo $downloadurl; ?>"class="mt-xs label label-primary has-click-event clickable-stat">
                            Copy
                        </span>
                    </td>
                    <td class="col-stats" style="text-align: centre;">
                        <span class="mt-xs label label-primary has-click-event clickable-stat">
                            <?php echo $item->getDownloadCount(); ?>
                        </span>
                    </td>

                 <?php  if ($isAdmin): ?>
                    <td class=""><?php echo $item->getId(); ?></td>
                 <?php  endif; ?>
                    <td>
                        <?php $hasEditAccess = $security->hasEntityAccess($permissions['asset:assets:editown'], $permissions['asset:assets:editother'], $item->getCreatedBy());
                        $hasDeleteAccess     = $security->hasEntityAccess($permissions['asset:assets:deleteown'], $permissions['asset:assets:deleteother'], $item->getCreatedBy());
                        $hasCloneAccess      = $permissions['asset:assets:create']; ?>
                        <div style="position: relative;" class="fab-list-container">
                            <div class="md-fab-wrapper">
                                <div class="md-fab md-fab-toolbar md-fab-small md-fab-primary" id="mainClass-<?php echo $item->getId(); ?>" style="">
                                    <i class="material-icons" onclick="Le.showActionButtons('<?php echo $item->getId(); ?>')"></i>
                                    <div tabindex="0" class="md-fab-toolbar-actions toolbar-actions-<?php echo $item->getId(); ?>">
                                        <?php if ($hasEditAccess): ?>
                                            <a class="hidden-xs-sm -nospin" title="<?php echo $view['translator']->trans('mautic.core.form.edit'); ?>" href="<?php echo $view['router']->path('le_asset_action', ['objectAction' => 'edit', 'objectId' => $item->getId()]); ?>" data-toggle="ajax">
                                                <span><i class="material-icons md-color-white">  </i></span></a>
                                        <?php endif; ?>
                                        <?php if ($hasCloneAccess) : ?>
                                            <a class="hidden-xs hide" title="<?php echo $view['translator']->trans('mautic.core.form.clone'); ?>" href="<?php echo $view['router']->path('le_asset_action', ['objectId' => $item->getId(), 'objectAction' => 'clone']); ?>" data-toggle="ajax" data-uk-tooltip="">
                                                <i class="material-icons md-color-white">  </i> </a>
                                        <?php endif; ?>
                                        <?php if ($isAdmin) : ?>
                                        <a data-toggle="ajaxmodal" data-target="#leSharedModal"
                                           title="<?php echo $view['translator']->trans('mautic.core.form.preview'); ?>"
                                           href="<?php echo $view['router']->path('le_asset_action', ['objectId' => $item->getId(), 'objectAction' => 'preview']); ?>">
                                            <i class="material-icons md-color-white">  </i> </a>
                                        <?php endif; ?>
                                        <?php if ($hasDeleteAccess):?>
                                            <a data-toggle="confirmation" href="<?php echo $view['router']->path('le_asset_action', ['objectAction' => 'delete', 'objectId' => $item->getId()]); ?>" data-message="<?php echo $view->escape($view['translator']->trans('mautic.asset.asset.form.confirmdelete', ['%name%'=> $item->getName()])); ?>" data-confirm-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.delete')); ?>" data-confirm-callback="executeAction" title="<?php echo $view['translator']->trans('mautic.core.form.delete'); ?>" data-cancel-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.cancel')); ?>">
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
                'menuLinkId' => 'le_asset_index',
                'baseUrl'    => $view['router']->path('le_asset_index'),
                'sessionVar' => 'asset',
            ]
        ); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php', ['tip' => 'mautic.asset.noresults.tip']); ?>
<?php endif; ?>

<?php echo $view->render(
    'MauticCoreBundle:Helper:modal.html.php',
    [
        'id'     => 'AssetPreviewModal',
        'header' => false,
    ]
);
