<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SubscriptionBundle\Form\Type;

use Mautic\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Mautic\CoreBundle\Form\EventListener\FormExitSubscriber;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\LanguageHelper;
use Mautic\LeadBundle\Helper\FormFieldHelper;
use Mautic\ReportBundle\Model\ReportModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class AccountType.
 */
class AccountType extends AbstractType
{
    /**
     * @var ReportModel
     */
    private $reportModel;

    /**
     * @var bool|mixed
     */
    private $supportedLanguages;

    /**
     * @var LanguageHelper
     */
    private $langHelper;

    /**
     * Translator object.
     *
     * @var \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    private $translator;

    public function __construct(ReportModel $reportModel, TranslatorInterface $translator, LanguageHelper $languageHelper, CoreParametersHelper $parametersHelper)
    {
        $this->reportModel = $reportModel;
        $this->translator  = $translator;
        $this->langHelper  = $languageHelper;

        $languages   = $languageHelper->fetchLanguages(false, false);
        $langChoices = [];

        foreach ($languages as $code => $langData) {
            $langChoices[$code] = $langData['name'];
        }

        $langChoices = array_merge($langChoices, $parametersHelper->getParameter('supported_languages'));

        // Alpha sort the languages by name
        asort($langChoices);

        $this->supportedLanguages = $langChoices;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber());
        $builder->addEventSubscriber(new FormExitSubscriber('user.user', $options));

        $builder->add(
            'accountname',
            'text',
            [
                'label'      => 'leadsengage.subs.accountname',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control le-input',
                    'autocomplete' => 'off',
                ],
                'required'    => false,
            ]
        );

        $builder->add(
            'domainname',
            'text',
            [
                'label'       => 'leadsengage.subs.domainname',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control le-input', 'tabindex' => '-1', 'style' => 'pointer-events: none;background-color: #ebedf0;opacity: 1;'],
                'required'    => true,
            ]
        );

        $builder->add(
            'website',
            'text',
            [
                'label'      => 'leadsengage.subs.website',
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class'        => 'form-control le-input',
                    'autocomplete' => 'off',
                ],
                'required'    => false,
            ]
        );

        $builder->add(
            'email',
            'email',
            [
                'label'       => 'leadsengage.subs.accountemail',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control le-input'],
                'required'    => false,
            ]
        );

        $builder->add(
            'phonenumber',
            'text',
            [
                'label'       => 'leadsengage.subs.accountmobile',
                'label_attr'  => ['class' => 'control-label required'],
                'attr'        => ['class' => 'form-control le-input', 'type'  => 'number'],
                'required'    => false,
            ]
        );

        $builder->add(
            'currencysymbol',
            'text',
            [
                //'choices'    => $currencyChoices,
                'label'       => 'leadsengage.subs.accountcurrency',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control le-input', 'tabindex' => '-1', 'style' => 'pointer-events: none;background-color: #ebedf0;opacity: 1;'],
                'required'    => false,
            ]
        );
        $timezones = FormFieldHelper::getCustomTimezones();
        $builder->add(
            'timezone',
            'choice',
            [
                'choices'    => $timezones,
                'label'      => 'leadsengage.subs.accounttimezone',
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class'   => 'form-control accountTimezone',
                    'tooltip' => 'mautic.core.config.form.default.timezone.tooltip',
                ],
                'multiple'    => false,
                'empty_value' => '(GMT+05:30)-Asia/Kolkata',
                'empty_data'  => '',
                'data'        => $options['data']->getTimezone(),
                'required'    => false,
            ]
        );

        $builder->add(
            'accountid',
            'text',
            [
                'label'       => 'leadsengage.subs.accountid',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control le-input', 'tabindex' => '-1', 'style' => 'pointer-events: none;background-color: #ebedf0;opacity: 1;'],
                'required'    => false,
            ]
        );
        $ispoweredby = true;
        if (isset($options['isPoweredBy'])) {
            $ispoweredby = $options['isPoweredBy'];
        }

        $builder->add(
            'needpoweredby',
            'yesno_button_group',
            [
                'label'      => 'leadsengage.subs.accountpowered',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control le-input'],
                'disabled'   => $ispoweredby,
                'data'       => false,
            ]
        );

        $builder->add(
            'buttons',
            'form_buttons'
        );

        // Get the list of available languages
        $languages   = $this->langHelper->fetchLanguages(false, false);
        $langChoices = [];

        foreach ($languages as $code => $langData) {
            $langChoices[$code] = $langData['name'];
        }

        $langChoices = array_merge($langChoices, $this->supportedLanguages);

        // Alpha sort the languages by name
        asort($langChoices);
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'    => 'Mautic\SubscriptionBundle\Entity\Account',
                'isPoweredBy'   => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'accountinfo';
    }
}
