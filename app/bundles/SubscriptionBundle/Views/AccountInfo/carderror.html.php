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
<div class="email-type-modal-backdrop" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #2a323c; opacity: 0.7; z-index: 9000"></div>

<div class="modal fade in email-type-modal" style="display: block; z-index: 9999;margin-top:10%;">
    <div class="le-modal-gradient " style="margin-left: 25%;">
    <div class="modal-dialog le-gradient-align" style="width: 700px;height: 357px;" >
        <div class="modal-content le-modal-content" style="height: 352px;">
            <div class="modal-header" style="padding: 25px;">
                <a href="javascript: void(0);" onclick="Le.closePluginModel('card-error-info');" class="close" ><span aria-hidden="true">&times;</span></a>
                <b>There are a few reasons why we might be having a problem renewing your account.</b>

                <div class="modal-loading-bar"></div>
            </div>
            <div class="modal-body form-select-modal" style="background-color: #eee;height: 250px;padding: 25px;padding-bottom: 50px;">
                <div>
                    <p>Normally the problem is either that your bank declined the purchase for some reason. It could be that you canceled the card you signed up with or something else has changed which prevented us from renewing your plan.</p>
                    <p>Please check your billing information to be sure it isn't a problem with what you entered. If this information is correct, please try another payment option if you can or speak to your bank to be sure there are no problems you need to address. Don't worry, we'll wait.</p>
                    <p>We're sorry we can't be more specific, but the credit card companies and banks out of respect for your privacy do not provide details in such situations.</p>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
