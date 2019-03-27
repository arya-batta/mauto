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

/**
 * Class FbLeadAdsType.
 */
class FbLeadAdsType extends AbstractType
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
        $builder->add('fbpage', 'choice', [
            'choices'    => $this->getFbPageChoices(),
            'label'      => 'le.integration.fbleadads.page',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'    => 'form-control',
                'onchange' => 'Le.loadLeadAdsForm(this.value);',
            ],
            'required'           => false,
        ]);

        $formModifier = function (FormEvent $event, $eventName) {
            $this->buildLeadGenFilter($event, $eventName);
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

    public function buildLeadGenFilter(FormEvent $event, $eventName)
    {
        $data      = $event->getData();
        $form      = $event->getForm();
        $options   = $form->getConfig()->getOptions();
        $fbPageId  = '';
        if (isset($options['fbpage'])) {
            $fbPageId = $options['fbpage'];
        }
        if (isset($data['fbpage'])) {
            $fbPageId = $data['fbpage'];
        }
        $integrationHelper    = $this->factory->getHelper('integration');
        $fbapiHelper          = $this->factory->getHelper('fbapi');
        $integrationsettings  =$integrationHelper->getIntegrationSettingsbyName('facebook_lead_ads');
        $leadgenformlist['-1']='Any Form';
        if (sizeof($integrationsettings) > 0 && !empty($fbPageId)) {
            if ($fbPageId != '-1') {
                $pageToken   =$fbapiHelper->getPageAccessToken($fbPageId, $integrationsettings['authtoken']);
                $leadGenForms=$fbapiHelper->getLeadGenFormsByPage($fbPageId, $pageToken);
                foreach ($leadGenForms as $leadGenForm) {
                    $leadgenformlist[$leadGenForm[0]]=$leadGenForm[1];
                }
            }
        }
        $form->add('leadgenform', 'choice', [
            'choices'    => $leadgenformlist,
            'label'      => 'le.integration.fbleadads.leadform',
            'label_attr' => ['class' => 'control-label'],
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
        return 'fb_leadads_list';
    }

    public function getFbPageChoices()
    {
        $integrationHelper  = $this->factory->getHelper('integration');
        $fbapiHelper        = $this->factory->getHelper('fbapi');
        $integrationsettings=$integrationHelper->getIntegrationSettingsbyName('facebook_lead_ads');
        $choices            =[];
        $choices['-1']      ='Any Page';
        if (sizeof($integrationsettings) > 0) {
            $pageList     =$fbapiHelper->getAllFbPages($integrationsettings['authtoken'], true);
            foreach ($pageList as $page) {
                $choices[$page[0]]=$page[1];
            }
        }

        return $choices;
    }
}
