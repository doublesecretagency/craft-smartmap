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

use Craft;
use craft\web\AssetBundle;
use doublesecretagency\smartmap\SmartMap;
use yii\web\HttpException;

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

        // Get request services
        $request = Craft::$app->getRequest();

        // Get browser key
        $settings = SmartMap::$plugin->getSettings();
        $key = trim($settings->getGoogleBrowserKey());

        // Ensure key exists
        if (!$key) {
            throw new HttpException('Google Maps API keys are required.');
        }

        // Check path to see if we're creating a new field
        $newField = preg_match('#actions/fields/render-settings#', $request->getPathInfo());

        // CDN path for Google Maps API
        $googleMapsApi = "https://maps.googleapis.com/maps/api/js?key={$key}";

        // If creating a new field, append callback
        if ($request->getIsCpRequest() && $newField) {
            $googleMapsApi .= "&callback=initAddressFieldtypeSettings";
        }

        // Register Google Maps API JS
        $this->js = [
            $googleMapsApi
        ];

    }

}
