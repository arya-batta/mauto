<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');

$view['slots']->set('leContent', 'featuresandideas');
$view['slots']->set('headerTitle', $view['translator']->trans('le.feauturesandideas.header.title'));
?>
<html>
<body>
<iframe width="100%" height="600" src="https://feedback.userreport.com/7e57b835-35f8-4bf6-83d1-a8d7504f90c1/" style="background: #ffffff;" frameborder="0"></iframe>
</body>
</html>
