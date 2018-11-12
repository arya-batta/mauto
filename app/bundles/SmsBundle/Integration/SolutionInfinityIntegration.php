<?php


/*
 * @copyright   2017 Mauto Contributors. All rights reserved
 * @author      Mauto
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SmsBundle\Integration;

use Mautic\PluginBundle\Integration\AbstractIntegration;

/**
 * Class SolutionInfinityIntegration.
 */
class SolutionInfinityIntegration extends AbstractIntegration
{
    /**
     * @var bool
     */
    protected $coreIntegration = true;

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'SolutionInfinity';
    }

    public function getIcon()
    {
        return 'app/bundles/SmsBundle/Assets/img/Solution.png';
    }

    public function getSecretKeys()
    {
        return ['password'];
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getRequiredKeyFields()
    {
        return [
            'url'      => 'mautic.sms.config.form.sms.solution.url',
            'apikey'   => 'mautic.sms.config.form.sms.solution.apikey',
            'senderid' => 'mautic.sms.config.form.sms.solution.senderid',
        ];
    }

    /**
     * @return array
     */
    public function getFormSettings()
    {
        return [
            'requires_callback'      => false,
            'requires_authorization' => false,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        return 'none';
    }

    /**
     * @param \Mautic\PluginBundle\Integration\Form|FormBuilder $builder
     * @param array                                             $data
     * @param string                                            $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        /*if ($formArea == 'features') {
            $builder->add(
                'sending_phone_number',
                'text',
                [
                    'label'      => 'mautic.sms.config.form.sms.sending_phone_number',
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => false,
                    'attr'       => [
                        'class' => 'form-control',
                    ],
                ]
            );
            $builder->add('frequency_number', 'number',
                [
                    'precision'  => 0,
                    'label'      => 'mautic.sms.list.frequency.number',
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => false,
                    'attr'       => [
                        'class' => 'form-control frequency',
                    ],
                ]);
            $builder->add('frequency_time', 'choice',
                [
                    'choices' => [
                        'DAY'   => 'day',
                        'WEEK'  => 'week',
                        'MONTH' => 'month',
                    ],
                    'label'      => 'le.lead.list.frequency.times',
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => false,
                    'multiple'   => false,
                    'attr'       => [
                        'class' => 'form-control frequency',
                    ],
                ]);
        }*/
    }
}
