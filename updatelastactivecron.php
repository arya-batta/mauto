<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 31/12/18
 * Time: 4:06 PM.
 */
chdir('/var/www/mauto');

//include '../mautosaas/lib/process/config.php';

$generalConfigPath ='settings.php';
$absolute_path     = __FILE__;

if (strpos($absolute_path, 'leadsengage') !== false) {
    $generalConfigPath = 'leadsengage/settings.php';
} elseif (strpos($absolute_path, 'cops') !== false) {
    $generalConfigPath = 'cops/settings.php';
}

if (file_exists('/var/www/appconfig/'.$generalConfigPath)) {
    include '/var/www/appconfig/'.$generalConfigPath;
}

include '../mautosaas/lib/process/field.php';
include '../mautosaas/lib/util.php';

function displayCronlog($domain, $msg)
{
    $logdir="app/logs/$domain";
    if (!is_dir($logdir)) {
        $old = umask(0);
        mkdir($logdir, 0777, true);
        umask($old);
    }
    $logfile     = "$logdir/cronmonitor.log";
    $baseurl     = 'localhost';
    $remoteaddr  = 'localhost';
    $logfilesize = getLogFileSize($logfile);
    if ($logfilesize > LOGINFO::$DEFAULT_FILE_SIZE) {
        $filepath = $logfile;
        createLogZipfile($logdir, 'qsignup.log');
        if (file_exists($filepath)) {
            $old = umask(0);
            unlink($filepath);
            umask($old);
        }
    }
    $currenttime = date('Y-m-d H:i:s');
    error_log($remoteaddr.' : '.$currenttime." : $msg\n", 3, $logfile);
}
try {
    $pdoconn = new PDOConnection('');
    if ($pdoconn) {
        $con = $pdoconn->getConnection();
        if ($con == null) {
            throw new Exception($pdoconn->getDBErrorMsg());
        }
    } else {
        throw new Exception('Not able to connect to DB');
    }
    date_default_timezone_set('UTC');
    $curtime = date('Y-m-d H:i:s');
    $sql     = "update leads set last_active='$curtime'";
    displayCronlog('general', 'SQL QUERY:'.$sql);
    $result = execSQL($con, $sql);
} catch (Exception $ex) {
    $msg = $ex->getMessage();
    displayCronlog('general', 'Exception Occur:'.$msg);
}
