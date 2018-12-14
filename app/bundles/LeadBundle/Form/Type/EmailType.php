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
use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\EmailBundle\Form\Validator\Constraints\EmailVerify;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class EmailType.
 */
class EmailType extends AbstractType
{
    /**
     * @var MauticFactory
     */
    private $factory;

    public function __construct(MauticFactory $factory)
    {
        $this->factory        = $factory;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['body' => 'html']));
        $emailProvider = $this->factory->get('mautic.helper.licenseinfo')->getEmailProvider();
        //  $currentUser   = $this->factory->get('mautic.helper.user')->getUser()->isAdmin();
        /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
        // $configurator  = $this->factory->get('mautic.configurator');
      //  $params        = $configurator->getParameters();
        $fromname      = ''; //$params['mailer_from_name'];
        $fromemail     = ''; //$params['mailer_from_email'];
        $emailmodel    =$this->factory->getModel('email');
        $defaultsender =$emailmodel->getDefaultSenderProfile();
        if (sizeof($defaultsender) > 0) {
            $fromname =$defaultsender[0];
            $fromemail=$defaultsender[1];
        }
        $builder->add(
            'subject',
            'text',
            [
                'label'       => 'le.email.subject',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control le-input'],
                'required'    => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'mautic.core.subject.required',
                    ]),
                ],
            ]
        );

        // $user = $this->factory->get('mautic.helper.user')->getUser();
//        if ($emailProvider == 'Sparkpost') {
        $default = (empty($options['data']['fromname'])) ? $fromname : $options['data']['fromname'];
//        } else {
//            $default = (empty($options['data']['fromname'])) ? $user->getFirstName().' '.$user->getLastName() : $options['data']['fromname'];
//        }

        $builder->add(
            'fromname',
            'text',
            [
                'label'      => 'le.lead.email.from_name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class'     => 'form-control le-input',
                'disabled'                   => false,
                ],
                'required'    => true,
                'data'        => $default,
                'constraints' => [
                    new NotBlank([
                        'message' => 'le.lead.from_name.required',
                    ]),
                ],
            ]
        );

//        if ($emailProvider == 'Sparkpost') {
        $default = (empty($options['data']['from'])) ? $fromemail : $options['data']['from'];
//        } else {
//            $default = (empty($options['data']['from'])) ? $user->getEmail() : $options['data']['from'];
//        }

        $builder->add(
            'from',
            'text',
            [
                'label'       => 'le.lead.email.from_email',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class'   => 'form-control le-input'],
                'required'    => true,
                'data'        => $default,
                'constraints' => [
                    new NotBlank([
                        'message' => 'le.core.email.required',
                    ]),
                    new Email([
                        'message' => 'le.core.email.required',
                    ]),
                    new EmailVerify(
                        [
                            'message' => 'le.email.verification.error',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'body',
            'textarea',
            [
                'label'      => 'le.email.form.body',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'                => 'form-control editor editor-basic-fullpage editor-builder-tokens editor-email',
                    'data-token-callback'  => 'email:getBuilderTokens',
                    'data-token-activator' => '{',
                ],
            ]
        );

        $builder->add('list', 'hidden');

        //if($this->factory->getUser()->isAdmin()) {
        $builder->add(
            'templates',
            'email_list',
            [
                'label'      => 'le.lead.email.template',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class'    => 'form-control le-input',
                    'onchange' => 'Le.getLeadEmailContent(this)',
                ],
                'multiple'   => false,
            ]
          );
        // }
        $builder->add('buttons', 'form_buttons', [
            'apply_text'  => false,
            'save_text'   => 'le.email.send',
            'save_class'  => 'le-btn-default',
            'save_icon'   => 'fa fa-send',
            'cancel_icon' => 'fa fa-times',
        ]);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'lead_quickemail';
    }
}
