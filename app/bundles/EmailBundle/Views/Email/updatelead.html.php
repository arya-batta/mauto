<html>
<head>
    <title>Update Your Profile Information| LeadsEngage</title>
    <link rel="icon" type="image/x-icon" href="<?php echo $view['assets']->getUrl('media/images/favicon.ico') ?>" />
    <link rel="icon" sizes="192x192" href="<?php echo $view['assets']->getUrl('media/images/favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?php echo $view['assets']->getUrl('media/images/apple-touch-icon.png') ?>" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
        .unsubscribe-intent{
            margin-bottom: -70px;
            background-color: #f5f5f5;
            border-bottom: 2px solid #dedede;
        }
        .unsubscribe-intent .inner{
            margin: 0 auto;
            padding: 30px 60px;
            width: 820px;
        }
        div.main-content{
            margin: 0 auto;
            padding: 120px 190px;
            width: 820px;
        }
        div.main-content h3 {
            font-family: Roboto,Helvetica,Arial,sans-serif;
            font-size: 36px;
            font-weight: 300;
            margin: 30px 200px;
            line-height: 1.2;
            color: #f22446;
        }
        div.main-content .footnote {
            font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            font-weight: normal;
            margin: 20px 0 0;
            padding: 15px 0;
            border-top: 1px solid #d0d0d0;
        }
        #label-adjustment{
            width: 170px;
            clear: left;
            text-align: left;
            padding-right: 10px;
            font-size:16px;
        }
        #error {
            text-align: center;
        }
        #error {
            text-shadow: 0px 0px 1px #999999;
            letter-spacing: 1px;
            font-weight: 300;
            font-size: 12px;
            color: #FF2A2A;
            padding-right: 200px;
        }
        .unsubscribe-intent{
            margin-bottom: -70px;
            background-color: #f5f5f5;
            border-bottom: 2px solid #dedede;
        }
        .unsubscribe-intent .inner{
            margin: 0 auto;
            padding: 30px 60px;
            width: 820px;
        }
        .unsubscribe-intent .inner h3{
            font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            font-weight: bold;
        }
        .unsubscribe-intent .inner p{
            font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            font-weight: normal;
        }
        div.main-content{
            margin: 0 auto;
            padding: 60px 60px;
            width: 820px;
        }
        div.main-content .subscription-manager h2 {
            font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            font-weight: normal;
        }
        div.main-content h2 {
            font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            font-weight: normal;
            margin-left: -5px;
            margin-top: 65px;
            letter-spacing: -1px;
            font-size: 32px;
            color: #333;
        }
        .cancel-subscription{
            background-color: #f22446;
            color: #FFFFFF;
            border: 1px solid !important;
            font-size: 16px;
            padding: 10px 10px;
        }
        .cancel-subscription:hover{
            background-color: #f64623;
            color: #FFFFFF;
            border: 1px solid #f22446;

        }
        .buttonle {
            color: #fff;
            background-color: #f22446;
            border-color: #f22446!important;
            font-size:16px;
            padding:10px 25px;
        }
        .buttonle:hover{
            color: #fff;
            background-color: #f22446;
            border-color: #f22446!important;
        }

        div.main-content .footnote {
            font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            font-weight: normal;
            margin: 20px 0px 0px;
            padding: 15px 0;
            border-top: 1px solid #d0d0d0;
            font-size:16px;

        }
        div.main-content .footnotes {
            font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            font-weight: normal;
            margin: 20px 0 0;
            padding: 0 0;

        }
        div.main-content .messageContainer {
            font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            font-weight: normal;
            margin: 20px 0 0;
            padding: 15px 0;
            border-bottom: 1px solid #d0d0d0;
        }
        .form-control {
            display: block;
            width: 100%;
            height: 34px;
            padding: 20px 45px;
            font-size: 14px;
            line-height: 1.42857143;
            color: #555;
            background-color: #fff;
            background-image: none;
            border: 1px solid #ccc;
            border-radius: 4px;
            -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
            box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
            -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
            -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
            transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        }
    </style>
</head>
<body>
<div class="unsubscribe-intent">
    <div class="inner">
        <h3>Manage Subscription...</h3>
    </div>
</div>

<?php if ($actionName == 'updatelead'): ?>
    <div class="main-content">
        <div >
            <h2> Update your profile </h2>
            <p>You are currently subscribed as: <b><?php echo $email ?></b></p>
        </div>
        <form  name='update-lead' action="<?php echo $actionroute?>"  onsubmit='return validateForm();' accept-charset="UTF-8" method="post" style="margin: 48px 0px 38px;">
            <div class="form-group form-inline" >
                <label id="label-adjustment">First Name</label>
                <input type="text" class="form-control" name="firstname">
            </div>
            <div class="form-group form-inline" style="padding-top: 15px;">
                <label id="label-adjustment">Last Name</label>
                <input type="text" class="form-control" name="lastname">
            </div>
            <b id="error"></b>
            <div class="form-group form-inline" >
                <label id="label-adjustment">Email Address</label>
                <input id='email-address' type="text" class="form-control" onkeyup='clearEmailAddressError();' name="emailaddress">
            </div>
            <div class="form-group" style="padding-top: 15px; display:inline-flex;">
                <input type="submit" class="buttonle" name="commit" value="Save changes">
                <a href="<?php echo $view['router']->path('le_email_subscribe', ['idHash' => $idHash]); ?>" class="buttonle" style="margin:0px 50px;">Or unsubscribe from all mailing list</a>
            </div>
        </form>
        <div class="footnote">
            <p style="margin:10px">Powered by <a href="https://leadsengage.com/?utm_source=app_unsubscribe_page"><u>LeadsEngage</u></a></p>
        </div>
    </div>
<?php elseif ($actionName == 'viewlead'): ?>
    <div class="main-content" style="width: 200%">
        <h3>Your details have been updated.</h3>
    </div>
<?php else : ?>
    <div class="unsubscribe-intent">
        <div class="inner">
            <h2><?php echo $message?></h2>
        </div>
    </div>
<?php endif; ?>
<script type='text/javascript'>
    function clearEmailAddressError() {
        document.getElementById("emailaddress").style.borderColor = "#e0e0e0";
    }
    function  validateForm() {
        var emailaddress = document.forms['update-lead']['emailaddress'].value;
        var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

        if (emailaddress == null || emailaddress == '') {
            document.getElementById("email-address").style.borderColor = "#e66c3e";
            document.getElementById('error').style.display = "block";
            document.getElementById('error').innerHTML = "Please fill your emailid!";
            return false;
        } else if (!emailaddress.match(mailformat)) {
            document.getElementById("email-address").style.borderColor = "#e66c3e";
            document.getElementById('error').style.display = "block";
            document.getElementById('error').innerHTML = "Please fill valid email!";
            return false;
        }
    }

</script>




