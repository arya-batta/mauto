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

class Version20180928104415 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     *
     * @throws SkipMigrationException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function preUp(Schema $schema)
    {
        if ($schema->getTable(MAUTIC_TABLE_PREFIX.'licenseinfo')->hasColumn('total_sms_count')) {
            throw new SkipMigrationException('Schema includes this migration');
        }

        if ($schema->getTable(MAUTIC_TABLE_PREFIX.'licenseinfo')->hasColumn('actual_sms_count')) {
            throw new SkipMigrationException('Schema includes this migration');
        }

        if ($schema->getTable(MAUTIC_TABLE_PREFIX.'licenseinfo')->hasColumn('sms_provider')) {
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

        $this->addSql("ALTER TABLE {$this->prefix}licenseinfo ADD total_sms_count VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->addSql("ALTER TABLE {$this->prefix}licenseinfo ADD actual_sms_count VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL");
        $this->addSql("ALTER TABLE {$this->prefix}licenseinfo ADD sms_provider VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL");
    }
}
