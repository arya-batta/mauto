<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<?php
$hideclass  = '';
$modalwidth = '64%';
$formwidth  = '50%';
if (!isset($typeThreeIconClass)) {
    $hideclass  = 'hide';
    $modalwidth = '';
    $formwidth  = '';
}
?>
<script>
    <?php foreach ($mauticLang as $key => $string): ?>
    mauticLang.<?php echo $key; ?> = "<?php echo $view['translator']->trans($string); ?>";
    <?php endforeach; ?>
</script>
<div class="<?php echo $typePrefix; ?>-type-modal-backdrop" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #000000; opacity: 0.9; z-index: 9000"></div>

<div class="modal fade in <?php echo $typePrefix; ?>-type-modal" style="display: block; z-index: 9999;">
    <div class="modal-dialog" style="width: <?php echo $modalwidth ?>">
        <div class="modal-content">
            <div class="modal-header">
                <a href="javascript: void(0);" onclick="Mautic.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($cancelUrl); ?>');" class="close" ><span aria-hidden="true">&times;</span></a>
                <h4 class="modal-title">
                    <?php echo $view['translator']->trans($header); ?>
                </h4>
                <div class="modal-loading-bar"></div>
            </div>
            <div class="modal-body form-select-modal" style="background-color: #eee;">
                <div class="row">
                    <div class="white-block" style="margin-left:15px;width:94%;">
                        <div class="col-md-6" style="width:<?php echo $formwidth ?>">
                            <div class="panel-primary">
                                <h3 class="panel-title le-email-editor-header"><?php echo $view['translator']->trans($typeTwoHeader); ?></h3>
                                <p style="width:200%;" class="le-email-editor-desc"><?php echo $view['translator']->trans($typeTwoDescription); ?></p>
                                <button class="le-btn-primary" onclick="<?php echo $typeTwoOnClick; ?>"><?php echo $view['translator']->trans('mautic.core.choose'); ?></button>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6" style="width:<?php echo $formwidth ?>">
                        <div class="panel panel-success">
                            <div class="white-block">
                                <div>
                                    <h3 class="panel-title le-email-editor-header"><?php echo $view['translator']->trans($typeOneHeader); ?></h3>
                                    <p class="le-email-editor-desc"><?php echo $view['translator']->trans($typeOneDescription); ?></p>
                                    <button class="le-btn-primary" onclick="<?php echo $typeOneOnClick; ?>"><?php echo $view['translator']->trans('mautic.core.choose'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (isset($typeThreeIconClass)): ?>
                        <div class="col-md-6 <?php echo $hideclass ?>"style="<?php echo $formwidth ?>">
                            <div class="panel panel-success" >
                                <div class="white-block">
                                    <div>
                                        <h3 class="panel-title le-email-editor-header"><?php echo $view['translator']->trans($typeThreeHeader); ?></h3>
                                        <p class="le-email-editor-desc"><?php echo $view['translator']->trans($typeThreeDescription); ?></p>
                                        <button class="le-btn-primary"  onclick="<?php echo $typeThreeOnClick; ?>"><?php echo $view['translator']->trans('mautic.core.choose'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>