<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

class Version20181214194000 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     */
    public function preUp(Schema $schema)
    {
        if ($schema->hasTable(MAUTIC_TABLE_PREFIX.'lead_listoptin')) {
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

        $this->addSql("CREATE TABLE lead_listoptin (id INT AUTO_INCREMENT NOT NULL, is_published TINYINT(1) NOT NULL, date_added DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime)', created_by INT DEFAULT NULL, created_by_user VARCHAR(255) DEFAULT NULL, date_modified DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime)', modified_by INT DEFAULT NULL, modified_by_user VARCHAR(255) DEFAULT NULL, checked_out DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime)', checked_out_by INT DEFAULT NULL, checked_out_by_user VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, listtype LONGTEXT DEFAULT NULL, thankyou TINYINT(1) NOT NULL, goodbye TINYINT(1) NOT NULL, doubleoptinemail VARCHAR(255) DEFAULT NULL, thankyouemail VARCHAR(255) DEFAULT NULL, goodbyeemail VARCHAR(255) DEFAULT NULL, footerText VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE lead_listoptin_leads (id INT AUTO_INCREMENT NOT NULL, leadlist_id INT NOT NULL, lead_id INT NOT NULL, date_added DATETIME NOT NULL COMMENT '(DC2Type:datetime)', manually_removed TINYINT(1) NOT NULL, manually_added TINYINT(1) NOT NULL, confirmed_lead TINYINT(1) NOT NULL, unconfirmed_lead TINYINT(1) NOT NULL, unsubscribed_lead TINYINT(1) NOT NULL, INDEX IDX_4D792786B9FC8874 (leadlist_id), INDEX IDX_4D79278655458D (lead_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql('ALTER TABLE lead_listoptin_leads ADD CONSTRAINT FK_4D792786B9FC8874 FOREIGN KEY (leadlist_id) REFERENCES lead_listoptin (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lead_listoptin_leads ADD CONSTRAINT FK_4D79278655458D FOREIGN KEY (lead_id) REFERENCES leads (id) ON DELETE CASCADE');
    }
}
