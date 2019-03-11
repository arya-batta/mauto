<?php
/**
 * Created by Kaviarasan S.
 * User: cratio
 * Date: 17/4/18
 * Time: 5:18 PM.
 */
$showcgst  = 'hide';
$showgst   = 'hide';
$taxamount = 0;
if ($billing->getState() == 'Tamil Nadu') {
    $showcgst  = 'hide';
    $showgst   = 'hide';
    $taxamount = $payment->getTaxamount();
    $taxamount = ($taxamount / 2);
} else {
    $showgst   = 'hide';
    $showcgst  = 'hide';
    $taxamount = $payment->getTaxamount();
}
if ($billing->getCountry() != 'India' || $payment->getCurrency() != '₹') {
    $showcgst = $showgst = 'hide';
}
$isaddon          = false;
$excesscredit     = 0;
$planaftercredit  = $payment->getAfterCredits() != 'UL' ? number_format($payment->getAfterCredits()) : $payment->getAfterCredits();
if ($payment->getAmount() != '0' && $payment->getAmount() != $view['translator']->trans('le.pricing.plan.amount1') && $payment->getAmount() != $view['translator']->trans('le.pricing.plan.amount2') && $payment->getAmount() != $view['translator']->trans('le.pricing.plan.amount3')) {
    $isaddon      = true;
}

$plancustomcredit = $payment->getNetamount() * 500;
$planinfo         = $payment->getPlanName() == 'leplan1' ? 'Subscription charges for Engage plan.' : 'Subscription charges for Engage Pro plan.';
$planlabel        = $payment->getPlanLabel();
$planinfo         = 'le.pricing.plan.invoice.description';
if ($isaddon) {
    $planinfo  = 'le.pricing.plan.invoice.description1';
}
$planinfo = $view['translator']->trans($planinfo);
$country  = '';
if ($billing->getCountry() != 'blank') {
    $country = $billing->getCountry();
}
?>
<html>
<head>
    <title>Receipt : <?php echo $planlabel; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="icon" type="image/x-icon" href="<?php echo $view['assets']->getUrl('media/images/favicon.ico') ?>" />
    <link rel="icon" sizes="192x192" href="<?php echo $view['assets']->getUrl('media/images/favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?php echo $view['assets']->getUrl('media/images/apple-touch-icon.png') ?>" />
    <style>
        body{
            font: normal 16px/24px "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            color: #262626;
            -webkit-font-smoothing: antialiased;
        }
        body.canvas{
            font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.5;
            position: relative;
            color: #555;
            background: #fff;
        }

        .hide{
            display:none;
        }
        #pageheader{
            font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            margin:60px auto;
            height:550px;
            padding:20px;
            border-radius: 8px;
            -webkit-box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            background-color: #fff;
            font-size: 14px;
            position: relative;
            width:680px;
        }
        #leadsengage_info{
            float:left;
            padding-left:8px;
        }
        #customer_info{
            float:right;
            max-width: 35%;
            width: 30%;
            padding-left:8px;
            margin-right: 20px;
            position:relative;
            bottom:15px;
        }
        #invoice_info {
            float: left;
            width: 100%;
            padding-left:8px;
        }
        #payment_info{
            width: 100%;
            float:left;
            padding-left:8px;
        }
        .table_header{
            border-top: 1px solid #dddddd;
            border-bottom: 1px solid #dddddd;
            padding:5px;
        }
        .planname_body{
            width:20%;
            text-align:left;
        }
        .description_body{
            width:32%;
            text-align:left;
        }
        .service_body{
            width:28%;
            text-align:left;
        }
        .amount_body{
            width:20%;
            text-align:right;
        }
        .gst_body{
            width:25%;
            text-align:right;
        }
        .gstamount_body{
            width:25%;
            text-align:right;
        }
        .table_body{
            border-bottom: 1px solid #dddddd;
            padding:5px;
            height: 50px;
        }
        a {
            text-decoration: underline;
            -webkit-text-decoration-skip: ink;
            text-decoration-skip: ink;
            color: #262626;
            font-family: "GT-Walsheim-Medium", "Poppins-Medium", Helvetica, Arial, sans-serif;
            font-weight: normal;
            -webkit-transition: color 150ms;
            transition: color 150ms;
        }
    </style>
</head>
<body class="canvas">
<div id="pageheader">
    <div style="text-align: left;float:left; width:50%;">
        <img style="width: 200px;" src="<?php echo $view['assets']->getUrl('media/images/anyfunnel_logo_large_icon.png') ?>">
    </div>
    <br>
    <div style="float:right;text-align:center; width:50%;position: relative;bottom: 15px;">
        <span style="font-size:25px;"><b>INVOICE</b></span>
    </div>
    <br>
    <div id="leadsengage_info">
        <p>
            <b>LeadsEngage Inc.</b><br>
            340 S Lemon Ave,<br>
            Walnut, CA 91789, USA<br>
        </p>
    </div>
    <div id="customer_info">
        <p>
            <b><?php echo $billing->getCompanyname(); ?></b><br>
            <?php echo $billing->getCompanyaddress(); ?><br>
            <span style="word-wrap:break-word;"><?php echo $billing->getCity().'-'.$billing->getPostalcode().','.'<br>'.$billing->getState().', '.$country.'.'; ?></span><br>
            <b style="<?php echo ($billing->getGstnumber() == '') ? 'display:none' : ''; ?>">TAXID:</b> <?php echo $billing->getGstnumber(); ?><br>
        </p>
    </div>

    <br>
    <br>
    <div id="invoice_info">
        <p>
            <b>Date:</b> <?php echo $view['date']->toDate($payment->getcreatedOn()); ?><br>
            <b>Invoice #</b>:<?php echo $payment->getOrderID(); ?><br>
<!--            <b>Transaction ID:</b> --><?php //echo $payment->getPaymentID();?><!--<br>-->
        </p>
    </div>
    <div id="payment_info">
        <table style="width:100%;padding-right:20px;font: inherit;">
            <thead>
            <th class="table_header planname_body" >
                Plan Name
            </th>
            <th class="table_header description_body" >
                Description
            </th>
            <th class="table_header service_body" >
                Service Period
            </th>
            <th class="table_header amount_body" >
                Amount (USD)
            </th>
            </thead>
            <tbody>
            <tr>
                <td class="table_body planname_body">
                    <?php echo $planlabel; ?>
                </td>
                <td class="table_body description_body">
                    <span class="<?php echo ($payment->getTaxamount() == 1) ? 'hide' : 'hide'; ?>"><?php echo 'Up To '.$planaftercredit.' leads credits '; ?></span> <!--Email Credits-->
                    <span class="<?php echo ($payment->getTaxamount() == 0) ? 'hide' : 'hide'; ?>"><?php echo 'Charged for '.$plancustomcredit.' additional lead credits'; ?></span> <!--Email Credits-->
                    <span><?php echo $planinfo ?></span> <!--Email Credits-->
                </td>
                <td class="table_body service_body">
                    <?php echo $view['date']->toDate($license->getLicenseStart()); ?> -<br> <?php echo $view['date']->toDate($payment->getValidityTill()); ?>
                </td>
                <td class="table_body amount_body">
                    <?php echo $payment->getCurrency().($payment->getProvider() == 'razorpay' ? number_format($payment->getNetamount()) : $payment->getNetamount())?>
                </td>
            </tr>
            <tr class="<?php echo $showcgst; ?>">
                <td class="table_body gst_body">
                    <p>Gross Amount
                        <br>SGST (9%)<br>CGST (9%)
                    </p>
                </td>
                <td class="table_body gstamount_body">
                    <p>
                        <?php echo $payment->getCurrency().($payment->getProvider() == 'razorpay' ? number_format($payment->getNetamount()) : $payment->getNetamount())?><br><?php echo $payment->getCurrency(); echo $taxamount; ?><br><?php echo $payment->getCurrency(); echo $taxamount; ?>
                    </p>
                </td>
            </tr>
            <tr class="<?php echo $showgst; ?>">
                <td class="table_body gst_body">
                    <p>Gross Amount<br>GST (18%)</p>
                </td>
                <td class="table_body gstamount_body">
                    <p>
                        <?php echo $payment->getCurrency().($payment->getProvider() == 'razorpay' ? number_format($payment->getNetamount()) : $payment->getNetamount())?><br><?php echo $payment->getCurrency(); echo $taxamount; ?>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="table_body planname_body">

                </td>
                <td class="table_body description_body">

                </td>
                <td class="table_body gst_body">
                    Amount Paid
                </td>
                <td class="table_body amount_body">
                    <?php echo $payment->getCurrency().($payment->getProvider() == 'razorpay' ? number_format($payment->getNetamount()) : $payment->getNetamount())?>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <br>
        <p>If you have any questions about this invoice, please contact <a href="mailto:support@anyfunnels.com"><b>support@anyfunnels.com</b></a>.</p>
        <p>AnyFunnels Team</p>
    </div>
</div>

</body>
</html>

