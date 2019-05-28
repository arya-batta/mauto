<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class SlackType.
 */
class SlackType extends AbstractType
{
    private $factory;

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
        //$builder->addEventSubscriber(new CleanFormSubscriber(['message' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('plugin.slack', $options));

        $builder->add(
            'name',
            'text',
            [
                'label'      => 'le.slack.internal.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control le-input'],
            ]
        );

        $builder->add(
            'description',
            'textarea',
            [
                'label'      => 'le.slack.form.internal.description',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
            ]
        );

        $builder->add(
            'message',
            'textarea',
            [
                'label'      => 'le.slack.form.message',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                    'rows'  => 6,
                ],
            ]
        );

        $builder->add('isPublished', 'yesno_button_group', [
            'no_label'   => 'mautic.core.form.unpublished',
            'yes_label'  => 'mautic.core.form.published',
            ]);

        //add category
        $builder->add(
             'category',
             'category',
             [
                 'bundle' => 'sms',
             ]
         );

        //$builder->add('buttons', 'form_buttons');
        $builder->add(
            'buttons',
            'form_buttons',
            [
                'apply_text' => false,
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
                'data_class' => 'Mautic\PluginBundle\Entity\Slack',
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'slack';
    }
}
