<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
    <li  class="dropdown d-none d-sm-block" id="globalSearchDropdown">
    <ul style="margin-left: 60px;margin-top: 60px;width: 300px;" class="dropdown-menu dropdown-menu-lg">
        <div class="scroll-content slimscroll" style="height:250px;width: 300px;" id="globalSearchResults">
            <?php echo $view->render('MauticCoreBundle:GlobalSearch:results.html.php', [
                'results' => $results,
            ]); ?>
        </div>
    </ul>
    </li>
<li class="hide-phone app-search float-left">
    <div class="search-container navbar-form" id="globalSearchContainer">
        <input type="text" value="" placeholder="<?php echo $view['translator']->trans('mautic.core.search.everything.placeholder'); ?>" id="globalSearchInput" name="global_search" autocomplete="false" data-toggle="livesearch" data-target="#globalSearchResults" data-action="<?php echo $view['router']->path('le_core_ajax', ['action' => 'globalSearch']); ?>" data-overlay="true" data-overlay-text="<?php echo $view['translator']->trans('mautic.core.search.livesearch'); ?>" class="search-bar">
    </div>
</li>