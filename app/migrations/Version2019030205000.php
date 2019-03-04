<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 2/3/19
 * Time: 5:32 PM
 */

namespace Mautic\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

class Version2019030205000 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("ALTER TABLE {$this->prefix}sms_messages ADD COLUMN failed_count INT(11) NOT NULL");
    }
}