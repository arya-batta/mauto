<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$isadmin    =$view['security']->isAdmin();
$hidepanel  = ($isadmin) ? '' : "style='display: none;'";
if ($dnc && $dnc['bounced']) {
    echo '<div class="alert alert-warning">'.$view['translator']->trans('le.lead.do.not.contact_bounced').'</div>';
} else {
    echo $view['form']->start($form);
    echo $view['form']->row($form['fromname']); ?>
    <div class="row">
      <div class="form-group col-xs-12" style="width:76%;padding-right: 0px;">
           <?php echo $view['form']->row($form['from'],
                ['attr' => ['tabindex' => '-1', 'style' =>'pointer-events: none;background-color: #ebedf0;opacity: 1;']]
            ); ?>
      </div>
        <li class="dropdown" name="verifiedemails" id="verifiedemails" style="display: block;">
            <a class="btn btn-nospin btn-primary btn-sm hidden-xs" style="font-size:13px;float:inherit;margin-top:24px;padding:6px !important;" data-toggle="dropdown" href="#">
                <span><?php echo $view['translator']->trans('le.core.button.aws.load'); ?></span> </span><span><i class="caret" ></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-right" id="verifiedemails">
                <?php foreach ($verifiedemail as $key=> $value): ?>
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
    <?php
     echo $view['form']->row($form['subject']);
    echo $view['form']->row($form['body']); ?>
    <div class="row" <?php echo $hidepanel; ?>>
        <?php   echo $view['form']->row($form['templates']); ?>
    </div>
    <?php
     echo $view['form']->end($form);
}
    ?>

