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
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ListActionType.
 */
class ListActionType extends AbstractType
{
    private $modelName;

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->modelName =  $factory->getRequest()->getPathInfo();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isRequired=true;
        if (strpos($this->modelName, 'forms') !== false) {
            $isRequired= false;
        }

        $builder->add('addToLists', 'leadlist_choices', [
            'label'      => 'le.lead.lead.events.addtolists',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class' => 'form-control',
            ],
            'required'    => $isRequired,
            'multiple'    => true,
            'expanded'    => false,
        ]);

        $builder->add('removeFromLists', 'leadlist_choices', [
            'label'      => 'le.lead.lead.events.removefromlists',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class' => 'form-control',
            ],
            'multiple' => true,
            'expanded' => false,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'leadlist_action';
    }
}
