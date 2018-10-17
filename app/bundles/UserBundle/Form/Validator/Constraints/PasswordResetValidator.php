<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        http://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\UserBundle\Form\Validator\Constraints;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\UserBundle\Model\UserModel;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordResetValidator extends ConstraintValidator
{
    /**
     * @var MauticFactory
     */
    private $factory;

    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(MauticFactory $factory, UserModel $userModel, TranslatorInterface $translator)
    {
        $this->factory        = $factory;
        $this->userModel      = $userModel;
        $this->translator     = $translator;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $user = $this->userModel->getRepository()->findByIdentifier($value);

        if ($user != null) {
            return;
        } else {
            $this->context->addViolation($this->translator->trans('mautic.user.user.passwordreset.nouserfound11111'));
        }
    }
}
