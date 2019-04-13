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
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AnalyticsType.
 */
class AnalyticsType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var MauticFactory
     */
    private $factory;

    /**
     * ConfigType constructor.
     *
     * @param TranslatorInterface $translator
     * @param MauticFactory       $factory
     */
    public function __construct(TranslatorInterface $translator, MauticFactory $factory)
    {
        $this->factory       = $factory;
        $this->translator    = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'drip_source',
            'text',
            [
                'label'      => 'le.analytics.config.mailer.source.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'drip_medium',
            'text',
            [
                'label'      => 'le.analytics.config.mailer.medium.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                ],
                'required'   => false,
            ]
        );

        $campaignChoices = [
            '{campaign_name}' => 'le.analytics.campaign.name',
            '{email_subject}' => 'le.analytics.email.subject',
        ];

        $builder->add(
            'drip_campaignname',
            'choice',
            [
                'choices'    => $campaignChoices,
                'label'      => 'le.analytics.config.mailer.campaignname.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'drip_content',
            'choice',
            [
                'choices'    => $campaignChoices,
                'label'      => 'le.analytics.config.mailer.content.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'list_source',
            'text',
            [
                'label'      => 'le.analytics.config.mailer.source.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'list_medium',
            'text',
            [
                'label'      => 'le.analytics.config.mailer.medium.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'list_campaignname',
            'choice',
            [
                'choices'    => $campaignChoices,
                'label'      => 'le.analytics.config.mailer.campaignname.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'list_content',
            'choice',
            [
                'choices'    => $campaignChoices,
                'label'      => 'le.analytics.config.mailer.content.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'analytics_status',
            'yesno_button_group',
            [
                'label'      => 'le.analytics.status.text',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                ],
                'required' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'analyticsconfig';
    }
}
