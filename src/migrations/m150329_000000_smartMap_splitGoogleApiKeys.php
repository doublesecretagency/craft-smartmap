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
 * Migration: Split Google API keys
 * @since 3.0.0
 */
class m150329_000000_smartMap_splitGoogleApiKeys extends Migration
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

        // If enableService isn't set to "google", bail
        if (!array_key_exists('enableService', $settings) || !in_array('google', $settings['enableService'])) {
            return true;
        }

        // If googleApiKey isn't set, bail
        if (!array_key_exists('googleApiKey', $settings)) {
            return true;
        }

        // Get existing API key
        $existingKey = $settings['googleApiKey'];

        // Modify settings
        $settings['googleServerKey']  = $existingKey;
        $settings['googleBrowserKey'] = $existingKey;
        unset($settings['googleApiKey']);

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
        echo "m150329_000000_smartMap_splitGoogleApiKeys cannot be reverted.\n";

        return false;
    }

}
