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

use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class LeadListOptInType.
 */
class LeadListOptInType extends AbstractType
{
    private $model;

    private $factory;

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->factory = $factory;
        $this->model   = $factory->getModel('lead.listoptin');
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $modelName = $this->factory->getRequest()->getPathInfo();
        /** @var \Mautic\LeadBundle\Model\ListOptInModel $model */
        $model = $this->model;
        $resolver->setDefaults([
            'choices' => function (Options $options) use ($model, $modelName) {
                $lists = $model->getListsOptIn();

                $choices = [];
                foreach ($lists as $l) {
                    if (strpos($modelName, 'forms') !== false) {
                        $choices[$l['id']] = $l['name'].' ('.$l['listtype'].')';
                    } else {
                        $choices[$l['id']] = $l['name'];
                    }
                }

                return $choices;
            },
            'global_only' => false,
            'required'    => false,
        ]);
    }

    /**
     * @return null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'listoptin_choices';
    }
}
