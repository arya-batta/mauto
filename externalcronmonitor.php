<?php

$absolute_path     = __FILE__;

$generalConfigPath ='settings.php';
$saaspath          = 'mautosaas';
$chdir             = 'mauto';

if (strpos($absolute_path, 'leadsengage') !== false) {
    $generalConfigPath = 'leadsengage/settings.php';
    $saaspath          = 'leadsengagesaas';
    $chdir             = 'leadsengage';
} elseif (strpos($absolute_path, 'cops') !== false) {
    $generalConfigPath = 'cops/settings.php';
    $saaspath          = 'copssaas';
    $chdir             = 'cops';
}

chdir('/var/www/'.$chdir);

//include '../'.$saaspath.'/lib/process/config.php';

if (file_exists('/var/www/appconfig/'.$generalConfigPath)) {
    include '/var/www/appconfig/'.$generalConfigPath;
}

include '../'.$saaspath.'/lib/process/field.php';
include '../'.$saaspath.'/lib/util.php';

$con=getDBConnection();

$sql        ="select domain,operation,createdtime from cronmonitor where createdtime < DATE_SUB(UTC_TIMESTAMP(),INTERVAL 15 MINUTE) and notified='0'";
$domainlist = getResultArray($con, $sql);

for ($i=0; $i < sizeof($domainlist); ++$i) {
    $domain      =$domainlist[$i][0];
    $operation   = $domainlist[$i][1];
    $createdtime = $domainlist[$i][2];

    $response = sendCronDelayInfoSlack($domain, $operation, $createdtime);
    if ($response->success) {
        $sql         = "update cronmonitor set notified='1' where domain = '$domain' and operation = '$operation'";
        $result      = execSQL($con, $sql);
    }
}

function getDBConnection()
{
    $con     =null;
    $pdoconn = new PDOConnection('');
    if ($pdoconn) {
        $con = $pdoconn->getConnection();
        if ($con == null) {
            throw new Exception($pdoconn->getDBErrorMsg());
        }
    } else {
        throw new Exception('Not able to connect to DB');
    }

    return $con;
}

function sendCronDelayInfoSlack($domain, $process, $createdtime)
{
    $additionalinfo['process']      = $process;
    $additionalinfo['CreatedTime']  = $createdtime;

    $response = sendInternalSlackNotification('cron_time_limit_exeeded', $domain, $additionalinfo);

    return $response;
}
