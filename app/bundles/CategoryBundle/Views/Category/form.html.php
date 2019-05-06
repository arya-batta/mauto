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

<?php /**echo $view['form']->form($form, ['attr' => ['data-hide-loadingbar' => 'true']]);*/ ?>

<div>

    <?php echo $view['form']->start($form, ['attr' => ['data-hide-loadingbar' => 'true']]); ?>

    <div>
        <?php echo $view['form']->row($form['title']); ?>
    </div>
    <div class="<?php echo !$view['security']->isAdmin() ? 'hide' : ''; ?>">
        <?php echo $view['form']->row($form['description']); ?>
    </div>
    <div class="<?php echo !$view['security']->isAdmin() ? 'hide' : ''; ?>">
        <?php echo $view['form']->row($form['alias']); ?>
    </div>
    <div>
        <?php echo $view['form']->row($form['color']); ?>
    </div>
    <div class="<?php echo !$view['security']->isAdmin() ? 'hide' : ''; ?>">
        <?php echo $view['form']->row($form['isPublished']); ?>
    </div>
    <div>
        <?php echo $view['form']->row($form['inForm']); ?>
    </div>

    <?php echo $view['form']->end($form); ?>
</div>
