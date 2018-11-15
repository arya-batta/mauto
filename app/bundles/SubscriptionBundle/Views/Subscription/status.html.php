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
$view['slots']->set('leContent', 'subscription-status');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.core.subscriptions'));
?>
<div class="alert <?php echo $status ? 'alert-success' : 'alert-danger' ?> subscription-status col-md-6 col-md-offset-3 mt-md" style="white-space: normal;">
    <h4><?php echo $status ? 'Transaction Successfull' : 'Transaction Failed' ?></h4>
    <table>
        <tbody>
        <tr>
            <td><span>Payment ID:</span><span><?php echo $paymentid ?></span></td>
        </tr>
        <tr>
            <td><span>Subscription ID:</span><span><?php echo $subscriptionid ?></span></td>
        </tr>
        </tbody>
    </table>
</div>
<div class="clearfix"></div>
