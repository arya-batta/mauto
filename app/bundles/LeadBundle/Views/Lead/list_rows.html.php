<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$stageaccess=$security->isGranted('stage:stages:view');
$isAdmin    =$view['security']->isAdmin();
?>
<?php foreach ($items as $item): ?>
    <?php /** @var \Mautic\LeadBundle\Entity\Lead $item */ ?>
    <?php $fields = $item->getFields(); ?>
    <tr<?php if (!empty($highlight)): echo ' class="warning"'; endif; ?>>
        <td class="">
            <?php
            $hasEditAccess = $security->hasEntityAccess(
                $permissions['lead:leads:editown'],
                $permissions['lead:leads:editother'],
                $item->getPermissionUser()
            );

            $custom = [];
            if ($hasEditAccess && !empty($currentList)) {
                //this lead was manually added to a list so give an option to remove them
                $custom[] = [
                    'attr' => [
                        'href' => $view['router']->path('le_segment_action', [
                            'objectAction' => 'removeLead',
                            'objectId'     => $currentList['id'],
                            'leadId'       => $item->getId(),
                        ]),
                        'data-toggle' => 'ajax',
                        'data-method' => 'POST',
                    ],
                    'btnText'   => 'le.lead.lead.remove.fromlist',
                    'iconClass' => 'fa fa-remove',
                ];
            }

            if (!empty($fields['core']['email']['value'])) {
                $custom[] = [
                    'attr' => [
                        'data-toggle' => 'ajaxmodal',
                        'data-target' => '#MauticSharedModal',
                        'data-header' => $view['translator']->trans('le.lead.email.send_email.header', ['%email%' => $fields['core']['email']['value']]),
                        'href'        => $view['router']->path('le_contact_action', ['objectId' => $item->getId(), 'objectAction' => 'email', 'list' => 1]),
                    ],
                    'btnText'   => 'le.lead.email.send_email',
                    'iconClass' => 'fa fa-send',
                ];
            }

            echo $view->render('MauticCoreBundle:Helper:list_actions.html.php', [
                'item'            => $item,
                'templateButtons' => [
                    'edit'   => $hasEditAccess,
                    'delete' => $security->hasEntityAccess($permissions['lead:leads:deleteown'], $permissions['lead:leads:deleteother'], $item->getPermissionUser()),
                ],
                'routeBase'     => 'contact',
                'langVar'       => 'lead.lead',
                'customButtons' => $custom,
            ]);
            ?>
        </td>
        <td class="table-description">
            <a href="<?php echo $view['router']->path('le_contact_action', ['objectAction' => 'view', 'objectId' => $item->getId()]); ?>" data-toggle="ajax">
                <?php if (in_array($item->getId(), array_keys($noContactList)))  : ?>
                    <div class="pull-right label label-danger"><i class="fa fa-ban"> </i></div>
                <?php endif; ?>
                <div> <?php echo ($item->isAnonymous()) ? $view['translator']->trans($item->getPrimaryIdentifier()) : $item->getPrimaryIdentifier(); ?></div>
                <div class="small"><?php echo $item->getSecondaryIdentifier(); ?></div>
                <div><?php echo $fields['core']['email']['value']; ?></div>
            </a>
        </td>
        <td class="visible-md visible-lg">
            <?php $colors = ['#ec407a', '#00a65a', '#f39c12', '#3c8dbc', '#dd4b39']; ?>
            <?php $tags   = $item->getTags(); ?>
            <?php $count  =  0; ?>
            <?php foreach ($tags as $tag):  ?>
                <?php if ($count == 5):
                     $count=0;
                endif; ?>
               <div class="label label-primary"  style="margin-bottom: 2px;background-color:<?php echo $colors[$count] ?>;"><?php echo $tag->getTag(); ?></div>
            <?php ++$count; ?>
            <?php endforeach; ?>
        </td>
        <td class="visible-md visible-lg" style="text-align:center;">
           <?php
            $score = (!empty($fields['core']['score']['value'])) ? $view['assets']->getLeadScoreIcon($fields['core']['score']['value']) : '';
           ?>
           <img src="<?php echo $score; ?>" style="max-height: 25px;" />

        </td>
        <td class="visible-md visible-lg text-center">
            <?php
            $color = $item->getColor();
            $style = !empty($color) ? ' style="background-color: '.$color.';"' : '';
            ?>
            <span class="label label-primary"><?php echo $item->getPoints(); ?></span>
        </td>
        <td class="visible-md visible-lg">
            <abbr title="<?php echo $view['date']->toFull($item->getLastActive()); ?>">
                <?php echo $view['date']->toText($item->getLastActive()); ?>
            </abbr>
        </td>
        <td class="visible-md visible-lg">
            <?php
            $flag = (!empty($fields['core']['country'])) ? $view['assets']->getCountryFlag($fields['core']['country']['value']) : '';
            if (!empty($flag)):
                ?>
                <img src="<?php echo $flag; ?>" style="max-height: 24px;" class="mr-sm" />
            <?php
            endif;
            $location = [];
            if (!empty($fields['core']['city']['value'])):
                $location[] = $fields['core']['city']['value'];
            endif;
            if (!empty($fields['core']['state']['value'])):
                $location[] = $fields['core']['state']['value'];
            elseif (!empty($fields['core']['country']['value'])):
                $location[] = $fields['core']['country']['value'];
            endif;
            echo implode(', ', $location);
            ?>
            <div class="clearfix"></div>
        </td>
        <?php if ($stageaccess)  : ?>
            <td class="hide text-center">
                <?php
                $color = $item->getColor();
                $style = !empty($color) ? ' style="background-color: '.$color.';"' : '';
                ?>
                <?php if ($item->getStage()):?>
                    <span class="label label-default"<?php echo $style; ?>><?php echo $item->getStage()->getName(); ?></span>
                <?php endif?>
            </td>
        <?php endif; ?>
        <?php  if ($isAdmin): ?>
        <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
        <?php  endif; ?>
        <td >
            <?php $hasEditAccess   = $security->hasEntityAccess($permissions['lead:leads:editown'], $permissions['lead:leads:editother'], $item->getPermissionUser());
                  $hasDeleteAccess = $security->hasEntityAccess($permissions['lead:leads:deleteown'], $permissions['lead:leads:deleteother'], $item->getPermissionUser()); ?>


            <div style="position: relative;" class="fab-list-container">
                <div class="md-fab-wrapper">
                    <div class="md-fab md-fab-toolbar md-fab-small md-fab-primary" id="mainClass-<?php echo $item->getId(); ?>" style="">
                        <i class="material-icons" onclick="Le.showActionButtons('<?php echo $item->getId(); ?>')"></i>
                        <div tabindex="0" class="md-fab-toolbar-actions toolbar-actions-<?php echo $item->getId(); ?>" id="toolbar-lead">
                            <?php if ($hasEditAccess): ?>
                                <a class="hidden-xs-sm -nospin" title="<?php echo $view['translator']->trans('mautic.core.form.edit'); ?>" href="<?php echo $view['router']->path('le_contact_action', ['objectAction' => 'edit', 'objectId' => $item->getId()]); ?>" data-toggle="ajax">
                                    <span><i class="material-icons md-color-white">  </i></span></a>
                            <?php endif; ?>
                            <?php if ($hasDeleteAccess):?>
                                <a data-toggle="confirmation" href="<?php echo $view['router']->path('le_contact_action', ['objectAction' => 'delete', 'objectId' => $item->getId()]); ?>" data-message="<?php echo $view->escape($view['translator']->trans('le.lead.lead.events.delete', ['%name%'=> $item->getName()])); ?>" data-confirm-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.delete')); ?>" data-confirm-callback="executeAction" title="<?php echo $view['translator']->trans('mautic.core.form.delete'); ?>" data-cancel-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.cancel')); ?>">
                                    <span><i class="material-icons md-color-white">  </i></span>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($fields['core']['email']['value'])) : ?>
                                <a title="<?php echo $view['translator']->trans('le.lead.email.send_email'); ?>" data-toggle="ajaxmodal" data-target="#MauticSharedModal" data-header="<?php echo $view['translator']->trans('le.lead.email.send_email.header', ['%email%' => $fields['core']['email']['value']]); ?>" href="<?php echo $view['router']->path('le_contact_action', ['objectId' => $item->getId(), 'objectAction' => 'email', 'list' => 1]); ?>" class="">
                                    <span><i class="material-icons md-color-white">  </i></span></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </td>
    </tr>
<?php endforeach; ?>