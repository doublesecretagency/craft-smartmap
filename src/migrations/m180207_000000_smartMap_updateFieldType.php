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

use doublesecretagency\smartmap\fields\Address;

/**
 * Migration: Update field type for Craft 3 compatibility
 * @since 3.0.0
 */
class m180207_000000_smartMap_updateFieldType extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Auto-update existing Address fields
        $this->update('{{%fields}}', [
            'type' => Address::class
        ], [
            'type' => 'SmartMap_Address'
        ], [], false);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180207_000000_smartMap_updateFieldType cannot be reverted.\n";

        return false;
    }

}