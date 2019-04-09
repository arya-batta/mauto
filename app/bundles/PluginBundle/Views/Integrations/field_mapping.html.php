<?php
$fields         =$form->vars['fields'];
$propertychoices=$form->vars['propertychoices'];
?>
<button type="button" class="btn btn-default btn-nospin btn-field-mapping waves-effect btn-new-field-mapping"><?php echo $view['translator']->trans('le.integrations.field.mapping.add') ?></button>
<?php $filter_index=count($form['field_mapping']->vars['value']) ? max(array_keys($form['field_mapping']->vars['value'])) : 0; ?>
<?php echo $view['form']->start($form); ?>
<div class="integration_field_mapping" data-prototype='<?php echo $view['form']->widget($form['field_mapping']->vars['prototype'])?>' data-index='<?php echo $filter_index + 1; ?>'>
    <div class="panel-body" style="
    font-weight: 700;
">
        <div class="col-sm-3">
            Lead Field
        </div>
        <div class="col-sm-5">
            Integration Field
        </div>
        <div class="col-sm-3">
            Default Value
        </div>
        <div class="col-sm-1">
        </div>
    </div>
    <?php echo $view['form']->widget($form['field_mapping']); ?>
</div>
<button type="button" class="btn btn-default btn-nospin btn-field-mapping waves-effect btn-save-field-mapping"><?php echo $view['translator']->trans('le.integrations.field.mapping.save') ?></button>
<?php echo $view['form']->end($form); ?>
<script>
    Le.integration_fieldmapping_details =
    <?php echo json_encode($fields, JSON_PRETTY_PRINT); ?>;
    Le.integration_property_choices =
    <?php echo json_encode($propertychoices, JSON_PRETTY_PRINT); ?>;
</script>
