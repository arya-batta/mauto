<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<html>
    <head>
        <title><?php echo $name; ?></title>
        <link rel="icon" type="image/x-icon" href="<?php echo $view['assets']->getUrl('media/images/favicon.ico') ?>" />
        <link rel="icon" sizes="192x192" href="<?php echo $view['assets']->getUrl('media/images/favicon.ico') ?>">
        <link rel="apple-touch-icon" href="<?php echo $view['assets']->getUrl('media/images/apple-touch-icon.png') ?>" />
        <?php echo $view['analytics']->getCode(); ?>

        <?php if (isset($stylesheets) && is_array($stylesheets)) : ?>
        <?php foreach ($stylesheets as $css): ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $css; ?>" />
        <?php endforeach; ?>
        <?php endif; ?>

    </head>
    <body>
        <?php echo $content; ?>
    </body>
</html>
