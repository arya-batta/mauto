<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 27/9/18
 * Time: 7:42 PM.
 */

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

class Version20181008102015 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     *
     * @throws SkipMigrationException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function preUp(Schema $schema)
    {
        if ($schema->getTable(MAUTIC_TABLE_PREFIX.'emails')->hasColumn('google_tags')) {
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

        $this->addSql("ALTER TABLE {$this->prefix}emails ADD google_tags TINYINT(1) COLLATE utf8_unicode_ci DEFAULT 0");
    }
}
