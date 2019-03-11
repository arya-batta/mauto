<h2 class="email-dataview-stats"><?php echo $view['translator']->trans('le.email.click.trackable')?></h2>

<div class="table-responsive email_stats_box">
    <table class="table table-hover table-bordered click-list">
        <thead>
        <tr>
            <th class="link-url"><b><?php echo $view['translator']->trans('le.trackable.click_url'); ?></b></th>
            <th class="link-unique"><b><?php echo $view['translator']->trans('le.trackable.unique.clickcount'); ?></b></th>
            <th class="link-total"><b><?php echo $view['translator']->trans('le.trackable.total.clickcount'); ?></b></th>
            <th class="link-redirect"><b><?php echo $view['translator']->trans('le.trackable.click_track_id'); ?></b></th>
        </tr>
        </thead>
        <?php if (!empty($trackables)): ?>
            <tbody>
            <?php
            $totalClicks       = 0;
            $totalUniqueClicks = 0;
            foreach ($trackables as $link):
                $totalClicks += $link['hits'];
                $totalUniqueClicks += $link['unique_hits'];
                ?>
                <tr>
                    <td class="long-text link-url"><a href="<?php echo $link['url']; ?>"><?php echo $link['url']; ?></a></td>
                    <td class="text-center link-unique"><?php echo $link['unique_hits']; ?></td>
                    <td class="text-center link-total"><?php echo $link['hits']; ?></td>
                    <td><?php echo $link['redirect_id']; ?></td>
                </tr>
            <?php endforeach; ?>

            <tr>
                <td class="long-text link-url"><?php echo $view['translator']->trans('le.trackable.total_clicks'); ?></td>
                <td class="text-center link-unique"><?php echo $totalUniqueClicks; ?></td>
                <td class="text-center link-total"><?php echo $totalClicks; ?></td>
                <td></td>
            </tr>

            </tbody>
        <?php else: ?>
            <?php echo $view->render('MauticEmailBundle:Email:noresults.html.php', ['tip' => 'mautic.form.noresults.tip', 'colspan' => '4']); ?>
        <?php endif; ?>
    </table>
</div>
<br>