<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Form\Type;

use Doctrine\DBAL\Connection;
use Mautic\LeadBundle\Entity\RegexTrait;
use Mautic\LeadBundle\Helper\FormFieldHelper;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait FilterTrait
{
    use RegexTrait;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param                     $eventName
     * @param FormEvent           $event
     * @param TranslatorInterface $translator
     */
    public function buildFiltersForm($eventName, FormEvent $event, TranslatorInterface $translator, $currentListId = null)
    {
        $data      = $event->getData();
        $form      = $event->getForm();
        $options   = $form->getConfig()->getOptions();
        $fieldType = $data['type'];
        $fieldName = $data['field'];

        $type = 'text';
        $attr = [
            'class' => 'form-control le-input',
        ];
        $displayType = 'hidden';
        $displayAttr = [];

        $field = [];

        if (isset($options['fields']['lead'][$fieldName])) {
            $field = $options['fields']['lead'][$fieldName];
        } elseif (isset($options['fields']['pages'][$fieldName])) {
            $field = $options['fields']['pages'][$fieldName];
        } elseif (isset($options['fields']['emails'][$fieldName])) {
            $field = $options['fields']['emails'][$fieldName];
        } elseif (isset($options['fields']['date_activity'][$fieldName])) {
            $field = $options['fields']['date_activity'][$fieldName];
        } elseif (isset($options['fields']['list_tags'][$fieldName])) {
            $field = $options['fields']['list_tags'][$fieldName];
        } elseif (isset($options['fields']['list_points'][$fieldName])) {
            $field = $options['fields']['list_points'][$fieldName];
        } elseif (isset($options['fields']['list_leadlist'][$fieldName])) {
            $field = $options['fields']['list_leadlist'][$fieldName];
        } elseif (isset($options['fields']['list_categories'][$fieldName])) {
            $field = $options['fields']['list_categories'][$fieldName];
        } elseif (isset($options['fields']['forms'][$fieldName])) {
            $field = $options['fields']['forms'][$fieldName];
        } elseif (isset($options['fields']['assets'][$fieldName])) {
            $field = $options['fields']['assets'][$fieldName];
        } /*elseif (isset($options['fields']['company'][$fieldName])) {
            $field = $options['fields']['company'][$fieldName];
        }*/

        $customOptions = [];
        switch ($fieldType) {
            case 'leadlist':
                if (!isset($data['filter'])) {
                    $data['filter'] = [];
                } elseif (!is_array($data['filter'])) {
                    $data['filter'] = [$data['filter']];
                }

                // Don't show the current list ID in the choices
                if (!empty($currentListId)) {
                    unset($options['lists'][$currentListId]);
                }

                $customOptions['choices']                   = $options['lists'];
                $customOptions['multiple']                  = true;
                $customOptions['choice_translation_domain'] = false;
                $type                                       = 'choice';
                break;
            case 'owner_id':
                if (!isset($data['filter'])) {
                    $data['filter'] = [];
                } elseif (!is_array($data['filter'])) {
                    $data['filter'] = [$data['filter']];
                }

                $customOptions['choices']                   = $options['users'];
                $customOptions['multiple']                  = true;
                $customOptions['choice_translation_domain'] = false;
                $type                                       = 'choice';
                break;
            case 'lead_email_received':
                if (!isset($data['filter'])) {
                    $data['filter'] = [];
                } elseif (!is_array($data['filter'])) {
                    $data['filter'] = [$data['filter']];
                }

                $customOptions['choices']                   = $options['emails'];
                $customOptions['multiple']                  = true;
                $customOptions['choice_translation_domain'] = false;
                $type                                       = 'choice';
                break;
            case 'landingpage_list':
                if (!isset($data['filter'])) {
                    $data['filter'] = [];
                } elseif (!is_array($data['filter'])) {
                    $data['filter'] = $data['filter'];
                }

                $customOptions['choices']                   = $options['landingpage_list'];
                $customOptions['multiple']                  = true;
                $customOptions['choice_translation_domain'] = false;
                $type                                       = 'choice';
                break;
            case 'score_list':
                if (!isset($data['filter'])) {
                    $data['filter'] = [];
                } elseif (!is_array($data['filter'])) {
                    $data['filter'] = $data['filter'];
                }

                $customOptions['choices']                   = $options['score_list'];
                $customOptions['multiple']                  = false;
                $customOptions['choice_translation_domain'] = false;
                $type                                       = 'choice';
                break;
            case 'formsubmit_list':
                if (!isset($data['filter'])) {
                    $data['filter'] = [];
                } elseif (!is_array($data['filter'])) {
                    $data['filter'] = $data['filter'];
                }

                $customOptions['choices']                   = $options['formsubmit_list'];
                $customOptions['multiple']                  = true;
                $customOptions['choice_translation_domain'] = false;
                $type                                       = 'choice';
                break;
            case 'asset_downloads_list':
                if (!isset($data['filter'])) {
                    $data['filter'] = [];
                } elseif (!is_array($data['filter'])) {
                    $data['filter'] = $data['filter'];
                }

                $customOptions['choices']                   = $options['asset_downloads_list'];
                $customOptions['multiple']                  = true;
                $customOptions['choice_translation_domain'] = false;
                $type                                       = 'choice';
                break;
            case 'device_type':
                if (!isset($data['filter'])) {
                    $data['filter'] = [];
                } elseif (!is_array($data['filter'])) {
                    $data['filter'] = [$data['filter']];
                }

                $customOptions['choices']  = $options['deviceTypes'];
                $customOptions['multiple'] = true;
                $type                      = 'choice';
                break;
            case 'device_brand':
                if (!isset($data['filter'])) {
                    $data['filter'] = [];
                } elseif (!is_array($data['filter'])) {
                    $data['filter'] = [$data['filter']];
                }

                $customOptions['choices']  = $options['deviceBrands'];
                $customOptions['multiple'] = true;
                $type                      = 'choice';
                break;
            case 'device_os':
                if (!isset($data['filter'])) {
                    $data['filter'] = [];
                } elseif (!is_array($data['filter'])) {
                    $data['filter'] = [$data['filter']];
                }

                $customOptions['choices']  = $options['deviceOs'];
                $customOptions['multiple'] = true;
                $type                      = 'choice';
                break;
            case 'tags':
                if (!isset($data['filter'])) {
                    $data['filter'] = [];
                } elseif (!is_array($data['filter'])) {
                    $data['filter'] = [$data['filter']];
                }
                $customOptions['choices']                   = $options['tags'];
                $customOptions['multiple']                  = true;
                $customOptions['choice_translation_domain'] = false;
                $attr                                       = array_merge(
                    $attr,
                    [
                        'data-placeholder'     => $translator->trans('le.lead.tags.select_or_create'),
                        'data-no-results-text' => $translator->trans('le.lead.tags.enter_to_create'),
                        'data-allow-add'       => 'true',
                        'onchange'             => 'Le.createLeadTag(this)',
                    ]
                );
                $type = 'choice';
                break;
            case 'stage':
                $customOptions['choices']                   = $options['stage'];
                $customOptions['choice_translation_domain'] = false;
                $type                                       = 'choice';
                break;
            case 'globalcategory':
                if (!isset($data['filter'])) {
                    $data['filter'] = [];
                } elseif (!is_array($data['filter'])) {
                    $data['filter'] = [$data['filter']];
                }
                $customOptions['choices']  = $options['globalcategory'];
                $customOptions['multiple'] = true;
                $type                      = 'choice';
                break;
            case 'timezone':
            case 'country':
            case 'region':
            case 'locale':
                switch ($fieldType) {
                    case 'timezone':
                        $choiceKey = 'timezones';
                        break;
                    case 'country':
                        $choiceKey = 'countries';
                        break;
                    case 'region':
                        $choiceKey = 'regions';
                        break;
                    case 'locale':
                        $choiceKey = 'locales';
                        break;
                }

                $type                                       = 'choice';
                $customOptions['choices']                   = $options[$choiceKey];
                $customOptions['choice_translation_domain'] = false;
                $customOptions['multiple']                  = (in_array($data['operator'], ['in', '!in']));

                if ($customOptions['multiple']) {
                    array_unshift($customOptions['choices'], ['' => '']);

                    if (!isset($data['filter'])) {
                        $data['filter'] = [];
                    }
                }

                break;
            case 'time':
            case 'date':
            case 'datetime':
                $attr['data-toggle'] = $fieldType;
                break;
            case 'lookup_id':
                $type        = 'hidden';
                $displayType = 'text';
                $displayAttr = array_merge(
                    $displayAttr,
                    [
                        'class'                => 'form-control',
                        'data-toggle'          => 'field-lookup',
                        'data-target'          => $data['field'],
                        'data-action'          => 'lead:fieldList',
                        'data-lookup-callback' => 'updateLookupListFilter',
                        'placeholder'          => $translator->trans(
                            'le.lead.list.form.filtervalue'
                        ),
                    ]
                );

                if (isset($field['properties']['list'])) {
                    $displayAttr['data-options'] = $field['properties']['list'];
                }

                break;
            case 'number':
                $type = 'integer';
                if ($fieldName == 'sessions' || $fieldName == 'lead_email_read_count' || $fieldName == 'source_id') {
                    $attr = array_merge(
                        $attr,
                        [
                            'min' => '0',
                        ]
                    );
                }
                break;
            case 'select':
            case 'multiselect':
            case 'boolean':
                $type = 'choice';
                $attr = array_merge(
                    $attr,
                    [
                        'placeholder' => $translator->trans('le.lead.list.form.filtervalue'),
                    ]
                );

                if (in_array($data['operator'], ['in', '!in'])) {
                    $customOptions['multiple'] = true;
                    if (!isset($data['filter'])) {
                        $data['filter'] = [];
                    } elseif (!is_array($data['filter'])) {
                        $data['filter'] = [$data['filter']];
                    }
                }

                $choices = [];
                if (!empty($field['properties']['list'])) {
                    $list    = $field['properties']['list'];
                    $choices = FormFieldHelper::parseList($list, true, ('boolean' === $fieldType));
                }

                if ('select' == $fieldType) {
                    // array_unshift cannot be used because numeric values get lost as keys
                    $choices     = array_reverse($choices, true);
                    $choices[''] = '';
                    $choices     = array_reverse($choices, true);
                }

                $customOptions['choices']                   = $choices;
                $customOptions['choice_translation_domain'] = false;
                break;
            case 'lookup':
            default:
                if ('number' !== $fieldType) {
                    $attr = array_merge(
                        $attr,
                        [
                            'data-toggle' => 'field-lookup',
                            'data-target' => $data['field'],
                            'data-action' => 'lead:fieldList',
                            'placeholder' => $translator->trans('le.lead.list.form.filtervalue'),
                        ]
                    );

                    if (isset($field['properties']['list'])) {
                        $attr['data-options'] = $field['properties']['list'];
                    }
                }

                break;
        }

        $disOpr                       = ['today', 'tomorrow', 'yesterday', 'week_last', 'week_next', 'week_this','last_15', 'next_15', 'last_60', 'next_60', 'last_90', 'next_90', 'month_last', 'month_next', 'month_this', 'year_last', 'year_next', 'year_this'];
        $customOptions['constraints'] = [];
        if (in_array($data['operator'], ['empty', '!empty'])) {
            $attr['disabled'] = 'disabled';
        } elseif (in_array($data['operator'], $disOpr)) {
            $attr['style']    = 'pointer-events: none;background-color: #ebedf0;opacity: 1;';
            $attr['tabindex'] = '-1';
        } elseif ($data['operator']) {
            $customOptions['constraints'][] = new NotBlank(
                [
                    'message' => 'mautic.core.value.required',
                ]
            );

            if (in_array($data['operator'], ['regexp', '!regexp']) && $this->connection) {
                // Let's add a custom valdiator to test the regex
                $customOptions['constraints'][] =
                    new Callback(
                        function ($regex, ExecutionContextInterface $context) {
                            // Let's test the regex's syntax by making a fake query
                            try {
                                $qb = $this->connection->createQueryBuilder();
                                $qb->select('l.id')
                                    ->from(MAUTIC_TABLE_PREFIX.'leads', 'l')
                                    ->where('l.id REGEXP :regex')
                                    ->setParameter('regex', $this->prepareRegex($regex))
                                    ->setMaxResults(1);
                                $qb->execute()->fetchAll();
                            } catch (\Exception $exception) {
                                $context->buildViolation('mautic.core.regex.invalid')->addViolation();
                            }
                        }
                    );
            }
        }

        // @todo implement in UI
        if (in_array($data['operator'], ['between', '!between'])) {
            $form->add(
                'filter',
                'collection',
                [
                    'type'    => $type,
                    'options' => [
                        'label' => false,
                        'attr'  => $attr,
                    ],
                    'label' => false,
                ]
            );
        } else {
            if (!empty($customOptions['constraints'])) {
                foreach ($customOptions['constraints'] as $i => $constraint) {
                    if (get_class($constraint) === 'NotBlank') {
                        array_splice($customOptions['constraints'], $i, 1);
                    }
                }
            }
            $form->add(
                'filter',
                $type,
                array_merge(
                    [
                        'label'          => false,
                        'attr'           => $attr,
                        'data'           => isset($data['filter']) ? $data['filter'] : '',
                        'error_bubbling' => false,
                    ],
                    $customOptions
                )
            );
        }

        $form->add(
            'display',
            $displayType,
            [
                'label'          => false,
                'attr'           => $displayAttr,
                'data'           => (isset($data['display'])) ? $data['display'] : '',
                'error_bubbling' => false,
            ]
        );

        $form->add(
            'customObject',
            'hidden',
            [
                'label'          => false,
                'attr'           => [],
                'data'           => (isset($data['customObject'])) ? $data['customObject'] : '',
                'error_bubbling' => false,
            ]
        );

        $form->add(
            'fieldlabel',
            'hidden',
            [
                'label'          => false,
                'attr'           => [],
                'data'           => (isset($data['fieldlabel'])) ? $data['fieldlabel'] : '',
                'error_bubbling' => false,
            ]
        );

        $form->add(
            'operator',
            'choice',
            [
                'label'   => false,
                'choices' => isset($field['operators']) ? $field['operators'] : [],
                'attr'    => [
                    'class'    => 'form-control not-chosen le-input',
                    'onchange' => 'Le.convertLeadFilterInput(this)',
                ],
            ]
        );

        if ($eventName == FormEvents::PRE_SUBMIT) {
            $event->setData($data);
        }
    }
}
