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
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CalendlyFormType.
 */
class CalendlyFormType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    protected $isauthorized = false;

    protected $factory;

    /**
     * ConfigType constructor.
     */
    public function __construct(TranslatorInterface $translator, MauticFactory $factory)
    {
        $this->translator = $translator;

        $this->factory = $factory;

        $integrationHelper    = $this->factory->getHelper('integration');
        $integrationsettings  =$integrationHelper->getIntegrationSettingsbyName('calendly');
        if (sizeof($integrationsettings) > 0) {
            $this->isauthorized = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'event_name',
            'text',
            [
                'attr'    => [
                    'class'       => 'form-control le-input',
                    'placeholder' => $this->translator->trans('le.integration.calendly.pagename.placeholder'),
                ],
                'label'       => 'le.integration.calendly.pagename',
                'required'    => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'calendly_type';
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['isauthorized'] = $this->isauthorized;
    }
}
