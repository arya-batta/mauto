<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$containerType     = (isset($type)) ? $type : 'text';
$defaultInputClass = (isset($inputClass)) ? $inputClass : 'input';
include __DIR__.'/field_helper.php';
$imgAttr=$view['assets']->getUrl('media/images/gcaptcha.png');
$label  = <<<HTML

                <label $labelAttr>{$field['label']}</label>
HTML;

if ($inBuilder):
    $image=<<<HTML

                <img style="width: 302px;height: 76px;" src='$imgAttr' alt="Google ReCaptcha"> </img>
HTML;

else:
    $image=<<<HTML

                <div class="g-recaptcha" data-sitekey="$gcaptchasitekey"></div>
HTML;
endif;

$html = <<<HTML

            <div $containerAttr>{$label}{$image} 
            <span class="leform-errormsg" style="display: none;">$validationMessage</span>      
            </div>

HTML;

echo $html;
