<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\UserBundle\Model;

use Mautic\CoreBundle\Model\FormModel;
use Mautic\EmailBundle\Helper\MailHelper;
use Mautic\UserBundle\Entity\User;
use Mautic\UserBundle\Entity\UserToken;
use Mautic\UserBundle\Enum\UserTokenAuthorizator;
use Mautic\UserBundle\Event\StatusChangeEvent;
use Mautic\UserBundle\Event\UserEvent;
use Mautic\UserBundle\Model\UserToken\UserTokenServiceInterface;
use Mautic\UserBundle\UserEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Class UserModel.
 */
class UserModel extends FormModel
{
    /**
     * @var MailHelper
     */
    protected $mailHelper;

    /**
     * @var UserTokenServiceInterface
     */
    private $userTokenService;

    /**
     * UserModel constructor.
     *
     * @param MailHelper                $mailHelper
     * @param UserTokenServiceInterface $userTokenService
     */
    public function __construct(
        MailHelper $mailHelper,
        UserTokenServiceInterface $userTokenService
    ) {
        $this->mailHelper          = $mailHelper;
        $this->userTokenService    = $userTokenService;
    }

    /**
     * Define statuses that are supported.
     *
     * @var array
     */
    private $supportedOnlineStatuses = [
        'online',
        'idle',
        'away',
        'manualaway',
        'dnd',
        'offline',
    ];

    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        return $this->em->getRepository(User::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionBase()
    {
        return 'user:users';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    public function saveEntity($entity, $unlock = true)
    {
        if (!$entity instanceof User) {
            throw new MethodNotAllowedHttpException(['User'], 'Entity must be of class User()');
        }

        parent::saveEntity($entity, $unlock);
    }

    /**
     * Get a list of users for an autocomplete input.
     *
     * @param string $search
     * @param int    $limit
     * @param int    $start
     * @param array  $permissionLimiter
     *
     * @return array
     */
    public function getUserList($search = '', $limit = 10, $start = 0, $permissionLimiter = [])
    {
        $currentuser=$this->userHelper->getUser();
        $this->getRepository()->setCurrentUser($currentuser);

        return $this->getRepository()->getUserList($search, $limit, $start, $permissionLimiter);
    }

    /**
     * Checks for a new password and rehashes if necessary.
     *
     * @param User                     $entity
     * @param PasswordEncoderInterface $encoder
     * @param string                   $submittedPassword
     * @param bool|false               $validate
     *
     * @return string
     */
    public function checkNewPassword(User $entity, PasswordEncoderInterface $encoder, $submittedPassword, $validate = false)
    {
        if ($validate) {
            if (strlen($submittedPassword) < 6) {
                throw new \InvalidArgumentException($this->translator->trans('mautic.user.user.password.minlength', 'validators'));
            }
        }

        if (!empty($submittedPassword)) {
            //hash the clear password submitted via the form
            return $encoder->encodePassword($submittedPassword, $entity->getSalt());
        }

        return $entity->getPassword();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof User) {
            throw new MethodNotAllowedHttpException(['User'], 'Entity must be of class User()');
        }
        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('user', $entity, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            return new User();
        }

        $entity = parent::getEntity($id);

        if ($entity) {
            //add user's permissions
            $entity->setActivePermissions(
                $this->em->getRepository('MauticUserBundle:Permission')->getPermissionsByRole($entity->getRole())
            );
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof User) {
            throw new MethodNotAllowedHttpException(['User'], 'Entity must be of class User()');
        }

        switch ($action) {
            case 'pre_save':
                $name = UserEvents::USER_PRE_SAVE;
                break;
            case 'post_save':
                $name = UserEvents::USER_POST_SAVE;
                break;
            case 'pre_delete':
                $name = UserEvents::USER_PRE_DELETE;
                break;
            case 'post_delete':
                $name = UserEvents::USER_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new UserEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }
            $this->dispatcher->dispatch($name, $event);

            return $event;
        }

        return null;
    }

    /**
     * Get list of entities for autopopulate fields.
     *
     * @param string $type
     * @param string $filter
     * @param int    $limit
     *
     * @return array
     */
    public function getLookupResults($type, $filter = '', $limit = 10)
    {
        $results = [];
        switch ($type) {
            case 'role':
                $results = $this->em->getRepository('MauticUserBundle:Role')->getRoleList($filter, $limit);
                break;
            case 'user':
                $currentuser=$this->userHelper->getUser();
                $this->em->getRepository('MauticUserBundle:User')->setCurrentUser($currentuser);
                $results = $this->em->getRepository('MauticUserBundle:User')->getUserList($filter, $limit, 0, []);
                break;
            case 'position':
                $results = $this->em->getRepository('MauticUserBundle:User')->getPositionList($filter, $limit);
                break;
        }

        return $results;
    }

    /**
     * Resets the user password and emails it.
     *
     * @param User                     $user
     * @param PasswordEncoderInterface $encoder
     * @param string                   $newPassword
     */
    public function resetPassword(User $user, PasswordEncoderInterface $encoder, $newPassword)
    {
        $encodedPassword = $this->checkNewPassword($user, $encoder, $newPassword);

        $user->setPassword($encodedPassword);
        $this->saveEntity($user);
    }

    /**
     * @param User $user
     *
     * @return UserToken
     */
    protected function getResetToken(User $user)
    {
        $userToken = new UserToken();
        $userToken->setUser($user)
            ->setAuthorizator(UserTokenAuthorizator::RESET_PASSWORD_AUTHORIZATOR)
            ->setExpiration((new \DateTime())->add(new \DateInterval('PT24H')))
            ->setOneTimeOnly();

        return $this->userTokenService->generateSecret($userToken, 64);
    }

    /**
     * @param User   $user
     * @param string $token
     *
     * @return bool
     */
    public function confirmResetToken(User $user, $token)
    {
        $userToken = new UserToken();
        $userToken->setUser($user)
            ->setAuthorizator(UserTokenAuthorizator::RESET_PASSWORD_AUTHORIZATOR)
            ->setSecret($token);

        return $this->userTokenService->verify($userToken);
    }

    /**
     * @param User $user
     *
     * @throws \RuntimeException
     */
    public function sendResetEmail(User $user, $mailer)
    {
        $mailer->start();
        $resetToken = $this->getResetToken($user);
        $this->em->persist($resetToken);
        try {
            $this->em->flush();
        } catch (\Exception $exception) {
            $this->logger->addError($exception->getMessage());
            throw new \RuntimeException();
        }
        $resetLink  = $this->router->generate('le_user_passwordresetconfirm', ['token' => $resetToken->getSecret()], true);
        $message    = \Swift_Message::newInstance();
        $message->setTo([$user->getEmail() => $user->getName()]);
        $message->setFrom(['support@leadsengage.com' => 'LeadsEngage']);
        $message->setSubject($this->translator->trans('mautic.user.user.passwordreset.subject'));
        /*$text = $this->translator->trans(
            'mautic.user.user.passwordreset.email.body',
            ['%name%' => $user->getFirstName(), '%resetlink%' => '<a href="'.$resetLink.'">'.$resetLink.'</a>']
        );
        $text = str_replace('\\n', "\n", $text);*/
        $name = $user->getFirstName();
        $text = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional //EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<!-- saved from url=(0048)https://cops.leadsengage.com/one-off/preview/245 -->
<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\" class=\"gr__cops_leadsengage_com\"><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"><!--[if gte mso 9]><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]--><meta name=\"viewport\" content=\"width=device-width\"><!--[if !mso]><!--><meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"><!--<![endif]--><title>02- Reset Password Email</title><!--[if !mso]><!--><!--<![endif]--><style type=\"text/css\">
		body {
			margin: 0;
			padding: 0;
		}

		table,
		td,
		tr {
			vertical-align: top;
			border-collapse: collapse;
		}

		* {
			line-height: inherit;
		}

		a[x-apple-data-detectors=true] {
			color: inherit !important;
			text-decoration: none !important;
		}

		.ie-browser table {
			table-layout: fixed;
		}

		[owa] .img-container div,
		[owa] .img-container button {
			display: block !important;
		}

		[owa] .fullwidth button {
			width: 100% !important;
		}

		[owa] .block-grid .col {
			display: table-cell;
			float: none !important;
			vertical-align: top;
		}

		.ie-browser .block-grid,
		.ie-browser .num12,
		[owa] .num12,
		[owa] .block-grid {
			width: 620px !important;
		}

		.ie-browser .mixed-two-up .num4,
		[owa] .mixed-two-up .num4 {
			width: 204px !important;
		}

		.ie-browser .mixed-two-up .num8,
		[owa] .mixed-two-up .num8 {
			width: 408px !important;
		}

		.ie-browser .block-grid.two-up .col,
		[owa] .block-grid.two-up .col {
			width: 306px !important;
		}

		.ie-browser .block-grid.three-up .col,
		[owa] .block-grid.three-up .col {
			width: 306px !important;
		}

		.ie-browser .block-grid.four-up .col [owa] .block-grid.four-up .col {
			width: 153px !important;
		}

		.ie-browser .block-grid.five-up .col [owa] .block-grid.five-up .col {
			width: 124px !important;
		}

		.ie-browser .block-grid.six-up .col,
		[owa] .block-grid.six-up .col {
			width: 103px !important;
		}

		.ie-browser .block-grid.seven-up .col,
		[owa] .block-grid.seven-up .col {
			width: 88px !important;
		}

		.ie-browser .block-grid.eight-up .col,
		[owa] .block-grid.eight-up .col {
			width: 77px !important;
		}

		.ie-browser .block-grid.nine-up .col,
		[owa] .block-grid.nine-up .col {
			width: 68px !important;
		}

		.ie-browser .block-grid.ten-up .col,
		[owa] .block-grid.ten-up .col {
			width: 60px !important;
		}

		.ie-browser .block-grid.eleven-up .col,
		[owa] .block-grid.eleven-up .col {
			width: 54px !important;
		}

		.ie-browser .block-grid.twelve-up .col,
		[owa] .block-grid.twelve-up .col {
			width: 50px !important;
		}
	</style><style type=\"text/css\" id=\"media-query\">
		@media only screen and (min-width: 640px) {
			.block-grid {
				width: 620px !important;
			}

			.block-grid .col {
				vertical-align: top;
			}

			.block-grid .col.num12 {
				width: 620px !important;
			}

			.block-grid.mixed-two-up .col.num3 {
				width: 153px !important;
			}

			.block-grid.mixed-two-up .col.num4 {
				width: 204px !important;
			}

			.block-grid.mixed-two-up .col.num8 {
				width: 408px !important;
			}

			.block-grid.mixed-two-up .col.num9 {
				width: 459px !important;
			}

			.block-grid.two-up .col {
				width: 310px !important;
			}

			.block-grid.three-up .col {
				width: 206px !important;
			}

			.block-grid.four-up .col {
				width: 155px !important;
			}

			.block-grid.five-up .col {
				width: 124px !important;
			}

			.block-grid.six-up .col {
				width: 103px !important;
			}

			.block-grid.seven-up .col {
				width: 88px !important;
			}

			.block-grid.eight-up .col {
				width: 77px !important;
			}

			.block-grid.nine-up .col {
				width: 68px !important;
			}

			.block-grid.ten-up .col {
				width: 62px !important;
			}

			.block-grid.eleven-up .col {
				width: 56px !important;
			}

			.block-grid.twelve-up .col {
				width: 51px !important;
			}
		}

		@media (max-width: 640px) {

			.block-grid,
			.col {
				min-width: 320px !important;
				max-width: 100% !important;
				display: block !important;
			}

			.block-grid {
				width: 100% !important;
			}

			.col {
				width: 100% !important;
			}

			.col>div {
				margin: 0 auto;
			}

			img.fullwidth,
			img.fullwidthOnMobile {
				max-width: 100% !important;
			}

			.no-stack .col {
				min-width: 0 !important;
				display: table-cell !important;
			}

			.no-stack.two-up .col {
				width: 50% !important;
			}

			.no-stack.mixed-two-up .col.num4 {
				width: 33% !important;
			}

			.no-stack.mixed-two-up .col.num8 {
				width: 66% !important;
			}

			.no-stack.three-up .col.num4 {
				width: 33% !important;
			}

			.no-stack.four-up .col.num3 {
				width: 25% !important;
			}

			.mobile_hide {
				min-height: 0px;
				max-height: 0px;
				max-width: 0px;
				display: none;
				overflow: hidden;
				font-size: 0px;
			}
		}
	</style><link rel=\"icon\" type=\"image/x-icon\" href=\"https://cops.leadsengage.com/media/images/favicon.ico\"><link rel=\"icon\" sizes=\"192x192\" href=\"https://cops.leadsengage.com/media/images/favicon.ico\"><link rel=\"apple-touch-icon\" href=\"https://cops.leadsengage.com/media/images/favicon.ico\"><script style=\"display: none;\">var tvt = tvt || {}; tvt.captureVariables = function(a){for(var b=
new Date,c={},d=Object.keys(a||{}),e=0,f;f=d[e];e++)if(a.hasOwnProperty(f)&&\"undefined\"!=typeof a[f])try{var g=[];c[f]=JSON.stringify(a[f],function(a,b){try{if(\"function\"!==typeof b){if(\"object\"===typeof b&&null!==b){if(b instanceof HTMLElement||b instanceof Node||-1!=g.indexOf(b))return;g.push(b)}return b}}catch(H){}})}catch(l){}a=document.createEvent(\"CustomEvent\");a.initCustomEvent(\"TvtRetrievedVariablesEvent\",!0,!0,{variables:c,date:b});window.dispatchEvent(a)};window.setTimeout(function() {tvt.captureVariables({'dataLayer.hide': (function(a){a=a.split(\".\");for(var b=window,c=0;c<a.length&&(b=b[a[c]],b);c++);return b})('dataLayer.hide'),'gaData': window['gaData'],'dataLayer': window['dataLayer']})}, 2000);</script></head><body class=\"clean-body\" style=\"margin: 0; padding: 0; -webkit-text-size-adjust: 100%; background-color: #FFFFFF;\" data-gr-c-s-loaded=\"true\">
<style type=\"text/css\" id=\"media-query-bodytag\">
@media (max-width: 640px) {
  .block-grid {
    min-width: 320px!important;
    max-width: 100%!important;
    width: 100%!important;
    display: block!important;
  }
  .col {
    min-width: 320px!important;
    max-width: 100%!important;
    width: 100%!important;
    display: block!important;
  }
  .col > div {
    margin: 0 auto;
  }
  img.fullwidth {
    max-width: 100%!important;
    height: auto!important;
  }
  img.fullwidthOnMobile {
    max-width: 100%!important;
    height: auto!important;
  }
  .no-stack .col {
    min-width: 0!important;
    display: table-cell!important;
  }
  .no-stack.two-up .col {
    width: 50%!important;
  }
  .no-stack.mixed-two-up .col.num4 {
    width: 33%!important;
  }
  .no-stack.mixed-two-up .col.num8 {
    width: 66%!important;
  }
  .no-stack.three-up .col.num4 {
    width: 33%!important
  }
  .no-stack.four-up .col.num3 {
    width: 25%!important
  }
}
</style><!--[if IE]><div class=\"ie-browser\"><![endif]--><table class=\"nl-container\" style=\"table-layout: fixed; vertical-align: top; min-width: 320px; Margin: 0 auto; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #FFFFFF; width: 100%;\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" bgcolor=\"#FFFFFF\" valign=\"top\"><tbody><tr style=\"vertical-align: top;\" valign=\"top\"><td style=\"word-break: break-word; vertical-align: top; border-collapse: collapse;\" valign=\"top\">
					<!--[if (mso)|(IE)]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td align=\"center\" style=\"background-color:#FFFFFF\"><![endif]-->
					<div style=\"background-color:transparent;\">
						<div class=\"block-grid \" style=\"Margin: 0 auto; min-width: 320px; max-width: 620px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;;\">
							<div style=\"border-collapse: collapse;display: table;width: 100%;background-color:transparent;\">
								<!--[if (mso)|(IE)]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:transparent;\"><tr><td align=\"center\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:620px\"><tr class=\"layout-full-width\" style=\"background-color:transparent\"><![endif]-->
								<!--[if (mso)|(IE)]><td align=\"center\" width=\"620\" style=\"background-color:transparent;width:620px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;\" valign=\"top\"><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"padding-right: 10px; padding-left: 10px; padding-top:5px; padding-bottom:5px;\"><![endif]-->
								<div class=\"col num12\" style=\"min-width: 320px; max-width: 620px; display: table-cell; vertical-align: top;;\">
									<div style=\"width:100% !important;\">
										<!--[if (!mso)&(!IE)]><!-->
										<div style=\"border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 10px; padding-left: 10px;\">
											<!--<![endif]-->
											<div class=\"img-container left fixedwidth\" align=\"left\" style=\"padding-right: 0px;padding-left: 0px;\">
												<!--[if mso]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr style=\"line-height:0px\"><td style=\"padding-right: 0px;padding-left: 0px;\" align=\"left\"><![endif]-->
												<div style=\"font-size:1px;line-height:15px\">&nbsp;</div><a href=\"https://leadsengage.com/\" target=\"_blank\"> <img class=\"left fixedwidth\" align=\"left\" border=\"0\" src=\"https://leadsengage.com/wp-content/uploads/leadsengage/leadsengage_logo-black.png\" alt=\"Marketing Automation Software | LeadsEngage\" title=\"Marketing Automation Software | LeadsEngage\" style=\"outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; clear: both; height: auto; float: none; border: none; width: 100%; max-width: 120px; display: block;\" width=\"120\"></a>
												<div style=\"font-size:1px;line-height:15px\">&nbsp;</div>
												<!--[if mso]></td></tr></table><![endif]-->
											</div>
											<!--[if mso]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"padding-right: 30px; padding-left: 0px; padding-top: 15px; padding-bottom: 15px; font-family: Georgia, 'Times New Roman', serif\"><![endif]-->
											<div style=\"color:#555555;font-family:Georgia, Times, &#39;Times New Roman&#39;, serif;line-height:150%;padding-top:15px;padding-right:30px;padding-bottom:15px;padding-left:0px;\">
												<div style=\"font-size: 12px; line-height: 18px; font-family: Georgia, Times, &#39;Times New Roman&#39;, serif; color: #555555;\">
													<p style=\"font-size: 14px; line-height: 21px; margin: 0;\"><span style=\"line-height: 21px; font-size: 14px;\">Hi&nbsp;$name,</span></p>
													<p style=\"font-size: 14px; line-height: 21px; margin: 0;\">&nbsp;</p>
													<p style=\"font-size: 14px; line-height: 21px; margin: 0;\"><span style=\"line-height: 21px; font-size: 14px;\">This email is in your inbox because you requested to reset your password. Here is the link to do just that. </span></p>
												</div>
											</div>
											<!--[if mso]></td></tr></table><![endif]-->
											<div class=\"button-container\" align=\"left\" style=\"padding-top:0px;padding-right:10px;padding-bottom:0px;padding-left:0px;\">
												<!--[if mso]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"border-spacing: 0; border-collapse: collapse; mso-table-lspace:0pt; mso-table-rspace:0pt;\"><tr><td style=\"padding-top: 0px; padding-right: 10px; padding-bottom: 0px; padding-left: 0px\" align=\"left\"><v:roundrect xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:w=\"urn:schemas-microsoft-com🏢word\" href=\"\" style=\"height:36pt; width:168.75pt; v-text-anchor:middle;\" arcsize=\"11%\" stroke=\"false\" fillcolor=\"#3292e0\"><w:anchorlock/><v:textbox inset=\"0,0,0,0\"><center style=\"color:#ffffff; font-family:Georgia, 'Times New Roman', serif; font-size:16px\"><![endif]-->
												<div style=\"text-decoration:none;display:inline-block;color:#ffffff;background-color:#3292e0;border-radius:5px;-webkit-border-radius:5px;-moz-border-radius:5px;width:auto; width:auto;;border-top:1px solid #3292e0;border-right:1px solid #3292e0;border-bottom:1px solid #3292e0;border-left:1px solid #3292e0;padding-top:10px;padding-bottom:10px;font-family:Georgia, Times, &#39;Times New Roman&#39;, serif;text-align:center;mso-border-alt:none;word-break:keep-all;\"><span style=\"padding-left:30px;padding-right:30px;font-size:16px;display:inline-block;\">
														<a href='$resetLink' style=\"font-size: 16px; line-height: 28px;text-decoration: none;color: white;\">Reset Your Password</a>
													</span></div>
												<!--[if mso]></center></v:textbox></v:roundrect></td></tr></table><![endif]-->
											</div>
											<!--[if mso]><table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"padding-right: 30px; padding-left: 0px; padding-top: 15px; padding-bottom: 15px; font-family: Georgia, 'Times New Roman', serif\"><![endif]-->
											<div style=\"color:#555555;font-family:Georgia, Times, &#39;Times New Roman&#39;, serif;line-height:150%;padding-top:15px;padding-right:30px;padding-bottom:15px;padding-left:0px;\">
												<div style=\"font-size: 12px; line-height: 18px; font-family: Georgia, Times, &#39;Times New Roman&#39;, serif; color: #555555;\">
													<p style=\"font-size: 12px; line-height: 18px; margin: 0;\"><span style=\"font-size: 14px; line-height: 21px;\">If you did not request a password reset, please reply to this email and let us know. We can investigate if the request was unauthorized.</span></p>
													<p style=\"font-size: 12px; line-height: 18px; margin: 0;\">&nbsp;</p>
													<p style=\"font-size: 12px; line-height: 18px; margin: 0;\"><span style=\"font-size: 14px; line-height: 21px;\">Thank you!<br>LeadsEngage Support.</span></p>
												</div>
											</div>
											<!--[if mso]></td></tr></table><![endif]-->
											<table class=\"divider\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;\" valign=\"top\"><tbody><tr style=\"vertical-align: top;\" valign=\"top\"><td class=\"divider_inner\" style=\"word-break: break-word; vertical-align: top; min-width: 100%; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; padding-top: 20px; padding-right: 20px; padding-bottom: 20px; padding-left: 20px; border-collapse: collapse;\" valign=\"top\">
															<table class=\"divider_content\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; border-top: 3px solid #3292e0; height: 0px;\" align=\"center\" height=\"0\" valign=\"top\"><tbody><tr style=\"vertical-align: top;\" valign=\"top\"><td style=\"word-break: break-word; vertical-align: top; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; border-collapse: collapse;\" height=\"0\" valign=\"top\"><span></span></td>
																	</tr></tbody></table></td>
													</tr></tbody></table><!--[if (!mso)&(!IE)]><!--></div>
										<!--<![endif]-->
									</div>
								</div>
								<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
								<!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
							</div>
						</div>
					</div>
					<!--[if (mso)|(IE)]></td></tr></table><![endif]-->
				</td>
			</tr></tbody></table><!--[if (IE)]></div><![endif]-->
</body><div><div class=\"gr_-editor gr-iframe-first-load\" style=\"display: none;\"><div class=\"gr_-editor_back\"></div><iframe class=\"gr_-ifr gr-_dialog-content\" src=\"./02- Reset Password Email_files/saved_resource.html\"></iframe></div></div><grammarly-card><div></div></grammarly-card><span class=\"gr__tooltip\"><span class=\"gr__tooltip-content\"></span><i class=\"gr__tooltip-logo\"></i><span class=\"gr__triangle\"></span></span></html>";
        //$html = nl2br($text);

        $message->setBody($text, 'text/html');
        //$mailer->setPlainText(strip_tags($text));

        $mailer->send($message);
    }

    /**
     * Set user preference.
     *
     * @param      $key
     * @param null $value
     * @param User $user
     */
    public function setPreference($key, $value = null, User $user = null)
    {
        if ($user == null) {
            $user = $this->userHelper->getUser();
        }

        $preferences       = $user->getPreferences();
        $preferences[$key] = $value;

        $user->setPreferences($preferences);

        $this->getRepository()->saveEntity($user);
    }

    /**
     * Get user preference.
     *
     * @param      $key
     * @param null $default
     * @param User $user
     */
    public function getPreference($key, $default = null, User $user = null)
    {
        if ($user == null) {
            $user = $this->userHelper->getUser();
        }
        $preferences = $user->getPreferences();

        return (isset($preferences[$key])) ? $preferences[$key] : $default;
    }

    /**
     * @param $status
     */
    public function setOnlineStatus($status)
    {
        $status = strtolower($status);

        if (in_array($status, $this->supportedOnlineStatuses)) {
            if ($this->userHelper->getUser()->getId()) {
                $this->userHelper->getUser()->setOnlineStatus($status);
                $this->getRepository()->saveEntity($this->userHelper->getUser());

                if ($this->dispatcher->hasListeners(UserEvents::STATUS_CHANGE)) {
                    $event = new StatusChangeEvent($this->userHelper->getUser());
                    $this->dispatcher->dispatch(UserEvents::STATUS_CHANGE, $event);
                }
            }
        }
    }

    /**
     * Return list of Users for formType Choice.
     *
     * @return array
     */
    public function getOwnerListChoices()
    {
        return $this->getRepository()->getOwnerListChoices($this);
    }

    public function getCurrentUserEntity()
    {
        return $this->userHelper->getUser();
    }

    public function getUserTimeZone()
    {
        $query = $this->em->getConnection()->createQueryBuilder()
            ->select('a.timezone')
            ->from(MAUTIC_TABLE_PREFIX.'accountinfo', 'a');

        $result = $query->execute()->fetch();

        return $result['timezone'];
    }

    public function getAdminUserList()
    {
        return $this->getRepository()->getAdminUserlist();
    }
}
