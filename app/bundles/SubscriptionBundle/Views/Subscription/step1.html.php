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
    <p id="complete_profile"><?php echo $view['translator']->trans('leadsengage.kyc.complete'); ?>(1/3)</p>
</div>
    <div class="first_time_setup">
        <form class="form-group login-form" name="terms_condition" data-toggle="ajax" role="form" action="<?php echo $view['router']->path('le_kyc_action', ['objectAction' => 'signup']) ?>" method="post">
<div role="tabpanel" class="tab-pane fade in active bdr-w-0">
    <div class="pt-md pr-md pl-md pb-md">
            <div class="panel-body" style="width:80%;">
                <div class="row">
                    <div>
                        <p>
                        <h3><b><?php echo $view['translator']->trans('leadsengage.kyc.about_you'); ?></b></h3>
                        </p>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $view['form']->label($userform['firstName']); ?>
                            <?php echo $view['form']->widget($userform['firstName']); ?>
                        </div>
                        <div class="col-md-6">

                            <?php echo $view['form']->label($userform['lastName']); ?>
                            <?php echo $view['form']->widget($userform['lastName']); ?>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $view['form']->label($form['phonenumber']); ?>
                            <?php echo $view['form']->widget($form['phonenumber']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $view['form']->label($userform['email']); ?>
                            <?php echo $view['form']->widget(
                                    $userform['email'],
                                    ['attr' => ['style' => 'pointer-events: none;background-color: #ebedf0;opacity: 1;']]
                                ); ?>
                        </div>
                    </div>
                    <br>
                    <div>
                        <p>
                        <h3><b><?php echo $view['translator']->trans('leadsengage.kyc.orginfo'); ?></b></h3>
                        <br>
                        <?php echo $view['translator']->trans('leadsengage.kyc.org.desc'); ?>
                        </p>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo $view['form']->label($billform['companyname']); ?>
                            <?php echo $view['form']->widget($billform['companyname']); ?>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            <?php echo $view['form']->label($billform['companyaddress']); ?>
                            <?php echo $view['form']->widget($billform['companyaddress']); ?>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $view['form']->label($billform['postalcode']); ?>
                            <?php echo $view['form']->widget($billform['postalcode']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $view['form']->label($billform['state']); ?>
                            <?php echo $view['form']->widget($billform['state']); ?>
                        </div>

                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $view['form']->label($billform['city']); ?>
                            <?php echo $view['form']->widget($billform['city']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $view['form']->label($billform['country']); ?>
                            <?php echo $view['form']->widget($billform['country']); ?>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo $view['form']->row($form['timezone']); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $view['form']->label($billform['gstnumber']); ?>
                            <?php echo $view['form']->widget($billform['gstnumber']); ?>
                        </div>
                    </div>
                    <br>
                    <br>
                    <button class="signup_next" type="submit" value="Next"><?php echo $view['translator']->trans('leadsengage.kyc.next_button'); ?></button>
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