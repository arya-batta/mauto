<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ConfigType.
 */
class ConfigTrackingPageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'track_contact_by_ip',
            'yesno_button_group',
            [
                'label' => 'mautic.page.config.form.track_contact_by_ip',
                'data'  => (bool) $options['data']['track_contact_by_ip'],
                'attr'  => [
                    'tooltip' => 'mautic.page.config.form.track_contact_by_ip.tooltip',
                ],
            ]
        );

        $builder->add(
            'track_by_tracking_url',
            'yesno_button_group',
            [
            'label' => 'mautic.page.config.form.track.by.tracking.url',
            'data'  => (bool) $options['data']['track_by_tracking_url'],
            'attr'  => [
                'tooltip' => 'mautic.page.config.form.track.by.tracking.url.tooltip',
            ],
        ]);

        $builder->add(
            'track_by_fingerprint',
            'yesno_button_group',
            [
            'label' => 'mautic.page.config.form.track.by.fingerprint',
            'data'  => (bool) $options['data']['track_by_fingerprint'],
            'attr'  => [
                'tooltip' => 'mautic.page.config.form.track.by.fingerprint.tooltip',
            ],
        ]);

        $builder->add(
            'facebook_pixel_id',
            'text',
            [
                'label' => 'mautic.page.config.form.facebook.pixel.id',
                'attr'  => [
                    'class' => 'form-control le-input',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'facebook_pixel_trackingpage_enabled',
            'yesno_button_group',
            [
                'label' => 'mautic.page.config.form.tracking.trackingpage.enabled',
                'data'  => (bool) $options['data']['facebook_pixel_trackingpage_enabled'],
            ]
        );

        $builder->add(
            'facebook_pixel_landingpage_enabled',
            'yesno_button_group',
            [
                'label' => 'mautic.page.config.form.tracking.landingpage.enabled',
                'data'  => (bool) $options['data']['facebook_pixel_landingpage_enabled'],
            ]
        );

        $builder->add(
            'google_analytics_id',
            'text',
            [
                'label' => 'mautic.page.config.form.google.analytics.id',
                'attr'  => [
                    'class' => 'form-control le-input',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'google_analytics_trackingpage_enabled',
            'yesno_button_group',
            [
                'label' => 'mautic.page.config.form.tracking.trackingpage.enabled',
                'data'  => (bool) $options['data']['google_analytics_trackingpage_enabled'],
            ]
        );

        $builder->add(
            'google_analytics_landingpage_enabled',
            'yesno_button_group',
            [
                'label' => 'mautic.page.config.form.tracking.landingpage.enabled',
                'data'  => (bool) $options['data']['google_analytics_landingpage_enabled'],
            ]
        );

        $builder->add(
            'emailInstructionsto',
            'text',
            [
                'label'      => 'mautic.page.tracking.emailinstructions',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                    'preaddon' => 'fa fa-envelope',
                    'id'       => 'emailInstructionsto',
                    'tooltip'  => 'mautic.page.tracking.emailinstructions.tooltip',
                ],
                'required' => false,
                'data'     => (array_key_exists('emailInstructionsto', $options['data']) && !empty($options['data']['emailInstructionsto']))
                    ? $options['data']['emailInstructionsto']
                    : '',
            ]
        );

        $builder->add(
            'emailAdditionainfo',
            'textarea',
            [
                'label' => 'mautic.page.tracking.emailAdditioninfo',
                'attr'  => [
                    'class'    => 'form-control',
                    'id'       => 'emailAdditionainfo',
                    'tooltip'  => 'mautic.page.tracking.emailAdditioninfo.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'send_tracking_instruction',
            'standalone_button',
            [
                'label'    => 'mautic.email.config.mailer.transport.send_tracking',
                'required' => false,
                'attr'     => [
                    'class'   => 'btn btn-success',
                    'onclick' => 'Mautic.testEmailServerConnection()',
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'trackingconfig';
    }
}
