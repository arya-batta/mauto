<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$filterindex =$form->vars['name'];
?>
<div class="panel" data-mapping-index="<?php echo $filterindex ?>">
    <div class="panel-body">
        <div class="col-sm-3">
            <?php echo $view['form']->widget($form['localfield']); ?>
        </div>
        <div class="col-sm-5">
            <?php echo $view['form']->widget($form['remotefield']); ?>
        </div>
        <div class="col-sm-3 mapping-default-segment">
            <?php echo $view['form']->widget($form['defaultvalue']); ?>
        </div>
        <div class="col-sm-1">
            <a href="javascript: void(0);" class="remove-selected btn btn-default text-danger pull-right waves-effect"><i class="fa fa-trash-o"></i></a>
        </div>
    </div>
</div>
