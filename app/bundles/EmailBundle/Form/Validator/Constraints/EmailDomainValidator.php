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
use Mautic\EmailBundle\Helper\EmailValidator;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EmailDomainValidator extends ConstraintValidator
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
        $regexval = '/^([\w-\.]+(?!@gmail.com)(?!@yahoo.com)(?!@yahoo.co.in)(?!@yahoo.in)(?!@hotmail.com)(?!@yahoo.co.in)(?!@aol.com)(?!@abc.com)(?!@xyz.com)(?!@pqr.com)(?!@rediffmail.com)(?!@live.com)(?!@outlook.com)(?!@me.com)(?!@msn.com)(?!@ymail.com)([\w-])+[\w-]{2,4})?$/';
        if ($value != '' && preg_match($regexval, $value)) {
            return;
        } else {
            $this->context->addViolation($this->translator->trans('le.email.domain.verification.error'));
        }
    }
}
