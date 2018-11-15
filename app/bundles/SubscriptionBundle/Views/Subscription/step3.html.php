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
    <p id="complete_profile"><?php echo $view['translator']->trans('leadsengage.kyc.anti_spam'); ?>(3/3)</p>
</div>
    <div class="first_time_setup">
    <form class="form-group login-form" name="terms_condition" data-toggle="ajax" role="form" action="<?php echo $view['router']->path('le_kyc_action', ['objectAction' => 'condition']) ?>" method="post">
        <div class="pt-md pr-md pl-md pb-md">

            <div class="panel-body" style="width:80%;">
                <p id="anti_spam_policy">
                    <?php echo $view['translator']->trans('leadsengage.kyc.anti_spam_desc'); ?>
                </p>
                <div id="terms">

                    <h4><b>LeadsEngage Anti Spam Policy</b></h4>
                    <p>LeadsEngage is against spam. We don't allow customers to send unsolicited emails from our platform.</p>
                    <br>
                    <p>We are OK if you send emails as mentioned below.</p>
                    <br>
                    <ul>
                        <li>Email newsletter subscriber</li>
                        <li>Email collected from website or optin forms</li>
                        <li>Your existing customers, leads, contacts.</li>
                        <li>Contacts collected through offline events such as expo.</li>
                        <li>Contact database that are verified, bounce free, spam free.</li>
                    </ul>
                    <br>
                    <p>We are NOT OK if you send email as mentioned below.</p>
                    <br>
                    <ul>
                        <li>Emailing to purchased 3rd party databases</li>
                        <li>Mass mailing contacts that are more than 1 year old and are not verified</li>
                    </ul>
                    <br>
                    <h4><b>Account Suspension</b></h4>
                    <p>We reserve the right to suspend your account immediately and start investigating your activity if your campaigns have high percentage of spam complaints (more than 0.2%), bounces (more than 5%), unsubscribes (more then 1%) or very small open rate (less than 3%). If it turns out that you were sending emails without permission - we will terminate your account. We can ask you to prove that you have permission from your recipients and we can close your account if you do not have such proof. Otherwise, we will activate your account and you will be able to use the service again.</p>

                    <h4><b>Multiple account abuse</b></h4>
                    <p>Creating multiple accounts with overlapping uses (similar email content, same links or same FROM email) or in order to evade the permanent suspension of a separate account is strictly forbidden.</p>

                </div>
                <br>
                <label class="control control-checkbox">
                    <?php echo $view['translator']->trans('leadsengage.kyc.terms_condition'); ?>
                    <input type="checkbox" name="kycinfo[conditionsagree]" required/>
                    <div class="control_indicator"></div>
                </label>
                <br>
                <label class="control control-checkbox">
                    <?php echo $view['translator']->trans('leadsengage.kyc.leadsengage_condition'); ?>
                    <input type="checkbox" name="kycinfo[leadsengage_conditionsagree]" required/>
                    <div class="control_indicator"></div>
                </label>
                <br>
                <button class="signup_next" type="submit" value="Next"><?php echo $view['translator']->trans('leadsengage.kyc.next_button'); ?></button>
                <span>&nbsp;&nbsp;&nbsp;&nbsp;Or&nbsp;&nbsp;&nbsp;&nbsp;</span>
                <a class="signup_back" onclick="Le.RedirectToGivenURL('<?php echo $view['router']->path('le_kyc_action', ['objectAction' => 'kyc']); ?>');"><?php echo $view['translator']->trans('leadsengage.kyc.go_back'); ?></a>
            </div>
        </div>
    </form>
    </div>
</section>
</body>
</html>