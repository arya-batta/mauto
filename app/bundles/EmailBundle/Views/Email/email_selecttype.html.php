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
$modalwidth = '';
$formwidth  = '50%';
if (!isset($typeThreeIconClass)) {
    $hideclass  = 'hide';
    $modalwidth = '';
    $formwidth  = '';
}
$isAdmin=$view['security']->isAdmin();
?>
<script>
    <?php foreach ($leLang as $key => $string): ?>
    leLang.<?php echo $key; ?> = "<?php echo $view['translator']->trans($string); ?>";
    <?php endforeach; ?>
</script>
<div class="<?php echo $typePrefix; ?>-type-modal-backdrop" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #2a323c; opacity: 0.9; z-index: 9000"></div>

<div class="modal fade in <?php echo $typePrefix; ?>-type-modal le-modal-box-align" style="display: block; z-index: 9999;margin-left: 20.55555%;">
    <div class="le-modal-gradient" style="margin-right: 120px;">
    <div class="modal-dialog le-gradient-align" style="margin-right: 120px;width: <?php echo $modalwidth ?>">
        <div class="modal-content le-modal-content" style="width: 120%;">
            <div class="modal-header">
                <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($cancelUrl); ?>');" class="close" ><span aria-hidden="true">&times;</span></a>
                <h4 class="modal-title">
                    <?php echo $view['translator']->trans($header); ?>
                </h4>
                <div class="modal-loading-bar"></div>
            </div>
            <div class="modal-body form-select-modal" style="background-color: #eee;">
                <div class="row">
                    <div class="white-block" style="margin-left:20px;width:47%;min-height:250px;text-align:left">
                        <div class="col-md-6" style="width:62%">
                            <div class="panel-primary">
                                <h3 class="panel-title le-email-editor-header" style="width: 250px;"><?php echo $view['translator']->trans($typeTwoHeader); ?></h3>
                                <p style="width:190%;" class="le-email-editor-desc"><?php echo $view['translator']->trans($typeTwoDescription); ?></p>
                                <br>
                                <button class="le-btn-primary waves-effect"  onclick="<?php echo $typeTwoOnClick; ?>"><?php echo $view['translator']->trans('mautic.core.choose'); ?></button>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-6" style="width:<?php echo $formwidth ?>">
                        <div class="panel panel-success">
                            <div class="white-block"style="text-align: left;height: 250px;">
                                <div>
                                    <h3 class="panel-title le-email-editor-header"><?php echo $view['translator']->trans($typeOneHeader); ?></h3>
                                    <p class="le-email-editor-desc"><?php echo $view['translator']->trans($typeOneDescription); ?></p>
                                    <br>
                                    <br>
                                    <br>
                                    <button class="le-btn-primary waves-effect" onclick="<?php echo $typeOneOnClick; ?>"><?php echo $view['translator']->trans('mautic.core.choose'); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($isAdmin): ?>
                    <?php if (isset($typeThreeIconClass)): ?>
                        <div class="col-md-6 <?php echo $hideclass ?>"style="<?php echo $formwidth ?>">
                            <div class="panel panel-success" >
                                <div class="white-block"style="text-align: left">
                                    <div>
                                        <h3 class="panel-title le-email-editor-header"><?php echo $view['translator']->trans($typeThreeHeader); ?></h3>
                                        <p class="le-email-editor-desc"><?php echo $view['translator']->trans($typeThreeDescription); ?></p>
                                        <button class="le-btn-primary"  onclick="<?php echo $typeThreeOnClick; ?>"><?php echo $view['translator']->trans('mautic.core.choose'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>