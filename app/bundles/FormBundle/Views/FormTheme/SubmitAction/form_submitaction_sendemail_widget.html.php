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
<?php
echo $view['form']->row($form['fromname']); 
?>
<div class="row">
    <div class="col-sm-12">
        <div class="form-group col-xs-12" style="width:76%;margin-left: -14px;">
            <?php echo $view['form']->row($form['from'],
                ['attr' => ['tabindex' => '-1', 'style' =>'pointer-events: none;background-color: #ebedf0;opacity: 1;']]
            ); ?>
        </div>
        <li class="dropdown" name="verifiedemails" id="verifiedemails" style="display: block;">
            <a class="btn btn-nospin btn-primary btn-sm hidden-xs" style="font-size:16px;margin-left:-10px;float:inherit;margin-top:26px;" data-toggle="dropdown" href="#">
                <span><?php echo $view['translator']->trans('le.core.button.aws.load'); ?></span> </span><span><i class="caret" ></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-right" id="verifiedemails">
                <?php foreach ($verifiedEmails as $key=> $value): ?>
                    <li>
                        <a style="text-transform: none" class="verified-emails" id="data-verified-emails" data-verified-emails="<?php echo $value; ?>" data-verified-fromname="<?php echo $key; ?>"><?php echo $value; ?></a>
                    </li>
                <?php endforeach; ?>
                <li >
                    <a style="text-transform: none" href="<?php echo $view['router']->generate('le_config_action', ['objectAction' => 'edit']); ?>" class="verified-emails" ><?php echo $view['translator']->trans('le.email.add.new.profile'); ?></a>
                </li>
            </ul>
        </li>
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

