<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SubscriptionBundle\Helper;

use Mautic\CoreBundle\Factory\MauticFactory;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

/**
 * Class PaymentHelper.
 */
class PaymentHelper
{
    /**
     * @var MauticFactory
     */
    protected $factory;

    public function __construct(MauticFactory $factory)
    {
        $this->factory   = $factory;
    }

    public function getPayPalApiContext()
    {
        $clientid        =$this->factory->getParameter('paypal_clientid');
        $clientsecret    =$this->factory->getParameter('paypal_clientsecret');
        $paypalmode      =$this->factory->getParameter('paypal_mode');
        $paypallogpath   =$this->factory->getParameter('paypal_logpath');
        $paypalloglevel  =$this->factory->getParameter('paypal_loglevel');
        $paypallogenabled=$this->factory->getParameter('paypal_log_enabled');
        $paypalcachepath =$this->factory->getParameter('paypal_cachepath');
        $paypalrootpath  =$this->factory->getParameter('paypal_rootpath');
        if (!is_dir($paypalrootpath) && !file_exists($paypalrootpath)) {
            mkdir($paypalrootpath, 0777);
        }
        if (!is_dir($paypallogpath) && !file_exists($paypallogpath)) {
            mkdir($paypallogpath, 0777);
        }
        if (!is_dir($paypalcachepath) && !file_exists($paypalcachepath)) {
            mkdir($paypalcachepath, 0777);
        }
        $dataArray['provider'] ='paypal';
        $apiContext            = new ApiContext(
            new OAuthTokenCredential(
                $clientid,
                $clientsecret
            )
        );
        $apiContext->setConfig(
            [
                'mode'           => $paypalmode,
                'log.LogEnabled' => $paypallogenabled,
                'log.FileName'   => $paypallogpath.'/paypal.log',
                'log.LogLevel'   => $paypalloglevel, // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled'  => true,
                'cache.FileName' => $paypalcachepath.'/auth.cache', // for determining paypal cache directory
                // 'http.CURLOPT_CONNECTTIMEOUT' => 30
                // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
                //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
            ]
        );

        return $apiContext;
    }

    public function getUUIDv4()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * @param User $user
     */
    public function sendPaymentNotification($paymenthistory, $billing, $mailer)
    {
        $mailer->start();
        $invoicelink  = $this->factory->getRouter()->generate('mautic_viewinvoice_action', ['id' => $paymenthistory->getId()], true);
        $message      = \Swift_Message::newInstance();
        $message->setTo([$billing->getAccountingemail() => $billing->getCompanyname()]);
        $message->setFrom(['support@lemailer3.com' => 'LeadsEngage']);
        $message->setReplyTo(['support@leadsengage.com' => 'LeadsEngage']);
        $message->setBcc(['sales@leadsengage.com' => 'LeadsEngage']);
        $message->setSubject($this->factory->getTranslator()->trans('le.payment.received.alert'));
        $datehelper =$this->factory->getDateHelper();
        $processedat=$datehelper->toDate($paymenthistory->getcreatedOn());

        $text = "<!DOCTYPE html>
<html>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>

	<head>
		<title></title>
		<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css'>
	</head>
	<body aria-disabled='false' style='min-height: 300px;margin:0px;'>
		<div style='background-color:#eff2f7'>
			<div style='padding-top: 55px;'>
				<div class='marle' style='margin: 0% 11.5%;background-color:#fff;padding: 50px 50px 50px 50px;border-bottom:5px solid #ec407a;'>

					<p style='text-align:center;'><img src='https://leadsengage.com/wp-content/uploads/leadsengage/leadsengage_logo-black.png' class='fr-fic fr-dii' height='40'></p>
					<br>
					<div style='text-align:center;width:100%;'>
						<div style='display:inline-block;width: 80%;'>

							<p style='text-align:left;font-size:14px;font-family: Montserrat,sans-serif;'>Dear ".$billing->getCompanyname().",</p>

							<p style='text-align:left;font-size:14px;line-height: 30px;font-family: Montserrat,sans-serif;'>Payment of <b>$".$paymenthistory->getNetamount().'</b> has been processed on <b>'.$processedat."</b> for LeadsEngage's Monthly Subscription.You can download the Invoice in your account.
</p><a href=\"$invoicelink\" class='butle' style='text-align:center;text-decoration:none;font-family: Montserrat,sans-serif;transition: all .1s ease;color: #fff;font-weight: 400;font-size: 18px;margin-top: 10px;font-family: Montserrat,sans-serif;display: inline-block;letter-spacing: .6px;padding: 15px 30px;box-shadow: 0 1px 2px rgba(0,0,0,.36);white-space: nowrap;border-radius: 35px;background-color: #ec407a;border: #0071ff;'>View Invoice</a>
							<br>

							<p style='text-align:left;font-size:14px;line-height: 30px;font-family: Montserrat,sans-serif;'>Contact <a href='mailto:support@leadsengage.com'>support@leadsengage.com</a> for any clarification</p>
							<p style='text-align:left;font-size:14px;font-family: Montserrat,sans-serif;'>Thank you for your business!</p>
							<p style='text-align:left;font-size:14px;font-family: Montserrat,sans-serif;'> The <a href='https://leadsengage.com/'>LeadsEngage</a> Team</p>
						</div>
					</div>
				</div>
				<br>
				<br>
				<br>
			</div>
		</div>
		
	</body>
</html>";
        //$html = nl2br($text);

        $message->setBody($text, 'text/html');
        //$mailer->setPlainText(strip_tags($text));

        $mailer->send($message);
    }
}
