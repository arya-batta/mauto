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
    $view->extend('MauticSmsBundle:Sms:index.html.php');
}
$isAdmin    =$view['security']->isAdmin();
if (count($items)):

    ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered sms-list">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'routeBase'       => 'sms',
                        'templateButtons' => [
                            'delete' => $permissions['sms:smses:deleteown'] || $permissions['sms:smses:deleteother'],
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
                        'sessionVar' => 'sms',
                        'orderBy'    => 'e.name',
                        'text'       => 'mautic.core.name',
                        'class'      => 'col-sms-name m_width',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'sms',
                        'orderBy'    => 'c.title',
                        'text'       => 'mautic.core.category',
                        'class'      => 'col-page-category',
                    ]
                );

                ?>
                <th class="col-email-stats" style="color: #000000"><?php echo $view['translator']->trans('le.sms.sent_count'); ?></th>
                <th class="col-email-stats" style="color: #000000"><?php echo $view['translator']->trans('le.sms.click_count'); ?></th>
                <?php
                //<th class="visible-sm visible-md visible-lg col-sms-stats"><?php echo $view['translator']->trans('mautic.core.stats');?></th>

                <?php
                if ($isAdmin):
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'sms',
                        'orderBy'    => 'e.id',
                        'text'       => 'mautic.core.id',
                        'class'      => 'visible-md visible-lg col-sms-id',
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
            <?php
            /** @var \Mautic\SmsBundle\Entity\Sms $item */
            foreach ($items as $item):
                $type      = $item->getSmsType();
                $sentcount = 0;
                ?>
                <tr>
                    <td>
                        <?php
                        $edit = $view['security']->hasEntityAccess(
                            $permissions['sms:smses:editown'],
                            $permissions['sms:smses:editother'],
                            $item->getCreatedBy()
                        );
                        $customButtons = [
                            [
                                'attr' => [
                                    'data-toggle' => 'ajaxmodal',
                                    'data-target' => '#MauticSharedModal',
                                    'data-header' => $view['translator']->trans('mautic.sms.smses.header.preview'),
                                    'data-footer' => 'false',
                                    'href'        => $view['router']->path(
                                        'mautic_sms_action',
                                        ['objectId' => $item->getId(), 'objectAction' => 'preview']
                                    ),
                                ],
                                'btnText'   => $view['translator']->trans('mautic.sms.preview'),
                                'iconClass' => 'fa fa-share',
                            ],
                        ];
                        echo $view->render(
                            'MauticCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit' => $view['security']->hasEntityAccess(
                                        $permissions['sms:smses:editown'],
                                        $permissions['sms:smses:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone'  => $permissions['sms:smses:create'],
                                    'delete' => $view['security']->hasEntityAccess(
                                        $permissions['sms:smses:deleteown'],
                                        $permissions['sms:smses:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'routeBase'     => 'sms',
                                'customButtons' => $customButtons,
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <?php if ($type == 'template'): ?>
                            <?php echo $view->render(
                                'MauticCoreBundle:Helper:publishstatus_icon.html.php',
                                ['item' => $item, 'model' => 'sms']
                            ); ?>
                        <?php else: ?>
                            <i class="fa fa-fw fa-lg fa-toggle-on text-muted disabled"></i>
                        <?php endif; ?>
                    </td>
                    <td class="table-description">
                        <div>
                            <a href="<?php echo $view['router']->path(
                                'mautic_sms_action',
                                ['objectAction' => 'view', 'objectId' => $item->getId()]
                            ); ?>" data-toggle="ajax">
                                <?php echo $item->getName(); ?>
                                <?php if ($type == 'list'): ?>
                                    <span data-toggle="tooltip" title="<?php echo $view['translator']->trans('mautic.sms.icon_tooltip.list_sms'); ?>"><i class="fa fa-fw fa-list"></i></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </td>
                    <td class="visible-md visible-lg" style="text-align: center;">
                        <?php $category = $item->getCategory(); ?>
                        <?php $catName  = ($category) ? $category->getTitle() : $view['translator']->trans('mautic.core.form.uncategorized'); ?>
                        <?php $color    = ($category) ? '#'.$category->getColor() : 'inherit'; ?>
                        <span style="white-space: nowrap;"><span class="label label-default pa-4" style="   border: 1px solid #d5d5d5; background: <?php echo $color; ?>;"> </span> <span><?php echo $catName; ?></span></span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats">
                        <?php echo $sentcount = $model->getSentCount($item->getId()); ?>

                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats">
                        <?php echo $clickcount = $model->getClickCount($item->getId()); ?><br>
                        <?php $clickpercent    = ($sentcount == 0) ? 0 : ($clickcount / $sentcount) * 100;
                        echo '('.$clickpercent.'%)' ?>

                    </td>
                   <?php /** <td class="visible-sm visible-md visible-lg col-stats" style="text-align: start;"> ?>
                        <span class="mt-xs label label-primary has-click-event clickable-stat"
                              data-toggle="tooltip"
                              title="<?php echo $view['translator']->trans('le.channel.stat.leadcount.tooltip'); ?>">
                            <a style="color:#fff;" href="<?php echo $view['router']->path(
                                'mautic_contact_index',
                                ['search' => $view['translator']->trans('le.lead.lead.searchcommand.sms_sent').':'.$item->getId()]
                            ); ?>"><?php echo $view['translator']->trans(
                                    'mautic.sms.stat.sentcount',
                                    ['%count%' => $item->getSentCount(true)]
                                ); ?></a>
                        </span>
                    </td> */ ?>
                    <?php if ($isAdmin): ?>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                    <?php endif; ?>
                    <td>
                        <?php $hasEditAccess = $view['security']->hasEntityAccess($permissions['sms:smses:editown'], $permissions['sms:smses:editother'], $item->getCreatedBy());
                        $hasDeleteAccess     = $view['security']->hasEntityAccess($permissions['sms:smses:deleteown'], $permissions['sms:smses:deleteother'], $item->getCreatedBy());
                        $hasCloneAccess      = $permissions['sms:smses:create']; ?>
                        <div style="position: relative;" class="fab-list-container">
                            <div class="md-fab-wrapper">
                                <div class="md-fab md-fab-toolbar md-fab-small md-fab-primary" id="mainClass-<?php echo $item->getId(); ?>" style="">
                                    <i class="material-icons" onclick="Mautic.showActionButtons('<?php echo $item->getId(); ?>')"></i>
                                    <div tabindex="0" class="md-fab-toolbar-actions toolbar-actions-<?php echo $item->getId(); ?>">
                                        <?php if ($hasEditAccess): ?>
                                            <a class="hidden-xs-sm -nospin" title="<?php echo $view['translator']->trans('mautic.core.form.edit'); ?>" href="<?php echo $view['router']->path('mautic_sms_action', ['objectAction' => 'edit', 'objectId' => $item->getId()]); ?>" data-toggle="ajax">
                                                <span><i class="material-icons md-color-white">  </i></span></a>
                                        <?php endif; ?>
                                        <?php if ($hasCloneAccess) : ?>
                                            <a class="hidden-xs" title="<?php echo $view['translator']->trans('mautic.core.form.clone'); ?>" href="<?php echo $view['router']->path('mautic_sms_action', ['objectId' => $item->getId(), 'objectAction' => 'clone']); ?>" data-toggle="ajax" data-uk-tooltip="">
                                                <i class="material-icons md-color-white">  </i> </a>
                                        <?php endif; ?>
                                        <a data-toggle="ajaxmodal" data-target="#MauticSharedModal"
                                           data-header="<?php echo $item->getName() ?> Preview"
                                           title="<?php echo $view['translator']->trans('mautic.core.form.preview'); ?>"
                                           href="<?php echo $view['router']->path('mautic_sms_action', ['objectId' => $item->getId(), 'objectAction' => 'preview']); ?>">
                                            <i class="material-icons md-color-white">  </i> </a>
                                        <?php if ($hasDeleteAccess):?>
                                            <a data-toggle="confirmation" href="<?php echo $view['router']->path('mautic_sms_action', ['objectAction' => 'delete', 'objectId' => $item->getId()]); ?>" data-message="<?php echo $view->escape($view['translator']->trans('mautic.sms.form.confirmdelete', ['%name%'=> $item->getName()])); ?>" data-confirm-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.delete')); ?>" data-confirm-callback="executeAction" title="<?php echo $view['translator']->trans('mautic.core.form.delete'); ?>" data-cancel-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.cancel')); ?>">
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
                'baseUrl'    => $view['router']->path('mautic_sms_index'),
                'sessionVar' => 'sms',
            ]
        ); ?>
    </div>
<?php elseif (!$configured): ?>
    <?php echo $view->render(
        'MauticCoreBundle:Helper:noresults.html.php',
        ['header' => 'mautic.sms.disabled', 'message' => 'mautic.sms.enable.in.configuration']
    ); ?>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php', ['message' => 'mautic.sms.create.in.campaign.builder']); ?>
<?php endif; ?>
