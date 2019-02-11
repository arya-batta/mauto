<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($tmpl == 'index') {
    //$view->extend('MauticLeadBundle:Note:index.html.php');
}
?>
<?php /**?>
<ul class="notes" id="LeadNotes">
    <?php foreach ($notes as $note): ?>
        <?php
        //Use a separate layout for AJAX generated content
        echo $view->render('MauticLeadBundle:Note:note.html.php', [
            'note'        => $note,
            'lead'        => $lead,
            'permissions' => $permissions,
        ]); ?>
    <?php endforeach; ?>
</ul>
<?php */ ?>
<div class="pa-md">
    <div class="row">
        <div class="col-sm-12" style="width: 106%;margin-left: -16px;padding-right: 0px;">
            <div class="panel">
                <div class="box-layout">
                    <div class="le-view">
                        <div class="le-note-panel">
                            <div class="le-notes-header">
                                <span class="le-notes-header-text">
                                    <?php echo $view['translator']->trans('le.lead.lead.tab.notes'); ?>
                                </span>
                                <a class="le-note-addbtn" href="<?php echo $view['router']->path('le_contactnote_action', ['leadId' => $lead->getId(), 'objectAction' => 'new']); ?>" data-toggle="ajaxmodal" data-target="#leSharedModal" data-header="<?php echo $view['translator']->trans('le.lead.note.header.new'); ?>">
                                    <span data-toggle="tooltip" title=" <?php echo $view['translator']->trans('le.lead.lead.tab.notes.tooltip'); ?>"><i class="fa fa-plus" style="color: #fff;font-size: 14px;"></i></span>
                                </a>
                            </div>
                            <div id="LeadNotes">
                                <?php foreach ($notes as $note): ?>
                                    <?php
                                        //Use a separate layout for AJAX generated content
                                        echo $view->render('MauticLeadBundle:Note:note.html.php', [
                                        'note'        => $note,
                                        'lead'        => $lead,
                                        'permissions' => $permissions,
                                    ]); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /** ?>
<?php if ($totalNotes = count($notes)): ?>
<div class="notes-pagination">
    <?php echo $view->render('MauticCoreBundle:Helper:pagination.html.php', [
        'totalItems' => $totalNotes,
        'target'     => '#notes-container',
        'page'       => $page,
        'limit'      => $limit,
        'sessionVar' => 'lead.'.$lead->getId().'.note',
        'baseUrl'    => $view['router']->path('le_contactnote_index', ['leadId' => $lead->getId()]),
    ]); ?>
</div>
<?php endif; ?>
<?php */ ?>
