<?php
?>
<div class="row" style="padding-top:5px;padding-bottom:15px;border-bottom: 1px solid;margin-left:15px;margin-right:15px;">
<a class="btn btn-default text-primary le-btn-default custom-preview-button blue-theme-bg" onclick="Le.closeBluePrintPage();" style="float: left;border-radius:4px;z-index:1003;" data-toggle="ajax">
<span>
<span class="" id="change-template-span"><?php echo $view['translator']->trans('le.drip.email.backtoeamils'); ?></span>
</span>
</a>
</div>
<?php if (count($items)): ?>
    <?php foreach ($items as $key => $entities): ?>
        <div class="row" style="margin-top:10px;border-bottom: 1px solid;margin:0px;">
            <div class="col-md-3" style="margin-bottom:10px;">
                <p><span style="font-size:18px;font-weight: bold;"><?php echo $drips[$key]['name']; ?></span></p>
                <p><span style="font-size:13px;"><?php echo $drips[$key]['description']; ?></span></p>
                <a class="btn btn-default text-primary le-btn-default custom-preview-button custom-use-button" id="use-this-blueprint" dripvalue="<?php echo $entity->getId(); ?>" href="<?php echo $view['router']->path('le_dripemail_email_action', ['objectId' => $entity->getId(), 'subobjectAction' => 'blueprint', 'subobjectId' => $drips[$key]['id']]); ?>" value="<?php echo $drips[$key]['id']; ?>" style="background-color: #ffda24;color:#000000;margin-top:10px;float: left;border-radius:4px;z-index:1003;" data-toggle="ajax">
                    <span>
                    <span class="" id="change-template-span"><?php echo $view['translator']->trans('le.drip.email.use.this'); ?></span>
                    </span>
                </a>
            </div>
            <div class="col-md-9">
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-bordered email-list">
                        <thead>
                        <tr>
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
                                    'class'      => 'col-email-stats col-page-category',
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
                            $previewUrl = $view['router']->path('le_dripemail_email_action', ['objectId' => $drips[$key]['id'], 'subobjectAction' => 'preview', 'subobjectId' => $item['id']], true);
                            ?>
                            <tr>
                                <td class="table-description">
                                    <span><?php echo $item['subject']; ?></span>
                                </td>
                                <td class="visible-sm visible-md visible-lg email-col-stats col-page-category" data-stats="<?php echo $item['id']; ?>">
                                    <span><?php echo $item['scheduleTime']; ?></span>
                                </td>
                                <td class="visible-sm visible-md visible-lg email-col-stats col-page-category drip-col-stats" data-stats="<?php echo $item['id']; ?>">
                                    <a class="btn btn-default text-primary le-btn-default custom-preview-button blue-theme-bg" href="<?php echo $previewUrl; ?>" target="_blank" style="float: right;border-radius:4px;z-index:1003;">
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
