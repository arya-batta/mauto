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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class EmailUtmTagsType.
 */
class EmailUtmTagsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'utmSource',
            'text',
            [
                'label'      => 'le.email.campaign_source',
                'data'       => 'le.core.email.utm.source',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control le-input',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'utmMedium',
            'text',
            [
                'label'      => 'le.email.campaign_medium',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control le-input',
                ],
                'data'     => isset($options['extra_fields_message']) ? $options['extra_fields_message'] : '',
                'required' => false,
            ]
        );

        $builder->add(
            'utmCampaign',
            'text',
            [
                'label'      => 'le.email.campaign_name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control le-input',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'utmContent',
            'text',
                [
                'label'      => 'le.email.campaign_content',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control le-input',
                ],
                'required' => false,
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'utm_tags';
    }
}
