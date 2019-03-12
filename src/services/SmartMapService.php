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

namespace doublesecretagency\smartmap\services;

use yii\base\Event;
use yii\caching\FileCache;

use Craft;
use craft\base\Component;
use craft\elements\db\ElementQueryInterface;

use doublesecretagency\smartmap\SmartMap;
use doublesecretagency\smartmap\enums\MapApi;
use doublesecretagency\smartmap\enums\MeasurementUnit;
use doublesecretagency\smartmap\events\DetectLocationEvent;
use doublesecretagency\smartmap\fields\Address as AddressField;
use doublesecretagency\smartmap\models\Address as AddressModel;
use doublesecretagency\smartmap\records\Address as AddressRecord;
use doublesecretagency\smartmap\models\FilterCriteria as FilterCriteriaModel;

/**
 * Class SmartMapService
 * @since 3.0.0
 */
class SmartMapService extends Component
{

    const KEY_REQUIRED_MESSAGE = 'As of June 11, 2018, Google API keys are now <a href="'.SmartMap::DOCS_URL.'/get-google-api-keys">required</a>.';

    public $settings;

    public $visitor = false;
    public $geoInfoSet = false;
    public $cacheData = false;

    public $targetCoords;

    public $measurementUnit;

    // Load geo data
    public function loadGeoData()
    {
        // If visitor already exists, bail
        if ($this->visitor) {
            return;
        }

        // Visitor defaults to empty container array
        $this->visitor = [
            'ip'        => false,
            'city'      => false,
            'state'     => false,
            'zipcode'   => false,
            'country'   => false,
            'latitude'  => false,
            'longitude' => false,
            'coords'    => false,
        ];

        // Get geolocation service
        $geoSelection = SmartMap::$plugin->getSettings()->geolocation;
        $geoServices  = ['ipstack','maxmind'];
        $usingGeo     = in_array($geoSelection, $geoServices);

        // If not using geolocation, bail
        if (!$usingGeo) {
            return;
        }

        // Detect & set current location
        $this->currentLocation();
    }

    // Automatically detect & set current location
    public function currentLocation()
    {
        // If geo info has been set, bail
        if ($this->geoInfoSet) {
            return;
        }

        // Detect IP address
        $ip = $this->_detectVisitorIp();

        // Do geolocation lookup (manual ?? automatic)
        $this->_setGeoData($ip ?? '');
    }

    // Automatically detect IP address
    private function _detectVisitorIp()
    {
        // Get user IP address
        $ip = Craft::$app->getRequest()->getUserIP();

        // If IP is local, bail
        if ('127.0.0.1' == $ip) {
            return false;
        }
        // If IP is invalid, bail
        if (!$this->validIp($ip)) {
            return false;
        }
        // Return IP address
        return $ip;
    }

    // Checks whether IP address is valid
    public function validIp($ip)
    {
        // TODO: THIS ISN'T CHECKING FOR IPv6
        $ipPattern = '/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/';
        return preg_match($ipPattern, $ip);
    }

    //
    public function appendVisitorCoords()
    {
        // Whether coords are valid
        $validCoords = (
            $this->visitor['latitude'] &&
            $this->visitor['longitude'] &&
            is_numeric($this->visitor['latitude']) &&
            is_numeric($this->visitor['longitude'])
        );
        // If valid, append coords
        if ($validCoords) {
            $this->visitor['coords'] = [
                $this->visitor['latitude'],
                $this->visitor['longitude']
            ];
        }
    }

    //
    private function _setGeoData($ip = '')
    {
        // Mark as set
        $this->geoInfoSet = true;

        // If data exists in cache, bail
        if ($this->_matchGeoData($ip)) {
            return;
        }

        // @TODO
        // Use Google Maps Geolocation API (as default service)

        switch (SmartMap::$plugin->getSettings()->geolocation) {
            case 'ipstack':
                SmartMap::$plugin->smartMap_ipstack->lookupIpData($ip);
                break;
            case 'maxmind':
                SmartMap::$plugin->smartMap_maxMind->lookupIpData($ip);
                break;
        }

        // Fire an 'afterDetectLocation' event
        $eventLocation = $this->cacheData['visitor'];
        unset($eventLocation['ip']);

        // Trigger event after location detection
        if (Event::hasHandlers(SmartMap::class, SmartMap::EVENT_AFTER_DETECT_LOCATION)) {
            Event::trigger(SmartMap::class, SmartMap::EVENT_AFTER_DETECT_LOCATION, new DetectLocationEvent([
                'ip'               => $this->cacheData['visitor']['ip'],
                'location'         => $eventLocation,
                'detectionService' => $this->cacheData['service'],
                'cacheExpires'     => $this->cacheData['expires'],
            ]));
        }
    }

    // Retrieve cached geo information for IP address
    private function _matchGeoData($ip)
    {
        // If no IP, bail
        if (!$ip) {
            return false;
        }

        // Get cached data for IP
        $key = $this->_geolocationCacheKey($ip);
        $this->cacheData = Craft::$app->getCache()->get($key);

        // If no cached data, bail
        if (!$this->cacheData) {
            return false;
        }

        // Get visitor data based on IP
        $this->visitor = $this->cacheData['visitor'];
        return true;
    }

    // Cache geo information for IP address
    public function cacheGeoData($ip, $geoLookupService, $duration = 7776000) // 60*60*24*90 // Expires in 90 days
    {
        // If no IP address, bail
        if (!$ip) {
            return;
        }

        // Ensure geo data is loaded
        $this->loadGeoData();

        // Set data to be cached
        $data = [
            'visitor' => $this->visitor,
            'expires' => time() + $duration,
            'service' => $geoLookupService,
        ];

        // Cache data
        $key = $this->_geolocationCacheKey($ip);
        Craft::$app->getCache()->set($key, $data, $duration);

        // Set cached data
        $this->cacheData = $data;
    }

    // Geolocation cache key
    private function _geolocationCacheKey($ip)
    {
        return "smartmap-geolocation[{$ip}]";
    }

    // Lookup cache key
    private function _lookupCacheKey($target, $components)
    {
        $target = strtolower($target);
        $target = trim($target);
        $key = "smartmap-lookup[{$target}]";
        if (!empty($components)) {
            $components = json_encode($components);
            $key .= "[{$components}]";
        }
        return $key;
    }

    // ==================================================== //

    // Use haversine formula
    private function _haversinePHP(array $coords_1, array $coords_2)
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
        $lat_1 = (float) $coords_1['lat'];
        $lng_1 = (float) $coords_1['lng'];
        $lat_2 = (float) $coords_2['lat'];
        $lng_2 = (float) $coords_2['lng'];
        // Calculate haversine formula
        return ($unitVal * acos(cos(deg2rad($lat_1)) * cos(deg2rad($lat_2)) * cos(deg2rad($lng_2) - deg2rad($lng_1)) + sin(deg2rad($lat_1)) * sin(deg2rad($lat_2))));
    }


    // ============================================================================ //
    // CALLED VIA doublesecretagency\smartmap\fields\Address::modifyElementsQuery()
    // ============================================================================ //

    // Modify fieldtype query
    public function modifyQuery(ElementQueryInterface $query, array $params)
    {
        // Join with plugin table
        $query->subQuery->leftJoin('{{%smartmap_addresses}} addresses', '[[addresses.elementId]] = [[elements.id]]');

        // Search by comparing coordinates
        if (array_key_exists('target', $params) || array_key_exists('range', $params)) {
            $this->_searchCoords($query, $params);
        }

        // Filter according to subfield(s)
        if (array_key_exists('filter', $params)) {
            $this->_filterSubfield($query, $params);
        }
    }

    // Filter according to subfield(s)
    private function _filterSubfield(&$query, $params = [])
    {
        $realSubfields = ['street1','street2','city','state','zip','country','lat','lng'];

        foreach ($params['filter'] as $subfield => $value) {
            if (in_array($subfield, $realSubfields)) {

                // Ensure value is an array
                if (is_string($value) || is_float($value)) {
                    $value = [$value];
                }
                // If value is not an array, skip
                if (!is_array($value)) {
                    continue;
                }

                // Compile WHERE clause
                $where = [];

                // Loop through filter values
                foreach ($value as $filterValue) {
                    $where[] = [$subfield => $filterValue];
                }

                // Re-organize WHERE filters
                if (1 == count($where)) {
                    $where = $where[0];
                } else {
                    array_unshift($where, 'or');
                }

                // Append WHERE clause to subquery
                $query->subQuery->andWhere($where);
            }
        }
    }


    // ==================================================== //
    // CALLED VIA FIELDTYPE
    // ==================================================== //

    // Save field to plugin table
    public function saveAddressField(AddressField $field, $element)
    {
        // Get field data
        $data = $element->getFieldValue($field->handle);

        // If data doesn't exist, bail
        if (!$data) {
            return false;
        }

        // Attempt to load existing record
        $record = AddressRecord::findOne([
            'elementId' => $element->id,
            'fieldId'   => $field->id,
        ]);

        // If no record exists, create new record
        if (!$record) {
            $record = new AddressRecord;
            $record->elementId = $element->id;
            $record->fieldId   = $field->id;
        }

        // Set record attributes
        $record->setAttribute('street1', $data['street1']);
        $record->setAttribute('street2', $data['street2']);
        $record->setAttribute('city',    $data['city']);
        $record->setAttribute('state',   $data['state']);
        $record->setAttribute('zip',     $data['zip']);
        $record->setAttribute('country', $data['country']);
        $record->setAttribute('lat',     $data['lat']);
        $record->setAttribute('lng',     $data['lng']);

        // Save record
        return $record->save();

    }

    // Retrieves address from 3rd party table
    public function getAddressField(AddressField $field, $element, $value)
    {
        // If no element, bail
        if (!$element) {
            return false;
        }

        // Attempt to load existing record
        $record = AddressRecord::findOne([
            'elementId' => $element->id,
            'fieldId'   => $field->id,
        ]);

        // Load Address model
        if (is_array($value)) {
            $model = new AddressModel($value);
        } else if ($record) {
            $model = new AddressModel($record->getAttributes());
            $model->distance = $value;
        } else {
            $model = new AddressModel();
        }
        $model->handle = $field->handle;

// This is bad, remove this from the next version.
// It was determining "distance" with relation to the geolocated user,
// even if no proximity search was conducted. It is not a behavior that
// people would expect, and can be very confusing.

// On a similar note, make `haversinePHP` a publicly accessible service.
// It can be useful in one-off calculations.

//        // Set distance property
//        $data = $model->getAttributes();
//        if ($this->targetCoords) {
//            $visitor = $this->targetCoords;
//        } else {
//            $this->loadGeoData();
//            $visitor = [
//                'lat' => $this->visitor['latitude'],
//                'lng' => $this->visitor['longitude'],
//            ];
//        }
//        if (is_numeric($data['lat']) && is_numeric($data['lng'])) {
//            $model->distance = $this->_haversinePHP($visitor, $data);
//        } else {
//            $model->distance = null;
//        }

        return $model;
    }

    // ==================================================== //
    // PRIVATE METHODS
    // ==================================================== //

    // Parse query filter
    private function _parseFilter(array $params)
    {

        if (!is_array($params)) {
            $params = [];
            $api = MapApi::LatLngArray;
            $coords = $this->defaultCoords();
        } else if (!array_key_exists('target', $params)) {
            $api = MapApi::LatLngArray;
            $coords = $this->defaultCoords();
        } else if (is_array($params['target'])) {
            $api = MapApi::LatLngArray;
            if (!$this->isAssoc($params['target']) && count($params['target']) == 2) {
                $lat = $params['target'][0];
                $lng = $params['target'][1];
            } else {
                $lat = $this->findKeyInArray($params['target'], ['latitude','lat']);
                $lng = $this->findKeyInArray($params['target'], ['longitude','lng','lon','long']);
            }
            $coords = [
                'lat' => $lat,
                'lng' => $lng,
            ];
        } else if (is_string($params['target']) || is_numeric($params['target'])) {
            $api = MapApi::GoogleMaps;
        } else {
            // Invalid target
            //  - Throw error here?
            $api = MapApi::LatLngArray;
            $coords = $this->defaultCoords();
        }

        $filter = new FilterCriteriaModel($params);

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
                $filter->coords = $this->_geocodeGoogleMapApi($filter->target, $filter->components);
                break;
        }

        $this->targetCoords    = $filter->coords;
        $this->measurementUnit = $filter->units;

        return $filter;
    }

    // Search by coordinates
    private function _searchCoords(&$query, array $params)
    {
        $filter = $this->_parseFilter($params);
        // Implement haversine formula
        $haversine = $this->_haversine(
            $filter->coords['lat'],
            $filter->coords['lng']
        );
        // Modify subquery
        $query->subQuery
            ->addSelect($haversine.' AS [[distance]]')
            ->andWhere('[[addresses.fieldId]] = :fieldId', [':fieldId' => $filter->fieldId])
            ->having('[[distance]] <= :range', [':range' => $filter->range])
        ;
        // Temporarily store the distance under the field handle
        $query->query->addSelect("[[subquery.distance]] AS [[{$params['fieldHandle']}]]");
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
        // Calculate haversine formula
        return "($unitVal * acos(cos(radians($lat)) * cos(radians([[addresses.lat]])) * cos(radians([[addresses.lng]]) - radians($lng)) + sin(radians($lat)) * sin(radians([[addresses.lat]]))))";
    }

    // Get coordinates from Google Maps API
    private function _geocodeGoogleMapApi($target, $components = [])
    {
        // Lookup geocode matches
        $response = $this->lookup($target, $components);
        // If no results, use default coords
        if (empty($response['results'])) {
            return $this->defaultCoords();
        }
        // Return location data
        return $response['results'][0]['geometry']['location'];
    }

    // Decipher map center & markers based on locations
    public function markerCoords($locations, $options = [])
    {

        // If no locations set, return default
        if (!$locations || empty($locations)) {
            return [
                'center'  => $this->defaultCoords(),
                'markers' => [],
            ];
        }

        // If Address model, process immediately
        if (is_object($locations) && is_a($locations, 'doublesecretagency\\smartmap\\models\\Address')) {

            $markers = [];

            // Set markers
            if ($locations->hasCoords()) {
                $lat = $locations->lat;
                $lng = $locations->lng;
                $markers[] = [
                    'lat'   => (float) $lat,
                    'lng'   => (float) $lng,
                    'title' => '',
                ];
            } else {
                $lat = 0;
                $lng = 0;
            }

            // Return center point and all markers
            return [
                'center'  => ['lat' => $lat, 'lng' => $lng],
                'markers' => $markers,
            ];

        }

        // If one location, process as an array
        if ($locations && !is_array($locations)) {
            return $this->markerCoords([$locations], $options);
        }

        // If ElementCriteriaModel, convert to normal array
        if (is_object($locations[0]) && is_a($locations[0], 'craft\\elements\\db\\ElementQuery')) {
            return $this->markerCoords($locations[0]->all(), $options);
        }

        // Initialize variables
        $markers = [];
        $allLats = [];
        $allLngs = [];
        $fieldHandles = [];

        // If locations are specified
        if (!empty($locations)) {
            // If location is a pair of coordinates
            if (!$this->isAssoc($locations) && count($locations) == 2 && !is_object($locations[0])) {
                $lat = $locations[0];
                $lng = $locations[1];
                $allLats[] = $lat;
                $allLngs[] = $lng;
                $markers[] = [
                    'lat' => $lat,
                    'lng' => $lng,
                ];
            } else {
                // Find all Smart Map Address field fieldIds
                foreach (Craft::$app->fields->getAllFields() as $field) {
                    if ($field->className() == 'doublesecretagency\\smartmap\\fields\\Address') {
                        $fieldHandles[] = $field->handle;
                    }
                }
                // Loop through locations
                foreach ($locations as $loc) {
                    if (is_object($loc)) {
                        // If Matrix Block model, get new set of field handles
                        if (is_a($loc, 'craft\\elements\\MatrixBlock')) {
                            // Find all Smart Map Address field fieldIds related specifically to this matrix block type
                            $fieldHandles = [];
                            $typeId = $loc->type->id;
                            foreach (Craft::$app->fields->getAllFields(null, "matrixBlockType:$typeId") as $field) {
                                if ($field->className() == 'doublesecretagency\\smartmap\\fields\\Address') {
                                    $fieldHandles[] = $field->handle;
                                }
                            }
                        }
                        // If location is an object
                        if (!empty($fieldHandles)) {
                            foreach ($fieldHandles as $fieldHandle) {
                                if (isset($loc->{$fieldHandle})) {
                                    $address = $loc->{$fieldHandle};
                                    if (!empty($address) && $address->hasCoords()) {
                                        $lat = $address['lat'];
                                        $lng = $address['lng'];
                                        $markers[] = [
                                            'lat'     => (float) $lat,
                                            'lng'     => (float) $lng,
                                            'title'   => $loc->title,
                                            'element' => $loc
                                        ];
                                        $allLats[] = $lat;
                                        $allLngs[] = $lng;
                                    }
                                }
                            }
                        }
                    } else if (is_array($loc)) {
                        // Else, if location is an array
                        if (!$this->isAssoc($loc) && count($loc) == 2 && !is_object($loc[0])) {
                            $lat = $loc[0];
                            $lng = $loc[1];
                            $title = '';
                        } else {
                            $lat = $this->findKeyInArray($loc, ['latitude','lat']);
                            $lng = $this->findKeyInArray($loc, ['longitude','lng','lon','long']);
                            $title = (array_key_exists('title',$loc) ? $loc['title'] : '');
                        }
                        $markers[] = [
                            'lat'     => $lat,
                            'lng'     => $lng,
                            'title'   => $title,
                            'element' => $loc
                        ];
                        $allLats[] = $lat;
                        $allLngs[] = $lng;
                    }
                }
            }
        }

        // Determine center of map
        if (array_key_exists('center', $options)) {
            // Center is specified in options
            $center = $options['center'];
        } else if (empty($locations) || empty($allLats) || empty($allLngs)) {
            // Error was triggered
            $markers = [];
            if (array_key_exists('target', $options)) {
                $components = (array_key_exists('components', $options) ? $options['components'] : []);
                $center = $this->targetCoords = $this->_geocodeGoogleMapApi($options['target'], $components);
            } else {
                $center = $this->targetCenter();
            }
        } else {
            // Calculate center of map
            $centerLat = (min($allLats) + max($allLats)) / 2;
            $centerLng = (min($allLngs) + max($allLngs)) / 2;
            $center = [
                'lat' => round($centerLat, 6),
                'lng' => round($centerLng, 6)
            ];
        }

        // Return center point and all markers
        return [
            'center'  => $center,
            'markers' => $markers,
        ];
    }

    // Center coordinates of target
    public function targetCenter($target = false, $components = [])
    {
        $coords =& $this->targetCoords;
        if (!$coords) {
            if ($target) {
                $coords = $this->_geocodeGoogleMapApi($target, $components);
            } else {
                $coords = $this->defaultCoords();
            }
        }
        return $coords;
    }

    // Lookup a target location, returning full JSON
    public function lookup($target, $components = [])
    {
        // Cache key
        $key = $this->_lookupCacheKey($target, $components);
        $cachedResponse = Craft::$app->getCache()->get($key);

        // If cached response exists, return it
        if ($cachedResponse) {
            return $cachedResponse;
        }

        // Configure API URL
        $api  = 'https://maps.googleapis.com/maps/api/geocode/json';
        $api .= '?address='.rawurlencode($target);
        $api .= $this->googleServerKey();

        // If components, add them to API URL
        if (is_array($components) && !empty($components)) {
            $mergedComponents = [];
            foreach ($components as $k => $v) {
                $mergedComponents[] = "$k:$v";
            }
            $api .= '&components='.implode('|', $mergedComponents);
        }

        // cURL call
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $api,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $response = json_decode(curl_exec($ch), true);
        $error = curl_error($ch);
        curl_close($ch);

        // If there was an error, log it and bail
        if ($error) {
            Craft::error('cURL error: '.$error, __METHOD__);
            return $response;
        }

        // Check for message
        $message = false;
        switch ($response['status']) {
            // case 'OK':
            //     return [
            //         'success' => true,
            //         'results' => $this->_restructureSearchResults($response['results'])
            //     ];
            //     break;
            // case 'ZERO_RESULTS':
            //     $message = Craft::t('smart-map', 'The geocode was successful but returned no results.');
            //     break;
            case 'OVER_QUERY_LIMIT':
                $message = Craft::t('smart-map', 'You are over your quota. If this is a shared server, enable Google Maps API Keys.');
                break;
            case 'REQUEST_DENIED':
                if (array_key_exists('error_message', $response) && $response['error_message']) {
                    $message = $response['error_message'];
                } else {
                    $message = Craft::t('smart-map', 'Your request was denied for some reason.');
                }
                break;
            case 'INVALID_REQUEST':
                $message = Craft::t('smart-map', 'Invalid request. Please provide more address information.');
                break;
        }

        // If error message, log it
        if ($message) {
            Craft::error('Google API error: '.$message, __METHOD__);
        }

        // If no error or message, cache response
        if (!$error && !$message) {
            $duration = 7776000; // 90 days
            Craft::$app->getCache()->set($key, $response, $duration);
        }

        // Return response
        return $response;
    }

    // Lookup a target location, returning only coordinates of first result
    public function lookupCoords($target, $components = [])
    {
        $response = $this->lookup($target, $components);
        if (empty($response['results'])) {
            return false;
        }
        return $response['results'][0]['geometry']['location'];
    }


    // ==================================================== //

    // Append Google API server key
    public function googleServerKey($prepend = '&')
    {
        // Get server key
        $settings = SmartMap::$plugin->getSettings();
        $key = trim($settings['googleServerKey']);

        // If no key, log deprecation message and bail
        if (!$key) {
            Craft::$app->getDeprecator()->log('SmartMapService::googleServerKey()', static::KEY_REQUIRED_MESSAGE);
            return '';
        }

        // Return key parameter
        return "{$prepend}key={$key}";
    }

    // Append Google API server key
    public function googleBrowserKey($prepend = '&')
    {
        // Get server key
        $settings = SmartMap::$plugin->getSettings();
        $key = trim($settings['googleBrowserKey']);

        // If no key, log deprecation message and bail
        if (!$key) {
            Craft::$app->getDeprecator()->log('SmartMapService::googleBrowserKey()', static::KEY_REQUIRED_MESSAGE);
            return '';
        }

        // Return key parameter
        return "{$prepend}key={$key}";
    }

    // ==================================================== //


    // Use default coordinates
    public function defaultCoords()
    {
        $defaultCoords = [
            // Point Nemo
            'lat' => -48.876667,
            'lng' => -123.393333,
        ];
        $this->loadGeoData();
        if ($this->visitor && array_key_exists('latitude', $this->visitor) && array_key_exists('longitude', $this->visitor)) {
            $coords = [
                // Current location
                'lat' => $this->visitor['latitude'],
                'lng' => $this->visitor['longitude'],
            ];
        } else {
            $coords = $defaultCoords;
        }
        if (!$coords['lat'] && !$coords['lng']) {
            $coords = $defaultCoords;
        }
        return $coords;
    }


    // ==================================================== //
    // HELPER FUNCTIONS
    // ==================================================== //

    // Get the target from an array
    public function findKeyInArray($array, $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            if (array_key_exists($key, $array)) {
                return $array[$key];
            }
        }
    }

    // Determine if array is associative
    public function isAssoc($array) {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

}
