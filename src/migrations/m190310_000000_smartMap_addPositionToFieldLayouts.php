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
 * Migration: Add a `position` value to Address layout subfields
 * @since 3.2.2
 */
class m190310_000000_smartMap_addPositionToFieldLayouts extends Migration
{

    /**
     * This migration caused too many problems.
     * We're going to neutralize it, and try again.
     *
     * @inheritdoc
     */
    public function safeUp()
    {
        // Successfully does nothing
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190310_000000_smartMap_addPositionToFieldLayouts cannot be reverted.\n";

        return false;
    }

}
