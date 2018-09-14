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
$view['slots']->set('mauticContent', 'accountinfo');
$view['slots']->set('headerTitle', $view['translator']->trans('leadsengage.accountinfo.header.title'));
$hidepanel  =$view['security']->isAdmin() ? '' : "style='display: none;'";
?>
<!-- start: box layout -->
<div class="box-layout">
    <!-- step container -->
    <?php echo $view->render('MauticSubscriptionBundle:AccountInfo:steps.html.php', [
        'step'                => 'accountinfo',
        'typePrefix'          => $typePrefix,
        'actionRoute'         => $actionRoute,
        'planType'            => $planType,
    ]); ?>
   <!-- container -->
    <div class="col-md-9 bg-auto height-auto bdr-l accountinfo">

        <!-- Tab panes -->
        <div class="tab-content">

            <?php echo $view['form']->start($form); ?>
            <div role="tabpanel" class="tab-pane fade in active bdr-w-0" id="accountinfo">
                <div class="pt-md pr-md pl-md pb-md">
                    <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo $view['translator']->trans('leadsengage.accountinfo.title'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6"><div class="row"><div class="form-group col-xs-12 ">
                                <?php echo $view['form']->label($form['accountname']); ?>
                                <?php echo $view['form']->widget($form['accountname']); ?>
                            </div></div></div>
                            <div class="col-md-6"><div class="row"><div class="form-group col-xs-12 ">
                                <?php echo $view['form']->label($form['domainname']); ?>
                                <?php echo $view['form']->widget($form['domainname']); ?>
                            </div></div></div>
                            <div class="col-md-6"><div class="row"><div class="form-group col-xs-12 ">
                                <?php echo $view['form']->label($form['email']); ?>
                                <?php echo $view['form']->widget($form['email']); ?>
                            </div></div></div>
                            <div class="col-md-6"><div class="row"><div class="form-group col-xs-12 ">
                                <?php echo $view['form']->label($form['phonenumber']); ?>
                                <?php echo $view['form']->widget($form['phonenumber']); ?>
                            </div></div></div>
                            <div class="col-md-6" <?php echo $hidepanel ?>><div class="row"><div class="form-group col-xs-12 ">
                                <?php echo $view['form']->label($form['currencysymbol']); ?>
                                <?php echo $view['form']->widget($form['currencysymbol']); ?>
                            </div></div></div>
                            <div class="col-md-6"><div class="row"><div class="form-group col-xs-12 ">
                                <?php echo $view['form']->label($form['timezone']); ?>
                                <?php echo $view['form']->widget($form['timezone']); ?>
                            </div></div></div>

                            <div class="col-md-6 hide"><div class="row"><div class="form-group col-xs-12 ">
                                <?php echo $view['form']->label($form['accountid']); ?>
                                <?php echo $view['form']->widget($form['accountid']); ?>
                            </div></div></div>

                            <div class="col-md-6"><div class="row"><div class="form-group col-xs-12 ">
                                <?php echo $view['form']->label($form['website']); ?>
                                <?php echo $view['form']->widget($form['website']); ?>
                            </div></div></div>

                            <div class="hide col-md-6"><div class="row"><div class="form-group col-xs-12 ">
                                <?php echo $view['form']->label($form['needpoweredby']); ?>
                                <?php echo $view['form']->widget($form['needpoweredby']); ?>
                            </div></div></div>


                        </div>
                    </div>
                </div>
                </div>
            </div>
            <?php echo $view['form']->end($form); ?>
        </div>
    </div>
</div>
