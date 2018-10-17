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

use Mautic\CoreBundle\Controller\FormController;
use Mautic\SubscriptionBundle\Entity\Account;
use Mautic\SubscriptionBundle\Entity\Billing;
use Mautic\SubscriptionBundle\Entity\KYC;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class KYCController.
 */
class KYCController extends FormController
{
    /**
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function signupAction()
    {
        /** @var \Mautic\UserBundle\Model\UserModel $usermodel */
        $usermodel     = $this->getModel('user.user');
        $userentity    = $usermodel->getCurrentUserEntity();

        $userform = $usermodel->createForm($userentity, $this->get('form.factory'));

        /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $model */
        $model         = $this->getModel('subscription.accountinfo');
        $accrepo       = $model->getRepository();
        $accountentity = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0]; //$model->getEntity(1);
        } else {
            $account = new Account();
        }
        $repository  =$this->get('le.core.repository.subscription');
        $signupinfo  =$repository->getSignupInfo($userentity->getEmail());
        if (!empty($signupinfo)) {
            $account->setPhonenumber($signupinfo[0]['f11']);
            $account->setDomainname($signupinfo[0]['f5']);
            $account->setAccountid($signupinfo[0]['appid']);
            $account->setAccountname($signupinfo[0]['f2']);
        }
        $form = $model->createForm($account, $this->get('form.factory'));

        /** @var \Mautic\SubscriptionBundle\Model\BillingModel $billingmodel */
        $billingmodel  = $this->getModel('subscription.billinginfo');
        $billingrepo   = $billingmodel->getRepository();
        $billingentity = $billingrepo->findAll();
        if (sizeof($billingentity) > 0) {
            $billing = $billingentity[0]; //$model->getEntity(1);
        } else {
            $billing = new Billing();
        }
        if (!empty($signupinfo)) {
            $billing->setCompanyname($signupinfo[0]['f2']);
        }
        $billform = $billingmodel->createForm($billing, $this->get('form.factory'), [], ['isBilling' => false]);

        if ($this->request->getMethod() == 'POST') {
            $data     = $this->request->request->get('billinginfo');

            $userdata = $this->request->request->get('user');

            $accountdata = $this->request->request->get('accountinfo');

            $firstName      = $userdata['firstName'];
            $lastName       = $userdata['lastName'];
            $email          = $userdata['email'];
            $userentity->setEmail($email);
            $userentity->setFirstName($firstName);
            $userentity->setLastName($lastName);
            $userentity->setTimezone($accountdata['timezone']);
            $usermodel->saveEntity($userentity);

            $companyname    = $data['companyname'];
            $companyaddress = $data['companyaddress'];
            $postalcode     = $data['postalcode'];
            $state          = $data['state'];
            $city           = $data['city'];
            $country        = $data['country'];
            $billing->setCompanyname($companyname);
            $billing->setAccountingemail($email);
            $billing->setCompanyaddress($companyaddress);
            $billing->setPostalcode($postalcode);
            $billing->setState($state);
            $billing->setCity($city);
            $billing->setCountry($country);
            $billingmodel->saveEntity($billing);

            $phonenumber    = $accountdata['phonenumber'];
            $timezone       = $accountdata['timezone'];
            $account->setTimezone($timezone);
            $account->setPhonenumber($phonenumber);
            $account->setEmail($email);
            /** @var \Mautic\CoreBundle\Configurator\Configurator $configurator */
            $configurator = $this->get('mautic.configurator');
            $isWritabale  = $configurator->isFileWritable();
            if ($isWritabale) {
                if ($timezone != '') {
                    $configurator->mergeParameters(['default_timezone' => $timezone]);
                    $configurator->write();
                }
                if ($companyaddress != '') {
                    $address = $companyaddress.','.$postalcode.','.$city.','.$state.','.$country;
                    $configurator->mergeParameters(['postal_address' => $address]);
                    $configurator->write();
                }
            }
            $dbname          = $this->coreParametersHelper->getParameter('db_name');
            $appid           = str_replace('leadsengage_apps', '', $dbname);
            $signuprepository=$this->get('le.core.repository.signup');
            $signuprepository->updateSignupInfo($accountdata, $data, $userdata, $appid);
            $model->saveEntity($account);
            $loginsession = $this->get('session');
            $loginsession->set('isLogin', false);

            return $this->delegateRedirect($this->generateUrl('mautic_contact_index'));
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'       => $form->createView(),
                    'billform'   => $billform->createView(),
                    'userform'   => $userform->createView(),
                    'actionRoute'=> 'mautic_kyc_action',
                    'typePrefix' => 'form',
                ],
                'contentTemplate' => 'MauticSubscriptionBundle:Subscription:step1.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_kyc_action',
                    'mauticContent' => 'kyc',
                    'route'         => $this->generateUrl(
                        'mautic_kyc_action',
                        [
                            'objectAction' => 'kyc',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function kycAction()
    {
        /** @var \Mautic\UserBundle\Model\UserModel $usermodel */
        $usermodel     = $this->getModel('user.user');
        $userentity    = $usermodel->getCurrentUserEntity();

        $form = $usermodel->createForm($userentity, $this->get('form.factory'));

        /** @var \Mautic\SubscriptionBundle\Model\KYCModel $kycmodel */
        $kycmodel         = $this->getModel('subscription.kycinfo');
        $kycrepo          = $kycmodel->getRepository();
        $kycentity        = $kycrepo->findAll();
        if (sizeof($kycentity) > 0) {
            $kyc = $kycentity[0]; //$model->getEntity(1);
        } else {
            $kyc = new KYC();
        }
        $kycform = $kycmodel->createForm($kyc, $this->get('form.factory'));

        if ($this->request->getMethod() == 'POST') {
            $data     = $this->request->request->get('kycinfo');

            $userdata = $this->request->request->get('user');

            $industry           = $data['industry'];
            $usercount          = $data['usercount'];
            $yearsactive        = $data['yearsactive'];
            $subscribercount    = $data['subscribercount'];
            $subscribersource   = $data['subscribersource'];
            $emailcontent       = $data['emailcontent'];
            $previoussoftware   = $data['previoussoftware'];
            $knowus             = $data['knowus'];
            $others             = $data['others'];

            $kyc->setIndustry($industry);
            $kyc->setUsercount($usercount);
            $kyc->setYearsactive($yearsactive);
            $kyc->setSubscribercount($subscribercount);
            $kyc->setSubscribersource($subscribersource);
            $kyc->setEmailcontent($emailcontent);
            $kyc->setPrevioussoftware($previoussoftware);
            $kyc->setKnowus($knowus);
            $kyc->setOthers($others);
            $kyc->setConditionsagree(0);

            $kycmodel->saveEntity($kyc);

            $signuprepository=$this->get('le.core.repository.signup');
            $signuprepository->updateKYCInfo($data, $userdata);
            $redirectUrl = $this->generateUrl('mautic_kyc_action', ['objectAction' => 'condition']);

            return new RedirectResponse($redirectUrl);
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'kycform'    => $kycform->createView(),
                    'form'       => $form->createView(),
                    'headerTitle'=> 'KYC | Leadsengage',
                    'actionRoute'=> 'mautic_kyc_action',
                    'typePrefix' => 'form',
                ],
                'contentTemplate' => 'MauticSubscriptionBundle:Subscription:step2.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_kyc_action',
                    'mauticContent' => 'kyc',
                    'route'         => $this->generateUrl(
                        'mautic_kyc_action',
                        [
                            'objectAction' => 'kyc',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function conditionAction()
    {
        /** @var \Mautic\SubscriptionBundle\Model\KYCModel $kycmodel */
        $kycmodel         = $this->getModel('subscription.kycinfo');
        $kycrepo          = $kycmodel->getRepository();
        $kycentity        = $kycrepo->findAll();
        if (sizeof($kycentity) > 0) {
            $kyc = $kycentity[0]; //$model->getEntity(1);
        } else {
            $kyc = new KYC();
        }
        $kycform          = $kycmodel->createForm($kyc, $this->get('form.factory'));
        if ($this->request->getMethod() == 'POST') {
            $data            = $this->request->request->get('kycinfo');
            $conditionsagree = $data['conditionsagree'];
            $kyc->setConditionsagree(1);

            $kycmodel->saveEntity($kyc);
            $loginsession = $this->get('session');
            $loginsession->set('isLogin', true);

            return $this->delegateRedirect($this->generateUrl('mautic_contact_index'));
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'kycform'    => $kycform->createView(),
                    'headerTitle'=> 'KYC | Leadsengage',
                    'actionRoute'=> 'mautic_kyc_action',
                    'typePrefix' => 'form',
                ],
                'contentTemplate' => 'MauticSubscriptionBundle:Subscription:step3.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_kyc_action',
                    'mauticContent' => 'condition',
                    'route'         => $this->generateUrl(
                        'mautic_kyc_action',
                        [
                            'objectAction' => 'condition',
                        ]
                    ),
                ],
            ]
        );
    }
}
