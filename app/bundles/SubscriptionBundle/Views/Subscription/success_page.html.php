<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('leContent', 'lead');
$view['slots']->set('headerTitle', $view['translator']->trans('le.success.offer'));

if ($showsetup) {
    echo $view->render('MauticSubscriptionBundle:Subscription:kyc.html.php',
        [
            'typePrefix' => 'email',
            'form'       => $accountform,
            'billform'   => $billingform,
            'userform'   => $userform,
            'videoURL'   => $videoURL,
            'showSetup'  => $showsetup,
            'showVideo'  => $showvideo,
            'isMobile'   => $isMobile,
        ]);
}
$emailproviderimg   = 'redtick.png';
$websitetrackingimg = 'redtick.png';
$segmentcreatedimg  = 'redtick.png';
$importimg          = 'redtick.png';
$campaignimg        = 'redtick.png';
if ($isProviderChanged) {
    $emailproviderimg = 'greentick.png';
}
if ($isWebsiteTracking) {
    $websitetrackingimg = 'greentick.png';
}
if ($isSegmentCreated) {
    $segmentcreatedimg = 'greentick.png';
}
if ($isCampaignCreated) {
    $campaignimg = 'greentick.png';
}
if ($isImportDone) {
    $importimg = 'greentick.png';
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office"><head>
    <!--[if gte mso 9]><xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml><![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width" />
    <!--[if !mso]><!--><meta http-equiv="X-UA-Compatible" content="IE=edge" /><!--<![endif]-->
    <title></title>


    <style type="text/css" id="media-query">
        body {
            margin: 0;
            padding: 0;
            font-family:Open Sans, Arial, Montserrat;
        }

        table, tr, td {
            vertical-align: top;
            border-collapse: collapse; }

        .ie-browser table, .mso-container table {
            table-layout: fixed; }

        * {
            line-height: inherit; }

        a[x-apple-data-detectors=true] {
            color: inherit !important;
            text-decoration: none !important; }

        [owa] .img-container div, [owa] .img-container button {
            display: block !important; }

        [owa] .fullwidth button {
            width: 100% !important; }

        [owa] .block-grid .col {
            display: table-cell;
            float: none !important;
            vertical-align: top; }

        .ie-browser .num12, .ie-browser .block-grid, [owa] .num12, [owa] .block-grid {
            width: 900px !important; }

        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {
            line-height: 100%; }

        .ie-browser .mixed-two-up .num4, [owa] .mixed-two-up .num4 {
            width: 300px !important; }

        .ie-browser .mixed-two-up .num8, [owa] .mixed-two-up .num8 {
            width: 600px !important; }

        .ie-browser .block-grid.two-up .col, [owa] .block-grid.two-up .col {
            width: 450px !important; }

        .ie-browser .block-grid.three-up .col, [owa] .block-grid.three-up .col {
            width: 300px !important; }

        .ie-browser .block-grid.four-up .col, [owa] .block-grid.four-up .col {
            width: 225px !important; }

        .ie-browser .block-grid.five-up .col, [owa] .block-grid.five-up .col {
            width: 180px !important; }

        .ie-browser .block-grid.six-up .col, [owa] .block-grid.six-up .col {
            width: 150px !important; }

        .ie-browser .block-grid.seven-up .col, [owa] .block-grid.seven-up .col {
            width: 128px !important; }

        .ie-browser .block-grid.eight-up .col, [owa] .block-grid.eight-up .col {
            width: 112px !important; }

        .ie-browser .block-grid.nine-up .col, [owa] .block-grid.nine-up .col {
            width: 100px !important; }

        .ie-browser .block-grid.ten-up .col, [owa] .block-grid.ten-up .col {
            width: 90px !important; }

        .ie-browser .block-grid.eleven-up .col, [owa] .block-grid.eleven-up .col {
            width: 81px !important; }

        .ie-browser .block-grid.twelve-up .col, [owa] .block-grid.twelve-up .col {
            width: 75px !important; }

        @media only screen and (min-width: 920px) {
            .block-grid .col {
                vertical-align: top; }
            .block-grid .col.num12 {
                width: 900px !important; }
            .block-grid.mixed-two-up .col.num4 {
                width: 300px !important; }
            .block-grid.mixed-two-up .col.num8 {
                width: 600px !important; }
            .block-grid.two-up .col {
                width: 450px !important; }
            .block-grid.three-up .col {
                width: 300px !important; }
            .block-grid.four-up .col {
                width: 225px !important; }
            .block-grid.five-up .col {
                width: 180px !important; }
            .block-grid.six-up .col {
                width: 150px !important; }
            .block-grid.seven-up .col {
                width: 128px !important; }
            .block-grid.eight-up .col {
                width: 112px !important; }
            .block-grid.nine-up .col {
                width: 100px !important; }
            .block-grid.ten-up .col {
                width: 90px !important; }
            .block-grid.eleven-up .col {
                width: 81px !important; }
            .block-grid.twelve-up .col {
                width: 75px !important; } }

        @media (max-width: 920px) {
            .col {
                width: 100% !important; }
            .col > div {
                margin: 0 auto; }
            img.fullwidth, img.fullwidthOnMobile {
                max-width: 100% !important; }
            .no-stack .col {
                min-width: 0 !important;
                display: table-cell !important; }
            .no-stack.two-up .col {
                width: 50% !important; }
            .no-stack.mixed-two-up .col.num4 {
                width: 33% !important; }
            .no-stack.mixed-two-up .col.num8 {
                width: 66% !important; }
            .no-stack.three-up .col.num4 {
                width: 33% !important; }
            .no-stack.four-up .col.num3 {
                width: 25% !important; }
            .mobile_hide {
                min-height: 0px;
                max-height: 0px;
                max-width: 0px;
                display: none;
                overflow: hidden;
                font-size: 0px; } }

    </style>
</head>
<body class="clean-body" style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;background-color: #FFFFFF">
<style type="text/css" id="media-query-bodytag">
    @media (max-width: 520px) {
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
        }
        img.fullwidthOnMobile {
            max-width: 100%!important;
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
            width: 33%!important;
        }
        .no-stack.four-up .col.num3 {
            width: 25%!important;
        }
        .mobile_hide {
            min-height: 0px!important;
            max-height: 0px!important;
            max-width: 0px!important;
            display: none!important;
            overflow: hidden!important;
            font-size: 0px!important;
        }
    }
    .box1{
        width:30%!important;
        padding:25px;
        border-top:2px solid #e7e7e7;
        border-left:2px solid #e7e7e7;
        border-bottom:2px solid #e7e7e7

    }
    .box2{
        width:70%!important;
        padding:25px;
        border:2px solid #e7e7e7

    }
</style>
<!--[if IE]><div class="ie-browser"><![endif]-->
<!--[if mso]><div class="mso-container"><![endif]-->
<table class="nl-container" style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 320px;Margin: 0 auto;background-color: #FFFFFF;width: 100%" cellpadding="0" cellspacing="0">
    <tbody>
    <tr style="vertical-align: top">
        <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top">
            <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" style="background-color: #FFFFFF;"><![endif]-->

            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 90%;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;" class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="background-color:transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width: 900px;"><tr class="layout-full-width" style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]><td align="center" width="900" style=" width:900px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><![endif]-->
                        <div class="col num12" style="min-width: 320px;max-width: 900px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!--><div style="border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;"><!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 25px; padding-left: 25px; padding-top: 50px; padding-bottom: 10px;"><![endif]-->
                                        <div style="color:#555555;font-family:Open Sans, Helvetica, Roboto, Arial;line-height:200%; padding-right: 25px; padding-left: 25px;  padding-bottom: 10px;">
                                            <div style="font-size:12px;line-height:24px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;text-align:left;"><p style="margin: 0;font-size: 14px;line-height: 28px"><span style="font-size: 22px; line-height: 44px;">Hey, Welcome!</span></p><p style="margin: 0;font-size: 14px;line-height: 28px"><span style="font-size: 14px; line-height: 28px;">LeadsEngage is build to help you automate your marketing process and grow your business faster. Here the LeadsEngage Product Overview &amp; 5 quick steps to create your first campaign.</span></p></div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>
            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 90%;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;" class="block-grid mixed-two-up ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="background-color:transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width: 900px;"><tr class="layout-full-width" style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]><td align="center" width="596" style=" width:596px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 2px solid #E7E7E7; border-left: 2px solid #E7E7E7; border-bottom: 2px solid #E7E7E7; border-right: 2px solid #E7E7E7;" valign="top"><![endif]-->
                        <div class="col num4" style="display: table-cell;vertical-align: top;min-width: 320px;max-width: 600px;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!--><div style="border-top: 2px solid #E7E7E7; border-left: 2px solid #E7E7E7; border-bottom: 2px solid #E7E7E7; border-right: 2px solid #E7E7E7; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;"><!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 20px; padding-bottom: 20px;"><![endif]-->
                                        <div style="color:#555555;font-family:Open Sans, Helvetica, Roboto, Arial;line-height:120%; padding-right: 10px; padding-left: 10px; padding-top: 20px; padding-bottom: 20px;">
                                            <div style="font-size:12px;line-height:14px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;text-align:left;"><p style="margin: 0;font-size: 14px;line-height: 17px;text-align: center"><strong><span style="font-size: 17px; line-height: 20px;"><span style="line-height: 20px; font-size: 17px;">Here a crisp 10 min video to help you get started</span></span></strong></p></div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>


                                    <div class="" style="font-size: 16px;font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif; text-align: center;"><iframe width="560" height="320" src="https://www.youtube.com/embed/r0vo8M8Ei7E?rel=0&showinfo=0&ecver=1&enablejsapi=1" frameborder="0"></iframe></div>


                                    <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td><td align="center" width="298" style=" width:298px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 2px solid #E7E7E7; border-left: 0px solid #E7E7E7; border-bottom: 2px solid #E7E7E7; border-right: 2px solid #E7E7E7;" valign="top"><![endif]-->
                        <div class="col num4" style="display: table-cell;vertical-align: top;max-width: 320px;min-width: 300px;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!--><div style="border-top: 2px solid #E7E7E7; border-left: 0px solid #E7E7E7; border-bottom: 2px solid #E7E7E7; border-right: 2px solid #E7E7E7; padding-top:5px; padding-bottom:22px; padding-right: 0px; padding-left: 0px;"><!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;"><![endif]-->
                                        <div style="color:#555555;font-family:Open Sans, Helvetica, Roboto, Arial;line-height:150%; padding-right: 10px; padding-left: 10px; padding-top: 30px; padding-bottom: 10px;">
                                            <div style="font-size:12px;line-height:18px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;text-align:left;"><p style="margin: 0;font-size: 14px;line-height: 21px;text-align: left"><strong><span style="font-size: 17px; line-height: 25px;"><span style="line-height: 25px; font-size: 17px;">Please follow the steps below to start&#160;</span></span></strong><strong><span style="font-size: 17px; line-height: 25px;"><span style="line-height: 25px; font-size: 17px;">your first campaign.</span></span></strong></p></div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>


                                    <div class="mobile_hide">
                                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 30px; padding-top: 20px; padding-bottom: 18px;"><![endif]-->
                                        <div style="color:#555555;font-family:Open Sans, Helvetica, Roboto, Arial;line-height:150%; padding-right: 10px; padding-left: 0px; padding-top: 10px; padding-bottom: 24px;">
                                            <div style="font-size:12px;line-height:18px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;text-align:left;"><ul style="list-style: none;">
                                                    <li style="font-size: 14px; line-height: 21px; text-align: left;background: url(<?php echo $view['assets']->getUrl('media/images/'.$emailproviderimg); ?>) no-repeat top left;padding: 5px 0px 0px 30px;"><span style="font-size: 14px; line-height: 21px;">Configure your email services provider</span><br /><br /><span style="font-size: 14px; line-height: 21px;"></span><span style="font-size: 14px; line-height: 21px;"></span></li>
                                                    <li style="font-size: 14px; line-height: 21px; text-align: left;background: url(<?php echo $view['assets']->getUrl('media/images/'.$websitetrackingimg); ?>) no-repeat top left; padding: 5px 0px 0px 30px;"><span style="font-size: 14px; line-height: 21px;">Configure your website tracking</span><br /><br /><span style="font-size: 14px; line-height: 21px;"></span></li>
                                                    <li style="font-size: 14px; line-height: 21px; text-align: left; background: url(<?php echo $view['assets']->getUrl('media/images/'.$segmentcreatedimg); ?>) no-repeat top left; padding: 5px 0px 0px 30px;"><span style="font-size: 14px; line-height: 21px;">Create your lead segments (List)</span><br /><br /><span style="font-size: 14px; line-height: 21px;"></span></li>
                                                    <li style="font-size: 14px; line-height: 21px; text-align: left; background: url(<?php echo $view['assets']->getUrl('media/images/'.$importimg); ?>) no-repeat top left; padding: 5px 0px 0px 30px;"><span style="font-size: 14px; line-height: 21px;">Import all of your contacts </span><br /><br /><span style="font-size: 14px; line-height: 21px;"></span></li>
                                                    <li style="font-size: 14px; line-height: 21px; text-align: left; background: url(<?php echo $view['assets']->getUrl('media/images/'.$campaignimg); ?>) no-repeat top left; padding: 5px 0px 0px 30px;"><span style="font-size: 14px; line-height: 21px;">Create &amp; send your first campaign.</span><br /><br /></li>
                                                </ul></div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>
            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 90%;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;" class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="background-color:transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width: 900px;"><tr class="layout-full-width" style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]><td align="center" width="900" style=" width:900px; padding-right: 0px; padding-left: 0px; padding-top:10px; padding-bottom:20px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><![endif]-->
                        <div class="col num12" style="min-width: 320px;max-width: 90%;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!--><div style="border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent; padding-top:10px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;"><!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 25px; padding-left: 25px; padding-top: 25px; padding-bottom: 10px;"><![endif]-->
                                        <div style="color:#555555;font-family:Open Sans, Helvetica, Roboto, Arial;line-height:180%; padding-right: 25px; padding-left: 25px; padding-top: 25px; padding-bottom: 5px;">
                                            <div style="font-size:12px;line-height:22px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;text-align:left;"><p style="margin: 0;font-size: 14px;line-height: 25px;text-align: center"><span style="font-size: 22px; line-height: 39px;"><strong><span style="line-height: 39px; font-size: 22px;">Get guaranteed success with Zero Risk Deal</span></strong></span></p></div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>
            <div style="text-align:center;width:90%;margin-left:5%;">
                <div style="display:flex;">

                    <div class="box1">
                        <div style="color:#555555;font-family:Open Sans, Helvetica, Roboto, Arial;line-height:200%; padding-right: 10px; padding-left: 10px; padding-top: 40px; padding-bottom: 38px;">
                            <div style="font-size:12px;line-height:24px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;text-align:left;"><p style="margin: 0;font-size: 12px;line-height: 24px;text-align: center"><span style="font-size: 16px; line-height: 32px;"><strong>Pay<span style="font-size: 42px; line-height: 84px;color:#F61488;"> $49 </span></strong></span></p><p style="margin: 0;font-size: 12px;line-height: 24px;text-align: center"><span style="font-size: 14px; line-height: 28px;">Get 3 Months Subscription</span></p><p style="margin: 0;font-size: 12px;line-height: 24px;text-align: center"><span style="font-size: 14px; line-height: 28px;">+ Unlimited Contacts &amp; Features</span></p><p style="margin: 0;font-size: 12px;line-height: 24px;text-align: center"><span style="font-size: 14px; line-height: 28px;">+ Detailed Training &amp; On-boarding</span></p><p style="margin: 0;font-size: 12px;line-height: 24px;text-align: center"><span style="font-size: 14px; line-height: 28px;">+ Premium Email, Phone Support</span></p></div>
                        </div>
                        <!--[if mso]></td></tr></table><![endif]-->





                        <div align="center" class="button-container center " style="padding-right: 40px; padding-left: 40px; padding-top:10px; padding-bottom:10px;">
                            <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-spacing: 0; border-collapse: collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;"><tr><td style="padding-right: 40px; padding-left: 40px; padding-top:10px; padding-bottom:10px;" align="center"><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="" style="height:39pt; v-text-anchor:middle; width:119pt;" arcsize="8%" strokecolor="#F61488" fillcolor="#F61488"><w:anchorlock/><v:textbox inset="0,0,0,0"><center style="color:#ffffff; font-family:Open Sans, Helvetica, Roboto, Arial; font-size:16px;"><![endif]-->
                            <div style="color: #ffffff; background-color: #F61488; border-radius: 4px; -webkit-border-radius: 4px; -moz-border-radius: 4px; max-width: 159px; width: 79px;width: auto; border-top: 0px solid transparent; border-right: 0px solid transparent; border-bottom: 0px solid transparent; border-left: 0px solid transparent; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px; font-family: Georgia, Times, 'Times New Roman', serif; text-align: center; mso-border-alt: none;">
                                <a href="<?php echo $pricingUrl?>" style="color:#fff;font-family:Open Sans, Helvetica, Roboto, Arial;font-size:16px;"><span style="font-size: 16px;">Get Zero Risk Deal</span></a>
                            </div>
                            <div style="font-size:12px;margin-top:5px;line-height:10px;font-family:Open Sans, Helvetica, Roboto, Arial;text-align:center;color:#555555;"><span style="font-size:12px; line-height:20px;">&#160;30 Days money back guarantee if <br>not convinced (No questions asked)</span><span style="font-size:16px; line-height:32px;"></span><span style="font-size:16px; line-height:32px;"></span></div>

                        </div></div>

                    <div class="box2">
                        <div style="font-size:12px;line-height:24px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;text-align:left;"><p style="margin: 0;font-size: 12px;line-height: 24px;text-align: center"><span style="font-size: 17px; line-height: 34px;"><strong><span style="line-height: 34px; font-size: 17px;">Deal Includes-</span></strong></span><br /><span style="font-size: 16px; line-height: 32px;"></span></p><ul style="list-style: none;">
                                <li style="font-size: 12px; line-height: 24px;background: url(<?php echo $view['assets']->getUrl('media/images/redtick.png'); ?>) no-repeat top left; padding: 1px 0px 0px 30px;"><span style="font-size: 14px; line-height: 28px;"><strong><span style="line-height: 28px; font-size: 14px;">3 MONTHS subscription with unlimited contacts &amp; all features (Normally cost you $49 * 3= $149). You save $100 in this.</span></strong><span style="line-height: 28px; font-size: 14px;"></span><span style="line-height: 28px; font-size: 14px;"></span></span></li><br>
                                <li style="font-size: 12px; line-height: 24px;background: url(<?php echo $view['assets']->getUrl('media/images/redtick.png'); ?>) no-repeat top left; padding: 1px 0px 0px 30px;"><span style="font-size: 14px; line-height: 28px;"><strong><span style="line-height: 28px; font-size: 14px;">Detailed Implementation, Technical Configuration &amp; Initial Setup- Our team will configure all the required configurations on your behalf. Which includes...</span></strong></span><br />
                                    <ul style="list-style: none;">
                                        <li style="font-size: 14px; line-height: 28px;background: url(<?php echo $view['assets']->getUrl('media/images/checkarrow.png'); ?>) no-repeat top left; padding: 1px 0px 0px 30px;"><span style="font-size: 14px; line-height: 28px;">Email service provider configuration as you choose (Sendgrid, Sparkpost, Elastic Email or Amazon SES)</span></li>
                                        <li style="font-size: 14px; line-height: 28px;background: url(<?php echo $view['assets']->getUrl('media/images/checkarrow.png'); ?>) no-repeat top left; padding: 1px 0px 0px 30px;"><span style="font-size: 14px; line-height: 28px;">Email deliverability settings include Domain verification, DKIM, &#160;SPF, DMARC settings.</span></li>
                                        <li style="font-size: 14px; line-height: 28px;background: url(<?php echo $view['assets']->getUrl('media/images/checkarrow.png'); ?>) no-repeat top left; padding: 1px 0px 0px 30px;"><span style="font-size: 14px; line-height: 28px;">SMS service provider configuration as you choose (Solution Infini or Twilio)</span></li>
                                        <li style="font-size: 14px; line-height: 28px;background: url(<?php echo $view['assets']->getUrl('media/images/checkarrow.png'); ?>) no-repeat top left; padding: 1px 0px 0px 30px;"><span style="font-size: 14px; line-height: 28px;">Setting up website tracking code, web forms &amp; other custom marketing tracking codes as required. </span></li>
                                        <li style="font-size: 14px; line-height: 28px;background: url(<?php echo $view['assets']->getUrl('media/images/checkarrow.png'); ?>) no-repeat top left; padding: 1px 0px 0px 30px;"><span style="font-size: 14px; line-height: 28px;">Configuring segments, lead scoring &amp; other lead qualification rules as required.</span></li><br>
                                    </ul>
                                </li>
                                <li style="font-size: 14px; line-height: 28px;background: url(<?php echo $view['assets']->getUrl('media/images/redtick.png'); ?>) no-repeat top left; padding: 1px 0px 0px 30px;"><span style="font-size: 14px; line-height: 28px;"><strong><span style="line-height: 28px; font-size: 14px;">Premium Support which includes </span></strong></span><br>
                                    <ul style="list-style: none;">
                                        <li style="font-size: 14px; line-height: 28px;background: url(<?php echo $view['assets']->getUrl('media/images/checkarrow.png'); ?>) no-repeat top left; padding: 1px 0px 0px 30px;"><span style="font-size: 14px; line-height: 28px;">One on One personalized consulting for your first campaign.</span><br /><span style="font-size: 14px; line-height: 28px;"></span></li>
                                        <li style="font-size: 14px; line-height: 28px;background: url(<?php echo $view['assets']->getUrl('media/images/checkarrow.png'); ?>) no-repeat top left; padding: 1px 0px 0px 30px;"><span style="font-size: 14px; line-height: 28px;">Customized product training session as per your need.</span><br /><span style="font-size: 14px; line-height: 28px;"></span></li>
                                        <li style="font-size: 14px; line-height: 28px;background: url(<?php echo $view['assets']->getUrl('media/images/checkarrow.png'); ?>) no-repeat top left; padding: 1px 0px 0px 30px;"><span style="font-size: 14px; line-height: 28px;">Phone &amp; Email support on business hours throughout the success period.</span></li>
                                    </ul>
                                </li>
                            </ul></div>
                    </div>
                    <!--[if mso]></td></tr></table><![endif]-->
                </div>

                <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
            </div>
            </div>
            <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
            </div>
            </div>
            </div>
            </div>

            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 90%;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;" class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="background-color:transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width: 900px;"><tr class="layout-full-width" style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]><td align="center" width="900" style=" width:900px; padding-right: 0px; padding-left: 0px; padding-top:15px; padding-bottom:20px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><![endif]-->
                        <div class="col num12" style="min-width: 320px;max-width: 90%;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!--><div style="border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent; padding-top:15px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;"><!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 25px; padding-left: 25px; padding-top: 25px; padding-bottom: 10px;"><![endif]-->
                                        <div style="color:#555555;font-family:Open Sans, Helvetica, Roboto, Arial;line-height:180%; padding-right: 25px; padding-left: 25px; padding-top: 25px; padding-bottom: 5px;">
                                            <div style="font-size:12px;line-height:22px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;text-align:left;"><p style="margin: 0;font-size: 14px;line-height: 25px;text-align: center"><span style="font-size: 22px; line-height: 39px;"><strong><span style="line-height: 39px; font-size: 22px;">Want to know how LeadsEngage fits in your business?</span></strong></span></p></div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>
            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 90%;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;" class="block-grid mixed-two-up ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="background-color:transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width: 900px;"><tr class="layout-full-width" style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]><td align="center" width="296" style=" width:296px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:5px; border-top: 2px solid #E7E7E7; border-left: 2px solid #E7E7E7; border-bottom: 2px solid #E7E7E7; border-right: 2px solid #E7E7E7;" valign="top"><![endif]-->
                        <div class="col num4" style="display: table-cell;vertical-align: top;max-width: 320px;min-width: 300px;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!--><div style="border-top: 2px solid #E7E7E7; border-left: 2px solid #E7E7E7; border-bottom: 2px solid #E7E7E7; border-right: 2px solid #E7E7E7; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;"><!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;"><![endif]-->
                                        <div style="color:#555555;font-family:Open Sans, Helvetica, Roboto, Arial;line-height:120%; padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;">
                                            <div style="font-size:12px;line-height:14px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;text-align:left;"><p style="margin: 0;font-size: 12px;line-height: 14px;text-align: center"><span style="font-size: 17px; line-height: 20px;"><strong><span style="line-height: 20px; font-size: 17px;">Schedule 1 on 1 Call</span></strong></span></p></div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>


                                    <div align="center" class="img-container center fixedwidth " style="padding-right: 0px;  padding-left: 0px;">
                                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr style="line-height:0px;line-height:0px;"><td style="padding-right: 0px; padding-left: 0px;" align="center"><![endif]-->
                                        <img class="center fixedwidth" align="center" border="0" src="https://leadsengage.com/wp-content/uploads/leadsengage/yourre.png" alt="Image" title="Image" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: block !important;border: 0;height: auto;float: none;width: 100%;max-width: 236.8px" width="236.8" />
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>


                                    <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td><td align="center" width="598" style=" width:598px; padding-right: 0px; padding-left: 0px; padding-top:5px; padding-bottom:22px; border-top: 2px solid #e7e7e7; border-left: 0px solid #e7e7e7; border-bottom: 2px solid #e7e7e7; border-right: 2px solid #e7e7e7;" valign="top"><![endif]-->
                        <div class="col num8" style="display: table-cell;vertical-align: top;min-width: 320px;max-width: 600px;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!--><div style="border-top: 2px solid #e7e7e7; border-left: 0px solid #e7e7e7; border-bottom: 2px solid #e7e7e7; border-right: 2px solid #e7e7e7; padding-top:5px; padding-bottom:22px; padding-right: 0px; padding-left: 0px;"><!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 15px;"><![endif]-->
                                        <div style="color:#555555;font-family:Open Sans, Helvetica, Roboto, Arial;line-height:200%; padding-right: 10px; padding-left: 10px; padding-top: 30px; padding-bottom: 22px;">
                                            <div style="font-size:12px;line-height:24px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;text-align:left;"><p style="margin: 0;font-size: 12px;line-height: 24px"><span style="font-size: 14px; line-height: 28px;">Schedule a call here and our solution expert will spend the time to understand your business, requirements and provide you one on one demo that relevant to your business.</span></p><p style="margin: 0;font-size: 12px;line-height: 24px"><br /><span style="font-size: 14px; line-height: 28px;">This demo will not be a sales call and we don't pressurize you to subscribe for service. Just that we will provide a quick product demo relevant to your business and help you to decide faster.</span></p></div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>



                                    <div align="center" class="button-container center " style="padding-right: 10px; padding-left: 10px; padding-top:10px; padding-bottom:10px;">
                                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-spacing: 0; border-collapse: collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top:10px; padding-bottom:10px;" align="center"><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="https://leadsengage.com/online-demo/" style="height:42pt; v-text-anchor:middle; width:318pt;" arcsize="8%" strokecolor="#F61488" fillcolor="#F61488"><w:anchorlock/><v:textbox inset="0,0,0,0"><center style="color:#ffffff; font-family:Open Sans, Helvetica, Roboto, Arial; font-size:18px;"><![endif]-->
                                        <a href="https://leadsengage.com/online-demo/" target="_blank" style="display: block;text-decoration: none;-webkit-text-size-adjust: none;text-align: center;color: #ffffff; background-color: #F61488; border-radius: 4px; -webkit-border-radius: 4px; -moz-border-radius: 4px; max-width: 454px; width: 374px;width: auto; border-top: 0px solid transparent; border-right: 0px solid transparent; border-bottom: 0px solid transparent; border-left: 0px solid transparent; padding-top: 10px; padding-right: 40px; padding-bottom: 10px; padding-left: 40px; font-family: Georgia, Times, 'Times New Roman', serif;mso-border-alt: none">
                                            <span style="font-family:Open Sans, Helvetica, Roboto, Arial;font-size:16px;line-height:32px;"><span style="font-size: 16px; line-height: 36px;">Schedule 1 on 1 call with our product specialist</span></span>
                                        </a>
                                        <!--[if mso]></center></v:textbox></v:roundrect></td></tr></table><![endif]-->
                                    </div>


                                    <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>
            <div style="background-color:transparent;">
                <div style="Margin: 0 auto;min-width: 320px;max-width: 90%;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;" class="block-grid ">
                    <div style="border-collapse: collapse;display: table;width: 100%;background-color:transparent;">
                        <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="background-color:transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width: 900px;"><tr class="layout-full-width" style="background-color:transparent;"><![endif]-->

                        <!--[if (mso)|(IE)]><td align="center" width="900" style=" width:900px; padding-right: 0px; padding-left: 0px; padding-top:15px; padding-bottom:20px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><![endif]-->
                        <div class="col num12" style="min-width: 320px;max-width: 900px;display: table-cell;vertical-align: top;">
                            <div style="background-color: transparent; width: 100% !important;">
                                <!--[if (!mso)&(!IE)]><!--><div style="border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent; padding-top:15px; padding-bottom:20px; padding-right: 0px; padding-left: 0px;"><!--<![endif]-->


                                    <div class="">
                                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 25px; padding-left: 25px; padding-top: 0px; padding-bottom: 0px;"><![endif]-->
                                        <div style="color:#555555;font-family:Open Sans, Helvetica, Roboto, Arial;line-height:180%; padding-right: 25px; padding-left: 25px; padding-top: 0px; padding-bottom: 0px;">
                                            <div style="font-size:12px;line-height:22px;color:#555555;font-family:Open Sans, Helvetica, Roboto, Arial;text-align:left;"><p style="margin: 0;font-size: 12px;line-height: 22px"><br data-mce-bogus="1" /></p></div>
                                        </div>
                                        <!--[if mso]></td></tr></table><![endif]-->
                                    </div>

                                    <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
                            </div>
                        </div>
                        <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
                    </div>
                </div>
            </div>
            <!--[if (mso)|(IE)]></td></tr></table><![endif]-->
        </td>
    </tr>
    </tbody>
</table>
<!--[if (mso)|(IE)]></div><![endif]-->


</body></html>