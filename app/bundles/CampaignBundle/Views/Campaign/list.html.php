<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$view['slots']->set('headerTitle', $view['translator']->trans('mautic.campaign.campaigns'));
if ($tmpl == 'index') {
    $view->extend('MauticCoreBundle:Standard:index.html.php');
}
$isAdmin=$view['security']->isAdmin();
?>
<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered campaign-list" id="campaignTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#campaignTable',
                        'routeBase'       => 'campaign',
                        'templateButtons' => [
                            'delete' => $permissions['campaign:campaigns:delete'],
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
                        'sessionVar' => 'campaign',
                        'orderBy'    => 'c.name',
                        'text'       => 'mautic.core.name',
                        'class'      => 'col-campaign-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'campaign',
                        'orderBy'    => 'cat.title',
                        'text'       => 'mautic.core.category',
                        'class'      => 'col-campaign-category',
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'workflow',
                        'orderBy'    => '',
                        'text'       => 'le.workflow.stats.progress',
                        'class'      => 'col-workflow-stats',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'workflow',
                        'orderBy'    => '',
                        'text'       => 'le.workflow.stats.completed',
                        'class'      => 'col-workflow-stats',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'workflow',
                        'orderBy'    => '',
                        'text'       => 'le.workflow.stats.goal.achieved',
                        'class'      => 'col-workflow-stats',
                        'default'    => true,
                    ]
                );
                if ($isAdmin):
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'campaign',
                        'orderBy'    => 'c.id',
                        'text'       => 'mautic.core.id',
                        'class'      => 'col-campaign-id',
                    ]
                );
                endif;
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'campaign',
                    'orderBy'    => '',
                    'text'       => 'mautic.core.actions',
                    'class'      => 'col-lead-location col-lead-actions',
                ]);
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
            <?php $mauticTemplateVars['item'] = $item; ?>
                <tr class="wf-row-stats" data-stats="<?php echo $item->getId(); ?>">
                    <td>
                        <?php
                        echo $view->render(
                            'MauticCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit'   => $permissions['campaign:campaigns:edit'],
                                    'clone'  => $permissions['campaign:campaigns:create'],
                                    'delete' => $permissions['campaign:campaigns:delete'],
                                ],
                                'routeBase' => 'campaign',
                            ]
                        );
                        ?>
                    </td>
                   <td>
                       <?php echo $view->render(
                           'MauticCoreBundle:Helper:publishstatus_icon.html.php',
                           [
                               'item'  => $item,
                               'model' => 'campaign',
                           ]
                       ); ?>
                   </td>
                    <td class="table-description">
                        <div>
                            <a href="<?php echo $view['router']->path(
                                'le_campaign_action',
                                ['objectAction' => 'edit', 'objectId' => $item->getId()]
                            ); ?>" data-toggle="ajax">
                                <?php echo $item->getName(); ?>
                            <?php echo $view['content']->getCustomContent('campaign.name', $mauticTemplateVars); ?>
                            </a>
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
                    <td class="col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs"
                            id="wf-progress-<?php echo $item->getId(); ?>">
                            <a href="<?php echo $view['router']->path(
                                'le_contact_index',
                                ['search' => $view['translator']->trans('le.lead.campaign.searchcommand.wf-progress').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('le.workflow.stats.progress.tooltip'); ?>">
                                <div class="wf-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs"
                            id="wf-completed-<?php echo $item->getId(); ?>">
                            <a href="<?php echo $view['router']->path(
                                'le_contact_index',
                                ['search' => $view['translator']->trans('le.lead.campaign.searchcommand.wf-completed').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('le.workflow.stats.completed.tooltip'); ?>">
                                <div class="wf-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs"
                            id="wf-goal-<?php echo $item->getId(); ?>">
                            <a href="<?php echo $view['router']->path(
                                'le_contact_index',
                                ['search' => $view['translator']->trans('le.lead.campaign.searchcommand.wf-goal').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('le.workflow.stats.goal.tooltip'); ?>">
                                <div class="wf-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <?php if ($isAdmin): ?>
                    <td class=""><?php echo $item->getId(); ?></td>
                    <?php endif; ?>
                    <td>
                        <?php $hasEditAccess = $permissions['campaign:campaigns:edit'];
                        $hasDeleteAccess     = $permissions['campaign:campaigns:delete'];
                        $hasCloneAccess      = $permissions['campaign:campaigns:create']; ?>
                        <div style="position: relative;" class="fab-list-container">
                            <div class="md-fab-wrapper">
                                <div class="md-fab md-fab-toolbar md-fab-small md-fab-primary" id="mainClass-<?php echo $item->getId(); ?>" style="">
                                    <i class="material-icons" onclick="Le.showActionButtons('<?php echo $item->getId(); ?>')"></i>
                                    <div tabindex="0" class="md-fab-toolbar-actions toolbar-actions-<?php echo $item->getId(); ?>">
                                        <?php if ($hasEditAccess): ?>
                                            <a class="hidden-xs-sm -nospin" title="<?php echo $view['translator']->trans('mautic.core.form.edit'); ?>" href="<?php echo $view['router']->path('le_campaign_action', ['objectAction' => 'edit', 'objectId' => $item->getId()]); ?>" data-toggle="ajax">
                                                <span><i class="material-icons md-color-white">  </i></span></a>
                                        <?php endif; ?>
                                        <?php if ($hasCloneAccess) : ?>
                                            <a class="hidden-xs" title="<?php echo $view['translator']->trans('mautic.core.form.clone'); ?>" href="<?php echo $view['router']->path('le_campaign_action', ['objectId' => $item->getId(), 'objectAction' => 'clone']); ?>" data-toggle="ajax" data-uk-tooltip="">
                                                <i class="material-icons md-color-white">  </i> </a>
                                        <?php endif; ?>
                                        <?php if ($hasDeleteAccess):?>
                                            <a data-toggle="confirmation" href="<?php echo $view['router']->path('le_campaign_action', ['objectAction' => 'delete', 'objectId' => $item->getId()]); ?>" data-message="<?php echo $view->escape($view['translator']->trans('mautic.campaign.form.confirmdelete', ['%name%'=> $item->getName()])); ?>" data-confirm-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.delete')); ?>" data-confirm-callback="executeAction" title="<?php echo $view['translator']->trans('mautic.core.form.delete'); ?>" data-cancel-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.cancel')); ?>">
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
                'menuLinkId' => 'le_campaign_index',
                'baseUrl'    => $view['router']->path('le_campaign_index'),
                'sessionVar' => 'campaign',
            ]
        ); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php', ['tip' => 'mautic.campaign.noresults.tip']); ?>
<?php endif; ?>
