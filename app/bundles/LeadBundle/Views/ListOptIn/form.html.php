<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('leContent', 'listoptin');
$id     = $form->vars['data']->getId();

if (!empty($id)) {
    $name   = $form->vars['data']->getName();
    $header = $view['translator']->trans('le.lead.list.optin.header.edit', ['%name%' => $name]);
} else {
    $header = $view['translator']->trans('le.lead.list.optin.header.new');
}
$view['slots']->set('headerTitle', $header);

$doubleoptinclass = ($entity->getListtype()) ? '' : 'hide';
$thankyouclass    = ($entity->getThankyou()) ? '' : 'hide';
$goodbyeclass     = ($entity->isGoodbye()) ? '' : 'hide';

//$filterErrors     = (count($form['doubleoptinemail']->vars['errors']) || count($form['thankyouemail']->vars['errors']) || count($form['goodbyeemail']->vars['errors'])) ? 'class="text-danger"' : '';
$filterErrors     = (count($form['fromname']->vars['errors']) || count($form['fromaddress']->vars['errors']) || count($form['subject']->vars['errors']) || count($form['message']->vars['errors'])) ? 'class="text-danger"' : '';
$mainErrors       = (count($form['name']->vars['errors'])) ? 'class="text-danger"' : '';
//$isSetupError     = (count($form['doubleoptinemail']->vars['errors']) || count($form['thankyouemail']->vars['errors']) || count($form['goodbyeemail']->vars['errors']));
$isSetupError     = (count($form['fromname']->vars['errors']) || count($form['fromaddress']->vars['errors']) || count($form['subject']->vars['errors']) || count($form['message']->vars['errors']));
?>

<?php echo $view['form']->start($form); ?>
<ul class="bg-auto nav nav-pills nav-wizard pr-md pl-md ">
    <li class="<?php echo ($isSetupError) ? '' : 'active'; ?> detail" id="detailstab">
        <a href="#details" style="padding: 0px 47px;" role="tab" data-toggle="tab"<?php echo $mainErrors; ?>>
            <div class="content-wrapper-first">
                <div><span class="small-xx">Step 01</span></div>
                <label><?php echo $view['translator']->trans('le.core.lists.name'); ?>
                    <?php if ($mainErrors): ?>
                        <i class="fa fa-warning"></i>
                    <?php endif; ?> </label>
            </div>
        </a>
    </li>
    <li class="<?php echo ($isSetupError) ? 'active' : ''; ?>" id="filterstab" data-toggle="tooltip" title="" data-placement="top" >
        <a href="#filters" style="padding: 0px 38px;" role="tab" data-toggle="tab"<?php echo $filterErrors; ?>>
            <div class="content-wrapper-first">
                <div><span class="small-xx">Step 02</span></div>
                <label>  <?php echo $view['translator']->trans('le.core.lists.setup'); ?>
                    <?php if ($filterErrors): ?>
                        <i class="fa fa-warning"></i>
                    <?php endif; ?> </label>
            </div>
        </a>
    </li>
</ul>
    <!-- start: box layout -->
<!--    <div class="center-align-container">-->
<div id="page-wrap">
    <div class="col-md-12 bg-white height-auto">
        <div class="tab-content pa-md">
<!--    <div class="listoptin-template-content">-->
            <div class="tab-pane fade in <?php echo ($isSetupError) ? '' : 'active'; ?> bdr-w-0" id="details">
                <div class="row">
                    <div class="col-md-6">
                        <?php echo $view['form']->row($form['name']); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <?php echo $view['form']->row($form['description']); ?>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade <?php echo ($isSetupError) ? 'in active' : ''; ?> bdr-w-0" id="filters">
                <div class="row">
                    <div class="col-md-12">
                        <?php echo $view['form']->label($form['listtype']); ?>
                            <p style="font-size: 12px;"><?php echo $view['translator']->trans('le.list.optin.tooltip'); ?></p>
                        <?php echo $view['form']->widget($form['listtype']); ?>
                    </div>
                </div>
                <div class="doubleoptinfields hide">
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo $view['form']->row($form['fromname']); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo $view['form']->row($form['fromaddress']); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo $view['form']->row($form['subject']); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo $view['form']->row($form['message']); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo $view['form']->label($form['resend']); ?>
                                <p style="font-size: 12px;"><?php echo $view['translator']->trans('le.lead.list.optin.resend.tooltip'); ?></p>
                            <?php echo $view['form']->widget($form['resend']); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row hide">
                <div class="col-xs-12">
                <!-- start: tab-content -->
                    <div class="tab-content pa-md">
                        <div class="tab-pane fade in <?php echo ($isSetupError) ? '' : 'active'; ?> bdr-w-0" id="details">
                        </div>
                        <div class="tab-pane fade <?php echo ($isSetupError) ? 'in active' : ''; ?> bdr-w-0" id="filters">
                            <div class="row">
                                <div class="col-md-6">
                                    <?php echo $view['form']->row($form['isPublished']); ?>
                                </div>
                                <div class="col-md-6 <?php echo $doubleoptinclass; ?>" id="doubleoptinemaillist">
                                    <?php echo $view['form']->row($form['doubleoptinemail']); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php echo $view['form']->row($form['thankyou']); ?>
                                </div>
                                <div class="col-md-6 <?php echo $thankyouclass; ?>" id="thankyouemaillist">
                                    <?php echo $view['form']->row($form['thankyouemail']); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php echo $view['form']->row($form['goodbye']); ?>
                                </div>
                                <div class="col-md-6 <?php echo $goodbyeclass; ?>" id="goodbyeemaillist">
                                    <?php echo $view['form']->row($form['goodbyeemail']); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12" id="unsubscribe_text_div">
                                    <?php echo $view['form']->row($form['footerText']); ?>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<!--        </div>-->
        </div>
    </div>
</div>
<!--</div>-->
<?php echo $view['form']->end($form); ?>