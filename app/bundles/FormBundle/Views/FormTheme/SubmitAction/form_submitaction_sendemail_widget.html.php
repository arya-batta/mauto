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
<div class="row hide">
    <div class="col-sm-6">
        <?php echo $view['form']->row($form['email_to_owner']); ?>
        <?php echo $view['form']->row($form['set_replyto']); ?>
    </div>
    <div class="col-sm-6">
        <?php echo $view['form']->row($form['copy_lead']); ?>
        <?php echo $view['form']->row($form['immediately']); ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <?php echo $view['form']->row($form['to']); ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <?php echo $view['form']->row($form['subject']); ?>
    </div>
</div>
 <div class="row">
    <div class="col-sm-8" id="emailMessage">
        <?php echo $view['form']->row($form['message']); ?>
    </div>
    <div class="col-sm-4">
        <label class="control-label"><?php echo $view['translator']->trans('mautic.form.action.sendemail.dragfield'); ?></label>
        <div id="formFieldTokens" class="list-group" style="max-height: 250px; overflow-y: auto;">
            <?php foreach ($formFields as $token => $field): ?>
                <a class="list-group-item ellipsis" href="#" onclick="mQuery('#formaction_properties_message').froalaEditor('html.insert', '<?php echo $token; ?>');"><?php echo $field; ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

