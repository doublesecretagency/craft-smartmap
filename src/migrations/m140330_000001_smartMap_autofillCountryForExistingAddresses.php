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
 * Migration: Autofill country for existing addresses
 * @since 3.0.0
 */
class m140330_000001_smartMap_autofillCountryForExistingAddresses extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Get locations with coordinates but no country
        $locations = (new Query())
            ->select(['id', 'lat', 'lng'])
            ->from(['{{%smartmap_addresses}}'])
            ->where(['or',
                ['country' => null],
                ['country' => '']
            ])
            ->andWhere(['not',
                ['lat' => null]
            ])
            ->andWhere(['not',
                ['lng' => null]
            ])
            ->all($this->db);
        // Loop through locations
        foreach ($locations as $address) {
            // Ping Google Maps API
            $coords = $address['lat'].','.$address['lng'];
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$coords.'&sensor=false';
            $response = @file_get_contents($url);
            // Skip if no results
            if (!$response) {continue;}
            $json = json_decode($response, true);
            // Skip if no match found
            if ('OK' != $json['status']) {continue;}
            $components = $json['results'][0]['address_components'];
            // Skip if components can't be collected
            if (!$components) {continue;}
            // Loop through components
            foreach ($components as $c) {
                // If country component
                if (in_array('country', $c['types'])) {
                    $data = ['country' => $c['long_name']];
                    $this->update('{{%smartmap_addresses}}', $data, ['id' => $address['id']]);
                    break;
                }
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m140330_000001_smartMap_autofillCountryForExistingAddresses cannot be reverted.\n";

        return false;
    }

}