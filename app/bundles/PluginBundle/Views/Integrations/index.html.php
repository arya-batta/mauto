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
$view['slots']->set('leContent', 'integration');
$header = $view['translator']->trans('le.integrations.menu.name');
$view['slots']->set('headerTitle', $header);
?>
<div class="bdr-t-wdh-0 mb-0 list-panel-padding">
        <?php foreach ($integrations as $key => $integration): ?>
        <div class="col-md-3">
            <div class="panel panel-success integration-tile">
                <a data-integration="<?php echo $key ?>" href="<?php echo $integration['route']?>">
                    <img alt="<?php echo $integration['name']?>" class="integration_tile_image" src="<?php echo $integration['image_url']?>">
                    <span class="integration_tile_text"><?php echo $integration['name']?></span>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
</div>


