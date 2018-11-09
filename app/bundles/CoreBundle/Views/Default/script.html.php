<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$mauticContent = $view['slots']->get(
    'mauticContent',
    isset($mauticTemplateVars['mauticContent']) ? $mauticTemplateVars['mauticContent'] : ''
);
?>

<script>
    var mauticBasePath    = '<?php echo $app->getRequest()->getBasePath(); ?>';
    var mauticBaseUrl     = '<?php echo $view['router']->path('mautic_base_index'); ?>';
    var mauticAjaxUrl     = '<?php echo $view['router']->path('mautic_core_ajax'); ?>';
    var mauticAjaxCsrf    = '<?php echo $view['security']->getCsrfToken('mautic_ajax_post'); ?>';
    var mauticAssetPrefix = '<?php echo $view['assets']->getAssetPrefix(true); ?>';
    var mauticContent     = '<?php echo $mauticContent; ?>';
    var mauticEnv         = '<?php echo $app->getEnvironment(); ?>';
    var leClientID        = '<?php echo $view['assets']->getAppid(); ?>';
    var mauticLang        = <?php echo $view['translator']->getJsLang(); ?>;
   //document.addEventListener("contextmenu", function(e){
     //   alert("Right Click Not Supported");
       // e.preventDefault();
    //}, false);
</script>

<?php $view['assets']->outputSystemScripts(true); ?>
<?php $view['assets']->outputBeeEditorScripts(); ?>
<?php
if ($mauticContent == 'subscription' || $mauticContent == 'prepaidplans') {
    echo '<script src="https://checkout.razorpay.com/v1/checkout.js"></script>';
}
//if ($mauticContent == 'accountinfo' || $mauticContent == 'pricingplans') {
    echo '<script src="https://js.stripe.com/v3/"></script>';
//}
?>
<?php
if ($mauticContent != 'user') {
    echo '<!--Start of Tawk.to Script-->
<script type="text/javascript">
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
        var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
        s1.async=true;
        s1.src=\'https://embed.tawk.to/5acda3a2d7591465c7096324/default\';
        s1.charset=\'UTF-8\';
        s1.setAttribute(\'crossorigin\',\'*\');
        s0.parentNode.insertBefore(s1,s0);
    })();
</script>
<!--End of Tawk.to Script-->
<!-- Start of Support Hero Script-->
<script async data-cfasync="false" src="https://d29l98y0pmei9d.cloudfront.net/js/widget.min.js?k=Y2xpZW50SWQ9MTgyOSZob3N0TmFtZT1sZWFkc2VuZ2FnZS5zdXBwb3J0aGVyby5pbw=="></script>
<!-- End of Support Hero Script-->';
}

echo '<!-- Hotjar Tracking Code for https://leadsengage.com -->
<script>
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:876063,hjsv:6};
        a=o.getElementsByTagName(\'head\')[0];
        r=o.createElement(\'script\');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,\'https://static.hotjar.com/c/hotjar-\',\'.js?sv=\');
</script>';
?>

