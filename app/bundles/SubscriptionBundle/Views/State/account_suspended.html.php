<?php
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('headerTitle', 'Account Suspended');
?>

<div class='panel panel-default state-inactive-alert-panel'>
    <div class='panel-body' style="background-color: white;">
        <div class="state-inactive-alert-title">
            Sorry, we can’t approve your account
        </div>
        <div class="state-inactive-alert-content">
            We review all new customers in order to protect our system and others from potential spam email.
        </div>
        <div class="state-inactive-alert-content">
            Our system performs many checks and tests to determine the validity of new accounts.
        </div>
        <div class="state-inactive-alert-content">
            Unfortunately your account didn't pass those tests.
        </div>
        <div class="state-inactive-alert-content">
            If you believe that it's a mistake, please <a href="https://anyfunnels.com/" target="_blank">contact our support team</a>.
        </div>

    </div>
</div>