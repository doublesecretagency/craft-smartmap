<?php
/**
 * Smart Map plugin for Craft CMS
 *
 * The most comprehensive proximity search and mapping tool for Craft.
 *
 * @author    Double Secret Agency
 * @link      https://www.doublesecretagency.com/
 * @copyright Copyright (c) 2014 Double Secret Agency
 */

namespace doublesecretagency\smartmap\migrations;

use craft\db\Migration;
use craft\db\Query;

/**
 * Migration: Change handle to field ID
 * @since 3.0.0
 */
class m140811_000001_smartMap_changeHandleToFieldId extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%smartmap_addresses}}', 'fieldId')) {
            // Create foreign key
            $this->addColumn('{{%smartmap_addresses}}', 'fieldId', $this->integer()->after('elementId'));
            $this->createIndex(null, '{{%smartmap_addresses}}', ['fieldId']);
            $this->addForeignKey(null, '{{%smartmap_addresses}}', ['fieldId'], '{{%fields}}', ['id'], 'CASCADE');
            // Get all Address fields
            $fields = (new Query())
                ->select(['id', 'handle'])
                ->from(['{{%fields}}'])
                ->where(['or',
                    ['type' => 'SmartMap_Address'], // OLD
                    ['type' => 'doublesecretagency\\smartmap\\fields\\Address'] // NEW
                ])
                ->all($this->db);
            // Update existing Address data
            foreach ($fields as $field) {
                $data = ['fieldId' => $field['id']];
                $this->update('{{%smartmap_addresses}}', $data, ['handle' => $field['handle']]);
            }
            // After values have been transferred, disallow null fieldId values
            $this->alterColumn('{{%smartmap_addresses}}', 'fieldId', $this->integer()->notNull());
            // Remove old column
            $this->dropColumn('{{%smartmap_addresses}}', 'handle');
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m140811_000001_smartMap_changeHandleToFieldId cannot be reverted.\n";

        return false;
    }

}