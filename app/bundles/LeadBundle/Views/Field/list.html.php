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
    $view->extend('MauticLeadBundle:Field:index.html.php');
}
$isadmin    =$view['security']->isAdmin();
$hidepanel  = ($isadmin) ? '' : "style='display: none;'";
$hideid     = ($isadmin) ? '' : 'hide';
?>
<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered leadfield-list" id="leadFieldTable" class="overflow:auto">
            <thead>
            <tr>
                <th class="col-leadfield-orderhandle"></th>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#leadFieldTable',
                        'langVar'         => 'lead.field',
                        'routeBase'       => 'contactfield',
                        'templateButtons' => [
                            'delete' => $permissions['lead:fields:full'],
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
                ?>

                <th class="col-leadfield-label"><?php echo $view['translator']->trans('mautic.lead.field.label'); ?></th>
                <th class="visible-md visible-lg col-leadfield-alias"><?php echo $view['translator']->trans('mautic.core.alias'); ?></th>
                <th class="hide"><?php echo $view['translator']->trans('mautic.lead.field.group'); ?></th>
                <th class="col-leadfield-type"><?php echo $view['translator']->trans('mautic.lead.field.type'); ?></th>
                <?php if ($isadmin): ?>
                <th class="visible-md visible-lg col-leadfield-id"><?php echo $view['translator']->trans('mautic.core.id'); ?></th>
                <?php endif; ?>
                <th class="visible-sm visible-md visible-lg col-leadfield-statusicons"></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr id="field_<?php echo $item->getId(); ?>">
                    <td><i class="fa fa-fw fa-ellipsis-v"></i></td>
                    <td>
                        <?php
                        echo $view->render(
                            'MauticCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit'   => true,
                                    'clone'  => true,
                                    'delete' => $item->isFixed() ? false : true,
                                ],
                                'routeBase' => 'contactfield',
                                'langVar'   => 'lead.field',
                                'pull'      => 'left',
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <?php echo $view->render(
                            'MauticCoreBundle:Helper:publishstatus_icon.html.php',
                            ['item' => $item, 'model' => 'lead.field', 'disableToggle' => ($isadmin) ? ($item->getAlias() == 'email') : ($item->getIsFixed() == 1)]
                        ); ?>
                    </td>
                    <td>
                    <span class="ellipsis">

                        <a href="<?php echo $view['router']->path(
                            'mautic_contactfield_action',
                            ['objectAction' => 'edit', 'objectId' => $item->getId()]
                        ); ?>"><?php echo $item->getLabel(); ?></a>
                    </span>
                    </td>
                    <td class="visible-md visible-lg" <?php echo $hidepanel; ?>><?php echo $item->getAlias(); ?></td>
                    <td class="hide"><?php echo $view['translator']->trans('mautic.lead.field.group.'.$item->getGroup()); ?></td>
                    <td><?php echo $view['translator']->transConditional(
                            'mautic.core.type.'.$item->getType(),
                            'mautic.lead.field.type.'.$item->getType()
                        ); ?></td>
                    <?php if ($isadmin): ?>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                    <?php endif; ?>
                    <td class="visible-sm visible-md visible-lg">
                        <?php if ($item->isRequired()): ?>
                            <i class="fa fa-asterisk" data-toggle="tooltip" data-placement="left" title="<?php echo $view['translator']->trans(
                                'mautic.lead.field.tooltip.required'
                            ); ?>"></i>
                        <?php endif; ?>
                        <?php if (!$item->isVisible()): ?>
                            <i class="fa fa-eye-slash" data-toggle="tooltip" data-placement="left" title="<?php echo $view['translator']->trans(
                                'mautic.lead.field.tooltip.invisible'
                            ); ?>"></i>
                        <?php endif; ?>
                        <?php if ($item->isFixed()): ?>
                            <i class="fa fa-lock" data-toggle="tooltip" data-placement="left" title="<?php echo $view['translator']->trans(
                                'mautic.lead.field.tooltip.fixed'
                            ); ?>"></i>
                        <?php endif; ?>
                        <?php if ($item->isListable()): ?>
                            <i class="fa fa-list " data-toggle="tooltip" data-placement="left" title="<?php echo $view['translator']->trans(
                                'mautic.lead.field.tooltip.listable'
                            ); ?>"></i>
                        <?php endif; ?>
                        <?php if ($item->isPubliclyUpdatable()): ?>
                            <i class="fa fa-globe text-danger " data-toggle="tooltip" data-placement="left" title="<?php echo $view['translator']->trans(
                                'mautic.lead.field.tooltip.public'
                            ); ?>"></i>
                        <?php endif; ?>

                        <?php if ($item->isUniqueIdentifer()): ?>
                            <i class="fa fa-key " data-toggle="tooltip" data-placement="left" title="<?php echo $view['translator']->trans(
                                'mautic.lead.field.tooltip.isuniqueidentifer'
                            ); ?>"></i>
                        <?php endif; ?>
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
                'baseUrl'    => $view['router']->path('mautic_contactfield_index'),
                'sessionVar' => 'leadfield',
            ]
        ); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
