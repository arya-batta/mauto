<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\AssetBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class CampaignEventAssetDownloadType.
 */
class CampaignEventAssetDownloadType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('assets', 'asset_list', [
            'label'      => 'mautic.asset.campaign.event.files',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'   => 'form-control',
                //'tooltip' => 'mautic.asset.campaign.event.assets.descr',
            ],
            'required'    => true,
            'constraints' => [
                new NotBlank(
                    [
                        'message' => 'mautic.core.value.required',
                    ]
                ),
            ],
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'campaignevent_assetdownload';
    }
}
