<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$searchValue    = (empty($searchValue)) ? '' : trim($searchValue, '"');
$target         = (empty($target)) ? '.page-list' : $target;
$overlayTarget  = (empty($overlayTarget)) ? $target : $overlayTarget;
$overlayEnabled = (!empty($overlayDisabled)) ? 'false' : 'true';
$id             = (empty($searchId)) ? 'list-search' : $searchId;
$tmpl           = (empty($tmpl)) ? 'list' : $tmpl;
$widthstyle     = (empty($merge_search)) ? 'width: 45%;padding-top:12px;' : 'width: 100;';
$isAdmin        = $view['security']->isAdmin();
$isMobile       = $view['security']->isMobile();
?>

<div class="input-group" style="<?php echo $isMobile ? '50%' : $widthstyle; ?>">
    <?php  if ($isAdmin): ?>
        <?php if (!empty($searchHelp)): ?>
            <div class="input-group-btn">
                <button class="btn btn-default btn-nospin waves-effect" data-toggle="modal" data-target="#<?php echo $searchId; ?>-search-help">
                    <i class="fa fa-question-circle"></i>
                </button>
            </div>
        <?php endif; ?>
        <input  type="search" class="form-control search le-filters-button" id="<?php echo $id; ?>" name="search" placeholder="<?php echo $view['translator']->trans('mautic.core.search.placeholder'); ?>" value="<?php echo $view->escape($searchValue); ?>" autocomplete="false" data-toggle="livesearch" data-target="<?php echo $target; ?>" data-tmpl="<?php echo $tmpl; ?>" data-action="<?php echo $action; ?>" data-overlay="<?php echo $overlayEnabled; ?>" data-overlay-text="<?php echo $view['translator']->trans('mautic.core.search.livesearch'); ?>" data-overlay-target="<?php echo $overlayTarget; ?>" />
    <?php else:?>
        <input type="search" class="form-control search le-filters-button" id="<?php echo $id; ?>" name="search" placeholder="<?php echo $view['translator']->trans('mautic.core.search.placeholder'); ?>" value="<?php echo $view->escape($searchValue); ?>" autocomplete="false" data-toggle="livesearch" data-target="<?php echo $target; ?>" data-tmpl="<?php echo $tmpl; ?>" data-action="<?php echo $action; ?>" data-overlay="<?php echo $overlayEnabled; ?>" data-overlay-text="<?php echo $view['translator']->trans('mautic.core.search.livesearch'); ?>" data-overlay-target="<?php echo $overlayTarget; ?>" />
    <?php endif; ?>
    <div class="input-group-btn" style="padding-bottom: 30px;">
        <button type="button" class="btn btn-search btn-nospin le-filters-button-search" id="btn-filter" data-livesearch-parent="<?php echo $id; ?>">
            <i class="fa fa-search fa-fw"></i>
        </button>
    </div>
</div>

<?php
if ($searchHelp):
echo $view->render('MauticCoreBundle:Helper:modal.html.php', [
    'id'     => $searchId.'-search-help',
    'header' => $view['translator']->trans('mautic.core.search.header'),
    'body'   => $view['translator']->trans('mautic.core.search.help').$view['translator']->trans($searchHelp),
]);
endif;
?>
