<?php
namespace Craft;

class SmartMapVariable
{

    private $_jsRendered = false;

    // Renders details about visitor's current location
    public function visitor()
    {
        craft()->smartMap->loadGeoData();
        return craft()->smartMap->visitor;
    }

    // ALIAS:  visitor()
    public function my()
    {
        return $this->visitor();
    }

    // Includes front-end Javascript
    public function js($renderHere = true)
    {
        return craft()->smartMap_variables->loadJs($renderHere);
    }

    // Display a dynamic Google map
    public function map($markers = false, $options = array())
    {
        return craft()->smartMap_variables->dynamicMap($markers, $options);
    }

    // Display a static map image
    public function img($markers = false, $options = array())
    {
        return craft()->smartMap_variables->staticMap($markers, $options);
    }

    // Render the source for a static map image
    public function imgSrc($markers = false, $options = array())
    {
        return craft()->smartMap_variables->staticMapSrc($markers, $options);
    }

    // Display map from a KML file
    public function kml($kmlFile, $options = array())
    {
        return craft()->smartMap_variables->kmlMap($kmlFile, $options);
    }

    // Add a KML layer to an existing map
    public function kmlLayer($kmlFile, $mapId = false)
    {
        return craft()->smartMap_variables->kmlMapLayer($kmlFile, $mapId);
    }

    // Link to the Google map
    public function linkToGoogle($address)
    {
        return craft()->smartMap_variables->linkToGoogle($address);
    }

    // Link to directions on a Google map
    public function directions($address, $title = null)
    {
        return craft()->smartMap_variables->linkToDirections($address, $title);
    }

    // Lookup a target location, returning full JSON
    public function lookup($target, $components = array())
    {
        return craft()->smartMap->lookup($target, $components);
    }

    // Lookup a target location, returning only coordinates of first result
    public function lookupCoords($target, $components = array())
    {
        return craft()->smartMap->lookupCoords($target, $components);
    }

    // Get the subfield label from its handle
    public function labelFromHandle($handle)
    {
        return craft()->smartMap_addressFieldType->labelFromHandle($handle);
    }

    // ================================================================== //

    // Google API Server Key
    public function serverKey()
    {
        return $this->_googleKey('googleServerKey');
    }

    // Google API Browser Key
    public function browserKey()
    {
        return $this->_googleKey('googleBrowserKey');
    }

    // Return Google key
    public function _googleKey($key)
    {
        $settings = craft()->smartMap->settings->attributes;
        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        } else {
            return false;
        }
    }

    // ================================================================== //
    // ================================================================== //

    // FOR INTERNAL USE ONLY
    public function settings()
    {
        return craft()->smartMap->settings;
    }
    public function debug()
    {
        $debugData = array(
            'remote_addr'   => craft()->request->userHostAddress,
            'cookieValue'   => false,
            'cookieExpires' => false,
            'cacheValue'    => false,
            'cacheExpires'  => false,
            'geoService'    => 'PHP',
        );
        if (craft()->smartMap->cookieData) {
            $debugData['cookieValue']   = craft()->smartMap->cookieData['ip'];
            $debugData['cookieExpires'] = craft()->smartMap->cookieData['expires'];
        }
        if (craft()->smartMap->cacheData) {
            $debugData['cacheValue']    = print_r(craft()->smartMap->cacheData['visitor'], true);
            $debugData['cacheExpires']  = craft()->smartMap->cacheData['expires'];
            $debugData['geoService']    = craft()->smartMap->cacheData['service'];
        }
        return $debugData;
    }

}