<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        http://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Form\Validator\Constraints;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Model\EmailModel;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EmailContentVerifierValidator extends ConstraintValidator
{
    /**
     * @var MauticFactory
     */
    private $factory;

    /**
     * @var EmailModel
     */
    protected $emailmodel;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(MauticFactory $factory, TranslatorInterface $translator)
    {
        $this->factory        = $factory;
        $this->emailmodel     = $this->factory->getModel('email');
        $this->translator     = $translator;
    }

    /**
     * @param mixed      $emailid
     * @param Constraint $constraint
     */
    public function validate($emailid, Constraint $constraint)
    {
        $tokenvalue = '{{confirmation_link}}';

        if ($emailid == '') {
            return;
        }
        /** @var Email */
        $email      = $this->emailmodel->getEntity($emailid);

        if ((strpos($email->getCustomHtml(), $tokenvalue) !== false)) {
            return;
        } else {
            $this->context->addViolation($this->translator->trans('le.lead.list.optin.token.missing', ['%TOKEN%' => 'Confirmation link {{confirmation_link}}']));
        }
    }
}
