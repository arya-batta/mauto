<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

class Version20190716144500 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     *
     * @throws SkipMigrationException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function preUp(Schema $schema)
    {
        $table = $schema->getTable("{$this->prefix}email_stats");
        if ($table->hasIndex("{$this->prefix}email_date_read_lead")) {
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

        $this->addSql("alter table {$this->prefix}email_stats add key {$this->prefix}email_date_read_lead (date_read, lead_id)");
        $table = $schema->getTable("{$this->prefix}email_stats");
        if ($table->hasIndex("{$this->prefix}email_date_read")) {
            $this->addSql("alter table {$this->prefix}email_stats drop key {$this->prefix}email_date_read");
        }
    }
}
