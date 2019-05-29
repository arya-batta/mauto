<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SmsBundle\Form\Type;

use Mautic\CoreBundle\Helper\UserHelper;
use Mautic\SmsBundle\Sms\TransportChain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ConfigType.
 */
class ConfigType extends AbstractType
{
    /**
     * @var TransportChain
     */
    private $transportChain;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * ConfigType constructor.
     *
     * @param TransportChain      $transportChain
     * @param TranslatorInterface $translator
     * @param UserHelper          $userHelper
     */
    public function __construct(TransportChain $transportChain, TranslatorInterface $translator, UserHelper $userHelper)
    {
        $this->transportChain = $transportChain;
        $this->translator     = $translator;
        $this->isadmin        = $userHelper->getUser()->isAdmin();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices       = [];
        $alltransports = $this->transportChain->getEnabledTransports();
        if (!$this->isadmin) {
            foreach ($alltransports as $transportServiceId => $transport) {
                if ($transportServiceId != 'le.sms.transport.solutioninfini' && $transportServiceId != 'le.sms.transport.leadsengage') {
                    $transports[$transportServiceId] = $alltransports[$transportServiceId];
                }
            }
            $alltransports = $transports;
        }
        foreach ($alltransports as $transportServiceId => $transport) {
            if ($transportServiceId == 'le.sms.transport.solutioninfini') {
                $choices[$transportServiceId] = $this->translator->trans('le.sms.transport.solutioninfini.choice');
            } else {
                $choices[$transportServiceId] = $this->translator->trans($transportServiceId);
            }
        }

        $builder->add('sms_transport', ChoiceType::class, [
            'label'      => 'mautic.sms.config.select_default_transport',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'       => 'form-control',
                /*'tooltip'     => 'mautic.sms.config.select_default_transport',*/
                'onchange'    => 'Le.updateTextMessageStatus()',
            ],
            'data'        => $options['data']['sms_transport'],
            'required'    => false,
            'choices'     => $choices,
            'empty_value' => 'mautic.core.form.chooseone',
        ]);
        $SolutionShowConditions   = '{"config_smsconfig_sms_transport":["le.sms.transport.solutioninfini"]}';
        $TwilioShowConditions     = '{"config_smsconfig_sms_transport":["le.sms.transport.twilio"]}';
        $LeadsEngageShowConditions= '{"config_smsconfig_sms_transport":["le.sms.transport.leadsengage"]}';
        $builder->add(
            'account_url',
            'text',
            [
                'label'      => 'le.sms.account.name.solutioninfini',
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class'        => 'form-control le-input',
                    'data-show-on' => $SolutionShowConditions,
                    'data-hide-on' => $TwilioShowConditions,
                    'disabled'     => false,
                    'onkeyup'      => 'Le.updateTextMessageStatus()',
                ],
                'required' => false,
                'data'     => $options['data']['account_url'],
            ]
        );
        $builder->add(
            'account_sid',
            'text',
            [
                'label'      => 'le.sms.account.name.twilio',
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class'        => 'form-control le-input',
                    'data-show-on' => $TwilioShowConditions,
                    'data-hide-on' => $SolutionShowConditions,
                    'disabled'     => false,
                    'onkeyup'      => 'Le.updateTextMessageStatus()',
                ],
                'required' => false,
                'data'     => $options['data']['account_sid'],
            ]
        );
        $builder->add(
            'account_api_key',
            'text',
            [
                'label'      => 'le.sms.account.api.solutioninfini',
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class'        => 'form-control le-input',
                    'data-hide-on' => $TwilioShowConditions,
                    'data-show-on' => $SolutionShowConditions,
                    'disabled'     => false,
                    'onkeyup'      => 'Le.updateTextMessageStatus()',
                ],
                'required' => false,
                'data'     => $options['data']['account_api_key'],
            ]
        );
        $builder->add(
            'account_auth_token',
            'text',
            [
                'label'      => 'le.sms.account.auth.token',
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class'        => 'form-control SII le-input',
                    'data-show-on' => $TwilioShowConditions,
                    'data-hide-on' => $SolutionShowConditions,
                    'disabled'     => false,
                    'onkeyup'      => 'Le.updateTextMessageStatus()',
                ],
                'required' => false,
                'data'     => $options['data']['account_auth_token'],
            ]
        );
        $builder->add(
            'account_sender_id',
            'text',
            [
                'label'      => 'le.sms.account.senderid.solutioninfini',
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class'        => 'form-control le-input',
                    'data-hide-on' => $TwilioShowConditions,
                    'data-show-on' => $SolutionShowConditions,
                    'disabled'     => false,
                    'onkeyup'      => 'Le.updateTextMessageStatus()',
                ],
                'required' => false,
                'data'     => $options['data']['account_sender_id'],
            ]
        );
        $builder->add(
            'sms_from_number',
            'text',
            [
                'label'      => 'le.sms.account.fromnumber',
                'label_attr' => ['class' => 'control-label required'],
                'required'   => false,
                'attr'       => [
                    'class'        => 'form-control le-input',
                    'data-show-on' => $TwilioShowConditions,
                    'data-hide-on' => $SolutionShowConditions,
                    'onkeyup'      => 'Le.updateTextMessageStatus()',
                ],
                'data' => $options['data']['sms_from_number'],
            ]
        );
        $builder->add(
            'sms_frequency_number',
            'number',
            [
                'precision'  => 0,
                'label'      => 'le.sms.features.frequency_number',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class' => 'form-control frequency',
                ],
                'data' => $options['data']['sms_frequency_number'],
            ]
        );
        $builder->add(
            'sms_frequency_time',
            'choice',
            [
                'choices' => [
                    'DAY'   => 'day',
                    'WEEK'  => 'week',
                    'MONTH' => 'month',
                ],
                'label'      => 'le.sms.feature.frequency_time',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'multiple'   => false,
                'attr'       => [
                    'class' => 'form-control frequency',
                ],
                'data' => $options['data']['sms_frequency_time'],
            ]
        );
        $builder->add(
            'publish_account',
            'yesno_button_group',
            [
                'label'      => 'le.sms.account.publish.account',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'data-hide-on' => $LeadsEngageShowConditions,
                    //'onChange'    => 'Le.updateTextMessageStatus()',
                ],
                'data'       => true,
                'required'   => false,
                'no_label'   => 'mautic.core.form.unpublished',
                'yes_label'  => 'mautic.core.form.published',
            ]
        );
        $class     = 'status_success';
        $smsStatus = $options['data']['sms_status'];
        if ($smsStatus == 'InActive') {
            $class = 'status_fail';
        }
        $builder->add(
            'sms_status',
            'text',
            [
                'label'      => 'le.sms.avtivate.status.label',
                'label_attr' => ['class' => 'control-label pointernone'],
                'attr'       => [
                    'class'        => 'form-control col-md-3 le-input '.$class,
                ],
                'data'     => (isset($options['data']['sms_status'])) ? $options['data']['sms_status'] : 'Active',
            ]
        );
        $builder->add(
            'link_shortener_url',
            'text',
            [
                'label'      => 'mautic.core.config.form.link.shortener',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control le-input',
                    'tooltip' => 'mautic.core.config.form.link.shortener.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'sms_test_connection_button',
            'standalone_button',
            [
                'label'    => 'le.sms.config.test_connection',
                'required' => false,
                'attr'     => [
                    'class'   => 'btn btn-success',
                    'onclick' => 'Le.testSmsServerConnection(true)',
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'smsconfig';
    }
}
