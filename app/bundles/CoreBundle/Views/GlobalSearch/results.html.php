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
<?php foreach ($results as $header => $result): ?>
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
<?php endforeach; ?>