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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class FbCustomAudienceType.
 */
class FbCustomAudienceType extends AbstractType
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
        $integrationsettings  =$integrationHelper->getIntegrationSettingsbyName('facebook_custom_audiences');
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
        $builder->add('adaccount', 'choice', [
            'choices'    => $this->getAdAccountsChoices(),
            'label'      => 'le.integration.fbcustomaudience.adaccount',
            'label_attr' => ['class' => 'control-label required'],
            'attr'       => [
                'class'    => 'form-control',
                'onchange' => 'Le.loadCustomAudiences(this.value);',
            ],
            'required'           => false,
        ]);

        $formModifier = function (FormEvent $event, $eventName) {
            $this->buildCustomAudienceFilter($event, $eventName);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $formModifier($event, FormEvents::PRE_SET_DATA);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $formModifier($event, FormEvents::PRE_SUBMIT);
            }
        );
    }

    public function buildCustomAudienceFilter(FormEvent $event, $eventName)
    {
        $data      = $event->getData();
        $form      = $event->getForm();
        $options   = $form->getConfig()->getOptions();
        $adAccount = '';
        if (isset($options['adaccount'])) {
            $adAccount = $options['adaccount'];
        }
        if (isset($data['adaccount'])) {
            $adAccount = $data['adaccount'];
        }
        $integrationHelper  = $this->factory->getHelper('integration');
        $fbapiHelper        = $this->factory->getHelper('fbapi');
        $integrationsettings=$integrationHelper->getIntegrationSettingsbyName('facebook_custom_audiences');
        $audienceList       =[];
        if (sizeof($integrationsettings) > 0) {
            $fbapiHelper->initFBAdsApi($integrationsettings['authtoken']);
            if (!empty($adAccount)) {
                $audiencemapping=$fbapiHelper->getAudienceListByAdAccount($adAccount);
                foreach ($audiencemapping as $key => $value) {
                    $audienceList[$value]=$key;
                }
            }
        }
        $form->add('customaudience', 'choice', [
            'choices'    => $audienceList,
            'label'      => 'le.integration.fbcustomaudience.customaudience',
            'label_attr' => ['class' => 'control-label required'],
            'attr'       => [
                'class'   => 'form-control',
            ],
            'required'           => false,
        ]);

        if ($eventName == FormEvents::PRE_SUBMIT) {
            $event->setData($data);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'fb_custom_audience_list';
    }

    public function getAdAccountsChoices()
    {
        $integrationHelper  = $this->factory->getHelper('integration');
        $fbapiHelper        = $this->factory->getHelper('fbapi');
        $integrationsettings=$integrationHelper->getIntegrationSettingsbyName('facebook_custom_audiences');
        $choices            =[];
        if (sizeof($integrationsettings) > 0) {
            $adaccounts=$fbapiHelper->getAllAdAccounts($integrationsettings['authtoken']);
            foreach ($adaccounts as $adaccount) {
                $choices[$adaccount['id']]=$adaccount['name'];
            }
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['isauthorized'] = $this->isauthorized;
    }
}
