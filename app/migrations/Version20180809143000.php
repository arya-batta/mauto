<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 12/6/18
 * Time: 7:15 PM.
 */

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

class Version20180809143000 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     *
     * @throws SkipMigrationException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function preUp(Schema $schema)
    {
        if ($schema->getTable(MAUTIC_TABLE_PREFIX.'emails')->hasColumn('preview_text')) {
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

        $this->addSql("ALTER TABLE {$this->prefix}emails ADD preview_text VARCHAR (300) DEFAULT NULL");
        $this->addSql("ALTER TABLE {$this->prefix}emails ADD unsubscribe_text VARCHAR (1000) DEFAULT NULL");
        $this->addSql("ALTER TABLE {$this->prefix}emails ADD postal_address VARCHAR (500) DEFAULT NULL");
    }
}
