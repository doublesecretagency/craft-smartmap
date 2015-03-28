<?php
namespace Craft;

class SmartMap_AddressModel extends BaseModel
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
            'fieldId'   => AttributeType::Number,
            'handle'    => AttributeType::String,
            'street1'   => AttributeType::String,
            'street2'   => AttributeType::String,
            'city'      => AttributeType::String,
            'state'     => AttributeType::String,
            'zip'       => AttributeType::String,
            'country'   => AttributeType::String,
            'lat'       => $coordColumn,
            'lng'       => $coordColumn,
            'distance'  => $coordColumn,
        );

        // THESE WILL GO IN THE "MAP MODEL":
        // 'zoom' => AttributeType::Number,
        // 'mapType' => AttributeType::Enum, // roadmap, satellite, hybrid, terrain (& streetview?)
        // 'locations' => AttributeType::Mixed, // Array of "Location" Models

        // USE THESE PARAMETERS IN THE "MAP MODEL"
        // https://developers.google.com/maps/documentation/staticmaps/index

    }

    /**
     * Nicely formats an address
     *
     * @return string
     */
    public function format($unitLine = false, $cityLine = false)
    {
        $unitGlue = ($unitLine ? ', ' : '<br />');
        $cityGlue = ($cityLine ? ', ' : '<br />');

        $formatted  = '';
        $formatted .= ($this->street1 ? $this->street1 : '');
        $formatted .= ($this->street1 && $this->street2 ? $unitGlue : '');
        $formatted .= ($this->street2 ? $this->street2 : '');
        $formatted .= ($this->city || $this->state ? $cityGlue : '');
        $formatted .= ($this->city ? $this->city : '');
        $formatted .= (($this->city && $this->state) ? ', ' : '');
        $formatted .= ($this->state ? $this->state : '').' ';
        $formatted .= ($this->zip ? $this->zip : '');

        return TemplateHelper::getRaw(trim($formatted));
    }

    /**
     * Checks whether address is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return (
               empty($this->street1)
            && empty($this->street2)
            && empty($this->city)
            && empty($this->state)
            && empty($this->zip)
            && empty($this->country)
        );
    }

    /**
     * Checks whether address has coordinates.
     *
     * @return bool
     */
    public function hasCoords()
    {
        return (
               !empty($this->lat) && is_numeric($this->lat)
            && !empty($this->lng) && is_numeric($this->lng)
        );
    }

}