<?php

//ini_set ( "display_errors", "1" );
//error_reporting ( E_ALL );
chdir('/var/www/mauto');

include '../mautosaas/lib/process/config.php';
include '../mautosaas/lib/process/field.php';
include '../mautosaas/lib/util.php';
include '../mautosaas/lib/process/createElasticEmailSubAccount.php';
include '../mautosaas/lib/process/createSendGridAccount.php';

$loader = require_once __DIR__.'/app/autoload.php';

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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

if (sizeof($argv) < 2) {
    exit('No Arguments Provided!');
}
$arguments      ='';
$domainattrfound=false;
for ($index=1; $index < sizeof($argv); ++$index) {
    $arguement=$argv[$index];
    if (strpos($arguement, '--domain=') !== false) {
        $domainattrfound=true;
    }
    $arguments .= ' '.$arguement;
}
try {
    if (!$domainattrfound) {
        $pdoconn = new PDOConnection('');
        if ($pdoconn) {
            $con = $pdoconn->getConnection();
            if ($con == null) {
                throw new Exception($pdoconn->getDBErrorMsg());
            }
        } else {
            throw new Exception('Not able to connect to DB');
        }
        $operation=$argv[1];
        if (isset($argv[1])) {
            $fcolname='';
            if ($operation == 'le:import') {
                $fcolname = 'f17';
            } elseif ($operation == 'le:segments:update') {
                $fcolname = 'f18';
            } elseif ($operation == 'le:campaigns:rebuild') {
                $fcolname = 'f19';
            } elseif ($operation == 'le:campaigns:trigger') {
                $fcolname = 'f20';
            } elseif ($operation == 'le:emails:send') {
                $fcolname = 'f21';
            } elseif ($operation == 'mautic:email:fetch') {
                $fcolname = 'f26';
            } elseif ($operation == 'mautic:list:update') {
                $fcolname = 'f27';
            } elseif ($operation == 'mautic:reports:scheduler') {
                $fcolname = 'f28';
            } elseif ($operation == 'le:payment:update') {
                $fcolname = 'f29';
            } elseif ($operation == 'le:dripemail:send') {
                $fcolname = 'f30';
            } elseif ($operation == 'le:oneoff:send') {
                $fcolname = 'f23';
            } elseif ($operation == 'le:dripemail:rebuild') {
                $fcolname = 'f22';
            }
        } else {
            exit('Please Configure Valid Parameter');
        }
        $sql  = "select skiplimit from cronmonitorinfo where command='$operation'";
//        displayCronlog('general', 'SQL QUERY:'.$sql);
        $monitorinfo = getResultArray($con, $sql);
        if (sizeof($monitorinfo) > 0) {
            /* $skiplimit=$monitorinfo[0][0];
             if($skiplimit >= $SKIP_MAX_LIMIT){
             cleanCronStatus($con,$operation,$domain);
             }else{
             $sql="update cronmonitorinfo set skiplimit=skiplimit+1 where domain='$domain' and command='$operation'";
             displayCronlog($domain,"SQL QUERY:".$sql);
             $result = execSQL($con, $sql);
             }	*/
            displayCronlog('general', "This operation ($operation) already in process.");
            exit(0);
        } else {
            $sql = "insert into cronmonitorinfo values('','$operation','0')";
            displayCronlog('general', 'SQL QUERY:'.$sql);
            $result = execSQL($con, $sql);
        }
        $sql        ='select f5 from applicationlist where '.$fcolname."='1' and f7 = 'Active'";
        $domainlist = getResultArray($con, $sql);
        //$SKIP_MAX_LIMIT=5;
        $errormsg = '';

        displayCronlog('general', 'Sizeof Domains :  '.sizeof($domainlist));
        for ($di=0; $di < sizeof($domainlist); ++$di) {
            $errormsg    = '';
            $domain      =$domainlist[$di][0];

            if ($domain == '') {
                continue;
            }

            if (!file_exists("app/config/$domain/local.php")) {
                continue;
            }

            $sql         = "select appid from applicationlist where f5 = '$domain';";
            $appidarr    = getResultArray($con, $sql);
            $appid       = $appidarr[0][0];
            $dbname      = DBINFO::$COMMONDBNAME.$appid;
            $sql         = "SELECT SCHEMA_NAME   FROM INFORMATION_SCHEMA.SCHEMATA  WHERE SCHEMA_NAME = '$dbname';";
            $result      = getResultArray($con, $sql);

            if (!sizeof($result) > 0) {
                continue;
            }

            if (checkLicenseAvailablity($con, $domain)) {
                continue;
            }
            if ($operation == 'le:import') {
                $importstatus = checkImportAvailablity($con, $domain);
                if (!$importstatus) {
                    displayCronlog('general', "This operation ($operation) for ($domain) is skipped because no import available.");
                    continue;
                }
            } elseif ($operation == 'le:campaigns:trigger') {
                $emailstatus = checkTriggerAvailablity($con, $domain);
                if (!$emailstatus) {
                    displayCronlog('general', "This operation ($operation) for ($domain) is skipped because no Contacts available.");
                    continue;
                }
            } elseif ($operation == 'le:emails:send') {
                $triggerstatus = checkEmailAvailablity($domain);
                if (!$triggerstatus) {
                    displayCronlog('general', "This operation ($operation) for ($domain) is skipped because no email available.");
                    continue;
                }
            }
            $currentdate = date('Y-m-d');
            $sql         = "select count(*) from cronerrorinfo where domain = '$domain' and operation = '$operation' and createdtime like '$currentdate%'";
            $errorinfo   = getResultArray($con, $sql);
            if (sizeof($errorinfo) != 0 && $errorinfo[0][0] > 5) {
                displayCronlog('general', "This operation ($operation) for ($domain) is failing repeatedly.");
                continue;
            }
            $command="app/console $arguments --domain=$domain";
            displayCronlog($domain, 'Command Invoked:'.$command);
            $output=executeCommand($command);
            if (strpos($output, 'exception->') !== false) {
                $errormsg = $output;
            }
            if (strpos($output, 'exceeded the timeout') !== false) {
                $errormsg = '';
            }
            //	    displayCronlog('general', $domain.'errorinfo:  '.$errormsg);
            if ($errormsg != '') {
                displayCronlog('general', 'errorinfo:  '.$errormsg);
                updatecronFailedstatus($con, $domain, $operation, $errormsg);
                if ($operation == 'le:emails:send') {
                    require_once "app/config/$domain/local.php";
                    $mailer        = $parameters['mailer_transport'];
                    $elasticapikey = $parameters['mailer_password'];
                    $subusername   = $parameters['mailer_user'];
                    if ($mailer == 'le.transport.elasticemail' && strpos($errormsg, 'Failed to authenticate on SMTP server with') !== false) {
                        CheckESPStatus($con, $domain, $mailer, $elasticapikey, $subusername);
                    } elseif ($mailer == 'le.transport.sendgrid_api' && strpos($errormsg, 'The provided authorization grant is invalid, expired, or revoked') !== false) {
                        CheckESPStatus($con, $domain, $mailer, $elasticapikey, $subusername);
                    }
                }
            }
            displayCronlog('general', $domain.' : '.$command);
            displayCronlog($domain, 'Command Results:'.$output);
            sleep(5);
        }
        cleanCronStatus($con, $operation, '');
    } else {
        $command="php app/console $arguments ";
        displayCronlog('general', 'Command Invoked:'.$command);
        // $output = shell_exec($command);
        $output=executeCommand($command);
        if (strpos($output, 'exception->') !== false) {
            $errormsg = $output;
        }
        displayCronlog('general', 'Command Results:'.$output);
    }
} catch (Exception $ex) {
    $msg = $ex->getMessage();
    displayCronlog('general', 'Exception Occur:'.$msg);
}

function cleanCronStatus($con, $command, $domain)
{
    $sql = "delete from cronmonitorinfo where  command='$command'";
    displayCronlog('general', 'SQL QUERY:'.$sql);
    $result = execSQL($con, $sql);
}

function updatecronFailedstatus($con, $domain, $operation, $errorinfo)
{
    $errorinfo   = mysql_escape_string($errorinfo);
    $currentdate = date('Y-m-d H:i:s');
    $sql         = "insert into cronerrorinfo values ('$domain','$operation','$currentdate','$errorinfo')";
    displayCronlog('general', 'SQL QUERY:'.$sql);
    $result      = execSQL($con, $sql);
}

function executeCommand($command)
{
    $output  ='';
    $process = new Process($command);
    try {
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        // $output = shell_exec($command);
        $output=$process->getOutput();
    } catch (ProcessFailedException $pex) {
        $output='exception->'.$pex->getMessage();
    } catch (Exception $pex) {
        $output='exception->'.$pex->getMessage();
    } finally {
        $process->clearOutput();
        $process->clearErrorOutput();
    }

    return $output;
}
function CheckESPStatus($con, $domain, $mailer, $elasticapikey, $subusername)
{
    $status = true;
    if ($mailer == 'le.transport.elasticemail') {
        if ($elasticapikey != '') {
            $status = checkStatusofElastic($elasticapikey);
        }
    } elseif ($mailer == 'le.transport.sendgrid_api') {
        if ($subusername != '') {
            $status = checkStatus($subusername);
        }
    }
    if (!$status) {
        updateEmailAccountStatus($con, $domain);
    }
}

function updateEmailAccountStatus($con, $domain)
{
    $sql         = "select appid from applicationlist where f5 = '$domain';";
    $appidarr    = getResultArray($con, $sql);
    $appid       = $appidarr[0][0];
    $licenseinfo = DBINFO::$COMMONDBNAME.$appid.'.licenseinfo';
    $sql         = "update $licenseinfo set app_status = 'Suspended'";
    $result      = execSQL($con, $sql);
    $sql         = "update applicationlist set f21 = 0 where f5 = '$domain'";
    $result      = execSQL($con, $sql);
}

function checkEmailAvailablity($domain)
{
    $directory = "app/spool/$domain/default/";
    $filecount = 0;
    $files     = glob($directory.'*');
    if ($files) {
        $filecount = count($files);
        displayCronlog('general', 'Email File Count from Spool:'.$filecount);
        if ($filecount > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function checkImportAvailablity($con, $domain)
{
    $sql         = "select appid from applicationlist where f5 = '$domain';";
    $appidarr    = getResultArray($con, $sql);
    $appid       = $appidarr[0][0];
    $dbname      = DBINFO::$COMMONDBNAME.$appid.'.imports';
    $sql         = "select count(*) from $dbname where is_published = 1 and status in ('1','7')";
    $result      = getResultArray($con, $sql);
    $count       = $result[0][0];
    displayCronlog('general', 'Import Live Count:'.$count);
    if ($count > 0) {
        return true;
    } else {
        return false;
    }
}

function checkTriggerAvailablity($con, $domain)
{
    $sql         = "select appid from applicationlist where f5 = '$domain';";
    $appidarr    = getResultArray($con, $sql);
    $appid       = $appidarr[0][0];
    $dbname      = DBINFO::$COMMONDBNAME.$appid.'.campaigns';
    $sql         = "select id from $dbname where is_published = 1";
    $result      = getResultArray($con, $sql);
    $count       = sizeof($result);
    displayCronlog('general', 'Campaign Published Count:'.$count);
    if ($count > 0) {
        for ($i = 0; $i < $count; ++$i) {
            $id          = $result[$i][0];
            $tablename   = DBINFO::$COMMONDBNAME.$appid.'.campaign_leads';
            $sql         = "select count(*) from $tablename where campaign_id = '$id'";
            $response    = getResultArray($con, $sql);
            if (sizeof($response) > 0) {
                displayCronlog('general', "Contacts available against this campaign($id)");

                return true;
            }
        }
        displayCronlog('general', 'No more contacts available against any campaign');

        return false;
    } else {
        return false;
    }
}

function checkLicenseAvailablity($con, $domain)
{
    $sql                =  "select appid from applicationlist where f5 = '$domain';";
    $appidarr           =  getResultArray($con, $sql);
    $appid              =  $appidarr[0][0];
    $dbname             =  DBINFO::$COMMONDBNAME.$appid.'.licenseinfo';
    $sql                =  "select licensed_days,license_end_date  from $dbname";
    $licenseresultsarr  =  getResultArray($con, $sql);
    $licenseRemDays     =  $licenseresultsarr[0][0];
    $licenseEnd         =  $licenseresultsarr[0][1];
    $currentDate        =date('Y-m-d');

    if ($licenseRemDays == 'UL') {
        return false;
    }
    $licenseremdays = round((strtotime($licenseEnd) - strtotime($currentDate)) / 86400);

    if ($licenseremdays < 0) {
        return true;
    } else {
        return false;
    }
}
