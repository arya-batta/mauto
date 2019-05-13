<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

class Version20190510181500 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE {$this->prefix}lead_fields set label='First name' where alias='firstname' ");
        $this->addSql("UPDATE {$this->prefix}lead_fields set label='Last name' where alias='lastname' ");
        $this->addSql("UPDATE {$this->prefix}lead_fields set label='Address line 1' where alias='address1' ");
        $this->addSql("UPDATE {$this->prefix}lead_fields set label='Address line 2' where alias='address2' ");
        $this->addSql("UPDATE {$this->prefix}lead_fields set label='Zip code' where alias='zipcode' ");
        $this->addSql("UPDATE {$this->prefix}lead_fields set label='Create source' where alias='created_source' ");
    }
}
