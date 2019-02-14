<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
/**
 * Class TagEntityType.
 */
class TagEntityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tag', 'text',
            [
                'label'      => 'le.lead.tags.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control le-input',
                ],
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'le.lead.tags.name.required',
                        ]
                    ),
                ],
                'required' => true,
            ]);
        $builder->add('is_published', 'yesno_button_group',[
            'no_label'   => 'mautic.core.form.unpublished',
            'yes_label'  => 'mautic.core.form.published',
            ]);
        $builder->add(
            'buttons',
            'form_buttons',
            [
                'apply_text'   => 'le.lead.tags.save.close',
                'save_text'    => false,
                'save_icon'    => false,
                'cancel_attr'  => [
                    'data-dismiss' => 'modal',
                    'href'         => '#',
                ],
            ]
        );
    }
    /**
     * @param OptionsResolver $resolver
     */
  /*  public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Mautic\LeadBundle\Entity\Tag',
            ]
        );
        $resolver->setDefined(['isNew']);
    }*/
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::class;
    }
}
