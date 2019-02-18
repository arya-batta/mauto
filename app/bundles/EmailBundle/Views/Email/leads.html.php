<?php

/*
 * @copyright   2019 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$permissions = $security->isGranted(
    [
        'lead:leads:viewown',
        'lead:leads:viewother',
        'lead:leads:create',
        'lead:leads:editown',
        'lead:leads:editother',
        'lead:leads:deleteown',
        'lead:leads:deleteother',
        'lead:imports:view',
        'lead:imports:create',
    ],
    'RETURN_ARRAY'
);
?>
<br>
<div class="table-responsive email_stats_box">
    <table class="table table-hover <?php echo count($items) ? 'table-striped' : ''?> table-bordered" id="leadTable">
        <thead>
        <tr>
            <?php
            echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                'text'       => 'mautic.core.type.name',
                'class'      => 'col-lead-name',
            ]);
            echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                'text'       => 'mautic.core.type.email',
                'class'      => 'col-lead-email',
            ]);

            echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                'text'       => 'mautic.core.type.score',
                'class'      => 'col-lead-score text-center',
            ]);

            echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                'text'       => 'le.lead.points',
                'class'      => 'visible-md visible-lg text-center col-lead-points',
            ]);
            echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                'text'       => 'le.lead.lastactive',
                'class'      => 'col-lead-lastactive visible-md visible-lg',
                'default'    => true,
            ]);

            echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                'text'       => 'le.lead.lead.thead.location',
                'class'      => 'col-lead-location visible-md visible-lg',
            ]);
            ?>
        </tr>
        </thead>
        <?php if (count($items)): ?>
        <tbody>
        <?php echo $view->render('MauticEmailBundle:Email:list_rows.html.php', [
            'items'         => $items,
            'security'      => $security,
            'currentList'   => [],
            'permissions'   => $permissions,
            'noContactList' => [],
        ]); ?>
        </tbody>
        <?php else: ?>
            <?php echo $view->render('MauticEmailBundle:Email:noresults.html.php', ['tip' => 'mautic.form.noresults.tip', 'colspan' => '6']); ?>
        <?php endif; ?>
    </table>
</div>
<br>