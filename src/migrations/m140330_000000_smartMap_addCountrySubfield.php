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
 * Migration: Add country subfield
 * @since 3.0.0
 */
class m140330_000000_smartMap_addCountrySubfield extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%smartmap_addresses}}', 'country')) {
            $this->addColumn('{{%smartmap_addresses}}', 'country', $this->string()->after('zip'));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m140330_000000_smartMap_addCountrySubfield cannot be reverted.\n";

        return false;
    }

}