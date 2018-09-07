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

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EmailDomain extends Constraint
{
    public $message = 'le.email.domain.verification.error';

    public function validatedBy()
    {
        return 'emaildomain_verify';
    }
}
