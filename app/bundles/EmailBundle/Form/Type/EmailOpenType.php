<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class EmailOpenType.
 */
class EmailOpenType extends AbstractType
{
    protected $factory;

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
        $iscampaign = false;
        if (isset($options['iscampaign'])) {
            $iscampaign = $options['iscampaign'];
        }

        $defaultOptions = [
            'label'      => 'le.email.open.limittoemails',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'            => 'hide form-control',
                'data-placeholder' => $this->factory->getTranslator()->trans('le.core.scoring.placeholder.email.select'),
            ],
            'enableNewForm'      => false,
          //  'multiple'           => false,
            'required'           => !$iscampaign ? false : true,
        ];

        $defaultDripOptions = [
            'label'      => 'le.email.open.limittodripemails',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'            => 'hide form-control',
                'onchange'         => 'Le.convertDripFilterInput(this.value);',
                'data-placeholder' => $this->factory->getTranslator()->trans('le.core.scoring.placeholder.campaign.select'),
            ],
            'enableNewForm'      => false,
            'multiple'           => false,
            'required'           => !$iscampaign ? false : true,
            'empty_value'        => '',
            ];
        if (isset($options['list_options'])) {
            if (isset($options['list_options']['attr'])) {
                $defaultOptions['attr'] = array_merge($defaultOptions['attr'], $options['list_options']['attr']);
                unset($options['list_options']['attr']);
            }

            $defaultOptions = array_merge($defaultOptions, $options['list_options']);
        }

        if (!$iscampaign) {
            $required    =false;
            $constraints = [];
        } else {
            $required    =true;
            $constraints = [
                new NotBlank(
                    ['message' => 'mautic.core.value.required']
                ),
            ];
        }

        $builder->add('campaigntype', 'choice', [
            'choices' => [
                'broadcast' => 'Broadcast Campaign',
                'drip'      => 'Drip Campaign',
            ],
            'empty_value' => '',
            'label'       => 'le.email.open.email.type',
            'label_attr'  => ['class' => 'control-label'],
            'attr'        => [
                'class'            => 'form-control le-input',
                'tooltip'          => !$iscampaign ? 'le.email.open.email.type.descr' : 'le.email.open.email.type.short_descr',
                'onchange'         => 'Le.getSelectedCampaignValue(this.value)',
                'data-placeholder' => !$iscampaign ? $this->factory->getTranslator()->trans('le.core.scoring.placeholder.email.select') : '',
            ],
            'required'     => $required,
            'constraints'  => $constraints,
        ]);

        $builder->add('emails', 'email_list', $defaultOptions);

        $builder->add('dripemail', 'dripemail_list', $defaultDripOptions);

        $formModifier = function (FormEvent $event, $eventName) {
            $this->buildDripFilterForm($event, $eventName);
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

    public function buildDripFilterForm(FormEvent $event, $eventName)
    {
        $data      = $event->getData();
        $form      = $event->getForm();
        $options   = $form->getConfig()->getOptions();

        $pointModel = $this->factory->getModel('point');
        $dripEmailId= '';

        if (isset($options['dripemail'])) {
            $dripEmailId = $options['dripemail'];
        }

        if (isset($data['dripemail'])) {
            $dripEmailId = $data['dripemail'];
        }

        $dripEmails =$pointModel->getRepository('MauticPointBundle:Point')->getDripEmailList($dripEmailId);

        $form->add('driplist', 'choice', [
         'label'         => 'le.email.open.limittoemails',
         'label_attr'    => ['class' => 'control-label'],
            'attr'       => [
                'class'            => 'form-control',
                'data-placeholder' => $this->factory->getTranslator()->trans('le.core.scoring.placeholder.email.select'),
            ],
         'required'    => false,
         'multiple'    => true,
         'choices'     => $dripEmails,
     ]);

        if ($eventName == FormEvents::PRE_SUBMIT) {
            $event->setData($data);
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(['list_options', 'iscampaign']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'emailopen_list';
    }
}
