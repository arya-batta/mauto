<?php
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('headerTitle', 'Account Sending Domain');
?>

<div class='panel panel-default state-inactive-alert-panel'>
    <div class='panel-body' style="background-color: white;">
        <div class="state-inactive-alert-title">
            Attention Needed!
        </div>
        <div class="state-inactive-alert-content">
            Email delivery for your account has been <b>Temporarily Inactive</b> since you don’t have any active sending domain.
        </div>
        <div class="state-inactive-alert-content">
            You can’t send emails for now till you resolve the issues
        </div>
        <div class="state-inactive-alert-content">
            <a href="https://anyfunnels.com/" target="_blank">Click Here</a> to know more or <a href="https://anyfunnels.com/" target="_blank">contact our support team</a>.
        </div>
        <div class="state-inactive-alert-content">
            <a href="<?php echo $view['router']->generate('le_config_action', ['objectAction' => 'edit', 'objectId'=> 'sendingdomain_config'])?>" target="_blank">Go to sending domain page</a>
        </div>
    </div>
</div>