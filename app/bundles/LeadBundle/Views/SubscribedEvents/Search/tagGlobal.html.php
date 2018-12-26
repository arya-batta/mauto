<?php if (!empty($showMore)): ?>
    <a href="<?php echo $view['router']->generate('le_tags_index', ['search' => $searchString]); ?>" data-toggle="ajax">
        <span><?php echo $view['translator']->trans('mautic.core.search.more', ['%count%' => $remaining]); ?></span>
    </a>
<?php else: ?>
    <a href="<?php echo $view['router']->generate('le_tags_index'); ?>" data-toggle="ajax">
        <span><?php echo $tag->getTag(); ?></span>
        <?php
        $color = "#bbbbbb";
        $style = !empty($color) ? ' style="background-color: '.$color.';"' : '';
        ?>
    </a>
    <div class="clearfix"></div>
<?php endif; ?>