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
<div class="row pricing-div-1">
    <div class="col-md-12">
        <div class="afprice">
            <div class="afpriceone">
                <p class="afpricetitle hide"><span style="color:#0071ff; font-size:30px;">SPECIAL PLAN</span></p>
                <p class="afpriceamt"><span style="text-align:center;font-weight:500;font-size:24px;color:#000;">$</span>19</p>
                <p ><span style="text-align:center;font-weight:500;font-size:14px;line-height:25px;color:#000;">per month, paid monthly.</span></p>
                <p class="afgap" style="margin-top:10px;"></p>
                <a style="width:200px;" href="<?php echo $view['router']->generate('le_billing_index') ?>" id="leplan1-button" type="button" class="price-buttonfoot">
                    <?php echo $view['translator']->trans('le.billing.paynow.button.text'); ?>
                </a>
                <p class="afreq" style="display:none;font-size: 13px; color: #000;line-height: 18px; margin-top: 10px;">No credit card required.</p>
            </div>
            <div class="afinclude">
                <h2>Plan Includes</h2>
                <section>

                    <div class="afincludesdiv"><span class="afincludes"><b>100K FREE</b> email credits, every month<div><span></span></div></span></div>

                    <div class="afincludesdiv"><span class="afincludes"><b>UNLIMITED</b> contacts<div><span></span></div></span></div>

                    <div class="afincludesdiv"><span class="afincludes"><b>UNLIMITED</b> access to all features<div><span></span></div></span></div>

                    <div class="afincludesdiv hide"><span class="afincludes"><b>FREE</b> onboarding & phone/ email support<div><span></span></div></span></div>
                </section>

                <h2>Additional Email Credits</h2>
                <section>

                    <div class="afincludesdiv"><span class="afincludes">19$ for every 100K email credit, unlimited validity<div><span></span></div></span></div>
                    <div class="afincludesdiv"><span class="afincludes">Note: Additional email credits will be used only if you exhaust the free email credits in a month</span></div>
                </section>

            </div>
        </div>
    </div>
</div>

