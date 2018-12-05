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

use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class LeadImportFieldType.
 */
class LeadImportFieldType extends AbstractType
{
    private $factory;

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $propertieFields = [
            'dateAdded'      => 'le.lead.import.label.dateAdded',
            'createdByUser'  => 'le.lead.import.label.createdByUser',
            'dateModified'   => 'le.lead.import.label.dateModified',
            'modifiedByUser' => 'le.lead.import.label.modifiedByUser',
            'lastActive'     => 'le.lead.import.label.lastActive',
            'dateIdentified' => 'le.lead.import.label.dateIdentified',
            'ip'             => 'le.lead.import.label.ip',
            'points'         => 'le.lead.import.label.points',
            'stage'          => 'le.lead.import.label.stage',
            'ownerusername'  => 'le.lead.import.label.ownerusername',
        ];

        if ($this->factory->getUser()->isAdmin()) {
            $specialFields =array_merge($propertieFields, ['doNotEmail'=> 'le.lead.import.label.doNotEmail']);
        } else {
            $specialFields =['doNotEmail'=> 'le.lead.import.label.doNotEmail'];
        }

        $importChoiceFields = [
            'le.lead.contact'        => $options['lead_fields'],
            'le.lead.company'        => $options['company_fields'],
            'le.lead.special_fields' => $specialFields,
        ];

        if ($options['object'] !== 'lead') {
            unset($importChoiceFields['le.lead.contact']);
        }

        foreach ($options['import_fields'] as $field => $label) {
            $builder->add(
                $field,
                'choice',
                [
                    'choices'    => $importChoiceFields,
                    'label'      => $label,
                    'required'   => false,
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control'],
                    'data'       => $this->getDefaultValue($field, $options['import_fields']),
                ]
            );
        }

        $transformer = new \Mautic\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer(
            $this->factory->getEntityManager(),
            'MauticUserBundle:User'
        );

        $builder->add(
            $builder->create(
                'owner',
                'user_list',
                [
                    'label'      => 'le.lead.lead.field.owner',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class' => 'form-control',
                    ],
                    'required' => false,
                    'multiple' => false,
                ]
            )
        );

        $constraints = [
            new \Symfony\Component\Validator\Constraints\NotBlank(),
        ];

        if ($options['object'] === 'lead') {
            $builder->add(
                $builder->create(
                    'list',
                    'leadlist_choices',
                    [
                        'label'      => 'le.lead.lead.field.list',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => [
                            'class' => 'form-control',
                        ],

                        'required'    => false,
                        'multiple'    => false,
                    ]
                )
            );

            $builder->add(
                'tags',
                'lead_tag',
                [
                    'label'      => 'le.lead.tags',
                    'required'   => false,
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'                => 'form-control',
                        'data-placeholder'     => $this->factory->getTranslator()->trans('le.lead.tags.select_or_create'),
                        'data-no-results-text' => $this->factory->getTranslator()->trans('le.lead.tags.enter_to_create'),
                        'data-allow-add'       => 'true',
                        'onchange'             => 'Le.createLeadTag(this)',
                    ],
                ]
            );
        }

        $buttons = ['cancel_icon' => 'fa fa-times', 'cancel_class' => ' btn  btn-cancel le-btn-default btn-copy'];

        if (empty($options['line_count_limit'])) {
            $buttons = array_merge(
                $buttons,
                [
                    'apply_text'  => 'le.lead.import.in.background',
                    'apply_class' => 'btn le-btn-default ',
                    'apply_icon'  => 'fa fa-history',
                    'save_text'   => 'le.lead.import.start',
                    'save_class'  => 'btn le-btn-default btn-copy import_btn_align',
                    'save_icon'   => 'fa fa-upload',
                ]
            );
        } else {
            $buttons = array_merge(
                $buttons,
                [
                    'apply_text' => false,
                    'save_text'  => 'le.lead.import',
                    'save_class' => 'btn le-btn-default btn-copy',
                    'save_icon'  => 'fa fa-upload',
                ]
            );
        }

        $builder->add('buttons', 'form_buttons', $buttons);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['lead_fields', 'import_fields', 'company_fields', 'object']);
        $resolver->setDefaults(['line_count_limit' => 0]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'lead_field_import';
    }

    /**
     * @param string $fieldName
     * @param array  $importFields
     *
     * @return string
     */
    public function getDefaultValue($fieldName, array $importFields)
    {
        if (isset($importFields[$fieldName])) {
            return $importFields[$fieldName];
        }

        return null;
    }
}
