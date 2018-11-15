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
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class KYCType.
 */
class KYCType extends AbstractType
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

        $choices = FormFieldHelper::getFeedbackChoices();

        $builder->add(
            'industry',
            'choice',
            [
                'choices'    => $choices['Industry'],
                'label'      => 'leadsengage.kyc.industry',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'autocomplete' => 'off',
                ],
                'empty_value' => '',
                'data'        => $options['data']->getIndustry(),
                'required'    => true,
            ]
        );

        $builder->add(
            'usercount',
            'choice',
            [
                'choices'    => $choices['UserCount'],
                'label'      => 'leadsengage.kyc.usercount',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'autocomplete' => 'off',
                ],
                'empty_value' => '',
                'data'        => $options['data']->getUsercount(),
                'required'    => true,
            ]
        );

        $builder->add(
            'yearsactive',
            'choice',
            [
                'choices'    => $choices['OrganisationYears'],
                'label'      => 'leadsengage.kyc.yearsactive',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'autocomplete' => 'off',
                ],
                'empty_value' => '',
                'data'        => $options['data']->getYearsactive(),
                'required'    => true,
            ]
        );

        $builder->add(
            'subscribercount',
            'choice',
            [
                'choices'    => $choices['SubscriberCount'],
                'label'      => 'leadsengage.kyc.subscriberscount',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'autocomplete' => 'off',
                ],
                'empty_value' => '',
                'data'        => $options['data']->getSubscribercount(),
                'required'    => true,
            ]
        );

        $builder->add(
            'subscribersource',
            'choice',
            [
                'choices'     => $choices['MarketingGoal'],
                'label'       => 'leadsengage.kyc.subscribersource',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'empty_value' => '',
                'data'        => $options['data']->getSubscribersource(),
                'required'    => true,
            ]
        );

        $builder->add(
            'previoussoftware',
            'choice',
            [
                'choices'    => $choices['MarketingSoftware'],
                'label'      => 'leadsengage.kyc.previoussoftware',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'autocomplete' => 'off',
                    'onchange'     => 'Le.showMarketingOthersField(this.value);',
                ],
                'empty_value' => '',
                'data'        => $options['data']->getPrevioussoftware(),
                'required'    => true,
            ]
        );

        $builder->add(
            'knowus',
            'choice',
            [
                'choices'    => $choices['HowDoYouKnow'],
                'label'      => 'leadsengage.kyc.knowus',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'autocomplete' => 'off',
                    'onchange'     => 'Le.showOthersField(this.value);',
                ],
                'empty_value' => '',
                'data'        => $options['data']->getKnowus(),
                'required'    => true,
            ]
        );

        $builder->add(
            'others',
            'text',
            [
                'label'      => 'leadsengage.kyc.others',
                'label_attr' => [
                    'class' => 'control-label',
                    'id'    => 'kycinfo_others_label',
                    'style' => 'display:none;',
                ],
                'attr'       => [
                    'class'        => 'form-control',
                    'autocomplete' => 'off',
                    'style'        => 'display:none;',
                ],
                'data'        => $options['data']->getOthers(),
                'required'    => false,
            ]
        );

        $builder->add(
            'emailcontent',
            'text',
            [
                'label'       => 'leadsengage.kyc.marketing.others',
                'label_attr'  => [
                    'class' => 'control-label',
                    'id'    => 'marketing_others_label',
                    'style' => 'display:none;',
                ],
                'attr'       => [
                    'class'        => 'form-control',
                    'autocomplete' => 'off',
                    'style'        => 'display:none;',
                ],
                'data'        => $options['data']->getEmailcontent(),
                'required'    => false,
            ]
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'kycinfo';
    }
}
