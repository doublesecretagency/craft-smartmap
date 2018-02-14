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
        $settings = $this->_getSettings();

        // If no settings exist, bail
        if (!is_array($settings)) {
            return true;
        }

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
        $this->_setSettings($settings);

        // Return true
        return true;
    }

    /**
     * Get plugin settings
     *
     * @return array
     */
    private function _getSettings()
    {
        // Get original settings value
        $oldSettings = (new Query())
            ->select(['settings'])
            ->from(['{{%plugins}}'])
            ->where(['handle' => 'smart-map'])
            ->one($this->db);
        return @json_decode($oldSettings['settings'], true);
    }

    /**
     * Save plugin settings
     *
     * @param array $settings Updated settings
     */
    private function _setSettings($settings)
    {
        // Update settings field
        $newSettings = json_encode($settings);
        $data = ['settings' => $newSettings];
        $this->update('{{%plugins}}', $data, ['handle' => 'smart-map']);
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