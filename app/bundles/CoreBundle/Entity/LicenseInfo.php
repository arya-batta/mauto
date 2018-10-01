<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 24/2/18
 * Time: 12:42 PM.
 */

namespace Mautic\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;

/**
 * Class LicenseInfo.
 */
class LicenseInfo
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $totalrecordcount;

    /**
     * @var string
     */
    private $actualrecordcount;

    /**
     * @var string
     */
    private $totalemailcount;

    /**
     * @var string
     */
    private $actualemailcount;

    /**
     * @var string
     */
    private $totalusercount;

    /**
     * @var string
     */
    private $activeusercount;

    /**
     * @var string
     */
    private $licenseddays;

    /**
     * @var string
     */
    private $licensestartdate;

    /**
     * @var string
     */
    private $licenseenddate;

    /**
     * @var string
     */
    private $totalattachementsize;

    /**
     * @var string
     */
    private $actualattachementsize;

    /**
     * @var string
     */
    private $bouncecount;

    /**
     * @var string
     */
    private $spamcount;

    /**
     * @var string
     */
    private $appstatus;

    /**
     * @var string
     */
    private $emailvalidity;

    /**
     * @var string
     */
    private $emailprovider;

    /**
     * @var string
     */
    private $deletecount;

    /**
     * @var string
     */
    private $deletemonth;

    /**
     * @var string
     */
    private $canceldate;

    /**
     * @var string
     */
    private $totalsmscount;

    /**
     * @var string
     */
    private $actualsmscount;

    /**
     * @var string
     */
    private $smsprovider;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('licenseinfo')
            ->setCustomRepositoryClass('Mautic\CoreBundle\Entity\LicenseInfoRepository');

        $builder->createField('id', 'integer')
            ->makePrimaryKey()
            ->generatedValue()
            ->build();

        $builder->createField('totalrecordcount', 'string')
            ->columnName('total_record_count')
            ->nullable()
            ->build();

        $builder->createField('actualrecordcount', 'string')
            ->columnName('actual_record_count')
            ->nullable()
            ->build();

        $builder->createField('totalemailcount', 'string')
            ->columnName('total_email_count')
            ->nullable()
            ->build();

        $builder->createField('actualemailcount', 'string')
            ->columnName('actual_email_count')
            ->nullable()
            ->build();

        $builder->createField('activeusercount', 'string')
            ->columnName('active_user_count')
            ->nullable()
            ->build();

        $builder->createField('totalusercount', 'string')
            ->columnName('total_user_count')
            ->nullable()
            ->build();
        $builder->createField('licenseddays', 'string')
            ->columnName('licensed_days')
            ->nullable()
            ->build();

        $builder->createField('licensestartdate', 'string')
            ->columnName('license_start_date')
            ->nullable()
            ->build();

        $builder->createField('licenseenddate', 'string')
            ->columnName('license_end_date')
            ->nullable()
            ->build();

        $builder->createField('totalattachementsize', 'string')
            ->columnName('total_attachement_size')
            ->nullable()
            ->build();

        $builder->createField('actualattachementsize', 'string')
            ->columnName('actual_attachement_size')
            ->nullable()
            ->build();

        $builder->createField('bouncecount', 'string')
            ->columnName('bounce_count')
            ->nullable()
            ->build();

        $builder->createField('spamcount', 'string')
            ->columnName('spam_count')
            ->nullable()
            ->build();

        $builder->createField('appstatus', 'string')
            ->columnName('app_status')
            ->nullable()
            ->build();

        $builder->createField('emailvalidity', 'string')
            ->columnName('email_validity')
            ->nullable()
            ->build();

        $builder->createField('emailprovider', 'string')
            ->columnName('email_provider')
            ->nullable()
            ->build();

        $builder->createField('deletecount', 'string')
            ->columnName('delete_count')
            ->nullable()
            ->build();

        $builder->createField('deletemonth', 'string')
            ->columnName('delete_month')
            ->nullable()
            ->build();

        $builder->createField('canceldate', 'string')
            ->columnName('cancel_date')
            ->nullable()
            ->build();

        $builder->createField('totalsmscount', 'string')
            ->columnName('total_sms_count')
            ->nullable()
            ->build();

        $builder->createField('actualsmscount', 'string')
            ->columnName('actual_sms_count')
            ->nullable()
            ->build();

        $builder->createField('smsprovider', 'string')
            ->columnName('sms_provider')
            ->nullable()
            ->build();
    }

    /**
     * Set id.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTotalRecordCount()
    {
        return $this->totalrecordcount;
    }

    /**
     * @param mixed $totalrecordcount
     */
    public function setTotalRecordCount($totalrecordcount)
    {
        $this->totalrecordcount = $totalrecordcount;
    }

    /**
     * @return mixed
     */
    public function getActualRecordCount()
    {
        return $this->actualrecordcount;
    }

    /**
     * @param mixed $actualrecordcount
     */
    public function setActualRecordCount($actualrecordcount)
    {
        $this->actualrecordcount = $actualrecordcount;
    }

    /**
     * @return mixed
     */
    public function getTotalEmailCount()
    {
        return $this->totalemailcount;
    }

    /**
     * @param mixed $totalemailcount
     */
    public function setTotalEmailCount($totalemailcount)
    {
        $this->totalemailcount = $totalemailcount;
    }

    /**
     * @return mixed
     */
    public function getActualEmailCount()
    {
        return $this->actualemailcount;
    }

    /**
     * @param mixed $actualemailcount
     */
    public function setActualEmailCount($actualemailcount)
    {
        $this->actualemailcount = $actualemailcount;
    }

    /**
     * @return mixed
     */
    public function getTotalUserCount()
    {
        return $this->totalusercount;
    }

    /**
     * @param mixed $totalusercount
     */
    public function setTotalUserCount($totalusercount)
    {
        $this->totalusercount = $totalusercount;
    }

    /**
     * @return mixed
     */
    public function getActiveUserCount()
    {
        return $this->activeusercount;
    }

    /**
     * @param mixed $activeusercount
     */
    public function setActiveUserCount($activeusercount)
    {
        $this->activeusercount = $activeusercount;
    }

    /**
     * @return mixed
     */
    public function getLicensedDays()
    {
        return $this->licenseddays;
    }

    /**
     * @param mixed $licenseddays
     */
    public function setLicensedDays($licenseddays)
    {
        $this->licenseddays = $licenseddays;
    }

    /**
     * @return mixed
     */
    public function getLicenseStart()
    {
        return $this->licensestartdate;
    }

    /**
     * @param mixed $licensestartdate
     */
    public function setLicenseStart($licensestartdate)
    {
        $this->licensestartdate = $licensestartdate;
    }

    /**
     * @return mixed
     */
    public function getLicenseEnd()
    {
        return $this->licenseenddate;
    }

    /**
     * @param mixed $licenseenddate
     */
    public function setLicenseEnd($licenseenddate)
    {
        $this->licenseenddate = $licenseenddate;
    }

    /**
     * @return mixed
     */
    public function getTotalAttachementSize()
    {
        return $this->totalattachementsize;
    }

    /**
     * @param mixed $totalattachementsize
     */
    public function setTotalAttachementSize($totalattachementsize)
    {
        $this->totalattachementsize = $totalattachementsize;
    }

    /**
     * @return mixed
     */
    public function getActualAttachementSize()
    {
        return $this->actualattachementsize;
    }

    /**
     * @param mixed $actualattachementsize
     */
    public function setActualAttachementSize($actualattachementsize)
    {
        $this->actualattachementsize = $actualattachementsize;
    }

    /**
     * @return mixed
     */
    public function getBounceCount()
    {
        return $this->bouncecount;
    }

    /**
     * @param mixed $bouncecount
     */
    public function setBounceCount($bouncecount)
    {
        $this->bouncecount = $bouncecount;
    }

    /**
     * @return mixed
     */
    public function getSpamCount()
    {
        return $this->spamcount;
    }

    /**
     * @param mixed $spamcount
     */
    public function setSpamCount($spamcount)
    {
        $this->spamcount = $spamcount;
    }

    /**
     * @return mixed
     */
    public function getAppStatus()
    {
        return $this->appstatus;
    }

    /**
     * @param mixed $appstatus
     */
    public function setAppStatus($appstatus)
    {
        $this->appstatus = $appstatus;
    }

    /**
     * @return mixed
     */
    public function getEmailValidity()
    {
        return $this->emailvalidity;
    }

    /**
     * @param mixed $emailvalidity
     */
    public function setEmailValidity($emailvalidity)
    {
        $this->emailvalidity = $emailvalidity;
    }

    /**
     * @return mixed
     */
    public function getEmailProvider()
    {
        return $this->emailprovider;
    }

    /**
     * @param mixed $emailprovider
     */
    public function setEmailProvider($emailprovider)
    {
        $this->emailprovider = $emailprovider;
    }

    /**
     * @return mixed
     */
    public function getDeleteCount()
    {
        return $this->deletecount;
    }

    /**
     * @param mixed $deletecount
     */
    public function setDeleteCount($deletecount)
    {
        $this->deletecount = $deletecount;
    }

    /**
     * @return mixed
     */
    public function getDeleteMonth()
    {
        return $this->deletemonth;
    }

    /**
     * @param mixed $deletemonth
     */
    public function setDeleteMonth($deletemonth)
    {
        $this->deletemonth = $deletemonth;
    }

    /**
     * @return mixed
     */
    public function getCancelDate()
    {
        return $this->canceldate;
    }

    /**
     * @param mixed $canceldate
     */
    public function setCancelDate($canceldate)
    {
        $this->canceldate = $canceldate;
    }

    /**
     * @return mixed
     */
    public function getTotalSmsCount()
    {
        return $this->totalsmscount;
    }

    /**
     * @param mixed $totalsmscount
     */
    public function setTotalSmsCount($totalsmscount)
    {
        $this->totalsmscount = $totalsmscount;
    }

    /**
     * @return mixed
     */
    public function getActualSmsCount()
    {
        return $this->actualsmscount;
    }

    /**
     * @param mixed $actualsmscount
     */
    public function setActualSmsCount($actualsmscount)
    {
        $this->actualsmscount = $actualsmscount;
    }

    /**
     * @return mixed
     */
    public function getSMSProvider()
    {
        return $this->smsprovider;
    }

    /**
     * @param mixed $smsprovider
     */
    public function setSMSProvider($smsprovider)
    {
        $this->smsprovider = $smsprovider;
    }
}
