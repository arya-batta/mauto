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
$view['slots']->set('leContent', 'Webhook');
$isAdmin=$view['security']->isAdmin();
$header = ($entity->getId()) ?
    $view['translator']->trans('mautic.webhook.webhook.header.edit',
        ['%name%' => $view['translator']->trans($entity->getName())]) :
    $view['translator']->trans('mautic.webhook.webhook.header.new');

$view['slots']->set('headerTitle', $header);

?>
    <div id="smart-action-form" class="alert alert-info le-alert-info " style="display: flex;margin-top: 5px;" >
        <p><?php echo $view['translator']->trans('le.smart.form.webhook.header'); ?></p>
    </div>
<?php echo $view['form']->start($form); ?>
<!-- start: box layout -->
<div class="box-layout">
    <!-- container -->

    <div class="col-md-12 bg-auto height-auto" >
        <div style="width: 68%;margin-left: 16%">
        <div class="pa-md">
            <div class="panel panel-default form-group mb-0">
                <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <?php // echo $view['form']->row($form['name']); ?>
                    <?php // echo $view['form']->row($form['description']); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $view['form']->row($form['webhookUrl']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $view['form']->row($form['isPublished']); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" id="event-types">
                    <?php echo $view['form']->row($form['events']); ?>
                </div>
                <div class="row">
                    <div class="col-md-5 " style="margin-left: 16px;">
                        <?php echo $view['form']->row($form['sendTest']); ?>
                        <span id="spinner" class="fa fa-spinner fa-spin hide"></span>
                        <br>
                        <div id="tester" class="text-left"></div>
                    </div>
                </div>
            </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <div class="col-md-3 bg-white height-auto bdr-l hide">
        <div class="pr-lg pl-lg pt-md pb-md">
            <?php echo $view['form']->row($form['category']); ?>
            <div class="<?php echo $isAdmin ? '' : 'hide' ?>" >
            <?php  echo $view['form']->row($form['eventsOrderbyDir']); ?>
            </div>

        </div>
    </div>
</div>
<?php echo $view['form']->end($form); ?>