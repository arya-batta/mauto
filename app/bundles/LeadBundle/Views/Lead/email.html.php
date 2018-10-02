<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$hideawsemailoptions = '';
$style               ='86%';
$tabindex            ='-1';
$pointereventstyle   = 'pointer-events: none;background-color: #ebedf0;opacity: 1;';
if ($mailertransport != 'mautic.transport.amazon' && $mailertransport != 'mautic.transport.sparkpost') {
    $hideawsemailoptions  = 'hide';
    $style                = '';
    $pointereventstyle    ='';
    $tabindex             = '';
}
if ($mailertransport != 'mautic.transport.amazon') {
    $hideawsemailoptions ='hide';
    $style               ='';
}
if ($dnc && $dnc['bounced']) {
    echo '<div class="alert alert-warning">'.$view['translator']->trans('mautic.lead.do.not.contact_bounced').'</div>';
} else {
    echo $view['form']->start($form);
    echo $view['form']->row($form['fromname']); ?>
    <div class="row">
      <div class="form-group col-xs-12" style="width:<?php echo $style; ?>">
           <?php echo $view['form']->row($form['from'],
                ['attr' => ['tabindex' => $tabindex, 'style' =>$pointereventstyle]]
            ); ?>
      </div>
        <li class="dropdown <?php echo $hideawsemailoptions; ?>" name="verifiedemails" id="verifiedemails" style="display: block;">
            <a class="btn btn-nospin btn-primary btn-sm hidden-xs" style="font-size:13px;float:inherit;margin-top:23px;" data-toggle="dropdown" href="#">
                <span><?php echo $view['translator']->trans('le.core.button.aws.load'); ?></span> </span><span><i class="caret" ></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-right" id="verifiedemails">
                <li>
                    <?php foreach ($verifiedemail as $key=> $value):?>
                <li >
                    <a class="verified-emails" id="data-verified-emails" data-verified-email="<?php echo $value; ?>"><?php echo $value; ?></a>
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

