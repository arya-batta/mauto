<div class="modal fade le-modal-box-align in <?php echo $typePrefix; ?>-type-modal" style="display: block;" id="leSharedModal" tabindex="-1" role="dialog" aria-labelledby="leSharedModal-label" aria-hidden="false">
    <div class="le-modal-gradient">
        <div class="modal-dialog le-gradient-align">
            <div class="modal-content le-modal-content">
                <div class="modal-body ">
                    <div class="modal-body-content">
                        Its recommend using AnyFunnels in the desktop browser than mobile browser for a better experience.<a href="javascript: void(0);" onclick="Le.closeModalAndRedirect('.<?php echo $typePrefix; ?>-type-modal', '<?php echo $view['router']->path('le_dashboard_index', ['hiderecommendpc' => true]); ?>');" style="color: blue;" > Don't show this again.</a>
                    </div>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade in">
</div>