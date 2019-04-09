<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($view['security']->isAdmin() || $view['security']->isCustomAdmin()) {
    $isadmin = true;
} else {
    $isadmin = false;
}
?>
<?php if ($isadmin): ?>

    <li class="dropdown d-none d-sm-block" id="notificationsDropdown">
        <?php $hideClass2 = (!empty($updateMessage['isNew']) || !empty($showNewIndicator)) ? '' : ' hide'; ?>
        <a href="#" data-target="#" onclick="Le.showNotifications();" class="dropdown-toggle waves-effect waves-light notification-icon-box" data-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-bell"></i> <span class="badge badge-xs badge-danger <?php echo $hideClass2; ?>" style="display: inline-block;margin-left: -12px;" id="newNotificationIndicator"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-lg">
            <?php $hideclass3 = (empty($notifications)) ? ' hide' : ''; ?>
            <li class="text-center notifi-title"><?php echo $view['translator']->trans('mautic.core.notifications'); ?><span class="badge badge-xs badge-success"><?php echo sizeof($notifications) ?></span>
                <a style="margin-right: 10px;" href="javascript:void(0);" class="btn btn-default btn-xs btn-nospin pull-right do-not-close <?php echo $hideclass3?>" data-toggle="tooltip" title="<?php echo $view['translator']->trans('mautic.core.notifications.clearall'); ?>" onclick="Le.clearNotification(0);"><i class="fa fa-times do-not-close"></i></a>
            </li>
            <div class="scroll-content slimscroll" style="height:250px;" id="notifications">
            <li class="list-group">
                <?php echo $view->render('MauticCoreBundle:Notification:notification_messages.html.php', [
                    'notifications' => $notifications,
                    'updateMessage' => $updateMessage,
                ]); ?>
                <?php $hideclass1 = (!empty($notifications)) ? ' hide' : ''; ?>
                <div class="list-group-item-empty <?php echo $hideclass1?>" id="notificationLEbot">
                    <?php echo $view['translator']->trans('le.core.empty.notifications'); ?>
                </div>
            </li>
            </div>
        </ul>
        <?php $lastNotification = reset($notifications); ?>
        <input id="leLastNotificationId" type="hidden" value="<?php echo $view->escape($lastNotification['id']); ?>" />
    </li>
<!--    <li class="dropdown dropdown-custom" id="notificationsDropdown" style="margin-left: 3px;">-->
<!--        <a href="javascript: void(0);" onclick="Le.showNotifications();" class="dropdown-toggle dropdown-notification" data-toggle="dropdown">-->
<!--            --><?php //$hideClass = (!empty($updateMessage['isNew']) || !empty($showNewIndicator)) ? '' : ' hide';?>
<!--             <span class="label label-danger--><?php //echo $hideClass;?><!--" id="newNotificationIndicator"><i class="fa fa-asterisk"></i></span>-->
<!--            <span class="fa fa-bell-o" style="font-size: 16px;"></span>-->
<!--        </a>-->
<!--        <div class="dropdown-menu">-->
<!--            <div class="panel panel-default">-->
<!--                <div class="panel-heading">-->
<!--                    <div class="panel-title">-->
<!--                        <h6 class="fw-sb">--><?php //echo $view['translator']->trans('mautic.core.notifications');?>
<!--                            <a href="javascript:void(0);" class="btn btn-default btn-xs btn-nospin pull-right text-danger" data-toggle="tooltip" title="--><?php //echo $view['translator']->trans('mautic.core.notifications.clearall');?><!--" onclick="Le.clearNotification(0);"><i class="fa fa-times"></i></a>-->
<!--                        </h6>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="pt-0 pb-xs pl-0 pr-0">-->
<!--                    <div class="scroll-content slimscroll" style="height:250px;" id="notifications">-->
<!--                        --><?php //echo $view->render('MauticCoreBundle:Notification:notification_messages.html.php', [
//                            'notifications' => $notifications,
//                            'updateMessage' => $updateMessage,
//                        ]);?>
<!--                        --><?php //$class = (!empty($notifications)) ? ' hide' : '';?>
<!--                        <div style="width: 100px; margin: 75px auto 0 auto;" class="--><?php //echo $class;?><!-- LEbot-image" id="notificationLEbot">-->
<!--                            <img class="img img-responsive" src="--><?php //echo $view['mautibot']->getImage('wave');?><!--" />-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--                --><?php //$lastNotification = reset($notifications);?>
<!--                <input id="leLastNotificationId" type="hidden" value="--><?php //echo $view->escape($lastNotification['id']);?><!--" />-->
<!--            </div>-->
<!--        </div>-->
<!--    </li>-->
<?php endif; ?>
