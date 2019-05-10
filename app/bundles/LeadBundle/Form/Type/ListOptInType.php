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

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Mautic\EmailBundle\Form\Validator\Constraints\EmailVerify;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Class ListOptInType.
 */
class ListOptInType extends AbstractType
{
    private $translator;

    /**
     * @var MauticFactory
     */
    private $factory;

    /**
     * ListType constructor.
     *
     * @param TranslatorInterface $translator
     * @param MauticFactory       $factory
     */
    public function __construct(TranslatorInterface $translator, MauticFactory $factory)
    {
        $this->translator = $translator;
        $this->factory    = $factory;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['footerText' => 'html', 'message' => 'html']));
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

        $builder->add('isPublished', 'yesno_button_group', [
            'no_label'   => 'mautic.core.form.unpublished',
            'yes_label'  => 'mautic.core.form.published',
            ]);

        $builder->add(
            'listtype',
            'yesno_button_group',
            [
                'label'      => 'le.lead.list.optin.list.type',
                'no_label'   => 'le.list.single.optin.type',
                'yes_label'  => 'le.list.double.optin.type',
                'attr'       => [
                    'onchange' => 'Le.toggleDoubleOptinFieldVisibility();',
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

        $builder->add(
            $builder->create(
                'subject',
                'text',
                [
                    'label'      => 'le.lead.list.optin.subject.name',
                    'label_attr' => ['class' => 'control-label required'],
                    'attr'       => ['class' => 'form-control le-input'],
                    'required'   => false,
                ]
            )
        );

        $configurator   = $this->factory->get('mautic.configurator');
        $params         = $configurator->getParameters();
        $fromname       = $params['mailer_from_name'];
        $fromemail      = $params['mailer_from_email'];
        $default        = (empty($options['data']->getFromname())) ? $fromname : $options['data']->getFromname();

        $builder->add(
            $builder->create(
                'fromname',
                'text',
                [
                    'label'       => 'le.lead.list.optin.fromname.name',
                    'label_attr'  => ['class' => 'control-label required'],
                    'attr'        => ['class' => 'form-control le-input'],
                    'required'    => false,
                    'data'        => $default,
                ]
            )
        );

        $default = (empty($options['data']->getFromaddress())) ? $fromemail : $options['data']->getFromaddress();

        $builder->add(
            $builder->create(
                'fromaddress',
                'text',
                [
                    'label'       => 'le.lead.list.optin.fromaddress.name',
                    'label_attr'  => ['class' => 'control-label required'],
                    'attr'        => ['class' => 'form-control le-input'],
                    'required'    => false,
                    'data'        => $default,
                    'constraints' => [
                        new Email([
                            'message' => 'le.core.email.required',
                        ]),
                        new EmailVerify(
                            [
                                'message' => 'le.email.verification.error',
                            ]
                        ),
                    ],
                ]
            )
        );

        $builder->add(
            $builder->create(
                'message',
                'textarea',
                [
                    'label'      => 'le.lead.list.optin.message.name',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'                => 'form-control editor editor-advanced editor-builder-tokens',
                        'data-token-callback'  => 'email:getBuilderTokens',
                        'data-token-activator' => '{',
                    ],
                    'data'     => $options['data']->getMessage(),
                    'required' => false,
                ]
            )
        );

        $builder->add('resend', 'yesno_button_group', [
            'label'      => 'le.lead.list.optin.resend.name',
            'no_label'   => 'le.lead.list.resend.off',
            'yes_label'  => 'le.lead.list.resend.on',
        ]);

        $builder->add('buttons', 'form_buttons',
            [
                'apply_icon'   => false,
                'save_icon'    => false,
                'apply_text'   => false,
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
