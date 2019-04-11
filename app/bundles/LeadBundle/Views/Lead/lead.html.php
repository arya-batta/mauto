<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

/** @var \Mautic\LeadBundle\Entity\Lead $lead */
/** @var array $fields */
$view->extend('MauticCoreBundle:Default:content.html.php');

$isAdmin     =$view['security']->isAdmin();
$isAnonymous = $lead->isAnonymous();

$flag = (!empty($fields['core']['country'])) ? $view['assets']->getCountryFlag($fields['core']['country']['value']) : '';

$leadName       = ($isAnonymous) ? $view['translator']->trans($lead->getPrimaryIdentifier()) : $lead->getPrimaryIdentifier();
$leadActualName = $lead->getName();
$leadCompany    = $lead->getCompany();

$view['slots']->set('leContent', 'lead');

$avatar = '';
if (!$isAnonymous) {
    $img    = $view['lead_avatar']->getAvatar($lead);
    $avatar = '<span class="pull-left img-wrapper img-rounded mr-10" style="width:33px"><img src="'.$img.'" alt="" /></span>';
}

$view['slots']->set('headerTitle', $leadActualName);
if (!$isAdmin) {
    $view['slots']->set('hideHeaderCss', 'hide');
}
$groups = array_keys($fields);
$edit   = $view['security']->hasEntityAccess(
    $permissions['lead:leads:editown'],
    $permissions['lead:leads:editother'],
    $lead->getPermissionUser()
);

$buttons = [];
//Send email button
if (!empty($fields['core']['email']['value'])) {
    $buttons[] = [
        'attr' => [
            'id'          => 'sendEmailButton',
            'data-toggle' => 'ajaxmodal',
            'data-target' => '#leSharedModal',
            'data-header' => $view['translator']->trans(
                'le.lead.email.send_email.header',
                ['%email%' => $fields['core']['email']['value']]
            ),
            'href' => $view['router']->path(
                'le_contact_action',
                ['objectId' => $lead->getId(), 'objectAction' => 'email']
            ),
        ],
        'btnText'   => $view['translator']->trans('le.lead.email.send_email'),
        'iconClass' => 'fa fa-send',
        'primary'   => true,
    ];
}

//View Contact Frequency button

if ($edit) {
    $buttons[] = [
        'attr' => [
            'class'       => $security->isAdmin() ? '' : 'hide',
            'data-toggle' => 'ajaxmodal',
            'data-target' => '#leSharedModal',
            'data-header' => $view['translator']->trans(
                'le.lead.lead.header.contact.frequency',
                ['%name%' => $lead->getPrimaryIdentifier()]
            ),
            'href' => $view['router']->path(
                'le_contact_action',
                ['objectId' => $lead->getId(), 'objectAction' => 'contactFrequency']
            ),
        ],
        'btnText'   => $view['translator']->trans('le.lead.contact.frequency'),
        'iconClass' => 'fa fa-signal',
    ];
}
//View Campaigns List button
if ($view['security']->isGranted('campaign:campaigns:edit')) {
    $buttons[] = [
        'attr' => [
            'class'       => $security->isAdmin() ? '' : 'hide',
            'data-toggle' => 'ajaxmodal',
            'data-target' => '#leSharedModal',
            'data-header' => $view['translator']->trans(
                'le.lead.lead.header.campaigns',
                ['%name%' => $lead->getPrimaryIdentifier()]
            ),
            'data-footer' => 'false',
            'href'        => $view['router']->path(
                'le_contact_action',
                ['objectId' => $lead->getId(), 'objectAction' => 'campaign']
            ),
        ],
        'btnText'   => $view['translator']->trans('mautic.workflow.workflow'),
        'iconClass' => 'fa fa-clock-o',
    ];
}
//Merge button
if (($view['security']->hasEntityAccess(
        $permissions['lead:leads:deleteown'],
        $permissions['lead:leads:deleteother'],
        $lead->getPermissionUser()
    ))
    && $edit && $isAdmin
) {
    $buttons[] = [
        'attr' => [
            'data-toggle' => 'ajaxmodal',
            'data-target' => '#leSharedModal',
            'data-header' => $view['translator']->trans(
                'le.lead.lead.header.merge',
                ['%name%' => $lead->getPrimaryIdentifier()]
            ),
            'href' => $view['router']->path(
                'le_contact_action',
                ['objectId' => $lead->getId(), 'objectAction' => 'merge']
            ),
        ],
        'btnText'   => $view['translator']->trans('le.lead.merge'),
        'iconClass' => 'fa fa-user',
    ];
}

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'item'            => $lead,
            'routeBase'       => 'contact',
            'langVar'         => 'lead.lead',
            'customButtons'   => $buttons,
            'templateButtons' => [
                'edit'   => $edit,
                'delete' => $view['security']->hasEntityAccess(
                    $permissions['lead:leads:deleteown'],
                    $permissions['lead:leads:deleteother'],
                    $lead->getPermissionUser()
                ),
                'close' => $view['security']->hasEntityAccess(
                    $permissions['lead:leads:viewown'],
                    $permissions['lead:leads:viewother'],
                    $lead->getPermissionUser()
                ),
            ],
        ]
    )
);
?>

<!-- start: box layout -->
<div class="box-layout">
    <!-- left section -->
    <div class="table-responsive">
    <div class="row">
    <div class="col-md-7 bg-white height-auto leadcontainer" id="lead-container">
        <div class="bg-auto" >
            <!--/ lead detail header -->

            <!-- lead detail collapseable -->
            <div class="collapse" id="lead-details">
                <ul class="pt-md nav nav-tabs pr-md pl-md" role="tablist">
                    <?php $step = 0; ?>
                    <?php foreach ($groups as $g): ?>
                        <?php if (!empty($fields[$g])): ?>
                            <li class="<?php if ($step === 0) {
    echo 'active';
} ?>">
                                <a href="#<?php echo $g; ?>" class="group" data-toggle="tab">
                                    <?php echo $view['translator']->trans('le.lead.field.group.'.$g); ?>
                                </a>
                            </li>
                            <?php ++$step; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

                <!-- start: tab-content -->
                <div class="tab-content pa-md bg-white">
                    <?php $i = 0; ?>
                    <?php foreach ($groups as $group): ?>
                        <div class="tab-pane fade <?php echo $i == 0 ? 'in active' : ''; ?> bdr-w-0"
                             id="<?php echo $group; ?>">
                            <div class="pr-md pl-md pb-md">
                                <div class="panel shd-none mb-0">
                                    <table class="table table-bordered table-striped mb-0">
                                        <tbody>
                                        <?php foreach ($fields[$group] as $field): ?>
                                            <tr>
                                                <td width="20%"><span class="fw-b"><?php echo $field['label']; ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($group == 'core' && $field['alias'] == 'country' && !empty($flag)): ?>
                                                    <img class="mr-sm" src="<?php echo $flag; ?>" alt="" style="max-height: 24px;"/>
                                                    <span class="mt-1"><?php echo $field['value']; ?>
                                                    <?php else: ?>
                                                        <?php if (is_array($field['value']) && 'multiselect' === $field['type']): ?>
                                                            <?php echo implode(', ', $field['value']); ?>
                                                        <?php elseif (is_string($field['value']) && 'url' === $field['type']): ?>
                                                            <a href="<?php echo $field['value']; ?>" target="_blank">
                                                                <?php echo $field['value']; ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <?php echo $field['value']; ?>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php ++$i; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <!--/ lead detail collapseable -->
        </div>

        <div>
            <div class="pa-md">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="panel col-md-12" style="padding-bottom: 3%;width: 102%;">
                            <?php // if (!$isAnonymous):?>
                            <div class="col-md-3" style="width: 18%;text-align: center;">
                               <img class="le-avatar-panel" src="<?php echo isset($img) ? $img : $view['gravatar']->getImage($app->getUser()->getEmail()); ?>" alt="<?php echo $leadName; ?> "/>
                            </div>
                            <?php // endif;?>
                            <div class="col-md-9"style="margin-left: -18px;width: 82%;">
                              <div>
                                <div class="row">
                                    <div  class="col-md-12"style="margin-top: 13px;" >
                                        <h3 class="text-primary fw-b" style="font-weight: bold;" ><?php echo $fields['core']['title']['value']; ?> <?php echo $fields['core']['firstname']['value']; ?> <?php echo $fields['core']['lastname']['value']; ?></h3>
                                        <br>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-b"><?php echo $view['translator']->trans('mautic.core.company'); ?></h6>
                                        <?php if (isset($fields['core']['company_new'])): ?>
                                            <p class="text-primary custom-font-primary"><?php echo $fields['core']['company_new']['value']; ?></p>
                                        <?php else: ?>
                                            <br>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-b "><?php echo $view['translator']->trans('le.lead.lead.field.owner'); ?></h6>
                                        <?php if ($lead->getOwner()) : ?>
                                            <p class="text-primary custom-font-primary"><?php echo $lead->getOwner()->getName(); ?></p>
                                        <?php else: ?>
                                            <br>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6" style="word-break: break-all">
                                        <h6 class="fw-b"><?php echo $view['translator']->trans('mautic.core.type.email'); ?></h6>
                                        <p class="text-primary custom-font-primary"><?php echo $fields['core']['email']['value']; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-b">
                                        <?php echo $view['translator']->trans('le.lead.field.address'); ?>
                                        </h6>
                                        <address class="text-primary custom-font-primary">
                                            <?php if (isset($fields['core']['address1'])): ?>
                                                <?php echo $fields['core']['address1']['value'] ?>
                                            <?php endif; ?>
                                            <?php if (!empty($fields['core']['address2']['value'])) : echo $fields['core']['address2']['value'].'.'
                                                .'<br>'; endif ?>
                                            <?php if (isset($fields['core']['city']['value'])) {
                                                    echo $fields['core']['city']['value'].'- ';
                                                } ?>
                                            <?php if (isset($fields['core']['zipcode']['value'])) {
                                                    echo $fields['core']['zipcode']['value'].'.';
                                                } ?><br>
                                            <?php if (isset($fields['core']['state']['value'])) {
                                                    echo $fields['core']['state']['value'].', ';
                                                } ?>
                                            <?php if (isset($fields['core']['country']['value'])) {
                                                    echo $fields['core']['country']['value'].'.';
                                                } ?><br>
                                        </address>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6" style="margin-top: -31px">
                                        <?php if (isset($fields['core']['phone'])): ?>
                                            <h6 class="fw-b"><?php echo $view['translator']->trans('le.lead.field.type.tel.home'); ?></h6>
                                            <p class="text-primary custom-font-primary"><?php echo $fields['core']['phone']['value']; ?></p>
                                        <?php endif; ?>

                                        <?php if (isset($fields['core']['mobile'])): ?>
                                            <h6 class="fw-b"><?php echo $view['translator']->trans('le.lead.field.type.tel.mobile'); ?></h6>
                                            <p class="text-primary mb-0 custom-font-primary"><?php echo $fields['core']['mobile']['value']; ?></p>
                                        <?php endif; ?>
                                        <br>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php $colors = ['#3292e0', '#5cb45b', '#04a2b3', '#f7b543', '#f03154', '#777', '#2a323c']; //'#ec407a', '#00a65a', '#f39c12', '#3c8dbc', '#dd4b39'?>
                                        <?php $count  =  0; ?>
                                        <h6 class="fw-b" ><?php echo $view['translator']->trans('le.lead.field.lists.belongsto'); ?></h6>
                                        <div class="leadprofile">
                                            <?php foreach ($listName as $list): ?>
                                                <?php if ($count == 7):
                                                    $count=0;
                                                endif; ?>
                                                <h5 class="pull-left mt-xs mr-xs"><span class="label label-primary" style="background-color:<?php echo $colors[$count] ?>"><?php echo $list['name']; ?></span></h5>
                                                <?php ++$count; ?>
                                            <?php endforeach; ?></div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                                  <br>
                                  <div class="row">
                                      <div class="col-md-12">
                                          <?php $colors = ['#3292e0', '#5cb45b', '#04a2b3', '#f7b543', '#f03154', '#777', '#2a323c']; //'#ec407a', '#00a65a', '#f39c12', '#3c8dbc', '#dd4b39'?>
                                          <?php $count  =  0; ?>
                                          <h6 class="fw-b" ><?php echo $view['translator']->trans('le.lead.field.segments.belongsto'); ?></h6>
                                          <div class="leadprofile">
                                              <?php foreach ($segmentName as $segment): ?>
                                                  <?php if ($count == 7):
                                                      $count=0;
                                                  endif; ?>
                                                  <h5 class="pull-left mt-xs mr-xs"><span class="label label-primary" style="background-color:<?php echo $colors[$count] ?>"><?php echo $segment['name']; ?></span></h5>
                                                  <?php ++$count; ?>
                                              <?php endforeach; ?></div>
                                          <div class="clearfix"></div>
                                      </div>
                                  </div>
                                  <br>
                                  <div class="row" >
                                      <div class="col-md-12">
                                          <?php $colors = ['#3292e0', '#5cb45b', '#04a2b3', '#f7b543', '#f03154', '#777', '#2a323c']; ?>
                                          <?php $tags   = $lead->getTags(); ?>
                                          <?php $count  =  0; ?>
                                          <h6 class="fw-b">
                                              <?php echo $view['translator']->trans('le.lead.field.tags.applied'); ?></h6>
                                          <div class="leadprofile">
                                              <?php foreach ($tags as $tag): ?>
                                                  <?php if ($count == 7):
                                                      $count=0;
                                                  endif; ?>
                                                  <h5 class="pull-left mt-xs mr-xs"><span class="label label-primary" style="background-color:<?php echo $colors[$count] ?>"><?php echo $tag->getTag(); ?></span>
                                                  </h5>
                                                  <?php ++$count; ?>
                                              <?php endforeach; ?></div>
                                          <div class="clearfix"></div>
                                      </div>
                                </div>
                                  <br>
                                  <div class="row" >
                                      <div class="col-md-12">
                                          <span class="fw-b"><?php echo $view['translator']->trans('le.lead.view.visited.pages'); ?></span><br>
                                          <div class="lead_page_hit_url_div">
                                              <?php if (!empty($pageHitDetails)): ?>
                                                  <?php foreach ($pageHitDetails as $counter => $event): ?>
                                                      <?php if ($event['url'] != ''):?>
                                                          <?php
                                                          $linkType       = 'target="_new"';
                                                          $string         = (strlen($event['url']) > 74) ? substr($event['url'], 0, 74).'....' : $event['url'];
                                                          $eventLabel     = "<a class= 'page_hit_url' href=\"{$event['url']}\" $linkType>{$string}</a>"; ?>
                                                          <h5 class="mt-xs mr-xs" style="font-size: 14px;font-weight: 300;">
                                                              <b><?php echo $event['pagehits'].'x '?></b>
                                                              <?php echo $eventLabel.'<br>'?>
                                                          </h5>
                                                      <?php endif; ?>
                                                  <?php endforeach; ?>
                                                  <div class="clearfix"></div>
                                              <?php endif; ?>
                                          </div>
                                      </div>
                                  </div>
                              </div>

                                <?php if ($doNotContact) : ?>
                                    <div id="bounceLabel<?php echo $doNotContact['id']; ?>">
                                        <h4 class="fw-b">
                                            <?php if ($doNotContact['unsubscribed']): ?>
                                                <span class="label le-label-danger dnc-alignment" data-toggle="tooltip" title="<?php echo $doNotContact['comments']; ?>">
                                <?php echo $view['translator']->trans('le.lead.do.not.contact'); ?>
                            </span>

                                            <?php elseif ($doNotContact['manual']): ?>
                                                <span class="label le-label-danger dnc-alignment" data-toggle="tooltip" title="<?php echo $doNotContact['comments']; ?>">
                                <?php echo $view['translator']->trans('le.lead.do.not.contact'); ?>
                                                    <span data-toggle="tooltip" data-placement="bottom" title="<?php echo $view['translator']->trans(
                                                        'le.lead.remove_dnc_status'
                                                    ); ?>">
                                    <i class="fa fa-times has-click-event" onclick="Le.removeBounceStatus(this, <?php echo $doNotContact['id']; ?>);"></i>
                                </span>
                            </span>

                                            <?php elseif ($doNotContact['bounced']): ?>
                                                <span class="label label-warning dnc-alignment" data-toggle="tooltip" data-placement="left" title="<?php echo $doNotContact['comments']; ?>">
                                <?php echo $view['translator']->trans('le.lead.do.not.contact_bounced'); ?>
                                                    <span data-toggle="tooltip" data-placement="bottom" title="<?php echo $view['translator']->trans(
                                                        'le.lead.remove_dnc_status'
                                                    ); ?>">
                                    <i class="fa fa-times has-click-event" onclick="Le.removeBounceStatus(this, <?php echo $doNotContact['id']; ?>);"></i>
                                </span>
                            </span>
                                            <?php elseif ($doNotContact['spam']): ?>
                                                <span class="label label-warning dnc-alignment" data-toggle="tooltip" title="<?php echo $doNotContact['comments']; ?>">
                                <?php echo $view['translator']->trans('le.lead.do.not.contact_spam'); ?>
                                                    <span data-toggle="tooltip" data-placement="bottom" title="<?php echo $view['translator']->trans(
                                                        'le.lead.remove_dnc_status'
                                                    ); ?>">
                                    <i class="fa fa-times has-click-event" onclick="Le.removeBounceStatus(this, <?php echo $doNotContact['id']; ?>);"></i>
                                </span>
                            </span>
                                            <?php endif; ?>
                                        </h4>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="uk-grid col-md-12">
            <div class="row" style="padding: 0px;width: 101%">
            <div class="le-lead-card-alignment col-md-3" style="margin-left: 15px;width: 24.5%;">
                <div class="md-card">
                    <div class="md-card-content">
                        <div class="uk-float-right">
                            <i class="le-lead-dialogue fa fa-line-chart" style="color: #3292e0;font-size:35px !important;"></i></div>
                        <span class="le-lead-card-header"> <?php echo $view['translator']->trans('le.lead.points'); ?> </span><br>
                       <span class="le-lead-card-content"><?php echo  $lead->getPoints(); ?></span>
                    </div>
                </div>
            </div>
                <?php  $score = (!empty($fields['core']['score']['value'])) ? $view['assets']->getLeadScoreIcon($fields['core']['score']['value']) : ''; ?>
            <div class="le-lead-card-alignment col-md-3" style="width: 24.5%;">
                    <div class="md-card">
                        <div class="md-card-content">
                            <div class="">
                                <span  class="le-lead-card-header" style="margin-right: 16px;"> <?php echo $view['translator']->trans('mautic.core.type.score'); ?> </span><br>
                                <span class="le-lead-card-content" style="text-transform: capitalize;margin-right: 16px;"><?php echo  $lead->getScore(); ?></span>
                                <img class="le-lead-card-score-content" src="<?php echo $score; ?>"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="le-lead-card-alignment col-md-3" style="width: 24.5%;">
                    <div class="md-card">
                        <div class="md-card-content">
                            <div class="uk-float-right">
                                <i class="le-lead-dialogue fa fa-calendar-check-o" style="color: #3292e0"></i></div>
                            <span  class="le-lead-card-header"> <?php echo $view['translator']->trans('le.lead.lastactive'); ?> </span><br>
                            <span class="le-lead-card-content"><?php echo $lastacitve = !empty($lead->getLastActive()) ? $view['date']->toCustDate($lead->getDateAdded(), 'local', 'M d, Y') : 'N/A'?> </span>
                        </div>
                    </div>
                </div>
                <div class="le-lead-card-alignment col-md-3" style="width: 24.5%;">
                    <div class="md-card">
                        <div class="md-card-content">
                            <div class="uk-float-right">
                                <i class="le-lead-dialogue fa fa-calendar-plus-o" style="color: #3292e0"></i></div>
                            <span  class="le-lead-card-header"> <?php echo $view['translator']->trans('le.core.added_first'); ?> </span><br>
                            <span class="le-lead-card-content"><?php echo $view['date']->toCustDate($lead->getDateAdded(), 'local', 'M d, Y'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <!-- lead detail collapseable toggler -->
           <!-- <div class="hr-expand nm">
                <span data-toggle="tooltip" title="<?php /*echo $view['translator']->trans('mautic.core.details'); */?>">
                    <a href="javascript:void(0)" class="arrow text-muted collapsed" data-toggle="collapse"
                       data-target="#lead-details"><span class="caret"></span> <?php /*echo $view['translator']->trans(
                            'mautic.core.details'
                        ); */?></a>
                </span>
            </div>-->
            <!--/ lead detail collapseable toggler -->
            <br><br><br><br>
<!--            --><?php //if (!$isAnonymous):?>
                <div class="pa-md">
                    <div class="row">
                        <div class="col-sm-12" style="width: 102%;">
                            <div class="panel">
                                <div class="panel-body box-layout">
                                    <div class="col-xs-4 va-m">
                                        <h5 class="text-white dark-md fw-b mb-xs">
                                            <?php echo $view['translator']->trans('le.lead.field.header.engagements'); ?>
                                        </h5>
                                    </div>
                                    <div class="col-xs-8 va-m">
                                        <?php echo $view->render('MauticCoreBundle:Helper:graph_dateselect.html.php', ['dateRangeForm' => $dateRangeForm, 'class' => 'pull-right']); ?>
                                    </div>
                                </div>
                                <?php echo $view->render(
                                    'MauticCoreBundle:Helper:chart.html.php',
                                    ['chartData' => $engagementData, 'chartType' => 'line', 'chartHeight' => 500]
                                ); ?>
                            </div>
                        </div>
                    </div>
                </div>
<!--            --><?php //endif;?>
            <!-- tabs controls -->
            <?php /** ?>
            <ul class="nav nav-tabs pr-md pl-md mt-10 hide">
                <li class="active">
                    <a href="#timeline-container" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('le.lead.lead.tab.history'); ?>
                        <span class="label label-primary mr-sm" id="TimelineCount">
                            <?php echo $events['total']; ?>
                        </span>
                    </a>
                </li>
                <li class="hide">
                    <a href="#notes-container" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('le.lead.lead.tab.notes'); ?>
                        <span class="label label-primary mr-sm" id="NoteCount">
                            <?php echo $noteCount; ?>
                        </span>
                    </a>
                </li>
                <?php if (!$isAnonymous && $security->isAdmin()): ?>
                    <li class="">
                        <a href="#social-container" role="tab" data-toggle="tab">
                            <?php echo $view['translator']->trans('le.lead.lead.tab.social'); ?>
                            <span class="label label-primary mr-sm" id="SocialCount">
                                <?php echo count($socialProfiles); ?>
                            </span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($view['security']->isAdmin()): ?>
                <li class="">
                    <a href="#integration-container" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('le.lead.lead.tab.integration'); ?>
                        <span class="label label-primary mr-sm" id="IntegrationCount">
                        <?php echo count($integrations); ?>
                    </span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="hide">
                    <a href="#auditlog-container" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('le.lead.lead.tab.auditlog'); ?>
                        <span class="label label-primary mr-sm" id="AuditLogCount">
                            <?php echo $auditlog['total']; ?>
                    </span>
                    </a>
                </li>
                <?php if ($places): ?>
                    <li class="">
                        <a href="#place-container" role="tab" data-toggle="tab" id="load-lead-map">
                            <?php echo $view['translator']->trans('le.lead.lead.tab.places'); ?>
                            <span class="label label-primary mr-sm" id="PlaceCount">
                                <?php echo count($places); ?>
                        </span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php echo $view['content']->getCustomContent('tabs', $mauticTemplateVars); ?>
            </ul>
            <?php */ ?>
            <!--/ tabs controls -->


        <!-- start: tab-content -->
        <div class="pa-md">
            <!-- #history-container -->
            <div class="tab-pane fade in active bdr-w-0" id="timeline-container">
                <?php echo $view->render(
                    'MauticLeadBundle:Timeline:list.html.php',
                    [
                        'events' => $events,
                        'lead'   => $lead,
                        'tmpl'   => 'index',
                    ]
                ); ?>
            </div>
            <!--/ #history-container -->

            <!-- #notes-container -->
            <?php /** ?>
            <div class="tab-pane fade bdr-w-0" id="notes-container">
                <?php echo $leadNotes; ?>
            </div>
            <?php */ ?>
            <!--/ #notes-container -->

            <!-- #social-container -->
            <?php if (!$isAnonymous && $view['security']->isAdmin()): ?>
                <div class="tab-pane hide fade bdr-w-0" id="social-container">
                    <?php echo $view->render(
                        'MauticLeadBundle:Social:index.html.php',
                        [
                            'lead'              => $lead,
                            'socialProfiles'    => $socialProfiles,
                            'socialProfileUrls' => $socialProfileUrls,
                        ]
                    ); ?>
                </div>
            <?php endif; ?>
            <!--/ #social-container -->
            <?php if ($view['security']->isAdmin()): ?>
            <!-- #integration-container -->
            <div class="tab-pane hide fade bdr-w-0" id="integration-container">
                <?php echo $view->render(
                    'MauticLeadBundle:Integration:index.html.php',
                    [
                        'lead'         => $lead,
                        'integrations' => $integrations,
                    ]
                ); ?>
            </div>
            <!--/ #integration-container -->
            <!-- #auditlog-container -->
                <div class="tab-pane hide fade bdr-w-0" id="auditlog-container">
                    <?php echo $view->render(
                        'MauticLeadBundle:Auditlog:list.html.php',
                        [
                            'events' => $auditlog,
                            'lead'   => $lead,
                            'tmpl'   => 'index',
                        ]
                    ); ?>
                </div>
                <!--/ #auditlog-container -->
            <?php endif; ?>
            <!-- custom content -->
            <?php echo $view['content']->getCustomContent('tabs.content', $mauticTemplateVars); ?>
            <!-- end: custom content -->
            
            <!-- #place-container -->
            <?php if ($places): ?>
                <div class="tab-pane fade hide bdr-w-0" id="place-container">
                    <?php echo $view->render('MauticLeadBundle:Lead:map.html.php', ['places' => $places]); ?>
                </div>
            <?php endif; ?>
            <!--/ #place-container -->
        </div>
        <!--/ end: tab-content -->
    </div>
    <!--/ left section -->

    <!-- right section -->
    <div class="col-md-3 bg-white bdr-l height-auto hide">
        <!-- form HTML -->
        <div class="panel bg-transparent shd-none bdr-rds-0 bdr-w-0 mb-0">
            <?php if (!$lead->isAnonymous()): ?>
                <div class="lead-avatar-panel">
                    <div class="avatar-collapser hr-expand nm">
                        <a href="javascript:void(0)"
                           class="arrow text-muted text-center<?php echo ($avatarPanelState == 'expanded') ? ''
                               : ' collapsed'; ?>" data-toggle="collapse" data-target="#lead-avatar-block"><span
                                class="caret"></span></a>
                    </div>
                    <div class="collapse<?php echo ($avatarPanelState == 'expanded') ? ' in' : ''; ?>"
                         id="lead-avatar-block">
                        <center>
                            <img class="img-responsive"  src="<?php echo $img; ?>" alt="<?php echo $leadName; ?> "/>
                        </center>
                    </div>
                </div>

            <?php endif; ?>
            <div class="mt-sm points-panel text-center">
                <?php
                $color = $lead->getColor();
                $style = !empty($color) ? ' style="font-color: '.$color.' !important;"' : '';
                $score = (!empty($fields['core']['score']['value'])) ? $view['assets']->getLeadScoreIcon($fields['core']['score']['value']) : '';
                ?>
                <h1
                    <?php echo $style; ?>>

                    <img src="<?php echo $score; ?>" style="max-height: 25px;vertical-align: baseline;" />
                    <?php echo $view['translator']->transChoice(
                        'le.lead.points.count',
                        $lead->getPoints(),
                        ['%points%' => $lead->getPoints()]
                    ); ?>
                </h1>
                <hr/>
                <?php if ($lead->getStage()): ?>
                    <?php echo $lead->getStage()->getName(); ?>
                    <hr>
                <?php endif; ?>
            </div>
            <?php if ($doNotContact) : ?>
                <div id="bounceLabel<?php echo $doNotContact['id']; ?>">
                    <div class="panel-heading text-center">
                        <h4 class="fw-b">
                            <?php if ($doNotContact['unsubscribed']): ?>
                                <span class="label label-danger" data-toggle="tooltip" title="<?php echo $doNotContact['comments']; ?>">
                                <?php echo $view['translator']->trans('le.lead.do.not.contact'); ?>
                            </span>

                            <?php elseif ($doNotContact['manual']): ?>
                                <span class="label label-danger" data-toggle="tooltip" title="<?php echo $doNotContact['comments']; ?>">
                                <?php echo $view['translator']->trans('le.lead.do.not.contact'); ?>
                                    <span data-toggle="tooltip" data-placement="bottom" title="<?php echo $view['translator']->trans(
                                        'le.lead.remove_dnc_status'
                                    ); ?>">
                                    <i class="fa fa-times has-click-event" onclick="Le.removeBounceStatus(this, <?php echo $doNotContact['id']; ?>);"></i>
                                </span>
                            </span>

                            <?php elseif ($doNotContact['bounced']): ?>
                                <span class="label label-warning" data-toggle="tooltip" title="<?php echo $doNotContact['comments']; ?>">
                                <?php echo $view['translator']->trans('le.lead.do.not.contact_bounced'); ?>
                                    <span data-toggle="tooltip" data-placement="bottom" title="<?php echo $view['translator']->trans(
                                        'le.lead.remove_dnc_status'
                                    ); ?>">
                                    <i class="fa fa-times has-click-event" onclick="Le.removeBounceStatus(this, <?php echo $doNotContact['id']; ?>);"></i>
                                </span>
                            </span>
                            <?php elseif ($doNotContact['spam']): ?>
                                <span class="label label-warning" data-toggle="tooltip" title="<?php echo $doNotContact['comments']; ?>">
                                <?php echo $view['translator']->trans('le.lead.do.not.contact_spam'); ?>
                                    <span data-toggle="tooltip" data-placement="bottom" title="<?php echo $view['translator']->trans(
                                        'le.lead.remove_dnc_status'
                                    ); ?>">
                                    <i class="fa fa-times has-click-event" onclick="Le.removeBounceStatus(this, <?php echo $doNotContact['id']; ?>);"></i>
                                </span>
                            </span>
                            <?php endif; ?>
                        </h4>
                    </div>
                    <hr/>
                </div>
            <?php endif; ?>
            <div class="panel-heading">
                <div class="panel-title">
                    <h6 class="fw-b"><?php echo $view['translator']->trans('le.lead.field.header.contact'); ?></h6>
                    <p class="text-muted"><?php echo $fields['core']['title']['value']; ?> <?php echo $fields['core']['firstname']['value']; ?> <?php echo $fields['core']['lastname']['value']; ?></p>
                </div>
            </div>
            <div class="panel-body pt-sm">
                <?php if (isset($fields['core']['company_new'])): ?>
                    <h6 class="fw-b"><?php echo $view['translator']->trans('mautic.core.company'); ?></h6>
                    <p class="text-muted"><?php echo $fields['core']['company_new']['value']; ?></p>
                <?php endif; ?>

            <?php if ($lead->getOwner()) : ?>
                <h6 class="fw-b"><?php echo $view['translator']->trans('le.lead.lead.field.owner'); ?></h6>
                <p class="text-muted"><?php echo $lead->getOwner()->getName(); ?></p>
            <?php endif; ?>

                <h6 class="fw-b">
                    <?php echo $view['translator']->trans('le.lead.field.address'); ?>
                </h6>
                <address class="text-muted">
                    <?php if (isset($fields['core']['address1'])): ?>
                        <?php echo $fields['core']['address1']['value']; ?><br>
                    <?php endif; ?>
                    <?php if (!empty($fields['core']['address2']['value'])) : echo $fields['core']['address2']['value']
                        .'<br>'; endif ?>
                    <?php echo $lead->getLocation(); ?> <?php if (isset($fields['core']['zipcode'])) {
                            echo $fields['core']['zipcode']['value'];
                        } ?><br>
                </address>

                <h6 class="fw-b"><?php echo $view['translator']->trans('mautic.core.type.email'); ?></h6>
                <p class="text-muted"><?php echo $fields['core']['email']['value']; ?></p>

                <?php if (isset($fields['core']['phone'])): ?>
                    <h6 class="fw-b"><?php echo $view['translator']->trans('le.lead.field.type.tel.home'); ?></h6>
                    <p class="text-muted"><?php echo $fields['core']['phone']['value']; ?></p>
                <?php endif; ?>

                <?php if (isset($fields['core']['mobile'])): ?>
                    <h6 class="fw-b"><?php echo $view['translator']->trans('le.lead.field.type.tel.mobile'); ?></h6>
                    <p class="text-muted mb-0"><?php echo $fields['core']['mobile']['value']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        <!--/ form HTML -->

        <?php if ($upcomingEvents) : ?>
            <hr class="hr-w-2" style="width:50%">

            <div class="panel bg-transparent shd-none bdr-rds-0 bdr-w-0">
                <div class="panel-heading">
                    <div class="panel-title"><?php echo $view['translator']->trans('le.lead.lead.upcoming.events'); ?></div>
                </div>
                <div class="panel-body pt-sm">
                    <ul class="media-list media-list-feed">
                        <?php foreach ($upcomingEvents as $event) : ?>
                        <?php
                            $metadata = unserialize($event['metadata']);
                            $errors   = false;
                            if (!empty($metadata['errors'])):
                                $errors = (is_array($metadata['errors'])) ? implode('<br />', $metadata['errors']) : $metadata['errors'];
                            endif;
                        ?>
                            <li class="media">
                                <div class="media-object pull-left mt-xs">
                                    <span class="figure"></span>
                                </div>
                                <div class="media-body">
                                    <?php $link = '<a href="'.$view['router']->path(
                                            'le_campaign_action',
                                            ['objectAction' => 'view', 'objectId' => $event['campaign_id']]
                                        ).'" data-toggle="ajax">'.$event['campaign_name'].'</a>'; ?>
                                    <?php echo $view['translator']->trans(
                                        'le.lead.lead.upcoming.event.triggered.at',
                                        ['%event%' => $event['event_name'], '%link%' => $link]
                                    ); ?>
                                    <?php if (!empty($errors)): ?>
                                    <i class="fa fa-warning text-danger" data-toggle="tooltip" title="<?php echo $errors; ?>"></i>
                                    <?php endif; ?>
                                    <p class="fs-12 dark-sm timeline-campaign-event-date-<?php echo $event['event_id']; ?>"><?php echo $view['date']->toFull($event['trigger_date'], 'utc'); ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
        <div class="pa-sm">
            <?php $tags = $lead->getTags(); ?>
            <?php foreach ($tags as $tag): ?>
                <h5 class="pull-left mt-xs mr-xs"><span class="label label-primary"><?php echo $tag->getTag(); ?></span>
                </h5>
            <?php endforeach; ?>
            <div class="clearfix"></div>
        </div>
        <div class="pa-sm">
            <?php if (!empty($pageHitDetails)): ?>
                <span class="fw-b"><?php echo $view['translator']->trans('le.lead.view.visited.pages'); ?></span>
            <?php foreach ($pageHitDetails as $counter => $event): ?>
            <?php
               $linkType   = 'target="_new"';
               $eventLabel = "<a class= 'page_hit_url' href=\"{$event['url']}\" $linkType>{$event['url']}</a>"; ?>
               <h5 class="pull-left mt-xs mr-xs">
                   <?php echo $event['pagehits'].' x '.$eventLabel.'<br>'?></h5>
              <?php endforeach; ?>
            <div class="clearfix"></div>
            <?php endif; ?>
        </div>

        <!-- <div class="pa-sm panel-companies">
            <div class="panel-title">  <?php echo $view['translator']->trans(
                    'le.lead.lead.companies'); ?></div>
           <?php foreach ($companies as $key => $company): ?>
                <h5 class="pull-left mt-xs mr-xs"><span class="label label-success" >
                       <i id="company-<?php echo $company['id']; ?>" class="fa fa-check <?php if ($company['is_primary'] == 1): ?>primary<?php endif?>" onclick="Le.setAsPrimaryCompany(<?php echo $company['id']?>, <?php echo $lead->getId()?>);" title="<?php echo $view['translator']->trans('le.lead.company.set.primary'); ?>"></i> <a href="<?php echo $view['router']->path('le_company_action', ['objectAction' => 'edit', 'objectId' => $company['id']]); ?>" style="color: white;"><?php echo $company['companyname']; ?></a>
                    </span>
                </h5>
            <?php endforeach; ?>
            <div class="clearfix"></div>
        </div>
    </div>-->
    <!--/ right section -->
</div>
    </div>
    <div class="col-md-3 notescontainer" id="notes-container">
        <div>
        <?php echo $leadNotes; ?>
        </div
    </div>
    </div>
    </div>
</div>

<!--/ end: box layout -->
