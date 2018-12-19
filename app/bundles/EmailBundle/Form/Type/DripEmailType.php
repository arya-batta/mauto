<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Mautic\CoreBundle\Form\Type\DynamicContentTrait;
use Mautic\LeadBundle\Helper\FormFieldHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class DripEmailType.
 */
class DripEmailType extends AbstractType
{
    use DynamicContentTrait;

    private $translator;
    private $defaultTheme;
    private $em;
    private $request;

    private $countryChoices  = [];
    private $regionChoices   = [];
    private $timezoneChoices = [];
    private $stageChoices    = [];
    private $localeChoices   = [];
    private $licenseHelper   = [];
    private $currentUser     = [];

    private $factory;

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->factory         = $factory;
        $this->translator      = $factory->getTranslator();
        $this->defaultTheme    = $factory->getParameter('theme');
        $this->em              = $factory->getEntityManager();
        $this->request         = $factory->getRequest();
        $this->countryChoices  = FormFieldHelper::getCountryChoices();
        $this->regionChoices   = FormFieldHelper::getRegionChoices();
        $this->timezoneChoices = FormFieldHelper::getCustomTimezones();
        $this->localeChoices   = FormFieldHelper::getLocaleChoices();
        $this->licenseHelper   = $factory->getHelper('licenseinfo');
        $this->currentUser     = $factory->getUser();
        $stages                = $factory->getModel('stage')->getRepository()->getSimpleList();

        foreach ($stages as $stage) {
            $this->stageChoices[$stage['value']] = $stage['label'];
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['unsubscribe_text' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('email.dripemail', $options));
        $emailProvider       = $this->licenseHelper->getEmailProvider();
        $configurator        = $this->factory->get('mautic.configurator');
        $coreParameterHelper = $this->factory->get('mautic.helper.core_parameters');
        $params              = $configurator->getParameters();
        $fromname            = ''; //$params['mailer_from_name'];
        $fromadress          = ''; //$params['mailer_from_email'];
        $emailmodel          =$this->factory->getModel('email');
        $defaultsender       =$emailmodel->getDefaultSenderProfile();
        if (sizeof($defaultsender) > 0) {
            $fromname  =$defaultsender[0];
            $fromadress=$defaultsender[1];
        }
        $unsubscribetxt  = $coreParameterHelper->getParameter('footer_text');
        $postaladdress   = $coreParameterHelper->getParameter('postal_address');
        $days            = [
            'Mon'        => 'le.drip.email.schedule.day.monday',
            'Tue'        => 'le.drip.email.schedule.day.tuesday',
            'Wed'        => 'le.drip.email.schedule.day.wednesday',
            'Thu'        => 'le.drip.email.schedule.day.thursday',
            'Fri'        => 'le.drip.email.schedule.day.friday',
            'Sat'        => 'le.drip.email.schedule.day.saturday',
            'Sun'        => 'le.drip.email.schedule.day.sunday',
        ];

        if ($options['isShortForm']) {
            $builder->add('name', 'text', [
                'label'      => 'mautic.core.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control le-input'],
                'required'   => true,
            ]);

            $builder->add('category', 'category', [
                'bundle'     => 'dripemail',
                'label'      => 'mautic.core.category',
                'label_attr' => ['class' => 'control-label'],
                'new_cat'    => false,
            ]);

            $builder->add(
                'buttons',
                'form_buttons',
                [
                    'apply_text'   => 'le.drip.email.create.drip',
                    'save_text'    => false,
                    'save_icon'    => false,
                    'cancel_attr'  => [
                        'data-dismiss' => 'modal',
                        'href'         => '#',
                    ],
                ]
            );
        } else {
            $builder->add(
                'name',
                'text',
                [
                    'label'      => 'le.drip.email.name',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control le-input'],
                ]
            );

            $builder->add(
                $builder->create(
                    'subject',
                    'text',
                    [
                        'label'      => 'le.drip.email.subject',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => ['class' => 'le-white-background form-control le-input',
                                         'style' => 'background-color: #fff;', ],
                        'required'   => false,
                    ]
                )
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

            $builder->add(
                'fromName',
                'text',
                [
                    'label'      => 'le.drip.email.fromName',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'    => 'form-control le-input',
                        'preaddon' => 'fa fa-user',
                        'tooltip'  => 'le.email.from_name.tooltip',
                        'disabled' => false,
                    ],
                    'required' => false,
                    'data'     => $options['data']->getFromName() ? $options['data']->getFromName() : $fromname,
                ]
            );
            $tooltip = 'le.email.from_email.tooltip';
            if ($emailProvider == $this->translator->trans('le.transport.amazon')) {
                $tooltip = 'le.email.amazon.fromaddress.tooltip';
            }
            $builder->add(
                'fromAddress',
                'text',
                [
                    'label'      => 'le.drip.email.fromAddress',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'    => 'form-control le-input',
                        'preaddon' => 'fa fa-envelope',
                        'tooltip'  => $tooltip,
                    ],
                    'required' => false,
                    'data'     => $options['data']->getFromAddress() ? $options['data']->getFromAddress() : $fromadress,
                ]
            );

            $builder->add(
                'replyToAddress',
                'text',
                [
                    'label'      => 'le.drip.email.replyToAddress',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'    => 'form-control le-input',
                        'preaddon' => 'fa fa-envelope',
                        'tooltip'  => 'le.email.reply_to_email.tooltip',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'bccAddress',
                'text',
                [
                    'label'      => 'le.drip.email.bccAddress',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'    => 'form-control le-input',
                        'preaddon' => 'fa fa-envelope',
                        'tooltip'  => 'le.email.bcc.tooltip',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'isPublished',
                'yesno_button_group',
                [
                    'attr'  => [
                        'onchange' => 'Le.toggleDripEmailPublisedListVisibility();',
                    ],
                ]
            );
            $builder->add(
                'google_tags',
                'yesno_button_group',
                [
                    'label'      => 'le.email.config.show.google.analytics',
                    'label_attr' => ['class' => 'control-label '],
                    'attr'       => [
                        'class'   => 'form-control ',
                    ],
                ]
            );

            $builder->add(
                'previewText',
                'text',
                [
                    'label'       => 'le.email.previewText',
                    'label_attr'  => ['class' => 'control-label'],
                    'attr'        => [
                        'class'   => 'le-white-background form-control le-input',
                        'style'   => 'background-color: #fff;',
                        'tooltip' => 'le.email.previewText.tooltip',
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                $builder->create(
                    'unsubscribe_text',
                    'textarea',
                    [
                        'label'      => 'le.email.footer.content',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => [
                            'class'                => 'form-control editor editor-advanced editor-builder-tokens',
                            'tooltip'              => 'le.drip.email.config.footer_content.tooltip',
                            'data-token-callback'  => 'email:getBuilderTokens',
                            'data-token-activator' => '{',
                        ],
                        'required' => false,
                        'data'     => $options['data']->getUnsubscribeText() ? $options['data']->getUnsubscribeText() : $unsubscribetxt,
                    ]
                )
            );

            $builder->add(
                'postal_address',
                'textarea',
                [
                    'label'      => 'le.email.postal.address.content',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'   => 'form-control',
                        'tooltip' => 'le.drip.email.config.postal_address.tooltip',
                        'style'   => 'height:100px;',
                    ],
                    'required' => false,
                    'data'     => $options['data']->getPostalAddress() ? $options['data']->getPostalAddress() : $postaladdress,
                ]
            );

            //add category
            $builder->add(
                'category',
                'category',
                [
                    'bundle' => 'dripemail',
                ]
            );

            $builder->add('scheduleDate', 'time', [
                'required'       => false,
                'label'          => 'le.drip.email.schedule.time',
                'label_attr'     => ['class' => 'control-label '],
                'widget'         => 'single_text',
                'attr'           => ['data-toggle' => 'time', 'class' => 'form-control le-input'],
                'input'          => 'string',
                'html5'          => false,
                'empty_value'    => false,
                'with_seconds'   => true,
                'with_minutes'   => true,
            ]);

            $builder->add(
                'daysEmailSend',
                'choice',
                [
                    'choices'     => $days,
                    'multiple'    => true,
                    'expanded'    => true,
                    'label'       => 'le.drip.email.schedule.day',
                    'label_attr'  => ['class' => 'control-label'],
                    'empty_value' => false,
                    'required'    => false,
                    'attr'        => [
                        'class'   => 'form-control',
                    ],
                    'data'        => $options['data']->getDaysEmailSend() ? $options['data']->getDaysEmailSend() : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                ]
            );

            $recipients = null;
            if (!empty($options['data'])) {
                if (is_array($options['data'])) {
                    $recipients = (!empty($options['data']['recipients'])) ? $options['data']['recipients'] : null;

                    // Merge the parent data over so the child forms could use them
                    if (is_array($recipients)) {
                        $recipients = array_merge($options['data'], $recipients);
                    }
                } elseif (is_object($options['data']) && method_exists($options['data'], 'getRecipients')) {
                    $recipients = $options['data']->getRecipients();
                }
            }
            $builder->add('recipients', 'dripemailrecipientsfilter', [
                'label'       => false,
                'data'        => $recipients,
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'le.email.recipients.filter.value.required',
                        ]
                    ),
                ],
            ]);

            $builder->add(
                'buttons',
                'form_buttons',
                [
                    'apply_text' => false,
                    'save_icon'  => false,
                ]
            );
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'  => 'Mautic\EmailBundle\Entity\DripEmail',
                'isShortForm' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['countries'] = $this->countryChoices;
        $view->vars['regions']   = $this->regionChoices;
        $view->vars['timezones'] = $this->timezoneChoices;
        $view->vars['stages']    = $this->stageChoices;
        $view->vars['locales']   = $this->localeChoices;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dripemailform';
    }
}
