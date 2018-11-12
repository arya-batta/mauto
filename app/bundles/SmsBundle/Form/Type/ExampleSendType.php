<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ExampleSendType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'smss',
            'sortablelist',
            [
                'label'            => 'le.email.example_recipients',
                'add_value_button' => 'le.email.add_recipient',
                'option_notblank'  => false,
            ]
        );

        $builder->add(
            'buttons',
            'form_buttons',
            [
                'apply_text' => false,
                'save_text'  => 'le.email.send',
                'save_icon'  => 'fa fa-send',
            ]
        );
    }
}
