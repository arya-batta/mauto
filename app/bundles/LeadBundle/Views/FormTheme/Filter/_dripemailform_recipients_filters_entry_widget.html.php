<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$isPrototype = ($form->vars['name'] == '__name__');
$filterType  = $form['field']->vars['value'];
$inGroup     = $form->vars['data']['glue'] === 'and';
$object      = (isset($form->vars['data']['object'])) ? $form->vars['data']['object'] : 'lead';
$class       = (isset($form->vars['data']['object']) && $form->vars['data']['object'] == 'company') ? 'fa-building' : 'fa-user';
$filterindex =$form->vars['name'];
if (!$isPrototype && !isset($fields[$object][$filterType]['label'])) {
    return;
}

?>
<div class="panel">
    <div class="panel-heading hide">
        <div class="panel-glue col-sm-2 pl-0 ">
            <?php echo $view['form']->widget($form['glue']); ?>
        </div>
    </div>
    <div class="panel-body">
        <!--        <div class="col-xs-6 col-sm-3 field-name">-->
        <!--            <i class="object-icon fa --><?php //echo $class;?><!--" aria-hidden="true"></i> <span>--><?php //echo ($isPrototype) ? '__label__' : $fields[$object][$filterType]['label'];?><!--</span>-->
        <!--        </div>-->
        <?php if (!empty($filterfields)): ?>
            <div class="col-xs-6 col-sm-5 field-name" data-filter-index="<?php echo $filterindex ?>">
                <select class="chosen form-control list_filter_fields">
                    <option value=""></option>
                    <?php
                    foreach ($filterfields as $object => $field):
                        $header = $object;
                        $icon   = ($object == 'company') ? 'building' : 'user';
                        ?>
                        <optgroup label="<?php echo $view['translator']->trans('le.leadlist.'.$header); ?>">
                            <?php foreach ($field as $value => $params):?>
                                <option value="<?php echo $view->escape($value); ?>"
                                        class="segment-filter <?php echo $icon; ?>" <?php echo !$isPrototype && isset($fields[$object][$filterType]['label']) && $params['label'] == $fields[$object][$filterType]['label'] ? 'selected="selected"' : ''?>>
                                    <?php echo $view['translator']->trans($params['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="col-xs-6 col-sm-2 padding-none filter-operator-segment">
            <?php echo $view['form']->widget($form['operator']); ?>
        </div>
        <?php $hasErrors = count($form['filter']->vars['errors']) || count($form['display']->vars['errors']); ?>
        <div class="col-xs-10 <?php echo $filterType == 'lead_email_activity' ? 'col-sm-1 lead_filter_padding_right' : 'col-sm-4'?> padding-none<?php if ($hasErrors): echo ' has-error'; endif; ?> filter-field-segment">
            <?php echo $view['form']->widget($form['filter']); ?>
            <?php echo $view['form']->widget($form['display']); ?>
            <?php echo $view['form']->errors($form['filter']); ?>
            <?php echo $view['form']->errors($form['display']); ?>
        </div>
        <div class="col-xs-6 col-sm-2 email-activity email-activity-label <?php echo $filterType == 'lead_email_activity' ? '' : 'hide'; ?>">
            <span>emails</span>
        </div>
        <div class="col-xs-2 col-sm-1">
            <a href="javascript: void(0);" class="remove-selected btn btn-default text-danger pull-right waves-effect"><i class="fa fa-trash-o"></i></a>
        </div>
        <?php echo $view['form']->widget($form['field']); ?>
        <?php echo $view['form']->widget($form['type']); ?>
        <?php echo $view['form']->widget($form['object']); ?>
        <?php echo $view['form']->widget($form['customObject']); ?>
        <?php echo $view['form']->widget($form['fieldlabel']); ?>
    </div>
</div>
