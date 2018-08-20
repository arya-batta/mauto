<?php if ($beetemplates) : ?>
    <div class="row">
        <?php
        $themeKey      ='blank';
        $themeInfo     =$beetemplates['blank'];
        $thumbnailName = 'thumbnail.png';
        $hasThumbnail  = file_exists($themeInfo['dir'].'/'.$thumbnailName);
        $isSelected    = ($active === 'blank');
        ?>
        <?php $thumbnailUrl = $view['assets']->getUrl($themeInfo['themesLocalDir'].'/'.$themeKey.'/'.$thumbnailName); ?>
        <div class="col-md-3 theme-list">
            <div class="panel panel-default <?php echo $isSelected ? 'theme-selected' : ''; ?>">
                <div class="panel-body text-center">
                    <h4 style="height: 30px"><?php echo $themeInfo['name']; ?></h4>
                    <?php if ($hasThumbnail) : ?>
                        <!-- <a href="#" data-toggle="modal" data-target="#theme-<?php echo $themeKey; ?>">-->
                        <div style="background-image: url(<?php echo $thumbnailUrl ?>);background-repeat:no-repeat;background-size:contain; background-position:center; width: 100%; height: 250px"></div>
                        <!-- </a>-->
                    <?php else : ?>
                        <div class="panel-body text-center" style="height: 250px">
                            <i class="fa fa-file-image-o fa-5x text-muted" aria-hidden="true" style="padding-top: 75px; color: #E4E4E4;"></i>
                        </div>
                    <?php endif; ?>
                    <a href="#" type="button" data-beetemplate="<?php echo $themeKey; ?>" class="select-theme-link btn btn-default <?php echo $isSelected ? 'hide' : '' ?>" onclick="mQuery('#dynamic-content-tab').addClass('hidden')">
                        Select
                    </a>
                    <button type="button" class="select-theme-selected btn btn-default <?php echo $isSelected ? '' : 'hide' ?>" disabled="disabled">
                        Selected
                    </button>
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

            <div class="col-md-3 theme-list bee-template <?php echo $themeInfo['config']['group']; ?>">
                <div class="panel panel-default <?php echo $isSelected ? 'theme-selected' : ''; ?>">
                    <div class="panel-body text-center">
                        <h4 style="height: 30px"><?php echo $themeInfo['name']; ?></h4>
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
                          <a class="label label-primary" style="text-transform: capitalize;"><?php echo $themeInfo['config']['group']; ?></a>
                        </div>
                        <a href="#" type="button" data-beetemplate="<?php echo $themeKey; ?>" class="select-theme-link btn btn-default <?php echo $isSelected ? 'hide' : '' ?>" onclick="mQuery('#dynamic-content-tab').addClass('hidden')">
                            Select
                        </a>
                        <button type="button" class="select-theme-selected btn btn-default <?php echo $isSelected ? '' : 'hide' ?>" disabled="disabled">
                            Selected
                        </button>
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
