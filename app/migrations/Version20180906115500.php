<?php
/**
 * Created by PhpStorm.
 * User: cratio
 * Date: 6/9/18
 * Time: 12:01 PM
 */


namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;


/**
 * Set all fields listable because this feature was broken
 * and even some core fields are missing in segment filters.
 */
class Version20180906115500 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("UPDATE `{$this->prefix}lead_fields` SET is_short_visible = 0 WHERE alias = 'score';");
    }

}