<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Model;

use Mautic\CoreBundle\Security\Permissions\CorePermissions;
use Mautic\FormBundle\Entity\Field;
use Mautic\ReportBundle\Event\ReportGeneratorEvent;
use Symfony\Component\Translation\TranslatorInterface;

class CompanyReportData
{
    /**
     * @var FieldModel
     */
    private $fieldModel;

    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var Security Interface
     */
    private $security;

    /**
     * CompanyReportData constructor.
     *
     * @param FieldModel          $fieldModel
     * @param TranslatorInterface $translator
     */
    public function __construct(FieldModel $fieldModel, TranslatorInterface $translator, CorePermissions $security)
    {
        $this->fieldModel = $fieldModel;
        $this->translator = $translator;
        $this->security   = $security;
    }

    /**
     * @return array
     */
    public function getCompanyData()
    {
        if (!$this->security->isAdmin()) {
            return [];
        }
        $companyColumns = $this->getCompanyColumns();
        $companyFields  = $this->fieldModel->getEntities([
            'filter' => [
                'force' => [
                    [
                        'column' => 'f.object',
                        'expr'   => 'like',
                        'value'  => 'company',
                    ],
                ],
            ],
        ]);

        $companyColumns = array_merge($companyColumns, $this->getFieldColumns($companyFields, 'comp.'));

        return $companyColumns;
    }

    /**
     * @param ReportGeneratorEvent $event
     *
     * @return bool
     */
    public function eventHasCompanyColumns(ReportGeneratorEvent $event)
    {
        $companyColumns = $this->getCompanyData();
        foreach ($companyColumns as $key => $column) {
            if ($event->hasColumn($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    private function getCompanyColumns()
    {
        return [
            'comp.id' => [
                'label' => 'le.lead.report.company.company_id',
                'type'  => 'int',
                'link'  => 'le_company_action',
            ],
            'companies_lead.is_primary' => [
                'label' => 'le.lead.report.company.is_primary',
                'type'  => 'bool',
            ],
        ];
    }

    /**
     * @param Field[] $fields
     * @param string  $prefix
     *
     * @return array
     */
    private function getFieldColumns($fields, $prefix)
    {
        $columns = [];
        foreach ($fields as $f) {
            switch ($f->getType()) {
                case 'boolean':
                    $type = 'bool';
                    break;
                case 'date':
                    $type = 'date';
                    break;
                case 'datetime':
                    $type = 'datetime';
                    break;
                case 'time':
                    $type = 'time';
                    break;
                case 'url':
                    $type = 'url';
                    break;
                case 'email':
                    $type = 'email';
                    break;
                case 'number':
                    $type = 'float';
                    break;
                default:
                    $type = 'string';
                    break;
            }
            $columns[$prefix.$f->getAlias()] = [
                'label' => $this->translator->trans('le.report.field.company.label', ['%field%' => $f->getLabel()]),
                'type'  => $type,
            ];
        }

        return $columns;
    }
}
