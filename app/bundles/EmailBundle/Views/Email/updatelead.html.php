<html>
<head>
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
            font-size: 55px;
            font-weight: 100;
            margin: 30px 0;
            line-height: 1.2;
            color: #337ab7;
        }
        div.main-content .footnote {
            font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            font-weight: normal;
            margin: 20px 0 0;
            padding: 15px 0;
            border-top: 1px solid #d0d0d0;
        }
        #label-adjustment{
            width: 150px;
            clear: left;
            text-align: right;
            padding-right: 10px;
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
            padding-right: 252px;
        }
    </style>
</head>
<body>

<?php if ($actionName == 'updatelead'): ?>
    <div class="main-content">
        <div >
            <h3>Your profile</h3>
            <h2> Update your profile </h2>
            <p>You are currently subscribed as: <b><?php echo $email ?></b></p>
        </div>
        <form  name='update-lead' action="<?php echo $actionroute?>"  onsubmit='return validateForm();' accept-charset="UTF-8" method="post" style="margin: 48px -140px 38px;">
            <div class="form-group form-inline" >
                <label id="label-adjustment">Name</label>
                <input type="text" class="form-control" name="leadname">
            </div>
            <b id="error"></b>
            <div class="form-group form-inline" >
                <label id="label-adjustment">New Email Address</label>
                <input id='email-address' type="text" class="form-control" onkeyup='clearEmailAddressError();' name="emailaddress">
            </div>
            <div class="form-group" style="padding-top: 15px;">
                <input style="margin-left: 153px;" type="submit" class="btn btn-primary" name="commit" value="Save changes">
            </div>
            </form>
        <div class="footnote">
            <p>Powered by <a href="https://leadsengage.com/?utm_source=app_unsubscribe_page">LeadsEngage</a></p>
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




