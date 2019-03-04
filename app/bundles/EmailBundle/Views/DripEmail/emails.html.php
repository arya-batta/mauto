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

<div class="table-responsive email_stats_box">
    <table class="table table-hover <?php echo count($items) ? 'table-striped' : ''?> table-bordered dripemail-list">
        <thead>
        <tr>
            <?php
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'text'       => 'le.page.report.hits.email_subject',
                    'class'      => 'col-page-titledrip',
                    'default'    => true,
                ]
            );

            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'text'       => 'le.dripemail.stat.sent',
                    'class'      => 'col-email-stats drip-email-stats',
                    'default'    => true,
                ]
            );
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'text'       => 'le.dripemail.stat.open',
                    'class'      => 'col-email-stats drip-email-stats',
                    'default'    => true,
                ]
            );
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'text'       => 'le.dripemail.stat.notopen',
                    'class'      => 'col-email-stats',
                    'default'    => true,
                ]
            );
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'text'       => 'le.dripemail.stat.click',
                    'class'      => 'col-email-stats drip-email-stats',
                    'default'    => true,
                ]
            );
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'text'       => 'le.dripemail.stat.unsubscribe',
                    'class'      => 'col-email-stats drip-email-stats',
                    'default'    => true,
                ]
            );
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'text'       => 'le.dripemail.stat.bounce',
                    'class'      => 'col-email-stats drip-email-stats',
                    'default'    => true,
                ]
            );
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'text'       => 'le.dripemail.stat.spam',
                    'class'      => 'col-email-stats drip-email-stats',
                    'default'    => true,
                ]
            );
            echo $view->render(
                'MauticCoreBundle:Helper:tableheader.html.php',
                [
                    'text'       => 'le.dripemail.stat.failed',
                    'class'      => 'col-email-stats drip-email-stats',
                    'default'    => true,
                ]
            );
            ?>
        </tr>
        </thead>
        <?php if (count($items)): ?>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr class="drip-emailcol-stats-view" data-stats="<?php echo $item->getId(); ?>">
                    <td class="table-description">
                        <span><span><?php echo $item->getSubject(); ?></span></span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                    <span class="mt-xs"
                          id="sent-count-<?php echo $item->getId(); ?>">
                            <span>
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </span>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                     <span class="mt-xs"
                           id="read-count-<?php echo $item->getId(); ?>">
                            <span>
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </span>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" style="width:10%;" data-stats="<?php echo $item->getId(); ?>">
                     <span class="mt-xs"
                           id="not-read-count-<?php echo $item->getId(); ?>">
                            <span>
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </span>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs"
                            id="read-percent-<?php echo $item->getId(); ?>">
                            <span>
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </span>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs"
                            id="unsubscribe-count-<?php echo $item->getId(); ?>">
                            <span>
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </span>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs"
                            id="bounced-count-<?php echo $item->getId(); ?>">
                            <span>
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </span>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs"
                            id="spam-count-<?php echo $item->getId(); ?>">
                            <span>
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </span>
                        </span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats" data-stats="<?php echo $item->getId(); ?>">
                      <span class="mt-xs"
                            id="failed-count-<?php echo $item->getId(); ?>">
                            <span>
                                <div class="email-spinner-alignment">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </span>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        <?php else: ?>
            <?php echo $view->render('MauticEmailBundle:Email:noresults.html.php', ['tip' => 'mautic.form.noresults.tip', 'colspan' => '8']); ?>
        <?php endif; ?>
    </table>
</div>
<br>