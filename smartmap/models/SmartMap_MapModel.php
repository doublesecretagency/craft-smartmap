<?php
namespace Craft;

class SmartMap_MapModel extends BaseModel
{
    protected function defineAttributes()
    {

        // Image Formats
        $defaultFormats = array(
            ImageFormat::Png,
            ImageFormat::Png8,
            ImageFormat::Png32,
            ImageFormat::Gif,
            ImageFormat::Jpg,
            ImageFormat::JpgBaseline,
        );

        // Map Types
        $defaultMapTypes = array(
            MapType::Roadmap,
            MapType::Satellite,
            MapType::Terrain,
            MapType::Hybrid,
        );

        // https://developers.google.com/maps/documentation/staticmaps/index
        return array(
            // Location Parameters
            'center'   => array(AttributeType::String, 'default' => '0,0'),
            'zoom'     => array(AttributeType::Number, 'default' => 12),
            // Map Parameters
            'size'     => array(AttributeType::String, 'default' => '200x200'),
            'modern'   => array(AttributeType::Bool,   'default' => true), // "visual_refresh"
            'scale'    => array(AttributeType::Number, 'default' => 1),
            'format'   => array(AttributeType::Enum,   'default' => ImageFormat::Png, 'values' => $defaultFormats),
            'maptype'  => array(AttributeType::Enum,   'default' => MapType::Roadmap, 'values' => $defaultMapTypes),
            'language' => array(AttributeType::String),
            'region'   => array(AttributeType::String),
            // Feature Parameters
            //'markers'  => array(AttributeType::Mixed),
            //'path'     => array(AttributeType::Mixed),
            //'visible'  => array(AttributeType::Mixed),
            //'style'    => array(AttributeType::Mixed),
            // Reporting Parameters
            'sensor'   => array(AttributeType::Bool, 'default' => false),
        );

    }
}