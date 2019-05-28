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
                    'hot'      => 'le.lead.lead.scoretype.hot',
                    'warm'     => 'le.lead.lead.scoretype.warm',
                    'cold'     => 'le.lead.lead.scoretype.cold',
                ],
                'label'       => 'le.campaign.lead.event.score',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class'    => 'form-control',
                                   'tooltip' => 'le.point.change.score.desc',
                                 ],
                'data'        => (isset($options['data']['score'])) ? $options['data']['score'] : 'cold',
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
