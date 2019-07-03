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
$view['slots']->set('leContent', 'pricingplans');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.dashboard.header.index'));

$welcomecontent = 'le.dashboard.welcome.content';
$welcomecontent = $view['translator']->trans($welcomecontent, ['%email%' => $accountEmail]);
$headermsg      = 'le.dashboard.welcome.message';
$block1img      = 'playvideo.png';
$block2img      = 'helpvideo.png';
$block3img      = 'facebook_community.png';
$img1link       = '#';
$img2link       = '#';
$img3link       = '#';
if (!$isEmailVerified) {
    $welcomecontent = 'le.dashboard.welcome.paidcontent';
    $headermsg      = 'le.dashboard.welcome.paid';
}
?>

<div style="width:100%;">
    <div class="<?php echo $isHideBlock ? '' : ''?>">
        <div>
            <div class="row row-padding ">
                <div class="col-sm-12 <?php echo !$isEmailVerified && $sendingDomainStatus ? 'hide' : '' ?>">
                    <p class="dashboard-header-p">
                    <span style="font-size: 22px; line-height: 39px;">
                        <strong>
                            <span class="dashboad-header"><i  style="color:orange;" class="fa fa-hand-paper-o hide"></i> <?php echo $view['translator']->trans($headermsg, ['%USERNAME%'=> $username]); ?>
                            </span>
                        </strong>
                    </span>
                        <br>
                    <p style="text-align: center;width:90%;margin-left:5%;">
                <span style="font-size:14px;">
                        <?php echo $view['translator']->trans($welcomecontent); ?>
                    </span>
                    </p>
                    </p>
                </div>
                <div id="pricingplan" class="<?php echo $isPaid ? 'hide' : ''; ?> pricingplan-dashboard hide">
                    <?php echo $view->render('MauticSubscriptionBundle:Pricing:pricing.html.php'); ?>
                </div>
                <br>
                <div class="col-sm-4 hide">
                    <div style="margin-top:60px;text-align: right;color:#3292e0;font-size:14px;font-weight: bold;padding-right:10px;">

                    </div>
                </div>
            </div>
            <div class="<?php echo !$isEmailVerified ? $sendingDomainStatus ? 'hide' : '' : 'hide' ?>">
                <div class="row" style="text-align: center;">
                    <div class="col-md-2">
                    </div>
                    <div class="col-md-8">
                        <div class="plan-card col-md-6" style="width:45%;">
                            <div class="">
                                <a href="<?php echo $view['router']->generate('le_sendingdomain_action')?>"><img class="welcome-img" src="<?php echo $view['assets']->getUrl('media/images/setup_sending_domain.png') ?>"/></a>
                            </div>
                        </div>
                        <div class="plan-card col-md-6" style="width:45%;">
                            <div class="">
                                <a href="https://anyfunnels.freshdesk.com/support/tickets/new" target="_blank"><img class="welcome-img" src="<?php echo $view['assets']->getUrl('media/images/help_sending_domain.png') ?>"/></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                    </div>
                </div>
            </div>
            <br>
            <div class="row hide">
                <br>
                <div class="col-sm-4"></div>
                <div class="col-sm-4 text-center">
                    <span style="font-size: 22px; line-height: 39px;">
                        <strong>
                            <span class="dashboad-header"><?php echo $view['translator']->trans('le.dashboard.getting.start.account'); ?>
                            </span>
                        </strong>
                    </span>
                </div>
                <div class="col-sm-4"></div>
            </div>
            <div class="row hide" style="text-align: center;margin-left:20px;">
                <div class="plan-card col-md-8 fl-left">
                    <div class="">
                        <a href="<?php echo $img1link?>"><img class="welcome-img" src="<?php echo $view['assets']->getUrl('media/images/'.$block1img) ?>"/></a>
                    </div>
                </div>

                <div class="plan-card col-md-8 fl-left">
                    <div class="">
                        <a href="<?php echo $img2link?>"><img class="welcome-img" src="<?php echo $view['assets']->getUrl('media/images/'.$block2img) ?>"/></a>
                    </div>
                </div>

                <div class="plan-card col-md-8 fl-left">
                    <div class="">
                        <a href="<?php echo $img3link?>"><img class="welcome-img" src="<?php echo $view['assets']->getUrl('media/images/'.$block3img) ?>"/></a>
                    </div>
                </div>
            </div>

            <div class="row row-padding hide">
                <div class="col-sm-4">
                </div>
                <div class="col-sm-4">
                    <div>


                    </div>
                </div>
                <div class="col-sm-4 hide">
                    <div class="bg-white lead-email-campaign-stats" style="height:550px;text-align: center;">
                    <div style="font-size:12px;line-height:24px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;text-align:left;"><p style="margin: 0;font-size: 12px;line-height: 24px;text-align: left;"><span style="font-size: 17px; line-height: 34px;"><strong><span style="line-height: 34px; font-size: 17px;">We believe your success is our success. Please do not hesitate to ask our team member to do first time setup on your behalf.</span></strong></span><br /><span style="font-size: 16px; line-height: 32px;"></span></p><br>

                        <p style="margin: 0;font-size: 12px;line-height: 24px;text-align: left;"><span style="font-size: 17px; line-height: 34px;"><strong><span style="line-height: 34px; font-size: 17px;">What’s Includes in this service?</span></strong></span><br /><span style="font-size: 16px; line-height: 32px;"></span></p>

                        <ul style="list-style: none;">

                            <li style="font-size: 12px; line-height: 24px;background: url(https://anyfunnels.com/wp-content/uploads/leproduct/check-tick-blue1.png) no-repeat top left; padding: 1px 0px 0px 30px;    margin-left: -15px;"><span style="font-size: 14px; line-height: 28px;"><span style="line-height: 28px; font-size: 14px;">Email services (SES/ SMTP) configuration as you choose (Sendgrid, Sparkpost, Elastic Email or Amazon SES)</span><span style="line-height: 28px; font-size: 14px;"></span><span style="line-height: 28px; font-size: 14px;"></span></span></li>
                            <li style="font-size: 12px; line-height: 24px;background: url(https://anyfunnels.com/wp-content/uploads/leproduct/check-tick-blue1.png) no-repeat top left; padding: 1px 0px 0px 30px;    margin-left: -15px;"><span style="font-size: 14px; line-height: 28px;"><span style="line-height: 28px; font-size: 14px;">Email deliverability settings include Domain verification, DKIM,  SPF, DMARC settings.</span></span>
                            <li style="font-size: 12px; line-height: 24px;background: url(https://anyfunnels.com/wp-content/uploads/leproduct/check-tick-blue1.png) no-repeat top left; padding: 1px 0px 0px 30px;    margin-left: -15px;"><span style="font-size: 14px; line-height: 28px;"><span style="line-height: 28px; font-size: 14px;">Setting up your website tracking, web forms & other custom technical configuration as required.</span></span><br /></li>
                        </ul>
                        <p style="margin: 0;font-size: 12px;line-height: 24px;text-align: left;"><span style="font-size: 17px; line-height: 34px;"><strong><span style="line-height: 34px; font-size: 17px;">Is this service charged extra?</span></strong></span><br /><span style="font-size: 16px; line-height: 32px;"></span></p>

                        <ul style="list-style: none;">

                            <li style="font-size: 12px; line-height: 24px;background: url(https://anyfunnels.com/wp-content/uploads/leproduct/check-tick-blue1.png) no-repeat top left; padding: 1px 0px 0px 30px;    margin-left: -15px;"><span style="font-size: 14px; line-height: 28px;"><span style="line-height: 28px; font-size: 14px;">No, we don’t charges you anything extra.</span><span style="line-height: 28px; font-size: 14px;"></span><span style="line-height: 28px; font-size: 14px;"></span></span></li>
                        </ul>
                    </div>
                    <a href="https://anyfunnels.com/online-demo/" target="_blank" class="schedule_demo"><?php echo $view['translator']->trans('le.dashboard.schedule.demo'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
    if (!$hideRecommendPc && $isMobile) {
        echo $view->render('MauticSubscriptionBundle:Subscription:desktop_recommend.html.php',
            [
                'typePrefix' => 'email',
            ]);
    }
?>
<div class="row" style="text-align: center;padding-top: 10px;margin-bottom: -20px;">
    <div class="col-sm-4"></div>
    <div class="col-sm-4">
        <div class="">
            <img style="<?php echo false ? 'margin-top:60px;' : ''?>" src="<?php echo $view['assets']->getUrl('media/images/dashboard.png') ?>"> </img>
        </div>
    </div class="col-sm-4">
    <div></div>
</div>
<div class="pa-md" style="<?php echo false ? 'margin-top:30px;' : ''?>">
    <div class="row row-padding">
        <?php echo $view->render('MauticSubscriptionBundle:Subscription:stats.html.php',
            [
                'sentcount'             => $emailStats['sent'],
                'uniqueopen'            => $emailStats['uopen'],
                'totalopen'             => $emailStats['topen'],
                'click'                 => $emailStats['click'],
                'unsubscribecount'      => $emailStats['unsubscribe'],
                'bouncecount'           => $emailStats['bounce'],
                'spamcount'             => $emailStats['spam'],
                'allleads'              => $leadStats['allleads'],
                'activeleads'           => $leadStats['activeleads'],
                'activeengagedleads'    => $leadStats['activeengagedleads'],
                'activenotengagedleads' => $leadStats['activenotengagedleads'],
                'invalid'               => $leadStats['invalid'],
                'complaint'             => $leadStats['complaint'],
                'unsubscribed'          => $leadStats['unsubscribed'],
                'notconfirmed'          => $leadStats['notconfirmed'],
                'inactiveleads'         => $leadStats['inactiveleads'],
                'recentadded'           => $leadStats['recentadded'],
                'recentactive'          => $leadStats['recentactive'],
            ]
        ); ?>
    </div>
</div>

<div class="pa-md">
    <div class="row row-padding">
        <?php echo $view->render('MauticSubscriptionBundle:Subscription:otherstats.html.php',
            [
                'workflow'    => $overallstats['activeworkflow'],
                'goals'       => $overallstats['goalsachived'],
                'forms'       => $overallstats['activeforms'],
                'submissions' => $overallstats['submissions'],
                'asset'       => $overallstats['activeasset'],
                'downloads'   => $overallstats['downloads'],
            ]
        ); ?>
    </div>
</div>

<div class="pa-md">
    <div class="row">
        <div class="col-sm-12">
            <h2 class="hide email-dataview-stats"><?php echo $view['translator']->trans('le.dashboard.account.stats'); ?> </h2>
            <br>
            <div class="panel">
                <div class="panel-body box-layout">
                    <div class="col-xs-6 va-m">
                        <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.dashboard.account.stats'); ?> </h2>
                    </div>
                    <div class="col-xs-8 va-m">
                        <?php echo $view->render(
                            'MauticCoreBundle:Helper:graph_dateselect.html.php',
                            ['dateRangeForm' => $dateRangeForm, 'class' => 'pull-right']
                        ); ?>
                    </div>
                </div>
                <div class="pt-0 pl-15 pb-10 pr-15">
                    <?php echo $view->render(
                        'MauticCoreBundle:Helper:chart.html.php',
                        ['chartData' => $stats, 'chartType' => 'line', 'chartHeight' => 500]
                    ); ?>
                </div>
            </div>
        </div>
    </div>
</div>
