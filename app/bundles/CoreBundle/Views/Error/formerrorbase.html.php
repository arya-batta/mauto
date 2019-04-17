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
    if ($status_text == 'Forbidden') {
        $status_text .= ' | '.$view['content']->getProductBrandName();
    }
    if (!$isInline) {
        $view['slots']->set('pageTitle', $status_text);
    }
}
$message = $view['slots']->get('message', 'mautic.core.error.generic');
$view['slots']->set('leContent', 'error');
?>form
<div class="accountbg" style="background: url('<?php echo $view['assets']->getUrl('media/images/errorbg.png') ?>')"></div>
<div class="wrapper-page">
    <div class="ex-page-content text-center">
        <h1 ><?php echo $status_code; ?>!</h1>
        <h3 ><?php echo $view['translator']->trans('mautic.core.error.404'); ?></h3><br>
    </div>
</div>
<div class="clearfix"></div>
