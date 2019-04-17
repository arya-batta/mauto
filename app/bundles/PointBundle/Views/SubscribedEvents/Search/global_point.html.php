<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<?php if (!empty($showMore)): ?>
    <a href="<?php echo $view['router']->generate('le_point_index', ['search' => $searchString]); ?>" class="list-group-item" data-toggle="ajax">
        <small class="text-primary"><?php echo $view['translator']->trans('mautic.core.search.more', ['%count%' => $remaining]); ?></small>
    </a>
</div>
<?php else: ?>
    <?php if ($canEdit): ?>
        <a href="<?php echo $view['router']->generate('le_point_action', ['objectAction' => 'edit', 'objectId' => $item->getId()]); ?>" onclick="Le.closeGlobalSearchResults();" class="list-group-item" data-toggle="ajax">
            <div class="media">
                <div class="media-heading"> <?php echo $item->getName(); ?></div>
            </div>
        </a>
    <?php else: ?>
        <a href="javascript:void(0);" class="list-group-item">
            <div class="media">
                <div class="media-heading"> <?php echo $item->getName(); ?></div>
            </div>
        </a>
    <?php endif; ?>
<?php endif; ?>