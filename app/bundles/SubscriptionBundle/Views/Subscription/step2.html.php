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
<!DOCTYPE html>
<html>
<?php echo $view->render('MauticCoreBundle:Default:head.html.php'); ?>

<body class="header-fixed">
<section id="app-wrapper">

<div id="complete_profile_header">
    <p id="complete_profile"><?php echo $view['translator']->trans('leadsengage.kyc.complete'); ?>(2/3)</p>
</div>
    <div class="first_time_setup">
        <form class="form-group login-form" name="terms_condition" data-toggle="ajax" role="form" action="<?php echo $view['router']->path('le_kyc_action', ['objectAction' => 'kyc']) ?>" method="post">
<div role="tabpanel" class="tab-pane fade in active bdr-w-0">
    <div class="pt-md pr-md pl-md pb-md">
            <div class="panel-body" style="width:80%;">
                <div class="row">
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $view['form']->label($kycform['industry']); ?>
                            <?php echo $view['form']->widget($kycform['industry']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $view['form']->label($kycform['usercount']); ?>
                            <?php echo $view['form']->widget($kycform['usercount']); ?>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $view['form']->label($kycform['yearsactive']); ?>
                            <?php echo $view['form']->widget($kycform['yearsactive']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $view['form']->label($kycform['subscribercount']); ?>
                            <?php echo $view['form']->widget($kycform['subscribercount']); ?>
                        </div>
                    </div>
                    <br>

                    <div class="row">
                        <div class="col-md-12">
                            <?php echo $view['form']->label($kycform['subscribersource']); ?>
                            <?php echo $view['form']->widget($kycform['subscribersource']); ?>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $view['form']->label($kycform['previoussoftware']); ?>
                            <?php echo $view['form']->widget($kycform['previoussoftware']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $view['form']->label($kycform['knowus']); ?>
                            <?php echo $view['form']->widget($kycform['knowus']); ?>
                        </div>

                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $view['form']->label($kycform['emailcontent']); ?>
                            <?php echo $view['form']->widget($kycform['emailcontent']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $view['form']->label($kycform['others']); ?>
                            <?php echo $view['form']->widget($kycform['others']); ?>
                        </div>
                    </div>
                    <br>

                    <div class="row" style="display:none;">
                        <div class="col-md-12">
                            <?php echo $view['form']->label($form['email']); ?>
                            <?php echo $view['form']->widget($form['email']); ?>
                        </div>
                    </div>

                    <button class="signup_next" type="submit" value="Next"><?php echo $view['translator']->trans('leadsengage.kyc.next_button'); ?></button>
                    <span>&nbsp;&nbsp;&nbsp;&nbsp;Or&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <a class="signup_back" onclick="Le.RedirectToGivenURL('<?php echo $view['router']->path('le_kyc_action', ['objectAction' => 'signup']); ?>');"><?php echo $view['translator']->trans('leadsengage.kyc.go_back'); ?></a>
                </div>
            </div>
    </div>

</div>
        </form>
    </div>
</section>
<script>

    mQuery("body select").not('.multiselect, .not-chosen').each(function() {
        Le.activateChosenSelect(this);
    });

</script>
</body>
</html>