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
?>
<script>
    <?php foreach ($leLang as $key => $string): ?>
    leLang.<?php echo $key; ?> = "<?php echo $view['translator']->trans($string); ?>";
    <?php endforeach; ?>
</script>
<div class="<?php echo $typePrefix; ?>-type-modal-backdrop" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #2a323c; opacity: 0.9; z-index: 9000"></div>

<div class="modal fade in <?php echo $typePrefix; ?>-type-modal le-modal-box-align" style="display: block; z-index: 9999;margin-left: 20.55555%;">

    <div class="le-modal-gradient" style="margin-right: 90px;">
    <div class="modal-dialog le-gradient-align" style="margin-right: 120px;width: <?php echo $modalwidth ?>">
        <div class="modal-content le-modal-content" style="width: 120%;">
            <div class="modal-header">
                <a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path($cancelUrl); ?>');" class="close" ><span aria-hidden="true">&times;</span></a>
                <h4 class="modal-title">
                    <?php echo $view['translator']->trans($header); ?>
                </h4>
                <div class="modal-loading-bar"></div>
            </div>
            <div class="modal-body form-select-modal" style="background-color:#eee;">
                <div class="row">
                    <div class="white-block" style="margin-left:36px;width:43%;text-align:left;min-height: 238px;">
                        <div class="col-md-6" style="width:<?php echo $formwidth ?>">
                            <div class="panel-primary" style="margin-left: -28px;">
                                <h3 style="width: 130%;" class="panel-title le-email-editor-header"><?php echo $view['translator']->trans($typeOneHeader); ?></h3>
                                <p style="width:219%;" class="le-email-editor-desc"><?php echo $view['translator']->trans($typeOneDescription); ?></p>
                                <button class="le-btn-primary waves-effect" onclick="<?php echo $typeOneOnClick; ?>"><?php echo $view['translator']->trans('mautic.core.choose'); ?></button>

                            </div>
                        </div>
                    </div>
                    <div class="white-block" style="margin-left:25px;width:43%;text-align:left;height: 261px;">
                        <div class="col-md-6" style="width:<?php echo $formwidth ?>">
                            <div class="panel-primary" style="margin-left: -28px;">
                                <h3 style="width: 150%;"  class="panel-title le-email-editor-header"><?php echo $view['translator']->trans($typeTwoHeader); ?></h3>
                                <p style="width:220%;" class="le-email-editor-desc"><?php echo $view['translator']->trans($typeTwoDescription); ?></p>
                                <button style="margin-top: 41px" class="le-btn-primary waves-effect" onclick="<?php echo $typeTwoOnClick; ?>"><?php echo $view['translator']->trans('mautic.core.choose'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <?php if (isset($typeThreeIconClass)): ?>
                    <div class="col-md-6 <?php echo $hideclass ?>" style="width:<?php echo $formwidth ?>">
                        <div class="panel panel-success" >
                            <div class="panel-heading" style="background-color: #6B7F82;">
                                <div class="col-xs-8 col-sm-10 np">
                                    <h3 class="panel-title le-email-editor-header"><?php echo $view['translator']->trans($typeThreeHeader); ?></h3>
                                </div>
                                <div class="col-xs-4 col-sm-2 pl-0 pr-0 pt-10 pb-10 text-right">
                                    <i class="hidden-xs fa <?php echo $typeThreeIconClass; ?> fa-lg"></i>
                                    <button class="visible-xs pull-right btn btn-sm btn-default btn-nospin text-primary waves-effect" onclick="<?php echo $typeThreeOnClick; ?>"><?php echo $view['translator']->trans('mautic.core.select'); ?></button>
                                </div>
                            </div>
                            <div class="panel-body">
                                <?php echo $view['translator']->trans($typeThreeDescription); ?>
                            </div>
                            <div class="hidden-xs panel-footer text-center">
                                <button class="btn btn-lg btn-default btn-nospin text-success waves-effect" style="color : #6B7F82;" onclick="<?php echo $typeThreeOnClick; ?>"><?php echo $view['translator']->trans('mautic.core.select'); ?></button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>