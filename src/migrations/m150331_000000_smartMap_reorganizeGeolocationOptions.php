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
 * Migration: Reorganize geolocation options
 * @since 3.0.0
 */
class m150331_000000_smartMap_reorganizeGeolocationOptions extends Migration
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

        // Default geolocation service
        $service = 'none';

        // If "enableService" value exists
        if (isset($settings['enableService'])) {

            // Get currently enabled
            $enabled = $settings['enableService'];

            // If geolocation is enabled
            if (in_array('geolocation', $enabled)) {

                // If MaxMind is not enabled, default to FreeGeoIp.net
                if (in_array('maxmind', $enabled)) {
                    $service = 'maxmind';
                } else {
                    $service = 'freegeoip';
                }

            }

        }

        // Set geolocation selection
        $settings['geolocation'] = $service;

        // Remove "enableService" value
        unset($settings['enableService']);

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
        echo "m150331_000000_smartMap_reorganizeGeolocationOptions cannot be reverted.\n";

        return false;
    }

}
