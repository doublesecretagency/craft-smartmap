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

namespace doublesecretagency\smartmap\variables;

use Craft;

use doublesecretagency\smartmap\SmartMap;

/**
 * Class SmartMapVariable
 * @since 3.0.0
 */
class SmartMapVariable
{

    // Display a dynamic Google map
    public function map($markers = false, $options = [])
    {
        return SmartMap::$plugin->smartMap_variables->dynamicMap($markers, $options);
    }

    // Display a static map image
    public function img($markers = false, $options = [])
    {
        return SmartMap::$plugin->smartMap_variables->staticMap($markers, $options);
    }

    // Render the source for a static map image
    public function imgSrc($markers = false, $options = [])
    {
        return SmartMap::$plugin->smartMap_variables->staticMapSrc($markers, $options);
    }

    // Display map from a KML file
    public function kml($kmlFile, $options = [])
    {
        return SmartMap::$plugin->smartMap_variables->kmlMap($kmlFile, $options);
    }

    // Add a KML layer to an existing map
    public function kmlLayer($kmlFile, $mapId = false)
    {
        return SmartMap::$plugin->smartMap_variables->kmlMapLayer($kmlFile, $mapId);
    }

    // ========================================================================= //

    // Includes front-end Javascript
    public function js($renderHere = true)
    {
        return SmartMap::$plugin->smartMap_variables->loadAssets($renderHere);
    }

    // ========================================================================= //

    // Link to the Google map
    public function linkToGoogle($address, $title = null)
    {
        $docsUrl = SmartMap::DOCS_URL.'/linking-to-a-separate-google-map-page';
        $deprecationMessage = 'craft.smartMap.linkToGoogle() has been <a href="'.$docsUrl.'">deprecated</a>. Use element.addressFieldHandle.googleMapUrl() instead.';
        Craft::$app->getDeprecator()->log('craft.smartMap.linkToGoogle', $deprecationMessage);
        return SmartMap::$plugin->smartMap_variables->linkToGoogle($address, $title);
    }

    // Link to directions on a Google map
    public function directions($address, $title = null)
    {
        $docsUrl = SmartMap::DOCS_URL.'/linking-to-a-separate-google-map-page';
        $deprecationMessage = 'craft.smartMap.directions() has been <a href="'.$docsUrl.'">deprecated</a>. Use element.addressFieldHandle.directionsUrl() instead.';
        Craft::$app->getDeprecator()->log('craft.smartMap.directions', $deprecationMessage);
        return SmartMap::$plugin->smartMap_variables->linkToDirections($address, $title);
    }

    // ========================================================================= //

    // Renders details about visitor's current location
    public function visitor()
    {
        SmartMap::$plugin->smartMap->loadGeoData();
        return SmartMap::$plugin->smartMap->visitor;
    }

    // ========================================================================= //

    // Lookup a target location, returning full JSON
    public function lookup($target, $components = [])
    {
        return SmartMap::$plugin->smartMap->lookup($target, $components);
    }

    // Lookup a target location, returning only coordinates of first result
    public function lookupCoords($target, $components = [])
    {
        return SmartMap::$plugin->smartMap->lookupCoords($target, $components);
    }

    // ========================================================================= //

    // Set Google API Server Key
    public function setServerKey($key)
    {
        SmartMap::$plugin->getSettings()->setAttributes([
            'googleServerKey' => $key
        ], false);
    }

    // Set Google API Browser Key
    public function setBrowserKey($key)
    {
        SmartMap::$plugin->getSettings()->setAttributes([
            'googleBrowserKey' => $key
        ], false);
    }

    // Google API Server Key
    public function serverKey()
    {
        return SmartMap::$plugin->settings->getGoogleServerKey();
    }

    // Google API Browser Key
    public function browserKey()
    {
        return SmartMap::$plugin->settings->getGoogleBrowserKey();
    }

    // ========================================================================= //

    // Link to full documentation
    public function docsUrl()
    {
        return SmartMap::DOCS_URL;
    }

    // ========================================================================= //

    // Class denoting version number
    public function versionClass()
    {
        $currentCraft = Craft::$app->getVersion();
        $v34orHigher = version_compare($currentCraft, '3.4', '>=');
        return ($v34orHigher ? 'c34' : '');
    }

    // ========================================================================= //

    // Values for debug page
    public function debug()
    {
        $debugData = [
            'remote_addr'   => Craft::$app->getRequest()->getUserIP(),
            'cacheValue'    => false,
            'cacheExpires'  => false,
            'geoService'    => 'PHP',
        ];
        if (SmartMap::$plugin->smartMap->cacheData) {
            $debugData['cacheValue']    = print_r(SmartMap::$plugin->smartMap->cacheData['visitor'], true);
            $debugData['cacheExpires']  = SmartMap::$plugin->smartMap->cacheData['expires'];
            $debugData['geoService']    = SmartMap::$plugin->smartMap->cacheData['service'];
        }
        return $debugData;
    }

    // ========================================================================= //

    // Get the subfield label from its handle
    public function labelFromHandle($handle)
    {
        switch ($handle) {
            case 'street1': $label = 'Street Address';     break;
            case 'street2': $label = 'Apartment or Suite'; break;
            case 'city':    $label = 'City';               break;
            case 'state':   $label = 'State';              break;
            case 'zip':     $label = 'Zip Code';           break;
            case 'country': $label = 'Country';            break;
            case 'lat':     $label = 'Latitude';           break;
            case 'lng':     $label = 'Longitude';          break;
        }
        // If no label, bail
        if (!$label) {
            return false;
        }
        return Craft::t('smart-map', $label);
    }

}
