<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\FormBundle\Form\Type;

use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\LeadBundle\Helper\FormFieldHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FieldType.
 */
class FieldType extends AbstractType
{
    use FormFieldTrait;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Populate settings
        $cleanMasks = [
            'labelAttributes'     => 'string',
            'inputAttributes'     => 'string',
            'containerAttributes' => 'string',
            'label'               => 'strict_html',
        ];
        $allowCustomAlias       =
        $addSaveResult          =
        $addContainerAttributes =
        $addLabelAttributes     =
        $addBehaviorFields      =false;
        $addHelpMessage         =
        $addShowLabel           =
        $addDefaultValue        =
        $addLeadFieldList       =
        $addInputAttributes     =
        $addIsRequired          = true;

        $createcolor = false;
        $style       ='';
        $tabindex    ='';
        $class       ='';

        if (!empty($options['customParameters'])) {
            $type = 'custom';

            $customParams    = $options['customParameters'];
            $formTypeOptions = [
                'required' => false,
                'label'    => false,
            ];
            if (!empty($customParams['formTypeOptions'])) {
                $formTypeOptions = array_merge($formTypeOptions, $customParams['formTypeOptions']);
            }

            $addFields = [
                'labelText',
                'addHelpMessage',
                'addShowLabel',
                'labelText',
                'addDefaultValue',
                'addLabelAttributes',
                'labelAttributesText',
                'addInputAttributes',
                'inputAttributesText',
                'addContainerAttributes',
                'containerAttributesText',
                'addLeadFieldList',
                'addSaveResult',
                'addBehaviorFields',
                'addIsRequired',
                'addHtml',
            ];
            foreach ($addFields as $f) {
                if (isset($customParams['builderOptions'][$f])) {
                    $$f = (bool) $customParams['builderOptions'][$f];
                }
            }
        } else {
            $type = $options['data']['type'];
            switch ($type) {
                case 'freetext':
                    $addHelpMessage      = $addDefaultValue      = $addIsRequired      = $addLeadFieldList      = $addSaveResult      = $addBehaviorFields      = false;
                    $labelText           = 'mautic.form.field.form.header';
                    $showLabelText       = 'mautic.form.field.form.showheader';
                    $inputAttributesText = 'mautic.form.field.form.freetext_attributes';
                    $labelAttributesText = 'mautic.form.field.form.header_attributes';

                    // Allow html
                    $cleanMasks['properties'] = 'html';
                    break;
                case 'freehtml':
                    $addHelpMessage      = $addDefaultValue      = $addIsRequired      = $addLeadFieldList      = $addSaveResult      = $addBehaviorFields      = false;
                    $labelText           = 'mautic.form.field.form.header';
                    $showLabelText       = 'mautic.form.field.form.showheader';
                    $inputAttributesText = 'mautic.form.field.form.freehtml_attributes';
                    $labelAttributesText = 'mautic.form.field.form.header_attributes';
                    // Allow html
                    $cleanMasks['properties'] = 'html';
                    break;
                case 'button':
                    $addHelpMessage = $addShowLabel = $addDefaultValue = $addLabelAttributes = $addIsRequired = $addLeadFieldList = $addSaveResult = $addBehaviorFields = false;
                    $createcolor    = true;
                    break;
                case 'hidden':
                    $addHelpMessage = $addShowLabel = $addLabelAttributes = $addIsRequired = false;
                    break;
                case 'captcha':
                    $addShowLabel = $addIsRequired = $addDefaultValue = $addLeadFieldList = $addSaveResult = $addBehaviorFields = false;
                    break;
                case 'pagebreak':
                    $addShowLabel = $allowCustomAlias = $addHelpMessage = $addIsRequired = $addDefaultValue = $addLeadFieldList = $addSaveResult = $addBehaviorFields = false;
                    break;
                case 'select':
                    $cleanMasks['properties']['list']['list']['label'] = 'strict_html';
                    $addDefaultValue                                   = false;
                    break;
                case 'checkboxgrp':
                    $addDefaultValue = false;
                    break;
                case 'radiogrp':
                    $cleanMasks['properties']['optionlist']['list']['label'] = 'strict_html';
                    $addDefaultValue                                         = false;
                    break;
                case 'file':
                    $addShowLabel = $addDefaultValue = $addLeadFieldList = $addBehaviorFields = false;
                    break;
            }
        }
        // Build form fields
        if ($options['data']['type'] == 'button') {
            $builder->add(
                'label',
                'text',
                [
                    'label'       => !empty($labelText) ? $labelText : 'mautic.form.field.form.label',
                    'label_attr'  => ['class' => 'control-label'],
                    'attr'        => ['class' => 'form-control le-input', 'onkeyup' => 'Le.updatePlaceholdervalue(this.value)'],
                    'constraints' => [
                        new Assert\NotBlank(
                            ['message' => 'mautic.form.field.label.notblank']
                        ),
                        new Assert\Length(
                            [
                                'max'        => 60,
                                'maxMessage' => 'le.form.field.button.label.length.invalid',
                            ]
                        ),
                    ],
                ]
            );
        } elseif ($options['data']['type'] == 'gcaptcha') {
            $builder->add(
                'label',
                'text',
                [
                    'label'      => !empty($labelText) ? $labelText : 'mautic.form.field.form.label',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control le-input', 'onkeyup' => 'Le.updatePlaceholdervalue(this.value)'],
                ]
            );
        } else {
            $builder->add(
                'label',
                'text',
                [
                    'label'       => !empty($labelText) ? $labelText : 'mautic.form.field.form.label',
                    'label_attr'  => ['class' => 'control-label'],
                    'attr'        => ['class' => 'form-control le-input', 'onkeyup' => 'Le.updatePlaceholdervalue(this.value)'],
                    'constraints' => [
                        new Assert\NotBlank(
                            ['message' => 'mautic.form.field.label.notblank']
                        ),
                    ],
                ]
            );
        }

        if ($allowCustomAlias) {
            $builder->add(
                'alias',
                'text',
                [
                    'label'      => 'mautic.form.field.form.alias',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'   => 'form-control le-input',
                        'tooltip' => 'mautic.form.field.form.alias.tooltip',
                    ],
                    'disabled' => (!empty($options['data']['id']) && strpos($options['data']['id'], 'new') === false) ? true : false,
                    'required' => false,
                ]
            );
        }
        if (isset($options['data']['alias']) && $options['data']['alias'] == 'gdpr') {
            $builder->add(
                'content',
                'textarea',
                [
                    'label'      => 'mautic.form.field.form.content',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'   => 'form-control le-input',
                    ],
                    'required'    => true,
                    'constraints' => [
                        new Assert\NotBlank(
                            ['message' => 'mautic.core.value.required']
                        ),
                    ],
                ]
            );
        }

        if ($addShowLabel) {
            $default = (!isset($options['data']['showLabel'])) ? false : (bool) $options['data']['showLabel'];
            $builder->add(
                'showLabel',
                'yesno_button_group',
                [
                    'label' => (!empty($showLabelText)) ? $showLabelText : 'mautic.form.field.form.showlabel',
                    'data'  => $default,
                ]
            );
        }

        if ($addDefaultValue) {
            $builder->add(
                'defaultValue',
                ($type == 'textarea') ? 'textarea' : 'text',
                [
                    'label'      => 'mautic.core.defaultvalue',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control le-input'],
                    'required'   => false,
                ]
            );
        }

        if ($addHelpMessage) {
            $builder->add(
                'helpMessage',
                'text',
                [
                    'label'      => 'mautic.form.field.form.helpmessage',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'   => 'form-control le-input',
                        'tooltip' => 'mautic.form.field.help.helpmessage',
                    ],
                    'required' => false,
                ]
            );
        }

        if ($addIsRequired) {
            $default = (!isset($options['data']['isRequired'])) ? false : (bool) $options['data']['isRequired'];
            $builder->add(
                'isRequired',
                'yesno_button_group',
                [
                    'label' => 'mautic.core.required',
                    'data'  => $default,
                ]
            );

            $builder->add(
                'validationMessage',
                'text',
                [
                    'label'      => 'mautic.form.field.form.validationmsg',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control le-input'],
                    'required'   => false,
                ]
            );
        }

        if ($addLabelAttributes) {
            $builder->add(
                'labelAttributes',
                'text',
                [
                    'label'      => (!empty($labelAttributesText)) ? $labelAttributesText : 'mautic.form.field.form.labelattr',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'     => 'form-control le-input',
                        'tooltip'   => 'mautic.form.field.help.attr',
                        'maxlength' => '255',
                    ],
                    'required' => false,
                ]
            );
        }

        if ($createcolor) {
            $style    = 'pointer-events: none;background-color: #ebedf0;opacity: 1;';
            $tabindex = '-1';
            $class    = 'hide';
        }
        if ($addInputAttributes) {
            $builder->add(
                'inputAttributes',
                'text',
                [
                    'label'      => (!empty($inputAttributesText)) ? $inputAttributesText : 'mautic.form.field.form.inputattr',
                    'label_attr' => ['class' => 'control-label '.$class],
                    'attr'       => [
                        'class'     => 'form-control le-input '.$class,
                        'tooltip'   => 'mautic.form.field.help.attr',
                        'maxlength' => '255',
                        'style'     => $style,
                        'tabindex'  => $tabindex,
                    ],
                    'required' => false,
                ]
            );
            if ($createcolor) {
                $builder->add(
                    'btnbgcolor',
                    'text',
                    [
                        'label'      => 'mautic.form.field.form.btn_bg_color',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => [
                            'class'       => 'form-control minicolors-input le-input',
                            'data-toggle' => 'color',
                            'onchange'    => 'Le.setBtnBackgroundColor()',
                        ],
                        'required' => false,
                    ]
                );

                $builder->add(
                    'btntxtcolor',
                    'text',
                    [
                        'label'      => 'mautic.form.field.form.btn_txt_color',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => [
                            'class'       => 'form-control minicolors-input le-input',
                            'data-toggle' => 'color',
                            'onchange'    => 'Le.setBtnTextColor()',
                        ],
                        'required' => false,
                    ]
                );
            }
        }

        if ($addContainerAttributes) {
            $builder->add(
                'containerAttributes',
                'text',
                [
                    'label'      => (!empty($containerAttributesText)) ? $containerAttributesText : 'mautic.form.field.form.container_attr',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'     => 'form-control le-input',
                        'tooltip'   => 'mautic.form.field.help.container_attr',
                        'maxlength' => '255',
                    ],
                    'required' => false,
                ]
            );
        }

        if ($addSaveResult) {
            $default = (!isset($options['data']['saveResult']) || $options['data']['saveResult'] === null) ? true
                : (bool) $options['data']['saveResult'];
            $builder->add(
                'saveResult',
                'yesno_button_group',
                [
                    'label' => 'mautic.form.field.form.saveresult',
                    'data'  => $default,
                    'attr'  => [
                        'tooltip' => 'mautic.form.field.help.saveresult',
                    ],
                ]
            );
        }

        if ($addBehaviorFields) {
            $default = (!isset($options['data']['showWhenValueExists']) || $options['data']['showWhenValueExists'] === null) ? true
                : (bool) $options['data']['showWhenValueExists'];
            $builder->add(
                'showWhenValueExists',
                'yesno_button_group',
                [
                    'label' => 'mautic.form.field.form.show.when.value.exists',
                    'data'  => $default,
                    'attr'  => [
                        'tooltip' => 'mautic.form.field.help.show.when.value.exists',
                    ],
                ]
            );

            $builder->add(
                'showAfterXSubmissions',
                'text',
                [
                    'label'      => 'mautic.form.field.form.show.after.x.submissions',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'   => 'form-control le-input',
                        'tooltip' => 'mautic.form.field.help.show.after.x.submissions',
                    ],
                    'required' => false,
                ]
            );

            $isAutoFillValue = (!isset($options['data']['isAutoFill'])) ? false : (bool) $options['data']['isAutoFill'];
            $builder->add(
                'isAutoFill',
                'yesno_button_group',
                [
                    'label' => 'mautic.form.field.form.auto_fill',
                    'data'  => $isAutoFillValue,
                    'attr'  => [
                        'class'   => 'auto-fill-data',
                        'tooltip' => 'mautic.form.field.help.auto_fill',
                    ],
                ]
            );
        }

        if ($addLeadFieldList) {
            if (!isset($options['data']['leadField'])) {
                switch ($type) {
                    case 'email':
                        $data = 'email';
                        break;
                    case 'country':
                        $data = 'country';
                        break;
                    case 'tel':
                        $data = 'phone';
                        break;
                    default:
                        $data = '';
                        break;
                }
            } elseif (isset($options['data']['leadField'])) {
                $data = $options['data']['leadField'];
            } else {
                $data = '';
            }

            $builder->add(
                'leadField',
                'choice',
                [
                    'choices'     => $options['leadFields'],
                    'choice_attr' => function ($val, $key, $index) use ($options) {
                        $objects = ['lead', 'company'];
                        foreach ($objects as $object) {
                            if (!empty($options['leadFieldProperties'][$object][$val]) && (in_array($options['leadFieldProperties'][$object][$val]['type'], FormFieldHelper::getListTypes()) || !empty($options['leadFieldProperties'][$object][$val]['properties']['list']) || !empty($options['leadFieldProperties'][$object][$val]['properties']['optionlist']))) {
                                return ['data-list-type' => 1];
                            }
                        }

                        return [];
                    },
                    'label'      => 'mautic.form.field.form.lead_field',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'   => 'form-control le-input',
                        'tooltip' => 'mautic.form.field.help.lead_field',
                    ],
                    'required' => false,
                    'data'     => $data,
                ]
            );
        }

        $builder->add('type', 'hidden');

        $update = (!empty($options['data']['id'])) ? true : false;
        if (!empty($update)) {
            $btnValue = 'mautic.core.form.update';
            $btnIcon  = 'fa fa-pencil';
        } else {
            $btnValue = 'mautic.core.form.add';
            $btnIcon  = 'fa fa-plus';
        }

        $builder->add(
            'buttons',
            'form_buttons',
            [
                'save_text'       => $btnValue,
                'save_icon'       => $btnIcon,
                'apply_text'      => false,
                'save_icon'       => false,
                'container_class' => 'bottom-form-buttons',
            ]
        );

        $builder->add(
            'formId',
            'hidden',
            [
                'mapped' => false,
            ]
        );

        // Put properties last so that the other values are available to form events
        $propertiesData = (isset($options['data']['properties'])) ? $options['data']['properties'] : [];
        if (!empty($options['customParameters'])) {
            $formTypeOptions = array_merge($formTypeOptions, ['data' => $propertiesData]);
            $builder->add('properties', $customParams['formType'], $formTypeOptions);
        } else {
            switch ($type) {
                case 'select':
                case 'country':
                    $builder->add(
                        'properties',
                        'formfield_select',
                        [
                            'field_type' => $type,
                            'label'      => false,
                            'parentData' => $options['data'],
                            'data'       => $propertiesData,
                        ]
                    );
                    break;
                case 'checkboxgrp':
                case 'radiogrp':
                    $builder->add(
                        'properties',
                        'formfield_group',
                        [
                            'label' => false,
                            'data'  => $propertiesData,
                        ]
                    );
                    break;
                case 'freetext':
                    $builder->add(
                        'properties',
                        'formfield_text',
                        [
                            'required' => false,
                            'label'    => false,
                            'editor'   => true,
                            'data'     => $propertiesData,
                        ]
                    );
                    break;
                case 'freehtml':
                    $builder->add(
                        'properties',
                        'formfield_html',
                        [
                            'required' => false,
                            'label'    => false,
                            'editor'   => true,
                            'data'     => $propertiesData,
                        ]
                    );
                    break;
                case 'date':
                case 'email':
                case 'number':
                case 'tel':
                case 'text':
                case 'url':
                    $builder->add(
                        'properties',
                        'formfield_placeholder',
                        [
                            'label' => false,
                            'data'  => $propertiesData,
                        ]
                    );
                    break;
                case 'captcha':
                    $builder->add(
                        'properties',
                        'formfield_captcha',
                        [
                            'label' => false,
                            'data'  => $propertiesData,
                        ]
                    );
                    break;
                case 'pagebreak':
                    $builder->add(
                        'properties',
                        FormFieldPageBreakType::class,
                        [
                            'label' => false,
                            'data'  => $propertiesData,
                        ]
                    );
                    break;
                case 'file':
                    $builder->add(
                        'properties',
                        FormFieldFileType::class,
                        [
                            'label' => false,
                            'data'  => $propertiesData,
                        ]
                    );
            }
        }

        $builder->addEventSubscriber(new CleanFormSubscriber($cleanMasks));

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'customParameters' => false,
            ]
        );

        $resolver->setOptional(['customParameters', 'leadFieldProperties']);

        $resolver->setRequired(['leadFields']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'formfield';
    }
}
