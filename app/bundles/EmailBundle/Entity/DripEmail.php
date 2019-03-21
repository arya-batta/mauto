<?php

/*
 * @copyright   2018 LeadsEngage Contributors. All rights reserved
 * @author      LeadsEngage
 *
 * @link        https://leadsengage.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\Entity;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class DripEmail.
 */
class DripEmail extends FormEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $fromAddress;

    /**
     * @var string
     */
    private $fromName;

    /**
     * @var string
     */
    private $replyToAddress;

    /**
     * @var string
     */
    private $bccAddress;

    /**
     * @var \DateTime
     */
    private $publishUp;

    /**
     * @var \DateTime
     */
    private $publishDown;

    /**
     * @var \Mautic\CategoryBundle\Entity\Category
     **/
    private $category;

    /**
     * @var null|string
     */
    private $scheduleDate;

    /**
     * @var string
     */
    private $sendEmailChoice;

    /**
     * @var string
     */
    private $daysEmailSend;

    /*
     * @var string
     */
    private $previewText;

    /*
     * @var string
     */
    private $unsubscribeText;

    /*
     * @var string
     */
    private $postalAddress;

    /**
     * Used to identify the page for the builder.
     *
     * @var
     */
    private $sessionId;

    /**
     * @var bool
     */
    private $google_tags = 1;

    /**
     * @var array
     */
    private $recipients = [];

    public function __clone()
    {
        $this->id                      = null;
        $this->sessionId               = 'new_'.hash('sha1', uniqid(mt_rand()));

        parent::__clone();
    }

    /**
     * DripEmail constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('dripemail')
            ->setCustomRepositoryClass('Mautic\EmailBundle\Entity\DripEmailRepository');

        $builder->addIdColumns();

        $builder->addPublishDates();

        $builder->addCategory();

        $builder->createField('subject', 'text')
            ->nullable()
            ->build();

        $builder->createField('fromAddress', 'string')
            ->columnName('from_address')
            ->nullable()
            ->build();

        $builder->createField('fromName', 'string')
            ->columnName('from_name')
            ->nullable()
            ->build();

        $builder->createField('replyToAddress', 'string')
            ->columnName('reply_to_address')
            ->nullable()
            ->build();

        $builder->createField('bccAddress', 'string')
            ->columnName('bcc_address')
            ->nullable()
            ->build();

        $builder->addNullableField('scheduleDate', Type::STRING, 'schedule_time');
        $builder->addNullableField('sendEmailChoice', Type::STRING, 'sendemail_choice');
        $builder->addNullableField('daysEmailSend', Type::TARRAY, 'daysemail_send');

        $builder->createField('previewText', 'text')
            ->columnName('preview_text')
            ->nullable()
            ->build();

        $builder->createField('unsubscribeText', 'text')
            ->columnName('unsubscribe_text')
            ->nullable()
            ->build();

        $builder->createField('postalAddress', 'text')
            ->columnName('postal_address')
            ->nullable()
            ->build();

        $builder->createField('google_tags', 'boolean')
            ->columnName('google_tags')
            ->build();

        $builder->addField('recipients', 'array');
    }

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint(
            'name',
            new NotBlank(
                [
                    'message' => 'le.drip.email.name.notblank',
                ]
            )
        );
    }

    /**
     * Prepares the metadata for API usage.
     *
     * @param $metadata
     */
    public static function loadApiMetadata(ApiMetadataDriver $metadata)
    {
        $metadata->setGroupPrefix('dripemail')
            ->addListProperties(
                [
                    'id',
                    'name',
                    //'subject',
                    'category',
                ]
            )
            ->addProperties(
                [
                    'fromAddress',
                    'fromName',
                    'replyToAddress',
                    'bccAddress',
                    'scheduleDate',
                    'daysEmailSend',
                ]
            )
            ->build();
    }

    /**
     * @param $prop
     * @param $val
     */
    protected function isChanged($prop, $val)
    {
        $getter  = 'get'.ucfirst($prop);
        $current = $this->$getter();

        if ($prop == 'category' || $prop == 'list') {
            $currentId = ($current) ? $current->getId() : '';
            $newId     = ($val) ? $val->getId() : null;
            if ($currentId != $newId) {
                $this->changes[$prop] = [$currentId, $newId];
            }
        } else {
            parent::isChanged($prop, $val);
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getFromAddress()
    {
        return $this->fromAddress;
    }

    /**
     * @param string $fromAddress
     */
    public function setFromAddress($fromAddress)
    {
        $this->fromAddress = $fromAddress;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
    }

    /**
     * @return string
     */
    public function getReplyToAddress()
    {
        return $this->replyToAddress;
    }

    /**
     * @param string $replyToAddress
     */
    public function setReplyToAddress($replyToAddress)
    {
        $this->replyToAddress = $replyToAddress;
    }

    /**
     * @return string
     */
    public function getBccAddress()
    {
        return $this->bccAddress;
    }

    /**
     * @param string $bccAddress
     */
    public function setBccAddress($bccAddress)
    {
        $this->bccAddress = $bccAddress;
    }

    /**
     * @return \DateTime
     */
    public function getPublishUp()
    {
        return $this->publishUp;
    }

    /**
     * @param \DateTime $publishUp
     */
    public function setPublishUp($publishUp)
    {
        $this->publishUp = $publishUp;
    }

    /**
     * @return \DateTime
     */
    public function getPublishDown()
    {
        return $this->publishDown;
    }

    /**
     * @param \DateTime $publishDown
     */
    public function setPublishDown($publishDown)
    {
        $this->publishDown = $publishDown;
    }

    /**
     * @return \Mautic\CategoryBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param \Mautic\CategoryBundle\Entity\Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return null|string
     */
    public function getScheduleDate()
    {
        return $this->scheduleDate;
    }

    /**
     * @param null|string $scheduleDate
     */
    public function setScheduleDate($scheduleDate)
    {
        $this->scheduleDate = $scheduleDate;
    }

    /**
     * @return string
     */
    public function getSendEmailChoice()
    {
        return $this->sendEmailChoice;
    }

    /**
     * @param string $sendEmailChoice
     */
    public function setSendEmailChoice($sendEmailChoice)
    {
        $this->sendEmailChoice = $sendEmailChoice;
    }

    /**
     * @return string
     */
    public function getDaysEmailSend()
    {
        return $this->daysEmailSend;
    }

    /**
     * @param string $daysEmailSend
     */
    public function setDaysEmailSend($daysEmailSend)
    {
        $this->daysEmailSend = $daysEmailSend;
    }

    /**
     * @return mixed
     */
    public function getPreviewText()
    {
        return $this->previewText;
    }

    /**
     * @param mixed $previewText
     */
    public function setPreviewText($previewText)
    {
        $this->previewText = $previewText;
    }

    /**
     * @return mixed
     */
    public function getUnsubscribeText()
    {
        return $this->unsubscribeText;
    }

    /**
     * @param mixed $unsubscribeText
     */
    public function setUnsubscribeText($unsubscribeText)
    {
        $this->unsubscribeText = $unsubscribeText;
    }

    /**
     * @return mixed
     */
    public function getPostalAddress()
    {
        return $this->postalAddress;
    }

    /**
     * @param mixed $postalAddress
     */
    public function setPostalAddress($postalAddress)
    {
        $this->postalAddress = $postalAddress;
    }

    /**
     * @return mixed
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param mixed $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return bool
     */
    public function isGoogleTags()
    {
        return $this->google_tags;
    }

    /**
     * @param bool $google_tags
     */
    public function setGoogleTags($google_tags)
    {
        $this->google_tags = $google_tags;
    }

    /**
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param array $recipients
     */
    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;
    }
}
