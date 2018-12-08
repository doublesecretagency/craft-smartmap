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

namespace doublesecretagency\smartmap\models;

use Craft;
use craft\base\Model;
use craft\helpers\Template;

use doublesecretagency\smartmap\SmartMap;

/**
 * Class Address
 * @since 3.0.0
 */
class Address extends Model
{

    /** @var int|null  $elementId  Explanation. */
    public $elementId;

    /** @var int|null  $fieldId  Explanation. */
    public $fieldId;

    /** @var string|null  $handle  Explanation. */
    public $handle;

    /** @var string|null  $street1  Explanation. */
    public $street1;

    /** @var string|null  $street2  Explanation. */
    public $street2;

    /** @var string|null  $city  Explanation. */
    public $city;

    /** @var string|null  $state  Explanation. */
    public $state;

    /** @var string|null  $zip  Explanation. */
    public $zip;

    /** @var string|null  $country  Explanation. */
    public $country;

    /** @var float|null  $lat  Explanation. */
    public $lat;

    /** @var float|null  $lng  Explanation. */
    public $lng;

    /** @var array|null  $coords  Explanation. */
    public $coords;

    /** @var float|null  $distance  Explanation. */
    public $distance;

    public function __construct($attributes = [], array $config = [])
    {
        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                if (property_exists($this, $key)) {
                    $this[$key] = $value;
                }
            }
        }

        if (null !== $this->lat && null !== $this->lng) {
            $this->coords = [$this->lat, $this->lng];
        }

        parent::__construct($config);
    }

    public function __toString(): string
    {
        return (string) $this->format(true, true);
    }

    /**
     * @inheritDoc BaseModel::populateModel()
     *
     * @param mixed $attributes
     *
     * @return Address
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

        $hasStreet = ($this->street1 || $this->street2);
        $hasCityState = ($this->city || $this->state || $this->zip);

        $formatted  = '';
        $formatted .= ($this->street1 ? $this->street1 : '');
        $formatted .= ($this->street1 && $this->street2 ? $unitGlue : '');
        $formatted .= ($this->street2 ? $this->street2 : '');
        $formatted .= ($hasStreet && $hasCityState ? $cityGlue : '');
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

        return Template::raw(trim($formatted));
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
    public function googleMapUrl($title = null)
    {
        return SmartMap::$plugin->smartMap_variables->linkToGoogle($this, $title);
    }

    /**
     * Generates URL for directions
     *
     * @return string
     */
    public function directionsUrl($destinationTitle = false, $startingTitle = false, $startingAddress = false)
    {
        return SmartMap::$plugin->smartMap_variables->linkToDirections($this, $destinationTitle, $startingTitle, $startingAddress);
    }

}
