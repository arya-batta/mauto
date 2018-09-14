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
                'empty',
                '!empty',
                'regexp',
                '!regexp',
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
            ],
        ],
        'multiselect' => [
            'include' => [
                'in',
                '!in',
            ],
        ],
        'date' => [
            'exclude' => [
                'in',
                '!in',
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
        'lookup_id' => [
            'include' => [
                '=',
                '!=',
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
                'empty',
                '!empty',
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
    ];

    protected $operatorOptions = [
        '=' => [
            'label'       => 'mautic.lead.list.form.operator.equals',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        '!=' => [
            'label'       => 'mautic.lead.list.form.operator.notequals',
            'expr'        => 'neq',
            'negate_expr' => 'eq',
        ],
        'gt' => [
            'label'       => 'mautic.lead.list.form.operator.greaterthan',
            'expr'        => 'gt',
            'negate_expr' => 'lt',
        ],
        'gte' => [
            'label'       => 'mautic.lead.list.form.operator.greaterthanequals',
            'expr'        => 'gte',
            'negate_expr' => 'lt',
        ],
        'lt' => [
            'label'       => 'mautic.lead.list.form.operator.lessthan',
            'expr'        => 'lt',
            'negate_expr' => 'gt',
        ],
        'lte' => [
            'label'       => 'mautic.lead.list.form.operator.lessthanequals',
            'expr'        => 'lte',
            'negate_expr' => 'gt',
        ],
        'empty' => [
            'label'       => 'mautic.lead.list.form.operator.isempty',
            'expr'        => 'empty', //special case
            'negate_expr' => 'notEmpty',
        ],
        '!empty' => [
            'label'       => 'mautic.lead.list.form.operator.isnotempty',
            'expr'        => 'notEmpty', //special case
            'negate_expr' => 'empty',
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
            'label'       => 'mautic.lead.list.form.operator.between',
            'expr'        => 'between', //special case
            'negate_expr' => 'notBetween',
            // @todo implement in list UI
            'hide' => true,
        ],
        '!between' => [
            'label'       => 'mautic.lead.list.form.operator.notbetween',
            'expr'        => 'notBetween', //special case
            'negate_expr' => 'between',
            // @todo implement in list UI
            'hide' => true,
        ],
        'in' => [
            'label'       => 'mautic.lead.list.form.operator.in',
            'expr'        => 'in',
            'negate_expr' => 'notIn',
        ],
        '!in' => [
            'label'       => 'mautic.lead.list.form.operator.notin',
            'expr'        => 'notIn',
            'negate_expr' => 'in',
        ],
        'regexp' => [
            'label'       => 'mautic.lead.list.form.operator.regexp',
            'expr'        => 'regexp', //special case
            'negate_expr' => 'notRegexp',
        ],
        '!regexp' => [
            'label'       => 'mautic.lead.list.form.operator.notregexp',
            'expr'        => 'notRegexp', //special case
            'negate_expr' => 'regexp',
        ],
        'date' => [
            'label'       => 'mautic.lead.list.form.operator.date',
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
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'week_next' => [
            'label'       => 'le.core.operator.week_next',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'week_this' => [
            'label'       => 'le.core.operator.week_this',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'month_last' => [
            'label'       => 'le.core.operator.month_last',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'month_next' => [
            'label'       => 'le.core.operator.month_next',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'month_this' => [
            'label'       => 'le.core.operator.month_this',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'year_last' => [
            'label'       => 'le.core.operator.year_last',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'year_next' => [
            'label'       => 'le.core.operator.year_next',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
        ],
        'year_this' => [
            'label'       => 'le.core.operator.year_this',
            'expr'        => 'eq',
            'negate_expr' => 'neq',
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
