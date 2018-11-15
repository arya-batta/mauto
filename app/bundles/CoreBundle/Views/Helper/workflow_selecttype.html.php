<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$index = 0;
?>

<div class="<?php echo $typePrefix; ?>-type-modal-backdrop" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #000000; opacity: 0.9; z-index: 9000"></div>

<div class="modal fade in <?php echo $typePrefix; ?>-type-modal" style="display: block; z-index: 9999;">
    <div class="modal-dialog-workflow">
        <div class="modal-content-workflow">
            <div class="modal-header">
                <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($cancelUrl); ?>');" class="close" ><span aria-hidden="true">&times;</span></a>
                <h4 class="modal-title">
                    <?php echo $view['translator']->trans($template); ?>
                </h4>
                <div class="modal-loading-bar"></div>
            </div>
            <div class="modal-body form-select-modal">
                <div class="row blankTemplateRow">
                    <div class="col-md-6">
                        <div class="panel panel-primary-workflow">
                            <div class="panel-heading" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($actionRoute, ['objectAction' => 'new', 'objectId' => 'blank']) ?>');">
                                <div class="col-xs-8 col-sm-10 np heading-workflow">
                                    <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($actionRoute, ['objectAction' => 'new', 'objectId' => 'blank']) ?>');" class="panel-title panel-title-workflow-blank" ><?php echo $view['translator']->trans($blanktemplate); ?></a>
                                </div>
                                <div class="heading-workflow-img">
                                    <i class="material-icons material-color-change">&#xE335;</i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-header-note">
                    <h4 class="modal-title-note">
                        <?php echo $view['translator']->trans($header); ?>
                    </h4>
                </div>
                <br>
                <?php foreach ($Campaigns as $item): $index++?>
                    <?php if ($index == 1): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel panel-primary-workflow">
                                    <div class="panel-heading" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($actionRoute, ['objectAction' => 'clone', 'objectId' => $item->getId()]) ?>');">
                                        <div class="col-xs-8 col-sm-10 np heading-workflow">
                                            <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($actionRoute, ['objectAction' => 'clone', 'objectId' => $item->getId()]) ?>');" class="panel-title panel-title-workflow"><?php echo $item->getName() ?></a>

                                        </div>
                                        <div class="heading-workflow-img">
                                            <i class="material-icons">&#xE335;</i>
                                        </div>

                                    </div>
                                </div>
                            </div>
                    <?php else: ?>
                        <div class="col-md-6">
                            <div class="panel panel-primary-workflow">
                                <div class="panel-heading" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($actionRoute, ['objectAction' => 'clone', 'objectId' => $item->getId()]) ?>');">
                                    <div class="col-xs-8 col-sm-10 np heading-workflow">
                                        <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($actionRoute, ['objectAction' => 'clone', 'objectId' => $item->getId()]) ?>');" class="panel-title panel-title-workflow"><?php echo $item->getName() ?></a>

                                    </div>
                                    <div class="heading-workflow-img">
                                        <i class="material-icons">&#xE335;</i>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</div>
</div>