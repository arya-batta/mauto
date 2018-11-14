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
    $view->extend('MauticLeadBundle:Lead:index.html.php');
}
?>

<div>
    <?php if (count($items)): ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered" id="leadTable">
                <thead>
                <tr>
                <?php
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                            'sessionVar' => 'lead',
                            'orderBy'    => 'l.lastname, l.firstname, l.company, l.email',
                            'text'       => 'mautic.core.type.lead',
                            'class'      => 'col-lead-name',
                        ]);
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'text'       => 'mautic.core.type.tags',
                    'class'      => 'col-lead-tags',
                ]);

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.score',
                    'text'       => 'mautic.core.type.score',
                    'class'      => 'col-lead-score',
                ]);

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.points',
                    'text'       => 'le.lead.points',
                    'class'      => 'visible-md visible-lg col-lead-points',
                ]);
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.last_active',
                    'text'       => 'le.lead.lastactive',
                    'class'      => 'col-lead-lastactive visible-md visible-lg',
                    'default'    => true,
                ]);

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.city, l.state',
                    'text'       => 'le.lead.lead.thead.location',
                    'class'      => 'col-lead-location visible-md visible-lg',
                ]); ?>
                </tr>
                </thead>
                <tbody>
        <?php foreach ($items as $item): ?>
            <?php /** @var \Mautic\LeadBundle\Entity\Lead $item */ ?>
            <?php $fields = $item->getFields(); ?>
            <td>
                <a href="<?php echo $view['router']->path('le_contact_action', ['objectAction' => 'view', 'objectId' => $item->getId()]); ?>" data-toggle="ajax">
                    <?php if (in_array($item->getId(), array_keys($noContactList)))  : ?>
                        <div class="pull-right label label-danger"><i class="fa fa-ban"> </i></div>
                    <?php endif; ?>
                    <div> <?php echo ($item->isAnonymous()) ? $view['translator']->trans($item->getPrimaryIdentifier()) : $item->getPrimaryIdentifier(); ?></div>
                    <div class="small"><?php echo $item->getSecondaryIdentifier(); ?></div>
                    <div><?php echo $fields['core']['email']['value']; ?></div>
                </a>
            </td>
            <td class="visible-md visible-lg">
                <?php $tags = $item->getTags(); ?>
                <?php foreach ($tags as $tag): ?>
                    <div class="label label-primary" style="margin-bottom: 2px;"><?php echo $tag->getTag(); ?></div>
                <?php endforeach; ?>
            </td>
            <td class="visible-md visible-lg" style="text-align:center;">
                <?php
                $score = (!empty($fields['core']['score']['value'])) ? $view['assets']->getLeadScoreIcon($fields['core']['score']['value']) : '';
                ?>
                <img src="<?php echo $score; ?>" style="max-height: 25px;" />

            </td>
            <td class="visible-md visible-lg text-center">
                <?php
                $color = $item->getColor();
                $style = !empty($color) ? ' style="background-color: '.$color.';"' : '';
                ?>
                <span class="label label-primary"><?php echo $item->getPoints(); ?></span>
            </td>
            <td class="visible-md visible-lg">
                <abbr title="<?php echo $view['date']->toFull($item->getLastActive()); ?>">
                    <?php echo $view['date']->toText($item->getLastActive()); ?>
                </abbr>
            </td>
            <td class="visible-md visible-lg">
                <?php
                $flag = (!empty($fields['core']['country'])) ? $view['assets']->getCountryFlag($fields['core']['country']['value']) : '';
                if (!empty($flag)):
                    ?>
                    <img src="<?php echo $flag; ?>" style="max-height: 24px;" class="mr-sm" />
                <?php
                endif;
                $location = [];
                if (!empty($fields['core']['city']['value'])):
                    $location[] = $fields['core']['city']['value'];
                endif;
                if (!empty($fields['core']['state']['value'])):
                    $location[] = $fields['core']['state']['value'];
                elseif (!empty($fields['core']['country']['value'])):
                    $location[] = $fields['core']['country']['value'];
                endif;
                echo implode(', ', $location);
                ?>
                <div class="clearfix"></div>
            </td>
            </tr>
        <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php', ['header' => 'le.lead.grid.noresults.header', 'message' => 'le.lead.grid.noresults.message']); ?>
        <div class="clearfix"></div>
    <?php endif; ?>
</div>
<?php if (count($items)): ?>
    <div class="panel-footer">
        <?php
        if (!isset($route)):
            $route = (isset($link)) ? $link : 'le_contact_index';
        endif;
        if (!isset($routeParameters)):
            $routeParameters = [];
        endif;
        if (isset($objectId)):
            $routeParameters['objectId'] = $objectId;
        endif;

        echo $view->render(
            'MauticCoreBundle:Helper:pagination.html.php',
            [
                'totalItems' => $totalItems,
                'page'       => $page,
                'limit'      => $limit,
                'baseUrl'    => $view['router']->path($route, $routeParameters),
                'tmpl'       => (!in_array($tmpl, ['grid', 'index'])) ? $tmpl : $indexMode,
                'sessionVar' => (isset($sessionVar)) ? $sessionVar : 'lead',
                'target'     => (!empty($target)) ? $target : '.page-list',
            ]
        );
        ?>
    </div>
<?php endif; ?>
 <!--For Card View in contact form-->
<!--<div class="row shuffle-grid">
    <?php
/*    foreach ($items as $item):
        echo $view->render(
            'MauticLeadBundle:Lead:grid_card.html.php',
            [
                'contact'       => $item,
                'noContactList' => (isset($noContactList)) ? $noContactList : [],
            ]
        );
    endforeach;
    */?>
</div>-->
