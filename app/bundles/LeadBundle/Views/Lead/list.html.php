<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($tmpl == 'index') {
    $view->extend('MauticLeadBundle:Lead:index.html.php');
}
$stageaccess   =$security->isGranted('stage:stages:view');
$isAdmin       =$view['security']->isAdmin();
$customButtons = [];
$changeStage   =[];
if ($permissions['lead:leads:editown'] || $permissions['lead:leads:editother']) {
    $customButton = [
        [
            'attr' => [
                'class'       => 'btn btn-default btn-sm btn-nospin waves-effect',
                'data-toggle' => 'ajaxmodal',
                'data-target' => '#leSharedModal',
                'href'        => $view['router']->path('le_contact_action', ['objectAction' => 'batchTags']),
                'data-header' => $view['translator']->trans('le.lead.batch.tags'),
            ],
            'btnText'   => $view['translator']->trans('le.lead.batch.tags'),
            'iconClass' => 'fa fa-pie-chart',
        ],
        [
            'attr' => [
                'class'       => 'btn btn-default btn-sm btn-nospin waves-effect hide',
                'data-toggle' => 'ajaxmodal',
                'data-target' => '#leSharedModal',
                'href'        => $view['router']->path('le_contact_action', ['objectAction' => 'batchLists']),
                'data-header' => $view['translator']->trans('le.lead.batch.lists'),
            ],
            'btnText'   => $view['translator']->trans('le.lead.batch.lists'),
            'iconClass' => 'fa fa-pie-chart',
        ],
        [
            'attr' => [
                'class'       => 'btn btn-default btn-sm btn-nospin waves-effect',
                'data-toggle' => 'ajaxmodal',
                'data-target' => '#leSharedModal',
                'href'        => $view['router']->path('le_contact_action', ['objectAction' => 'batchListOptin']),
                'data-header' => $view['translator']->trans('le.lead.batch.listoptin'),
            ],
            'btnText'   => $view['translator']->trans('le.lead.batch.listoptin'),
            'iconClass' => 'fa fa-list-ul',
        ],
        [
            'attr' => [
                'class'       => $isAdmin ? 'btn btn-default btn-sm btn-nospin waves-effect' : 'hide',
                'data-toggle' => 'ajaxmodal',
                'data-target' => '#leSharedModal',
                'href'        => $view['router']->path('le_contact_action', ['objectAction' => 'batchCampaigns']),
                'data-header' => $view['translator']->trans('le.lead.batch.campaigns'),
            ],
            'btnText'   => $view['translator']->trans('le.lead.batch.campaigns'),
            'iconClass' => 'fa fa-clock-o',
        ],
        [
            'attr' => [
                'class'       => 'btn btn-default btn-sm btn-nospin waves-effect',
                'data-toggle' => 'ajaxmodal',
                'data-target' => '#leSharedModal',
                'href'        => $view['router']->path('le_contact_action', ['objectAction' => 'batchOwners']),
                'data-header' => $view['translator']->trans('le.lead.batch.owner'),
            ],
            'btnText'   => $view['translator']->trans('le.lead.batch.owner'),
            'priority'  => 1,
            'iconClass' => 'fa fa-user',
        ],
        [
            'attr' => [
                'class'       => 'hidden-xs btn btn-default btn-sm btn-nospin waves-effect',
                'data-toggle' => 'ajaxmodal',
                'data-target' => '#leSharedModal',
                'href'        => $view['router']->path('le_contact_action', ['objectAction' => 'batchDnc']),
                'data-header' => $view['translator']->trans('le.lead.batch.dnc'),
            ],
            'btnText'   => $view['translator']->trans('le.lead.batch.dnc'),
            'iconClass' => 'fa fa-ban text-danger',
        ],
    ];
    if ($stageaccess) {
        $changeStage= [
                [
                'attr' => [
                    'class'       => 'btn btn-default btn-sm btn-nospin waves-effect',
                    'data-toggle' => 'ajaxmodal',
                    'data-target' => '#leSharedModal',
                    'href'        => $view['router']->path('le_contact_action', ['objectAction' => 'batchStages']),
                    'data-header' => $view['translator']->trans('le.lead.batch.stages'),
                ],
                'btnText'   => $view['translator']->trans('le.lead.batch.stages'),
                'iconClass' => 'fa fa-tachometer',
               ],
            ];
    }
    $custom[]     = array_merge($changeStage, $customButton);
    $customButtons=$custom[0];
}
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

?>

<?php if (count($items)): ?>
    <?php if ($isEmailSearch):?>
        <div class="alert alert-info le-alert-info" id="form-action-placeholder" style="width:98%;margin-left:10px;margin-right:20px;">
            <p><?php echo $view['translator']->trans('le.leads.stats.info'); ?></p>
        </div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered" id="leadTable">
            <thead>
            <tr>
                <?php
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'checkall'        => 'true',
                    'target'          => '#leadTable',
                    'templateButtons' => [
                        'delete' => $permissions['lead:leads:deleteown'] || $permissions['lead:leads:deleteother'],
                    ],
                    'customButtons' => $customButtons,
                    'langVar'       => 'lead.lead',
                    'routeBase'     => 'contact',
                    'tooltip'       => $view['translator']->trans('le.lead.list.checkall.help'),
                ]);

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.lastname, l.firstname, l.company, l.email',
                    'text'       => 'mautic.core.type.lead',
                    'class'      => 'col-lead-name col-lead-width',
                ]);
                /*echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.company_new',
                    'text'       => 'mautic.core.company',
                    'class'      => 'col-lead-company visible-md visible-lg',
                ]);*/
               /* echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.email',
                    'text'       => 'mautic.core.type.contact',
                    'class'      => 'col-lead-email',
                ]);*/
               if ($isAdmin):
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'text'       => 'mautic.core.type.tags',
                    'class'      => 'col-lead-tags',
                ]);
               endif;
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.score',
                    'text'       => 'mautic.core.type.score',
                    'class'      => 'col-lead-score text-center',
                ]);

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.points',
                    'text'       => 'le.lead.points',
                    'class'      => 'visible-md visible-lg text-center col-lead-points adjust-points-width',
                ]);
               /* echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.mobile',
                    'text'       => 'mautic.core.type.mobile',
                    'class'      => 'col-lead-email visible-md visible-lg',
                ]);
                if ($stageaccess) {
                    echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                        'sessionVar' => 'lead',
                        'orderBy'    => 'l.stage_id',
                        'text'       => 'le.lead.stage.label',
                        'class'      => 'col-lead-stage',
                    ]);
                }*/
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.date_added',
                    'text'       => 'le.lead.dateadded',
                    'class'      => 'col-lead-lastactive visible-md visible-lg adjust-time-width',
                ]);

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.last_active',
                    'text'       => 'le.lead.lastactive',
                    'class'      => 'col-lead-lastactive visible-md visible-lg adjust-time-width',
                    'default'    => true,
                ]);

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.city, l.state',
                    'text'       => 'le.lead.lead.thead.location',
                    'class'      => 'col-lead-location visible-md visible-lg',
                ]);
                if ($isAdmin):
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => 'l.id',
                    'text'       => 'mautic.core.id',
                    'class'      => 'col-lead-id visible-md visible-lg',
                ]);
                endif;
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'lead',
                    'orderBy'    => '',
                    'text'       => 'mautic.core.actions',
                    'class'      => 'col-lead-location visible-md visible-lg col-lead-actions',
                ]);
                ?>
            </tr>
            </thead>
            <tbody>
            <?php echo $view->render('MauticLeadBundle:Lead:list_rows.html.php', [
                'items'         => $items,
                'security'      => $security,
                'currentList'   => $currentList,
                'permissions'   => $permissions,
                'noContactList' => $noContactList,
            ]); ?>
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <?php echo $view->render('MauticCoreBundle:Helper:pagination.html.php', [
            'totalItems' => $totalItems,
            'page'       => $page,
            'limit'      => $limit,
            'menuLinkId' => 'le_contact_index',
            'baseUrl'    => $view['router']->path('le_contact_index'),
            'tmpl'       => $indexMode,
            'sessionVar' => 'lead',
        ]); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
