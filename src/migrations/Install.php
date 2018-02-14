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

/**
 * Installation Migration
 * @since 3.0.0
 */
class Install extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%smartmap_addresses}}');
    }

    /**
     * Creates the tables.
     *
     * @return void
     */
    protected function createTables()
    {
        $this->createTable('{{%smartmap_addresses}}', [
            'id'          => $this->primaryKey(),
            'elementId'   => $this->integer()->notNull(),
            'fieldId'     => $this->integer()->notNull(),
            'street1'     => $this->string(),
            'street2'     => $this->string(),
            'city'        => $this->string(),
            'state'       => $this->string(),
            'zip'         => $this->string(),
            'country'     => $this->string(),
            'lat'         => $this->decimal(12, 8),
            'lng'         => $this->decimal(12, 8),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid'         => $this->uid(),
        ]);
    }

    /**
     * Creates the indexes.
     *
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(null, '{{%smartmap_addresses}}', ['elementId']);
        $this->createIndex(null, '{{%smartmap_addresses}}', ['fieldId']);
    }

    /**
     * Adds the foreign keys.
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(null, '{{%smartmap_addresses}}', ['elementId'], '{{%elements}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%smartmap_addresses}}', ['fieldId'],   '{{%fields}}',   ['id'], 'CASCADE');
    }

}