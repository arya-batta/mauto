<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$group             =0;
$filter_group_start=false;
foreach ($form as $i => $filter) {
    $html        ='';
    $isPrototype = ($filter->vars['name'] == '__name__');
    $filterType  = $filter['field']->vars['value'];
    $andGlue     = $filter->vars['data']['glue'] === 'and';
    if (!$isPrototype) {
        if (!$andGlue || $i == 0) {
            $filter_group_start = true;
            $group              = $group + 1;
            if ($i > 0) {
                $html .= '</div>'; //to close filter panel holder
                $html .= $addconditionbtn;
                $html .= '</div>'; //to close main panel holder
            }
        } elseif ($andGlue) {
            $filter_group_start = false;
        }
        if ($filter_group_start) {
            $html .= "<div class='leadlist-filter-group filter-and-group'><div class='filter-panel-holder'>";
        }
    }
    foreach ($form->parent->vars['fields'] as $object => $objectfields) {//$isPrototype ||
        if (isset($objectfields[$filter->vars['value']['field']])) {
            $html .= $view['form']->widget($filter, ['first' => ($i === 0), 'filterfields'=> isset($filterfields) ? $filterfields : []]);
        }
    }
    if (!$isPrototype && $i == sizeof($form) - 1) {
        $html .= '</div>'; //to close filter panel holder
        $html .= $addconditionbtn;
        $html .= '</div>'; //to close main panel holder
    }
    echo $html;
}
