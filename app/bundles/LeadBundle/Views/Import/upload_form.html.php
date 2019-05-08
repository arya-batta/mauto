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
    <div class="col-sm-offset-1 col-sm-10">
        <div class="ml-lg mr-lg mt-md pa-lg">
            <div class="panel panel-info">
                <div class="panel-heading" style="background-color: #ffffff;">
                    <div class="panel-title" style="color: #212529;padding: 14px 0;"><?php echo $view['translator']->trans('le.lead.import.start.instructions'); ?></div>
                </div>
                <div class="panel-body">
                    <?php echo $view['form']->start($form); ?>
                    <div class="row center-align-container hide" style="float: none;">
                        <div class="pull-center col-xs-6">
                            <a href="<?php echo $view['assets']->getImportSampleFilePath() ?>" download>
                            <span class="input-group-btn download_sample">
                                <i class="fa fa-download"></i> <b><?php echo $view['translator']->trans('le.lead.import.download.sample'); ?></b>
                            </span>
                            </a>
                        </div>
                    </div>

                    <div class="input-group well mt-lg col-md-7 import-fileupload-container">
                        <?php echo $view['form']->widget($form['file']); ?>
                        <span class="input-group-btn">
                            <?php echo $view['form']->widget($form['start']); ?>
                        </span>
                    </div>
                        <div class="col-md-10" style="margin-left: 8%;margin-right: auto;">
                            <div class="pa-lg">
                                <h5><?php echo $view['translator']->trans('mautic.webhook.note'); ?></h5>
                                <li class="import-notes-list" style="margin-top: 10px;"><?php echo $view['translator']->trans('le.lead.import.start.note1'); ?></li>
                                <li class="import-notes-list"><div style="margin-left: 17px;margin-top: -17px;"><?php echo $view['translator']->trans('le.lead.import.start.note2'); ?></div></li>
                                <li class="import-notes-list"><?php echo $view['translator']->trans('le.lead.import.start.note3', ['%href%' => $view['assets']->getImportSampleFilePath()]); ?></li>
                                <li class="import-notes-list"><?php echo $view['translator']->trans('le.lead.import.start.note4', ['%href%' => $view['router']->path('le_import_index', ['object' => 'lead'])]); ?></li>
                            </div>
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
