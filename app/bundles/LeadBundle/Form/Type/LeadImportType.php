<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

/**
 * Class LeadImportType.
 */
class LeadImportType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'file',
            'file',
            [
                'label' => 'le.lead.import.file',
                'attr'  => [
                    'accept' => '.csv',
                    'class'  => 'form-control',
                ],
                'constraints' => [
                    new File(
                        [
                            'mimeTypes'        => ['text/csv', 'text/plain', 'application/octet-stream'],
                            'mimeTypesMessage' => 'mautic.core.invalid_file_type',
                        ]
                    ),
                    new \Symfony\Component\Validator\Constraints\NotBlank(
                        ['message' => 'le.lead.import.upload']
                    ),
                ],
                'error_bubbling' => true,
            ]
        );

        $constraints = [
            new \Symfony\Component\Validator\Constraints\NotBlank(
                ['message' => 'mautic.core.value.required']
            ),
        ];

        $default = (empty($options['data']['delimiter'])) ? ',' : htmlspecialchars($options['data']['delimiter']);
        $builder->add(
            'delimiter',
            'text',
            [
                'label' => 'le.lead.import.delimiter',
                'attr'  => [
                    'class' => 'form-control',
                ],
                'data'        => $default,
                'constraints' => $constraints,
            ]
        );

        $default = (empty($options['data']['enclosure'])) ? '&quot;' : htmlspecialchars($options['data']['enclosure']);
        $builder->add(
            'enclosure',
            'text',
            [
                'label' => 'le.lead.import.enclosure',
                'attr'  => [
                    'class' => 'form-control',
                ],
                'data'        => $default,
                'constraints' => $constraints,
            ]
        );

        $default = (empty($options['data']['escape'])) ? '\\' : $options['data']['escape'];
        $builder->add(
            'escape',
            'text',
            [
                'label' => 'le.lead.import.escape',
                'attr'  => [
                    'class' => 'form-control',
                ],
                'data'        => $default,
                'constraints' => $constraints,
            ]
        );

        $default = (empty($options['data']['batchlimit'])) ? 300 : (int) $options['data']['batchlimit'];
        $builder->add(
            'batchlimit',
            'text',
            [
                'label' => 'le.lead.import.batchlimit',
                'attr'  => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.lead.import.batchlimit_tooltip',
                ],
                'data'        => $default,
                'constraints' => $constraints,
            ]
        );

        $builder->add(
            'start',
            'submit',
            [
                'attr' => [
                    'class'   => 'btn btn-primary',
                    'icon'    => 'fa fa-upload',
                    'onclick' => "mQuery(this).prop('disabled', true); mQuery('form[name=\'lead_import\']').submit();",
                ],
                'label' => 'le.lead.import.upload',
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
        return 'lead_import';
    }
}
