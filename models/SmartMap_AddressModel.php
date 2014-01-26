<?php
namespace Craft;

class SmartMap_AddressModel extends BaseModel // Change name to "LocationModel" ?
{
    protected function defineAttributes()
    {

        // decimal(12,8)
        $coordColumn = array(
            AttributeType::Number,
            'column'   => ColumnType::Decimal,
            'length'   => 12,
            'decimals' => 8,
        );

        return array(
            'elementId' => AttributeType::Number,
            'handle'    => AttributeType::String,
            'street1'   => AttributeType::String,
            'street2'   => AttributeType::String,
            'city'      => AttributeType::String,
            'state'     => AttributeType::String,
            'zip'       => AttributeType::String,
            'lat'       => $coordColumn,
            'lng'       => $coordColumn,
        );

        // THESE WILL GO IN THE "MAP MODEL":
        // 'zoom' => AttributeType::Number,
        // 'mapType' => AttributeType::Enum, // roadmap, satellite, hybrid, terrain (& streetview?)
        // 'locations' => AttributeType::Mixed, // Array of "Location" Models

        // USE THESE PARAMETERS IN THE "MAP MODEL"
        // https://developers.google.com/maps/documentation/staticmaps/index

    }
}