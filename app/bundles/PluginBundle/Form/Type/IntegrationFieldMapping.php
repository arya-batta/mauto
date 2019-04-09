<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PluginBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class IntegrationFieldMapping.
 */
class IntegrationFieldMapping extends AbstractType
{
    private $translator;
    private $fieldChoices           = [];
    private $propertyChoices        = [];

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->translator    = $factory->getTranslator();
        $fieldmodel          =$factory->get('mautic.lead.model.field');
        $this->fieldChoices  = $fieldmodel->getFieldListWithProperties('lead', true);
        // $this->propertyChoices['country']  = FormFieldHelper::getCountryChoices();
        //$this->propertyChoices['region']  = FormFieldHelper::getRegionChoices();
        $userModel   =$factory->getModel('user');
        $userchoices = $userModel->getRepository()->getEntities(
            [
                'filter' => [
                    'force' => [
                        [
                            'column' => 'u.isPublished',
                            'expr'   => 'eq',
                            'value'  => true,
                        ],
                    ],
                ],
            ]
        );
        foreach ($userchoices as $user) {
            $this->propertyChoices['owner_id'][$user->getId()]=$user->getName(true);
        }
        // Segments
        $listModel=$factory->get('mautic.lead.model.list');
        $lists    = $listModel->getUserLists();
        foreach ($lists as $list) {
            $this->propertyChoices['leadlist'][$list['id']] = $list['name'];
        }

        // Lists
        $listOptInModel=$factory->get('mautic.lead.model.listoptin');
        $listoptins    = $listOptInModel->getListsOptIn();
        foreach ($listoptins as $listoptin) {
            $this->propertyChoices['listoptin'][$listoptin['id']] = $listoptin['name'];
        }
        //Tags
        $leadModel=$factory->get('mautic.lead.model.lead');
        $tags     = $leadModel->getTagList();
        foreach ($tags as $tag) {
            $this->propertyChoices['tags'][$tag['value']] = $tag['label'];
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder->create(
                'field_mapping',
                'collection',
                [
                    'type'    => 'lead_field_mapping',
                    'options' => [
                        'label'                => false,
                        'fields'               => $this->fieldChoices,
                        'propertychoices'      => $this->propertyChoices,
                        'integration'          => $options['integration'],
                    ],
                    'error_bubbling' => true,
                    'mapped'         => true,
                    'allow_add'      => true,
                    'allow_delete'   => true,
                    'label'          => false,
                ]
            )
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(
            [
                'integration',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['fields']          = $this->fieldChoices;
        $view->vars['propertychoices'] = $this->propertyChoices;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'integration_field_mapping';
    }
}
