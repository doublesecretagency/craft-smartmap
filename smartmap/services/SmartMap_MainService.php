<?php
namespace Craft;

class SmartMap_MainService extends BaseApplicationComponent
{

	// Search for address using Google Maps API
	public function addressSearch($address)
	{
		$message = false;

		$api  = 'https://maps.googleapis.com/maps/api/geocode/json';
		$api .= '?address='.str_replace(' ', '+', $address);
		$api .= craft()->smartMap->googleServerKey();

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

		switch ($response['status']) {
			case 'OK':
				return array(
					'success' => true,
					'results' => $this->_restructureSearchResults($response['results'])
				);
				break;
			case 'ZERO_RESULTS':
				$message = Craft::t('The geocode was successful but returned no results.');
				break;
			case 'OVER_QUERY_LIMIT':
				$message = Craft::t('You are over your quota. If this is a shared server, enable <a href="{url}">Google Maps API Keys.</a>', array(
					'url' => UrlHelper::getCpUrl('settings/plugins/smartmap')
				));
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

		if (!$message) {
			if (!$response) {
				$message = Craft::t('Failed to execute cURL command.');
			} else if (array_key_exists('status', $response) && $response['status']) {
				$message = Craft::t('Response from Google Maps API:').' '.$response['status'];
			} else {
				$message = Craft::t('Unknown cURL response:').' '.json_encode($response);
			}
		}

		SmartMapPlugin::log('Address search error: '.$message, LogLevel::Error);

		return array(
			'success' => false,
			'message' => $message
		);
	}

	// Rearrange the search results to be more usable
	private function _restructureSearchResults($searchResults)
	{
		$restructuredResults = array();
		foreach ($searchResults as $result) {
			$restructured = array();
			foreach ($result['address_components'] as $component) {
				if (empty($component['types'])) {
					$restructured[$c] = '';
				} else {
					$c = $component['types'][0];
					switch ($c) {
						case 'country':
							$restructured[$c] = $component['long_name'];
							break;
						default:
							$restructured[$c] = $component['short_name'];
							break;
					}
				}
			}
			$restructured['formatted'] = $result['formatted_address'];
			$restructured['coords'] = $result['geometry']['location'];
			$restructuredResults[] = $restructured;
		}
		return $restructuredResults;
	}
/*
for (c in components) {
	switch (components[c]['types'][0]) {
		case 'street_number':
			number = components[c]['short_name'];
			break;
		case 'route':
			street = components[c]['short_name'];
			break;
		case 'sublocality':
			subcity = components[c]['short_name'];
			break;
		case 'locality':
			city = components[c]['short_name'];
			break;
		case 'administrative_area_level_1':
			state = components[c]['short_name'];
			break;
		case 'postal_code':
			zip = components[c]['short_name'];
			break;
		case 'country':
			country = components[c]['long_name'];
			break;
	}
}
addressOptions[handle][i] = {
	'street1' : ((number ? number : '')+' '+(street ? street : '')).trim(),
	'city'    : (typeof subcity === 'undefined' ? city : subcity),
	'state'   : state,
	'zip'     : zip,
	'country' : country,
	'lat'     : address.geometry.location.lat(),
	'lng'     : address.geometry.location.lng()
}
*/

}