<?php

namespace Mautic\PluginBundle\Helper;

use FacebookAds\Api;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\CustomAudienceMultiKey;
use FacebookAds\Object\Fields\CustomAudienceFields;
use FacebookAds\Object\Fields\CustomAudienceMultikeySchemaFields;
use FacebookAds\Object\Lead;
use FacebookAds\Object\Values\CustomAudienceSubtypes;

class FacebookAdsApiHelper
{
    /**
     * @param $clientid
     * @param $clientsecret
     * @param $clienttoken
     *
     * @return Api|null
     */
    public static function init($clientid, $clientsecret, $clienttoken)
    {
        Api::init($clientid, $clientsecret, $clienttoken);

        return Api::instance();
    }

    /**
     * @param $adAccount
     *
     * @return array
     */
    public static function getFBAudiences($adAccount)
    {
        $name_mapping = [];
        try {
            $account      = new AdAccount($adAccount);
            foreach ($account->getCustomAudiences([CustomAudienceFields::ID, CustomAudienceFields::NAME]) as $list) {
                $data                        = $list->getData();
                $name_mapping[$data['name']] = $data['id'];
            }
        } catch (\Exception $e) {
            $name_mapping = [];
        }

        return $name_mapping;
    }

    /**
     * @param $listName
     *
     * @return bool|mixed
     */
    public static function getFBAudienceID($listName, $adaccount)
    {
        $audiences = static::getFBAudiences($adaccount);
        if (isset($audiences[$listName])) {
            return $audiences[$listName];
        }

        return false;
    }

    /**
     * @param $listName
     *
     * @return CustomAudienceMultiKey
     */
    public static function getFBAudience($listName, $adaccount)
    {
        if ($audience_id = static::getFBAudienceID($listName, $adaccount)) {
            return new CustomAudienceMultiKey($audience_id);
        }
    }

    public static function getFBAudienceByID($audienceid, $adaccount)
    {
        $audiences        = static::getFBAudiences($adaccount);
        $customaudienceObj=false;
        foreach ($audiences as $key => $value) {
            if ($value == $audienceid) {
                $customaudienceObj=new CustomAudienceMultiKey($audienceid);
                break;
            }
        }

        return $customaudienceObj;
    }

    /**
     * @param $name
     */
    public static function deleteList($name, $adaccount)
    {
        $audiences = static::getFBAudiences($adaccount);
        if (isset($audiences[$name])) {
            $audience_id = $audiences[$name];
            $audience    = new CustomAudienceMultiKey($audience_id);
            $audience->deleteSelf();
        }
    }

    /**
     * @param \Mautic\LeadBundle\Entity\LeadList $list
     * @param $adAccount
     *
     * @return CustomAudienceMultiKey
     *
     * @throws \Exception
     */
    public static function addList(\Mautic\LeadBundle\Entity\LeadList $list, $adAccount)
    {
        // Get the name of the list, or the old one to rename.
        $changes = $list->getChanges();
        if (isset($changes['name']) && is_array($changes['name'])) {
            $orig_name = $changes['name'][0];
        } else {
            $orig_name = $list->getName();
        }
        $audiences = static::getFBAudiences($adAccount);
        if (isset($audiences[$orig_name])) {
            $audience_id = $audiences[$orig_name];
            $audience    = new CustomAudienceMultiKey($audience_id);
            $audience->setData([
                CustomAudienceFields::NAME        => $list->getName(),
                CustomAudienceFields::DESCRIPTION => 'Mautic Segment: '.$list->getDescription(),
            ]);
            $audience->update();
        } else {
            $audience = new CustomAudienceMultiKey();
            $audience->setParentId($adAccount);
            $audience->setData([
                CustomAudienceFields::NAME        => $list->getName(),
                CustomAudienceFields::SUBTYPE     => CustomAudienceSubtypes::CUSTOM,
                CustomAudienceFields::DESCRIPTION => 'Mautic Segment: '.$list->getDescription(),
            ]);
            $audience->create();
        }

        return $audience;
    }

    public static function createCustomAudience($name, $adaccount)
    {
        $audiences = static::getFBAudiences($adaccount);
        if (!isset($audiences[$name])) {
            $audience = new CustomAudienceMultiKey();
            $audience->setParentId($adaccount);
            $audience->setData([
                CustomAudienceFields::NAME                 => $name,
                CustomAudienceFields::SUBTYPE              => CustomAudienceSubtypes::CUSTOM,
                CustomAudienceFields::DESCRIPTION          => 'created by anyfunnels via workflow',
                CustomAudienceFields::CUSTOMER_FILE_SOURCE => 'USER_PROVIDED_ONLY',
            ]);
            $audience->create();
        } else {
            $audience=static::getFBAudience($name, $adaccount);
        }

        return $audience;
    }

    /**
     * @param CustomAudienceMultiKey $audience
     * @param array                  $users
     */
    public static function addUsers(CustomAudienceMultiKey $audience, array $users)
    {
        return $audience->addUsers($users, static::getFBSchema());
    }

    /**
     * @param CustomAudienceMultiKey $audience
     * @param array                  $users
     */
    public static function removeUsers(CustomAudienceMultiKey $audience, array $users)
    {
        return $audience->removeUsers($users, static::getFBSchema());
    }

    /**
     * @return array
     */
    protected static function getFBSchema()
    {
        return [
            CustomAudienceMultikeySchemaFields::FIRST_NAME,
            CustomAudienceMultikeySchemaFields::LAST_NAME,
            CustomAudienceMultikeySchemaFields::EMAIL,
            CustomAudienceMultiKeySchemaFields::PHONE,
            CustomAudienceMultiKeySchemaFields::COUNTRY,
        ];
    }

    /**
     * @return array
     */
    public static function getLeadDetailsByID($id)
    {
        $response='';
        try {
            $fields  =[];
            $params  =[];
            $response=json_encode((new Lead($id))->getSelf(
                $fields,
                $params
            )->exportAllData(), JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            $response='';
        }

        return $response;
    }
}
