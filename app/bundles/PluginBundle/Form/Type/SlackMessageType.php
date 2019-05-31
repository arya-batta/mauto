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
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class SlackMessageType.
 */
class SlackMessageType extends AbstractType
{
    protected $factory;

    protected $isauthorized = false;

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->factory = $factory;

        $integrationHelper    = $this->factory->getHelper('integration');
        $integrationsettings  =$integrationHelper->getIntegrationSettingsbyName('slack');
        if (sizeof($integrationsettings) > 0) {
            $this->isauthorized = true;
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $integrationHelper  = $this->factory->getHelper('integration');
        $slackHelper        = $this->factory->getHelper('slack');
        $integrationsettings=$integrationHelper->getIntegrationSettingsbyName('slack');
        $channelList        =[];
        if (sizeof($integrationsettings) > 0) {
            $channelList = $slackHelper->getChannelList($integrationsettings['authtoken']);
        }

        $builder->add('channellist', 'choice', [
            'choices'    => $channelList,
            'label'      => 'le.integration.slack.channel',
            'label_attr' => ['class' => 'control-label required'],
            'attr'       => [
                'class'    => 'form-control',
            ],
            'required'           => true,
        ]);

        $builder->add(
            'slacklist',
            'slack_list',
            [
                'label'      => 'le.integration.slack.slackmessage',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                ],
                'multiple'    => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'mautic.plugin.slack.chooseslack.notblank']
                    ),
                ],
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'slack_message_list';
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['isauthorized'] = $this->isauthorized;
    }
}
