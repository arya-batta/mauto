<?php if (!empty($showMore)): ?>
    <a href="<?php echo $view['router']->generate('le_tags_index', ['search' => $searchString]); ?>" class="list-group-item" data-toggle="ajax">
        <small class="text-primary"><?php echo $view['translator']->trans('mautic.core.search.more', ['%count%' => $remaining]); ?></small>
    </a>
<?php else: ?>
    <a href="<?php echo $view['router']->generate('le_tags_index'); ?>" onclick="Le.closeGlobalSearchResults();" class="list-group-item" data-toggle="ajax">
        <div class="media">
            <div class="media-heading"><?php echo $tag->getTag(); ?></div>
        </div>
    </a>
<?php endif; ?>