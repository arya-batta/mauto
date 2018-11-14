<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$header = $view['translator']->trans('leadsengage.kyc.video_header');
?>
<div class="type-modal-backdrop" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #000000; opacity: 0.5; z-index: 9000"></div>

<div class="modal fade in " style="display: block;z-index: 9999;">
    <div class="modal-dialog" role="document" style="width:55%;">
        <div class="modal-content">
            <div class="modal-header">
                <a href="javascript: void(0);" onclick="Mautic.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path('le_dashboard_index'); ?>');" class="dont_show_again close_button" ><span aria-hidden="true">Close</span></a>
                <a href="javascript: void(0);" onclick="Mautic.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path('le_dashboard_index', ['login' => 'dont_show_again']); ?>');" class="dont_show_again" ><span aria-hidden="true"><?php echo $view['translator']->trans('leadsengage.kyc.dont_show'); ?></span></a>
                <h4 class="modal-title">
                    <?php echo $view['translator']->trans($header); ?>
                </h4>
            </div>
            <div class="modal-body form-select-modal">
                <iframe width="100%" height="450px"
                        src="<?php echo $videoURL; ?>">
                </iframe>
            </div>
        </div>
    </div>
</div>

