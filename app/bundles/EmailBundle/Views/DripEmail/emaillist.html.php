<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<?php if (count($items)): ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered dripemail-list">
            <thead>
            <tr>
                <th class="col-leadfield-orderhandle"></th>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'mautic.core.update.heading.status',
                        'class'      => 'col-status-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.page.report.hits.email_subject',
                        'class'      => 'col-page-titledrip',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.drip.email.graph.line.stats.scheduled',
                        'class'      => 'col-email-stats drip-email-stats',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.email.graph.line.stats.sent',
                        'class'      => 'col-email-stats drip-email-stats',
                        'default'    => true,
                    ]
                );
                /*echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.email.label.list.reads',
                        'class'      => 'col-email-stats drip-email-stats',
                        'default'    => true,
                    ]
                );
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.email.report.hits_count',
                        'class'      => 'col-email-stats drip-email-stats',
                        'default'    => true,
                    ]
                );*/

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.drip.email.graph.line.stats.delay',
                        'class'      => 'col-email-stats col-email-delay',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.email.report.preview',
                        'class'      => 'col-email-stats drip-email-stats',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'email',
                        'orderBy'    => '',
                        'text'       => 'le.email.wizard.sendexample',
                        'class'      => 'col-email-stats drip-email-stats',
                        'default'    => true,
                    ]
                );

                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'email',
                    'orderBy'    => '',
                    'text'       => 'mautic.core.actions',
                    'class'      => 'col-lead-location visible-md visible-lg col-lead-actions',
                ]);
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <?php
                $scheduleTime      = 1;
                $scheduleFrequency = 1;
                $scheduleUnit      = 'days';
                if ($item->getScheduleTime() != '') {
                    $schedule     = explode(' ', $item->getScheduleTime());
                    $scheduleTime = $schedule[0];
                    $scheduleUnit = $schedule[1];
                    if ($schedule[1] == 'hours') {
                        $scheduleFrequency = 2;
                    } elseif ($schedule[1] == 'minutes') {
                        $scheduleFrequency = 3;
                    }
                }
                ?>
                <tr class="drip-emailcol-stats" data-stats="<?php echo $item->getId(); ?>">
                    <td><i class="fa fa-fw fa-ellipsis-v"></i></td>
                    <td>
                        <?php echo $view->render('MauticCoreBundle:Helper:publishstatus_icon.html.php', ['item' => $item, 'model' => 'email']); ?>
                    </td>
                    <td class="table-description">
                        <a href="<?php echo $view['router']->path('le_dripemail_email_action', ['objectId' => $entity->getId(), 'subobjectAction' => 'edit', 'subobjectId' => $item->getId()]); ?>"><span><?php echo $item->getSubject(); ?></span></a>
                    </td>
                    <td class="visible-sm visible-md visible-lg drip-col-stats" data-stats="<?php echo $item->getId(); ?>">
                    <span class="mt-xs has-click-event clickable-stat"
                          id="scheduled-count-<?php echo $item->getId(); ?>">
                            <a data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('le.drip.email.scheduled_leads'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                    <span class="mt-xs has-click-event clickable-stat"
                          id="sent-count-<?php echo $item->getId(); ?>">
                            <a href="<?php echo $view['router']->path(
                                'le_contact_index',
                                ['search' => $view['translator']->trans('le.lead.lead.searchcommand.email_sent').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('le.email.stat.tooltip.drip.sent'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <!--<td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                     <span class="mt-xs has-click-event clickable-stat"
                           id="read-count-<?php echo $item->getId(); ?>">
                            <a href="<?php echo $view['router']->path(
                                'le_contact_index',
                                ['search' => $view['translator']->trans('le.lead.lead.searchcommand.email_read').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('le.email.stat.read.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs has-click-event clickable-stat"
                            id="read-percent-<?php echo $item->getId(); ?>">
                            <a href="<?php echo $view['router']->path(
                                'le_contact_index',
                                ['search' => $view['translator']->trans('le.lead.lead.searchcommand.email_click').':'.$item->getId()]
                            ); ?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('le.email.stat.click.percentage.tooltip'); ?>">
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </a>
                        </span>
                    </td>-->
                    <td class="visible-sm visible-md visible-lg">
                        <div class="row" style="margin-left:0px;margin-right:-25px;width: 140px;">
                            <div class="col-md-5" style="width:50%;">
                            <input type="text" style="height: 33px;background-color: transparent;border-right:0px;" onfocusout="Le.updateFrequencyValue(<?php echo $item->getId(); ?>);" id="drip-email-frequency-value-<?php echo $item->getId(); ?>" class="form-control" value="<?php echo $scheduleTime; ?>" frequencyUnitValue="<?php echo $scheduleUnit?>" />
                            </div>
                            <div class="col-md-7" id="drip-email-delay" style="position:relative;width:70%;margin-left: -29px">
                                <select class="dripemail_form_scheduleTime"  id="drip_emailform_scheduleTime-<?php echo $item->getId(); ?>" onchange="Le.updateDripEmailFrequency(this.value,<?php echo $item->getId(); ?>)" name="emailform[scheduleTime]" class="form-control le-input" data-report-schedule="scheduleUnit" autocomplete="false" style="display: none;">
                                    <option <?php echo $scheduleFrequency == 1 ? 'selected' : ''; ?> value="days">day</option>
                                    <option <?php echo $scheduleFrequency == 2 ? 'selected' : ''; ?> value="hours">hours</option>
                                    <option <?php echo $scheduleFrequency == 3 ? 'selected' : ''; ?> value="minutes">minutes</option>
                                </select>
                            </div>
                        </div>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                          <a class="text-primary le-send-button custom-preview-button"
                             href="<?php echo $view['router']->path('le_dripemail_email_action', ['objectId' => $item->getId(), 'subobjectAction' => 'preview', 'subobjectId' => '1'], true)?>" data-toggle="tooltip"
                             title="<?php echo $view['translator']->trans('le.drip.email.preview.tooltip'); ?>"
                             target="_blank">
                              <span><i class="fa fa-eye le-send-icon"></i></span>
                          </a>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                            <a class="text-primary le-send-button custom-preview-button blue-theme-bg" style="color:#FFFFFF;font-size:13px;" data-toggle = "ajaxmodal" data-target = "#leSharedModal" href="<?php echo $view['router']->path('le_email_campaign_action', ['objectAction' => 'sendExample', 'objectId' => $item->getId()])?>" data-toggle="tooltip"
                               title="<?php echo $view['translator']->trans('le.drip.email.wizard.sendexample.tooltip'); ?>">
                                <span><i class="fa fa-send-o le-send-icon" style="margin-left: -2px;margin-right: 2px;"></i></span>
                            </a>
                    </td>
                    <td>

                        <?php $hasEditAccess = true; //$view['security']->hasEntityAccess($permissions['email:emails:editown'], $permissions['email:emails:editother'], $item->getCreatedBy());
                        $hasDeleteAccess     = true; //$view['security']->hasEntityAccess($permissions['email:emails:deleteown'], $permissions['email:emails:deleteother'], $item->getCreatedBy());
                        $hasCloneAccess      = true; //$permissions['email:emails:create'];?>
                        <div style="position: relative;" class="fab-list-container">
                            <div class="md-fab-wrapper">
                                <div class="md-fab md-fab-toolbar md-fab-small md-fab-primary" id="mainClass-<?php echo $item->getId(); ?>" style="">
                                    <i class="material-icons" onclick="Le.showActionButtons('<?php echo $item->getId(); ?>');"></i>
                                    <div tabindex="0" class="md-fab-toolbar-actions toolbar-actions-<?php echo $item->getId(); ?>">
                                        <?php if ($hasEditAccess): ?>
                                            <a title="<?php echo $view['translator']->trans('mautic.core.form.edit'); ?>" href="<?php echo $view['router']->path('le_dripemail_email_action', ['objectId' => $entity->getId(), 'subobjectAction' => 'edit', 'subobjectId' => $item->getId()]); ?>"><!--onclick="Le.allowEditEmailfromDrip(<?php echo $item->getId() ?>);"-->
                                                <span><i class="material-icons md-color-white">  </i></span></a>
                                        <?php endif; ?>
                                        <?php if ($hasDeleteAccess):?>
                                            <a  title="<?php echo $view['translator']->trans('mautic.core.form.delete'); ?>" onclick="Le.removeEmailfromDrip(<?php echo $item->getId(); ?>,<?php echo $entity->getId(); ?>);" >
                                                <span><i class="material-icons md-color-white">  </i></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticEmailBundle:DripEmail:blueprint.html.php', [
            'entity'          => $entity,
        ]
    ); ?>
<?php endif; ?>
