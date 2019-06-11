<?php
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('headerTitle', 'Account Under Review');
?>

<div class='panel panel-default state-inactive-alert-panel'>
    <div class='panel-body' style="background-color: white;">
        <div class="state-inactive-alert-title">
            Attention Needed!
        </div>
        <div class="state-inactive-alert-content state-inactive-padding">
            Your account has been kept Under Review as our compliance team found that your account has high spam complaints or bounce rate or policy violations. You can’t send emails for now till you resolve the issues.
        </div>
        <div class="state-inactive-alert-content hide">
            You can’t send emails for now till you resolve the issues
        </div>
        <div class="state-inactive-alert-content state-inactive-padding">
            <a href="https://anyfunnels.freshdesk.com/support/tickets/new" target="_blank">Click Here</a>  to contact our support team.
        </div>
    </div>
</div>