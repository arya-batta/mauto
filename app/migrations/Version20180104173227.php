<?php

namespace Mautic\Migrations;

use Doctrine\DBAL\Migrations\SkipMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180104173227 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     *
     * @throws SkipMigrationException
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function preUp(Schema $schema)
    {
        if ($schema->hasTable(MAUTIC_TABLE_PREFIX.'plugin_integration_settings')) {
            $row = $this->connection->createQueryBuilder()
                ->select('*')
                ->from($this->prefix.'plugin_integration_settings')
                ->where('name = "SolutionInfinity"')
                ->execute()
                ->fetch()
            ;

            if ($row !== false) {
                throw new SkipMigrationException('Schema includes this migration');
            }
        } else {
            throw new SkipMigrationException('Schema includes this migration');
        }
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $sql = <<<SQL
INSERT INTO {$this->prefix}plugin_integration_settings (
  name, 
  is_published, 
  supported_features, 
  api_keys, 
  feature_settings
) VALUES ('SolutionInfinity',0,'','','')
SQL;
        $this->addSql($sql);
        /*$insert = [
            'name' => 'Solution Infini',
            'is_published'     => '0',
            'supported_features'  => '',
            'api_keys'        => '',
            'feature_settings' => '',
        ];*/

        //$this->connection->insert($this->prefix.'plugin_integration_settings', $insert);
        //unset($insert);
    }
}
