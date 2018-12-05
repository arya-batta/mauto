<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$leContent = $view['slots']->get(
    'leContent',
    isset($mauticTemplateVars['leContent']) ? $mauticTemplateVars['leContent'] : ''
);
?>

<script>
    var leBasePath    = '<?php echo $app->getRequest()->getBasePath(); ?>';
    var leBaseUrl     = '<?php echo $view['router']->path('le_base_index'); ?>';
    var leAjaxUrl     = '<?php echo $view['router']->path('le_core_ajax'); ?>';
    var leAjaxCsrf    = '<?php echo $view['security']->getCsrfToken('mautic_ajax_post'); ?>';
    var leAssetPrefix = '<?php echo $view['assets']->getAssetPrefix(true); ?>';
    var leContent     = '<?php echo $leContent; ?>';
    var leEnv         = '<?php echo $app->getEnvironment(); ?>';
    var leClientID        = '<?php echo $view['assets']->getAppid(); ?>';
    var leLang        = <?php echo $view['translator']->getJsLang(); ?>;
   //document.addEventListener("contextmenu", function(e){
     //   alert("Right Click Not Supported");
       // e.preventDefault();
    //}, false);
</script>

<?php $view['assets']->outputSystemScripts(true); ?>
<?php $view['assets']->outputBeeEditorScripts(); ?>
<?php
//if ($leContent == 'subscription' || $leContent == 'prepaidplans') {
//    echo '<script src="https://checkout.razorpay.com/v1/checkout.js"></script>';
//}
//if ($leContent == 'accountinfo' || $leContent == 'pricingplans') {
    echo '<script src="https://js.stripe.com/v3/"></script>';
//}
?>
<?php
//if ($leContent != 'user') {
echo '<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,\'script\',\'dataLayer\',\'GTM-5SC6L7G\');</script>
<!-- End Google Tag Manager -->';
//}
?>

