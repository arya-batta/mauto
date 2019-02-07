<?php

namespace Mautic\SubscriptionBundle\Entity;

use Doctrine\ORM\EntityManager;
use Mautic\CoreBundle\Entity\LicenseInfoRepository;
use Mautic\SubscriptionBundle\Model\AccountInfoModel;

class SubscriptionRepository
{
    /**
     * @var EntityManager
     */
    private $commondbentityManager;
    /**
     * @var LicenseInfoRepository
     */
    private $licenseinforepo;

    /**
     * @var SignupRepository
     */
    private $signuprepo;

    /**
     * @var AccountInfoModel
     */
    private $accmodel;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $commondbentityManager, LicenseInfoRepository $licenseinforepo, SignupRepository $signuprepo, AccountInfoModel $accmodel)
    {
        $this->commondbentityManager = $commondbentityManager;
        $this->licenseinforepo       =$licenseinforepo;
        $this->signuprepo            =$signuprepo;
        $this->accmodel              =$accmodel;
    }

    /**
     * @return EntityManager
     */
    public function getCommonDbEntityManager()
    {
        return $this->commondbentityManager;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->getCommonDbEntityManager()->getConnection();
    }

    public function getPlanInfo($provider, $planname, $plancycle)
    {
        $qb = $this->getConnection()->createQueryBuilder();

        $qb->select('pl.planid')
            ->from(MAUTIC_TABLE_PREFIX.'planinfo', 'pl');
        $qb->andWhere('pl.provider = :provider')
            ->setParameter('provider', $provider);
        $qb->andWhere('pl.planname = :planname')
            ->setParameter('planname', $planname);
        $qb->andWhere('pl.plancycle = :plancycle')
            ->setParameter('plancycle', $plancycle);

        return $qb->execute()->fetchAll();
    }

    public function getSignupInfo($emailid)
    {
        $qb = $this->getConnection()->createQueryBuilder();

        $qb->select('al.f11', 'al.f2', 'al.f5', 'al.appid')
            ->from(MAUTIC_TABLE_PREFIX.'applicationlist', 'al');
        $qb->andWhere('al.f4 = :email')
            ->setParameter('email', $emailid);

        return $qb->execute()->fetchAll();
    }

    public function getVideoURL()
    {
        $qb = $this->getConnection()->createQueryBuilder();

        $qb->select('v.video_url')
            ->from(MAUTIC_TABLE_PREFIX.'video_config', 'v');

        return $qb->execute()->fetchAll();
    }

    public function getAllPrepaidPlans()
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('pp.*')
            ->from(MAUTIC_TABLE_PREFIX.'prepaidplans', 'pp');
        $qb->orderBy('pp.planorder', 'ASC');

        return $qb->execute()->fetchAll();
    }

    public function getSMSConfig()
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('s.*')
            ->from(MAUTIC_TABLE_PREFIX.'smsconfig', 's');
        $qb->andWhere('s.isdefault = :isdefault')
            ->setParameter('isdefault', 1);

        return $qb->execute()->fetchAll();
    }

    public function updateEmailCredits($plankey)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('pp.*')
            ->from(MAUTIC_TABLE_PREFIX.'prepaidplans', 'pp');
        $qb->andWhere('pp.name = :name')
            ->setParameter('name', $plankey);
        $plans=$qb->execute()->fetchAll();
        if (sizeof($plans) > 0) {
            $plan           =$plans[0];
            $credits        =$plan['credits'];
            $months         =$plan['months'];
            $licentity      =$this->licenseinforepo->findAll()[0];
            $totalemailcount=$licentity->getTotalEmailCount();
            $licenseddays   =$licentity->getLicensedDays();
            if (is_numeric($totalemailcount)) {
                $validity       =date('Y-m-d', strtotime("+$months months"));
                if ($licenseddays != 'UL') {
                    $licensestart=date('Y-m-d');
                    $licenseend  =date('Y-m-d', strtotime($validity.' + 14 days'));
                    $licentity->setLicenseStart($licensestart);
                    $licentity->setLicenseEnd($licenseend);
                }
                $totalemailcount=$totalemailcount + $credits;
                $licentity->setTotalEmailCount($totalemailcount);
                $licentity->setEmailValidity($validity);
                $this->licenseinforepo->saveEntity($licentity);
            }
        }
        $accrepo       = $this->accmodel->getRepository();
        $accountentity = $accrepo->findAll();
        if (sizeof($accountentity) > 0) {
            $account = $accountentity[0]; //$model->getEntity(1);
            $email   = $account->getEmail();
            if ($email != '') {
                $this->signuprepo->updateCustomerStatus('Paid- Active', $email);
            }
        }
    }

    public function updateContactCredits($credits, $validitytill, $startdate, $clearactualmailcount=false)
    {
        $starttime    =strtotime($startdate);
        $endtime      = strtotime($validitytill);
        $datediff     = $endtime - $starttime;
        $validitydays = round($datediff / (60 * 60 * 24));

        $licentity=$this->licenseinforepo->findAll()[0];
        $licentity->setTotalRecordCount($credits);
        $licentity->setTotalEmailCount('UL');
        $licentity->setLicensedDays($validitydays);
        $licentity->setEmailValidity('UL');
        $licentity->setLicenseStart($startdate);
        $licentity->setLicenseEnd($validitytill);
        if ($clearactualmailcount) {
            $licentity->setActualEmailCount(0);
        }
        $this->licenseinforepo->saveEntity($licentity);
    }

    public function getPlanValidity($plankey)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('pp.*')
            ->from(MAUTIC_TABLE_PREFIX.'prepaidplans', 'pp');
        $qb->andWhere('pp.name = :name')
            ->setParameter('name', $plankey);
        $plans   =$qb->execute()->fetchAll();
        $validity='';
        if (sizeof($plans) > 0) {
            $plan           =$plans[0];
            $months         =$plan['months'];
            $licentity      =$this->licenseinforepo->findAll()[0];
            $totalemailcount=$licentity->getTotalEmailCount();
            if (is_numeric($totalemailcount)) {
                $validity=date('Y-m-d', strtotime("+$months months"));
            }
        }

        return $validity;
    }
}
