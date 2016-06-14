<?php
namespace Craft;

class SmartMap_FilterCriteriaModel extends BaseModel
{
    protected function defineAttributes()
    {

        $defaultCoords = array(
            'lat' => NULL,
            'lng' => NULL,
        );

        return array(

            // Geomapping
            'target' => AttributeType::String,
            'coords' => array(AttributeType::Mixed,  'default' => $defaultCoords),
            'range'  => array(AttributeType::Number, 'default' => 25),
            'units'  => array(AttributeType::String, 'default' => MeasurementUnit::Miles),

            // Field
            'elementType'   => array(AttributeType::String, 'default' => ElementType::Entry),
            'sectionHandle' => AttributeType::String,
            'fieldHandle'   => AttributeType::String,
            'fieldId'       => AttributeType::Number,

            // SQL Search Query
            'page'   => array(AttributeType::Number, 'default' => NULL),
            'limit'  => array(AttributeType::Number, 'default' => 20),
            'offset' => array(AttributeType::Number, 'default' => 0),

        );

    }
}