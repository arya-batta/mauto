<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('leContent', 'user');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.user.users'));

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['create'],
            ],
            'routeBase' => 'user',
            'langVar'   => 'user.user',
        ]
    )
);
?>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('mautic.user.users'); ?></h3></div>
<?php echo $view->render(
    'MauticCoreBundle:Helper:list_toolbar.html.php',
    [
        'searchValue' => $searchValue,
        'searchHelp'  => 'mautic.user.user.help.searchcommands',
        'action'      => $currentRoute,
    ]
); ?>

<div class="page-list">
    <?php $view['slots']->output('_content'); ?>
</div>
