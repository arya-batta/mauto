<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

if (!$isEmbedded) {
    $view->extend('MauticCoreBundle:Default:content.html.php');

    $view['slots']->set('leContent', 'email');
    $view['slots']->set('headerTitle', $email->getName());
}
//dump($actionRoute);
$variantContent = $view->render(
    'MauticCoreBundle:Variant:index.html.php',
    [
        'activeEntity'  => $email,
        'variants'      => $variants,
        'abTestResults' => $abTestResults,
        'model'         => 'email',
        'actionRoute'   => $actionRoute,
    ]
);

$showVariants = !empty(trim($variantContent));

$translationContent = $view->render(
    'MauticCoreBundle:Translation:index.html.php',
    [
        'activeEntity' => $email,
        'translations' => $translations,
        'model'        => 'email',
        'actionRoute'  => $actionRoute,
    ]
);
$showTranslations = !empty(trim($translationContent));

$emailType = $email->getEmailType();
if (empty($emailType)) {
    $emailType = 'template';
}

$customButtons   = [];
$customButtons[] = [
    'attr' => [
        'class'       => 'btn btn-default btn-nospin le-btn-default',
        'data-toggle' => 'none',
        'href'        => $previewUrl,
        'target'      => '_blank',
    ],
    'iconClass' => 'fa fa-eye',
    'btnText'   => 'mautic.campaign.preview.header',
];
if (!$isEmbedded) {
    if ($emailType == 'list') {
        $customButtons[] = [
            'attr' => [
                'data-toggle' => 'ajax',
                'href'        => $view['router']->path(
                    $actionRoute,
                    ['objectAction' => 'send', 'objectId' => $email->getId()]
                ),
            ],
            'iconClass' => 'fa fa-send-o',
            'btnText'   => 'le.email.send',
            'primary'   => true,
        ];
    }

    $customButtons[] = [
        'attr' => [
            'class'       => 'btn btn-default btn-nospin le-btn-default',
            'data-toggle' => 'ajaxmodal',
            'data-target' => '#leSharedModal',
            'href'        => $view['router']->path($actionRoute, ['objectAction' => 'sendExample', 'objectId' => $email->getId()]),
            'data-header' => $view['translator']->trans('le.email.send.example'),
        ],
        'iconClass' => 'fa fa-send',
        'btnText'   => 'le.email.send.example',
        'primary'   => true,
    ];
}
// Only show A/B test button if not already a translation of an a/b test
$allowAbTest = $email->isTranslation(true) && $translations['parent']->isVariant(true) ? false : true;
if (!$isEmbedded) {
    $view['slots']->set(
        'actions',
        $view->render(
            'MauticCoreBundle:Helper:page_actions.html.php',
            [
                'item'            => $email,
                'templateButtons' => [
                    'edit' => $view['security']->hasEntityAccess(
                        $permissions['email:emails:editown'],
                        $permissions['email:emails:editother'],
                        $email->getCreatedBy()
                    ),
                    'clone'  => $permissions['email:emails:create'],
                    'abtest' => false, //(!$notificationemail && $allowAbTest && $permissions['email:emails:create']),
                    'delete' => $view['security']->hasEntityAccess(
                        $permissions['email:emails:deleteown'],
                        $permissions['email:emails:deleteother'],
                        $email->getCreatedBy()
                    ),
                    'close' => $view['security']->hasEntityAccess(
                        $permissions['email:emails:viewown'],
                        $permissions['email:emails:viewother'],
                        $email->getCreatedBy()
                    ),
                ],
                'actionRoute'   => $actionRoute,
                'indexRoute'    => $indexRoute,
                'customButtons' => $customButtons,
                'langVar'       => !$notificationemail ? 'email' : 'email.notification',
            ]
        )
    );

    $view['slots']->set(
        'publishStatus',
        $view->render('MauticCoreBundle:Helper:publishstatus_badge.html.php', ['entity' => $email])
    );
}
?>

<!-- start: box layout -->
<div class="box-layout">
    <!-- left section -->
    <div class="col-md-12 bg-white height-auto">
        <div class="bg-auto">
            <!-- email detail header -->
            <div class="pr-md pl-md pt-lg pb-lg hide">
                <div class="box-layout">
                    <div class="col-xs-10">
                        <div class="hide">
                            <?php echo \Mautic\CoreBundle\Helper\EmojiHelper::toHtml($email->getSubject(), 'short'); ?>
                        </div>
                        <?php if ($email->isVariant(true)): ?>
                            <div class="small">
                                <a href="<?php echo $view['router']->path($actionRoute, ['objectAction' => 'view', 'objectId' => $variants['parent']->getId()]); ?>" data-toggle="ajax">
                                    <?php echo $view['translator']->trans('mautic.core.variant_of', ['%parent%' => $variants['parent']->getName()]); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if ($email->isTranslation(true)): ?>
                            <div class="small">
                                <a href="<?php echo $view['router']->path($actionRoute, ['objectAction' => 'view', 'objectId' => $translations['parent']->getId()]); ?>" data-toggle="ajax">
                                    <?php echo $view['translator']->trans('mautic.core.translation_of', ['%parent%' => $translations['parent']->getName()]); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!--/ email detail header -->

            <!-- email detail collapseable -->
            <div class="collapse" id="email-details">
                <div class="pr-md pl-md pb-md">
                    <div class="panel shd-none mb-0">
                        <table class="table table-bordered table-striped mb-0">
                            <tbody>
                            <?php echo $view->render(
                                'MauticCoreBundle:Helper:details.html.php',
                                ['entity' => $email]
                            ); ?>
                            <tr>
                                <td width="20%">
                                    <span class="fw-b"><?php echo $view['translator']->trans('mautic.core.form.theme'); ?></span>
                                </td>
                                <td><?php echo $email->getTemplate(); ?></td>
                            </tr>
                            <?php if ($fromName = $email->getFromName()): ?>
                                <tr>
                                    <td width="20%">
                                        <span class="fw-b"><?php echo $view['translator']->trans('le.email.from_name'); ?></span>
                                    </td>
                                    <td><?php echo $fromName; ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($fromEmail = $email->getFromAddress()): ?>
                                <tr>
                                    <td width="20%">
                                        <span class="fw-b"><?php echo $view['translator']->trans('le.email.from_email'); ?></span>
                                    </td>
                                    <td><?php echo $fromEmail; ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($replyTo = $email->getReplyToAddress()): ?>
                                <tr>
                                    <td width="20%">
                                        <span class="fw-b"><?php echo $view['translator']->trans('le.email.reply_to_email'); ?></span>
                                    </td>
                                    <td><?php echo $replyTo; ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ($bccAddress = $email->getBccAddress()): ?>
                                <tr>
                                    <td width="20%">
                                        <span class="fw-b"><?php echo $view['translator']->trans('le.email.bcc'); ?></span>
                                    </td>
                                    <td><?php echo $bccAddress; ?></td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--/ email detail collapseable -->
        </div>

        <div class="bg-auto bg-dark-xs">
            <!-- email detail collapseable toggler -->
            <div class="hr-expand nm hide">
                <span data-toggle="tooltip" title="<?php echo $view['translator']->trans('mautic.core.details'); ?>">
                    <a href="javascript:void(0)" class="arrow text-muted collapsed" data-toggle="collapse"
                       data-target="#email-details">
                        <span class="caret"></span> <?php echo $view['translator']->trans('mautic.core.details'); ?>
                    </a>
                </span>
            </div>
            <?php if (($showTranslations || $showVariants)): ?>
                <div class="col-md-6 text-right small" id="variant-chart-switcher">
                    <div style="padding-top:10px;">
                <span>
                    <a data-toggle="ajax" class="btn btn-xs<?php if (!$showAllStats) {
                                echo ' disabled';
                            } ?>" href="<?php echo $actionRoute.'&stats=variant'; ?>">
                        <?php echo $view['translator']->trans('le.email.variant.graph.variant'); ?>
                    </a>
                </span>
                    </span> | </span>
                    <span>
                    <a data-toggle="ajax" class="btn btn-xs<?php  if ($showAllStats) {
                                echo ' disabled';
                            } ?>" href="<?php echo $actionRoute.'&stats=all'; ?>">
                        <?php echo $view['translator']->trans('le.email.variant.graph.all'); ?>
                    </a>
                </span>
                    </div>
                </div>
            <?php endif; ?>
            <!--/ email detail collapseable toggler -->
            <?php echo $view->render('MauticEmailBundle:Email:campaign_stats.html.php',
                [
                    'sentcount'        => $emailStats['sent'],
                    'uniqueopen'       => $emailStats['uopen'],
                    'totalopen'        => $emailStats['topen'],
                    'notopencount'     => $emailStats['nopen'],
                    'click'            => $emailStats['click'],
                    'unsubscribecount' => $emailStats['unsubscribe'],
                    'bouncecount'      => $emailStats['bounce'],
                    'spamcount'        => $emailStats['spam'],
                ]
            ); ?>
            <?php echo $view->render(
                'MauticEmailBundle:Email:graph.html.php',
                [
                    'stats'         => $stats,
                    'statsDevices'  => $statsDevices,
                    'emailType'     => $emailType,
                    'email'         => $email,
                    'isVariant'     => ($showTranslations || $showVariants),
                    'showAllStats'  => $showAllStats,
                    'dateRangeForm' => $dateRangeForm,
                ]
            ); ?>
            <?php if ($showVariants): ?>
                <!-- #variants-container -->
                <div class="col-md-12 bdr-w-0" id="variants-container">
                    <?php echo $variantContent; ?>
                </div>
                <!--/ #variants-container -->
            <?php endif; ?>
            <div class="row list-panel-padding">
            <div class="col-md-12 bdr-w-0" id="clicks-container">
                <?php echo $view->render('MauticPageBundle:Trackable:click_counts.html.php', ['trackables' => $trackables]); ?>
            </div>

            <div class="col-md-12 bdr-w-0">
                <div>
                    <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.email.click.last10openleads')?><a class="" href="<?php echo $view['router']->path(
                            'le_contact_index',
                            ['search' => $view['translator']->trans('le.lead.lead.searchcommand.email_read').':'.$email->getId()]
                        ); ?>" title="<?php echo $view['translator']->trans('le.email.click.viewmore')?>"><span> <i class="fa fa-th-list email-stat-icon"></i></span></a></h2>
                    <?php echo $view->render('MauticEmailBundle:Email:leads.html.php', ['items' => $openLeads, 'security' => $security]); ?>
                </div>
            </div>
            <div class="col-md-12 bdr-w-0">
                <div>
                    <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.email.click.last10clickleads')?><a class="" href="<?php echo $view['router']->path(
                            'le_contact_index',
                            ['search' => $view['translator']->trans('le.lead.lead.searchcommand.email_click').':'.$email->getId()]
                        ); ?>" title="<?php echo $view['translator']->trans('le.email.click.viewmore')?>"><span> <i class="fa fa-th-list email-stat-icon"></i></span></a></h2>
                    <?php echo $view->render('MauticEmailBundle:Email:leads.html.php', ['items' => $clickLeads, 'security' => $security]); ?>
                </div>
            </div>
            <div class="col-md-12 bdr-w-0">
                <div>
                    <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.email.click.last10unsubscribeleads')?><a class="" href="<?php echo $view['router']->path(
                            'le_contact_index',
                            ['search' => $view['translator']->trans('le.lead.lead.searchcommand.email_unsubscribe').':'.$email->getId()]
                        ); ?>" title="<?php echo $view['translator']->trans('le.email.click.viewmore')?>"><span> <i class="fa fa-th-list email-stat-icon"></i></span></a></h2>
                    <?php echo $view->render('MauticEmailBundle:Email:leads.html.php', ['items' => $unsubscribeLeads, 'security' => $security]); ?>
                </div>
            </div>
            <div class="col-md-12 bdr-w-0">
                <div>
                    <h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.email.click.last10bouncedleads')?><a class="" href="<?php echo $view['router']->path(
                            'le_contact_index',
                            ['search' => $view['translator']->trans('le.lead.lead.searchcommand.email_bounce').':'.$email->getId()]
                        ); ?>" title="<?php echo $view['translator']->trans('le.email.click.viewmore')?>"><span> <i class="fa fa-th-list email-stat-icon"></i></span></a></h2>
                    <?php echo $view->render('MauticEmailBundle:Email:leads.html.php', ['items' => $bounceLeads, 'security' => $security]); ?>
                </div>
            </div>
            </div>
            <!-- tabs controls -->
            <ul class="nav hide nav-tabs pr-md pl-md">
                <li class="active">
                    <a href="#clicks-container" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('le.trackable.click_counts'); ?>
                    </a>
                </li>
                <?php if (!$notificationemail): ?>
                <li>
                    <a href="#contacts-container" role="tab" data-toggle="tab">
                        <?php echo $view['translator']->trans('le.email.associated.contacts'); ?>
                    </a>
                </li>
                <?php endif; ?>
                <?php if ($showVariants): ?>
                    <li>
                        <a href="#variants-container" role="tab" data-toggle="tab">
                            <?php echo $view['translator']->trans('mautic.core.variants'); ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($showTranslations): ?>
                    <li>
                        <a href="#translation-container" role="tab" data-toggle="tab">
                            <?php echo $view['translator']->trans('mautic.core.translations'); ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <!--/ tabs controls -->
        </div>

        <!-- start: tab-content -->
        <div class="tab-content pa-md hide">
            <?php if (false): ?>
            <div class="tab-pane bdr-w-0" id="contacts-container">
                <?php echo $contacts; ?>
            </div>
            <?php endif; ?>
            <!-- #translation-container -->
            <?php if ($showTranslations): ?>
                <div class="tab-pane bdr-w-0" id="translation-container">
                    <?php echo $translationContent; ?>
                </div>
            <?php endif; ?>
            <!--/ #translation-container -->
        </div>
    </div>
    <!--/ left section -->

    <!-- right section -->
    <div class="col-md-3 hide bg-white bdr-l height-auto">
        <!-- preview URL -->
        <div class="panel bg-transparent shd-none bdr-rds-0 bdr-w-0 mt-sm mb-0">
            <div class="panel-heading">
                <div class="panel-title"><?php echo $view['translator']->trans('le.email.urlvariant'); ?></div>
            </div>
            <div class="panel-body pt-xs">
                <div class="input-group">
                    <input onclick="this.setSelectionRange(0, this.value.length);" type="text" class="form-control"
                           readonly
                           value="<?php echo $view->escape($previewUrl); ?>"/>
                    <span class="input-group-btn">
                        <button class="btn btn-default btn-nospin" onclick="window.open('<?php echo $previewUrl; ?>', '_blank');">
                            <i class="fa fa-external-link"></i>
                        </button>
                    </span>
                </div>
            </div>
        </div>

        <!-- activity feed -->
        <?php echo $view->render('MauticCoreBundle:Helper:recentactivity.html.php', ['logs' => $logs]); ?>
    </div>
    <!--/ right section -->
    <input name="entityId" id="entityId" type="hidden" value="<?php echo $view->escape($email->getId()); ?>"/>
</div>
