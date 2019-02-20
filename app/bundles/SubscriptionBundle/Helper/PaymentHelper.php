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
        $invoicelink  = $this->factory->getRouter()->generate('le_viewinvoice_action', ['id' => $paymenthistory->getId()], true);
        $message      = \Swift_Message::newInstance();
        $message->setTo([$billing->getAccountingemail() => $billing->getCompanyname()]);
        $message->setFrom(['notifications@leadsengage.com' => 'LeadsEngage']);
        $message->setReplyTo(['support@leadsengage.com' => 'LeadsEngage']);
        $message->setBcc(['sales@leadsengage.com' => 'LeadsEngage']);
        $message->setSubject($this->factory->getTranslator()->trans('le.payment.received.alert'));
        $datehelper =$this->factory->getDateHelper();
        $processedat=$datehelper->toDate($paymenthistory->getcreatedOn());
        $user       = $this->factory->getUser();
        $firstname  = $billing->getCompanyname();
        if ($user != null) {
            $firstname = $user->getFirstName();
        }
        $amount = $paymenthistory->getNetamount();

        $text = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional //EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<!-- saved from url=(0048)https://cops.leadsengage.com/one-off/preview/247 -->
<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\" class=\"gr__cops_leadsengage_com\"><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"><!--[if gte mso 9]><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]--><meta name=\"viewport\" content=\"width=device-width\"><!--[if !mso]><!--><meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"><!--<![endif]--><title>04. Payment Success</title><!--[if !mso]><!--><!--<![endif]--><style type=\"text/css\">
		body {
			margin: 0;
			padding: 0;
		}

		table,
		td,
		tr {
			vertical-align: top;
			border-collapse: collapse;
		}

		* {
			line-height: inherit;
		}

		a[x-apple-data-detectors=true] {
			color: inherit !important;
			text-decoration: none !important;
		}

		.ie-browser table {
			table-layout: fixed;
		}

		[owa] .img-container div,
		[owa] .img-container button {
			display: block !important;
		}

		[owa] .fullwidth button {
			width: 100% !important;
		}

		[owa] .block-grid .col {
			display: table-cell;
			float: none !important;
			vertical-align: top;
		}

		.ie-browser .block-grid,
		.ie-browser .num12,
		[owa] .num12,
		[owa] .block-grid {
			width: 620px !important;
		}

		.ie-browser .mixed-two-up .num4,
		[owa] .mixed-two-up .num4 {
			width: 204px !important;
		}

		.ie-browser .mixed-two-up .num8,
		[owa] .mixed-two-up .num8 {
			width: 408px !important;
		}

		.ie-browser .block-grid.two-up .col,
		[owa] .block-grid.two-up .col {
			width: 306px !important;
		}

		.ie-browser .block-grid.three-up .col,
		[owa] .block-grid.three-up .col {
			width: 306px !important;
		}

		.ie-browser .block-grid.four-up .col [owa] .block-grid.four-up .col {
			width: 153px !important;
		}

		.ie-browser .block-grid.five-up .col [owa] .block-grid.five-up .col {
			width: 124px !important;
		}

		.ie-browser .block-grid.six-up .col,
		[owa] .block-grid.six-up .col {
			width: 103px !important;
		}

		.ie-browser .block-grid.seven-up .col,
		[owa] .block-grid.seven-up .col {
			width: 88px !important;
		}

		.ie-browser .block-grid.eight-up .col,
		[owa] .block-grid.eight-up .col {
			width: 77px !important;
		}

		.ie-browser .block-grid.nine-up .col,
		[owa] .block-grid.nine-up .col {
			width: 68px !important;
		}

		.ie-browser .block-grid.ten-up .col,
		[owa] .block-grid.ten-up .col {
			width: 60px !important;
		}

		.ie-browser .block-grid.eleven-up .col,
		[owa] .block-grid.eleven-up .col {
			width: 54px !important;
		}

		.ie-browser .block-grid.twelve-up .col,
		[owa] .block-grid.twelve-up .col {
			width: 50px !important;
		}
	</style><style type=\"text/css\" id=\"media-query\">
		@media only screen and (min-width: 640px) {
			.block-grid {
				width: 620px !important;
			}

			.block-grid .col {
				vertical-align: top;
			}

			.block-grid .col.num12 {
				width: 620px !important;
			}

			.block-grid.mixed-two-up .col.num3 {
				width: 153px !important;
			}

			.block-grid.mixed-two-up .col.num4 {
				width: 204px !important;
			}

			.block-grid.mixed-two-up .col.num8 {
				width: 408px !important;
			}

			.block-grid.mixed-two-up .col.num9 {
				width: 459px !important;
			}

			.block-grid.two-up .col {
				width: 310px !important;
			}

			.block-grid.three-up .col {
				width: 206px !important;
			}

			.block-grid.four-up .col {
				width: 155px !important;
			}

			.block-grid.five-up .col {
				width: 124px !important;
			}

			.block-grid.six-up .col {
				width: 103px !important;
			}

			.block-grid.seven-up .col {
				width: 88px !important;
			}

			.block-grid.eight-up .col {
				width: 77px !important;
			}

			.block-grid.nine-up .col {
				width: 68px !important;
			}

			.block-grid.ten-up .col {
				width: 62px !important;
			}

			.block-grid.eleven-up .col {
				width: 56px !important;
			}

			.block-grid.twelve-up .col {
				width: 51px !important;
			}
		}

		@media (max-width: 640px) {

			.block-grid,
			.col {
				min-width: 320px !important;
				max-width: 100% !important;
				display: block !important;
			}

			.block-grid {
				width: 100% !important;
			}

			.col {
				width: 100% !important;
			}

			.col>div {
				margin: 0 auto;
			}

			img.fullwidth,
			img.fullwidthOnMobile {
				max-width: 100% !important;
			}

			.no-stack .col {
				min-width: 0 !important;
				display: table-cell !important;
			}

			.no-stack.two-up .col {
				width: 50% !important;
			}

			.no-stack.mixed-two-up .col.num4 {
				width: 33% !important;
			}

			.no-stack.mixed-two-up .col.num8 {
				width: 66% !important;
			}

			.no-stack.three-up .col.num4 {
				width: 33% !important;
			}

			.no-stack.four-up .col.num3 {
				width: 25% !important;
			}

			.mobile_hide {
				min-height: 0px;
				max-height: 0px;
				max-width: 0px;
				display: none;
				overflow: hidden;
				font-size: 0px;
			}
		}
	</style><link rel=\"icon\" type=\"image/x-icon\" href=\"https://cops.leadsengage.com/media/images/favicon.ico\"><link rel=\"icon\" sizes=\"192x192\" href=\"https://cops.leadsengage.com/media/images/favicon.ico\"><link rel=\"apple-touch-icon\" href=\"https://cops.leadsengage.com/media/images/favicon.ico\"><script style=\"display: none;\">var tvt = tvt || {}; tvt.captureVariables = function(a){for(var b=
new Date,c={},d=Object.keys(a||{}),e=0,f;f=d[e];e++)if(a.hasOwnProperty(f)&&\"undefined\"!=typeof a[f])try{var g=[];c[f]=JSON.stringify(a[f],function(a,b){try{if(\"function\"!==typeof b){if(\"object\"===typeof b&&null!==b){if(b instanceof HTMLElement||b instanceof Node||-1!=g.indexOf(b))return;g.push(b)}return b}}catch(H){}})}catch(l){}a=document.createEvent(\"CustomEvent\");a.initCustomEvent(\"TvtRetrievedVariablesEvent\",!0,!0,{variables:c,date:b});window.dispatchEvent(a)};window.setTimeout(function() {tvt.captureVariables({'dataLayer.hide': (function(a){a=a.split(\".\");for(var b=window,c=0;c<a.length&&(b=b[a[c]],b);c++);return b})('dataLayer.hide'),'gaData': window['gaData'],'dataLayer': window['dataLayer']})}, 2000);</script></head><body class=\"clean-body\" style=\"margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #FFFFFF;\" data-gr-c-s-loaded=\"true\">
<style type=\"text/css\" id=\"media-query-bodytag\">
@media (max-width: 640px) {
  .block-grid {
    min-width: 320px!important;
    max-width: 100%!important;
    width: 100%!important;
    display: block!important;
  }
  .col {
    min-width: 320px!important;
    max-width: 100%!important;
    width: 100%!important;
    display: block!important;
  }
  .col > div {
    margin: 0 auto;
  }
  img.fullwidth {
    max-width: 100%!important;
    height: auto!important;
  }
  img.fullwidthOnMobile {
    max-width: 100%!important;
    height: auto!important;
  }
  .no-stack .col {
    min-width: 0!important;
    display: table-cell!important;
  }
  .no-stack.two-up .col {
    width: 50%!important;
  }
  .no-stack.mixed-two-up .col.num4 {
    width: 33%!important;
  }
  .no-stack.mixed-two-up .col.num8 {
    width: 66%!important;
  }
  .no-stack.three-up .col.num4 {
    width: 33%!important
  }
  .no-stack.four-up .col.num3 {
    width: 25%!important
  }
}
</style><!--[if IE]><div class=\"ie-browser\"><![endif]--><table class=\"nl-container\" style=\"table-layout: fixed; vertical-align: top; min-width: 320px; Margin: 0 auto; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #FFFFFF; width: 100%;\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" bgcolor=\"#FFFFFF\" valign=\"top\"><tbody><tr style=\"vertical-align: top;\" valign=\"top\"><td style=\"word-break: break-word; vertical-align: top; border-collapse: collapse;\" valign=\"top\">
					<!--[if (mso)|(IE)]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td align=\"center\" style=\"background-color:#FFFFFF\"><![endif]-->
					<div style=\"background-color:transparent;\">
						<div class=\"block-grid \" style=\"Margin: 0 auto; min-width: 320px; max-width: 620px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;;\">
							<div style=\"border-collapse: collapse;display: table;width: 100%;background-color:transparent;\">
								<!--[if (mso)|(IE)]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\"><tr><td align=\"center\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:620px\"><tr class=\"layout-full-width\" style=\"background-color:transparent\"><![endif]-->
								<!--[if (mso)|(IE)]><td align=\"center\" width=\"620\" style=\"background-color:transparent;width:620px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\"><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"padding-right: 10px; padding-left: 10px; padding-top:5px; padding-bottom:5px;\"><![endif]-->
								<div class=\"col num12\" style=\"min-width: 320px; max-width: 620px; display: table-cell; vertical-align: top;;\">
									<div style=\"width:100% !important;\">
										<!--[if (!mso)&(!IE)]><!-->
										<div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 10px; padding-left: 10px;\">
											<!--<![endif]-->
											<div class=\"img-container left fixedwidth\" align=\"left\" style=\"padding-right: 0px;padding-left: 0px;\">
												<!--[if mso]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr style=\"line-height:0px\"><td style=\"padding-right: 0px;padding-left: 0px;\" align=\"left\"><![endif]-->
												<div style=\"font-size:1px;line-height:15px\">&nbsp;</div><a href=\"https://leadsengage.com/\" target=\"_blank\"> <img class=\"left fixedwidth\" align=\"left\" border=\"0\" src=\"https://leadsengage.com/wp-content/uploads/leproduct/leadsengage_logo-black.png\" alt=\"Marketing Automation Software | LeadsEngage\" title=\"Marketing Automation Software | LeadsEngage\" style=\"outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; clear: both; height: auto; float: none; border: none; width: 100%; max-width: 120px; display: block;\" width=\"120\"></a>
												<div style=\"font-size:1px;line-height:15px\">&nbsp;</div>
												<!--[if mso]></td></tr></table><![endif]-->
											</div>
											<!--[if mso]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"padding-right: 30px; padding-left: 0px; padding-top: 15px; padding-bottom: 15px; font-family: Georgia, 'Times New Roman', serif\"><![endif]-->
											<div style=\"color:#555555;font-family:Georgia, Times, &#39;Times New Roman&#39;, serif;line-height:150%;padding-top:15px;padding-right:30px;padding-bottom:15px;padding-left:0px;\">
												<div style=\"font-size: 12px; line-height: 18px; font-family: Georgia, Times, &#39;Times New Roman&#39;, serif; color: #555555;\">
													<p style=\"font-size: 14px; line-height: 21px; margin: 0;\"><span style=\"line-height: 21px; font-size: 14px;\">Hi $firstname,</span></p>
													<p style=\"font-size: 14px; line-height: 21px; margin: 0;\">&nbsp;</p>
													<p style=\"font-size: 14px; line-height: 21px; margin: 0;\">We received your payment <strong>$$amount</strong> on <strong>$processedat,</strong> for LeadsEngage software subscription.</p>
													<p style=\"font-size: 14px; line-height: 21px; margin: 0;\"><br>This payment information has been updated in your account, and you can download the Invoice any time from payments history tab in account settings.</p>
												</div>
											</div>
											<!--[if mso]></td></tr></table><![endif]-->
											<div class=\"button-container\" align=\"left\" style=\"padding-top:0px;padding-right:10px;padding-bottom:0px;padding-left:0px;\">
												<!--[if mso]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"border-spacing: 0; border-collapse: collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;\"><tr><td style=\"padding-top: 0px; padding-right: 10px; padding-bottom: 0px; padding-left: 0px\" align=\"left\"><v:roundrect xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:w=\"urn:schemas-microsoft-com🏢word\" href=\"\" style=\"height:36pt; width:132.75pt; v-text-anchor:middle;\" arcsize=\"11%\" stroke=\"false\" fillcolor=\"#3292e0\"><w:anchorlock/><v:textbox inset=\"0,0,0,0\"><center style=\"color:#ffffff; font-family:Georgia, 'Times New Roman', serif; font-size:16px\"><![endif]-->
												<div style=\"text-decoration:none;display:inline-block;color:#ffffff;background-color:#3292e0;border-radius:5px;-webkit-border-radius:5px;-moz-border-radius:5px;width:auto; width:auto;;border-top:1px solid #3292e0;border-right:1px solid #3292e0;border-bottom:1px solid #3292e0;border-left:1px solid #3292e0;padding-top:10px;padding-bottom:10px;font-family:Georgia, Times, &#39;Times New Roman&#39;, serif;text-align:center;mso-border-alt:none;word-break:keep-all;\"><span style=\"padding-left:35px;padding-right:35px;font-size:16px;display:inline-block;\">
														<a href='$invoicelink' style=\"font-size: 16px; line-height: 28px;text-decoration: none;color: white;\">View Invoice</a>
													</span></div>
												<!--[if mso]></center></v:textbox></v:roundrect></td></tr></table><![endif]-->
											</div>
											<!--[if mso]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"padding-right: 30px; padding-left: 0px; padding-top: 15px; padding-bottom: 15px; font-family: Georgia, 'Times New Roman', serif\"><![endif]-->
											<div style=\"color:#555555;font-family:Georgia, Times, &#39;Times New Roman&#39;, serif;line-height:150%;padding-top:15px;padding-right:30px;padding-bottom:15px;padding-left:0px;\">
												<div style=\"font-size: 12px; line-height: 18px; font-family: Georgia, Times, &#39;Times New Roman&#39;, serif; color: #555555;\">
													<p style=\"font-size: 12px; line-height: 18px; margin: 0;\"><span style=\"font-size: 14px; line-height: 21px;\">Contact&nbsp;<a style=\"text-decoration: underline; color: #0068A5;\" title=\"support@leadsengage.com\" href=\"mailto:support@leadsengage.com\">support@leadsengage.com</a>&nbsp;for any clarification.</span></p>
													<p style=\"font-size: 12px; line-height: 18px; margin: 0;\">&nbsp;</p>
													<p style=\"font-size: 12px; line-height: 18px; margin: 0;\"><span style=\"font-size: 14px; line-height: 21px;\">Thanks for your Business!<br>The<span style=\"font-size: 14px; line-height: 21px;\"> LeadsEngage</span> Team.</span></p>
												</div>
											</div>
											<!--[if mso]></td></tr></table><![endif]-->
											<table class=\"divider\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><tbody><tr style=\"vertical-align: top;\" valign=\"top\"><td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 20px; padding-right: 20px; padding-bottom: 20px; padding-left: 20px; border-collapse: collapse;\" valign=\"top\">
															<table class=\"divider_content\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; border-top: 3px solid #3292e0; height: 0px;\" align=\"center\" height=\"0\" valign=\"top\"><tbody><tr style=\"vertical-align: top;\" valign=\"top\"><td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; border-collapse: collapse;\" height=\"0\" valign=\"top\"><span></span></td>
																	</tr></tbody></table></td>
													</tr></tbody></table><!--[if (!mso)&(!IE)]><!--></div>
										<!--<![endif]-->
									</div>
								</div>
								<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
								<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
							</div>
						</div>
					</div>
					<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
				</td>
			</tr></tbody></table><!--[if (IE)]></div><![endif]-->
</body><div><div class=\"gr_-editor gr-iframe-first-load\" style=\"display: none;\"><div class=\"gr_-editor_back\"></div><iframe class=\"gr_-ifr gr-_dialog-content\" src=\"./04. Payment Success_files/saved_resource.html\"></iframe></div></div><grammarly-card><div></div></grammarly-card><span class=\"gr__tooltip\"><span class=\"gr__tooltip-content\"></span><i class=\"gr__tooltip-logo\"></i><span class=\"gr__triangle\"></span></span></html>";
        //$html = nl2br($text);

        $message->setBody($text, 'text/html');
        //$mailer->setPlainText(strip_tags($text));

        $mailer->send($message);
    }
}
