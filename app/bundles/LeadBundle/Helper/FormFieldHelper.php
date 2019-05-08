<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Helper;

use Mautic\CoreBundle\Helper\AbstractFormFieldHelper;
use Symfony\Component\Intl\Intl;

class FormFieldHelper extends AbstractFormFieldHelper
{
    /**
     * @var array
     */
    private static $types = [
        'text' => [
            'properties' => [],
        ],
        'textarea' => [
            'properties' => [],
        ],
        'multiselect' => [
            'properties' => [
                'list' => [
                    'required'  => true,
                    'error_msg' => 'le.lead.field.select.listmissing',
                ],
            ],
        ],
        'select' => [
            'properties' => [
                'list' => [
                    'required'  => true,
                    'error_msg' => 'le.lead.field.select.listmissing',
                ],
            ],
        ],
        /**'boolean' => [
            'properties' => [
                'yes' => [
                    'required'  => true,
                    'error_msg' => 'le.lead.field.boolean.yesmissing',
                ],
                'no' => [
                    'required'  => true,
                    'error_msg' => 'le.lead.field.boolean.nomissing',
                ],
            ],
        ],
        'lookup' => [
            'properties' => [
                'list' => [],
            ],
        ],*/
        'date' => [
            'properties' => [
                'format' => [],
            ],
        ],
        'datetime' => [
            'properties' => [
                'format' => [],
            ],
        ],
        'time' => [
            'properties' => [],
        ],
        /**'timezone' => [
            'properties' => [],
        ],
        'email' => [
            'properties' => [],
        ],
        'number' => [
            'properties' => [
                'roundmode' => [],
                'precision' => [],
            ],
        ],
        'tel' => [
            'properties' => [],
        ],*/
        'url' => [
            'properties' => [],
        ],
       /* 'country' => [
            'properties' => [],
        ],
        'region' => [
            'properties' => [],
        ],
        'locale' => [
            'properties' => [],
        ],*/
        /*'le_currency' => [
            'properties' => [
                'roundmode' => [],
                'precision' => [],
            ],
        ],*/
    ];

    /**
     * Set the translation key prefix.
     */
    public function setTranslationKeyPrefix()
    {
        $this->translationKeyPrefix = 'le.lead.field.type.';
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return self::$types;
    }

    /**
     * @return array
     */
    public static function getListTypes()
    {
        return ['select', 'boolean', 'lookup', 'country', 'region', 'timezone', 'locale'];
    }

    /**
     * @param $type
     * @param $properties
     *
     * @return bool
     */
    public static function validateProperties($type, &$properties)
    {
        if (!array_key_exists($type, self::$types)) {
            //ensure the field type is supported
            return [false, 'le.lead.field.typenotrecognized'];
        }

        $fieldType = self::$types[$type];
        foreach ($properties as $key => $value) {
            if (!array_key_exists($key, $fieldType['properties'])) {
                unset($properties[$key]);
            }

            if (!empty($fieldType['properties'][$key]['required']) && empty($value)) {
                //ensure requirements are met
                return [false, $fieldType['properties'][$key]['error_msg']];
            }
        }

        return [true, ''];
    }

    /**
     * @return array
     */
    public static function getCountryChoices()
    {
        $countryJson = file_get_contents(__DIR__.'/../../CoreBundle/Assets/json/countries.json');
        $countries   = json_decode($countryJson);

        $choices = array_combine($countries, $countries);

        return $choices;
    }

    /**
     * @return array
     */
    public static function getRegionChoices()
    {
        $regionJson = file_get_contents(__DIR__.'/../../CoreBundle/Assets/json/regions.json');
        $regions    = json_decode($regionJson);

        $choices = [];
        foreach ($regions as $country => &$regionGroup) {
            $choices[$country] = array_combine($regionGroup, $regionGroup);
        }

        return $choices;
    }

    /**
     * @return array
     */
    public static function getFeedbackChoices()
    {
        $feedbackJson = file_get_contents(__DIR__.'/../../CoreBundle/Assets/json/feedback.json');
        $feedbacks    = json_decode($feedbackJson);

        $choices = [];
        foreach ($feedbacks as $property => &$propertyValue) {
            $choices[$property] = array_combine($propertyValue, $propertyValue);
        }

        return $choices;
    }

    /**
     * Symfony deprecated and changed Symfony\Component\Form\Extension\Core\Type\TimezoneType::getTimezones to private
     * in 3.0 - so duplicated code here.
     *
     * @return array
     */
    public static function getTimezonesChoices()
    {
        static $timezones;

        if (null === $timezones) {
            $timezones = [];

            foreach (\DateTimeZone::listIdentifiers() as $timezone) {
                $parts = explode('/', $timezone);

                if (count($parts) > 2) {
                    $region = $parts[0];
                    $name   = $parts[1].' - '.$parts[2];
                } elseif (count($parts) > 1) {
                    $region = $parts[0];
                    $name   = $parts[1];
                } else {
                    $region = 'Other';
                    $name   = $parts[0];
                }

                $timezones[$region][$timezone] = str_replace('_', ' ', $name);
            }
        }

        return $timezones;
    }

    /**
     * Symfony deprecated and changed Symfony\Component\Form\Extension\Core\Type\TimezoneType::getTimezones to private
     * in 3.0 - so duplicated code here.
     *
     * @return array
     */
    public static function getCustomTimezones()
    {
        $timezonesJson     = file_get_contents(__DIR__.'/../../CoreBundle/Assets/json/timezone.json');
        $timezones         = json_decode($timezonesJson);
        $timezonesnameJson = file_get_contents(__DIR__.'/../../CoreBundle/Assets/json/timezonename.json');
        $timezonesname     = json_decode($timezonesnameJson);

        $choices = array_combine($timezonesname, $timezones);

        return $choices;
    }

    /**
     * Get locale choices.
     *
     * @return array
     */
    public static function getLocaleChoices()
    {
        return Intl::getLocaleBundle()->getLocaleNames();
    }

    /**
     * Get date field choices.
     *
     * @return array
     */
    public function getDateChoices()
    {
        $options = [
            'anniversary' => $this->translator->trans('mautic.campaign.event.timed.choice.anniversary'),
            '+P0D'        => $this->translator->trans('mautic.campaign.event.timed.choice.today'),
            '-P1D'        => $this->translator->trans('mautic.campaign.event.timed.choice.yesterday'),
            '+P1D'        => $this->translator->trans('mautic.campaign.event.timed.choice.tomorrow'),
        ];

        return $options;
    }

    public function getCreatedSourceFields($index)
    {
        $sourceOptions    = [];
        $sourceOptions[1] = $this->translator->trans('le.leads.created.source.1');
        $sourceOptions[2] = $this->translator->trans('le.leads.created.source.2');
        $sourceOptions[3] = $this->translator->trans('le.leads.created.source.3');
        $sourceOptions[4] = $this->translator->trans('le.leads.created.source.4');
        $sourceOptions[5] = $this->translator->trans('le.leads.created.source.5');

        return $sourceOptions[$index];
    }

    public function getLeadStatus($index = 0)
    {
        $statusOptions    = [];
        $statusOptions[1] = $this->translator->trans('le.leads.status.1');
        $statusOptions[2] = $this->translator->trans('le.leads.status.2');
        $statusOptions[3] = $this->translator->trans('le.leads.status.3');
        $statusOptions[4] = $this->translator->trans('le.leads.status.4');
        $statusOptions[5] = $this->translator->trans('le.leads.status.5');
        $statusOptions[6] = $this->translator->trans('le.leads.status.6');

        if ($index == 0) {
            return $statusOptions;
        } elseif ($index == '') {
            return '';
        } else {
            return $statusOptions[$index];
        }
    }

    public function getStatusColors($index = 0)
    {
        $statuscolors = ['', '#3292e0', '#5cb45b', '#f7b543', '#f03154', '#f03154', '#f03154'];

        return $statuscolors[$index];
    }
}
