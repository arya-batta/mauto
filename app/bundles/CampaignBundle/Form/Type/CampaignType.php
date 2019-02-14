<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CampaignBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class CampaignType.
 */
class CampaignType extends AbstractType
{
    private $security;
    private $translator;
    private $em;

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->translator = $factory->getTranslator();
        $this->security   = $factory->getSecurity();
        $this->em         = $factory->getEntityManager();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('campaign', $options));
        if ($options['isShortForm']) {
            $builder->add('name', 'text', [
                'label'      => 'mautic.core.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control le-input'],
                'required'   => true,
            ]);

            $builder->add('category', 'category', [
                'bundle'     => 'campaign',
                'label'      => 'mautic.core.category',
                'label_attr' => ['class' => 'control-label'],
                'new_cat'    => false,
             ]);

            $builder->add(
                'buttons',
                'form_buttons',
                [
                    'apply_text' => 'le.campaign.new.add.create.campaign',
                    'save_text'  => false,
                    'save_attr'  => [
                        'href'         => '#',
                    ],
                    'save_onclick' => 'Le.CloseDataModelCampaign()',
                    'cancel_attr'  => [
                        'data-dismiss' => 'modal',
                        'href'         => '#',
                    ],
                ]
            );
        } else {
            $builder->add('name', 'text', [
                'label'      => 'mautic.core.name',
                'label_attr' => ['class' => 'control-label', 'style' => 'color:#fff;'],
                'attr'       => ['class' => 'form-control'],
            ]);

            $builder->add('description', 'textarea', [
                'label'      => 'mautic.core.description',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control editor'],
                'required'   => false,
            ]);

            //add category
            $builder->add('category', 'category', [
                'bundle'     => 'campaign',
                'label_attr' => ['class' => 'control-label', 'style' => 'color:#fff;'],
            ]);

            if (!empty($options['data']) && $options['data']->getId()) {
                $readonly = !$this->security->isGranted('campaign:campaigns:publish');
                $data     = $options['data']->isPublished(false);
            } elseif (!$this->security->isGranted('campaign:campaigns:publish')) {
                $readonly = true;
                $data     = false;
            } else {
                $readonly = false;
                $data     = false;
            }

            $builder->add('isPublished', 'yesno_button_group', [
                'read_only' => $readonly,
                'data'      => $data,
                'no_label'   => 'mautic.core.form.unpublished',
                'yes_label'  => 'mautic.core.form.published',
            ]);

            $builder->add('publishUp', 'datetime', [
                'widget'     => 'single_text',
                'label'      => 'mautic.core.form.publishup',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'datetime',
                ],
                'format'   => 'yyyy-MM-dd HH:mm',
                'required' => false,
            ]);

            $builder->add('publishDown', 'datetime', [
                'widget'     => 'single_text',
                'label'      => 'mautic.core.form.publishdown',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'datetime',
                ],
                'format'   => 'yyyy-MM-dd HH:mm',
                'required' => false,
            ]);

            $builder->add('sessionId', 'hidden', [
                'mapped' => false,
            ]);

            if (!empty($options['action'])) {
                $builder->setAction($options['action']);
            }
            $builder->add(
                'buttons',
                'form_buttons',
                [
                    'apply_text' => false,
                    'save_icon'  => false,
                    'save_text'  => 'le.campaign.new.add.create.campaign',
                    'save_attr'  => [
                        'href'         => '#',
                    ],
                    'save_onclick' => 'Le.CloseDataModelCampaign()',
                    'cancel_attr'  => [
                        'data-dismiss' => 'modal',
                        'href'         => '#',
                    ],
                ]
            );
            $builder->add('buttons', 'form_buttons', [
                'pre_extra_buttons' => [
                    [
                        'name'  => 'builder',
                        'label' => 'mautic.campaign.campaign.launch.builder',
                        'attr'  => [
                            'class'   => 'btn btn-default btn-dnd le-btn-default',
                            'icon'    => 'fa fa-cube',
                            'onclick' => 'Le.launchCampaignEditor();',
                        ],
                    ],
                ],
            ]);
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => 'Mautic\CampaignBundle\Entity\Campaign',
            'isShortForm' => false,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'campaign';
    }
}
