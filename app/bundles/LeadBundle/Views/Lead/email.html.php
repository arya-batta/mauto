<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($dnc && $dnc['bounced']) {
    echo '<div class="alert alert-warning">'.$view['translator']->trans('le.lead.do.not.contact_bounced').'</div>';
} else {
    echo $view['form']->start($form);
    echo $view['form']->row($form['fromname']); ?>
    <div class="row">
      <div class="form-group col-xs-12" style="width:76%">
           <?php echo $view['form']->row($form['from'],
                ['attr' => ['tabindex' => '-1', 'style' =>'pointer-events: none;background-color: #ebedf0;opacity: 1;']]
            ); ?>
      </div>
        <li class="dropdown" name="verifiedemails" id="verifiedemails" style="display: block;">
            <a class="btn btn-nospin btn-primary btn-sm hidden-xs" style="font-size:13px;float:inherit;margin-top:25px;" data-toggle="dropdown" href="#">
                <span><?php echo $view['translator']->trans('le.core.button.aws.load'); ?></span> </span><span><i class="caret" ></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-right" id="verifiedemails">
                <li>
                    <?php foreach ($verifiedemail as $key=> $value): ?>
                <li>
                    <a style="text-transform: none" class="verified-emails" id="data-verified-emails" data-verified-emails="<?php echo $value; ?>" data-verified-fromname="<?php echo $key; ?>"><?php echo $key; ?></a>
                </li>
                <?php endforeach; ?>
                </li>
            </ul>
        </li>
    </div>
    <?php
    echo $view['form']->row($form['subject']);
    echo $view['form']->row($form['body']);
    echo $view['form']->row($form['templates']);

    echo $view['form']->end($form);
}
?>

