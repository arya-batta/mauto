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
    <a href="<?php echo $view['router']->generate('le_page_index', ['search' => $searchString]); ?>" class="list-group-item" data-toggle="ajax">
        <small class="text-primary"><?php echo $view['router']->generate('le_page_index', ['search' => $searchString]); ?></small>
    </a>
<?php else: ?>
    <a href="<?php echo $view['router']->generate('le_page_action', ['objectAction' => 'view', 'objectId' => $page->getId()]); ?>" onclick="Le.closeGlobalSearchResults();" class="list-group-item" data-toggle="ajax">
        <div class="media">
            <div class="media-heading"><?php echo $page->getTitle(); ?></div>
        </div>
    </a>
<?php endif; ?>