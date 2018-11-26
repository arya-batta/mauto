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

use Mautic\ChannelBundle\Entity\MessageQueue;
use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class EmailSendType.
 */
class EmailSendType extends AbstractType
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
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isSendMail=!empty($options['with_email_types']) ? $options['with_email_types'] : false;
        $email_list_type=!empty($options['set_email_list_type']) ? $options['set_email_list_type'] : 'list';

        $builder->add(
            'email',
            'email_list',
            [
                'label'      => 'le.email.send.email.list',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    //'tooltip'  => 'le.email.choose.emails_descr',
                    'onchange' => 'Le.disabledEmailAction(window, this)',
                ],
                'email_type' => $email_list_type,
                'multiple'    => !$isSendMail,
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'mautic.core.value.required']
                    ),
                ],
            ]
        );
        if($this->factory->getUser()->isAdmin()) {
            if (!empty($options['with_email_types'])) {
                $builder->add(
                    'email_type',
                    'button_group',
                    [
                        'choices' => [
                            'transactional' => 'le.email.send.emailtype.transactional',
                            'marketing' => 'le.email.send.emailtype.marketing',
                        ],
                        'label' => 'le.email.send.emailtype',
                        'label_attr' => ['class' => 'control-label'],
                        'attr' => [
                            'class' => 'form-control email-type',
                            'tooltip' => 'le.email.send.emailtype.tooltip',
                        ],
                        'data' => (!isset($options['data']['email_type'])) ? 'transactional' : $options['data']['email_type'],
                    ]
                );
            }
        }

        if (!empty($options['update_select'])) {
            $windowUrl = $this->factory->getRouter()->generate(
                'le_email_action',
                [
                    'objectAction' => 'new',
                    'contentOnly'  => 1,
                    'updateSelect' => $options['update_select'],
                ]
            );

            $builder->add(
                'newEmailButton',
                'button',
                [
                    'attr' => [
                        'class'   => 'btn btn-primary btn-nospin hide',
                        'onclick' => 'Le.loadNewWindow({
                        "windowUrl": "'.$windowUrl.'"
                    })',
                        'icon' => 'fa fa-plus',
                    ],
                    'label' => 'le.email.send.new.email',
                ]
            );

            // create button edit email
            $windowUrlEdit = $this->factory->getRouter()->generate(
                'le_email_campaign_action',
                [
                    'objectAction' => 'edit',
                    'objectId'     => 'emailId',
                    'contentOnly'  => 1,
                    'updateSelect' => $options['update_select'],
                ]
            );

            $builder->add(
                'editEmailButton',
                'button',
                [
                    'attr' => [
                        'class'    => 'btn btn-primary btn-nospin',
                        'onclick'  => 'Le.loadNewWindow(Le.standardEmailUrl({"windowUrl": "'.$windowUrlEdit.'","origin":"#'.$options['update_select'].'"}))',
                        'disabled' => !isset($options['data']['email']),
                        'icon'     => 'fa fa-edit',
                    ],
                    'label' => 'le.email.send.edit.email',
                ]
            );

            // create button preview email

            if($options['update_select'] == 'campaignevent_properties_useremail_email'){
                $windowUrlPreview = $this->factory->getRouter()->generate('le_email_preview', ['type' => 'emailnotifications','objectId' => 'emailId']);
            }else{
                $windowUrlPreview = $this->factory->getRouter()->generate('le_email_preview', ['type'=> 'broadcast','objectId' => 'emailId']);
            }
            $builder->add(
                'previewEmailButton',
                'button',
                [
                    'attr' => [
                        'class'    => 'btn btn-primary btn-nospin',
                        'onclick'  => 'Le.loadNewWindow(Le.standardEmailUrl({"windowUrl": "'.$windowUrlPreview.'","origin":"#'.$options['update_select'].'"}))',
                        'disabled' => !isset($options['data']['email']),
                        'icon'     => 'fa fa-external-link',
                    ],
                    'label' => 'le.email.send.preview.email',
                ]
            );
            if (!empty($options['with_email_types'])) {
                $data = (!isset($options['data']['priority'])) ? 2 : (int) $options['data']['priority'];
                $builder->add(
                    'priority',
                    'choice',
                    [
                        'choices' => [
                            MessageQueue::PRIORITY_NORMAL => 'mautic.channel.message.send.priority.normal',
                            MessageQueue::PRIORITY_HIGH   => 'mautic.channel.message.send.priority.high',
                        ],
                        'label'    => 'mautic.channel.message.send.priority',
                        'required' => false,
                        'attr'     => [
                            'class'        => 'form-control',
                            'tooltip'      => 'mautic.channel.message.send.priority.tooltip',
                            'data-show-on' => '{"campaignevent_properties_email_type_1":"checked"}',
                        ],
                        'data'        => $data,
                        'empty_value' => false,
                    ]
                );

                $data = (!isset($options['data']['attempts'])) ? 3 : (int) $options['data']['attempts'];
                $builder->add(
                    'attempts',
                    'number',
                    [
                        'label' => 'mautic.channel.message.send.attempts',
                        'attr'  => [
                            'class'        => 'form-control le-input',
                            'tooltip'      => 'mautic.channel.message.send.attempts.tooltip',
                            'data-show-on' => '{"campaignevent_properties_email_type_1":"checked"}',
                        ],
                        'data'       => $data,
                        'empty_data' => 0,
                        'required'   => false,
                    ]
                );
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'with_email_types' => false,
            ]
        );

        $resolver->setDefined(['update_select', 'with_email_types','set_email_list_type']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'emailsend_list';
    }
}
