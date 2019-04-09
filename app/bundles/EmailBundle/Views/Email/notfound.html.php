
<html>
<head>
    <title>Email Webview | LeadsEngage</title>
    <link rel="icon" type="image/x-icon" href="<?php echo $view['assets']->getUrl('media/images/favicon.ico') ?>" />
    <link rel="icon" sizes="192x192" href="<?php echo $view['assets']->getUrl('media/images/favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?php echo $view['assets']->getUrl('media/images/apple-touch-icon.png') ?>" />
    <style>
        .unsubscribe-intent{
            margin-bottom: -70px;
            background-color: #f5f5f5;
            border-bottom: 2px solid #dedede;
        }
        .unsubscribe-intent .inner{
            margin: 0 auto;
            padding: 30px 200px;
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
        .inner-font {
            text-align: center;
            font-weight: 500;
            font-size: 30px;
            color:#333333;
            font-family:'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        </style>
</head>
<body>
<div class="unsubscribe-intent">
    <div class="inner">
        <h2 class="inner-font"><?php echo $content ?></h2>
    </div>
</div>
</body>
</html>