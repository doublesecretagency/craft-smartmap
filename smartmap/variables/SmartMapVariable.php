<?php
namespace Craft;

class SmartMapVariable
{

    // Link to the Google map
    public function linkToGoogle($address)
    {
        return craft()->smartMap_variables->linkToGoogle($address);
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

    // Renders details about "my" current location
    public function my()
    {
        return craft()->smartMap->here;
    }

    // Includes front-end Javascript
    public function js()
    {
        craft()->templates->includeJsFile('//maps.google.com/maps/api/js?sensor=false');
        craft()->templates->includeJsResource('smartmap/js/smartmap.js');
    }

    // FOR INTERNAL USE ONLY
    public function settings()
    {
        return craft()->smartMap->settings;
    }
    
}