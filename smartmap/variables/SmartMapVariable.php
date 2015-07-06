<?php
namespace Craft;

class SmartMapVariable
{

    // Renders details about "my" current location
    public function my()
    {
        craft()->smartMap->loadGeoData();
        return craft()->smartMap->here;
    }

    // Includes front-end Javascript
    public function js($renderHere = false)
    {
        $api  = '//maps.googleapis.com/maps/api/js';
        $api .= craft()->smartMap->googleBrowserKey('?');
        if ($renderHere) {
            return '
<script type="text/javascript" src="'.$api.'"></script>
<script type="text/javascript" src="'.UrlHelper::getResourceUrl('smartmap/js/smartmap.js').'"></script>
';
        } else {
            craft()->templates->includeJsFile($api);
            craft()->templates->includeJsResource('smartmap/js/smartmap.js');
        }
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

    // Lookup a target location, returning full JSON
    public function lookup($target)
    {
        return craft()->smartMap->lookup($target);
    }

    // Get the subfield label from its handle
    public function labelFromHandle($handle)
    {
        return craft()->smartMap_addressFieldType->labelFromHandle($handle);
    }

    // ================================================================== //
    // ================================================================== //

    // Move to SmartMap_InternalVariable.php

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
            $debugData['cacheValue']    = print_r(craft()->smartMap->cacheData['here'], true);
            $debugData['cacheExpires']  = craft()->smartMap->cacheData['expires'];
            $debugData['geoService']    = craft()->smartMap->cacheData['service'];
        }
        return $debugData;
    }
    
}