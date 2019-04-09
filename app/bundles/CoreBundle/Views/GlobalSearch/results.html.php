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
<?php if ($results):
    foreach ($results as $header => $result): ?>
        <?php if (isset($result['count'])) {
        $count = $result['count'];
        unset($result['count']);
    } ?>

    <li class="text-center notifi-title"><?php echo $header; ?>
        <?php if (!empty($count)): ?>
            <span class="badge badge-xs badge-success"><?php echo $count; ?></span>
        <?php endif; ?>
    </li>
    <li class="list-group">
        <?php foreach ($result as $r): ?>
            <?php echo $r; ?>
        <?php endforeach; ?>
    </li>
    <?php endforeach;
    elseif ($searchstr != ''):  ?>
        <li class="text-center notifi-title"><?php echo $view['translator']->trans('mautic.core.search.global.noresult.header'); ?>
            <span class="badge badge-xs badge-success"><?php echo '0'; ?></span>
        </li>
        <li class="list-group">
            <a class="list-group-item">
                <div class="media">
                    <div class="media-heading"><?php echo $view['translator']->trans('mautic.core.search.global.noresult'); ?></div>
                </div>
            </a>
        </li>
<?php endif; ?>
