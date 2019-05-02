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
                ->set('company_new', ':company')
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
                ->setParameter('company', $companyname)
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

    public function getSignupLeadinfo($appid)
    {
        $qb = $this->getConnection()->createQueryBuilder();

        $qb->select('l.id')
            ->from(MAUTIC_TABLE_PREFIX.'leads', 'l');
        $qb->andWhere('l.app_id = :app_id')
            ->setParameter('app_id', $appid);

        return $qb->execute()->fetchAll();
    }

    public function getTagIdbyName($tagname)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('t.id as tagid')
            ->from(MAUTIC_TABLE_PREFIX.'lead_tags', 't', 't.tag')
            ->andWhere($qb->expr()->eq('t.tag', ':tagName'))
            ->setParameter('tagName', $tagname);

        return $qb->execute()->fetchAll();
    }

    public function updateLeadwithTag($tagname, $leadid)
    {
        $tagdetails = $this->getTagIdbyName($tagname);
        if (!empty($tagdetails)) {
            $tagid = $tagdetails[0]['tagid'];
            $this->linkLeadwithTag($tagid, $leadid);
        } else {
            $this->createNewTag($tagname);
            $tagdetails = $this->getTagIdbyName($tagname);
            if (!empty($tagdetails)) {
                $tagid = $tagdetails[0]['tagid'];
                $this->linkLeadwithTag($tagid, $leadid);
            }
        }
    }

    public function createNewTag($tagname)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->insert(MAUTIC_TABLE_PREFIX.'lead_tags')
            ->values([
                'tag' => ':tagName',
            ])
            ->setParameter('tagName', $tagname)
            ->execute();
    }

    public function linkLeadwithTag($tagid, $leadid)
    {
        $qb      = $this->getConnection()->createQueryBuilder();
        $taglist = $this->isLeadAlreadyLinkedwithTag($leadid, $tagid);
        if (empty($taglist)) {
            $qb->insert(MAUTIC_TABLE_PREFIX.'lead_tags_xref')
                ->values([
                    'lead_id' => ':leadId',
                    'tag_id'  => ':tagId',
                ])
                ->setParameter('tagId', $tagid)
                ->setParameter('leadId', $leadid)
                ->execute();
        }
    }

    public function isLeadAlreadyLinkedwithTag($leadId, $tagId)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('t.tag_id as tagid')
            ->from(MAUTIC_TABLE_PREFIX.'lead_tags_xref', 't', 't.tag_id')
            ->andWhere($qb->expr()->eq('t.tag_id', $tagId))
            ->andWhere($qb->expr()->eq('t.lead_id', $leadId));

        return $qb->execute()->fetchAll();
    }

    public function isLeadAlreadyLinkedwithCampaign($campaignId, $leadId)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('c.campaign_id as campaignId')
            ->from(MAUTIC_TABLE_PREFIX.'campaign_leads', 'c', 't.campaign_id')
            ->andWhere($qb->expr()->eq('c.campaign_id', $campaignId))
            ->andWhere($qb->expr()->eq('c.lead_id', $leadId));

        return $qb->execute()->fetchAll();
    }

    public function unScheduleAllLeadsforCampaign($campaignId)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->update(MAUTIC_TABLE_PREFIX.'campaign_lead_event_log')
            ->set('is_scheduled', 0)
            ->where(
                $qb->expr()->in('campaign_id', $campaignId)
            )
            ->execute();
    }

    public function addLeadinCampaignLog($campaignId, $leadId, $eventId, $isScheduled)
    {
        $qb              = $this->getConnection()->createQueryBuilder();
        $campaignDetails = $this->isCampaignEventAvailable($eventId);
        if (!empty($campaignDetails)) {
            date_default_timezone_set('UTC');
            $currenttime = date('Y-m-d H:i:s');
            $qb->insert(MAUTIC_TABLE_PREFIX.'campaign_lead_event_log')
                ->values([
                    'campaign_id'      => ':campaignId',
                    'lead_id'          => ':leadId',
                    'event_id'         => ':eventId',
                    'date_triggered'   => ':dateTriggered',
                    'is_scheduled'     => ':isScheduled',
                ])
                ->setParameter('campaignId', $campaignId)
                ->setParameter('leadId', $leadId)
                ->setParameter('eventId', $eventId)
                ->setParameter('dateTriggered', $currenttime)
                ->setParameter('isScheduled', $isScheduled)
                ->execute();
        }
    }

    public function linkLeadwithCampaign($campaignId, $leadId)
    {
        $qb              = $this->getConnection()->createQueryBuilder();
        $campaignDetails = $this->isCampaignAvailable($campaignId);
        $campaignLink    = $this->isLeadAlreadyLinkedwithCampaign($campaignId, $leadId);
        if (!empty($campaignDetails)) {
            if (empty($campaignLink)) {
                date_default_timezone_set('UTC');
                $currenttime = date('Y-m-d H:i:s');
                $qb->insert(MAUTIC_TABLE_PREFIX.'campaign_leads')
                    ->values([
                        'campaign_id'      => ':campaignId',
                        'lead_id'          => ':leadId',
                        'date_added'       => ':dateAdded',
                        'manually_removed' => 0,
                        'manually_added'   => 1,
                        'date_last_exited' => ':dateLast',
                        'rotation'         => 1,
                    ])
                    ->setParameter('campaignId', $campaignId)
                    ->setParameter('leadId', $leadId)
                    ->setParameter('dateAdded', $currenttime)
                    ->setParameter('dateLast', null)
                    ->execute();
            }
        }
    }

    public function isCampaignAvailable($campaignId)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('c.id as campaignId')
            ->from(MAUTIC_TABLE_PREFIX.'campaigns', 'c', 'c.id')
            ->andWhere($qb->expr()->eq('c.id', $campaignId));

        return $qb->execute()->fetchAll();
    }

    public function isCampaignEventAvailable($eventId)
    {
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('e.id as eventId')
            ->from(MAUTIC_TABLE_PREFIX.'campaign_events', 'e', 'e.id')
            ->andWhere($qb->expr()->eq('e.id', $eventId));

        return $qb->execute()->fetchAll();
    }

    public function getBluePrintCampaigns()
    {
        $driemails = $this->getDripEmailsForBluePrint();

        $resultArr = [];
        foreach ($driemails as $dripemail) {
            //file_put_contents("/var/www/log.txt",$dripemail['id']."\n",FILE_APPEND);
            $emails = $this->getEmailsByDripId($dripemail['id']);
            //dump($emails);
            if (!empty($emails)) {
                $resultArr[$dripemail['id']] = $emails;
            }
        }

        return $resultArr;
    }

    public function getDripEmailsForBluePrint()
    {
        try {
            $this->getConnection()->connect();
        } catch (\Exception $e) {
            return [];
        }
        $qb = $this->getConnection()->createQueryBuilder();
        $qb->select('d.id')
            ->from(MAUTIC_TABLE_PREFIX.'dripemail', 'd')
            ->andWhere($qb->expr()->eq('d.is_published', 0))
            ->andWhere($qb->expr()->eq('d.created_by', 1))
            ->orderBy('d.templateorder', 'asc');

        return $dripemails = $qb->execute()->fetchAll();
    }

    /**
     * Get a list of entities.
     *
     * @return Paginator
     */
    public function getEmailsByDripId($dripId)
    {
        $q = $this->getConnection()->createQueryBuilder()
            ->select('e.*')
            ->from(MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id');
        $q->andWhere('e.dripemail_id = :dripEmail')
            ->setParameter('dripEmail', $dripId)
            ->orderBy('e.dripEmailOrder', 'asc');

        return $emails = $q->execute()->fetchAll();
    }

    /**
     * Get a list of entities.
     *
     * @return Paginator
     */
    public function getEmailsByEmailId($emailId)
    {
        try {
            $this->getConnection()->connect();
        } catch (\Exception $e) {
            return [];
        }
        $q = $this->getConnection()->createQueryBuilder()
            ->select('e.*')
            ->from(MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id');
        $q->andWhere('e.id = :emailId')
            ->setParameter('emailId', $emailId)
            ->orderBy('e.dripEmailOrder', 'asc');

        return $emails = $q->execute()->fetchAll();
    }

    /**
     * Get a list of entities.
     *
     * @return Paginator
     */
    public function getDripEmails()
    {
        try {
            $this->getConnection()->connect();
        } catch (\Exception $e) {
            return [];
        }
        $q = $this->getConnection()
            ->createQueryBuilder()
            ->select('d.*')
            ->from(MAUTIC_TABLE_PREFIX.'dripemail', 'd', 'd.id');
        /*$q->andWhere('e.dripEmail = :dripEmail')
            ->setParameter('dripEmail', $dripId);*/

        /*$args = [
            'filter' => [

            ],
            'ignore_paginator' => true,
        ];
        $args['qb'] = $q;*/
        $dripemails = $q->execute()->fetchAll();

        $drips = [];
        foreach ($dripemails as $key => $item) {
            $drips[$item['id']] = $item;
        }

        return $drips;
    }

    public function updateSignupUserInfo($userData)
    {
        $qb = $this->getConnection()->createQueryBuilder();

        $firstName      = $userData['firstname'];
        $lastName       = $userData['lastname'];
        $email          = $userData['email'];
        $phonenumber    = $userData['phone'];

        $recordid = $this->checkisRecordAvailable($email);
        if (!$recordid) {
        } else {
            $qb->update(MAUTIC_TABLE_PREFIX.'leads')
                ->set('lead_stage', ':stage')
                ->set('lastname', ':lastname')
                ->set('firstname', ':firstname')
                ->set('mobile', ':mobile')
                ->setParameter('lastname', $lastName)
                ->setParameter('firstname', $firstName)
                ->setParameter('mobile', $phonenumber)
                ->setParameter('stage', 'Trial- Activated')
                ->where(
                    $qb->expr()->in('id', $recordid)
                )
                ->execute();
        }
    }

    public function updateSignupUserBusinessInfo($businessData)
    {
        $qb = $this->getConnection()->createQueryBuilder();

        $website     = $businessData['websiteurl'];
        $business    = $businessData['business'];
        $email       = $businessData['email'];
        $industry    = $businessData['industry'];
        $empcount    = $businessData['empcount'];
        //$orgexp      = $businessData['org_experience'];
        $emailvol    = $businessData['emailvol'];
        $listsize    = $businessData['listsize'];
        $currentesp  = $businessData['currentesp'];

        $recordid = $this->checkisRecordAvailable($email);
        if (!$recordid) {
        } else {
            $qb->update(MAUTIC_TABLE_PREFIX.'leads')
                ->set('website1', ':website')
                ->set('company_new', ':company')
                ->set('industry', ':industry')
                ->set('employees_count', ':empcount')
                //->set('company_age', ':org_experience')
                ->set('monthly_email_volume', ':emailvol')
                ->set('your_current_list_size', ':listsize')
                ->set('current_email_marketing_p', ':currentesp')
                ->setParameter('website', $website)
                ->setParameter('company', $business)
                ->setParameter('industry', $industry)
                //->setParameter('empcount', $empcount)
                //->setParameter('org_experience', $orgexp)
                ->setParameter('emailvol', $emailvol)
                //->setParameter('listsize', $listsize)
                ->setParameter('currentesp', $currentesp)
                ->where(
                    $qb->expr()->in('id', $recordid)
                )
                ->execute();
        }
    }

    public function updateSignupUserAddressInfo($addressData)
    {
        $qb = $this->getConnection()->createQueryBuilder();

        $address1 = $addressData['address-line-1'];
        $address2 = $addressData['address-line-2'];
        $city     = $addressData['city'];
        $state    = $addressData['state'];
        $country  = $addressData['country'];
        $taxid    = $addressData['taxid'];
        $zip      = $addressData['zip'];
        $timezone = $addressData['timezone'];
        $email    = $addressData['email'];

        $recordid = $this->checkisRecordAvailable($email);
        if (!$recordid) {
        } else {
            $qb->update(MAUTIC_TABLE_PREFIX.'leads')
                ->set('address1', ':address1')
                ->set('address2', ':address2')
                ->set('city', ':city')
                ->set('state', ':state')
                ->set('zipcode', ':zipcode')
                ->set('country', ':country')
                ->set('timezone1', ':timezone1')
                ->set('gst_no', ':gst_no')
                ->setParameter('address1', $address1)
                ->setParameter('address2', $address2)
                ->setParameter('city', $city)
                ->setParameter('state', $state)
                ->setParameter('zipcode', $zip)
                ->setParameter('country', $country)
                ->setParameter('timezone1', $timezone)
                ->setParameter('gst_no', $taxid)
                ->where(
                    $qb->expr()->in('id', $recordid)
                )
                ->execute();
        }
    }
}
