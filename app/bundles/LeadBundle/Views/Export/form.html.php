<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 12/6/19
 * Time: 12:58 PM.
 */
?>
<p style="margin-top: -15px;"><?php echo $view['translator']->trans('le.lead.export.desc'); ?></p>
<br>
<?php echo $view['form']->start($form); ?>
    <div class="has-error">
        <div id="export-form-error" class="help-block hide">
            <?php echo $view['translator']->trans('le.lead.export.error.msg'); ?>
        </div>
        <div id="export-field-error" class="help-block hide">
            <?php echo $view['translator']->trans('le.lead.export.error.msg'); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?php echo $view['form']->row($form['startIndex']); ?>
        </div>
        <div class="col-md-6">
            <?php echo $view['form']->row($form['endIndex']); ?>
        </div>
    </div>
    <a data-dismiss='modal' href='#' class="btn btn-default btn-cancel le-btn-default waves-effect btn-copy">
    Cancel</a>
    <a href="#" link="<?php echo $actionUrl; ?>?isdownload=1" onclick="Le.leadExport(this)" class="btn btn-default btn-apply le-btn-default waves-effect text-transform-none btn-copy">
        <span>
            <span>Export Leads</span>
        </span>
    </a>
<?php echo $view['form']->end($form); ?>
