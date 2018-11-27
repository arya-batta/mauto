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

use Mautic\EmailBundle\Model\DripEmailModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class DripEmailListType.
 */
class DripEmailListType extends AbstractType
{
    private $choices = [];

    /**
     * UserListType constructor.
     *
     * @param DripEmailModel $model
     */
    public function __construct(DripEmailModel $model)
    {
        $filterarray= [
            'force' => [
                [
                    'column' => 'd.isPublished',
                    'expr'   => 'eq',
                    'value'  => true,
                ],
                [
                    'column' => 'd.id',
                    'expr'   => 'neq',
                    'value'  => '1',
                ],
            ],
        ];

        $choices = $model->getEntities(
            [
                'filter' => $filterarray,
            ]
        );

        foreach ($choices as $choice) {
            $this->choices[$choice->getId()] = $choice->getName(true);
        }

        //sort by language
        ksort($this->choices);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'choices'            => $this->choices,
                'expanded'           => false,
                'multiple'           => false,
                'required'           => false,
                'empty_value'        => 'mautic.core.form.chooseone',
                'enableNewForm'      => false,
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dripemail_list';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return 'choice';
    }
}
