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
    var productBrandName        = '<?php echo $view['content']->getProductBrandName(); ?>';
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
<script>
    var currenturl = window.location.href;
    var lazyvalue;
    if (currenturl.indexOf('login') > -1) {
        lazyvalue = true;
    } else {
        lazyvalue = false;
    }

    var beamer_config = {
        product_id : 'ywOsMtvPnull', //DO NOT CHANGE: This is your product code on Beamer
        selector : '#le_beamer_index , .le_beamer , .beamer_avoid', /*Optional: Id, class (or list of both) of the HTML element to use as a button*/
        //display : 'right', /*Optional: Choose how to display the Beamer panel in your site*/
        //top: 0, /*Optional: Top position offset for the notification bubble*/
        //right: 0, /*Optional: Right position offset for the notification bubble*/
        //bottom: 0, /*Optional: Bottom position offset for the notification bubble*/
        //left: 0, /*Optional: Left position offset for the notification bubble*/
        //button_position: 'bottom-right', /*Optional: Position for the notification button that shows up when the selector parameter is not set*/
        //icon: 'bell_lines', /*Optional: Alternative icon to display in the notification button*/
        //language: 'EN', /*Optional: Bring news in the language of choice*/
        //filter: 'admin', /*Optional : Bring the news for a certain role as well as all the public news*/
        lazy: lazyvalue, /*Optional : true if you want to manually start the script by calling Beamer.init()*/
        //alert : true, /*Optional : false if you don't want to initialize the selector*/
        //delay : 0, /*Optional : Delay (in milliseconds) before initializing Beamer*/
        //embed : false, /*Optional : true if you want to embed and display the feed inside the element selected by the 'selector' parameter*/
        //mobile : true, /*Optional : false if you don't want to initialize Beamer on mobile devices*/
        //notification_prompt : 'sidebar', /*Optional : override the method selected to prompt users for permission to receive web push notifications*/
        //callback : your_callback_function, /*Optional : Beamer will call this function, with the number of new features as a parameter, after the initialization*/
        //onclick : your_onclick_function(url, openInNewWindow), /*Optional : Beamer will call this function when a user clicks on a link in one of your posts*/
        //onopen : your_onopen_function, /*Optional : Beamer will call this function when opening the panel*/
        //onclose : your_onclose_function, /*Optional : Beamer will call this function when closing the panel*/
        //---------------Visitor Information---------------
        //user_firstname : "firstname", /*Optional : Input your user firstname for better statistics*/
        //user_lastname : "lastname", /*Optional : Input your user lastname for better statistics*/
        //user_email : "email", /*Optional : Input your user email for better statistics*/
        //user_id : "user_id" /*Optional : Input your user ID for better statistics*/
    };
</script>
<?php /** src= https://app.getbeamer.com/js/beamer-embed.js   modified by GS*/ ?>
<script type="text/javascript" src="" defer="defer"></script>



<?php
//if ($leContent != 'user') {
echo '<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,\'script\',\'dataLayer\',\'GTM-KGNSCMP\');</script>
<!-- End Google Tag Manager -->';
//}
?>

