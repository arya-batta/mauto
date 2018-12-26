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
    <a href="<?php echo $view['router']->generate('le_segment_index', ['search' => $searchString]); ?>" data-toggle="ajax">
        <span><?php echo $view['translator']->trans('mautic.core.search.more', ['%count%' => $remaining]); ?></span>
    </a>
<?php else: ?>
    <a href="<?php echo $view['router']->generate('le_segment_action', ['objectAction' => 'view', 'objectId' => $list->getId()]); ?>" data-toggle="ajax">
        <span><?php echo $list->getName(); ?></span>
    </a>
    <div class="clearfix"></div>
<?php endif; ?>