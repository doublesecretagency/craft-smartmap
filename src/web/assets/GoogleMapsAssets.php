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

namespace doublesecretagency\smartmap\web\assets;

use craft\web\AssetBundle;

use doublesecretagency\smartmap\SmartMap;

/**
 * Class GoogleMapsAssets
 * @since 3.0.0
 */
class GoogleMapsAssets extends AssetBundle
{

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        // Google Maps API
        $googleMapsApi = 'https://maps.googleapis.com/maps/api/js';

        // Get browser key
        $settings = SmartMap::$plugin->getSettings();
        $key = trim($settings['googleBrowserKey']);

        // Append browser key
        if ($key) {
            $googleMapsApi .= "?key={$key}";
        }

        $this->js = [
            $googleMapsApi
        ];
    }

}