<?php $isAdmin=$view['security']->isAdmin(); ?>
<div class="row">
    <div class="col-xs-12">
        <h4 class="mb-sm"><?php echo $view['translator']->trans('le.lead.lead.update.action.help'); ?></h4>
    </div>
    <?php foreach ($form->children as $child): ?>
        <?php  if (!$isAdmin): ?>
            <?php if ($child->vars['name'] == 'points'
                      || $child->vars['name'] == 'score'
                      || $child->vars['name'] == 'title'
                      || $child->vars['name'] == 'firstname'
                      || $child->vars['name'] == 'lastname'
                      || $child->vars['name'] == 'mobile'
                      || $child->vars['name'] == 'email'
                      || $child->vars['name'] == 'company_new'): ?>
                <div class="hidden">
                    <?php echo $view['form']->label($child); ?>
                    <?php echo $view['form']->widget($child); ?>
                </div>
            <?php else:?>
                <div class="form-group col-xs-6 <?php echo (count($child->vars['errors'])) ? ' has-error' : ''; ?>">
                    <?php echo $view['form']->label($child); ?>
                    <?php echo $view['form']->widget($child); ?>
                    <?php echo $view['form']->errors($child);?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="form-group col-xs-6">
                <?php echo $view['form']->label($child); ?>
                <?php echo $view['form']->widget($child); ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>