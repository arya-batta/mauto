<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

if (!function_exists('getLocalConfigPath')) {
    function getLocalConfigPath()
    {
        $host='localhost';
        if (isset($_SERVER['SERVER_NAME'])) {
            $host=$_SERVER['SERVER_NAME'];
        }
        if ($host == '192.168.1.52') {
            $host='localhost';
        }
        $hostarr=explode('.', $host);
        if (sizeof($hostarr) > 1) {
            $host= $hostarr[0];
        }
        if (LEADSENGAGE_DOMAIN != '') {//request comes via command prompt
            $host=LEADSENGAGE_DOMAIN;
        }
        $localpath='%kernel.root_dir%/config/'.$host.'/local.php';
        //   file_put_contents("/var/www/mautic/app/cache/log.txt",$localpath."\n",FILE_APPEND);
        return $localpath;
    }
}

$paths = [
    //customizable
    'themes'             => 'themes',
    'beetemplates'       => 'beetemplates',
    'plans'              => 'plans',
    'assets'             => 'media',
    'asset_prefix'       => '',
    'plugins'            => 'plugins',
    'translations'       => 'translations',
    'local_config'       => getLocalConfigPath(),
];

//allow easy overrides of the above
if (file_exists(__DIR__.'/paths_local.php')) {
    include __DIR__.'/paths_local.php';
}

//fixed
$paths = array_merge($paths, [
    //remove /app from the root
    'root'    => substr($root, 0, -4),
    'app'     => 'app',
    'bundles' => 'app/bundles',
    'vendor'  => 'vendor',
]);
