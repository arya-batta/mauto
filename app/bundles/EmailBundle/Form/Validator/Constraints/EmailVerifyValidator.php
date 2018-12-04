<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        http://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Form\Validator\Constraints;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\EmailBundle\Entity\AwsVerifiedEmails;
use Mautic\EmailBundle\Helper\EmailValidator;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EmailVerifyValidator extends ConstraintValidator
{
    /**
     * @var MauticFactory
     */
    private $factory;

    /**
     * @var EmailValidator
     */
    protected $emailValidator;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(MauticFactory $factory, EmailValidator $emailValidator, TranslatorInterface $translator)
    {
        $this->factory        = $factory;
        $this->emailValidator = $emailValidator;
        $this->translator     = $translator;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value != '' && !preg_match('/^.+\@\S+\.\S+$/', $value)) {
            return;
        }
        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        $configurator    = $this->factory->get('mautic.configurator');
        $params          = $configurator->getParameters();
        $newfromaddress  = $value;
        $mailertransport = $this->factory->get('session')->get('mailer_transport');
        $maileruser      = $this->factory->get('session')->get('mailer_user');
        $mailerpassword  = $this->factory->get('session')->get('mailer_password');
        $mailerregion    = $this->factory->get('session')->get('mailer_amazon_region');

        if (isset($mailertransport)) {
            $transport = $mailertransport;
        } else {
            $transport      = $params['mailer_transport'];
        }
        if (isset($maileruser)) {
            $emailuser = $maileruser;
        } else {
            $emailuser      = $params['mailer_user'];
        }
        if (isset($mailerpassword)) {
            $emailpassword= $mailerpassword;
        } else {
            $emailpassword      = $params['mailer_password'];
        }
        if (isset($mailerregion)) {
            $region = $mailerregion;
        } else {
            $region = $params['mailer_amazon_region'];
        }

        if ($transport == 'le.transport.amazon') {
            $message       = '';
            if ($emailpassword == '') {
                $message = $this->translator->trans('le.email.password.error');
            }
            /** @var \Mautic\EmailBundle\Model\EmailModel $emailModel */
            $emailModel       = $this->factory->getModel('email');
            $getAllEmailIds   = $emailModel->getAllEmailAddress();
            $verifiedemailRepo=$emailModel->getAwsVerifiedEmailsRepository();
            $verifiedEmails   = $this->emailValidator->getVerifiedEmailList($emailuser, $emailpassword, $region);
            /** @var \Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper $routerHelper */
            $awscallbackurl   = $this->factory->get('templating.helper.router')->url('le_mailer_transport_callback', ['transport' => 'amazon_api']);
            $isValidEmail     = $this->emailValidator->getVerifiedEmailAddressDetails($emailuser, $emailpassword, $region, $newfromaddress);
            $entity           = new AwsVerifiedEmails();
            $emailStatus      = $this->emailValidator->getEmailListAndStatus($emailuser, $emailpassword, $region, $newfromaddress);
            $awsAccountStatus = $this->emailValidator->getAwsAccountStatus($emailuser, $emailpassword, $region);
            if (!$awsAccountStatus && sizeof($verifiedEmails) > 0) {
                $awsAccountStatus=true;
            }
            if (!$isValidEmail) {
                if (!$emailStatus) {
                    $emailModel->upAwsDeletedEmailVerificationStatus($newfromaddress);
                    $result = $this->emailValidator->sendVerificationMail($emailuser, $emailpassword, $region, $newfromaddress, $awscallbackurl);
                    if ($result == 'Policy not written') {
                        $message = $this->translator->trans('le.email.verification.policy.error');
                    } elseif ($result == 'Sns Policy not written') {
                        $message = $this->translator->trans('le.email.verification.sns.policy.error');
                    } else {
                        $message = $this->translator->trans('le.aws.email.verification');
                    }
                }
            } else {
                if ($awsAccountStatus) {
                    return;
                } else {
                    $message = $this->translator->trans('le.email.verification.inactive.key');
                }
            }
            if (!empty($verifiedEmails)) {
                if (in_array($newfromaddress, $verifiedEmails)) {
                    if (!in_array($newfromaddress, $getAllEmailIds)) {
                        $entity->setVerifiedEmails($newfromaddress);
                        $entity->setVerificationStatus('Verified');
                        $verifiedemailRepo->saveEntity($entity);

                        return;
                    }
                }
            }

            if ($isValidEmail == 'Policy not written' && $awsAccountStatus) {
                $message = $this->translator->trans('le.email.verification.policy.error');
            }
            if (!in_array($newfromaddress, $getAllEmailIds) && $newfromaddress != '') {
                if (!$isValidEmail) {
                    $result = $this->emailValidator->sendVerificationMail($emailuser, $emailpassword, $region, $newfromaddress, $awscallbackurl);
                    if ($result == 'Policy not written') {
                        $message = $this->translator->trans('le.email.verification.policy.error');
                    } elseif ($result == 'Sns Policy not written') {
                        $message = $this->translator->trans('le.email.verification.sns.policy.error');
                    } else {
                        $message = $this->translator->trans('le.aws.email.verification');
                    }
                }
            } else {
                if ($emailStatus) {
                    $result = $this->emailValidator->getEmailVerificationStatus($emailuser, $emailpassword, $region, $newfromaddress);
                    if (!$result) {
                        $message = $this->translator->trans('le.email.verification.error');
                    } elseif ($result) {
                        return;
                    } elseif ($result == 'Policy not written') {
                        $message = $this->translator->trans('le.email.verification.policy.error');
                    }
                }
            }
            $this->context->addViolation($message);
        } else {
            return;
        }

        unset($transport);
        unset($emailpassword);
        unset($emailuser);
    }
}
