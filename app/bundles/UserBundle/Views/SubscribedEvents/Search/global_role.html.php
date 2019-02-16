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
    <a href="<?php echo $view['router']->generate('le_role_index', ['filter-user' => $searchString]); ?>" class="list-group-item" data-toggle="ajax">
        <small class="text-primary"><?php echo $view['translator']->trans('mautic.core.search.more', ['%count%' => $remaining]); ?></small>
    </a>
<?php else: ?>
    <?php if ($canEdit): ?>
        <a href="<?php echo $view['router']->generate('le_role_action', ['objectAction' => 'edit', 'objectId' => $role->getId()]); ?>" class="list-group-item" data-toggle="ajax">
            <div class="media">
                <div class="media-heading"> <?php echo $role->getName(true); ?></div>
            </div>
        </a>
    <?php else: ?>
        <a href="javascript:void(0);" class="list-group-item">
            <div class="media">
                <div class="media-heading"> <?php echo $role->getName(true); ?></div>
            </div>
        </a>
    <?php endif; ?>
<?php endif; ?>