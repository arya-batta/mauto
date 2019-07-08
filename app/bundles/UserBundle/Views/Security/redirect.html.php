<?php
/**
 * Created by PhpStorm.
 * User: prabhu
 * Date: 8/7/19
 * Time: 12:18 PM.
 */
?>
<html>
<head>
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
</head>
<body>
<script>
    var loginUrl=<?php echo '"'.$redirectUrl.'"' ?>;
    window.location=loginUrl
</script>
</body>
</html>
