<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$isInline = !empty($inline);
if (!$app->getRequest()->isXmlHttpRequest()) {
    $view->extend($baseTemplate);
    if ($status_text == "Forbidden"){
        $status_text .= " | LeadsEngage";
    }
    if (!$isInline) {
        $view['slots']->set('pageTitle', $status_text);
    }
}

$img = $view['slots']->get('mautibot', 'wave');
$src = $view['mautibot']->getImage($img);

$message = $view['slots']->get('message', 'mautic.core.error.generic');
?>
<div class="pa-20 mautibot-error<?php if (!empty($inline)): echo ' inline well'; endif; ?>" style="position:relative;top:100px;">
    <div class="row mt-lg pa-md">
        <?php if ($isInline): ?>
            <div class="mautibot-content col-xs-12">
                <h1><i class="fa fa-warning fa-fw text-danger"></i><?php echo $view['translator']->trans($message, ['%code%' => $status_code]); ?></h1>
                <h4 class="mt-5 hide"><strong><?php echo $status_code; ?></strong> <?php echo $status_text; ?></h4>
            </div>
        <?php else: ?>

            <div class="mautibot-image col-xs-4 col-md-3" style="position:relative;bottom:45px;">
                <img class="img-responsive" src="<?php echo $src; ?>" />
            </div>
            <div class="mautibot-content col-xs-8 col-md-9"  style="margin-top:30px;">
                <blockquote class="np break-word">
                    <h1><i class="fa fa-quote-left"></i> <?php echo $view['translator']->trans($message, ['%code%' => $status_code]); ?> <i class="fa fa-quote-right"></i></h1>
                    <h4 class="mt-5 hide"><strong><?php echo $status_code; ?></strong> <?php echo $status_text; ?></h4>

                    <footer class="text-right">LeadsEngageBot</footer>
                </blockquote>
                <div class="pull-right">
                    <!--http://mau.tc/report-issue-->
                    <a class="text-muted" href="https://leadsengage.freshdesk.com/support/login" target="_new"><?php echo $view['translator']->trans('mautic.core.report_issue'); ?></a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="clearfix"></div>
