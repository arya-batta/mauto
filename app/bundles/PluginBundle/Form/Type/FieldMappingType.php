<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\LeadBundle\Helper\FormFieldHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class FieldMappingType.
 */
class FieldMappingType extends AbstractType
{
    protected $factory;
    private $translator;

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->translator    = $factory->getTranslator();
        $this->factory       =$factory;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'localfield',
            'choice',
            [
                'label'   => false,
                'choices' => $this->getLeadFieldChoices($options),
                'attr'    => [
                    'class'    => 'form-control chosen local-mapping-fields',
                ],
            ]
        );

        $builder->add(
            'remotefield',
            'choice',
            [
                'label'   => false,
                'choices' => $this->getRemoteFieldChoices($options['integration']),
                'attr'    => [
                    'class'    => 'form-control chosen',
                ],
                'multiple'=> true,
            ]
        );

        $formModifier = function (FormEvent $event, $eventName) {
            $this->buildMappingForm($event, $eventName);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $formModifier($event, FormEvents::PRE_SET_DATA);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $formModifier($event, FormEvents::PRE_SUBMIT);
            }
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(
            [
                'fields',
                'propertychoices',
                'integration',
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'lead_field_mapping';
    }

    public function getLeadFieldChoices($options)
    {
        $choices=[];
        if (isset($options['fields'])) {
            $fields=$options['fields'];
            foreach ($fields as $key => $details) {
                $choices[$key]= $details['label'];
            }
        }

        return $choices;
    }

    public function getRemoteFieldChoices($integration)
    {
        $integrationHelper    = $this->factory->getHelper('integration');
        $integrationEntity    =$integrationHelper->getIntegrationInfobyName($integration);
        $choices              =[];
        if ($integrationEntity) {
            $fieldmappings=$integrationEntity->getFieldMapping();
            foreach ($fieldmappings as $fieldmapping) {
                $groupname =$fieldmapping->getGroup();
                $fields    =$fieldmapping->getFields();
                $subchoices=[];
                foreach ($fields as $field) {
                    $subchoices[$groupname.'@$@'.$field]= $field;
                }
                $choices[$groupname]=$subchoices;
            }
        }

        return $choices;
    }

    public function buildMappingForm(FormEvent $event, $eventName)
    {
        $data      = $event->getData();
        $form      = $event->getForm();
        $options   = $form->getConfig()->getOptions();
        $fieldName = '';
        $fieldType ='';
        if (isset($data['localfield'])) {
            $fieldName=$data['localfield'];
        }
        $field = [];
        if (isset($options['fields'][$fieldName])) {
            $field    = $options['fields'][$fieldName];
            $fieldType=$field['type'];
        }
        $customOptions = [];
        $attr          = [
            'class' => 'form-control le-input',
        ];
        $type = 'text';
        if (!isset($data['defaultvalue'])) {
            $data['defaultvalue']='';
        }
        switch ($fieldType) {
            case 'leadlist':
//                    if (!isset($data['defaultvalue'])) {
//                        $data['defaultvalue'] = [];
//                    } elseif (!is_array($data['defaultvalue'])) {
//                        $data['defaultvalue'] = [$data['defaultvalue']];
//                    }
                $customOptions['choices']                   = isset($options['propertychoices']['leadlist']) ? $options['propertychoices']['leadlist'] : [];
                $customOptions['empty_value']               = 'mautic.core.form.chooseone';
                $type                                       = 'choice';
                break;
            case 'listoptin':
//                    if (!isset($data['defaultvalue'])) {
//                        $data['defaultvalue'] = [];
//                    } elseif (!is_array($data['defaultvalue'])) {
//                       $data['defaultvalue'] = [$data['defaultvalue']];
//                    }
                $customOptions['choices']                   = isset($options['propertychoices']['listoptin']) ? $options['propertychoices']['listoptin'] : [];
                $customOptions['empty_value']               = 'mautic.core.form.chooseone';
                $type                                       = 'choice';
                break;
            case 'owner_id':
//                   if (!isset($data['defaultvalue'])) {
//                        $data['defaultvalue'] = [];
//                    } elseif (!is_array($data['defaultvalue'])) {
//                        $data['defaultvalue'] = [$data['defaultvalue']];
//                    }

                $customOptions['choices']                   = isset($options['propertychoices']['owner_id']) ? $options['propertychoices']['owner_id'] : [];
                $type                                       = 'choice';
                break;
            case 'tags':
//                   if (!isset($data['defaultvalue'])) {
//                        $data['defaultvalue'] = [];
//                    } elseif (!is_array($data['defaultvalue'])) {
//                        $data['defaultvalue'] = [$data['defaultvalue']];
//                    }
                $customOptions['choices']                   = isset($options['propertychoices']['tags']) ? $options['propertychoices']['tags'] : [];
                $customOptions['empty_value']               = 'mautic.core.form.chooseone';
                $type                                       = 'choice';
                break;
            case 'time':
            case 'date':
            case 'datetime':
                $attr['data-toggle'] = $fieldType;
                break;
            case 'select':
            case 'multiselect':
            case 'boolean':
                $type = 'choice';
//                if (!isset($data['defaultvalue'])) {
//                    $data['defaultvalue'] = [];
//                } elseif (!is_array($data['defaultvalue'])) {
//                    $data['defaultvalue'] = [$data['defaultvalue']];
//                }
                $choices = [];
                if ($field['alias'] == 'eu_gdpr_consent') {
                    $choices = $options['propertychoices']['eu_gdpr_consent'];
                } else {
                    if (!empty($field['properties'])) {
                        $list    = $field['properties'];
                        $choices = FormFieldHelper::parseList($list, true, ('boolean' === $fieldType));
                    }
                    if ('select' == $fieldType) {
                        // array_unshift cannot be used because numeric values get lost as keys
                        $choices     = array_reverse($choices, true);
                        $choices[''] = '';
                        $choices     = array_reverse($choices, true);
                    }
                }
                $customOptions['choices']= $choices;
                break;
        }
        $form->add(
            'defaultvalue',
            $type,
            array_merge(
                [
                    'label'          => false,
                    'attr'           => $attr,
                    'data'           => $data['defaultvalue'],
                    'error_bubbling' => false,
                ],
                $customOptions
            )
        );
        if ($eventName == FormEvents::PRE_SUBMIT) {
            $event->setData($data);
        }
    }
}
