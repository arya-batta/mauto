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
<a href="<?php echo $view['router']->generate('le_dripemail_index', ['search' => $searchString]); ?>" data-toggle="ajax">
    <span><?php echo $view['translator']->trans('mautic.core.search.more', ['%count%' => $remaining]); ?></span>
</a>
<?php else: ?>
<a href="<?php echo $view['router']->generate('le_dripemail_campaign_action', ['objectAction' => 'edit', 'objectId' => $drip->getId()]); ?>" data-toggle="ajax">
    <?php echo $drip->getName(); ?>
</a>
<?php endif; ?>