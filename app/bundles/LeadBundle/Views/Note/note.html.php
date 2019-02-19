<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($note instanceof \Mautic\LeadBundle\Entity\LeadNote) {
    $id     = $note->getId();
    $text   = $note->getText();
    $date   = $note->getDateTime();
    $author = $note->getCreatedByUser();
    $type   = $note->getType();
} else {
    $id     = $note['id'];
    $text   = $note['text'];
    $date   = $note['dateTime'];
    $author = $note['createdByUser'];
    $type   = $note['type'];
}

switch ($type) {
    default:
    case 'general':
        $icon = 'fa-file-text';
        break;
    case 'email':
        $icon = 'fa-send';
        break;
    case 'call':
        $icon = 'fa-phone';
        break;
    case 'meeting':
        $icon = 'fa-group';
        break;
}

?>
<?php /** ?>
<li id="LeadNote<?php echo $id; ?>">
    <div class="panel ">
        <div class="panel-body np box-layout">
            <div class="height-auto icon bdr-r bg-dark-xs col-xs-1 text-center">
                <h3><i class="fa fa-lg fa-fw <?php echo $icon; ?>"></i></h3>
            </div>
            <div class="media-body col-xs-11 pa-10">
                <div class="pull-right btn-group">
                    <?php if ($permissions['edit']): ?>
                        <a class="btn btn-default btn-xs" href="<?php echo $view['router']->generate('le_contactnote_action', ['leadId' => $lead->getId(), 'objectAction' => 'edit', 'objectId' => $id]); ?>" data-toggle="ajaxmodal" data-target="#leSharedModal" data-header="<?php echo $view['translator']->trans('le.lead.note.header.edit'); ?>"><i class="fa fa-pencil"></i></a>
                    <?php endif; ?>
                     <?php if ($permissions['delete']): ?>
                         <a class="btn btn-default btn-xs"
                            data-toggle="confirmation"
                            href="<?php echo $view['router']->path('le_contactnote_action', ['objectAction' => 'delete', 'objectId' => $id, 'leadId' => $lead->getId()]); ?>"
                            data-message="<?php echo $view->escape($view['translator']->trans('le.lead.note.confirmdelete')); ?>"
                            data-confirm-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.delete')); ?>"
                            data-confirm-callback="executeAction"
                            data-cancel-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.cancel')); ?>">
                             <i class="fa fa-trash text-danger"></i>
                         </a>
                     <?php endif; ?>
                </div>
                <?php echo $text; ?>
                <div class="mt-15 text-muted"><i class="fa fa-clock-o fa-fw"></i><span class="small"><?php echo $view['date']->toFullConcat($date); ?></span> <i class="fa fa-user fa-fw"></i><span class="small"><?php echo $author; ?></span></div>
            </div>
        </div>
    </div>
</li>
 */ ?>

        <div class="le-notes-panel" id="LeadNote<?php echo $id; ?>" >
            <div class="le-notes-panel-body">
                <div class="le-notes-list clearfix" >
                    <div class="le-notes-icon"><i class="fa <?php echo $icon; ?>" style="height: 12px;width: 12px"></i></div>
                    <div class="le-notes-content">
                        <div><p><?php echo $text; ?></p></div>
                        <div class="col-sm-7" style="width: 58%;padding-left: 0">
                        <div class="le-small">
                            <i class="fa fa-user le-icon-user" style="color:#ffa800"></i>
                            <span class=""><?php echo $author; ?></span>
                        </div>
                            <small class="le-small le-text-muted"><span class="small"><?php echo $view['date']->toFullConcat($date); ?></small>
                        </div><div class="col-sm-3" style="margin-top: 9px;margin-left: 42px;">
                            <ul class="le-option-list le-list-divided">
                                <div>
                                <li data-toggle="" title="Edit" ><?php /**class="le-option-list-item"*/ ?>
                                    <?php if ($permissions['edit']): ?>
                                        <a class="le-option-list-item-link" style="margin-left: -1px;" href="<?php echo $view['router']->generate('le_contactnote_action', ['leadId' => $lead->getId(), 'objectAction' => 'edit', 'objectId' => $id]); ?>" data-toggle="ajaxmodal" data-target="#leSharedModal" data-header="<?php echo $view['translator']->trans('le.lead.note.header.edit'); ?>"><i class="fa fa-pencil"></i></a>
                                    <?php endif; ?>
                                </li>
                                </div>
                                <div>
                                <li data-toggle="" title="Delete" ><?php /**class="le-option-list-item"*/ ?>
                                    <?php if ($permissions['delete']): ?>
                                        <a class="le-option-list-item-link" data-toggle="confirmation" style="margin-left: 28px;"
                                           href="<?php echo $view['router']->path('le_contactnote_action', ['objectAction' => 'delete', 'objectId' => $id, 'leadId' => $lead->getId()]); ?>"
                                           data-message="<?php echo $view->escape($view['translator']->trans('le.lead.note.confirmdelete')); ?>"
                                           data-confirm-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.delete')); ?>"
                                           data-confirm-callback="executeAction"
                                           data-cancel-text="<?php echo $view->escape($view['translator']->trans('mautic.core.form.cancel')); ?>"><i class="fa fa-trash"></i></a>
                                    <?php endif; ?>
                                </li>
                                </div>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>


