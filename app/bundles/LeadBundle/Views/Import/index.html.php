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
$view['slots']->set('leContent', $leContent);
$view['slots']->set('headerTitle', $view['translator']->trans('le.lead.import.list'));
$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions[$permissionBase.':create'],
            ],
            'routeBase' => 'import',
            'langVar'   => $translationBase,
            'query'     => [
                    'object' => $view['request']->getParameter('object'),
            ],
        ]
    )
);

?>
<div class="le-header-align"><h3><?php echo $view['translator']->trans('le.lead.import.header.index'); ?></h3></div>
<div class="info-box-holder">
</div>
<div class="panel panel-default bdr-t-wdh-0 mb-0 list-panel-padding mt-less-50" id="importpanel">
    <?php // todo
    // echo $view->render(
    //     'MauticCoreBundle:Helper:list_toolbar.html.php',
    //     [
    //         'searchValue'   => $searchValue,
    //         'searchHelp'    => 'le.lead.lead.help.searchcommands',
    //         'action'        => $currentRoute,
    //     ]
    // );
    ?>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>
