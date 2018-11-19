<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Mautic\CoreBundle\Helper\UserHelper;

/**
 * Class CampaignEventPageHitType.
 */
class CampaignEventPageHitType extends AbstractType
{

    public  $isadmin;
    /**
     * @param UserHelper    $userHelper
     */
    public function _construct( UserHelper $userHelper){
        $this->isadmin = $userHelper->getUser()->isAdmin();
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($this->isadmin){
        $builder->add('pages', 'page_list', [
            'label'      => 'le.page.campaign.event.form.pages',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'   => 'form-control le-input',
                'tooltip' => 'le.page.campaign.event.form.pages.descr',
            ],
            'required'   => false,
        ]);
        }
        $builder->add('url', 'text', [
            'label'      => 'le.page.campaign.event.form.url',
            'label_attr' => ['class' => 'control-label'],
            'required'   => false,
            'attr'       => [
                'class'   => 'form-control le-input',
                'tooltip' => 'le.page.campaign.event.form.url.descr',
            ],
        ]);

        $builder->add('referer', 'text', [
            'label'      => 'le.page.campaign.event.form.referer',
            'label_attr' => ['class' => 'control-label'],
            'required'   => false,
            'attr'       => [
                'class'   => 'form-control le-input',
                'tooltip' => 'le.page.campaign.event.form.referer.descr',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'campaignevent_pagehit';
    }
}
