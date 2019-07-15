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

if (!isset($item)) {
    $item = null;
}

if (!isset($tooltip)) {
    $tooltip = null;
}
if (isset($onlyexport)) {
    $view['buttons']->reset($app->getRequest(), ButtonHelper::LOCATION_PAGE_ACTIONS, ButtonHelper::TYPE_GROUP, $item);
} else {
    $view['buttons']->reset($app->getRequest(), ButtonHelper::LOCATION_PAGE_ACTIONS, ButtonHelper::TYPE_BUTTON_DROPDOWN, $item);
}
include 'action_button_helper.php';

foreach ($templateButtons as $action => $enabled) {
    if (!$enabled) {
        continue;
    }

    if (!$enabled) {
        continue;
    }

    $path     = false;
    $primary  = false;
    $priority = 0;
    switch ($action) {
        case 'clone':
        case 'abtest':
            $actionQuery = [
                'objectId' => ('abtest' == $action && method_exists($item, 'getVariantParent') && $item->getVariantParent())
                    ? $item->getVariantParent()->getId() : $item->getId(),
            ];
            $icon = ($action == 'clone') ? 'copy' : 'sitemap';
            $path = $view['router']->path($actionRoute, array_merge(['objectAction' => $action], $actionQuery, $query));
            break;
        case 'close':
            $closeParameters = isset($routeVars['close']) ? $routeVars['close'] : [];
            $icon            = 'remove';
            $path            = $view['router']->path($indexRoute, $closeParameters);
            $primary         = true;
            $priority        = 200;
            break;
        case 'new':
        case'edit':
            $actionQuery = ('edit' == $action) ? ['objectId' => $item->getId()] : [];
            $icon        = ('edit' == $action) ? 'pencil-square-o' : 'plus';
            if (!isset($customAction) || $customAction == '') {
                $customAction = $action;
            }
            $path        = $view['router']->path($actionRoute, array_merge(['objectAction' => $customAction], $actionQuery, $query));
            $primary     = true;
            break;
        case 'delete':
            $view['buttons']->addButton(
                [
                    'confirm' => [
                        'message' => $view['translator']->trans(
                            'mautic.'.$langVar.'.form.confirmdelete',
                            ['%name%' => $item->$nameGetter()/*.' ('.$item->getId().')'*/]
                        ),
                        'confirmAction' => $view['router']->path(
                            $actionRoute,
                            array_merge(['objectAction' => 'delete', 'objectId' => $item->getId()], $query)
                        ),
                        'template' => 'delete',
                        'btnClass' => false,
                    ],
                    'priority' => -1,
                ]
            );
            break;
    }

    if ($path) {
        $mergeAttr = (!in_array($action, ['edit', 'new'])) ? [] : $editAttr;
        if (isset($customAttr)) {
            $mergeAttr = array_merge($mergeAttr, $customAttr);
        }
        $view['buttons']->addButton(
            [
                'attr' => array_merge(
                    [
                        'class'       => 'btn btn-default le-btn-default waves-effect',
                        'href'        => $path,
                        'data-toggle' => 'ajax',
                    ],
                    $mergeAttr
                ),
                'iconClass' => 'fa fa-'.$icon,
                'btnText'   => $view['translator']->trans('mautic.core.form.'.$action),
                'priority'  => $priority,
                'primary'   => $primary,
                'tooltip'   => $tooltip,
            ]
        );
    }
}

if (isset($onlyexport)) {
    echo '<div class="std-toolbar btn-group" style="margin-left: -32px;float:right;">';

    $dropdownOpenHtml = '<button type="button" class="btn btn-default  le-btn-default waves-effect"  >';

    echo $view['buttons']->renderButtons($dropdownOpenHtml, '</button>', 'page_actions');

    echo '</div>';
} else {
    $data='';
    if ($view['buttons']->getButtonCount() > 0) {
        echo '<div class="std-toolbar btn-group" style="margin-left: -32px;float:right;">';
        if (isset($routeBase)) {
            if ($routeBase == 'report') {
                $data ='Export ';
            }
        }
        $dropdownOpenHtml = '<button type="button" class="btn btn-default btn-nospin  dropdown-toggle le-btn-default waves-effect" data-toggle="dropdown" aria-expanded="false"><span>'.$data.'</span><i class="fa fa-caret-down"></i></button>'."\n";
        $dropdownOpenHtml .= '<ul class="dropdown-menu dropdown-menu-right" role="menu" style="z-index: 10000">'."\n";
        echo $view['buttons']->renderButtons($dropdownOpenHtml, '</ul>', 'page_actions');

        echo '</div>';
    }
}

echo $extraHtml;
