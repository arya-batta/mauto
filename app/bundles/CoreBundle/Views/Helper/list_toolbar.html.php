<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Mautic\CoreBundle\Templating\Helper\ButtonHelper;

$wrap = true;
$view['buttons']->reset($app->getRequest(), ButtonHelper::LOCATION_TOOLBAR_ACTIONS, ButtonHelper::TYPE_GROUP);
include 'action_button_helper.php';
?>
<div class="panel-body" style="background-color: white;">
    <div class="box-layout">
        <div class="col-xs-6 col-lg-9 va-m form-inline">
            <?php if (isset($searchValue)): ?>
            <?php echo $view->render('MauticCoreBundle:Helper:search.html.php', [
                    'searchId'    => (empty($searchId)) ? null : $searchId,
                    'searchValue' => $searchValue,
                    'action'      => (isset($action)) ? $action : '',
                    'searchHelp'  => (isset($searchHelp)) ? $searchHelp : '',
                    'target'      => (empty($target)) ? null : $target,
                    'tmpl'        => (empty($tmpl)) ? null : $tmpl,
                    'merge_search'=> '',
                ]); ?>
            <?php endif; ?>

            <?php if (!empty($filters)): ?>
            <?php echo $view->render('MauticCoreBundle:Helper:list_filters.html.php', [
                    'filters' => $filters,
                    'target'  => (empty($target)) ? null : $target,
                    'tmpl'    => (empty($tmpl)) ? null : $tmpl,
                ]); ?>
            <?php endif; ?>
        </div>

        <div class="col-xs-6 col-lg-4 va-m text-right hide">
            <?php if (!empty($buttonHelp)): ?>
                 <div class="input-group-btn">
                    <button class="btn btn-default btn-nospin waves-effect" data-toggle="modal" data-target="#<?php echo $searchId; ?>-search-help">
                        <i class="fa fa-question-circle"></i>
                    </button>
                </div>
            <?php endif; ?>
            <?php echo $view['buttons']->renderButtons(); ?>
        </div>
        <div class="col-xs-4 col-sm-6 va-m">
            <div class="toolbar text-right" id="toolbar">
                <?php $view['slots']->output('actions'); ?>

                <div class="toolbar-bundle-buttons pull-left"><?php $view['slots']->output('toolbar'); ?></div>
                <div class="toolbar-form-buttons hide pull-right">
                    <div class="btn-group toolbar-standard hidden-xs hidden-sm "></div>
                    <div class="btn-group toolbar-dropdown hidden-md hidden-lg">
                        <button type="button" class="btn btn-default btn-main waves-effect"></button>
                        <button type="button" class="btn btn-default btn-nospin  dropdown-toggle waves-effect" data-toggle="dropdown"
                                aria-expanded="false"><i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                    </div>
                </div>
                <?php echo $view['content']->getCustomContent('page.header.right', $mauticTemplateVars); ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
