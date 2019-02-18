<tbody>
<tr>
    <td colspan="<?php echo $colspan?>">
<div class="col-md-12 col-md-offset-3 mt-md" style="white-space: normal;">
    <span class="beamer_avoid" style="max-height: 0px;max-width: 0px;"></span>
    <div class="mautibot-image col-xs-6 text-center">
    <?php if (!isset($header)) {
    $header = 'le.core.no.data';
} ?>
    <div class="noresult-alert-msg">
    <h4 style="font-size: 18px;"><?php echo $view['translator']->trans($header); ?></h4>
    <?php if (!isset($message)) {
    $message = 'le.core.no.data';
} ?>
    <p class="hide"><?php echo $view['translator']->trans($message); ?></p>
    </div>
</div>
</div>
    </td>
</tr>
</tbody>
