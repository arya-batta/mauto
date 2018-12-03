<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 1/12/18
 * Time: 3:28 PM.
 */

namespace Mautic\EmailBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class DripEmailMoveType.
 */
class DripEmailMoveType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'movedripfrom',
            'dripemail_list',
            [
                'label'      => 'le.drip.email.movefrom.dripemail',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                ],
                'multiple'    => false,
                'required'    => false,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'le.drip.email.choosedripemail.notblank']
                    ),
                ],
            ]
        );

        $builder->add(
            'movedripto',
            'dripemail_list',
            [
                'label'      => 'le.drip.email.moveto.dripemail',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                ],
                'multiple'    => false,
                'required'    => false,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'le.drip.email.choosedripemail.notblank']
                    ),
                ],
            ]
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(
            [
                'data_class' => 'Mautic\EmailBundle\Entity\DripEmail',
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dripemail_move';
    }
}
