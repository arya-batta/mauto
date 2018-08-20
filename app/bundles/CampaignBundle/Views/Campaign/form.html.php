<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');

$header = ($entity->getId()) ?
    $view['translator']->trans('mautic.campaign.menu.edit',
        ['%name%' => $view['translator']->trans($entity->getName())]) :
    $view['translator']->trans('mautic.campaign.menu.new');
$view['slots']->set('headerTitle', $header);
$isAdmin=$view['security']->isAdmin();
?>

<?php
if ($items != null && !empty($items)):
echo $view->render(
    'MauticCoreBundle:Helper:workflow_selecttype.html.php',
        [
            'item'               => $entity,
            'Campaigns'          => $items,
            'actionRoute'        => $actionRoute,
            'typePrefix'         => 'form',
            'cancelUrl'          => 'mautic_campaign_index',
            'header'             => 'mautic.campaign.type.choose.header',
            'template'           => 'mautic.campaign.type.template.header',
            'blanktemplate'      => 'mautic.campaign.type.blanktemplate.header',
        ]
    );
endif;
?>
<?php
$actions    = trim($view->render('MauticCampaignBundle:Campaign:events.html.php', ['events' => $events['action']]));
echo $view->render('MauticCampaignBundle:Campaign:builder.html.php', [
    'campaignId'      => $form['sessionId']->vars['data'],
    'campaignEvents'  => $campaignEvents,
    'campaignSources' => $campaignSources,
    'eventSettings'   => $eventSettings,
    'canvasSettings'  => $entity->getCanvasSettings(),
    'form'            => $form,
    'actions'         => $actions,
    'actionRoute'     => $actionRoute,
    'entity'          => $entity,
]);

?>

