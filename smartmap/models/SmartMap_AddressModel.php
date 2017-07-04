<?php
namespace Craft;

class SmartMap_AddressModel extends BaseModel
{

    public function __toString()
    {
        return (string) $this->format(true, true);
    }

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
            'coords'    => AttributeType::Mixed,
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
     * @inheritDoc BaseModel::populateModel()
     *
     * @param mixed $attributes
     *
     * @return SmartMap_AddressModel
     */
    public static function populateModel($attributes)
    {
        $address = parent::populateModel($attributes);

        // If address has legitimate coordinates
        if ($address->hasCoords()) {

            // Set coords array
            $coords = [
                $address->lat,
                $address->lng
            ];

        } else {

            // Set coords to NULL
            $coords = null;

        }

        $address->coords = $coords;

        return $address;
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

        // Merge repeated commas
        $formatted = preg_replace('/(, ){2,}/', ', ', $formatted);
        // Eliminate leading comma
        $formatted = preg_replace('/^, /', '', $formatted);
        // Eliminate trailing comma
        $formatted = preg_replace('/, $/', '', $formatted);

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

    /**
     * Generates URL of full Google Map
     *
     * @return string
     */
    public function googleMapUrl()
    {
        return craft()->smartMap_variables->linkToGoogle($this);
    }

    /**
     * Generates URL for directions
     *
     * @return string
     */
    public function directionsUrl($destinationTitle = false, $startingTitle = false, $startingAddress = false)
    {
        // First parameter is skippable
        if (is_string($startingAddress)) {
            $destinationTitle = $startingAddress;
            $startingAddress = false;
        }
        // Prep destination address
        if ($this->hasCoords()) {
            $destinationCoords = $this->lat.','.$this->lng;
        } else {
            return '#invalid-address-coordinates';
        }
        if (!$destinationTitle) {
            $destinationTitle = $this->format(true, true);
        }
        // Prep starting address
        if (is_a($startingAddress, 'Craft\SmartMap_AddressModel')) {
            if ($startingAddress->hasCoords()) {
                $startingCoords = $startingAddress->lat.','.$startingAddress->lng;
            } else {
                return '#invalid-starting-address-coordinates';
            }
            if (!$startingTitle) {
                $startingTitle = $startingAddress->format(true, true);
            }
        } else {
            $startingAddress = false;
        }
        // Compile URL
        $url = 'https://maps.google.com/maps?';
        if ($startingAddress) {
            $url .= 'saddr='.rawurlencode($startingTitle).'@'.$startingCoords.'&';
        }
        $url .= 'daddr='.rawurlencode($destinationTitle).'@'.$destinationCoords;
        return $url;
    }

}