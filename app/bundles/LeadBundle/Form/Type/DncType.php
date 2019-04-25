<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class DncType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'reason',
            'textarea',
            [
                'label'       => 'le.lead.batch.dnc_reason',
                'required'    => false,
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'constraints' => [new Assert\Length(
                    [
                        'max'        => 250,
                        'maxMessage' => 'le.lead.batch.dnc.reason.length',
                    ]
                )],
            ]
        );

        $builder->add(
            'ids',
            'hidden'
        );

        $builder->add(
            'buttons',
            'form_buttons',
            [
                'apply_text'     => false,
                'save_text'      => 'mautic.core.form.save',
                'cancel_onclick' => 'javascript:void(0);',
                'cancel_attr'    => [
                    'data-dismiss' => 'modal',
                ],
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'lead_batch_dnc';
    }
}
