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

use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class SmartFormField.
 */
class SmartFormFieldType extends AbstractType
{
    private $translator;

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->translator    = $factory->getTranslator();
        //$this->currentListId = $factory->getRequest()->attributes->get('objectId', false);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('smartfield', 'text', [
            'attr'           => ['class' => 'form-control le-input'],
            'label'          => false,
            'required'       => false,
        ]);
        $builder->add('dbfield', 'text', [
            'attr'           => ['class' => 'form-control le-input'],
            'label'          => false,
            'required'       => false,
        ]);
        $formModifier = function (FormEvent $event, $eventName) {
            $data      = $event->getData();
            $form      = $event->getForm();
            $options   = $form->getConfig()->getOptions();
            $form->add(
                'leadfield',
                'choice',
                [
                    'label'          => false,
                    'attr'           => [
                        'class' => 'form-control le-input',
                    ],
                    'error_bubbling'           => false,
                    'choices'                  => $options['leadfieldchoices'],
                    'multiple'                 => false,
                    'choice_translation_domain'=> false,
                    //'data'           => isset($data['filter']) ? $data['filter'] : '',
                ]
            );
            if ($eventName == FormEvents::PRE_SUBMIT) {
                $event->setData($data);
            }
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
                'leadfieldchoices',
            ]
        );

        $resolver->setDefaults(
            [
                'label'          => false,
                'error_bubbling' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['leadfieldchoices'] = $options['leadfieldchoices'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'smart_form_fields';
    }
}
