<?php
namespace Craft;

class SmartMapService extends BaseApplicationComponent
{

    public $targetCoords; // TEMP: Until P&T "distance" fix

    public $measurementUnit;
    public $mapApi;
    public $mapApiKey;

    public $dbPrefix;
    public $pluginTable;

    public $content;
    public $isNewContent;


    // TEMP: Until P&T "distance" fix
    // Use haversine formula
    private function _haversinePHP($coords_1, $coords_2)
    {
        // Determine unit of measurement
        switch ($this->measurementUnit) {
            case MeasurementUnit::Kilometers:
                $unitVal = 6371;
                break;
            default:
            case MeasurementUnit::Miles:
                $unitVal = 3959;
                break;
        }
        // Set coordinates
        $lat_1 = $coords_1['lat'];
        $lng_1 = $coords_1['lng'];
        $lat_2 = $coords_2['lat'];
        $lng_2 = $coords_2['lng'];
        // Calculate haversine formula
        return ($unitVal * acos(cos(deg2rad($lat_1)) * cos(deg2rad($lat_2)) * cos(deg2rad($lng_2) - deg2rad($lng_1)) + sin(deg2rad($lat_1)) * sin(deg2rad($lat_2))));
    }
    // END TEMP

    // Check if API key is valid
    public function checkApiKey()
    {
        if (!$this->mapApiKey) {
            throw new Exception("Please enter your Google Maps API key. [/admin/settings/plugins/smartmap]");
        }
    }


    // ==================================================== //
    // CALLED VIA SmartMap_AddressFieldType::modifyElementsQuery()
    // ==================================================== //

    // Modify fieldtype query
    public function modifyQuery(DbCommand $query, $params)
    {
        // Join with plugin table
        $query->join($this->pluginTable, 'elements.id = '.$this->dbPrefix.$this->pluginTable.'.elementId');
        // Search by comparing coordinates
        $filter = $this->_parseFilter($params);
        $this->_searchCoords($query, $filter);
        // Return modified query
        return $query;
    }


    // ==================================================== //
    // CALLED VIA SmartMapPlugin::init()
    // ==================================================== //

    // Once the content has been saved...
    public function contentSaved(ContentModel $content, $isNewContent)
    {
        $this->content = $content;
        $this->isNewContent = $isNewContent;
    }


    // ==================================================== //
    // CALLED VIA FIELDTYPE
    // ==================================================== //

    // Save field to plugin table
    public function saveAddressField(BaseFieldType $fieldType)
    {
        // Get elementId and handle
        $elementId = $fieldType->element->id;
        $handle    = $fieldType->model->handle;

        // Check if attribute exists
        if (!$this->content->getAttribute($handle)) {
            return false;
        }

        // Set specified attributes
        $attr = $this->content[$handle];

        // Attempt to load existing record
        $addressRecord = SmartMap_AddressRecord::model()->findByAttributes(array(
            'elementId' => $elementId,
            'handle'    => $handle,
        ));

        // If no record exists, create new record
        if (!$addressRecord) {
            $addressRecord = new SmartMap_AddressRecord;
            $attr['elementId'] = $elementId;
            $attr['handle']    = $handle;
        }

        // Set record attributes
        $addressRecord->setAttributes($attr, false);

        //if (!$addressRecord->save()) {
        //    $errors = $addressRecord->getErrors();
        //}

        return $addressRecord->save();

    }

    // Retrieves address from 3rd party table
    public function getAddress(BaseFieldType $fieldType)
    {
        // Load record (if exists)
        $addressRecord = SmartMap_AddressRecord::model()->findByAttributes(array(
            'elementId' => $fieldType->element->id,
            'handle'    => $fieldType->model->handle,
        ));

        // Get attributes
        if ($addressRecord) {
            $attr = $addressRecord->getAttributes();
            $attr['distance'] = $this->_haversinePHP($this->targetCoords, $attr); // TEMP: Until P&T "distance" fix
        } else {
            $attr = array();
        }

        return $attr;
    }

    // ==================================================== //
    // PRIVATE METHODS
    // ==================================================== //

    // Parse query filter
    private function _parseFilter($params)
    {
        if (is_array($params['target'])) {
            if (!$this->_isAssoc($params['target']) && count($params['target']) == 2) {
                $lat = $params['target'][0];
                $lng = $params['target'][1];
            } else {
                $lat = $this->_findKeyInArray($params['target'],array('latitude','lat'));
                $lng = $this->_findKeyInArray($params['target'],array('longitude','lng','lon','long'));
            }
            $coords = array(
                'lat' => $lat,
                'lng' => $lng,
            );
            $api = MapApi::LatLngArray;
        } else if (is_string($params['target'])) {
            $api = MapApi::GoogleMaps;
        } else {
            // Invalid target
            //  - Throw error here?
            $coords = $this->_searchNorthPole();
            $api = MapApi::LatLngArray;
        }

        $filter = SmartMap_FilterCriteriaModel::populateModel($params);

        // If page is specified
        if ($filter->page) {
            $filter->offset = ($filter->page * $filter->limit) - $filter->limit;
        }

        switch ($api) {
            case MapApi::LatLngArray:
                $filter->coords = $coords;
                break;
            case MapApi::GoogleMaps:
            default:
                $filter->coords = $this->_geocodeGoogleMapApi($filter->target);
                break;
        }

        $this->targetCoords    = $filter->coords; // TEMP: Until P&T "distance" fix
        $this->measurementUnit = $filter->units;  // TEMP: Until P&T "distance" fix

        return $filter;
    }

    // Search by coordinates
    private function _searchCoords(&$query, SmartMap_FilterCriteriaModel $filter)
    {
        // Implement haversine formula
        $haversine = $this->_haversine(
            $filter->coords['lat'],
            $filter->coords['lng'],
            $filter->units
        );
        // Modify query
        $query
            ->addSelect($haversine.' AS distance')
            ->having('distance <= '.$filter->range)
        ;
    }

    // Use haversine formula
    private function _haversine($lat, $lng)
    {
        // Determine unit of measurement
        switch ($this->measurementUnit) {
            case MeasurementUnit::Kilometers:
                $unitVal = 6371;
                break;
            default:
            case MeasurementUnit::Miles:
                $unitVal = 3959;
                break;
        }
        // Set table reference
        $table = $this->dbPrefix.$this->pluginTable;
        // Calculate haversine formula
        return "($unitVal * acos(cos(radians($lat)) * cos(radians($table.lat)) * cos(radians($table.lng) - radians($lng)) + sin(radians($lat)) * sin(radians($table.lat))))";
    }

    // Get coordinates from Google Maps API
    private function _geocodeGoogleMapApi($target)
    {

        $api = 'http://maps.googleapis.com/maps/api/geocode/json?address='.rawurlencode($target).'&sensor=false';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($ch), true);

        if (empty($response['results'])) {
            return $this->_searchNorthPole();
        } else {
            return $response['results'][0]['geometry']['location'];
        }

    }

    // Invalid target, search from North Pole
    private function _searchNorthPole()
    {
        return array(
            'lat' => 90,
            'lng' => 0,
        );
    }


    // ==================================================== //
    // HELPER FUNCTIONS
    // ==================================================== //

    // Get the target from an array
    private function _findKeyInArray($array, $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            if (array_key_exists($key, $array)) {
                return $array[$key];
            }
        }
    }

    // Determine if array is associative
    private function _isAssoc($array) {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

}