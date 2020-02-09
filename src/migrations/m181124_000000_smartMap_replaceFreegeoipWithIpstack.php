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

use Craft;
use craft\db\Migration;
use doublesecretagency\smartmap\SmartMap;

/**
 * Migration: Replace FreeGeoIp.net with ipstack
 * @since 3.2.0
 */
class m181124_000000_smartMap_replaceFreegeoipWithIpstack extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Get settings
        $settings = SmartMap::$plugin->getSettings();

        // If no settings exist, bail
        if (!$settings) {
            return true;
        }

        // Convert model into array
        $settings = $settings->getAttributes();

        // If setting doesn't exist, bail
        if (!isset($settings['geolocation'])) {
            return true;
        }

        // If not set to "freegeoip", bail
        if ('freegeoip' != $settings['geolocation']) {
            return true;
        }

        // Modify settings
        $settings['geolocation'] = 'ipstack';

        // Save settings
        Craft::$app->getPlugins()->savePluginSettings(SmartMap::$plugin, $settings);

        // Return true
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181124_000000_smartMap_replaceFreegeoipWithIpstack cannot be reverted.\n";

        return false;
    }

}
