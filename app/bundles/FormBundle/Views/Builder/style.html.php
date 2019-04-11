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
    .leform_wrapper { max-width: 600px; margin: 10px auto; }
    .leform-innerform {}
    .leform-post-success {}
    .leform-name { font-weight: bold; font-size: 1.5em; margin-bottom: 3px; }
    .leform-description { margin-top: 2px; margin-bottom: 10px; }
    .leform-error { margin-bottom: 10px; color: red; }
    .leform-message { margin-bottom: 10px;color: green; }
    .leform-row { display: block; margin-bottom: 20px; }
    .leform-label { font-weight: 600; line-height: 18px;font-family: "Open Sans",Helvetica,Arial,sans-serif;margin-bottom: 5px;font-size: 1.1em; display: block;  }
    .leform-row.leform-required .leform-label:after { color: #e32; content: " *"; display: inline; }
    .leform-helpmessage { display: block; font-size: 0.9em; margin-bottom: 3px; }
    .leform-errormsg { display: block; color: red; margin-top: 0px;margin-bottom: -15px;}
    .leform-selectbox, .leform-input, .leform-textarea { width: 100%; padding: 0.75em 0.5em; border: 1px solid #CCC; background: #fff; box-shadow: 0px 0px 0px #fff inset; border-radius: 4px; box-sizing: border-box; font-size:14px;  font-family: "Open Sans",Helvetica,Arial,sans-serif;  }
    .leform-checkboxgrp-row {}
    .leform-checkboxgrp-label { font-weight: normal;font-family: "Open Sans",Helvetica,Arial,sans-serif; }
    .leform-checkboxgrp-checkbox {}
    .leform-radiogrp-row {}
    .leform-radiogrp-label { font-weight: normal; }
    .leform-radiogrp-radio {}
    .leform-button-wrapper .leform-button.btn-default, .leform-pagebreak-wrapper .leform-pagebreak.btn-default { color: #ffffff;  background-color: #ff9900;  border-color: #dddddd;  padding: 10px 40px;  font-size: 16px;  font-family:"Open Sans",Helvetica,Arial,sans-serif;  }
    .leform-button-wrapper .leform-button, .leform-pagebreak-wrapper .leform-pagebreak { display: inline-block;margin-bottom: 0;font-weight: 600;text-align: center;vertical-align: middle;cursor: pointer;background-image: none;border: 1px solid transparent;white-space: nowrap;padding: 6px 12px;font-size: 14px;line-height: 1.3856;border-radius: 3px;-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;}
    .leform-button-wrapper .leform-button.btn-default[disabled], .leform-pagebreak-wrapper .leform-pagebreak.btn-default[disabled] { background-color: #ff9900; border-color: #dddddd; opacity: 0.75; cursor: not-allowed; }
    .leform-pagebreak-wrapper .leform-button-wrapper {  display: inline; }
</style>