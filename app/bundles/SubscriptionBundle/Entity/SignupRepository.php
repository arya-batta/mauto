<?php

namespace Mautic\SubscriptionBundle\Entity;

use Doctrine\ORM\EntityManager;

class SignupRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->getEntityManager()->getConnection();
    }

    public function checkisRecordAvailable($emailid)
    {
        $qb = $this->getConnection()->createQueryBuilder();

        $qb->select('l.id')
            ->from(MAUTIC_TABLE_PREFIX.'leads', 'l');
        $qb->andWhere('l.email = :email')
            ->setParameter('email', $emailid);
        $leads = $qb->execute()->fetchAll();

        if (!empty($leads)) {
            return $leads[0]['id'];
        } else {
            return false;
        }
    }

    public function updateSignupInfo($accountData, $billingData, $userData, $appid)
    {
        $qb = $this->getConnection()->createQueryBuilder();

        $firstName      = $userData['firstName'];
        $lastName       = $userData['lastName'];
        $email          = $userData['email'];

        $companyname    = $billingData['companyname'];
        $companyaddress = $billingData['companyaddress'];
        $postalcode     = $billingData['postalcode'];
        $state          = $billingData['state'];
        $city           = $billingData['city'];
        $country        = $billingData['country'];
        $gstnumber      = $billingData['gstnumber'];

        $phonenumber    = $accountData['phonenumber'];
        $timezone       = $accountData['timezone'];
        $website        = $accountData['website'];

        $recordid = $this->checkisRecordAvailable($email);
        if (!$recordid) {
        } else {
            $qb->update(MAUTIC_TABLE_PREFIX.'leads')
                ->set('address1', ':address')
                ->set('city', ':city')
                ->set('state', ':state')
                ->set('zipcode', ':zipcode')
                ->set('timezone1', ':timezone')
                ->set('country', ':country')
                ->set('gst_no', ':gst_no')
                ->set('lead_stage', ':stage')
                ->set('lastname', ':lastname')
                ->set('mobile', ':mobile')
                ->set('website1', ':website')
                ->set('app_id', ':appid')
                ->setParameter('address', $companyaddress)
                ->setParameter('city', $city)
                ->setParameter('state', $state)
                ->setParameter('zipcode', $postalcode)
                ->setParameter('timezone', $timezone)
                ->setParameter('country', $country)
                ->setParameter('gst_no', $gstnumber)
                ->setParameter('lastname', $lastName)
                ->setParameter('mobile', $phonenumber)
                ->setParameter('website', $website)
                ->setParameter('appid', $appid)
                ->setParameter('stage', 'Trial- Activated')
                ->where(
                    $qb->expr()->in('id', $recordid)
                )
                ->execute();
        }
    }

    public function updateKYCInfo($kycdata, $userdata)
    {
        $qb    = $this->getConnection()->createQueryBuilder();
        $email = $userdata['email'];

        $industry           = $kycdata['industry'];
        $usercount          = $kycdata['usercount'];
        $yearsactive        = $kycdata['yearsactive'];
        $subscribercount    = $kycdata['subscribercount'];
        $subscribersource   = $kycdata['subscribersource'];
        $emailcontent       = $kycdata['emailcontent'];
        $previoussoftware   = $kycdata['previoussoftware'];
        $knowus             = $kycdata['knowus'];
        $others             = $kycdata['others'];

        $recordid = $this->checkisRecordAvailable($email);
        if (!$recordid) {
        } else {
            $qb->update(MAUTIC_TABLE_PREFIX.'leads')
                ->set('what_industry_are_you_in', ':industry')
                ->set('how_many_people_work_for', ':usercount')
                ->set('how_old_is_your_organizat', ':yearsactive')
                ->set('how_many_subscribers_do_y', ':subscribercount')
                ->set('what_is_your_marketing_go', ':subscribersource')
                ->set('have_you_used_other_email', ':previoussoftware')
                ->set('other_marketing_software', ':emailcontent')
                ->set('how_did_you_find_out_abou', ':knowus')
                ->set('other', ':others')
                ->setParameter('industry', $industry)
                ->setParameter('usercount', $usercount)
                ->setParameter('yearsactive', $yearsactive)
                ->setParameter('subscribercount', $subscribercount)
                ->setParameter('subscribersource', $subscribersource)
                ->setParameter('previoussoftware', $previoussoftware)
                ->setParameter('emailcontent', $emailcontent)
                ->setParameter('knowus', $knowus)
                ->setParameter('others', $others)
                ->where(
                    $qb->expr()->in('id', $recordid)
                )
                ->execute();
        }
    }

    public function updateCustomerStatus($stage, $email)
    {
        $qb       = $this->getConnection()->createQueryBuilder();
        $recordid = $this->checkisRecordAvailable($email);
        if (!$recordid) {
        } else {
            $qb->update(MAUTIC_TABLE_PREFIX.'leads')
                ->set('lead_stage', ':stage')
                ->setParameter('stage', $stage)
                ->where(
                    $qb->expr()->in('id', $recordid)
                )
                ->execute();
        }
    }

    public function selectfocusItems($args = [])
    {
        try {
            $this->getConnection()->connect();
        } catch (\Exception $e) {
            return [];
        }
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('f.*')
            ->from(MAUTIC_TABLE_PREFIX.'focus', 'f', 'f.id')
            ->andWhere($qb->expr()->neq('f.focus_type', ':form'))
            ->setParameter(':form', 'form')
            ->andWhere($qb->expr()->eq('f.is_published', 0))
            ->andWhere($qb->expr()->eq('f.created_by', 1))
            ->orderBy('f.templateorder', 'asc');

        return $qb->execute()->fetchAll();
    }

    public function selectformItems($args = [])
    {
        try {
            $this->getConnection()->connect();
        } catch (\Exception $e) {
            return [];
        }
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('f.*')
            ->from(MAUTIC_TABLE_PREFIX.'forms', 'f', 'f.id')
            ->andWhere($qb->expr()->eq('f.is_published', 0))
            ->andWhere($qb->expr()->eq('f.created_by', 1))
            ->andWhere($qb->expr()->eq('f.form_type', ':formtype'))
            ->setParameter(':formtype', 'standalone')
            ->orderBy('f.templateorder', 'asc');

        return $qb->execute()->fetchAll();
    }

    public function selectPopupTemplatebyID($formid)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('f.*')
            ->from(MAUTIC_TABLE_PREFIX.'focus', 'f', 'f.id')
            ->andWhere($qb->expr()->eq('f.is_published', 0))
            ->andWhere($qb->expr()->eq('f.id', $formid));

        return $qb->execute()->fetch();
    }

    public function selectFormTemplatebyID($formid)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('f.*')
            ->from(MAUTIC_TABLE_PREFIX.'forms', 'f', 'f.id')
            ->andWhere($qb->expr()->eq('f.is_published', 0))
            ->andWhere($qb->expr()->eq('f.id', $formid));

        return $qb->execute()->fetch();
    }

    public function selectFormFieldsTemplatebyID($formid)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('f.id as id, f.label as label, f.show_label as showLabel, f.alias as alias, f.type as type, f.is_custom as isCustom, f.custom_parameters as customParameters,f.default_value as defaultValue, f.is_required as isRequired, f.validation_message as validationMessage, f.help_message as helpMessage, f.field_order as forder, f.properties as properties, f.label_attr as labelAttributes, f.input_attr as inputAttributes, f.container_attr as containerAttributes, f.lead_field as leadField , f.save_result as saveResult, f.is_auto_fill as isAutoFill, f.show_when_value_exists as showWhenValueExists, f.show_after_x_submissions as showAfterXSubmissions,f.form_id as formId')
            ->from(MAUTIC_TABLE_PREFIX.'form_fields', 'f', 'f.id')
            ->andWhere($qb->expr()->eq('f.form_id', $formid))
            ->orderBy('f.field_order', 'asc');

        return $qb->execute()->fetchAll();
    }
}
