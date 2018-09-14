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
 * Class PointsActionType.
 */
class ScoreActionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'score',
            'choice',
            [
                'choices' => [
                    'hot'      => 'mautic.lead.lead.scoretype.hot',
                    'warm'     => 'mautic.lead.lead.scoretype.warm',
                    'cold'     => 'mautic.lead.lead.scoretype.cold',
                ],
                'label'       => 'mautic.campaign.lead.event.score',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'data'        => (isset($options['data']['score'])) ? $options['data']['score'] : 'Cold',
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'leadscore_action';
    }
}
