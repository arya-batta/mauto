<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'routeBase'       => 'contact_import',
            'langVar'         => 'lead.import',
            'templateButtons' => [
                'close' => false,
            ],
        ]
    )
);
$isAdmin=$view['security']->isAdmin();
$style  = [];
$hide   = '';
if (!$isAdmin) {
    $style =  ['attr' => ['tabindex' => '-1', 'style' => 'pointer-events: none;background-color: #ebedf0;opacity: 1;']];
    $hide  = "style='display:none;'";
}

?>
<div class="row">
    <div class="col-sm-offset-3 col-sm-6">
        <div class="ml-lg mr-lg mt-md pa-lg">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title"><?php echo $view['translator']->trans('mautic.lead.import.start.instructions'); ?></div>
                </div>
                <div class="panel-body">
                    <?php echo $view['form']->start($form); ?>
                    <div class="row" style="margin-left:16%;">
                        <div class="pull-left col-xs-3">
                            <a href="<?php echo $view['assets']->getImportSampleFilePath() ?>" download>
                            <span class="input-group-btn download_sample">
                                <i class="fa fa-download"></i> <b><?php echo $view['translator']->trans('leadsengage.lead.import.download.sample'); ?></b>
                            </span>
                            </a>
                        </div>
                    </div>

                    <div class="input-group well mt-lg">
                        <?php echo $view['form']->widget($form['file']); ?>
                        <span class="input-group-btn">
                            <?php echo $view['form']->widget($form['start']); ?>
                        </span>
                    </div>

                    <div class="row" <?php echo $hide; ?>>
                        <div class="col-xs-3">
                            <?php echo $view['form']->label($form['batchlimit']); ?>
                            <?php echo $view['form']->widget($form['batchlimit'], $style); ?>
                            <?php echo $view['form']->errors($form['batchlimit']); ?>
                        </div>

                        <div class="col-xs-3">
                            <?php echo $view['form']->label($form['delimiter']); ?>
                            <?php echo $view['form']->widget($form['delimiter'], $style); ?>
                            <?php echo $view['form']->errors($form['delimiter']); ?>
                        </div>

                        <div class="col-xs-3">
                            <?php echo $view['form']->label($form['enclosure']); ?>
                            <?php echo $view['form']->widget($form['enclosure'], $style); ?>
                            <?php echo $view['form']->errors($form['enclosure']); ?>
                        </div>

                        <div class="col-xs-3">
                            <?php echo $view['form']->label($form['escape']); ?>
                            <?php echo $view['form']->widget($form['escape'], $style); ?>
                            <?php echo $view['form']->errors($form['escape']); ?>
                        </div>
                    </div>
                    <?php echo $view['form']->end($form); ?>
                </div>
            </div>
        </div>
    </div>
</div>
