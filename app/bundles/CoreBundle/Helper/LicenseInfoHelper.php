<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 26/2/18
 * Time: 11:35 AM.
 */

namespace Mautic\CoreBundle\Helper;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\Entity\LicenseInfo;
use Mautic\CoreBundle\Entity\LicenseInfoRepository;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\SubscriptionBundle\Entity\Account;
use Mautic\SubscriptionBundle\Entity\Billing;

class LicenseInfoHelper
{
    private $em;

    /**
     * @var LicenseInfoRepository
     */
    private $licenseinfo;

    /**
     * @var MauticFactory
     */
    private $factory;

    /**
     * LicenseInfoHelper constructor.
     *
     * @param EntityManager         $entityManager
     * @param LicenseInfoRepository $licenseinforepository
     * @param MauticFactory         $factory
     */
    public function __construct(EntityManager $entityManager, LicenseInfoRepository $licenseinforepository, MauticFactory $factory)
    {
        $this->em         = $entityManager;
        $this->licenseinfo=$licenseinforepository;
        $this->factory    = $factory;
    }

    public function intRecordCount($totalRecordCount, $isSum)
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        if (!isset($totalRecordCount)) {
            $totalRecordCount = 0;
        }
        $previousValue=$entity->getActualRecordCount();
        if ($isSum) {
            $totalCountValue = $previousValue + $totalRecordCount;
        } else {
            $totalCountValue = $previousValue - $totalRecordCount;
        }

        $entity->setActualRecordCount($totalCountValue);

        $this->licenseinfo->saveEntity($entity);
    }

    public function intUserCount($totalUserCount, $isSum)
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        if (!isset($totalUserCount)) {
            $totalUserCount = 0;
        }

        $previousValue=$entity->getActiveUserCount();
        if ($isSum) {
            $totalUser = $previousValue + $totalUserCount;
        } else {
            $totalUser = $previousValue - $totalUserCount;
        }
        $entity->setActiveUserCount($totalUser);

        $this->licenseinfo->saveEntity($entity);
    }

    public function intAttachmentSize($attachmentSize, $isSum)
    {
        $data = $this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }

        if (!isset($attachmentSize)) {
            $attachmentSize = 0;
        }
        if (strpos(($attachmentSize), 'KB') !== false) {
            $sizeInBytes = $attachmentSize * 1024;
        } elseif (strpos(($attachmentSize), 'MB') !== false) {
            $sizeInBytes = $attachmentSize * 1048576;
        } else {
            $sizeInBytes = $attachmentSize;
        }

        $previousValue = $entity->getActualAttachementSize() * 1000000;

        if ($isSum) {
            $totalSize = $previousValue + $sizeInBytes;
        } else {
            $totalSize = $previousValue - $sizeInBytes;
        }

        $updateSizeInMb = number_format($totalSize / 1000000, 2);

        $entity->setActualAttachementSize($updateSizeInMb);

        $this->licenseinfo->saveEntity($entity);
    }

    public function intEmailCount($emailCount)
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        if (!isset($emailCount)) {
            $emailCount = 0;
        }

        $previousValue= $entity->getActualEmailCount();
        $totalSize    = $previousValue + $emailCount;
        $entity->setActualEmailCount($totalSize);

        $this->licenseinfo->saveEntity($entity);
    }

    public function intBounceCount($bounceCount)
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        if (!isset($bounceCount)) {
            $bounceCount = 0;
        }

        $previousValue= $entity->getBounceCount();
        $totalSize    = $previousValue + $bounceCount;
        $entity->setBounceCount($totalSize);

        $this->licenseinfo->saveEntity($entity);
    }

    public function intSpamCount($spamCount)
    {
        $data = $this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        if (!isset($spamCount)) {
            $spamCount = 0;
        }

        $previousValue= $entity->getSpamCount();
        $totalSize    = $previousValue + $spamCount;
        $entity->setSpamCount($totalSize);

        $this->licenseinfo->saveEntity($entity);
    }

    public function intDeleteCount($deleteCount, $sum)
    {
        $data = $this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        if (!isset($deleteCount)) {
            $deleteCount = 0;
        }
        $previousValue= $entity->getDeleteCount();
        if ($sum) {
            $totalCountValue = $previousValue + $deleteCount;
        } else {
            $totalCountValue = $deleteCount;
        }

        $entity->setDeleteCount($totalCountValue);

        $this->licenseinfo->saveEntity($entity);
    }

    public function intDeleteMonth($month)
    {
        $data = $this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        if (!isset($month)) {
            $month ='';
        }

        $entity->setDeleteMonth($month);

        $this->licenseinfo->saveEntity($entity);
    }

    public function getDeleteCount()
    {
        $data = $this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }

        $totalDeleteCount= $entity->getDeleteCount();

        return $totalDeleteCount;
    }

    public function getTotalRecordCount()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }

        $totalRecordCount= $entity->getTotalRecordCount();

        return $totalRecordCount;
    }

    public function isValidRecordAdd()
    {
        $lastpayment=$this->em->getRepository('Mautic\SubscriptionBundle\Entity\PaymentHistory')->getLastPayment();
        /*if ($lastpayment != null) {
            return true;
        }*/
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();
        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }

        $totalRecordCount  = $entity->getTotalRecordCount();
        $actualRecordCount = $entity->getActualRecordCount();

        if ($totalRecordCount == 'UL') {
            return true;
        } else {
            if ($totalRecordCount > $actualRecordCount) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getActualRecordCount()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        $actualRecordCount= $entity->getActualRecordCount();

        return $actualRecordCount;
    }

    public function getTotalUserCount()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        $actualRecordCount= $entity->getTotalUserCount();

        return $actualRecordCount;
    }

    public function getActiveUserCount()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        $actualRecordCount= $entity->getActiveUserCount();

        return $actualRecordCount;
    }

    public function isValidUserCount()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }

        $totalUserCOunt  = $entity->getTotalUserCount();
        $actualUserCount = $entity->getActiveUserCount();

        if ($totalUserCOunt == 'UL') {
            return true;
        } else {
            if ($totalUserCOunt > $actualUserCount) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getLicensedDays()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        $licensedDays= $entity->getLicensedDays();

        return $licensedDays;
    }

    public function getLicenseRemainingDays()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        $currentDate   =date('Y-m-d'); //, strtotime('- 1 day'));
        $licenseEnd    = $entity->getLicenseEnd();
        $licenseRemDays= $entity->getLicensedDays();

        if ($licenseRemDays == 'UL') {
            $licenseremdays = 7300;
        } else {
            $licenseremdays = round((strtotime($licenseEnd) - strtotime($currentDate)) / 86400);
        }

        return $licenseremdays;
    }

    public function getTotalAttachementSize()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        $totalAttachmentSize= $entity->getTotalAttachementSize();

        return $totalAttachmentSize;
    }

    public function getActualAttachementSize()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        $actualAttachmentSize= $entity->getActualAttachementSize();

        return $actualAttachmentSize;
    }

    public function getActualEmailCount()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        $actualEmailCount= $entity->getActualEmailCount();

        return $actualEmailCount;
    }

    public function getTotalEmailCount()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        $totalEmailCount= $entity->getTotalEmailCount();

        return $totalEmailCount;
    }

    public function isValidEmailCount()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }

        $totalEmailCount  = $entity->getTotalEmailCount();
        $actualEmailCount = $entity->getActualEmailCount();

        if ($totalEmailCount == 'UL') {
            return true;
        } else {
            if ($totalEmailCount > $actualEmailCount) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getTotalEmailUsage()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        $actualEmailCount = $entity->getActualEmailCount();
        $totalEmailCount  = $entity->getTotalEmailCount();

        if ($totalEmailCount == 'UL') {
            return $totalEmailCount;
        } else {
            if ($actualEmailCount > 0) {
                $emailUsageCount = ($actualEmailCount / $totalEmailCount) * 100;

                return $emailUsageCount;
            }
        }
    }

    public function getAppStatus()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }

        $appStatus  = $entity->getAppStatus();

        return $appStatus;
    }

    public function isHavingEmailValidity()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        $currentDate     = date('Y-m-d');
        $emailValidity   = $entity->getEmailValidity();
        $totalEmailCount = $entity->getTotalEmailCount();

        $remDays    = round((strtotime($emailValidity) - strtotime($currentDate)) / 86400);
        $lastpayment=$this->em->getRepository('Mautic\SubscriptionBundle\Entity\PaymentHistory')->getLastPayment();
        if ($totalEmailCount == 'UL' && $lastpayment != null) {
            return true;
        } else {
            if ($remDays >= 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function getEmailValidityDays()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        $currentDate     = date('Y-m-d');
        $emailValidity   = $entity->getEmailValidity();
        $totalEmailCount = $entity->getTotalEmailCount();

        $validityDays = round((strtotime($emailValidity) - strtotime($currentDate)) / 86400);
        $lastpayment  =$this->em->getRepository('Mautic\SubscriptionBundle\Entity\PaymentHistory')->getLastPayment();
        if ($totalEmailCount == 'UL' && $lastpayment != null) {
            return 'UL';
        } else {
            return $validityDays;
        }
    }

    public function getEmailBounceUsageCount()
    {
        $data=$this->em->getRepository('Mautic\CoreBundle\Entity\LicenseInfo')->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }

        $actualEmailCount  = $entity->getActualEmailCount();
        $bouncedEmailCount = $entity->getBounceCount();

        if ($actualEmailCount > 0) {
            $bounceUsageCount = ($bouncedEmailCount / $actualEmailCount) * 100;

            return round($bounceUsageCount);
        }
    }

    public function getAvailableEmailCount()
    {
        $entity           = $this->licenseinfo->findAll()[0];
        $totalEmailCount  = $entity->getTotalEmailCount();
        $actualEmailCount = $entity->getActualEmailCount();
        $email_provider   = $entity->getEmailProvider();
        if ($totalEmailCount == 'UL') {
            return -1;
        } else {
            if ($email_provider != 'LeadsEngage') {
                $availablecredits = $totalEmailCount - $actualEmailCount;
            } else {
                $availablecredits = 100 - $actualEmailCount;
            }

            return $availablecredits > 0 ? $availablecredits : 0;
        }
    }

    public function getLicenseEndDate()
    {
        $data = $this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }

        $licenseDate   = $entity->getLicenseEnd();
        $convertDate   = strtotime($licenseDate);
        $licenseEndDate= date('d-M-Y', $convertDate);

        return $licenseEndDate;
    }

    public function getEmailValidityEndDate()
    {
        $data = $this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }

        $emailValidityEndDate = $entity->getEmailValidity();
        $convertDate          = strtotime($emailValidityEndDate);
        $validityEndDate      = date('d-M-Y', $convertDate);

        return $validityEndDate;
    }

    public function getTotalRecordUsage()
    {
        $data=$this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }

        $totalRecordCount  = $entity->getTotalRecordCount();
        $actualRecordCount = $entity->getActualRecordCount();

        if ($totalRecordCount == 'UL') {
            return $totalRecordCount;
        } else {
            if ($actualRecordCount > 0) {
                $totalRecordUsage = ($actualRecordCount / $totalRecordCount) * 100;

                return $totalRecordUsage;
            }
        }
    }

    public function getAvailableRecordCount()
    {
        $data=$this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }

        $totalRecordCount  = $entity->getTotalRecordCount();
        $actualRecordCount = $entity->getActualRecordCount();

        if ($totalRecordCount == 'UL') {
            return -1;
        } else {
            $availablerecordcount = $totalRecordCount - $actualRecordCount;

            return $availablerecordcount > 0 ? $availablerecordcount : 0;
        }
    }

    public function emailCountExpired()
    {
        $data=$this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }

        $totalEmailCount  = $entity->getTotalEmailCount();
        $actualEmailCount = $entity->getActualEmailCount();

        $configtransport    = $this->factory->get('mautic.helper.core_parameters')->getParameter('mailer_transport_name');
        $paymentrepository  = $this->factory->get('le.subscription.repository.payment');
        $lastpayment        = $paymentrepository->getLastPayment();
        if ($configtransport == 'le.transport.vialeadsengage' && ($lastpayment == null || $lastpayment->getPlanName() == 'leplan1')) {
            $totalEmailCount = 100;
        }

        if ($totalEmailCount == 'UL') {
            return $totalEmailCount;
        } else {
            if ($actualEmailCount > 0) {
                $emailCountExpired = $totalEmailCount - $actualEmailCount;
                if ($emailCountExpired == 0 || $emailCountExpired < 0) {
                    return  0;
                } else {
                    return $emailCountExpired;
                }
            }
        }
    }

    public function getEmailValidity()
    {
        $entity           = $this->licenseinfo->findAll()[0];
        $validity         = $entity->getEmailValidity();

        return $validity;
    }

    public function getAccountStatus()
    {
        $entity           = $this->licenseinfo->findAll()[0];
        $accountStatus    = $entity->getAppStatus();

        if ($accountStatus == 'Suspended') {
            return true;
        } else {
            return false;
        }
    }

    public function getEmailProvider()
    {
        $entity           = $this->licenseinfo->findAll()[0];
        $accountStatus    =  $entity->getEmailProvider();

        return $accountStatus;
    }

    public function intEmailProvider($emailProvider)
    {
        $data=$this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        if (!isset($emailProvider)) {
            $emailProvider = '';
        }

        $entity->setEmailProvider($emailProvider);
        $this->licenseinfo->saveEntity($entity);
    }

    public function getDeleteCountBasedonMonth($month)
    {
        $query = $this->em->getConnection()->createQueryBuilder()
            ->select('l.delete_count')
            ->from(MAUTIC_TABLE_PREFIX.'licenseinfo', 'l')
            ->where('l.delete_month'.'='."'$month'");

        $result = $query->execute()->fetch();

        return $result['delete_count'];
    }

    public function getLicenseEntity()
    {
        $data  =$this->licenseinfo->findAll();
        $entity=null;
        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }

        return $entity;
    }

    public function suspendApplication()
    {
        $data = $this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        $entity->setAppStatus('Suspended');
        $this->licenseinfo->saveEntity($entity);
    }

    public function intCancelDate($canceldate)
    {
        $data=$this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        if (!isset($canceldate)) {
            $canceldate = '';
        }
        $entity->setCancelDate($canceldate);
        $this->licenseinfo->saveEntity($entity);
    }

    public function getCancelDate()
    {
        $entity             = $this->licenseinfo->findAll()[0];
        $canceldate         = $entity->getCancelDate();

        return $canceldate;
    }

    public function intAppStatus($status)
    {
        $data=$this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        if (!isset($status)) {
            $status = '';
        }

        $entity->setAppStatus($status);
        $this->licenseinfo->saveEntity($entity);
    }

    public function getElasticAccountDetails($apikey, $name, $limit = false)
    {
        $data_array['apikey']=$apikey;
        if ($limit) {
            $data_array['limit'] = 1;
        }
        $ch = curl_init("https://api.elasticemail.com/v2/account/$name");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data_array));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result     = curl_exec($ch);
        $dataresult = json_decode($result, true);

        return isset($dataresult['data']) ? $dataresult['data'] : [];
    }

    public function getSendGridStatus($subusername)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_URL, "https://api.sendgrid.com/v3/subusers?username=$subusername");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer <SENDGRID_PASSWORD>', ]);
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        if (isset($result[0]['disabled']) && !$result[0]['disabled']) {
            return 'Active';
        } else {
            return 'InActive';
        }
    }

    public function getFirstTimeSetup($dbhost = 'localhost', $islogin = false)
    {
        if ($dbhost != 'localhost' && $islogin) {
            $billformview = '';
            $accformview  = '';
            $userformview = '';
            $kycview      = [];
            $showsetup    = true;
            /** @var \Mautic\UserBundle\Model\UserModel $usermodel */
            $usermodel  = $this->factory->getModel('user.user');
            $userentity = $usermodel->getCurrentUserEntity();

            $userform = $usermodel->createForm($userentity, $this->factory->get('form.factory'));

            /** @var \Mautic\SubscriptionBundle\Model\BillingModel $billingmodel */
            $billingmodel  = $this->factory->getModel('subscription.billinginfo');
            $billingrepo   = $billingmodel->getRepository();
            $billingentity = $billingrepo->findAll();
            if (sizeof($billingentity) > 0) {
                $billing = $billingentity[0]; //$model->getEntity(1);
            } else {
                $showsetup = true;
                $billing   = new Billing();
            }
            $countrydetails = $this->getCountryName();
            $timezone       = $countrydetails['timezone'];
            $countryname    = $countrydetails['countryname'];
            //if ($countryname == 'India') {
            //    $timezone = 'Asia/Calcutta';
            $billing->setCountry($countryname);
            //}
            $repository = $this->factory->get('le.core.repository.subscription');
            $signupinfo = $repository->getSignupInfo($userentity->getEmail());
            if (!empty($signupinfo)) {
                $billing->setCompanyname($signupinfo[0]['f2']);
                $billing->setAccountingemail($userentity->getEmail());
            }

            $billform = $billingmodel->createForm($billing, $this->factory->get('form.factory'), [], ['isBilling' => false]);

            /** @var \Mautic\SubscriptionBundle\Model\AccountInfoModel $model */
            $model         = $this->factory->getModel('subscription.accountinfo');
            $accrepo       = $model->getRepository();
            $accountentity = $accrepo->findAll();
            if (sizeof($accountentity) > 0) {
                $account = $accountentity[0]; //$model->getEntity(1);
                if (!$account->getMobileverified()) {
                    $showsetup = true;
                }
            } else {
                $showsetup = true;
                $account   = new Account();
            }
            if (!empty($signupinfo)) {
                //$account->setPhonenumber($signupinfo[0]['f11']);
            }
            if ($timezone != '') {
                $account->setTimezone($timezone);
            }
            $account->setNeedpoweredby(true);
            $accform = $model->createForm($account, $this->factory->get('form.factory'));
            if ($showsetup) {
                $billformview = $billform->createView();
                $accformview  = $accform->createView();
                $userformview = $userform->createView();
                $kycview[]    = $billformview;
                $kycview[]    = $accformview;
                $kycview[]    = $userformview;
                $kycview[]    = $billing;
                $kycview[]    = $account;
                $kycview[]    = $userentity;
            } else {
                $kycview = [];
            }

            return $kycview;
        } else {
            return [];
        }
    }

    public function getCountryName()
    {
        $clientip                      = $this->factory->getRequest()->getClientIp();
        $countrydetails                = [];
        $countrydetails['countryname'] = '';
        $countrydetails['timezone']    = '';
        $countrydetails['city']        = '';
        $countrydetails['state']       = '';
        try {
            $dataArray                     = json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip='.$clientip));
            $countrycode                   = $dataArray->{'geoplugin_countryName'};
            $lat                           = $dataArray->{'geoplugin_latitude'};
            $lon                           = $dataArray->{'geoplugin_longitude'};
            $city                          = $dataArray->{'geoplugin_city'};
            $state                         = $dataArray->{'geoplugin_region'};

            $countrydetails                = [];
            $countrydetails['countryname'] = $countrycode;
            $timezone                      = 'Asia/Calcutta';
            $ipInfo                        = file_get_contents('http://ip-api.com/json/'.$clientip);
            $ipInfo                        = json_decode($ipInfo);
            if ($ipInfo->status != 'fail') {
                $timezone = $ipInfo->timezone;
                if ($timezone == 'Asia/Kolkata') {
                    $timezone = 'Asia/Calcutta';
                }
            }
            $countrydetails['timezone']    = $timezone;
            $countrydetails['city']        = $city;
            $countrydetails['state']       = $state;
        } catch (\Exception $ex) {
        }

        return $countrydetails;
    }

    public function getTimeZone($lat, $lon)
    {
        $url             = "https://maps.googleapis.com/maps/api/timezone/json?location=$lat,$lon&timestamp=1458000000";
        $dataArray       = json_decode(file_get_contents($url));
        $timezone        =$dataArray->{'timeZoneId'};
        if ($timezone == 'Asia/Kolkata') {
            $timezone = 'Asia/Calcutta';
        }

        return $timezone;
    }

    public function intSMSCount($SMSCount)
    {
        $data=$this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        if (!isset($SMSCount)) {
            $SMSCount = 0;
        }

        $previousValue = $entity->getActualSmsCount();
        $totalCount    = $previousValue + $SMSCount;
        $entity->setActualSmsCount($totalCount);

        $this->licenseinfo->saveEntity($entity);
    }

    public function smsCountExpired()
    {
        $data=$this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }

        if (!$data) {
            $entity = new LicenseInfo();
        }

        $totalSMSCount  = $entity->getTotalSmsCount();
        $actualSMSCount = $entity->getActualSmsCount();

        $smsprovider = $this->factory->get('mautic.helper.core_parameters')->getParameter('sms_transport');
        if ($smsprovider == 'le.sms.transport.leadsengage') {
            $totalSMSCount = 10;
        }

        if ($totalSMSCount == 'UL') {
            return true;
        } else {
            if ($totalSMSCount > $actualSMSCount) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function intSMSProvider($smsProvider)
    {
        $data=$this->licenseinfo->findAll();

        if (sizeof($data) > 0 && $data != null) {
            $entity = $data[0];
        }
        if (!$data) {
            $entity = new LicenseInfo();
        }
        if (!isset($smsProvider)) {
            $smsProvider = '';
        }

        $entity->setSMSProvider($smsProvider);
        $this->licenseinfo->saveEntity($entity);
    }

    public function redirectToCardinfo()
    {
        $currentuser = $this->factory->getUser();
        if ($currentuser->isAdmin()) {
            return false;
        }
        $lastpayment=$this->em->getRepository('Mautic\SubscriptionBundle\Entity\PaymentHistory')->getLastPayment();
        if ($lastpayment != null && $lastpayment->getPaymentStatus() != 'Paid') {
            return true;
        } else {
            return false;
        }
    }

    public function redirectToSubscriptionpage()
    {
        $currentuser = $this->factory->getUser();
        if ($currentuser->isAdmin()) {
            return false;
        }
        $lastpayment=$this->em->getRepository('Mautic\SubscriptionBundle\Entity\PaymentHistory')->getLastPayment();
        if ($this->getLicenseRemainingDays() < 0) {
            return true;
        } else {
            return false;
        }
    }

    public function isLeadsEngageEmailExpired($pending)
    {
        $configtransport     = $this->factory->get('mautic.helper.core_parameters')->getParameter('mailer_transport_name');
       // $availableemailcount =  $this->getAvailableEmailCount();
        $totalemailcount     =  100;
        $actualEmailCount    = $this->getActualEmailCount();
        $availableemailcount = $totalemailcount - $actualEmailCount;
        $paymentrepository   = $this->factory->get('le.subscription.repository.payment');
        $lastpayment         = $paymentrepository->getLastPayment();
        if ($configtransport == 'le.transport.vialeadsengage') {
            if ($pending > $availableemailcount) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }
}
