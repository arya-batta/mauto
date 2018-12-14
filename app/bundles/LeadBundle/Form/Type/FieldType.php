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

use Doctrine\ORM\EntityRepository;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Mautic\LeadBundle\Form\DataTransformer\FieldToOrderTransformer;
use Mautic\LeadBundle\Helper\FormFieldHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class FieldType.
 */
class FieldType extends AbstractType
{
    private $translator;
    private $em;

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->translator = $factory->getTranslator();
        $this->em         = $factory->getEntityManager();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber());
        $builder->addEventSubscriber(new FormExitSubscriber('lead.field', $options));

        $builder->add(
            'label',
            'text',
            [
                'label'      => 'le.lead.field.label',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control le-input', 'length' => 50],
            ]
        );

        $disabled = (!empty($options['data'])) ? $options['data']->isFixed() : false;

        $builder->add(
            'group',
            'choice',
            [
                'choices' => [
                    'core'         => 'le.lead.field.group.core',
                    'social'       => 'le.lead.field.group.social',
                    'personal'     => 'le.lead.field.group.personal',
                    'professional' => 'le.lead.field.group.professional',
                ],
                'attr' => [
                    'class'   => 'form-control le-input',
                    'tooltip' => 'le.lead.field.form.group.help',
                ],
                'expanded'    => false,
                'multiple'    => false,
                'label'       => 'le.lead.field.group',
                'empty_value' => false,
                'required'    => false,
                'disabled'    => $disabled,
            ]
        );

        $new         = (!empty($options['data']) && $options['data']->getId()) ? false : true;
        $type        = $options['data']->getType();
        $default     = (empty($type)) ? 'text' : $type;
        $fieldHelper = new FormFieldHelper();
        $fieldHelper->setTranslator($this->translator);
        $builder->add(
            'type',
            'choice',
            [
                'choices'     => $fieldHelper->getChoiceList(),
                'expanded'    => false,
                'multiple'    => false,
                'label'       => 'le.lead.field.type',
                'empty_value' => false,
                'disabled'    => ($disabled || !$new),
                'attr'        => [
                    'class'    => 'form-control le-input',
                    'onchange' => 'Le.updateLeadFieldProperties(this.value);',
                ],
                'data'     => $default,
                'required' => false,
            ]
        );

        $builder->add(
            'properties_select_template',
            'sortablelist',
            [
                'mapped'          => false,
                'label'           => 'le.lead.field.form.properties.select',
                'option_required' => false,
                'with_labels'     => true,
            ]
        );

        $builder->add(
            'properties_lookup_template',
            'sortablelist',
            [
                'mapped'          => false,
                'label'           => 'le.lead.field.form.properties.select',
                'option_required' => false,
                'with_labels'     => false,
            ]
        );

        $listChoices = [
            'country'  => FormFieldHelper::getCountryChoices(),
            'region'   => FormFieldHelper::getRegionChoices(),
            'timezone' => FormFieldHelper::getCustomTimezones(),
            'locale'   => FormFieldHelper::getLocaleChoices(),
            'select'   => [],
        ];

        foreach ($listChoices as $listType => $choices) {
            $builder->add(
                'default_template_'.$listType,
                'choice',
                [
                    'choices'    => $choices,
                    'label'      => 'mautic.core.defaultvalue',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control not-chosen'],
                    'required'   => false,
                    'mapped'     => false,
                ]
            );
        }

        $builder->add(
            'default_template_text',
            'text',
            [
                'label'      => 'mautic.core.defaultvalue',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
                'mapped'     => false,
            ]
        );

        $builder->add(
            'default_template_textarea',
            'textarea',
            [
                'label'      => 'mautic.core.defaultvalue',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
                'mapped'     => false,
            ]
        );

        $builder->add(
            'default_template_boolean',
            'yesno_button_group',
            [
                'label'       => 'mautic.core.defaultvalue',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'required'    => false,
                'mapped'      => false,
                'data'        => '',
                'empty_value' => ' x ',
            ]
        );

        $builder->add(
            'properties',
            'collection',
            [
                'required'       => false,
                'allow_add'      => true,
                'error_bubbling' => false,
            ]
        );

        $builder->add(
            'defaultValue',
            'text',
            [
                'label'      => 'mautic.core.defaultvalue',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control le-input',
                    'tooltip' => 'le.lead.field.help.defaultvalue',
                ],
                'required' => false,
            ]
        );

        $formModifier = function (FormEvent $event) use ($listChoices) {
            $form = $event->getForm();
            $data = $event->getData();
            $type = (is_array($data)) ? (isset($data['type']) ? $data['type'] : null) : $data->getType();

            switch ($type) {
                case 'multiselect':
                case 'select':
                case 'lookup':
                    if (is_array($data)) {
                        $properties = isset($data['properties']) ? $data['properties'] : [];
                    } else {
                        $properties = $data->getProperties();
                    }

                    $form->add(
                        'properties',
                        'sortablelist',
                        [
                            'required'    => false,
                            'label'       => 'le.lead.field.form.properties.select',
                            'data'        => $properties,
                            'with_labels' => ('lookup' !== $type),
                        ]
                    );

                    $list = isset($properties['list']) ? FormFieldHelper::parseList($properties['list']) : [];
                    $form->add(
                        'defaultValue',
                        'choice',
                        [
                            'label'      => 'mautic.core.defaultvalue',
                            'label_attr' => ['class' => 'control-label is-chosen'],
                            'attr'       => ['class' => 'form-control le-input'],
                            'required'   => false,
                            'choices'    => $list,
                        ]
                    );
                    break;
                case 'country':
                case 'locale':
                case 'timezone':
                case 'region':
                    $form->add(
                        'defaultValue',
                        'choice',
                        [
                            'choices'    => $listChoices[$type],
                            'label'      => 'mautic.core.defaultvalue',
                            'label_attr' => ['class' => 'control-label'],
                            'attr'       => ['class' => 'form-control le-input'],
                            'required'   => false,
                        ]
                    );
                    break;
                case 'boolean':
                    if (is_array($data)) {
                        $value    = isset($data['defaultValue']) ? $data['defaultValue'] : false;
                        $yesLabel = !empty($data['properties']['yes']) ? $data['properties']['yes'] : 'mautic.core.form.yes';
                        $noLabel  = !empty($data['properties']['no']) ? $data['properties']['no'] : 'mautic.core.form.no';
                    } else {
                        $value    = $data->getDefaultValue();
                        $props    = $data->getProperties();
                        $yesLabel = !empty($props['yes']) ? $props['yes'] : 'mautic.core.form.yes';
                        $noLabel  = !empty($props['no']) ? $props['no'] : 'mautic.core.form.no';
                    }

                    if ($value !== '' && $value !== null) {
                        $value = (int) $value;
                    }

                    $form->add(
                        'defaultValue',
                        'yesno_button_group',
                        [
                            'label'       => 'mautic.core.defaultvalue',
                            'label_attr'  => ['class' => 'control-label'],
                            'attr'        => ['class' => 'form-control'],
                            'required'    => false,
                            'data'        => $value,
                            'no_label'    => $noLabel,
                            'yes_label'   => $yesLabel,
                            'empty_value' => ' x ',
                        ]
                    );
                    break;
                case 'datetime':
                case 'date':
                case 'time':
                    $constraints = [];
                    switch ($type) {
                        case 'datetime':
                            $constraints = [
                                new Assert\Callback(
                                    function ($object, ExecutionContextInterface $context) {
                                        if (!empty($object) && \DateTime::createFromFormat('Y-m-d H:i', $object) === false) {
                                            $context->buildViolation('le.lead.datetime.invalid')->addViolation();
                                        }
                                    }
                                ),
                            ];
                            break;
                        case 'date':
                            $constraints = [
                                new Assert\Callback(
                                    function ($object, ExecutionContextInterface $context) {
                                        if (!empty($object)) {
                                            $validator = $context->getValidator();
                                            $violations = $validator->validateValue($object, new Assert\Date());

                                            if (count($violations) > 0) {
                                                $context->buildViolation('le.lead.date.invalid')->addViolation();
                                            }
                                        }
                                    }
                                ),
                            ];
                            break;
                        case 'time':
                            $constraints = [
                                new Assert\Callback(
                                    function ($object, ExecutionContextInterface $context) {
                                        if (!empty($object)) {
                                            $validator = $context->getValidator();
                                            $violations = $validator->validateValue(
                                                $object,
                                                new Assert\Regex(['pattern' => '/(2[0-3]|[01][0-9]):([0-5][0-9])/'])
                                            );

                                            if (count($violations) > 0) {
                                                $context->buildViolation('le.lead.time.invalid')->addViolation();
                                            }
                                        }
                                    }
                                ),
                            ];
                            break;
                    }

                    $form->add(
                        'defaultValue',
                        'text',
                        [
                            'label'      => 'mautic.core.defaultvalue',
                            'label_attr' => ['class' => 'control-label'],
                            'attr'       => [
                                'class'       => 'form-control le-input',
                                'data-toggle' => $type,
                            ],
                            'required'    => false,
                            'constraints' => $constraints,
                        ]
                    );
                    break;
                case 'number':
                case 'tel':
                case 'url':
                case 'email':
                    $form->add(
                        'defaultValue',
                        'text',
                        [
                            'label'      => 'mautic.core.defaultvalue',
                            'label_attr' => ['class' => 'control-label'],
                            'attr'       => [
                                'class' => 'form-control le-input',
                                'type'  => $type,
                            ],
                            'required' => false,
                        ]
                    );

                break;
            }
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $formModifier($event);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $formModifier($event);
            }
        );

        //get order list
        $transformer = new FieldToOrderTransformer($this->em);
        $builder->add(
            $builder->create(
                'order',
                'entity',
                [
                    'label'         => 'mautic.core.order',
                    'class'         => 'MauticLeadBundle:LeadField',
                    'property'      => 'label',
                    'label_attr'    => ['class' => 'control-label'],
                    'attr'          => ['class' => 'form-control le-input'],
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('f')
                            ->orWhere('f.createdBy != :nullcreatedby OR f.isPublished = :isPublished')
                            ->orderBy('f.order', 'ASC')
                            ->setParameter('isPublished', 1)
                            ->setParameter('nullcreatedby', 'isNotNull');
                    },
                    'required' => false,
                ]
            )->addModelTransformer($transformer)
        );

        $builder->add(
            'alias',
            'text',
            [
                'label'      => 'mautic.core.alias',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control le-input',
                    'length'  => 25,
                    'tooltip' => 'le.lead.field.help.alias',
                ],
                'required' => false,
                'disabled' => ($disabled || !$new),
            ]
        );

        $builder->add(
            'isPublished',
            'yesno_button_group',
            [
                'disabled' => ($options['data']->getIsFixed() == 1),
                'data'     => ($options['data']->getIsFixed() == 1) ? true : $options['data']->getIsPublished(),
            ]
        );

        $builder->add(
            'isRequired',
            'yesno_button_group',
            [
                'label' => 'mautic.core.required',
            ]
        );

        $builder->add(
            'isVisible',
            'yesno_button_group',
            [
                'label' => 'le.lead.field.form.isvisible',
            ]
        );

        $builder->add(
            'isShortVisible',
            'yesno_button_group',
            [
                'label' => 'le.lead.field.form.isshortvisible',
                'attr'  => [
                    'tooltip' => 'le.lead.field.form.isshortvisible.tooltip',
                ],
            ]
        );

        $builder->add(
            'isListable',
            'yesno_button_group',
            [
                'label' => 'le.lead.field.form.islistable',
            ]
        );

        $data = $options['data']->isUniqueIdentifier();
        $builder->add(
            'isUniqueIdentifer',
            'yesno_button_group',
            [
                'label' => 'le.lead.field.form.isuniqueidentifer',
                'attr'  => [
                    'tooltip'  => 'le.lead.field.form.isuniqueidentifer.tooltip',
                    'onchange' => 'Le.displayUniqueIdentifierWarning(this)',
                ],
                'disabled' => ($options['data']->getAlias() == 'email'),
                'data'     => (!empty($data)),
            ]
        );

        $builder->add(
            'isPubliclyUpdatable',
            'yesno_button_group',
            [
                'label' => 'le.lead.field.form.ispubliclyupdatable',
                'attr'  => [
                    'tooltip' => 'le.lead.field.form.ispubliclyupdatable.tooltip',
                ],
            ]
        );

        $builder->add(
            'object',
            'choice',
            [
                'choices' => [
                    'le.lead.contact'    => 'lead',
                    'le.company.company' => 'company',
                ],
                'choices_as_values' => true,
                'expanded'          => false,
                'multiple'          => false,
                'label'             => 'le.lead.field.object',
                'empty_value'       => false,
                'attr'              => [
                    'class' => 'form-control le-input',
                ],
                'required' => false,
                'disabled' => ($disabled || !$new),
            ]
        );

        $builder->add('buttons', 'form_buttons');

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Mautic\LeadBundle\Entity\LeadField',
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'leadfield';
    }
}
