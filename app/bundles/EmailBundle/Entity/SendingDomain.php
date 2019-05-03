<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 23/5/18
 * Time: 12:25 PM.
 */

namespace Mautic\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;

class SendingDomain
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var bool
     */
    private $spfCheck;

    /**
     * @var bool
     */
    private $dkimCheck;

    /**
     * @var bool
     */
    private $trackingCheck;

    /**
     * @var bool
     */
    private $mxCheck;

    /**
     * @var bool
     */
    private $dmarcCheck;

    /**
     * @var bool
     */
    private $isdefault;

    /**
     * @var bool
     */
    private $status;

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('sendingdomains')
            ->setCustomRepositoryClass('Mautic\EmailBundle\Entity\EmailRepository');

        $builder->createField('id', 'integer')
            ->makePrimaryKey()
            ->generatedValue()
            ->build();
        $builder->createField('domain', 'string')
            ->columnName('domain')
            ->nullable()
            ->build();
        $builder->createField('spfCheck', 'boolean')
            ->columnName('spf_check')
            ->nullable()
            ->build();
        $builder->createField('dkimCheck', 'boolean')
            ->columnName('dkim_check')
            ->nullable()
            ->build();
        $builder->createField('trackingCheck', 'boolean')
            ->columnName('tracking_check')
            ->nullable()
            ->build();
        $builder->createField('mxCheck', 'boolean')
            ->columnName('mx_check')
            ->nullable()
            ->build();
        $builder->createField('dmarcCheck', 'boolean')
            ->columnName('dmarc_check')
            ->nullable()
            ->build();
        $builder->createField('status', 'boolean')
            ->columnName('status')
            ->nullable(false)
            ->option('default', '0')
            ->build();
        $builder->createField('isdefault', 'boolean')
            ->columnName('isdefault')
            ->nullable(false)
            ->option('default', '0')
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
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @param $flag
     */
    public function setspfCheck($flag)
    {
        $this->spfCheck = $flag;
    }

    /**
     * @return bool
     */
    public function getspfCheck()
    {
        return $this->spfCheck;
    }

    /**
     * @param $flag
     */
    public function setdkimCheck($flag)
    {
        $this->dkimCheck = $flag;
    }

    /**
     * @return bool
     */
    public function getdkimCheck()
    {
        return $this->dkimCheck;
    }

    /**
     * @param $flag
     */
    public function settrackingCheck($flag)
    {
        $this->trackingCheck = $flag;
    }

    /**
     * @return bool
     */
    public function gettrackingCheck()
    {
        return $this->trackingCheck;
    }

    /**
     * @param $flag
     */
    public function setmxCheck($flag)
    {
        $this->mxCheck = $flag;
    }

    /**
     * @return bool
     */
    public function getmxCheck()
    {
        return $this->mxCheck;
    }

    /**
     * @param $flag
     */
    public function setdmarcCheck($flag)
    {
        $this->dmarcCheck = $flag;
    }

    /**
     * @return bool
     */
    public function getdmarcCheck()
    {
        return $this->dmarcCheck;
    }

    /**
     * @param $flag
     */
    public function setStatus($flag)
    {
        $this->status = $flag;
    }

    /**
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $flag
     */
    public function setIsDefault($flag)
    {
        $this->isdefault = $flag;
    }

    /**
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isdefault;
    }
}
