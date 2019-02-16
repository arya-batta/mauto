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
    <a href="<?php echo $view['router']->generate('le_contact_index', ['search' => $searchString]); ?>" class="list-group-item" data-toggle="ajax">
        <small class="text-primary"><?php echo $view['translator']->trans('mautic.core.search.more', ['%count%' => $remaining]); ?></small>
    </a>
<?php else: ?>
    <a href="<?php echo $view['router']->generate('le_contact_action', ['objectAction' => 'view', 'objectId' => $lead->getId()]); ?>" class="list-group-item" data-toggle="ajax">
        <div class="media">
            <div class="media-heading"><?php echo $lead->getPrimaryIdentifier(true); ?></div>
        </div>
    </a>
<?php endif; ?>