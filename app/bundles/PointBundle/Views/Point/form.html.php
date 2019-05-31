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
$view['slots']->set('leContent', 'point');
$isAdmin=$view['security']->isAdmin();
$header = ($entity->getId()) ?
    $view['translator']->trans('mautic.point.menu.edit',
        ['%name%' => $view['translator']->trans($entity->getName())]) :
    $view['translator']->trans('mautic.point.menu.new');
$view['slots']->set('headerTitle', $header);

echo $view['form']->start($form);
?>
<div id="page-wrap">
    <!-- start: box layout -->
    <div class="box-layout border-margin">
        <!-- container -->
        <div class="col-md-12 bg-auto height-auto" id="details">
            <div class="row">
                <div class="col-md-6">
                    <div class="pa-md">
                        <?php echo $view['form']->row($form['name']); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="pa-md">
                        <?php echo $view['form']->row($form['category']); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="pa-md">
                        <?php echo $view['form']->row($form['delta']); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="pa-md">
                        <?php echo $view['form']->row($form['score']); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="pa-md">
                        <?php echo $view['form']->row($form['type']); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="pa-md">
                        <div id="pointActionProperties">
                            <?php
                            if (isset($form['properties'])):
                                echo $view['form']->row($form['properties']);
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div <?php echo $isAdmin ? 'class="pa-md"' : 'class="hide"' ?>>
                        <?php echo $view['form']->row($form['description']);
                        echo $view['form']->row($form['isPublished']); ?>
                    </div>
                </div>
            </div>
            <div class="hide col-md-3 bg-white height-auto">
                <div class="pr-lg pl-lg pt-md pb-md">
                    <?php  echo $view['form']->row($form['category']);
                    echo $view['form']->row($form['isPublished']); ?>
                    <div <?php echo $isAdmin ? '' : 'class="hide"' ?>>
                        <?php
                        echo $view['form']->row($form['publishUp']);
                        echo $view['form']->row($form['publishDown']);
                        ?>
                    </div>
                </div>
            </div>
            <br>
            <br>
            <br>
        </div>
        <?php echo $view['form']->end($form); ?>
</div>
