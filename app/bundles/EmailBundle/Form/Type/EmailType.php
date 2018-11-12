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
use Mautic\CoreBundle\Form\DataTransformer\EmojiToShortTransformer;
use Mautic\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Mautic\CoreBundle\Form\Type\DynamicContentTrait;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\EmailBundle\Form\Validator\Constraints\EmailDomain;
use Mautic\EmailBundle\Form\Validator\Constraints\EmailVerify;
use Mautic\LeadBundle\Helper\FormFieldHelper;
use Mautic\ReportBundle\Scheduler\Enum\SchedulerEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EmailType.
 */
class EmailType extends AbstractType
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

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
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
        $builder->addEventSubscriber(new CleanFormSubscriber(['content' => 'html', 'customHtml' => 'html', 'beeJSON' => 'raw']));
        $builder->addEventSubscriber(new FormExitSubscriber('email.email', $options));
        $emailProvider = $this->licenseHelper->getEmailProvider();

        $name = 'le.email.form.template.name';
        if (!$options['isEmailTemplate']) {
            $name = 'le.email.form.campaign.name';
        }

        $builder->add(
            'name',
            'text',
            [
                'label'      => $name,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control le-input'],
            ]
        );

        $emojiTransformer = new EmojiToShortTransformer();
        $builder->add(
            $builder->create(
                'subject',
                'text',
                [
                    'label'      => 'le.email.subject',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control le-input'],
                ]
            )->addModelTransformer($emojiTransformer)
        );

        $builder->add(
            'fromName',
            'text',
            [
                'label'      => 'le.email.from_name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                    'preaddon' => 'fa fa-user',
                    'tooltip'  => 'le.email.from_name.tooltip',
                    'disabled' => false,
                ],
                'required' => false,
            ]
        );
        $tooltip = 'le.email.from_email.tooltip';
        if ($emailProvider == $this->translator->trans('mautic.transport.amazon')) {
            $tooltip = 'le.email.amazon.fromaddress.tooltip';
        }
        $builder->add(
            'fromAddress',
            'text',
            [
                'label'      => 'le.email.from_email',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                    'preaddon' => 'fa fa-envelope',
                    'tooltip'  => $tooltip,
                 ],
                'constraints' => [
                    new EmailVerify(
                        [
                            'message' => 'le.email.verification.error',
                        ]
                    ),
                    new EmailDomain(
                        [
                            'message' => 'le.email.verification.error',
                        ]
                    ),
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'replyToAddress',
            'text',
            [
                'label'      => 'le.email.reply_to_email',
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
                'label'      => 'le.email.bcc',
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
            'utmTags',
            'utm_tags',
            [
                'label'      => 'le.email.utm_tags',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control le-input',
                    'tooltip' => 'le.email.utm_tags.tooltip',
                ],
                'extra_fields_message'   => 'email',
                 'required'              => false,
            ]
        );

        $builder->add(
            'scheduleTime',
            ChoiceType::class,
            [
                'choices'     => SchedulerEnum::getUnitEnumForSelect(),
                'expanded'    => false,
                'multiple'    => false,
                'label'       => 'mautic.report.schedule.every',
                'label_attr'  => ['class' => 'control-label'],
                'empty_value' => false,
                'required'    => false,
                'attr'        => [
                    'class'                => 'form-control',
                    'data-report-schedule' => 'scheduleUnit',
                ],
            ]
        );

        $builder->add(
            'template',
            'theme_list',
            [
                'feature' => 'email',
                'attr'    => [
                    'class'   => 'form-control hide not-chosen hidden',
                    'tooltip' => 'le.email.form.template.help',
                ],
                'data' => $options['data']->getTemplate() ? $options['data']->getTemplate() : 'blank',
            ]
        );

        $builder->add('isPublished', 'yesno_button_group');
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
            'publishUp',
            'datetime',
            [
                'widget'     => 'single_text',
                'label'      => 'mautic.core.form.publishup',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'datetime',
                ],
                'format'   => 'yyyy-MM-dd HH:mm',
                'required' => false,
            ]
        );

        $builder->add(
            'publishDown',
            'datetime',
            [
                'widget'     => 'single_text',
                'label'      => 'mautic.core.form.publishdown',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'datetime',
                    'tooltip'     => 'le.email.form.publishdown.help',
                ],
                'format'   => 'yyyy-MM-dd HH:mm',
                'required' => false,
            ]
        );

        $builder->add(
            'plainText',
            'textarea',
            [
                'label'      => 'le.email.form.plaintext',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'tooltip'              => 'le.email.form.plaintext.help',
                    'class'                => 'form-control le-input',
                    'rows'                 => '15',
                    'data-token-callback'  => 'email:getBuilderTokens',
                    'data-token-activator' => '{',
                    'data-token-visual'    => 'false',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            $builder->create(
                'customHtml',
                'textarea',
                [
                    'label'      => 'le.email.form.body',
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => false,
                    'attr'       => [
                        'class'                => 'form-control editor editor-advanced editor-builder-tokens builder-html',
                        'data-token-callback'  => 'email:getBuilderTokens',
                        'data-token-activator' => '{',
                    ],
                ]
            )->addModelTransformer($emojiTransformer)
        );
        $builder->add(
            'beeJSON',
            'textarea',
            [
                'label'      => 'le.email.form.beejson',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class'                => 'form-control bee-editor-json',
                ],
            ]
        );

        $transformer = new IdToEntityModelTransformer($this->em, 'MauticFormBundle:Form', 'id');
        $builder->add(
            $builder->create(
                'unsubscribeForm',
                'form_list',
                [
                    'label'      => 'le.email.form.unsubscribeform',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'            => 'form-control',
                        'tooltip'          => 'le.email.form.unsubscribeform.tooltip',
                        'data-placeholder' => $this->translator->trans('mautic.core.form.chooseone'),
                    ],
                    'required'    => false,
                    'multiple'    => false,
                    'empty_value' => '',
                ]
            )
                ->addModelTransformer($transformer)
        );

        $transformer = new IdToEntityModelTransformer($this->em, 'MauticPageBundle:Page', 'id');
        $builder->add(
            $builder->create(
                'preferenceCenter',
                'preference_center_list',
                [
                    'label'      => 'le.email.form.preference_center',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'            => 'form-control',
                        'tooltip'          => 'le.email.form.preference_center.tooltip',
                        'data-placeholder' => $this->translator->trans('mautic.core.form.chooseone'),
                    ],
                    'required'    => false,
                    'multiple'    => false,
                    'empty_value' => '',
                ]
            )
                ->addModelTransformer($transformer)
        );

        $transformer = new IdToEntityModelTransformer($this->em, 'MauticEmailBundle:Email');
        $builder->add(
            $builder->create(
                'variantParent',
                'hidden'
            )->addModelTransformer($transformer)
        );

        $builder->add(
            $builder->create(
                'translationParent',
                'hidden'
            )->addModelTransformer($transformer)
        );

        $variantParent     = $options['data']->getVariantParent();
        $translationParent = $options['data']->getTranslationParent();
        $builder->add(
            'segmentTranslationParent',
            'email_list',
            [
                'label'      => 'mautic.core.form.translation_parent',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.core.form.translation_parent.help',
                ],
                'required'       => false,
                'multiple'       => false,
                'email_type'     => 'list',
                'empty_value'    => 'mautic.core.form.translation_parent.empty',
                'top_level'      => 'translation',
                'variant_parent' => ($variantParent) ? $variantParent->getId() : null,
                'ignore_ids'     => [(int) $options['data']->getId()],
                'mapped'         => false,
                'data'           => ($translationParent) ? $translationParent->getId() : null,
            ]
        );

        $builder->add(
            'templateTranslationParent',
            'email_list',
            [
                'label'      => 'mautic.core.form.translation_parent',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mautic.core.form.translation_parent.help',
                ],
                'required'       => false,
                'multiple'       => false,
                'empty_value'    => 'mautic.core.form.translation_parent.empty',
                'top_level'      => 'translation',
                'variant_parent' => ($variantParent) ? $variantParent->getId() : null,
                'email_type'     => 'template',
                'ignore_ids'     => [(int) $options['data']->getId()],
                'mapped'         => false,
                'data'           => ($translationParent) ? $translationParent->getId() : null,
            ]
        );

        $builder->add(
            'previewText',
            'text',
            [
                'label'      => 'le.email.previewText',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                    'tooltip'  => 'le.email.previewText.tooltip',
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
                        'tooltip'              => 'le.email.config.footer_content.tooltip',
                        'data-token-callback'  => 'email:getBuilderTokens',
                        'data-token-activator' => '{',
                    ],
                    'required' => false,
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
                    'tooltip' => 'le.email.config.postal_address.tooltip',
                    'style'   => 'height:100px;',
                ],
                'required' => false,
            ]
        );

        $url                     = $this->request->getSchemeAndHttpHost().$this->request->getBasePath();
        $variantSettingsModifier = function (FormEvent $event, $isVariant) use ($url) {
            if ($isVariant) {
                $event->getForm()->add(
                    'variantSettings',
                    'emailvariant',
                    [
                        'label' => false,
                    ]
                );
            }
        };

        // Building the form
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($variantSettingsModifier) {
                $variantSettingsModifier(
                    $event,
                    $event->getData()->getVariantParent()
                );
            }
        );

        // After submit
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($variantSettingsModifier) {
                $data = $event->getData();
                $variantSettingsModifier(
                    $event,
                    !empty($data['variantParent'])
                );

                if (isset($data['emailType']) && $data['emailType'] == 'list') {
                    $data['translationParent'] = isset($data['segmentTranslationParent']) ? $data['segmentTranslationParent'] : null;
                } else {
                    $data['translationParent'] = isset($data['templateTranslationParent']) ? $data['templateTranslationParent'] : null;
                }

                $event->setData($data);
            }
        );

        //add category
        $builder->add(
            'category',
            'category',
            [
                'bundle' => 'email',
            ]
        );

        //add lead lists
        $transformer = new IdToEntityModelTransformer($this->em, 'MauticLeadBundle:LeadList', 'id', true);
        $builder->add(
            $builder->create(
                'lists',
                'leadlist_choices',
                [
                    'label'      => 'le.email.form.list',
                    'label_attr' => ['class' => 'control-label '],
                    'attr'       => [
                        'class'        => 'form-control',
                        'tooltip'      => 'le.email.segment.tooltip',
                        'data-show-on' => '{"emailform_segmentTranslationParent":[""]}',
                    ],
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                ]
            )
                ->addModelTransformer($transformer)
        );

        $builder->add(
            'language',
            'locale',
            [
                'label'      => 'mautic.core.language',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'required' => false,
            ]
        );

        //add lead lists
        $transformer = new IdToEntityModelTransformer(
            $this->em,
            'MauticAssetBundle:Asset',
            'id',
            true
        );
        $builder->add(
            $builder->create(
                'assetAttachments',
                'asset_list',
                [
                    'label'      => 'le.email.attachments',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'    => 'form-control',
                        'onchange' => 'Mautic.getTotalAttachmentSize();',
                    ],
                    'multiple' => true,
                    'expanded' => false,
                ]
            )
                ->addModelTransformer($transformer)
        );

        $builder->add('sessionId', 'hidden');
        $builder->add('emailType', 'hidden');
        if (!InputHelper::isMobile()) {
            $customButtons = [//builder disabled due to bee editor
//          [
//              'name'  => 'builder',
//              'label' => 'mautic.core.builder',
//              'attr'  => [
//                  'class'   => 'btn btn-default btn-dnd btn-nospin text-primary btn-builder',
//                  'icon'    => 'fa fa-cube',
//                  'onclick' => "Mautic.launchBuilder('emailform', 'email');",
//              ],
//          ],
                [
                    'name'  => 'beeeditor',
                    'label' => 'mautic.core.beeeditor',
                    'attr'  => [
                        'class'   => 'btn btn-default btn-dnd btn-nospin text-primary btn-beeditor le-btn-default',
                        'icon'    => 'fa fa-cube',
                        'onclick' => "Mautic.launchBeeEditor('emailform', 'email');",
                    ],
                ],
            ];
        } else {
            $customButtons = [];
        }
        /*$builder->add(
            'buttons',
            'form_buttons',
            [
                'apply_text' => 'Send',
            ]
        );*/
        $builder->add(
            'buttons',
            'form_buttons',
            [
                'pre_extra_buttons' => [
                    [
                        'name'  => 'sendtest',
                        'label' => 'Send',
                        'type'  => 'submit',
                        'attr'  => [
                            'class'   => 'btn btn-default pull-right le-btn-default hide sendEmailTest',
                            'icon'    => 'fa fa-send-o',
                        ],
                    ],
                ],
                'apply_text' => false,
            ]
        );
        if (!empty($options['update_select'])) {
            $builder->add(
                'updateSelect',
                'hidden',
                [
                    'data'   => $options['update_select'],
                    'mapped' => false,
                ]
            );
        }

        $this->addDynamicContentField($builder);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Mautic\EmailBundle\Entity\Email',
            ]
        );

        $resolver->setDefined(['update_select', 'isEmailTemplate']);
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
        return 'emailform';
    }
}
