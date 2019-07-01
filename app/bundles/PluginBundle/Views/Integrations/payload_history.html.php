
<?php if (sizeof($payloads) > 0): ?>
    <a href="#" style="color:blue;text-decoration: underline;float:right;" onclick="Le.refreshPayloadHistory('<?php echo $name?>');">refresh</a>
    <table class="payload-history" style="word-break: break-all;">
        <thead>
        <tr>
            <th>
                <span class="header">PayLoad</span>
            </th>
            <th>
                <span class="header">Received At</span>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($payloads as $payload): ?>
            <tr>
                <td class="data" style="width: 80%;">
                    <span><?php echo $payload->getPayLoad()?></span>
                </td>
                <td>
                    <span class="data" style="width: 20%;"><?php echo $view['date']->toFull($payload->getCreatedOn()); ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="payload-noresult"> Nothing to display. <br>Connect your integration, send test data, press <a href="#" style="color:blue;text-decoration: underline;" onclick="Le.refreshPayloadHistory('<?php echo $name?>');">refresh</a> to get new data.</div>
<?php endif; ?>



