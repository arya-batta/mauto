<?php
?>
<table style="width: 100%;">
    <thead>
    <tr>
        <th style="text-align: left;width: 25%;">
          Domain
        </th>
        <th style="width: 15%" data-help="spf">
         SPF
            <i class="fa fa-question ecircle" aria-hidden="true"></i>
        </th>
        <th style="width: 15%" data-help="dkim">
         DKIM
            <i class="fa fa-question ecircle" aria-hidden="true"></i>
        </th>
        <th style="width: 15%" data-help="dmark">
            DMARC
            <!--<i class="fa fa-question ecircle" aria-hidden="true"></i>-->
        </th>
        <!--<th style="width: 15%" data-help="tracking">
         Tracking
            <i class="fa fa-question ecircle" aria-hidden="true"></i>
        </th>-->
        <th style="width: 10%;">
         <span class="header"><span>
        </th>
        <th style="width: 10%;">
         <span class="header"><span>
        </th>
        <th style="width: 10%;">
         <span class="header"><span>
        </th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($sendingdomains as $sendingdomain): ?>
    <tr>
        <td style="text-align: left">
            <?php echo $sendingdomain->getDomain()?>
            <?php echo $sendingdomain->getIsDefault() ? '(default)' : ''?>
        </td>
        <td>
            <i class="fa fa-lg <?php echo $sendingdomain->getspfCheck() ? 'fas fa-check-circle' : 'fas fa-times-circle'?>"></i>
        </td>
        <td>
            <i class="fa fa-lg <?php echo $sendingdomain->getdkimCheck() ? 'fas fa-check-circle' : 'fas fa-times-circle'?>"></i>
        </td>
        <td>
            <i class="fa fa-lg <?php echo $sendingdomain->getdmarcCheck() ? 'fas fa-check-circle' : 'fas fa-times-circle'?>"></i>
        </td>
        <!--<td>
            <i class="fa fa-lg <?php echo $sendingdomain->gettrackingCheck() ? 'fas fa-check-circle' : 'fas fa-times-circle'?>"></i>
        </td>-->
        <td>
            <a href="javascript: void(0);" class="verify-sending-domain btn btn-default text-danger pull-right waves-effect" data-toggle="tooltip" title="<?php echo $view['translator']->trans('le.sending.domain.verify.tooltip'); ?>" data-domain="<?php echo $sendingdomain->getDomain()?>">Verify</a>
        </td>
        <td>
            <a href="javascript: void(0);" class="default-sending-domain btn btn-default text-danger pull-right waves-effect <?php echo $sendingdomain->getStatus() && !$sendingdomain->getIsDefault() ? '' : 'btn-disable'?>"  data-domain="<?php echo $sendingdomain->getDomain()?>">Set As Default</a>
        </td>
        <td>
            <a href="javascript: void(0);" class="remove-sending-domain btn btn-default text-danger pull-right waves-effect" data-toggle="tooltip" title="<?php echo $view['translator']->trans('le.sending.domain.remove.tooltip'); ?>" data-domain="<?php echo $sendingdomain->getDomain()?>"><i class="fa fa-trash-o"></i></a>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
