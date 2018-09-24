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

<style type="text/css" scoped>
    .mauticform_wrapper { max-width: 600px; margin: 10px auto; }
    .mauticform-innerform {}
    .mauticform-post-success {}
    .mauticform-name { font-weight: bold; font-size: 1.5em; margin-bottom: 3px; }
    .mauticform-description { margin-top: 2px; margin-bottom: 10px; }
    .mauticform-error { margin-bottom: 10px; color: red; }
    .mauticform-message { margin-bottom: 10px;color: green; }
    .mauticform-row { display: block; margin-bottom: 20px; }
    .mauticform-label { font-weight: 600; line-height: 18px;font-family: "Open Sans",Helvetica,Arial,sans-serif;margin-bottom: 5px;font-size: 1.1em; display: block;  }
    .mauticform-row.mauticform-required .mauticform-label:after { color: #e32; content: " *"; display: inline; }
    .mauticform-helpmessage { display: block; font-size: 0.9em; margin-bottom: 3px; }
    .mauticform-errormsg { display: block; color: red; margin-top: 2px; }
    .mauticform-selectbox, .mauticform-input, .mauticform-textarea { width: 100%; padding: 0.5em 0.5em; border: 1px solid #CCC; background: #fff; box-shadow: 0px 0px 0px #fff inset; border-radius: 4px; box-sizing: border-box; font-size:14px;  font-family: "Open Sans",Helvetica,Arial,sans-serif;  }
    .mauticform-checkboxgrp-row {}
    .mauticform-checkboxgrp-label { font-weight: normal; }
    .mauticform-checkboxgrp-checkbox {}
    .mauticform-radiogrp-row {}
    .mauticform-radiogrp-label { font-weight: normal; }
    .mauticform-radiogrp-radio {}
    .mauticform-button-wrapper .mauticform-button.btn-default, .mauticform-pagebreak-wrapper .mauticform-pagebreak.btn-default { color: #ffffff;  background-color: #ff9900;  border-color: #dddddd;  padding: 10px 40px;  font-size: 16px;  font-family:"Open Sans",Helvetica,Arial,sans-serif;  }
    .mauticform-button-wrapper .mauticform-button, .mauticform-pagebreak-wrapper .mauticform-pagebreak { display: inline-block;margin-bottom: 0;font-weight: 600;text-align: center;vertical-align: middle;cursor: pointer;background-image: none;border: 1px solid transparent;white-space: nowrap;padding: 6px 12px;font-size: 13px;line-height: 1.3856;border-radius: 3px;-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;}
    .mauticform-button-wrapper .mauticform-button.btn-default[disabled], .mauticform-pagebreak-wrapper .mauticform-pagebreak.btn-default[disabled] { background-color: #ffffff; border-color: #dddddd; opacity: 0.75; cursor: not-allowed; }
    .mauticform-pagebreak-wrapper .mauticform-button-wrapper {  display: inline; }
</style>