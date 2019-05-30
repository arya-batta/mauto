<?php
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('headerTitle', 'Account Under Review');
?>

<div class='panel panel-default state-inactive-alert-panel'>
    <div class='panel-body' style="background-color: white;">
        <div class="state-inactive-alert-title">
            Attention Needed!
        </div>
        <div class="state-inactive-alert-content">
            Your account has been kept <b>Under Review</b> as our compliance team found that your account have high spam complaints or bounce rate or policy violations.
        </div>
        <div class="state-inactive-alert-content">
            You can’t send emails for now till you resolve the issues
        </div>
        <div class="state-inactive-alert-content">
            <a href="https://anyfunnels.com/" target="_blank">Click Here</a> to know more or <a href="https://anyfunnels.com/" target="_blank">contact our support team</a>.
        </div>
    </div>
</div>