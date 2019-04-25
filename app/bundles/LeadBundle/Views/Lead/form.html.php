<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
$header = ($lead->getId()) ?
    $view['translator']->trans('le.lead.lead.header.edit',
        ['%name%' => $view['translator']->trans($lead->getPrimaryIdentifier())]) :
    $view['translator']->trans('le.lead.lead.header.new');
$view['slots']->set('headerTitle', $header);
$view['slots']->set('leContent', 'lead');
$stagehideattr= $view['security']->isGranted('stage:stages:view') ? '' : "style='display: none;'";
$isAdmin      =$view['security']->isAdmin();
$groups       = array_keys($fields);
sort($groups);

$img = $view['lead_avatar']->getAvatar($lead);
?>
<?php echo $view['form']->start($form); ?>
<!-- start: box layout -->
<div class="box-layout row lead-edit-outer">
    <!-- container -->
    <div class="col-md-9 lead-edit-inside">
            <!-- pane -->
            <?php
            foreach ($groups as $key => $group):
                if (isset($fields[$group])):
                    $groupFields = $fields[$group];
                    if (!empty($groupFields)): ?>
                        <div class="tab-pane fade<?php if ($key === 0): echo ' in active'; endif; ?> bdr-rds-0 bdr-w-0"
                             id="<?php echo $group; ?>">
                            <div class="pa-md bg-auto bg-light-xs bdr-b hide">
                                <h4 class="fw-sb"><?php echo $view['translator']->trans('le.lead.field.group.'.$group); ?></h4>
                            </div>
                            <div class="pa-md" style="width: 150%;">
                                <?php if ($group == 'core'): ?>
                                    <?php if (isset($form['title']) || isset($form['firstname']) || isset($form['lastname'])): ?>
                                        <div class="form-group mb-0">
                                            <label
                                                    class="control-label mb-xs"><?php echo $view['translator']->trans('mautic.core.name'); ?></label>
                                            <div class="row">
                                                <?php if (isset($form['title'])): ?>
                                                    <div class="col-sm-2">
                                                        <?php echo $view['form']->widget($form['title'], ['attr' => ['placeholder' => $form['title']->vars['label']]]); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (isset($form['firstname'])): ?>
                                                    <div class="col-sm-3">
                                                        <?php echo $view['form']->widget($form['firstname'], ['attr' => ['placeholder' => $form['firstname']->vars['label']]]); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (isset($form['lastname'])): ?>
                                                    <div class="col-sm-3">
                                                        <?php echo $view['form']->widget($form['lastname'], ['attr' => ['placeholder' => $form['lastname']->vars['label']]]); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <hr class="mnr-md mnl-md">
                                        </div>
                                    <?php endif; ?>

                                    <div class="form-group mb-0">
                                        <?php if (isset($form['company_new'])): ?>
                                            <div class="row">
                                                <div class="col-sm-8">
                                                    <?php echo $view['form']->row($form['company_new']); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="row" style="max-width: 68%;">
                                        <div class="form-group col-sm-6 <?php if ($view['form']->containsErrors($form['email'])) {
                        echo ' has-error';
                    } ?>">
                                                <?php echo $view['form']->label($form['email']); ?>
                                                <?php echo $view['form']->widget($form['email'], ['attr' => ['placeholder' => $form['email']->vars['label']]]); ?>
                                                <?php echo $view['form']->errors($form['email']); ?>
                                            </div>

                                        <?php if (isset($form['mobile'])): ?>
                                                <div class="col-sm-6">
                                                    <?php echo $view['form']->row($form['mobile']); ?>
                                                </div>
                                        <?php endif; ?>
                                        </div>
                                        <div class="row" style="max-width: 68%;">
                                        <?php if (isset($form['points'])): ?>
                                                <div class="col-sm-6">
                                                    <?php echo $view['form']->row($form['points'], ['attr' => ['tabindex' => '-1', 'style' => 'pointer-events: none;background-color: #ebedf0;opacity: 1;']]); ?>
                                                </div>
                                        <?php endif; ?>
                                        <?php if (isset($form['score'])): ?>
                                             <div class="col-sm-6">
                                              <?php echo $view['form']->row($form['score'], ['attr' => ['tabindex' => '-1', 'style' => 'pointer-events: none;background-color: #ebedf0;opacity: 1;text-transform: capitalize;']]); ?>
                                             </div>
                                        <?php endif; ?>
                                        </div>
                                    </div>
                                    <hr class="mnr-md mnl-md" >
                                    <div class="form-group mb-0" <?php echo $stagehideattr ?>>
                                        <label><?php echo $view['translator']->trans('le.company.company'); ?></label>
                                        <div class="row">
                                            <?php if (isset($form['companies'])): ?>
                                                <div class="col-sm-4">
                                                    <?php echo $view['form']->widget($form['companies']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (isset($form['position'])): ?>
                                                <div class="col-sm-4">
                                                    <?php echo $view['form']->widget($form['position'], ['attr' => ['placeholder' => $form['position']->vars['label']]]); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <hr class="mnr-md mnl-md" <?php echo $stagehideattr ?>>

                                    <?php if (isset($form['address1']) || isset($form['address2']) || isset($form['city']) || isset($form['state']) || isset($form['zipcode']) || isset($form['country'])): ?>
                                        <div class="form-group mb-0">
                                            <label
                                                    class="control-label mb-xs"><?php echo $view['translator']->trans('le.lead.field.address'); ?></label>
                                            <?php if (isset($form['address1'])): ?>
                                                <div class="row mb-xs">
                                                    <div class="col-sm-8">
                                                        <?php echo $view['form']->widget($form['address1'], ['attr' => ['placeholder' => $form['address1']->vars['label']]]); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (isset($form['address2'])): ?>
                                                <div class="row mb-xs">
                                                    <div class="col-sm-8">
                                                        <?php echo $view['form']->widget($form['address2'], ['attr' => ['placeholder' => $form['address2']->vars['label']]]); ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <div class="row mb-xs">
                                                <?php if (isset($form['city'])): ?>
                                                    <div class="col-sm-4">
                                                        <?php echo $view['form']->widget($form['city'], ['attr' => ['placeholder' => $form['city']->vars['label']]]); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (isset($form['state'])): ?>
                                                    <div class="col-sm-4">
                                                        <?php echo $view['form']->widget($form['state'], ['attr' => ['placeholder' => $form['state']->vars['label']]]); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="row">
                                                <?php if (isset($form['zipcode'])): ?>
                                                    <div class="col-sm-4">
                                                        <?php echo $view['form']->widget($form['zipcode'], ['attr' => ['placeholder' => $form['zipcode']->vars['label']]]); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (isset($form['country'])): ?>
                                                    <div class="col-sm-4">
                                                        <?php echo $view['form']->widget($form['country'], ['attr' => ['placeholder' => $form['country']->vars['label']]]); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <hr class="mnr-md mnl-md">
                                    <?php endif; ?>
                                <div class="form-group">
                                    <div class="row">
                                        <?php if (isset($form['gdpr_timezone'])): ?>
                                            <div class="col-sm-4" id="leadfield_eu_gdpr_timezone">
                                                <?php echo $view['form']->label($form['gdpr_timezone']); ?>
                                                <?php echo $view['form']->widget($form['gdpr_timezone']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($form['eu_gdpr_consent'])): ?>
                                            <div class="col-sm-4" id="leadfield_eu_gdpr_consent">
                                                <?php echo $view['form']->label($form['eu_gdpr_consent']); ?>
                                                <?php echo $view['form']->widget($form['eu_gdpr_consent']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                    <?php if (isset($form['attribution']) && isset($form['attribution_date'])): ?>
                                        <div class="form-group mb-0">
                                            <label
                                                    class="control-label mb-xs"><?php echo $view['translator']->trans('le.lead.attribution'); ?></label>
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <?php echo $view['form']->widget($form['attribution'], ['attr' => ['placeholder' => $form['attribution']->vars['label'], 'preaddon' => 'fa fa-money']]); ?>
                                                </div>
                                                <div class="col-sm-4">
                                                    <?php echo $view['form']->widget($form['attribution_date'], ['attr' => ['placeholder' => $form['attribution_date']->vars['label']]]); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="mnr-md mnl-md">
                                    <?php endif; ?>
                                <?php endif; ?>
                                <div class="form-group mb-0">
                                    <div class="row">
                                        <?php foreach ($groupFields as $alias => $field): ?>
                                            <?php
                                            if ($isCompany = ('company' === $alias) || !isset($form[$alias]) || $form[$alias]->isRendered()):
                                                // Company rendered so that it doesn't show up at the bottom of the form
                                                if ($isCompany):
                                                    $form[$alias]->setRendered();
                                                endif;
                                                continue;
                                            endif;
                                            ?>
                                            <div class="col-sm-8">
                                                <?php echo $view['form']->row($form[$alias]); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php if ($group == 'core'): ?>
                                    <hr class="mnr-md mnl-md" <?php echo $stagehideattr ?>>
                                    <div class="row" <?php echo $stagehideattr ?>>
                                        <?php if (isset($form['stage'])): ?>
                                            <div class="col-sm-8">
                                                <?php echo $view['form']->label($form['stage']); ?>
                                                <?php echo $view['form']->widget($form['stage']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <hr class="mnr-md mnl-md" <?php echo $stagehideattr ?>>

                                <?php endif; ?>
                            </div>
                        </div>
                    <?php
                    endif;
                endif;
            endforeach;
            ?>
            <!--/ #pane -->
        </div>
    <div class="col-sm-1" style="width: 1%">
    </div>
   <?php /** </div>*/ ?>
    <!--/ end: container -->
    <!-- step container -->
    <div class="col-md-3 pa-md lead-edit-inside">
        <div class="pr-lg pl-lg pt-md pb-md">
            <div class="media">
                <div class="media-body">
                    <img class="img-rounded img-bordered img-responsive media-object" style="margin-top: 0px;border-radius: 120px;height: 150px;margin-left: auto;margin-right: auto;" src="<?php echo $img; ?>" alt="">
                </div>
            </div>

            <div class="row mt-xs">
                <div class="col-sm-12">
                    <?php // echo $view['form']->label($form['preferred_profile_image']);?>
                    <?php echo $view['form']->widget($form['preferred_profile_image']); ?>
                </div>
                <div
                        class="col-sm-12<?php if ($view['form']->containsErrors($form['custom_avatar'])) {
                echo ' has-error';
            } ?>"
                        id="customAvatarContainer"
                        style="<?php if ($form['preferred_profile_image']->vars['data'] != 'custom') {
                echo 'display: none;';
            } ?>">
                    <?php echo $view['form']->widget($form['custom_avatar']); ?>
                    <?php echo $view['form']->errors($form['custom_avatar']); ?>
                </div>
            </div>

            <hr/>
            <div class="form-group le-mb-footer">
                <div class="row">
                        <?php echo $view['form']->label($form['owner']); ?>
                        <?php echo $view['form']->widget($form['owner']); ?>
                </div>
                <br>
                <div class="row">
                    <?php echo $view['form']->label($form['lead_listsoptin']); ?>
                    <?php echo $view['form']->widget($form['lead_listsoptin']); ?>
                </div>
                <br>
                <div class="row hide">
                    <?php echo $view['form']->label($form['lead_lists']); ?>
                    <?php echo $view['form']->widget($form['lead_lists']); ?>
                </div>
                <br>
                <div class="row">
                        <?php echo $view['form']->label($form['tags']); ?>
                        <?php echo $view['form']->widget($form['tags']); ?>
                </div>
            </div>
            <ul class="list-group list-group-tabs">
                <?php $step = 1; ?>
                <?php foreach ($groups as $g): ?>
                    <?php if (!empty($fields[$g])): ?>
                        <?php  if ($isAdmin): ?>
                            <li class="list-group-item <?php if ($step === 1) {
                echo 'active';
            } ?>">
                                <a href="#<?php echo $g; ?>" class="steps" data-toggle="tab">
                                    <?php echo $view['translator']->trans('le.lead.field.group.'.$g); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        <?php ++$step; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <!--/ step container -->
</div>
<?php echo $view['form']->end($form); ?>
<!--/ end: box layout -->
