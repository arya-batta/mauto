<?php
?>
<div class="row" style="padding-top:5px;padding-bottom:15px;border-bottom: 1px solid;">
<a class="btn btn-default text-primary le-btn-default custom-preview-button" onclick="Le.closeBluePrintPage();" style="background-color: #ec407a;color:#ffffff;float: left;border-radius:4px;z-index:1003;" data-toggle="ajax">
<span>
<span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.drip.email.backtoeamils'); ?></span>
</span>
</a>
</div>
<?php if (count($items)): ?>
    <?php foreach ($items as $key => $entities): ?>
        <div class="row" style="margin-top:10px;border-bottom: 1px solid;">
            <div class="col-md-3" style="margin-bottom:10px;">
                <p><span style="font-size:18px;font-weight: bold;"><?php echo $drips[$key]['name']; ?></span></p>
                <p><span style="font-size:13px;"><?php echo $drips[$key]['description']; ?></span></p>
                <a class="btn btn-default text-primary le-btn-default custom-preview-button custom-use-button" id="use-this-blueprint" dripvalue="<?php echo $entity->getId(); ?>" onclick="Le.useBluePrintDrip(this);" value="<?php echo $drips[$key]['id']; ?>" style="background-color: #ffda24;color:#000000;margin-top:10px;float: left;border-radius:4px;z-index:1003;" data-toggle="ajax">
                    <span>
                    <span class="hidden-xs hidden-sm" id="change-template-span"><?php echo $view['translator']->trans('le.drip.email.use.this'); ?></span>
                    </span>
                </a>
            </div>
            <div class="col-md-9">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered email-list">
                        <thead>
                        <tr style="background: #f2f2f2;">
                            <?php
                            echo $view->render(
                                'MauticCoreBundle:Helper:tableheader.html.php',
                                [
                                    'sessionVar' => 'email',
                                    'orderBy'    => '',
                                    'text'       => 'Emails ',
                                    'class'      => 'col-sms-name',
                                    'default'    => true,
                                ]
                            );

                            echo $view->render(
                                'MauticCoreBundle:Helper:tableheader.html.php',
                                [
                                    'sessionVar' => 'email',
                                    'orderBy'    => '',
                                    'text'       => 'le.drip.email.graph.line.stats.delay',
                                    'class'      => 'col-email-stats text-start',
                                    'default'    => true,
                                ]
                            );

                            echo $view->render(
                                'MauticCoreBundle:Helper:tableheader.html.php',
                                [
                                    'sessionVar' => 'email',
                                    'orderBy'    => '',
                                    'text'       => '',
                                    'class'      => 'col-email-stats',
                                    'default'    => true,
                                ]
                            );
                            ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($entities as $item):
                            $previewUrl = $view['router']->path('le_email_preview', ['objectId' => $item['id']], true);
                            ?>
                            <tr>
                                <td class="table-description">
                                    <span><?php echo $item['subject']; ?></span>
                                </td>
                                <td class="visible-sm visible-md visible-lg email-col-stats" data-stats="<?php echo $item['id']; ?>">
                                    <span><?php echo $item['scheduleTime']; ?></span>
                                </td>
                                <td class="visible-sm visible-md visible-lg email-col-stats drip-col-stats" data-stats="<?php echo $item['id']; ?>">
                                    <a class="btn btn-default text-primary le-btn-default custom-preview-button" onclick="window.open('<?php echo $previewUrl; ?>', '_blank');" style="background-color: #ec407a;color:#ffffff;float: right;border-radius:4px;z-index:1003;" data-toggle="ajax">
                                            <span>
                                            <span class="hidden-xs hidden-sm" id="view-emailtemplate-button"><?php echo $view['translator']->trans('View Email'); ?></span>
                                            </span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
