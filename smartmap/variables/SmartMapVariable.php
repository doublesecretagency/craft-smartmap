<?php
namespace Craft;

class SmartMapVariable
{

    // Renders details about "my" current location
    public function my()
    {
        return craft()->smartMap->here;
    }

    // Includes front-end Javascript
    public function js()
    {
        $api  = '//maps.google.com/maps/api/js';
        $api .= craft()->smartMap->appendGoogleApiKey('?');
        craft()->templates->includeJsFile($api);
        craft()->templates->includeJsResource('smartmap/js/smartmap.js');
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
            'remote_addr'   => $_SERVER['REMOTE_ADDR'],
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
            $debugData['cacheValue']    = print_r(craft()->smartMap->cacheData['here'], true);
            $debugData['cacheExpires']  = craft()->smartMap->cacheData['expires'];
            $debugData['geoService']    = craft()->smartMap->cacheData['service'];
        }
        return $debugData;
    }
    
}