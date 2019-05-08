<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Mautic\CoreBundle\Doctrine\AbstractMauticMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190507110740 extends AbstractMauticMigration
{
    /**
     * @param Schema $schema
     */
    public function preUp(Schema $schema)
    {
        if ($schema->getTable(MAUTIC_TABLE_PREFIX.'leads')->hasColumn('status')) {
            throw new SkipMigrationException('Schema includes this migration');
        }
    }

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE leads ADD status VARCHAR(255) DEFAULT NULL, ADD created_source VARCHAR(255) DEFAULT NULL;');
        // Fix a bug in dynamic content and segments if there are boolean fields

        // Check if there are even boolean fields to worry about
        $qb = $this->connection->createQueryBuilder();
        $qb->select('max(lf.field_order) as fieldorder')
           ->from($this->prefix.'lead_fields', 'lf');
        $fieldOrder = $qb->execute()->fetchAll();

        $fieldorder  = $fieldOrder[0]['fieldorder'];
        $statusorder = $fieldorder + 1;
        $sourceorder = $statusorder + 1;
        $now         = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $sql         = $this->createLeadStatus($statusorder, $now);
        $this->addSql($sql);
        $sql = $this->createLeadSource($sourceorder, $now);
        $this->addSql($sql);
    }

    public function createLeadStatus($statusorder, $now)
    {
        $list            = [];
        $list['list'][0] = [
            'label' => 'Active',
            'value' => 1,
        ];
        $list['list'][1] = [
            'label' => 'Engaged',
            'value' => 2,
        ];
        $list['list'][2] = [
            'label' => 'Invalid',
            'value' => 3,
        ];
        $list['list'][3] = [
            'label' => 'Complaint',
            'value' => 4,
        ];
        $list['list'][4] = [
            'label' => 'Unsubscribed',
            'value' => 5,
        ];
        $list['list'][5] = [
            'label' => 'Not Confirmed',
            'value' => 6,
        ];
        $properties = serialize($list);
        $sql        = <<<SQL
insert into {$this->prefix}lead_fields (is_published,date_added,created_by,created_by_user,label,alias,type,field_group,default_value,is_required,is_fixed,is_visible,is_short_visible,is_listable,is_publicly_updatable,is_unique_identifer,field_order,object,properties) values (1,'$now',0,'System User','Lead status','status','select','core',1,0,1,1,1,1,0,0,'$statusorder','lead','$properties'
)
SQL;

        return $sql;
    }

    public function createLeadSource($sourceorder, $now)
    {
        $list            = [];
        $list['list'][0] = [
            'label' => 'Manual',
            'value' => 1,
        ];
        $list['list'][1] = [
            'label' => 'Import',
            'value' => 2,
        ];
        $list['list'][2] = [
            'label' => 'Form Submit',
            'value' => 3,
        ];
        $list['list'][3] = [
            'label' => 'API',
            'value' => 4,
        ];
        $list['list'][4] = [
            'label' => 'Integration',
            'value' => 5,
        ];
        $properties = serialize($list);
        $sql        = <<<SQL
insert into {$this->prefix}lead_fields (
  is_published,date_added,created_by,created_by_user,label,alias,type,field_group,default_value,is_required,is_fixed,is_visible,is_short_visible,is_listable,is_publicly_updatable,is_unique_identifer,field_order,object,properties) values (1,'$now',0,'System User','Created Source','created_source','select','core',1,0,1,1,1,1,0,0,'$sourceorder','lead','$properties'
)
SQL;

        return $sql;
    }
}
