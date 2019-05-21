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
    $view->extend('MauticUserBundle:User:index.html.php');
endif;
$isAdmin=$view['security']->isAdmin();
?>
<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered user-list" id="userTable">
        <thead>
        <tr>
            <?php
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'checkall'        => 'true',
                    'target'          => '#userTable',
                    'langVar'         => 'user.user',
                    'routeBase'       => 'user',
                    'templateButtons' => [
                        'delete' => $permissions['delete'],
                    ],
                ]
            );
            ?>

            <?php
            if ($isAdmin) {
                echo '<th class="visible-md visible-lg col-user-avatar"></th>';
            } else {
                echo '<!-- <th class="visible-md visible-lg col-user-avatar"></th> -->';
            }
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'sessionVar' => 'user',
                    'orderBy'    => 'u.username',
                    'text'       => 'mautic.core.forms.published',
                    'class'      => 'col-user-username',
                ]
            );
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'sessionVar' => 'user',
                    'orderBy'    => 'u.lastName, u.firstName, u.username',
                    'text'       => 'mautic.core.name',
                    'class'      => 'col-user-name',
                    'default'    => true,
                ]
            );
            if ($isAdmin):
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'sessionVar' => 'user',
                    'orderBy'    => 'u.username',
                    'text'       => 'mautic.core.username',
                    'class'      => 'col-user-username',
                ]
            );
            endif;
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'sessionVar' => 'user',
                    'orderBy'    => 'u.email',
                    'text'       => 'mautic.core.type.email',
                    'class'      => 'col-user-email',
                ]
            );
            if ($isAdmin):
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'sessionVar' => 'user',
                    'orderBy'    => 'u.mobile',
                    'text'       => 'mautic.core.type.mobile',
                    'class'      => 'col-user-mobile',
                ]
            );
            endif;
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'sessionVar' => 'user',
                    'orderBy'    => 'r.name',
                    'text'       => 'mautic.user.role',
                    'class'      => 'col-user-role',
                    'tooltip'    => 'le.users.table.header.role',
                ]
            );
            if ($isAdmin):
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'sessionVar' => 'user',
                    'orderBy'    => 'u.id',
                    'text'       => 'mautic.core.id',
                    'class'      => 'col-user-id',
                ]
            );
            endif;
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
                                'edit'   => $permissions['edit'],
                                'delete' => $permissions['delete'],
                            ],
                            'routeBase' => 'user',
                            'langVar'   => 'user.user',
                            'pull'      => 'left',
                        ]
                    );
                    ?>
                </td>
                <?php if ($isAdmin) : ?>
                    <td class="visible-md visible-lg">
                        <img class="img img-responsive img-thumbnail w-44" src="<?php echo $view['gravatar']->getImage($item->getEmail(), '50'); ?>"/>
                    </td>
                <?php endif; ?>
                <td>
                    <?php echo $view->render(
                        'MauticCoreBundle:Helper:publishstatus_icon.html.php',
                        [
                            'item'  => $item,
                            'model' => 'user.user',
                        ]
                    ); ?>
                </td>
                <td>
                    <div>
                        <?php if ($permissions['edit']) : ?>
                            <a href="<?php echo $view['router']->path(
                                'le_user_action',
                                ['objectAction' => 'edit', 'objectId' => $item->getId()]
                            ); ?>" data-toggle="ajax">
                                <?php echo $item->getName(true); ?>
                            </a>
                        <?php else : ?>
                            <?php echo $item->getName(true); ?>
                        <?php endif; ?>
                    </div>
                    <div class="small"><em><?php echo $item->getPosition(); ?></em></div>
                </td>
                <?php if ($isAdmin) : ?>
                <td><?php echo $item->getUsername(); ?></td>
                <?php endif; ?>
                <td class="">
                    <a href="mailto: <?php echo $item->getEmail(); ?>"><?php echo $item->getEmail(); ?></a>
                </td>
                <?php if ($isAdmin) : ?>
                <td class=""><?php echo $item->getMobile(); ?></td>
                <?php endif; ?>
                <td class=""><?php echo $item->getRole()->getName(); ?></td>
                <?php if ($isAdmin) : ?>
                <td class=""><?php echo $item->getId(); ?></td>
                <?php endif; ?>
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
                'baseUrl'    => $view['router']->path('le_user_index'),
                'sessionVar' => 'user',
            ]
        ); ?>
    </div>
</div>
