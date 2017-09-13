<?php
namespace Craft;

class SmartMapService extends BaseApplicationComponent
{

	const IP_COOKIE_NAME = 'SmartMap_VisitorIp';

	public $settings;

	public $here = false; // DEPRECATED
	public $visitor = false;
	public $geoInfoSet = false;

	public $cookieData = false;
	public $cacheData = false;

	public $targetCoords;

	public $measurementUnit;

	// Initialize
	public function init()
	{
		// ALIAS
		$this->here =& $this->visitor;
	}

	// Load geo data
	public function loadGeoData()
	{
		if (!$this->visitor) {
			$this->visitor = array( // Default to empty container array
				'ip'        => false,
				'city'      => false,
				'state'     => false,
				'zipcode'   => false,
				'country'   => false,
				'latitude'  => false,
				'longitude' => false,
				'coords'    => false,
			);
			// If using geolocation, get cookie data
			$geoSelection = $this->settings['geolocation'];
			$geoServices  = array('freegeoip','maxmind');
			$usingGeo     = in_array($geoSelection, $geoServices);
			if ($usingGeo && !craft()->isConsole()) {
				$ipCookie = static::IP_COOKIE_NAME;
				if (array_key_exists($ipCookie, $_COOKIE)) {
					$this->cookieData = json_decode($_COOKIE[$ipCookie], true);
				}
				$this->currentLocation();
			}
		}
	}

	// Automatically detect & set current location
	public function currentLocation()
	{
		// Detect IP address
		$ip = $this->_detectVisitorIp();
		// If IP can't be detected
		if (!$ip) {
			if ($this->cookieData) {
				$ip = $this->cookieData['ip'];
			} else {
				$this->_setGeoData(); // Auto detect IP
			}
		}
		// Set new geo data
		if ($ip && !$this->geoInfoSet) {
			$this->_setGeoData($ip); // Manually set IP
		}
	}

	// Automatically detect IP address
	private function _detectVisitorIp()
	{
		$ip = craft()->request->userHostAddress;
		if (('127.0.0.1' == $ip) || (!$this->validIp($ip))) {
			return false;
		} else {
			return $ip;
		}
	}

	// Checks whether IP address is valid
	public function validIp($ip)
	{
		// TODO: THIS ISN'T CHECKING FOR IPv6
		$ipPattern = '/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/';
		return preg_match($ipPattern, $ip);
	}

	// Whether this is running in a task
	private function _isTask()
	{
		// Loop through stack trace until we find a task
		foreach (debug_backtrace() as $caller) {
			// If called from a task
			if (array_key_exists('file', $caller) && substr($caller['file'], -8) == 'Task.php') {
				return true;
			}
		}
		// Apparently not a task
		return false;
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
		// If running in a task, bail
		if ($this->_isTask()) {
			SmartMapPlugin::log('Geolocation lookups are not run in tasks.');
			return;
		}
		// If existing data isn't found, go get it
		if (!$this->_matchGeoData($ip)) {

			// @TODO
			// Use Google Maps Geolocation API (as default service)

			if (craft()->smartMap_maxMind->available) {
				craft()->smartMap_maxMind->lookupIpData($ip);
			} else {
				craft()->smartMap_freeGeoIp->lookupIpData($ip);
			}
			// Fire an 'onDetectLocation' event
			$eventLocation = $this->cacheData['visitor'];
			unset($eventLocation['ip']);
			$this->onDetectLocation(new Event($this, array(
				'ip'               => $this->cacheData['visitor']['ip'],
				'location'         => $eventLocation,
				'detectionService' => $this->cacheData['service'],
				'cacheExpires'     => $this->cacheData['expires'],
			)));
		}
		$this->geoInfoSet = true;
	}

	// Retrieve cached geo information for IP address
	private function _matchGeoData($ip)
	{
		if ($ip) {
			$this->cacheData = craft()->fileCache->get($ip);
			if ($this->cacheData) {
				$this->visitor = $this->cacheData['visitor'];
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	// Set geo information in cookie
	public function setGeoDataCookie($ipSet, $lifespan = 300) // Expires in five minutes
	{
		if (!$ipSet) {
			craft()->smartMap->loadGeoData();
			$this->cookieData = array(
				'ip'      => $this->visitor['ip'],
				'expires' => time() + $lifespan,
			);
			setcookie(static::IP_COOKIE_NAME, json_encode($this->cookieData), time()+$lifespan, '/');
		}
	}

	// Cache geo information for IP address
	public function cacheGeoData($ip, $geoLookupService, $lifespan = 7776000) // 60*60*24*90 // Expires in 90 days
	{
		if ($ip) {
			craft()->smartMap->loadGeoData();
			$data = array(
				'visitor' => $this->visitor,
				'expires' => time() + $lifespan,
				'service' => $geoLookupService,
			);
			craft()->fileCache->set($ip, $data, $lifespan);
			$this->cacheData = $data;
		}
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
		$lat_1 = $coords_1['lat'];
		$lng_1 = $coords_1['lng'];
		$lat_2 = $coords_2['lat'];
		$lng_2 = $coords_2['lng'];
		// Calculate haversine formula
		return ($unitVal * acos(cos(deg2rad($lat_1)) * cos(deg2rad($lat_2)) * cos(deg2rad($lng_2) - deg2rad($lng_1)) + sin(deg2rad($lat_1)) * sin(deg2rad($lat_2))));
	}


	// ==================================================== //
	// CALLED VIA SmartMap_AddressFieldType::modifyElementsQuery()
	// ==================================================== //

	// Modify fieldtype query
	public function modifyQuery(DbCommand $query, $params = array())
	{
		// Join with plugin table
		$query->join(SmartMap_AddressRecord::TABLE_NAME, 'elements.id='.craft()->db->tablePrefix.SmartMap_AddressRecord::TABLE_NAME.'.elementId');

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
	private function _filterSubfield(&$query, $params = array())
	{
		$realSubfields = array('street1','street2','city','state','zip','country');

		foreach ($params['filter'] as $subfield => $value) {
			if (in_array($subfield, $realSubfields)) {

				// Set full field reference
				$field = craft()->db->tablePrefix.SmartMap_AddressRecord::TABLE_NAME.'.'.$subfield;

				// Ensure value is an array
				if (is_string($value)) {
					$value = array($value);
				}
				// If value is not an array, skip
				if (!is_array($value)) {
					continue;
				}

				// Compile WHERE clause
				$where = '';
				$placeholders = array();

				// Loop through filter values
				foreach ($value as $filterValue) {

					// Generate placeholder token
					$token = ':a'.StringHelper::randomString();

					// Append to WHERE
					if ($where) {$where .= ' OR ';}
					$where .= "($field=$token)";

					// Add placeholder
					$placeholders[$token] = $filterValue;
				}

				// Append WHERE clause to query
				$query->andWhere("($where)", $placeholders);
			}
		}
	}


	// ==================================================== //
	// CALLED VIA FIELDTYPE
	// ==================================================== //

	// Save field to plugin table
	public function saveAddressField(BaseFieldType $field)
	{
		// Get fieldId, elementId, and content
		$elementId = $field->element->id;
		$fieldId   = $field->model->id;
		$content   = $field->element->getContent();

		// Set specified attributes
		$fieldHandle = $field->model->handle;
		$data = $content->getAttribute($fieldHandle);

		// Return false if attribute doesn't exist
		if (!$data) {
			return false;
		}

		// Attempt to load existing record
		$addressRecord = SmartMap_AddressRecord::model()->findByAttributes(array(
			'elementId' => $elementId,
			'fieldId'   => $fieldId,
		));

		// If no record exists, create new record
		if (!$addressRecord) {
			$addressRecord = new SmartMap_AddressRecord;
			$addressRecord->elementId = $elementId;
			$addressRecord->fieldId   = $fieldId;
		}

		// Set record attributes
		$addressRecord->setAttributes($data, false);

		// Empty values default to NULL
		if (!$addressRecord->street1) {$addressRecord->street1 = null;}
		if (!$addressRecord->street2) {$addressRecord->street2 = null;}
		if (!$addressRecord->city)    {$addressRecord->city    = null;}
		if (!$addressRecord->state)   {$addressRecord->state   = null;}
		if (!$addressRecord->zip)     {$addressRecord->zip     = null;}
		if (!$addressRecord->country) {$addressRecord->country = null;}
		if (!is_numeric($addressRecord->lat)) {$addressRecord->lat = null;}
		if (!is_numeric($addressRecord->lng)) {$addressRecord->lng = null;}

		// Save record
		$saved = $addressRecord->save();
		if (!$saved) {
			$errors = $addressRecord->getErrors();
		}
		return $saved;

	}

	// Retrieves address from 3rd party table
	public function getAddress(SmartMap_AddressFieldType $field, $value)
	{

		// Load record (if exists)
		$record	= SmartMap_AddressRecord::model()->findByAttributes(array(
			'elementId' => $field->element->id,
			'fieldId'   => $field->model->id,
		));

		if (craft()->request->getPost() && $value)
		{
			$model = SmartMap_AddressModel::populateModel($value);
		}
		else if ($record)
		{
			$model = SmartMap_AddressModel::populateModel($record->getAttributes());
		}
		else
		{
			$model = new SmartMap_AddressModel;
		}

		// Set distance property
		$data = $model->getAttributes();
		if ($this->targetCoords) {
			$visitor = $this->targetCoords;
		} else {
			craft()->smartMap->loadGeoData();
			$visitor = array(
				'lat' => $this->visitor['latitude'],
				'lng' => $this->visitor['longitude'],
			);
		}
		if (is_numeric($data['lat']) && is_numeric($data['lng'])) {
			$model->distance = $this->_haversinePHP($visitor, $data);
		} else {
			$model->distance = null;
		}

		return $model;
	}

	// ==================================================== //
	// PRIVATE METHODS
	// ==================================================== //

	// Parse query filter
	private function _parseFilter($params = array())
	{

		if (!is_array($params)) {
			$params = array();
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
				$lat = $this->findKeyInArray($params['target'],array('latitude','lat'));
				$lng = $this->findKeyInArray($params['target'],array('longitude','lng','lon','long'));
			}
			$coords = array(
				'lat' => $lat,
				'lng' => $lng,
			);
		} else if (is_string($params['target']) || is_numeric($params['target'])) {
			$api = MapApi::GoogleMaps;
		} else {
			// Invalid target
			//  - Throw error here?
			$api = MapApi::LatLngArray;
			$coords = $this->defaultCoords();
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
				$components = (array_key_exists('components', $params) ? $params['components'] : array());
				$filter->coords = $this->_geocodeGoogleMapApi($filter->target, $components);
				break;
		}

		$this->targetCoords    = $filter->coords;
		$this->measurementUnit = $filter->units;

		return $filter;
	}

	// Search by coordinates
	private function _searchCoords(&$query, $params = array())
	{
		$filter = $this->_parseFilter($params);
		// Implement haversine formula
		$haversine = $this->_haversine(
			$filter->coords['lat'],
			$filter->coords['lng'],
			$filter->units
		);
		// Modify query
		$query
			->addSelect($haversine.' AS distance')
			->andWhere(craft()->db->tablePrefix.SmartMap_AddressRecord::TABLE_NAME.'.fieldId = '.$filter->fieldId)
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
		$table = craft()->db->tablePrefix.SmartMap_AddressRecord::TABLE_NAME;
		// Calculate haversine formula
		return "($unitVal * acos(cos(radians($lat)) * cos(radians($table.lat)) * cos(radians($table.lng) - radians($lng)) + sin(radians($lat)) * sin(radians($table.lat))))";
	}

	// Get coordinates from Google Maps API
	private function _geocodeGoogleMapApi($target, $components = array())
	{
		// Lookup geocode matches
		$response = $this->lookup($target, $components);
		// If no results, use default coords
		if (empty($response['results'])) {
			return $this->defaultCoords();
		} else {
			return $response['results'][0]['geometry']['location'];
		}
	}

	// Decipher map center & markers based on locations
	public function markerCoords($locations, $options = array())
	{

		// If no locations set, return default
		if (!$locations || empty($locations)) {
			return array(
				'center'  => $this->defaultCoords(),
				'markers' => array(),
			);
		}

		// If SmartMap_AddressModel, process immediately
		if (is_object($locations) && is_a($locations, 'Craft\\SmartMap_AddressModel')) {

			$markers = array();

			// Set markers
			if ($locations->hasCoords()) {
				$lat = $locations->lat;
				$lng = $locations->lng;
				$markers[] = array(
					'lat'   => (float) $lat,
					'lng'   => (float) $lng,
					'title' => '',
				);
			} else {
				$lat = 0;
				$lng = 0;
			}

			// Return center point and all markers
			return array(
				'center'  => array('lat' => $lat, 'lng' => $lng),
				'markers' => $markers,
			);

		}

		// If one location, process as an array
		if ($locations && !is_array($locations)) {
			return $this->markerCoords(array($locations), $options);
		}

		// If ElementCriteriaModel, convert to normal array
		if (is_object($locations[0]) && is_a($locations[0], 'Craft\\ElementCriteriaModel')) {
			return $this->markerCoords($locations[0]->find(), $options);
		}

		// Initialize variables
		$markers = array();
		$allLats = array();
		$allLngs = array();
		$fieldHandles = array();

		// If locations are specified
		if (!empty($locations)) {
			// If location is a pair of coordinates
			if (!$this->isAssoc($locations) && count($locations) == 2 && !is_object($locations[0])) {
				$lat = $locations[0];
				$lng = $locations[1];
				$allLats[] = $lat;
				$allLngs[] = $lng;
				$markers[] = array(
					'lat' => $lat,
					'lng' => $lng,
				);
			} else {
				// Find all Smart Map Address field fieldIds
				foreach (craft()->fields->getAllFields() as $field) {
					if ($field->type == 'SmartMap_Address') {
						$fieldHandles[] = $field->handle;
					}
				}
				// Loop through locations
				foreach ($locations as $loc) {
					if (is_object($loc)) {
						// If MatrixBlockModel, get new set of field handles
						if (is_a($loc, 'Craft\\MatrixBlockModel')) {
							// Find all Smart Map Address field fieldIds related specifically to this matrix block type
							$fieldHandles = array();
							$typeId = $loc->type->id;
							foreach (craft()->fields->getAllFields(null, "matrixBlockType:$typeId") as $field) {
								if ($field->type == 'SmartMap_Address') {
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
										$markers[] = array(
											'lat'     => (float) $lat,
											'lng'     => (float) $lng,
											'title'   => $loc->title,
											'element' => $loc
										);
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
							$lat = $this->findKeyInArray($loc, array('latitude','lat'));
							$lng = $this->findKeyInArray($loc, array('longitude','lng','lon','long'));
							$title = (array_key_exists('title',$loc) ? $loc['title'] : '');
						}
						$markers[] = array(
							'lat'     => $lat,
							'lng'     => $lng,
							'title'   => $title,
							'element' => $loc
						);
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
			$markers = array();
			if (array_key_exists('target', $options)) {
				$components = (array_key_exists('components', $options) ? $options['components'] : array());
				$center = $this->targetCoords = $this->_geocodeGoogleMapApi($options['target'], $components);
			} else {
				$center = $this->targetCenter();
			}
		} else {
			// Calculate center of map
			$centerLat = (min($allLats) + max($allLats)) / 2;
			$centerLng = (min($allLngs) + max($allLngs)) / 2;
			$center = array(
				'lat' => round($centerLat, 6),
				'lng' => round($centerLng, 6)
			);
		}

		// Return center point and all markers
		return array(
			'center'  => $center,
			'markers' => $markers,
		);
	}

	/*
	// Search via AJAX
	public function ajaxSearch($params)
	{
		$query = craft()->db->createCommand()
			->select()
			->from('elements');

		// Join with plugin table
		$query->join(SmartMap_AddressRecord::TABLE_NAME, craft()->db->tablePrefix.'elements.id='.craft()->db->tablePrefix.SmartMap_AddressRecord::TABLE_NAME.'.elementId');

		// Join with content table
		$query->join('content', craft()->db->tablePrefix.'elements.id='.craft()->db->tablePrefix.'content.elementId');

		// Set query limit
		if (array_key_exists('limit', $params)) {
			$query->limit($params['limit']);
		}

		// Filter by specified section(s)
		if (array_key_exists('section', $params)) {
			if (!is_array($params['section'])) {
				$where = craft()->db->tablePrefix.'sections.fieldId=:fieldId';
				$pdo = array(':fieldId'=>$params['section']);
			} else {
				$i = 0;
				$where = '';
				$pdo = array();
				foreach ($params['section'] as $fieldId) {
					if ($where) {$where .= ' OR ';}
					$where .= craft()->db->tablePrefix.'sections.fieldId=:fieldId'.$i;
					$pdo[':fieldId'.$i] = $fieldId;
					$i++;
				}
			}
			$query
				->join('entries', craft()->db->tablePrefix.SmartMap_AddressRecord::TABLE_NAME.'.elementId='.craft()->db->tablePrefix.'entries.id')
				->join('sections', craft()->db->tablePrefix.'entries.sectionId='.craft()->db->tablePrefix.'sections.id')
				->andWhere($where, $pdo)
			;
		}

		/* BUG: Not working properly
		// Filter by specified field(s)
		if (array_key_exists('field', $params)) {
			if (!is_array($params['field'])) {
				$where = craft()->db->tablePrefix.SmartMap_AddressRecord::TABLE_NAME.'.fieldId=:fieldId';
				$pdo = array(':fieldId'=>$params['field']);
			} else {
				$i = 0;
				$where = '';
				$pdo = array();
				foreach ($params['field'] as $fieldId) {
					if ($where) {$where .= ' OR ';}
					$where .= craft()->db->tablePrefix.SmartMap_AddressRecord::TABLE_NAME.'.fieldId=:fieldId'.$i;
					$pdo[':fieldId'.$i] = $fieldId;
					$i++;
				}
			}
			$query
				->andWhere($where, $pdo)
			;
		}
		* /

		// Search by comparing coordinates
		$this->_searchCoords($query, $params);

		$query->order('distance');
		$markers = $query->queryAll();
		return $this->markerCoords($markers);
	}
	*/

	// Center coordinates of target
	public function targetCenter($target = false, $components = array())
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
	public function lookup($target, $components = array())
	{
		$api  = 'https://maps.googleapis.com/maps/api/geocode/json';
		$api .= '?address='.rawurlencode($target);
		$api .= $this->googleServerKey();

		if (is_array($components) && !empty($components)) {
			$mergedComponents = array();
			foreach ($components as $key => $value) {
				$mergedComponents[] = "$key:$value";
			}
			$api .= '&components='.implode('|', $mergedComponents);
		}

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $api,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => true,
		));
		$response = json_decode(curl_exec($ch), true);
		$error = curl_error($ch);

		if ($error) {
			SmartMapPlugin::log('cURL error: '.$error, LogLevel::Error);
		}

		curl_close($ch);

		$message = false;
		switch ($response['status']) {
			// case 'OK':
			// 	return array(
			// 		'success' => true,
			// 		'results' => $this->_restructureSearchResults($response['results'])
			// 	);
			// 	break;
			// case 'ZERO_RESULTS':
			// 	$message = Craft::t('The geocode was successful but returned no results.');
			// 	break;
			case 'OVER_QUERY_LIMIT':
				$message = Craft::t('You are over your quota. If this is a shared server, enable Google Maps API Keys.');
				break;
			case 'REQUEST_DENIED':
				if (array_key_exists('error_message', $response) && $response['error_message']) {
					$message = $response['error_message'];
				} else {
					$message = Craft::t('Your request was denied for some reason.');
				}
				break;
			case 'INVALID_REQUEST':
				$message = Craft::t('Invalid request. Please provide more address information.');
				break;
		}

		if ($message) {
			SmartMapPlugin::log('Google API error: '.$message, LogLevel::Error);
		}

		return $response;
	}

	// Lookup a target location, returning only coordinates of first result
	public function lookupCoords($target, $components = array())
	{
		$response = $this->lookup($target, $components);
		if (!empty($response['results'])) {
			return $response['results'][0]['geometry']['location'];
		}
		return false;
	}


	// ==================================================== //

	// Append Google API server key
	public function googleServerKey($prepend = '&')
	{
		return $this->_googleKey('googleServerKey', $prepend);
	}

	// Append Google API browser key
	public function googleBrowserKey($prepend = '&')
	{
		return $this->_googleKey('googleBrowserKey', $prepend);
	}

	// Append Google API key
	private function _googleKey($setting, $prepend)
	{
		if ($this->settings[$setting]) {
			return $prepend.'key='.trim($this->settings[$setting]);
		} else {
			return '';
		}
	}


	// ==================================================== //


	// Use default coordinates
	public function defaultCoords()
	{
		$defaultCoords = array(
			// Point Nemo
			'lat' => -48.876667,
			'lng' => -123.393333,
		);
		craft()->smartMap->loadGeoData();
		if (array_key_exists('latitude', $this->visitor) && array_key_exists('longitude', $this->visitor)) {
			$coords = array(
				// Current location
				'lat' => $this->visitor['latitude'],
				'lng' => $this->visitor['longitude'],
			);
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


	// ==================================================== //

	// Events

	/**
	 * Fires an 'onDetectLocation' event.
	 *
	 * @param Event $event
	 */
	public function onDetectLocation(Event $event)
	{
		$this->raiseEvent('onDetectLocation', $event);
	}
	/*
	// Event returns params:
	array(
		'ip' => 'xx.xx.xx.xx'
		'location' => array(
			'city' => 'Los Angeles'
			'state' => 'California'
			'zipcode' => '90000'
			'country' => 'United States'
			'latitude' => 33.0000
			'longitude' => -118.0000
		)
		'detectionService' => 'MaxMind'
		'cacheExpires' => 1413590881
	)
	*/

}