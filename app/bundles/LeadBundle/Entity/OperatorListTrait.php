<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Entity;

trait OperatorListTrait
{
    protected $typeOperators = [
        'text' => [
            'include' => [
                '=',
                '!=',
                'empty',
                '!empty',
                'like',
                '!like',
            ],
        ],
        'select' => [
            'include' => [
                '=',
                '!=',
                //'empty',
                //'!empty',
                //'regexp',
                //'!regexp',
                'in',
                '!in',
            ],
        ],
        'bool' => [
            'include' => [
                '=',
                '!=',
            ],
        ],
        'email_dnc' => [
            'include' => [
                '=',
            ],
        ],
        'default' => [
            'exclude' => [
                'in',
                '!in',
                'date',
                'regexp',
                '!regexp',
                'startsWith',
                'endsWith',
                'contains',
                'today',
                'tomorrow',
                'yesterday',
                'week_last',
                'week_next',
                'week_this',
                'last_03',
                'next_03',
                'last_07',
                'next_07',
                'last_15',
                'next_15',
                'last_30',
                'next_30',
                'last_60',
                'next_60',
                'last_90',
                'next_90',
                'month_last',
                'month_next',
                'month_this',
                'year_last',
                'year_next',
                'year_this',
                'year_till',
                'activity',
                'inactivity',
            ],
        ],
        'multiselect' => [
            'include' => [
                'in',
                '!in',
                //'empty',
                //'!empty',
            ],
        ],
        'date' => [
            'exclude' => [
                'in',
                '!in',
                'activity',
                'inactivity',
            ],
        ],
        'custmdate' => [
            'exclude' => [
                'in',
                '!in',
                'like',
                '!like',
                'regexp',
                '!regexp',
                'startsWith',
                'endsWith',
                'contains',
                'activity',
                'inactivity',
            ],
        ],
        'number'    => [
            'include' => [
                '=',
                '!=',
                'gt',
                'gte',
                'lt',
                'lte',
            ],
        ],
        'owner_id' => [
            'include' => [
                'empty',
                '!empty',
                'in',
                '!in',
            ],
        ],
        'pages'     => [
            'include' => [
                '=',
                '!=',
                'like',
                '!like',
            ],
        ],
        'selecttemplate' => [
            'include' => [
                'in',
                '!in',
            ],
        ],
        'emailactivity' => [
            'include' => [
                'activity',
                'inactivity',
            ],
        ],
        'country' => [
            'include' => [
                'empty',
                '!empty',
                'in',
                '!in',
            ],
        ],
        'region' => [
            'include' => [
                'empty',
                '!empty',
                'in',
                '!in',
            ],
        ],
        'landingpage_list' => [
            'include' => [
                'in',
                '!in',
            ],
        ],
        'score_type' => [
            'include' => [
                '=',
                '!=',
            ],
        ],
    ];

    protected $operatorOptions = [
        '=' => [
            'label'       => 'le.lead.list.form.operator.equals',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        '!=' => [
            'label'       => 'le.lead.list.form.operator.notequals',
            'expr'        => 'neq',
            'negate_expr' => 'eq',
        ],
        'gt' => [
            'label'       => 'le.lead.list.form.operator.greaterthan',
            'expr'        => 'gt',
            'negate_expr' => 'lt',
        ],
        'gte' => [
            'label'       => 'le.lead.list.form.operator.greaterthanequals',
            'expr'        => 'gte',
            'negate_expr' => 'lt',
        ],
        'lt' => [
            'label'       => 'le.lead.list.form.operator.lessthan',
            'expr'        => 'lt',
            'negate_expr' => 'gt',
        ],
        'lte' => [
            'label'       => 'le.lead.list.form.operator.lessthanequals',
            'expr'        => 'lte',
            'negate_expr' => 'gt',
        ],
        'like' => [
            'label'       => 'le.lead.list.form.operator.contains',
            'expr'        => 'like',
            'negate_expr' => 'notLike',
        ],
        '!like' => [
            'label'       => 'le.lead.list.form.operator.notcontains',
            'expr'        => 'notLike',
            'negate_expr' => 'like',
        ],
        'between' => [
            'label'       => 'le.lead.list.form.operator.between',
            'expr'        => 'between', //special case
            'negate_expr' => 'notBetween',
            // @todo implement in list UI
            'hide' => true,
        ],
        '!between' => [
            'label'       => 'le.lead.list.form.operator.notbetween',
            'expr'        => 'notBetween', //special case
            'negate_expr' => 'between',
            // @todo implement in list UI
            'hide' => true,
        ],
        'in' => [
            'label'       => 'le.lead.list.form.operator.in',
            'expr'        => 'in',
            'negate_expr' => 'notIn',
        ],
        '!in' => [
            'label'       => 'le.lead.list.form.operator.notin',
            'expr'        => 'notIn',
            'negate_expr' => 'in',
        ],
        'regexp' => [
            'label'       => 'le.lead.list.form.operator.regexp',
            'expr'        => 'regexp', //special case
            'negate_expr' => 'notRegexp',
        ],
        '!regexp' => [
            'label'       => 'le.lead.list.form.operator.notregexp',
            'expr'        => 'notRegexp', //special case
            'negate_expr' => 'regexp',
        ],
        'date' => [
            'label'       => 'le.lead.list.form.operator.date',
            'expr'        => 'date', //special case
            'negate_expr' => 'date',
            'hide'        => true,
        ],
        'startsWith' => [
            'label'       => 'mautic.core.operator.starts.with',
            'expr'        => 'startsWith',
            'negate_expr' => 'startsWith',
        ],
        'endsWith' => [
            'label'       => 'mautic.core.operator.ends.with',
            'expr'        => 'endsWith',
            'negate_expr' => 'endsWith',
        ],
        'contains' => [
            'label'       => 'mautic.core.operator.contains',
            'expr'        => 'contains',
            'negate_expr' => 'contains',
        ],
        'activity' => [
            'label'       => 'le.lead.list.form.operator.activity',
            'expr'        => 'in',
            'negate_expr' => 'notIn',
        ],
        'inactivity' => [
            'label'       => 'le.lead.list.form.operator.inactivity',
            'expr'        => 'notIn',
            'negate_expr' => 'in',
        ],

        'last_03'   => [
            'label'       => 'le.core.operator.last_03',
            'expr'        => 'gte',
            'negate_expr' => 'lte',
        ],
        'next_03'   => [
            'label'       => 'le.core.operator.next_03',
            'expr'        => 'lte',
            'negate_expr' => 'gte',
        ],
        'last_07'   => [
            'label'       => 'le.core.operator.last_07',
            'expr'        => 'gte',
            'negate_expr' => 'lte',
        ],
        'next_07'   => [
            'label'       => 'le.core.operator.next_07',
            'expr'        => 'lte',
            'negate_expr' => 'gte',
        ],
        'last_15'   => [
            'label'       => 'le.core.operator.last_15',
            'expr'        => 'gte',
            'negate_expr' => 'lte',
        ],
        'next_15'   => [
            'label'       => 'le.core.operator.next_15',
            'expr'        => 'lte',
            'negate_expr' => 'gte',
        ],
        'last_30'   => [
            'label'       => 'le.core.operator.last_30',
            'expr'        => 'gte',
            'negate_expr' => 'lte',
        ],
        'next_30'   => [
            'label'       => 'le.core.operator.next_30',
            'expr'        => 'lte',
            'negate_expr' => 'gte',
        ],
        'last_60'   => [
            'label'       => 'le.core.operator.last_60',
            'expr'        => 'gte',
            'negate_expr' => 'lte',
        ],
        'next_60'   => [
            'label'       => 'le.core.operator.next_60',
            'expr'        => 'lte',
            'negate_expr' => 'gte',
        ],
        'last_90'   => [
            'label'       => 'le.core.operator.last_90',
            'expr'        => 'gte',
            'negate_expr' => 'lte',
        ],
        'next_90'   => [
            'label'       => 'le.core.operator.next_90',
            'expr'        => 'lte',
            'negate_expr' => 'gte',
        ],
        'today' => [
            'label'       => 'le.core.operator.today',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'tomorrow' => [
            'label'       => 'le.core.operator.tomorrow',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'yesterday' => [
            'label'       => 'le.core.operator.yesterday',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'week_last' => [
            'label'       => 'le.core.operator.week_last',
            'expr'        => 'gte',
            'negate_expr' => 'lte',
        ],
        'week_next' => [
            'label'       => 'le.core.operator.week_next',
            'expr'        => 'lte',
            'negate_expr' => 'gte',
        ],
        'week_this' => [
            'label'       => 'le.core.operator.week_this',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'month_last' => [
            'label'       => 'le.core.operator.month_last',
            'expr'        => 'gte',
            'negate_expr' => 'lte',
        ],
        'month_next' => [
            'label'       => 'le.core.operator.month_next',
            'expr'        => 'lte',
            'negate_expr' => 'gte',
        ],
        'month_this' => [
            'label'       => 'le.core.operator.month_this',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'year_till' => [
            'label'       => 'le.core.operator.year_till',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'year_last' => [
            'label'       => 'le.core.operator.year_last',
            'expr'        => 'gte',
            'negate_expr' => 'lte',
        ],
        'year_next' => [
            'label'       => 'le.core.operator.year_next',
            'expr'        => 'lte',
            'negate_expr' => 'gte',
        ],
        'year_this' => [
            'label'       => 'le.core.operator.year_this',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'empty' => [
            'label'       => 'le.lead.list.form.operator.isempty',
            'expr'        => 'empty', //special case
            'negate_expr' => 'notEmpty',
        ],
        '!empty' => [
            'label'       => 'le.lead.list.form.operator.isnotempty',
            'expr'        => 'notEmpty', //special case
            'negate_expr' => 'empty',
        ],
    ];

    /**
     * @param null $operator
     *
     * @return array
     */
    public function getFilterExpressionFunctions($operator = null)
    {
        return (null === $operator) ? $this->operatorOptions : $this->operatorOptions[$operator];
    }

    /**
     * @param null|string|array $type
     * @param array             $overrideHiddenTypes
     *
     * @return array
     */
    public function getOperatorsForFieldType($type = null, $overrideHiddenTypes = [])
    {
        static $processedTypes = [];

        if (is_array($type)) {
            return $this->getOperatorChoiceList($type, $overrideHiddenTypes);
        } elseif (array_key_exists($type, $processedTypes)) {
            return $processedTypes[$type];
        }

        $this->normalizeType($type);

        if (null === $type) {
            foreach ($this->typeOperators as $type => $def) {
                if (!array_key_exists($type, $processedTypes)) {
                    $processedTypes[$type] = $this->getOperatorChoiceList($def, $overrideHiddenTypes);
                }
            }

            return $processedTypes;
        }

        $processedTypes[$type] = $this->getOperatorChoiceList($this->typeOperators[$type], $overrideHiddenTypes);

        return $processedTypes[$type];
    }

    /**
     * @param       $definition
     * @param array $overrideHiddenOperators
     *
     * @return array
     */
    public function getOperatorChoiceList($definition, $overrideHiddenOperators = [])
    {
        static $operatorChoices = [];
        if (empty($operatorChoices)) {
            $operatorList    = $this->getFilterExpressionFunctions();
            $operatorChoices = [];
            foreach ($operatorList as $operator => $def) {
                if (empty($def['hide']) || in_array($operator, $overrideHiddenOperators)) {
                    $operatorChoices[$operator] = $def['label'];
                }
            }
        }

        $choices = $operatorChoices;
        if (isset($definition['include'])) {
            // Inclusive operators
            $choices = array_intersect_key($choices, array_flip($definition['include']));
        } elseif (isset($definition['exclude'])) {
            // Exclusive operators
            $choices = array_diff_key($choices, array_flip($definition['exclude']));
        }

        if (isset($this->translator)) {
            foreach ($choices as $value => $label) {
                $choices[$value] = $this->translator->trans($label);
            }
        }

        return $choices;
    }

    /**
     * Normalize type operator.
     *
     * @param $type
     */
    protected function normalizeType(&$type)
    {
        if (null === $type) {
            return;
        }

        if ($type === 'boolean') {
            $type = 'bool';
        } elseif (in_array($type, ['country', 'timezone', 'region', 'locale'])) {
            $type = 'select';
        } elseif (in_array($type, ['lookup',  'text', 'email', 'url', 'email', 'tel'])) {
            $type = 'text';
        } elseif ($type === 'datetime') {
            $type = 'date';
        } elseif (!array_key_exists($type, $this->typeOperators)) {
            $type = 'default';
        }
    }

    /**
     * @param null $operator
     *
     * @return string
     */
    public function getOperatorLabel($operator = null)
    {
        return (isset($this->operatorOptions[$operator])) ? $this->operatorOptions[$operator]['label'] : '';
    }
}
