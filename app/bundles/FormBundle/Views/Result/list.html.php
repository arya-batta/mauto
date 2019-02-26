<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($tmpl == 'index'):
    $view->extend('MauticFormBundle:Result:index.html.php');
endif;

$formId = $form->getId();
$isAdmin=$view['security']->isAdmin();

?>
<div class="col-xs-6 va-m hide" style="margin-bottom: 10px;margin-left: -17px;">
    <h5 class="text-white dark-md fw-sb mb-xs">
        <span class="fa fa-database"></span><?php echo $view['translator']->trans('mautic.form.results.result'); ?></h5>
</div>
<div class="table-responsive table-responsive-force">
    <table class="table  table-hover table-striped table-bordered formresult-list" id="formResultTable">
        <thead>
            <tr>
                <?php
                if ($isAdmin):
                if ($canDelete):
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'checkall'        => 'true',
                    'target'          => '#formResultTable',
                    'tmpl'            => 'index',
                    'routeBase'       => 'form_results',
                    'query'           => ['formId' => $formId],
                    'templateButtons' => [
                        'delete' => $canDelete,
                    ],
                  //  'default'         => true,
                ]);
                endif;
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'formresult.'.$formId,
                    'tmpl'       => 'index',
                    'orderBy'    => 's.id',
                    'text'       => 'mautic.core.id',
                    'class'      => 'col-formresult-id',
                  //  'filterBy'   => 's.id',
                ]);
                endif;
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'formresult.'.$formId,
                    'tmpl'       => 'index',
                    'orderBy'    => 's.date_submitted',
                    'text'       => 'mautic.form.result.thead.date',
                    //'class'      => 'col-formresult-date',
                    'default'    => true,
                    //'filterBy'   => 's.date_submitted',
                    'dataToggle' => 'date',
                ]);
                if ($isAdmin):
                echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                    'sessionVar' => 'formresult.'.$formId,
                    'tmpl'       => 'index',
                    'orderBy'    => 'i.ip_address',
                    'text'       => 'mautic.core.ipaddress',
                    'class'      => 'col-formresult-ip',
                   // 'filterBy'   => 'i.ip_address',
                ]);
                endif;
                $fields     = $form->getFields();
                $fieldCount = ($canDelete) ? 4 : 3;
                if ($form->isSmartForm()) {
                    foreach ($form->getSmartFields() as $index => $f):
                        echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                            'sessionVar' => 'formresult.'.$formId,
                            'tmpl'       => 'index',
                            'orderBy'    => 'r.'.$f['dbfield'],
                            'text'       => $f['smartfield'],
                            'class'      => 'col-formresult-field col-formresult-field'.$index,
                           // 'filterBy'   => 'r.'.$f['dbfield'],
                        ]);
                    ++$fieldCount;
                    endforeach;
                } else {
                    foreach ($fields as $f):
                        if (in_array($f->getType(), $viewOnlyFields) || $f->getSaveResult() === false) {
                            continue;
                        }
                    echo $view->render('MauticCoreBundle:Helper:tableheader.html.php', [
                            'sessionVar' => 'formresult.'.$formId,
                            'tmpl'       => 'index',
                            'orderBy'    => 'r.'.$f->getAlias(),
                            'text'       => $f->getLabel(),
                            'class'      => 'col-formresult-field col-formresult-field'.$f->getId(),
                           // 'filterBy'   => 'r.'.$f->getAlias(),
                        ]);
                    ++$fieldCount;
                    endforeach;
                }

                ?>
            </tr>
        </thead>
        <tbody>
        <?php if (count($items)): ?>
        <?php foreach ($items as $item): ?>
            <?php $item['name'] = $view['translator']->trans('mautic.form.form.results.name', ['%id%' => $item['id']]); ?>
            <tr>
                <?php if ($isAdmin): ?>
                   <?php if ($canDelete): ?>
                <td>
                    <?php
                    echo $view->render('MauticCoreBundle:Helper:list_actions.html.php', [
                        'item'            => $item,
                        'templateButtons' => [
                            'delete' => $canDelete,
                        ],
                        'route'   => 'le_form_results_action',
                        'langVar' => 'form.results',
                        'query'   => [
                            'formId'       => $formId,
                            'objectAction' => 'delete',
                        ],
                    ]);
                    ?>
                </td>
                   <?php endif; ?>
                <td><?php echo $item['id']; ?></td>
                <?php endif; ?>
                <td>
                    <?php if (!empty($item['lead']['id'])): ?>
                    <a href="<?php echo $view['router']->path('le_contact_action', ['objectAction' => 'view', 'objectId' => $item['lead']['id']]); ?>" data-toggle="ajax">
                        <?php echo $view['date']->convertUTCtoIST($item['dateSubmitted']); ?>
                    </a>
                    <?php else: ?>
                    <?php echo $view['date']->convertUTCtoIST($item['dateSubmitted']); ?>
                    <?php endif; ?>
                </td>
                <?php if ($isAdmin): ?>
                <td><?php echo $item['ipAddress']; ?></td>
                <?php endif; ?>
                <?php foreach ($item['results'] as $key => $r): ?>
                    <?php $isTextarea = isset($r['type']) && $r['type'] === 'textarea'; ?>
                    <td <?php echo $isTextarea ? 'class="long-text"' : ''; ?>>
                        <?php if ($isTextarea) : ?>
                            <?php echo nl2br($r['value']); ?>
                        <?php elseif (isset($r['type']) && $r['type'] === 'file') : ?>
                            <a href="<?php echo $view['router']->path('le_form_file_download', ['submissionId' => $item['id'], 'field' => $key]); ?>">
                                <?php echo $r['value']; ?>
                            </a>
                        <?php else : ?>
                            <?php echo $r['value']; ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        <?php else: ?>
                <?php echo $view->render('MauticEmailBundle:Email:noresults.html.php', ['tip' => 'mautic.form.noresults.tip', 'colspan' => $fieldCount, 'removespace' => true]); ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="panel-footer">
    <?php  echo $view->render('MauticCoreBundle:Helper:pagination.html.php', [
        'totalItems' => $totalCount,
        'page'       => $page,
        'limit'      => $limit,
        'baseUrl'    => $view['router']->path('le_form_action', ['objectAction'=>'view', 'objectId' => $form->getId()]),
        'sessionVar' => 'form.results',
    ]); ?>
</div>
