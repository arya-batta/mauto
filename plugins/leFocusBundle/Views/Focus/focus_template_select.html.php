<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view['slots']->set('leContent', 'focus');
?>
<div class="row">
    <?php
    $isSelected = false;
    //if ($entity->getName() == '') {
    //    $isSelected = true;
    //}
    ?>
    <div class="col-md-3 theme-list">
        <div class="panel panel-default <?php echo $isSelected ? 'theme-selected' : '' ?>" id="focus_select_template">
            <div class="panel-body text-center">
                <h4 style="height: 30px">Start From Scratch</h4>
                    <div class="panel-body text-center" style="height: 250px">
                        <i class="fa fa-file-image-o fa-5x text-muted" aria-hidden="true" style="padding-top: 75px; color: #E4E4E4;"></i>
                    </div>
                <a href="<?php echo $view['router']->generate('le_focus_action', ['objectAction' => 'new', 'objectId' => 'blank']); ?>" type="button" id="focus_select_button" class="select-theme-link btn btn-default <?php echo $isSelected ? 'hide' : '' ?>" >
                    Select
                </a>
                <button type="button" id="focus_selected_button" class="select-theme-selected btn btn-default <?php echo $isSelected ? '' : 'hide' ?>" disabled="disabled">
                    Selected
                </button>
            </div>
        </div>

    </div>

    <?php for ($i = 0; $i < sizeof($focusTemplates); ++$i) : ?>
        <?php
        $isselected = false;
        if ($entity->getName() == $focusTemplates[$i]['name']) {
            $isselected = true;
        }
        ?>

        <div class="col-md-3 theme-list">
            <div class="panel panel-default <?php echo $isselected ? 'theme-selected' : '' ?>">
                <div class="panel-body text-center">
                    <h4 style="height: 30px"><?php echo $focusTemplates[$i]['name']; ?></h4>
                    <div style="background-image: url(<?php echo $focusTemplates[$i]['imageurl'] ?>);background-repeat:no-repeat;background-size:contain; background-position:center; width: 100%; height: 250px"></div>

                    <a href="<?php echo $view['router']->generate('le_focus_action', ['objectAction' => 'cloneTemplate', 'objectId' => $focusTemplates[$i]['id']]); ?>" type="button" class="select-theme-link btn btn-default <?php echo $isselected ? 'hide' : '' ?>" onclick="mQuery('#dynamic-content-tab').addClass('hidden')">
                        Select
                    </a>
                    <button type="button" class="select-theme-selected btn btn-default <?php echo $isselected ? '' : 'hide' ?>" disabled="disabled">
                        Selected
                    </button>
                </div>
            </div>
        </div>
    <?php endfor; ?>
</div>