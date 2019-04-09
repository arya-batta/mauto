<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

class Version20190409112500 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     */
    public function preUp(Schema $schema)
    {
        if ($schema->hasTable(MAUTIC_TABLE_PREFIX.'integration_field_mapping')) {
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

        $this->addSql("CREATE TABLE integration_field_mapping (id INT AUTO_INCREMENT NOT NULL, integration_id INT DEFAULT NULL, groupname VARCHAR(255) NOT NULL, fields LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)', INDEX IDX_7CDF08559E82DDEA (integration_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql('ALTER TABLE integration_field_mapping ADD CONSTRAINT FK_7CDF08559E82DDEA FOREIGN KEY (integration_id) REFERENCES plugin_integration_settings (id) ON DELETE CASCADE');
        $this->addSql('CREATE TABLE integration_payload_history (id INT AUTO_INCREMENT NOT NULL, integration_id INT DEFAULT NULL, payload LONGTEXT DEFAULT NULL, createdOn DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', INDEX IDX_C592FD049E82DDEA (integration_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE integration_payload_history ADD CONSTRAINT FK_C592FD049E82DDEA FOREIGN KEY (integration_id) REFERENCES plugin_integration_settings (id) ON DELETE CASCADE');
    }
}
