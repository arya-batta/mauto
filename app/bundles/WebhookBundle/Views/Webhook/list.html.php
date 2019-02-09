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
    $view->extend('MauticWebhookBundle:Webhook:index.html.php');
}
?>

<?php if (count($items)): ?>
    <div class="table-responsive panel-collapse pull out webhook-list">
        <table class="table table-hover table-striped table-bordered webhook-list" id="webhookTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#webhookTable',
                        'routeBase'       => 'webhook',
                        'templateButtons' => [
                            'delete' => $permissions['webhook:webhooks:deleteown'] || $permissions['webhook:webhooks:deleteother'],
                        ],
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'text'       => 'le.lead.import.status',
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'mautic_webhook',
                        'orderBy'    => 'e.webhookUrl',
                        'text'       => 'mautic.webhook.webhook_url',
                        'class'      => 'col-webhook-id visible-md visible-lg',
                    ]
                );?>
                <th class="col-webhook-response" style="color: #000000;text-align: -webkit-center;"><?php echo $view['translator']->trans('mautic.webhook.webhook_response'); ?></th>
                <th class="col-webhook-runtime" style="color: #000000;"><?php echo $view['translator']->trans('mautic.webhook.webhook_runtime'); ?></th>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'mautic_webhook',
                        'orderBy'    => 'e.id',
                        'text'       => 'mautic.core.id',
                        'class'      => 'col-webhook-id visible-md visible-lg',
                    ]
                );
                ?>
            </tr>
            </thead>
            <tbody>
            <?php /** @var \Mautic\WebhookBundle\Entity\Webhook $item */ ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <?php
                        echo $view->render(
                            'MauticCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit' => $view['security']->hasEntityAccess(
                                        $permissions['webhook:webhooks:editown'],
                                        $permissions['webhook:webhooks:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone'  => $permissions['webhook:webhooks:create'],
                                    'delete' => $view['security']->hasEntityAccess(
                                        $permissions['webhook:webhooks:deleteown'],
                                        $permissions['webhook:webhooks:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'routeBase' => 'webhook',
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <div>
                            <?php echo $view->render(
                                'MauticCoreBundle:Helper:publishstatus_icon.html.php',
                                ['item' => $item, 'model' => 'webhook']
                            ); ?>
                        </div>
                    </td>
                    <td class="visible-md visible-lg">
                        <a data-toggle="ajax" href="<?php echo $view['router']->path(
                            'le_webhook_action',
                            ['objectId' => $item->getId(), 'objectAction' => 'edit']
                        ); ?>">
                            <?php echo $item->getWebhookUrl(); ?>
                        </a>
                    </td>
                    <?php $logs=$item->getLogs();
                    $response="";
                    $note="";
                    $runtime="";
                    $date="";

                    foreach ($logs as $log){
                        $response  =$log->getStatusCode() == '200'?"200-":"";
                        $note      = $response =='200-'?"Success":$log->getNote();
                        $date      =$view['date']->toFull($log->getDateAdded());
                        break;
                    }
                    $class= $note != "Success"? $note==""?"label-default":"le-label-danger":"label-success";
                    $value= $note != "Success"? $note==""?"UnAvailable":"Failed":"Success";
                    ?>
                    <td class="visible-md visible-lg" style="text-align: center"><span class="label <?php echo $class;?>" data-toggle="tooltip" data-original-title="<?php echo $note;?>"><?php echo $value; ?></span></td>
                    <td class="visible-md visible-lg"><?php echo $date; ?></td>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="panel-footer">
        <?php echo $view->render(
            'MauticCoreBundle:Helper:pagination.html.php',
            [
                'totalItems' => count($items),
                'page'       => $page,
                'limit'      => $limit,
                'menuLinkId' => 'le_webhook_index',
                'baseUrl'    => $view['router']->path('le_webhook_index'),
                'sessionVar' => 'mautic_webhook',
            ]
        ); ?>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
