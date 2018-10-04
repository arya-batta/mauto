<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\SubscriptionBundle\Controller;

use Mautic\CoreBundle\Controller\FormController as CommonFormController;

/**
 * Class PublicController.
 */
class PublicController extends CommonFormController
{
    /**
     * @param $id
     */
    public function viewinvoiceAction($id)
    {
        /** @var \Mautic\SubscriptionBundle\Model\BillingModel $billingmodel */
        $billingmodel  = $this->getModel('subscription.billinginfo');
        $billingrepo   = $billingmodel->getRepository();
        $billingentity = $billingrepo->findAll();
        if (sizeof($billingentity) > 0) {
            $billing = $billingentity[0]; //$model->getEntity(1);
        }
        $paymentrepository  =$this->get('le.subscription.repository.payment');
        $paymenthistory     = $paymentrepository->getEntity($id);

        $licensehelper = $this->get('mautic.helper.licenseinfo');
        $licenseinfo   = $licensehelper->getLicenseEntity();

        return $this->delegateView([
            'viewParameters' => [
                'billing' => $billing,
                'payment' => $paymenthistory,
                'license' => $licenseinfo,
            ],
            'contentTemplate' => 'MauticSubscriptionBundle:AccountInfo:view_invoice.html.php',
            'passthroughVars' => [
            ],
        ]);
    }
}
