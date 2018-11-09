<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181109114125 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE dripemail (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, is_published TINYINT(1) NOT NULL, date_added DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', created_by INT DEFAULT NULL, created_by_user VARCHAR(255) DEFAULT NULL, date_modified DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', modified_by INT DEFAULT NULL, modified_by_user VARCHAR(255) DEFAULT NULL, checked_out DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', checked_out_by INT DEFAULT NULL, checked_out_by_user VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, publish_up DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', publish_down DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', subject LONGTEXT DEFAULT NULL, from_address VARCHAR(255) DEFAULT NULL, from_name VARCHAR(255) DEFAULT NULL, reply_to_address VARCHAR(255) DEFAULT NULL, bcc_address VARCHAR(255) DEFAULT NULL, schedule_time VARCHAR(255) DEFAULT NULL, sendemail_choice VARCHAR(255) DEFAULT NULL, daysemail_send LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', preview_text LONGTEXT DEFAULT NULL, unsubscribe_text LONGTEXT DEFAULT NULL, postal_address LONGTEXT DEFAULT NULL, google_tags TINYINT(1) NOT NULL, INDEX IDX_3D4FDD7512469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dripemail_lead_event_log (id INT AUTO_INCREMENT NOT NULL, lead_id INT NOT NULL, email_id INT DEFAULT NULL, dripemail_id INT DEFAULT NULL, rotation INT NOT NULL, date_triggered DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', is_scheduled TINYINT(1) NOT NULL, trigger_date VARCHAR(255) DEFAULT NULL, system_triggered TINYINT(1) NOT NULL, failedReason VARCHAR(255) DEFAULT NULL, INDEX IDX_ED08B1D655458D (lead_id), INDEX IDX_ED08B1D6A832C1C9 (email_id), INDEX IDX_ED08B1D6E68E88E5 (dripemail_id), INDEX dripemail_leads (lead_id, dripemail_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dripemail_leads (id INT AUTO_INCREMENT NOT NULL, dripemail_id INT NOT NULL, lead_id INT NOT NULL, date_added DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', manually_removed TINYINT(1) NOT NULL, manually_added TINYINT(1) NOT NULL, date_last_exited DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime)\', rotation INT NOT NULL, INDEX IDX_58E6B0FCE68E88E5 (dripemail_id), INDEX IDX_58E6B0FC55458D (lead_id), INDEX dripemail_leads_date_added (date_added), INDEX dripemail_leads_date_exited (date_last_exited), INDEX dripemail_leads (dripemail_id, manually_removed, lead_id, rotation), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dripemail ADD CONSTRAINT FK_3D4FDD7512469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE dripemail_lead_event_log ADD CONSTRAINT FK_ED08B1D655458D FOREIGN KEY (lead_id) REFERENCES leads (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dripemail_lead_event_log ADD CONSTRAINT FK_ED08B1D6A832C1C9 FOREIGN KEY (email_id) REFERENCES emails (id)');
        $this->addSql('ALTER TABLE dripemail_lead_event_log ADD CONSTRAINT FK_ED08B1D6E68E88E5 FOREIGN KEY (dripemail_id) REFERENCES dripemail (id)');
        $this->addSql('ALTER TABLE dripemail_leads ADD CONSTRAINT FK_58E6B0FCE68E88E5 FOREIGN KEY (dripemail_id) REFERENCES dripemail (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dripemail_leads ADD CONSTRAINT FK_58E6B0FC55458D FOREIGN KEY (lead_id) REFERENCES leads (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE assets CHANGE disallow disallow TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE campaigns CHANGE canvas_settings canvas_settings LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE emails ADD dripemail_id INT DEFAULT NULL, ADD scheduleTime LONGTEXT DEFAULT NULL, ADD dripEmailOrder LONGTEXT DEFAULT NULL, CHANGE preview_text preview_text LONGTEXT DEFAULT NULL, CHANGE unsubscribe_text unsubscribe_text LONGTEXT DEFAULT NULL, CHANGE postal_address postal_address LONGTEXT DEFAULT NULL, CHANGE google_tags google_tags TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE emails ADD CONSTRAINT FK_4C81E852E68E88E5 FOREIGN KEY (dripemail_id) REFERENCES dripemail (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_4C81E852E68E88E5 ON emails (dripemail_id)');
        $this->addSql('ALTER TABLE email_stats CHANGE is_spam is_spam TINYINT(1) NOT NULL');
        $this->addSql('DROP INDEX tracking_id ON lead_devices');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_48C912F47D05ABBE ON lead_devices (tracking_id)');
        $this->addSql('ALTER TABLE user_tokens DROP FOREIGN KEY user_tokens_ibfk_1');
        $this->addSql('ALTER TABLE user_tokens CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('DROP INDEX secret ON user_tokens');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CF080AB35CA2E8E5 ON user_tokens (secret)');
        $this->addSql('DROP INDEX user_id ON user_tokens');
        $this->addSql('CREATE INDEX IDX_CF080AB3A76ED395 ON user_tokens (user_id)');
        $this->addSql('ALTER TABLE user_tokens ADD CONSTRAINT user_tokens_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE webhook_queue CHANGE payload payload INT NOT NULL');
        $this->addSql('ALTER TABLE accountinfo CHANGE mobileverified mobileverified INT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
