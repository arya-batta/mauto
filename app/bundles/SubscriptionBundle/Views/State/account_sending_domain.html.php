<?php
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('headerTitle', 'Account Sending Domain');
?>

<div class='panel panel-default state-inactive-alert-panel'>
    <div class='panel-body' style="background-color: white;">
        <div class="state-inactive-alert-title">
            Attention Needed!
        </div>
        <div class="state-inactive-alert-content state-inactive-padding">
            Email delivery for your account has been Temporarily Inactive since you don’t have any active sending domain. You can’t send emails for now till you resolve the issues.
        </div>
        <div class="state-inactive-alert-content hide">
            You can’t send emails for now till you resolve the issues
        </div>
        <div class="state-inactive-alert-content state-inactive-padding">
            <a href="<?php echo $view['router']->generate('le_config_action', ['objectAction' => 'edit', 'objectId'=> 'sendingdomain_config'])?>" target="_blank">Click Here</a> to add a sending domain or <a href="https://anyfunnels.freshdesk.com/support/tickets/new" target="_blank">Click here</a> to contact our support team.
        </div>
        <div class="state-inactive-alert-content hide">
            <a href="<?php echo $view['router']->generate('le_config_action', ['objectAction' => 'edit', 'objectId'=> 'sendingdomain_config'])?>" target="_blank">Go to sending domain page</a>
        </div>
    </div>
</div>