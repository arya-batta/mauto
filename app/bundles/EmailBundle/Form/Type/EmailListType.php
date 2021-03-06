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
use Mautic\CoreBundle\Form\Type\EntityLookupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EmailListType.
 */
class EmailListType extends AbstractType
{
    protected $factory;

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $modelname    = $this->factory->getRequest()->get('_route');
        $enableNewForm=true;
        if ($modelname == 'le_campaignevent_action' || $modelname == 'le_formaction_action' || $modelname == 'le_listoptin_action') {
            $enableNewForm = false;
        }

        $resolver->setDefaults(
            [
                'multiple'    => true,
                'required'    => false,
                'modal_route' => 'le_email_action',
                // Email form UI too complicated for a modal so force a popup
                'force_popup'        => true,
                'model'              => 'email',
                'enableNewForm'      => $enableNewForm,
                'ajax_lookup_action' => function (Options $options) {
                    $query = [
                        'email_type'     => $options['email_type'],
                        'top_level'      => $options['top_level'],
                        'variant_parent' => $options['variant_parent'],
                        'ignore_ids'     => $options['ignore_ids'],
                    ];

                    return 'email:getLookupChoiceList&'.http_build_query($query);
                },
                'model_lookup_method' => 'getLookupResults',
                'lookup_arguments'    => function (Options $options) {
                    return [
                        'type'    => 'email',
                        'filter'  => '$data',
                        'limit'   => 0,
                        'start'   => 0,
                        'options' => [
                            'email_type'     => $options['email_type'],
                            'top_level'      => $options['top_level'],
                            'variant_parent' => $options['variant_parent'],
                            'ignore_ids'     => $options['ignore_ids'],
                        ],
                    ];
                },
                //'modal_route_parameters' => 'template'
                'email_type'     => 'list',
                'top_level'      => 'variant',
                'variant_parent' => null,
                'ignore_ids'     => [],
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'email_list';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return EntityLookupType::class;
    }
}
