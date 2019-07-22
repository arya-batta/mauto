<?php

//ini_set ( "display_errors", "1" );
//error_reporting ( E_ALL );
chdir('/var/www/mauto');

include '../mautosaas/lib/process/config.php';
include '../mautosaas/lib/process/field.php';
include '../mautosaas/lib/util.php';
//include '../mautosaas/lib/process/createElasticEmailSubAccount.php';
//include '../mautosaas/lib/process/createSendGridAccount.php';

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
        createLogZipfile($logdir, 'cronmonitor.log');
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
        date_default_timezone_set('UTC');
        $operation=$argv[1];
        if (isset($argv[1])) {
            $fcolname='';
            if ($operation == 'le:import') {
                $fcolname = 'f17';
            } elseif ($operation == 'le:segments:update') {
                $fcolname = 'f18';
            } /*elseif ($operation == 'le:campaigns:rebuild') {
                $fcolname = 'f19';
            } */elseif ($operation == 'le:campaigns:trigger') {
                $fcolname = 'f20';
            } elseif ($operation == 'le:emails:send') {
                $fcolname = 'f21';
            } elseif ($operation == 'le:email:fetch') {
                $fcolname = 'f26';
            } /*elseif ($operation == 'mautic:list:update') {
                $fcolname = 'f27';
            } */elseif ($operation == 'le:reports:scheduler') {
                $fcolname = 'f28';
            } elseif ($operation == 'le:payment:update') {
                $fcolname = 'f29';
            } elseif ($operation == 'le:dripemail:send') {
                $fcolname = 'f30';
            } elseif ($operation == 'le:oneoff:send') {
                $fcolname = 'f23';
            } elseif ($operation == 'le:dripemail:rebuild') {
                $fcolname = 'f22';
            } elseif ($operation == 'le:score:update') {
                $fcolname = 'f24';
            } elseif ($operation == 'le:listoptin:resend') {
                $fcolname = 'f25';
            } elseif ($operation == 'le:iplookup:download') {
                $fcolname = 'f16';
            } elseif ($operation == 'le:statemachine:checker') {
                $fcolname = 'f15';
            }
            if ($fcolname == '') {
                exit('Please Configure Valid Parameter');
            }
        } else {
            exit('Please Configure Valid Parameter');
        }
        $sql         = "select skiplimit from cronmonitorinfo where command='$operation'";
        $monitorinfo = getResultArray($con, $sql);
        if (sizeof($monitorinfo) > 0) {
            displayCronlog('general', "This operation ($operation) already in process.");
            exit(0);
        } else {
            $sql = "insert into cronmonitorinfo values('','$operation','0')";
            displayCronlog('general', 'SQL QUERY:'.$sql);
            $result = execSQL($con, $sql);
        }
        $sql        ='select f5,appid from applicationlist where '.$fcolname."='1' and f7 = 'Active'";
        $domainlist = getResultArray($con, $sql);
        //$SKIP_MAX_LIMIT=5;
        $errormsg = '';

        displayCronlog('general', 'Sizeof Domains :  '.sizeof($domainlist));
        for ($di=0; $di < sizeof($domainlist); ++$di) {
            $errormsg    = '';
            $domain      =$domainlist[$di][0];
            $appid       = $domainlist[$di][1];
            if ($domain == '') {
                continue;
            }
            if (!file_exists("app/config/$domain/local.php")) {
                continue;
            }
//            $sql         = "select appid from applicationlist where f5 = '$domain';";
//            $appidarr    = getResultArray($con, $sql);

            $dbname      = DBINFO::$COMMONDBNAME.$appid;
            $sql         = "show databases like '$dbname'";
            $result      = getResultArray($con, $sql);
            if (!sizeof($result) > 0) {
                continue;
            }
//            if ($operation != 'le:statemachine:checker' && checkLicenseAvailablity($con, $dbname, $appid)) {
//                continue;
//            }
            if (!isValidCronExecution($con, $domain, $dbname, $appid, $operation)) {
                displayCronlog($domain, "This operation ($operation) for ($domain) is skipped.");
                continue;
            }
            displayCronlog($domain, "This operation ($operation) for ($domain) is Progress.");
            $currentdate = date('Y-m-d');
            $sql         = "select count(*) from cronerrorinfo where domain = '$domain' and operation = '$operation' and createdtime like '$currentdate%'";
            $errorinfo   = getResultArray($con, $sql);
            if (sizeof($errorinfo) != 0 && $errorinfo[0][0] > 5) {
                displayCronlog($domain, "This operation ($operation) for ($domain) is failing repeatedly.");
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
//                if ($operation == 'le:emails:send') {
//                    require_once "app/config/$domain/local.php";
//                    $mailer        = $parameters['mailer_transport'];
//                    $elasticapikey = $parameters['mailer_password'];
//                    $subusername   = $parameters['mailer_user'];
//                    if ($subusername != '') {
//                        if ($mailer == 'le.transport.elasticemail' && strpos($errormsg, 'Failed to authenticate on SMTP server with') !== false) {
//                            CheckESPStatus($con, $domain, $mailer, $elasticapikey, $subusername);
//                        } elseif ($mailer == 'le.transport.sendgrid_api' && strpos($errormsg, 'The provided authorization grant is invalid, expired, or revoked') !== false) {
//                            CheckESPStatus($con, $domain, $mailer, $elasticapikey, $subusername);
//                        }
//                    }
//                }
            }
            displayCronlog($domain, $domain.' : '.$command);
            displayCronlog($domain, 'Command Results:'.$output);
            displayCronlog($domain, "This operation ($operation) for ($domain) is Completed.");
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
    $process->setTimeout(null);
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

function isValidCronExecution($con, $domain, $dbname, $appid, $operation)
{
    if (!isActiveCustomer($con, $domain, $dbname)) {
        displayCronlog($domain, "This $domain is InActive");

        return false;
    }
    $status = true;
    if ($operation == 'le:import') {
        $status = checkImportAvailablity($con, $dbname);
    } elseif ($operation == 'le:campaigns:trigger') {
        $status = checkTriggerAvailablity($con, $dbname);
    } elseif ($operation == 'le:emails:send') {
        $status = checkEmailAvailablity($domain);
    } elseif ($operation == 'le:segments:update') {
        $status = checkSegmentAvailablity($con, $dbname);
    } elseif ($operation == 'le:reports:scheduler') {
        $status = checkReportSchedulerAvailablity($con, $dbname);
    } elseif ($operation == 'le:payment:update') {
        $status = checkPaymentAvailablity($con, $dbname);
    } elseif ($operation == 'le:dripemail:send') {
        $status = checkDripEmailAvailablity($con, $dbname);
    } elseif ($operation == 'le:oneoff:send') {
        $status = checkBroadcastEmailRebuildAvailablity($con, $dbname);
    } elseif ($operation == 'le:dripemail:rebuild') {
        $status = checkDripEmailRebuildAvailablity($con, $dbname);
    } elseif ($operation == 'le:listoptin:resend') {
        $status = checkListResentAvailablity($con, $dbname);
    } elseif ($operation == 'le:statemachine:checker') {
        $status = checkStateMachineAvailablity($con, $dbname);
    }

    return $status;
}

function isActiveCustomer($con, $domain, $dbname)
{
    $statetable  = $dbname.'.statemachine';
    $sql         = "select * from $statetable where state in ('Customer_Active','Trial_Active','Trial_Sending_Domain_Not_Configured','Customer_Sending_Domain_Not_Configured') and isalive = '1'";
    $result      = getResultArray($con, $sql);
    if (sizeof($result) > 0) {
        return true;
    } else {
        return false;
    }
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
        displayCronlog($domain, 'Email File Count from Spool:'.$filecount);
        if ($filecount > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function checkImportAvailablity($con, $dbname)
{
    $importtable = $dbname.'.imports';
    $sql         = "select count(*) from $importtable where is_published = 1 and status in ('1','2')";
    $result      = getResultArray($con, $sql);
    $count       = $result[0][0];
    displayCronlog('general', 'Import Live Count:'.$count);
    if ($count > 0) {
        return true;
    } else {
        return false;
    }
}

function checkTriggerAvailablity($con, $dbname)
{
    $campaigntable = $dbname.'.campaigns';
    $sql           = "select id from $campaigntable where is_published = 1";
    $result        = getResultArray($con, $sql);
    $count         = sizeof($result);
    displayCronlog('general', 'Campaign Published Count:'.$count);
    if ($count > 0) {
        for ($i = 0; $i < $count; ++$i) {
            $id          = $result[$i][0];
            $tablename   = $dbname.'.campaign_leads';
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

function checkLicenseAvailablity($con, $dbname, $appid)
{
    $licensetable       =  $dbname.'.licenseinfo';
    $sql                =  "select licensed_days,license_end_date  from $licensetable";
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

function checkSegmentAvailablity($con, $dbname)
{
    $segmenttable = $dbname.'.lead_lists';
    $sql          = "select filters from $segmenttable where is_published = 1";
    $result       = getResultArray($con, $sql);
    if (count($result) > 0) {
        for ($i = 0; $i < sizeof($result); ++$i) {
            $filters = $result[$i][0];
            $filters = unserialize($filters);
            if (sizeof($filters) > 0) {
                return true;
            }
        }

        return false;
    } else {
        return false;
    }
}

function checkReportSchedulerAvailablity($con, $dbname)
{
    $reporttable = $dbname.'.reports_schedulers';
    $currenttime = date('Y-m-d H:i:s');
    $sql         = "select * from $reporttable <= $currenttime";
    $result      = getResultArray($con, $sql);
    if (count($result) > 0) {
        return true;
    } else {
        return false;
    }
}

function checkPaymentAvailablity($con, $dbname)
{
    $paymenttable = $dbname.'.paymenthistory';
    $statetable   = $dbname.'.statemachine';
    $sql          = "select * from $statetable where state = 'Customer_Inactive_Exit_Cancel' and isalive = 1";
    $result       = getResultArray($con, $sql);
    if (count($result) > 0) {
        return false;
    } else {
        $sql         = "select * from $paymenttable";
        $result      = getResultArray($con, $sql);
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }
}

function checkDripEmailAvailablity($con, $dbname)
{
    $currentDate       = date('Y-m-d H:i:s');
    $driplogtable      = $dbname.'.dripemail_lead_event_log';
    $sql               = "select * from $driplogtable where trigger_date <= '$currentDate'";
    $result            = getResultArray($con, $sql);
    if (count($result) > 0) {
        return true;
    } else {
        return false;
    }
}

function checkDripEmailRebuildAvailablity($con, $dbname)
{
    $driptable   = $dbname.'.dripemail';
    $sql         = "select * from $driptable where is_scheduled = 1;";
    $result      = getResultArray($con, $sql);
    if (count($result) > 0) {
        return true;
    } else {
        return false;
    }
}

function checkBroadcastEmailRebuildAvailablity($con, $dbname)
{
    $emailtable  = $dbname.'.emails';
    $sql         = "select * from $emailtable where is_scheduled = 1;";
    $result      = getResultArray($con, $sql);
    if (count($result) > 0) {
        return true;
    } else {
        return false;
    }
}
function checkListResentAvailablity($con, $dbname)
{
    $dateinterval   = date('Y-m-d H:i:s', strtotime('-2 day'));
    $listoptintable = $dbname.'.lead_listoptin_leads';
    $sql            = "select * from $listoptintable where date_added <= '$dateinterval' and isrescheduled = 1";
    $result         = getResultArray($con, $sql);
    if (count($result) > 0) {
        return true;
    } else {
        return false;
    }
}
function checkStateMachineAvailablity($con, $dbname)
{
    $statetable  = $dbname.'.statemachine';
    $sql         = "select * from $statetable where state = 'Customer_Inactive_Archive' and isalive = 1";
    $result      = getResultArray($con, $sql);
    if (count($result) > 0) {
        return false;
    } else {
        return true;
    }
}
