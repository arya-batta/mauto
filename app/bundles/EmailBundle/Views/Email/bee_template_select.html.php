
<?php if ($beetemplates) : ?>

    <div class="row">
        <?php
        $isPageTemplate= !empty($beetemplates['zblank']) ? 'hide' : '';
        $themeKey      = 'blank';
        $themeInfo     = !empty($beetemplates['zblank']) ? $beetemplates['zblank'] : $beetemplates['blank'];
        $thumbnailName = 'thumbnail.png';
        $hasThumbnail  = file_exists($themeInfo['dir'].'/'.$thumbnailName);
        $isSelected    = ($active === 'blank');

        ?>
        <?php $thumbnailUrl = $view['assets']->getUrl($themeInfo['themesLocalDir'].'/'.$themeKey.'/'.$thumbnailName); ?>
        <div class="col-md-3 theme-list b-temp-width <?php echo $isPageTemplate; ?>" >
            <div class="panel panel-default <?php echo $isSelected ? 'theme-selected' : ''; ?>">
                <div class="panel-body text-center">
                    <h5 style="height: 31px"><?php echo $themeInfo['name']; ?></h5>
                    <?php if ($hasThumbnail) : ?>
                        <!-- <a href="#" data-toggle="modal" data-target="#theme-<?php echo $themeKey; ?>">-->
                        <div style="background-image: url(<?php echo $thumbnailUrl ?>);background-repeat:no-repeat;background-size:contain; background-position:center; width: 100%; height: 250px"></div>
                        <!-- </a>-->
                    <?php else : ?>
                        <div class="panel-body text-center" style="height: 250px">
                            <i class="fa fa-file-image-o fa-5x text-muted" aria-hidden="true" style="padding-top: 75px; color: #E4E4E4;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="row" style="margin-top: 30px;">
                        <a href="#" type="button" data-beetemplate="<?php echo $themeKey; ?>" class="btn-nospin col-md-6 select-theme-link bee_template <?php echo $isSelected ? 'hide' : '' ?>" onclick="mQuery('#dynamic-content-tab').addClass('hidden')">
                            <i class="fa fa-check-circle template_icon" style="color: #6A7474">
                            <span style="margin-left: 24px;font-size: 15px;">Select</span>
                            </i>
                        </a>
                        <a type="button" class=" btn-nospin col-md-6 select-theme-selected bee_template <?php echo $isSelected ? '' : 'hide' ?>" disabled="disabled"style="margin-top: 0px;">
                            <i class="fa fa-check-circle template_icon blue-theme-fg">
                            <span style="margin-left: 24px;font-size: 15px;color: #6A7474;">Selected</span>
                            </i>
                        </a>
                        <a href="<?php echo $view['router']->path('le_email_preview_action', ['template' => $themeKey, 'route' => $route]); ?>" target="_blank" type="button" class="col-md-6 bee_template btn-nospin" onclick="">
                            <i class="fa fa-eye  template_icon" style="color: #6A7474">
                            <span style="margin-left: 24px;font-size: 15px;color: #6A7474">Preview</span>
                            </i>
                        </a>
                    </div>
                </div>
            </div>
            <?php if ($hasThumbnail) : ?>
                <!-- Modal -->
                <div class="modal fade" id="theme-<?php echo $themeKey; ?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo $themeKey; ?>">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="<?php echo $themeKey; ?>"><?php echo $themeInfo['name']; ?></h4>
                            </div>
                            <div class="modal-body">
                                <div style="background-image: url(<?php echo $thumbnailUrl ?>);background-repeat:no-repeat;background-size:contain; background-position:center; width: 100%; height: 600px"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php foreach ($beetemplates as $themeKey => $themeInfo) : ?>
            <?php
            if ($themeKey == 'blank') {
                continue;
            }
            $isSelected        = ($active === $themeKey);
            $thumbnailName     = 'thumbnail.png';
                $hasThumbnail  = file_exists($themeInfo['dir'].'/'.$thumbnailName);
            ?>
            <?php $thumbnailUrl = $view['assets']->getUrl($themeInfo['themesLocalDir'].'/'.$themeKey.'/'.$thumbnailName); ?>

            <div class="col-md-3 theme-list bee-template b-temp-width <?php echo !empty($themeInfo['config']['group']) ? $themeInfo['config']['group'] : ''; ?>">
                <div class="panel panel-default <?php echo $isSelected ? 'theme-selected' : ''; ?>">
                    <div class="panel-body text-center">
                        <h5 style="height: 31px"><?php echo $themeInfo['name']; ?></h5>
                        <?php if ($hasThumbnail) : ?>
                          <!-- <a href="#" data-toggle="modal" data-target="#theme-<?php echo $themeKey; ?>">-->
                                <div style="background-image: url(<?php echo $thumbnailUrl ?>);background-repeat:no-repeat;background-size:contain; background-position:center; width: 100%; height: 250px"></div>
                           <!-- </a>-->
                        <?php else : ?>
                            <div class="panel-body text-center" style="height: 250px">
                                <i class="fa fa-file-image-o fa-5x text-muted" aria-hidden="true" style="padding-top: 75px; color: #E4E4E4;"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                          <a class="label label-primary hide" style="text-transform: capitalize;"><?php echo !empty($themeInfo['config']['group']) ? $themeInfo['config']['group'] : '' ?></a>
                        </div>
                        <div class="row" style="margin-top: 30px;">
                              <a href="#" type="button" data-beetemplate="<?php echo $themeKey; ?>" class="btn-nospin col-md-6  select-theme-link bee_template <?php echo $isSelected ? 'hide' : '' ?>" onclick="mQuery('#dynamic-content-tab').addClass('hidden')">
                                 <i class="fa fa-check-circle template_icon" style="color: #6A7474;">
                                  <span style="margin-left: 24px;font-size: 15px;">Select</span>
                                 </i>
                              </a>
                              <a type="button" class=" btn-nospin col-md-6 select-theme-selected  bee_template <?php echo $isSelected ? '' : 'hide' ?>" disabled="disabled"style="margin-top: 0px;">
                                  <i class="fa fa-check-circle template_icon blue-theme-fg">
                                  <span style="margin-left: 24px;font-size: 15px;color: #6A7474;">Selected</span>
                                  </i>
                              </a>
                            <a href="<?php echo $view['router']->path('le_email_preview_action', ['template' => $themeKey, 'route' => $route]); ?>" target="_blank" type="button" class=" col-md-6 bee_template btn-nospin" onclick="">
                                <i class="fa fa-eye template_icon" style="color: #6A7474">
                                <span style="margin-left: 24px;font-size: 15px;">Preview</span>
                                </i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php if ($hasThumbnail) : ?>
                    <!-- Modal -->
                    <div class="modal fade" id="theme-<?php echo $themeKey; ?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo $themeKey; ?>">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="<?php echo $themeKey; ?>"><?php echo $themeInfo['name']; ?></h4>
                                </div>
                                <div class="modal-body">
                                    <div style="background-image: url(<?php echo $thumbnailUrl ?>);background-repeat:no-repeat;background-size:contain; background-position:center; width: 100%; height: 600px"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <div class="clearfix"></div>
    </div>
<?php endif; ?>
