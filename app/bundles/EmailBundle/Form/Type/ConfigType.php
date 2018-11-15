<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\EmailBundle\Form\Validator\Constraints\EmailDomain;
use Mautic\EmailBundle\Form\Validator\Constraints\EmailVerify;
use Mautic\EmailBundle\Model\TransportType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ConfigType.
 */
class ConfigType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var TransportType
     */
    private $transportType;

    private $licenseHelper   = [];
    private $currentUser     = [];

    /**
     * ConfigType constructor.
     *
     * @param TranslatorInterface $translator
     * @param TransportType       $transportType
     * @param MauticFactory       $factory
     */
    public function __construct(TranslatorInterface $translator, TransportType $transportType, MauticFactory $factory)
    {
        $this->translator    = $translator;
        $this->transportType = $transportType;
        $this->licenseHelper = $factory->getHelper('licenseinfo');
        $this->currentUser   = $factory->getUser();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['footer_text' => 'html']));

        $emailProvider = $this->licenseHelper->getEmailProvider();
        $currentUser   = $this->currentUser->isAdmin();

        $builder->add(
            $builder->create(
                'footer_text',
                'textarea',
                [
                    'label'      => 'le.email.config.footer_text',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'                => 'form-control editor editor-advanced editor-builder-tokens',
                        'data-token-callback'  => 'email:getBuilderTokens',
                        'data-token-activator' => '{',
                    ],
                    'required' => true,
                    'data'     => (array_key_exists('footer_text', $options['data']) && !empty($options['data']['footer_text']))
                        ? $options['data']['footer_text']
                        : '',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'le.email.config.default.footer',
                            ]
                        ),
                    ],
                ]
            )
        );

        $builder->add(
            'webview_text',
            'textarea',
            [
                'label'      => 'le.email.config.webview_text',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.webview_text.tooltip',
                ],
                'required' => false,
                'data'     => (array_key_exists('webview_text', $options['data']) && !empty($options['data']['webview_text']))
                    ? $options['data']['webview_text']
                    : $this->translator->trans(
                        'le.email.webview.text',
                        ['%link%' => '|URL|']
                    ),
            ]
        );

        $builder->add(
            'unsubscribe_message',
            'textarea',
            [
                'label'      => 'le.email.config.unsubscribe_message',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.footer_content.tooltip',
                    'tooltip' => 'le.email.config.unsubscribe_message.tooltip',
                ],
                'required' => false,
                'data'     => $this->translator->trans(
                        'le.email.unsubscribed.success',
                        [
                            '%resubscribeUrl%' => '|URL|',
                            '%email%'          => '|EMAIL|',
                        ]
                    ),
            ]
        );

        $builder->add(
            'postal_address',
            'textarea',
            [
                'label'      => 'le.email.config.postal_address',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.postal_address.tooltip',
                    'style'   => 'height:100px;',
                ],
                'required' => false,
                'data'     => (array_key_exists('postal_address', $options['data']) && !empty($options['data']['postal_address']))
                    ? $options['data']['postal_address']
                    : '',
            ]
        );

        $builder->add(
            'resubscribe_message',
            'textarea',
            [
                'label'      => 'le.email.config.resubscribe_message',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.resubscribe_message.tooltip',
                ],
                'required' => false,
                'data'     => $this->translator->trans(
                        'le.email.resubscribed.success',
                        [
                            '%unsubscribeUrl%' => '|URL|',
                            '%email%'          => '|EMAIL|',
                        ]
                    ),
            ]
        );

        $builder->add(
            'default_signature_text',
            'textarea',
            [
                'label'      => 'le.email.config.default_signature_text',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.default_signature_text.tooltip',
                ],
                'required' => false,
                'data'     => (!empty($options['data']['default_signature_text']))
                    ? $options['data']['default_signature_text']
                    : $this->translator->trans(
                        'mautic.email.default.signature',
                        [
                            '%from_name%' => '|FROM_NAME|',
                        ]
                    ),
            ]
        );
        $class = 'status_success';
        $emailStatus = $options['data']['email_status'];
        if($emailStatus == "InActive"){
            $class = 'status_fail';
        }

        $builder->add(
            'email_status',
            'text',
            [
                'label'      => false,
                'attr'       => [
                    'class'    => 'form-control btn btn-primary '.$class,
                ],
                'data'       => (isset($options['data']['email_status'])) ? $options['data']['email_status'] : 'Active',
            ]
        );
        $builder->add(
            'mailer_from_name',
            'text',
            [
                'label'      => 'le.email.config.mailer.from.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                    'tooltip'  => 'le.email.config.mailer.from.name.tooltip',
                ],
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'mautic.core.value.required',
                        ]
                    ),
                ],
            ]
        );

        $choices = [
            'mautic.transport.amazon'     => 'mautic.transport.amazon',
            'le.transport.vialeadsengage' => 'le.transport.vialeadsengage',
        ];
        $transport        = $options['data']['mailer_transport'];
        $datavalue        = $transport;
        $disabletransport = false;
        $tabIndex         ='';
        $style            ='';
        if ($emailProvider == 'LeadsEngage' && ($transport == 'mautic.transport.elasticemail' || $transport == 'mautic.transport.sendgrid_api') && !$currentUser) {
            $datavalue        = 'le.transport.vialeadsengage';
            $disabletransport = false;
        } elseif (!$emailProvider == 'Sparkpost' && !$currentUser) {
            $style   = 'pointer-events: none;background-color: #ebedf0;opacity: 1;';
            $tabIndex= '-1';
        }
        if ($currentUser) {
            $disabletransport = false;
        }

        $builder->add(
            'mailer_from_email',
            'text',
            [
                'label'      => 'le.email.config.mailer.from.email',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control le-input',
                    'tabindex' => $tabIndex,
                    'style'    => $style,
                    'tooltip'  => 'le.email.config.mailer.from.email.tooltip',
                    'onkeyup'      => 'Le.updateEmailStatus();',
                ],
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'le.core.email.required',
                        ]
                    ),
                    new Email(
                        [
                            'message' => 'le.core.email.required',
                        ]
                    ),
                    new EmailVerify(
                        [
                            'message' => 'le.email.verification.error',
                        ]
                    ),
                    new EmailDomain(
                        [
                            'message' => 'le.email.verification.error',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'mailer_return_path',
            'text',
            [
                'label'      => 'le.email.config.mailer.return.path',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control le-input',
                    'tooltip' => 'le.email.config.mailer.return.path.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'mailer_transport',
            ChoiceType::class,
            [
                'choices'  => $this->transportType->getTransportTypes(),
                'label'    => 'le.email.config.mailer.transport',
                'required' => false,
                'attr'     => [
                    'class'   => 'form-control le-input',
                    'tooltip' => 'le.email.config.mailer.transport.tooltip',
                    'onchange'=> 'Le.showBounceCallbackURL(this)',
                ],
                'data'        => $transport,
                'disabled'    => $disabletransport,
                'empty_value' => false,
            ]
        );

        $builder->add(
            'mailer_convert_embed_images',
            'yesno_button_group',
            [
                'label'      => 'le.email.config.mailer.convert.embed.images',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.mailer.convert.embed.images.tooltip',
                ],
                'data'     => empty($options['data']['mailer_convert_embed_images']) ? false : true,
                'required' => false,
            ]
        );

        $builder->add(
            'mailer_append_tracking_pixel',
            'yesno_button_group',
            [
                'label'      => 'le.email.config.mailer.append.tracking.pixel',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.mailer.append.tracking.pixel.tooltip',
                ],
                'data'     => empty($options['data']['mailer_append_tracking_pixel']) ? false : true,
                'required' => false,
            ]
        );

        $builder->add(
            'disable_trackable_urls',
            'yesno_button_group',
            [
                'label'      => 'le.email.config.mailer.disable.trackable.urls',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.mailer.disable.trackable.urls.tooltip',
                ],
                'data'     => empty($options['data']['disable_trackable_urls']) ? false : true,
                'required' => false,
            ]
        );

        $smtpServiceShowConditions  = '{"config_emailconfig_mailer_transport":['.$this->transportType->getSmtpService().']}';
        if ($currentUser) {
            $amazonRegionShowConditions = '{"config_emailconfig_mailer_transport":['.$this->transportType->getAmazonService().']}';
        } else {
            $amazonRegionShowConditions = '{"config_emailconfig_mailer_transport_name":['.$this->transportType->getAmazonService().']}';
        }

        $builder->add(
            'mailer_host',
            'text',
            [
                'label'      => 'le.email.config.mailer.host',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control le-input',
                    'data-show-on' => $smtpServiceShowConditions,
                    'tooltip'      => 'le.email.config.mailer.host.tooltip',
                ],
                'required' => false,
            ]
        );
        $needLeTransport = true;
        if ($options['data']['mailer_transport_name'] != 'le.transport.vialeadsengage') {
            $needLeTransport = false;
        }

        $builder->add(
            'mailer_transport_name',
            'choice',
            [
                'choices'  => $this->transportType->getCustomTransportType($needLeTransport),
                'label'    => 'le.email.tranport.header',
                'required' => false,
                'attr'     => [
                    'class'        => 'form-control le-input',
                    'onchange'     => 'Le.showBounceCallbackURL(this)',
                ],
                'data'        => $datavalue,
                'disabled'    => $disabletransport,
                'empty_value' => false,
            ]
        );

        $builder->add(
            'mailer_amazon_region',
            'choice',
            [
//                'choices' => [
//                    'email-smtp.eu-west-1.amazonaws.com' => 'le.email.config.mailer.amazon_host.eu_west_1',
//                    'email-smtp.us-east-1.amazonaws.com' => 'le.email.config.mailer.amazon_host.us_east_1',
//                    'email-smtp.us-west-2.amazonaws.com' => 'le.email.config.mailer.amazon_host.eu_west_2',
//                ],
                'choices' => [
                    'email.eu-west-1.amazonaws.com' => 'email.eu-west-1.amazonaws.com',
                    'email.us-east-1.amazonaws.com' => 'email.us-east-1.amazonaws.com',
                    'email.us-west-2.amazonaws.com' => 'email.us-west-2.amazonaws.com',
                ],
                'label'    => 'le.email.config.mailer.amazon_host',
                'required' => false,
                'attr'     => [
                    'class'        => 'form-control',
                    'data-show-on' => $amazonRegionShowConditions,
                    'tooltip'      => 'le.email.config.mailer.amazon_host.tooltip',
                    'onchange'     => 'Le.updateEmailStatus();',
                ],
                'empty_value' => false,
            ]
        );

        $builder->add(
            'mailer_port',
            'text',
            [
                'label'      => 'le.email.config.mailer.port',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'data-show-on' => $smtpServiceShowConditions,
                    'tooltip'      => 'le.email.config.mailer.port.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'mailer_auth_mode',
            'choice',
            [
                'choices' => [
                    'plain'    => 'le.email.config.mailer_auth_mode.plain',
                    'login'    => 'le.email.config.mailer_auth_mode.login',
                    'cram-md5' => 'le.email.config.mailer_auth_mode.cram-md5',
                ],
                'label'      => 'le.email.config.mailer.auth.mode',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class'        => 'form-control',
                    'data-show-on' => $smtpServiceShowConditions,
                    'tooltip'      => 'le.email.config.mailer.auth.mode.tooltip',
                ],
                'empty_value' => 'le.email.config.mailer_auth_mode.none',
            ]
        );
        if ($currentUser) {
            $mailerLoginUserShowConditions = '{
            "config_emailconfig_mailer_auth_mode":[
                "plain",
                "login",
                "cram-md5"
            ], "config_emailconfig_mailer_transport":['.$this->transportType->getServiceRequiresLogin().'],
            "config_emailconfig_mailer_transport_name":['.$this->transportType->getServiceRequiresPassword().']
            }';

            $mailerLoginPasswordShowConditions = '{
            "config_emailconfig_mailer_auth_mode":[
                "plain",
                "login",
                "cram-md5"
            ], "config_emailconfig_mailer_transport":['.$this->transportType->getServiceRequiresPassword().'],
            "config_emailconfig_mailer_transport_name":['.$this->transportType->getServiceRequiresPassword().']
            }';

            $mailerLoginUserHideConditions = '{
            "config_emailconfig_mailer_transport":['.$this->transportType->getServiceDoNotNeedLogin().']
            }';

            $mailerLoginPasswordHideConditions = '{
            "config_emailconfig_mailer_transport":['.$this->transportType->getServiceDoNotNeedPassword().']
            }';
        } else {
            $mailerLoginUserShowConditions = '{
            "config_emailconfig_mailer_auth_mode":[
                "plain",
                "login",
                "cram-md5"
            ], "config_emailconfig_mailer_transport_name":['.$this->transportType->getCustomService().']
            }';

            $mailerLoginPasswordShowConditions = '{
            "config_emailconfig_mailer_auth_mode":[
                "plain",
                "login",
                "cram-md5"
            ], "config_emailconfig_mailer_transport_name":['.$this->transportType->getCustomServiceForUser().']
            }';
            $mailerLoginUserHideConditions = '{
            "config_emailconfig_mailer_transport_name":['.$this->transportType->getLeadsEngageService().']
            ,"config_emailconfig_mailer_transport_name":['.$this->transportType->getServiceDoNotNeedLogin().']
            }';

            $mailerLoginPasswordHideConditions = '{
            "config_emailconfig_mailer_transport_name":['.$this->transportType->getLeadsEngageService().']
            ,"config_emailconfig_mailer_transport_name":['.$this->transportType->getServiceDoNotNeedLogin().']
            }';
        }

        $builder->add(
            'mailer_user',
            'text',
            [
                'label'      => 'le.email.config.mailer.user',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control le-input',
                    'placeholder'  => 'mautic.user.user.form.passwordplaceholder',
                    'data-show-on' => $mailerLoginUserShowConditions,
                    'data-hide-on' => $mailerLoginUserHideConditions,
                    'tooltip'      => 'le.email.config.mailer.user.tooltip',
                    'autocomplete' => 'off',
                    'onkeyup'      => 'Le.updateEmailStatus();',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'mailer_password',
            'password',
            [
                'label'      => 'le.email.config.mailer.password',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control le-input',
                    'placeholder'  => 'mautic.user.user.form.passwordplaceholder',
                    'preaddon'     => 'fa fa-lock',
                    'data-show-on' => $mailerLoginPasswordShowConditions,
                    'data-hide-on' => $mailerLoginPasswordHideConditions,
                    'tooltip'      => 'le.email.config.mailer.password.tooltip',
                    'autocomplete' => 'off',
                    'onkeyup'      => 'Le.updateEmailStatus();',
                ],
                'required' => false,
            ]
        );
        if ($currentUser) {
            $apiKeyShowConditions = '{"config_emailconfig_mailer_transport":['.$this->transportType->getServiceRequiresApiKey().']}';
        } else {
            $apiKeyShowConditions = '{"config_emailconfig_mailer_transport_name":['.$this->transportType->getServiceRequiresApiKey().']}';
        }
        $builder->add(
            'mailer_api_key',
            'password',
            [
                'label'      => 'le.email.config.mailer.apikey',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control le-input',
                    'data-show-on' => $apiKeyShowConditions,
                    'tooltip'      => 'le.email.config.mailer.apikey.tooltop',
                    'autocomplete' => 'off',
                    'placeholder'  => 'le.email.config.mailer.apikey.placeholder',
                    'onkeyup'      => 'Le.updateEmailStatus();',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'mailer_encryption',
            'choice',
            [
                'choices' => [
                    'ssl' => 'le.email.config.mailer_encryption.ssl',
                    'tls' => 'le.email.config.mailer_encryption.tls',
                ],
                'label'    => 'mautic.email.config.mailer.encryption',
                'required' => false,
                'attr'     => [
                    'class'        => 'form-control',
                    'data-show-on' => $smtpServiceShowConditions,
                    'tooltip'      => 'le.email.config.mailer.encryption.tooltip',
                ],
                'empty_value'      => 'le.email.config.mailer_encryption.none',
            ]
        );

        $builder->add(
            'mailer_test_connection_button',
            'standalone_button',
            [
                'label'    => 'le.email.config.mailer.transport.test_connection',
                'required' => false,
                'attr'     => [
                    'class'   => 'btn btn-success',
                    'onclick' => 'Le.testEmailServerConnection(true)',
                ],
            ]
        );

        $builder->add(
            'mailer_test_send_button',
            'standalone_button',
            [
                'label'    => 'le.email.config.mailer.transport.test_send',
                'required' => false,
                'attr'     => [
                    'class'   => 'btn btn-info',
                    'onclick' => 'Le.testEmailServerConnection(true)',
                ],
            ]
        );

        $builder->add(
            'mailer_mailjet_sandbox',
            'yesno_button_group',
            [
                'label'      => 'le.email.config.mailer.mailjet.sandbox',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'tooltip'      => 'le.email.config.mailer.mailjet.sandbox',
                    'data-show-on' => '{"config_emailconfig_mailer_transport":['.$this->transportType->getMailjetService().']}',
                ],
                'data'     => empty($options['data']['mailer_mailjet_sandbox']) ? false : true,
                'required' => false,
            ]
        );

        $builder->add(
            'mailer_mailjet_sandbox_default_mail',
            'text',
            [
                'label'      => 'le.email.config.mailer.mailjet.sandbox.mail',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'tooltip'      => 'le.email.config.mailer.mailjet.sandbox.mail',
                    'data-show-on' => '{"config_emailconfig_mailer_transport":['.$this->transportType->getMailjetService().']}',
                    'data-hide-on' => '{"config_emailconfig_mailer_mailjet_sandbox_0":"checked"}',
                ],
                'constraints' => [
                    new Email(
                        [
                            'message' => 'le.core.email.required',
                        ]
                    ),
                ],
                'required' => false,
            ]
        );

        $spoolConditions = '{"config_emailconfig_mailer_spool_type":["memory"]}';

        $builder->add(
            'mailer_spool_type',
            'choice',
            [
                'choices' => [
                    'memory' => 'le.email.config.mailer_spool_type.memory',
                    'file'   => 'le.email.config.mailer_spool_type.file',
                ],
                'label'      => 'le.email.config.mailer.spool.type',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.mailer.spool.type.tooltip',
                ],
                'empty_value' => false,
            ]
        );

        $builder->add(
            'mailer_spool_path',
            'text',
            [
                'label'      => 'le.email.config.mailer.spool.path',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'data-hide-on' => $spoolConditions,
                    'tooltip'      => 'le.email.config.mailer.spool.path.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'mailer_spool_msg_limit',
            'text',
            [
                'label'      => 'le.email.config.mailer.spool.msg.limit',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'data-hide-on' => $spoolConditions,
                    'tooltip'      => 'le.email.config.mailer.spool.msg.limit.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'mailer_spool_time_limit',
            'text',
            [
                'label'      => 'le.email.config.mailer.spool.time.limit',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'data-hide-on' => $spoolConditions,
                    'tooltip'      => 'le.email.config.mailer.spool.time.limit.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'mailer_spool_recover_timeout',
            'text',
            [
                'label'      => 'le.email.config.mailer.spool.recover.timeout',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'data-hide-on' => $spoolConditions,
                    'tooltip'      => 'le.email.config.mailer.spool.recover.timeout.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'mailer_spool_clear_timeout',
            'text',
            [
                'label'      => 'le.email.config.mailer.spool.clear.timeout',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'data-hide-on' => $spoolConditions,
                    'tooltip'      => 'le.email.config.mailer.spool.clear.timeout.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'monitored_email',
            'monitored_email',
            [
                'label'    => false,
                'data'     => (array_key_exists('monitored_email', $options['data'])) ? $options['data']['monitored_email'] : [],
                'required' => false,
            ]
        );

        $builder->add(
            'mailer_is_owner',
            'yesno_button_group',
            [
                'label'      => 'le.email.config.mailer.is.owner',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.mailer.is.owner.tooltip',
                ],
                'data'     => empty($options['data']['mailer_is_owner']) ? false : true,
                'required' => false,
            ]
        );
        $builder->add(
            'email_frequency_number',
            'number',
            [
                'precision'  => 0,
                'label'      => 'le.lead.list.frequency.number',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class' => 'form-control frequency',
                ],
            ]
        );
        $builder->add(
            'email_frequency_time',
            'choice',
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
            ]
        );
        $builder->add(
            'show_contact_segments',
            'yesno_button_group',
            [
                'label'      => 'le.email.config.show.contact.segments',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.show.contact.segments.tooltip',
                ],
                'data'     => empty($options['data']['show_contact_segments']) ? false : true,
                'required' => false,
            ]
        );
        $builder->add(
            'show_contact_preferences',
            'yesno_button_group',
            [
                'label'      => 'le.email.config.show.preference.options',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.show.preference.options.tooltip',
                ],
                'data'     => empty($options['data']['show_contact_preferences']) ? false : true,
                'required' => false,
            ]
        );
        $builder->add(
            'show_contact_frequency',
            'yesno_button_group',
            [
                'label'      => 'le.email.config.show.contact.frequency',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.show.contact.frequency.tooltip',
                ],
                'data'     => empty($options['data']['show_contact_frequency']) ? false : true,
                'required' => false,
            ]
        );
        $builder->add(
            'show_contact_pause_dates',
            'yesno_button_group',
            [
                'label'      => 'le.email.config.show.contact.pause.dates',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.show.contact.pause.dates.tooltip',
                ],
                'data'     => empty($options['data']['show_contact_pause_dates']) ? false : true,
                'required' => false,
            ]
        );
        $builder->add(
            'show_contact_categories',
            'yesno_button_group',
            [
                'label'      => 'le.email.config.show.contact.categories',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.show.contact.categories.tooltip',
                ],
                'data'     => empty($options['data']['show_contact_categories']) ? false : true,
                'required' => false,
            ]
        );
        $builder->add(
            'show_contact_preferred_channels',
            'yesno_button_group',
            [
                'label'      => 'le.email.config.show.contact.preferred.channels',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'le.email.config.show.contact.preferred.channels',
                ],
                'data'     => empty($options['data']['show_contact_preferred_channels']) ? false : true,
                'required' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'emailconfig';
    }
}
