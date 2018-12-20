<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
//Check to see if the entire page should be displayed or just main content
if ($tmpl == 'index'):
    $view->extend('MauticLeadBundle:Tag:index.html.php');
endif;
$listCommand = $view['translator']->trans('le.lead.lead.searchcommand.tag');
$isAdmin     =$view['security']->isAdmin();
?>

<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered" id="leadTagTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#leadTagTable',
                        'langVar'         => 'lead.tag',
                        'routeBase'       => 'tags',
                        'templateButtons' => [
                            'delete' => $permissions['lead:tags:full'],
                        ],
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'tags',
                        'orderBy'    => '',
                        'text'       => 'mautic.core.update.heading.status',
                        'class'      => 'col-status-name',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'tags',
                        'orderBy'    => 't.tag',
                        'text'       => 'mautic.core.name',
                        'class'      => 'col-leadlist-name',
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'tags',
                        'text'       => 'le.lead.list.thead.leadcount',
                        'class'      => 'visible-md visible-lg col-leadlist-leadcount text-center',
                    ]
                );
                if ($isAdmin):
                    echo $view->render(
                        'MauticCoreBundle:Helper:tableheader.html.php',
                        [
                            'sessionVar' => 'tags',
                            'orderBy'    => 't.id',
                            'text'       => 'mautic.core.id',
                            'class'      => 'visible-md visible-lg col-leadlist-id',
                        ]
                    );
                endif;
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
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
                                'source'          => "tag",
                                'templateButtons' => [
                                    'delete' =>true,// $view['security']->hasEntityAccess(true, $permissions['lead:tags:full'], $item->getCreatedBy()),
                                ],
                                'routeBase' => 'tags',
                                'langVar'   => 'lead.tag',
                                'custom'    => [
                                    [
                                        'attr' => [
                                            'data-toggle' => 'ajax',
                                            'href'        => $view['router']->path(
                                                'le_tags_index',
                                                [
                                                    'search' => "$listCommand:{$item->getId()}",
                                                ]
                                            ),
                                        ],
                                        'icon'  => 'fa-users',
                                        'label' => 'le.lead.list.view_leads',
                                    ],
                                ],
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <?php echo $view->render(
                            'MauticCoreBundle:Helper:publishstatus_icon.html.php',
                            ['item' => $item, 'model' => 'lead.tag']
                        );
                        //file_put_contents('/var/www/log1.txt',json_encode($items))?>
                    </td>
                    <td class="table-description" style="width:70%;">
                        <div>
                            <a href="<?php echo $view['router']->path(
                                'le_tags_action',
                                ['objectAction' => 'edit', 'objectId' => $item->getId()]
                            ); ?>" data-toggle="ajaxmodal"
                               data-target = "#leSharedModal"
                               data-header="<?php echo $view['translator']->trans('le.lead.tags.header.edit')?>">
                                    <?php echo $item->getTag();?>
                            </a>
                        </div>
                    </td>
                    <td class="visible-md visible-lg text-center" style="width: 30%;">
                        <a class="label label-primary" href="<?php echo $view['router']->path(
                            'le_contact_index',
                            ['search' => $view['translator']->trans('le.lead.lead.searchcommand.tag').':'.$item->getTag()]
                        ); ?>" data-toggle="ajax"<?php  echo ($leadCounts[$item->getId()] == 0) ? 'disabled=disabled' : ''; ?>>
                            <?php echo $view['translator']->transChoice(
                                'le.lead.list.viewleads_count',
                                $leadCounts[$item->getId()],
                                ['%count%' => $leadCounts[$item->getId()]]
                            ); ?>
                        </a>
                    </td>
                    <?php  if ($isAdmin):?>
                        <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                    <?php  endif; ?>
                    <td style="width: 30%;">
                        <?php $hasEditAccess   = $view['security']->hasEntityAccess(true, $permissions['lead:tags:full'],$item->getId());
                        $hasDeleteAccess = $view['security']->hasEntityAccess(true, $permissions['lead:tags:full'],$item->getId());?>
                        <div style="position: relative;" class="fab-list-container">
                            <div class="md-fab-wrapper">
                                <div class="md-fab md-fab-toolbar md-fab-small md-fab-primary" id="mainClass-<?php echo $item->getId(); ?>" style="">
                                    <i class="material-icons" onclick="Le.showActionButtons('<?php echo $item->getId(); ?>')"></i>
                                    <div tabindex="0" class="md-fab-toolbar-actions toolbar-actions-<?php echo $item->getId(); ?>">
                                        <?php if ($hasEditAccess): ?>
                                            <a href="<?php echo $view['router']->path(
                                                'le_tags_action',
                                                ['objectAction' => 'edit', 'objectId' => $item->getId()]
                                            ); ?>" data-toggle="ajaxmodal"
                                               data-target = "#leSharedModal"
                                               data-header="<?php echo $view['translator']->trans('le.lead.tags.header.edit')?>">
                                                <span><i class="material-icons md-color-white">  </i></span></a>
                                        <?php endif; ?>
                                        <?php if ($hasDeleteAccess):?>
                                            <a data-toggle="confirmation" href="<?php echo $view['router']->path('le_tags_action', ['objectAction' => 'delete', 'objectId' => $item->getId()]); ?>" data-message="<?php echo $view->escape($view['translator']->trans('le.lead.tags.form.confirmdelete', ['%name%'=> $item->getTag()])); ?>" data-confirm-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.delete')); ?>" data-confirm-callback="executeAction" title="<?php echo $view['translator']->trans('mautic.core.form.delete'); ?>" data-cancel-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.cancel')); ?>">
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
                    'totalItems' => count($items),
                    'page'       => $page,
                    'limit'      => $limit,
                    'baseUrl'    => $view['router']->path('le_tags_index'),
                    'sessionVar' => 'segment',
                ]
            ); ?>
        </div>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
