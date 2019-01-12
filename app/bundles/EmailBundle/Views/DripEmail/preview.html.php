<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if (!isset($content)){
    $content = 'No Preview Available...!';}
if (!empty($email) && !empty($email[0]) && $email[0]['custom_html']) {
    $content = $email[0]['custom_html'];
}
?>
<html>
<head>
    <title>Email Preview | LeadsEngage</title>
    <link rel="icon" type="image/x-icon" href="<?php echo $view['assets']->getUrl('media/images/favicon.ico') ?>" />
    <link rel="icon" sizes="192x192" href="<?php echo $view['assets']->getUrl('media/images/favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?php echo $view['assets']->getUrl('media/images/apple-touch-icon.png') ?>" />
</head>
<body>
<?php echo $content; ?>
<?php if(isset($email[1]['footer']) && $email[2]['type']=='RichTextEditor' || $email[2]['type']== null):?>
<hr>
<?php echo $email[1]['footer']?>
<?php endif;?>
</body>
</html>
