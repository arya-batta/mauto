<?php
foreach ($forms as $formindex => $form):
    $formname  =$form['name'];
    $formid    =$form['id'];
    $fields    =$form['fields'];
    $fieldcount=sizeof($fields);
    $fieldjson =json_encode($fields);
    $formlabel ='';
    if ($formname != '') {
        $formlabel=  $formname;
    } elseif ($formid != '') {
        $formlabel=  $formid;
    }

    ?>
<div class="smart-form-list">
<div class="smart-form-icon">
    <i class="fa fa-newspaper-o" id="icon-class-leads" style="margin-left: 4px;margin-top: 4px;"></i>
</div>
    <div class="smart-form-info-holder">
        <div class="smart-form-name">
            <a class='smart-form-link' data-formname='<?php echo $formname?>' data-formid='<?php echo $formid?>' data-formfield='<?php echo $fieldjson?>' onclick='Le.openSmartFormPanel()'><?php echo $formlabel?></a>
        </div>
        <div class="smart-form-fieldcount">
            <?php echo '#'.$fieldcount ?> fields are available
        </div>
    </div>
</div>
<?php
endforeach;
?>

