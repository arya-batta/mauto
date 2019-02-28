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
<html>
<head>
    <title><?php echo $name.' | LeadsEngage'; ?></title>
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
            letter-spacing: -1px;
            font-size: 55px;
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
        div.main-content .footnote {
            font-family: "GT-Walsheim-Regular", "Poppins-Regular", Helvetica, Arial, sans-serif;
            font-weight: normal;
            margin: 20px 0 0;
            padding: 15px 0;
            border-top: 1px solid #d0d0d0;
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
    </style>

</head>
<body>
<?php if ($actionName == 'subscribe') : ?>
    <?php if ($message == '') : ?>
        <div class="unsubscribe-intent">
            <div class="inner">
                <h3><?php echo $view['translator']->trans('le.email.subscriptions.title'); ?></h3>
                <p><?php echo $view['translator']->trans('le.lead.list.optin.subscriptions.content'); ?></p>
            </div>
        </div>
        <div class="main-content">
            <div class="subscription-manager">

                <h2 class="email"><?php echo $email?></h2>

                <div class="form-control button-container">

                    <form action="<?php echo $actionroute?>" accept-charset="UTF-8" method="post">
                        <input type="submit" name="commit" value="Unsubscribe from this list" class="cancel-subscription" data-disable-with="Reactivate my subscription" />
                    </form>
                </div>
                <div class="footnote">
                    <p>Powered by <a href="http://anyfunnels.com/?utm_source=app_unsubscribe_page">LeadsEngage</a></p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="unsubscribe-intent">
            <div class="inner">
                <h2><?php echo $message?></h2>
            </div>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="main-content">
        <div class="subscription-manager">

            <h2><?php echo $view['translator']->trans($subscriptiontitle); ?></h2>

            <p class="messageContainer"><?php echo $message?></p>

            <div class="footnotes">
                <p>Powered by <a href="http://anyfunnels.com/?utm_source=app_unsubscribe_page">LeadsEngage</a></p>
            </div>
        </div>
    </div>
<?php endif; ?>
</body>
</html>
