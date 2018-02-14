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
        $settings = $this->_getSettings();

        // If no settings exist, bail
        if (!is_array($settings)) {
            return true;
        }

        // Default geolocation service
        $service = 'none';

        // If "enableService" value exists
        if (array_key_exists('enableService', $settings)) {

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
        echo "m150331_000000_smartMap_reorganizeGeolocationOptions cannot be reverted.\n";

        return false;
    }

}