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
        $settings = $this->_getSettings();

        // If no settings exist, bail
        if (!is_array($settings)) {
            return true;
        }

        // If setting doesn't exist, bail
        if (!array_key_exists('geolocation', $settings)) {
            return true;
        }

        // If not set to "freegeoip", bail
        if ('freegeoip' != $settings['geolocation']) {
            return true;
        }

        // Modify settings
        $settings['geolocation'] = 'ipstack';
        $settings['ipstackAccessKey'] = '';

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
        echo "m181124_000000_smartMap_replaceFreegeoipWithIpstack cannot be reverted.\n";

        return false;
    }

}
