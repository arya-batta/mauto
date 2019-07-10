<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

/**
 * Add indexes to speed up campaign view rendering.
 */
class Version20190710143500 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     *
     * @throws SkipMigrationException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function preUp(Schema $schema)
    {
        $table = $schema->getTable("{$this->prefix}campaign_lead_event_log");
        if ($table->hasIndex("{$this->prefix}campaign_actions")) {
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

        $this->addSql("CREATE INDEX {$this->prefix}campaign_actions ON {$this->prefix}campaign_lead_event_log (event_id, non_action_path_taken)");
        $this->addSql("CREATE INDEX {$this->prefix}campaign_stats ON {$this->prefix}campaign_lead_event_log (campaign_id, event_id, date_triggered)");
    }
}
