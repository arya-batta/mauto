<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Form\Type;

use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Mautic\LeadBundle\Form\Validator\Constraints\EmailContentVerifier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ListOptInType.
 */
class ListOptInType extends AbstractType
{
    private $translator;

    /**
     * ListType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['footerText' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('lead.listoptin', $options));

        $listtype            = [
            'single'        => 'le.lead.list.optin.single.optin',
            'double'        => 'le.lead.list.optin.double.optin',
        ];

        $builder->add(
            'name',
            'text',
            [
                'label'      => 'mautic.core.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control le-input'],
            ]
        );

        $builder->add(
            'description',
            'textarea',
            [
                'label'      => 'mautic.core.description',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control editor'],
                'required'   => false,
            ]
        );

        $builder->add('isPublished', 'yesno_button_group');

        $builder->add(
            'listtype',
            'choice',
            [
                'choices'     => $listtype,
                'multiple'    => false,
                'label'       => 'le.lead.list.optin.list.type',
                'label_attr'  => ['class' => 'control-label'],
                'empty_value' => false,
                'required'    => false,
                'attr'        => [
                    'class'    => 'form-control le-input',
                    'onchange' => 'Le.showDoubleOptInList(this)',
                ],
            ]
        );

        $builder->add('thankyou', 'yesno_button_group', [
            'label' => 'le.lead.list.optin.thankyou.mail',
            'attr'  => [
                'onchange' => 'Le.toggleThankYouEmailListVisibility();',
            ],
        ]);

        $builder->add('goodbye', 'yesno_button_group', [
            'label' => 'le.lead.list.optin.goodbye.mail',
            'attr'  => [
                'onchange' => 'Le.toggleGoodbyeEmailListVisibility();',
            ],
        ]);

        $builder->add('doubleoptinemail', 'email_list', [
            'label'      => 'le.lead.list.optin.doubleoptin.email',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'    => 'form-control le-input',
                'tooltip'  => 'le.lead.list.optin.mails.tooltip',
            ],
            'email_type'  => 'template',
            'multiple'    => false,
            'required'    => true,
            'constraints' => [
                new EmailContentVerifier(
                    [
                        'message' => 'le.lead.list.optin.token.missing',
                    ]
                ),
            ],
        ]);

        $builder->add('thankyouemail', 'email_list', [
            'label'      => 'le.lead.list.optin.thanyou.email',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'    => 'form-control le-input',
                'tooltip'  => 'le.lead.list.optin.mails.tooltip',
            ],
            'email_type'  => 'template',
            'multiple'    => false,
            'required'    => true,
        ]);

        $builder->add('goodbyeemail', 'email_list', [
            'label'      => 'le.lead.list.optin.goodbye.email',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'    => 'form-control',
                'tooltip'  => 'le.lead.list.optin.mails.tooltip',
            ],
            'email_type'  => 'template',
            'multiple'    => false,
            'required'    => true,
        ]);

        $builder->add(
            $builder->create(
                'footerText',
                'textarea',
                [
                    'label'      => 'le.email.footer.content',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'                => 'form-control editor editor-advanced editor-builder-tokens',
                        'tooltip'              => 'le.lead.list.optin.footer_text.tooltip',
                        'data-token-callback'  => 'email:getBuilderTokens',
                        'data-token-activator' => '{',
                    ],
                    'required' => false,
                ]
            )
        );

        $builder->add('buttons', 'form_buttons',
            [
                'apply_icon'   => false,
                'save_icon'    => false,
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Mautic\LeadBundle\Entity\LeadListOptIn',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'leadlistoptin';
    }
}
