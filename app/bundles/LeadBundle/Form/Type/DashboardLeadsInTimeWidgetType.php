<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class DashboardLeadsInTimeWidgetType.
 */
class DashboardLeadsInTimeWidgetType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('flag', 'choice', [
                'label'   => 'le.lead.list.filter',
                'choices' => [
                    ''                         => 'le.lead.show.all',
                    'identified'               => 'le.lead.show.identified',
                    'anonymous'                => 'le.lead.show.anonymous',
                    'identifiedVsAnonymous'    => 'le.lead.show.identified.vs.anonymous',
                    'top'                      => 'le.lead.show.top',
                    'topIdentifiedVsAnonymous' => 'le.lead.show.top.leads.identified.vs.anonymous',
                ],
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'empty_data' => '',
                'required'   => false,
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'lead_dashboard_leads_in_time_widget';
    }
}
