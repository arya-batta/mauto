<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

class Version20190501135500 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     */
    public function preUp(Schema $schema)
    {
        if ($schema->hasTable(MAUTIC_TABLE_PREFIX.'sendingdomains')) {
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
        $this->addSql("CREATE TABLE sendingdomains (id INT AUTO_INCREMENT NOT NULL, domain VARCHAR(255) DEFAULT NULL, spf_check TINYINT(1) DEFAULT NULL, dkim_check TINYINT(1) DEFAULT NULL, tracking_check TINYINT(1) DEFAULT NULL, mx_check TINYINT(1) DEFAULT NULL, dmarc_check TINYINT(1) DEFAULT NULL, status TINYINT(1) DEFAULT '0' NOT NULL, isdefault TINYINT(1) DEFAULT '0' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;");
    }
}
