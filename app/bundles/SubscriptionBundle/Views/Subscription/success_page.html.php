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
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.dashboard.header.index'));

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

<div style="width:100%;">
    <div class="<?php echo $isPaid ? 'hide' : ''?>">
        <div>
            <div>
                <p style="margin-top: 60px;margin-bottom:30px;font-size: 14px;line-height: 25px;text-align: center">
                    <span style="font-size: 22px; line-height: 39px;">
                        <strong>
                            <span style="line-height: 39px; font-size: 22px;"><i  style="color:orange;" class="fa fa-hand-paper-o"></i> <?php echo $view['translator']->trans('le.dashboard.welcome.message', ['%USERNAME%'=> $username]); ?>
                            </span>
                        </strong>
                    </span>
                </p>
            </div>
            
            <div class="row row-padding" style="text-align: center">
                <div class="col-sm-4">
                    <div class="bg-white welcome-stats">
                    <img src="https://leadsengage.com/wp-content/uploads/leproduct/icon-configuration.png">
                        <div style="text-align: left;padding:15px;">
                            <ul class="todolist">
                                <li class="<?php echo $isProviderChanged ? 'active' : ''; ?>">
                                    <div class="todolist-container">
                                        <div class="todolist-input"><i class="fa fa-circle-thin"></i></div>
                                        <div class="todolist-title"><a href="<?php echo $view['router']->path('le_config_action', ['objectAction' => 'edit'])?>"><?php echo $view['translator']->trans('le.dashboard.connect.smtp'); ?></a></div>
                                    </div>
                                </li>
                                <li class="<?php echo $isWebsiteTracking ? 'active' : ''; ?>">
                                    <div class="todolist-container">
                                        <div class="todolist-input"><i class="fa fa-circle-thin"></i></div>
                                        <div class="todolist-title"><a href="<?php echo $view['router']->path('le_config_action', ['objectAction' => 'edit'])?>"><?php echo $view['translator']->trans('le.dashboard.connect.website'); ?></a></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="bg-white welcome-stats">
                    <img src="https://leadsengage.com/wp-content/uploads/leproduct/icon-upload.png">
                        <div style="text-align: left;padding:15px;">
                            <ul class="todolist">
                                <li class="<?php echo $isListCreated ? 'active' : ''; ?>">
                                    <div class="todolist-container">
                                        <div class="todolist-input"><i class="fa fa-circle-thin"></i></div>
                                        <div class="todolist-title"><a href="<?php echo $view['router']->path('le_listoptin_index')?>"><?php echo $view['translator']->trans('le.dashboard.create.list'); ?></a></div>
                                    </div>
                                </li>
                                <li class="<?php echo $isImportDone ? 'active' : ''; ?>">
                                    <div class="todolist-container">
                                        <div class="todolist-input"><i class="fa fa-circle-thin"></i></div>
                                        <div class="todolist-title"><a href="<?php echo $view['router']->path('le_import_action', ['objectAction' => 'new'])?>"><?php echo $view['translator']->trans('le.dashboard.import.leads'); ?></a></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="bg-white welcome-stats">
                    <img src="https://leadsengage.com/wp-content/uploads/leproduct/icon-send-email.png">
                        <div style="text-align: left;padding:15px;"
                            <ul class="todolist">
                                <li class="<?php echo $isOneOffCreated ? 'active' : ''; ?>">
                                    <div class="todolist-container">
                                        <div class="todolist-input"><i class="fa fa-circle-thin"></i></div>
                                        <div class="todolist-title"><a href="<?php echo $view['router']->path('le_email_campaign_index')?>"><?php echo $view['translator']->trans('le.dashboard.send.one-off'); ?></a></div>
                                    </div>
                                </li>
                                <li class="<?php echo $isDripCreated ? 'active' : ''; ?>">
                                    <div class="todolist-container">
                                        <div class="todolist-input"><i class="fa fa-circle-thin"></i></div>
                                        <div class="todolist-title"><a href="<?php echo $view['router']->path('le_dripemail_index')?>"><?php echo $view['translator']->trans('le.dashboard.drip.drip'); ?></a></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <br><br>
            <div class="row row-padding">
                <div class="col-sm-6">
                    <div class="bg-white dashboard-email-campaign-stats" style="height:550px;">
                    <div>
                        <script src="https://leadsengage.cdn.vooplayer.com/assets/vooplayer.js"></script><a class="fancyboxIframe vooplayer " href="https://leadsengage.cdn.vooplayer.com/publish/MTIxMjU0" data-playerId="MTIxMjU0" data-fancybox-type="iframe"><img style="width:100%;" src="https://leadsengage.com/wp-content/uploads/leproduct/play-video.png"></a>
                    </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="bg-white lead-email-campaign-stats" style="height:550px;text-align: center;">
                    <div style="font-size:12px;line-height:24px;font-family:Open Sans, Helvetica, Roboto, Arial;color:#555555;text-align:left;"><p style="margin: 0;font-size: 12px;line-height: 24px;text-align: left;"><span style="font-size: 17px; line-height: 34px;"><strong><span style="line-height: 34px; font-size: 17px;">We believe your success is our success. Please do not hesitate to ask our team member to do first time setup on your behalf.</span></strong></span><br /><span style="font-size: 16px; line-height: 32px;"></span></p><br>

                        <p style="margin: 0;font-size: 12px;line-height: 24px;text-align: left;"><span style="font-size: 17px; line-height: 34px;"><strong><span style="line-height: 34px; font-size: 17px;">What’s Includes in this service?</span></strong></span><br /><span style="font-size: 16px; line-height: 32px;"></span></p>

                        <ul style="list-style: none;">

                            <li style="font-size: 12px; line-height: 24px;background: url(https://leadsengage.com/wp-content/uploads/leproduct/check-tick-blue1.png) no-repeat top left; padding: 1px 0px 0px 30px;    margin-left: -15px;"><span style="font-size: 14px; line-height: 28px;"><span style="line-height: 28px; font-size: 14px;">Email services (SES/ SMTP) configuration as you choose (Sendgrid, Sparkpost, Elastic Email or Amazon SES)</span><span style="line-height: 28px; font-size: 14px;"></span><span style="line-height: 28px; font-size: 14px;"></span></span></li>
                            <li style="font-size: 12px; line-height: 24px;background: url(https://leadsengage.com/wp-content/uploads/leproduct/check-tick-blue1.png) no-repeat top left; padding: 1px 0px 0px 30px;    margin-left: -15px;"><span style="font-size: 14px; line-height: 28px;"><span style="line-height: 28px; font-size: 14px;">Email deliverability settings include Domain verification, DKIM,  SPF, DMARC settings.</span></span>
                            <li style="font-size: 12px; line-height: 24px;background: url(https://leadsengage.com/wp-content/uploads/leproduct/check-tick-blue1.png) no-repeat top left; padding: 1px 0px 0px 30px;    margin-left: -15px;"><span style="font-size: 14px; line-height: 28px;"><span style="line-height: 28px; font-size: 14px;">Setting up your website tracking, web forms & other custom technical configuration as required.</span></span><br /></li>
                        </ul>
                        <p style="margin: 0;font-size: 12px;line-height: 24px;text-align: left;"><span style="font-size: 17px; line-height: 34px;"><strong><span style="line-height: 34px; font-size: 17px;">Is this service charged extra?</span></strong></span><br /><span style="font-size: 16px; line-height: 32px;"></span></p>

                        <ul style="list-style: none;">

                            <li style="font-size: 12px; line-height: 24px;background: url(https://leadsengage.com/wp-content/uploads/leproduct/check-tick-blue1.png) no-repeat top left; padding: 1px 0px 0px 30px;    margin-left: -15px;"><span style="font-size: 14px; line-height: 28px;"><span style="line-height: 28px; font-size: 14px;">No, we don’t charges you anything extra.</span><span style="line-height: 28px; font-size: 14px;"></span><span style="line-height: 28px; font-size: 14px;"></span></span></li>
                        </ul>
                    </div>
                    <a href="https://leadsengage.com/online-demo/" target="_blank" class="schedule_demo"><?php echo $view['translator']->trans('le.dashboard.schedule.demo'); ?></a>
                    </div>
                </div>
            </div>
        <br>
        <br>
        </div>
    </div>

<div class="row" style="text-align: center;">
    <div class="col-sm-4"></div>
    <div class="col-sm-4">
        <div class="">
            <img style="<?php echo $isPaid ? 'margin-top:60px;' : ''?>" src="<?php echo $view['assets']->getUrl('media/images/dashboard.png') ?>"> </img>
        </div>
    </div class="col-sm-4">
    <div></div>
</div>
<div class="pa-md">
    <div class="row row-padding">
        <?php echo $view->render('MauticSubscriptionBundle:Subscription:stats.html.php',
            [
                'sentcount'        => $emailStats['sent'],
                'uniqueopen'       => $emailStats['uopen'],
                'totalopen'        => $emailStats['topen'],
                'click'            => $emailStats['click'],
                'unsubscribecount' => $emailStats['unsubscribe'],
                'bouncecount'      => $emailStats['bounce'],
                'spamcount'        => $emailStats['spam'],
                'allleads'         => $leadStats['allleads'],
                'activeleads'      => $leadStats['activeleads'],
                'recentadded'      => $leadStats['recentadded'],
                'recentactive'     => $leadStats['recentactive'],
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
            <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.dashboard.account.stats'); ?> </h2>
            <br>
            <div class="panel">
                <div class="panel-body box-layout">
                    <div class="col-xs-6 va-m">
                        <h5 class="text-white hide dark-md fw-sb mb-xs">
                            <span class="fa fa-download"></span>
                            <?php echo $view['translator']->trans('le.dashboard.account.stats'); ?>
                        </h5>
                    </div>
                    <div class="col-md-8 va-m">
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
