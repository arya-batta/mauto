<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view['slots']->set('mauticContent', 'form');

?>
<div class="row">
    <?php
    $isSelected = false;
    if ($entity->getName() == '') {
        $isSelected = false;
    }
    ?>
    <div class="col-md-3 theme-list">
        <div class="panel panel-default <?php echo $isSelected ? 'theme-selected' : '' ?>">
            <div class="panel-body text-center">
                <h4 style="height: 30px">Start From Scratch</h4>
                    <div class="panel-body text-center" style="height: 250px">
                        <i class="fa fa-file-image-o fa-5x text-muted" aria-hidden="true" style="padding-top: 75px; color: #E4E4E4;"></i>
                    </div>
                <a onclick="Mautic.openNewFormAction('<?php echo $newFormURL; ?>');" type="button" class="select-theme-link btn btn-default <?php echo $isSelected ? 'hide' : '' ?>" >
                    Select
                </a>
                <button type="button" class="select-theme-selected btn btn-default <?php echo $isSelected ? '' : 'hide' ?>" disabled="disabled">
                    Selected
                </button>
            </div>
        </div>

    </div>

    <?php for ($i = 0; $i < sizeof($formTemplates); ++$i) : ?>
        <?php
        $isselected = false;
        if ($entity->getName() == $formTemplates[$i]['name']) {
            $isselected = true;
        }
        ?>

        <div class="col-md-3 theme-list" id="form_template_<?php echo $formTemplates[$i]['form_type']; ?>">
            <div class="panel panel-default <?php echo $isselected ? 'theme-selected' : '' ?>">
                <div class="panel-body text-center">
                    <h4 style="height: 30px"><?php echo $formTemplates[$i]['name']; ?></h4>
                    <div style="background-image: url(<?php echo $formTemplates[$i]['imageurl'] ?>);background-repeat:no-repeat;background-size:contain; background-position:center; width: 100%; height: 250px"></div>
                    <a href="<?php echo $view['router']->generate('le_form_action', ['objectAction' => 'cloneFormTemplate', 'objectId' => $formTemplates[$i]['id']]); ?>" type="button" class="select-theme-link btn btn-default <?php echo $isselected ? 'hide' : '' ?>" onclick="mQuery('#dynamic-content-tab').addClass('hidden')">
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