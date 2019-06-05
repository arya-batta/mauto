<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');

$view['slots']->set('leContent', 'config');
$view['slots']->set('headerTitle', $view['translator']->trans('le.config.setupmenu.header.title'));
?>
<div class="settings-menu-wrapper">
    <div class="row settings-menu-container">
        <?php foreach ($settingsMenu as $groupHeader => $groupvalues):?>
        <div class="settings-menu-group">
            <div class="settings-menu-group-header"><?php echo $groupHeader; ?></div>
            <div class="settings-menu-group-container">
                <?php foreach ($groupvalues as $listHeader =>$listItem):?>
                    <?php if ($isTrialAccount && ($listHeader == 'Sending Domain' || $listHeader == 'Sender Reputation')) {
    continue;
} ?>
                <div class="settings-menu-list-item">
                    <a href="<?php echo $listItem['url']; ?>" data-toggle="ajax" class="settings-menu-list-item-link">
                        <div class="settings-menu-list-item-icon">
                            <img src="<?php echo $view['assets']->getUrl('media/images/settings/'.$listItem['img']); ?>" />
                        </div>
                        <span class="settings-menu-list-item-name"><?php echo $listItem['name']; ?></span>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>