<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

class Version20190509100600 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     */
    public function preUp(Schema $schema)
    {
        if ($schema->getTable(MAUTIC_TABLE_PREFIX.'lead_listoptin')->hasColumn('fromname')) {
            throw new SkipMigrationException('Schema includes this migration');
        }
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE lead_listoptin ADD fromname VARCHAR(255) DEFAULT NULL, ADD fromaddress VARCHAR(255) DEFAULT NULL, ADD subject VARCHAR(255) DEFAULT NULL, ADD message LONGTEXT DEFAULT NULL, ADD resend TINYINT(1) DEFAULT NULL;');
        $this->addSql('ALTER TABLE lead_listoptin CHANGE listtype listtype TINYINT(1) DEFAULT NULL;');
        $this->addSql('ALTER TABLE lead_listoptin_leads ADD isrescheduled TINYINT(1) NOT NULL;');
    }
}
