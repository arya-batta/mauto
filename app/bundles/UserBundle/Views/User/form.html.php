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
$view['slots']->set('leContent', 'user');
$userId = $form->vars['data']->getId();
if (!empty($userId)) {
    $user   = $form->vars['data']->getName();
    $header = $view['translator']->trans('mautic.user.user.header.edit', ['%name%' => $user]);
} else {
    $header = $view['translator']->trans('mautic.user.user.header.new');
}
$view['slots']->set('headerTitle', $header);
$isAdmin       =$view['security']->isAdmin();
$isLogginedUser=$view['security']->isLoginUserID($userId);
$emailattr     = ['attr' => ['placeholder' => $form['email']->vars['label']]];
if ($isLogginedUser) {
    $emailattr = ['attr' => ['tabindex' => '-1', 'style' => 'pointer-events: none;background-color: #ebedf0;opacity: 1;', 'placeholder' => $form['email']->vars['label']]];
}
?>
<!-- start: box layout -->
<div class="box-layout">
    <!-- container -->
    <?php echo $view['form']->start($form); ?>
    <div class="col-md-12 bg-auto height-auto bdr-r">
		<div class="pa-md">
            <div class="form-group mb-0">
                <div class="row">
                    <div class="col-sm-6 <?php echo $isAdmin ? '' : ' hide'?> <?php echo (count($form['position']->vars['errors'])) ? ' has-error' : ''; ?>">
                        <label class="control-label mb-xs"><?php echo $view['form']->label($form['position']); ?></label>
                        <?php echo $view['form']->widget($form['position'], ['attr' => ['placeholder' => $form['position']->vars['label']]]); ?>
                        <?php echo $view['form']->errors($form['position']); ?>
                    </div>
                    <div class="col-sm-6<?php echo (count($form['signature']->vars['errors'])) ? ' has-error' : ''; ?><?php echo $isAdmin ? '' : ' hide'?>">
                        <label class="control-label mb-xs"><?php echo $view['form']->label($form['signature']); ?></label>
                        <?php echo $view['form']->widget($form['signature'], ['attr' => ['placeholder' => $form['signature']->vars['label']]]); ?>
                        <?php echo $view['form']->errors($form['signature']); ?>
                    </div>
                </div>
            </div>
            <hr class="mnr-md mnl-md">

			<div class="panel panel-default form-group mb-0">
				<div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group <?php echo (count($form['username']->vars['errors'])) ? ' has-error' : ''; ?>">
                                <?php echo $view['form']->label($form['username']); ?>
                                <?php echo $view['form']->widget($form['username'], ['attr' => ['placeholder' => $form['username']->vars['label']]]); ?>
                                <?php echo $view['form']->errors($form['username']); ?>
                            </div>
                            <div class="form-group <?php echo (count($form['email']->vars['errors'])) ? ' has-error' : ''; ?>">
                                <?php echo $view['form']->label($form['email']); ?>
                                <?php echo $view['form']->widget($form['email'], $emailattr); ?>
                                <?php echo $view['form']->errors($form['email']); ?>
                            </div>
                        </div>
                        <div class="col-sm-6" style="margin-top:8px">
                            <?php echo $view['form']->widget($form['plainPassword'], ['attr' => ['placeholder' => $form['plainPassword']->vars['label']]]); ?>
                        </div>
                    </div>
                        <div class="row">
                            <div class="col-sm-6<?php echo (count($form['firstName']->vars['errors'])) ? ' has-error' : ''; ?>">
                                <label class="control-label mb-xs"><?php echo $view['form']->label($form['firstName']); ?></label>
                                <?php echo $view['form']->widget($form['firstName'], ['attr' => ['placeholder' => $form['firstName']->vars['label']]]); ?>
                                <?php echo $view['form']->errors($form['firstName']); ?>
                            </div>
                            <div class="col-sm-6<?php echo (count($form['lastName']->vars['errors'])) ? ' ' : ''; ?>">
                                <label class="control-label mb-xs"><?php echo $view['form']->label($form['lastName']); ?></label>
                                <?php echo $view['form']->widget($form['lastName'], ['attr' => ['placeholder' => $form['lastName']->vars['label']]]); ?>
                                <?php echo $view['form']->errors($form['lastName']); ?>
                            </div>
                        </div>
                    <hr class="mnr-md mnl-md">
                        <div class="row">
                            <div class="col-sm-6<?php echo (count($form['mobile']->vars['errors'])) ? '' : ''; ?>">
                                <label class="control-label mb-xs"><?php echo $view['form']->label($form['mobile']); ?></label>
                                <?php echo $view['form']->widget($form['mobile'], ['attr' => ['placeholder' => $form['mobile']->vars['label']]]); ?>
                                <?php echo $view['form']->errors($form['mobile']); ?>
                            </div>
                            <div class="col-sm-6<?php echo (count($form['role']->vars['errors'])) ? ' has-error' : ''; ?>" style="margin-top: 5px;">
                                <label class="control-label mb-xs hide"><?php echo $view['form']->label($form['role']); ?></label>
                                <?php echo $view['form']->row($form['role'], ['attr' => ['placeholder' => $form['role']->vars['label']]]); ?>
                                <?php echo $view['form']->errors($form['role']); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class=" col-sm-6 form-group <?php echo (count($form['timezone']->vars['errors'])) ? ' has-error' : ''; ?>">
                                <label class="control-label mb-xs"><?php echo $view['form']->label($form['timezone']); ?></label>
                                <?php echo $view['form']->widget($form['timezone']); ?>
                                <?php echo $view['form']->errors($form['timezone']); ?>
                            </div>
                            <div <?php echo $isLogginedUser ? "style='pointer-events: none'" : '' ?> class="form-group  col-sm-6 ">
                                <label class="control-label mb-xs"><?php echo $view['form']->label($form['isPublished']); ?></label>
                                <?php echo $view['form']->widget($form['isPublished']); ?>
                            </div>
                        </div>
                        <div class="hidden">
                            <?php echo $view['form']->row($form['preferred_profile_image']); ?>
                            <?php echo $view['form']->row($form['custom_avatar']); ?>
                        </div>

				</div>
			</div>
            <hr class="mnr-md mnl-md">
             <?php if (!$isAdmin): ?>
                <div class="hidden">
                <?php echo $view['form']->row($form['locale']); ?>
                </div>
            <?php endif; ?>
		</div>
	</div>
    <?php echo $view['form']->end($form); ?>
</div>