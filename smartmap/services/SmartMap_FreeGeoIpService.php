<?php
namespace Craft;

class SmartMap_FreeGeoIpService extends BaseApplicationComponent
{

	private $_freegeoipApi = 'http://freegeoip.net/json/';

	// Look up geolocation data based on IP address
	public function lookupIpData($ip)
	{
		// Log lookup
		SmartMapPlugin::log('Visitor lookup via FreeGeoIp.net');
		// Attempt lookup
		try
		{
			// Ping geo location service
			$results = $this->rawData($ip);
			// Populate visitor geolocation data
			craft()->smartMap->visitor = array(
				'ip'        => (array_key_exists('ip',$results)           ? $results['ip']           : ''),
				'city'      => (array_key_exists('city',$results)         ? $results['city']         : ''),
				'state'     => (array_key_exists('region_name',$results)  ? $results['region_name']  : ''),
				'zipcode'   => (array_key_exists('zipcode',$results)      ? $results['zipcode']      : ''),
				'country'   => (array_key_exists('country_name',$results) ? $results['country_name'] : ''),
				'latitude'  => (array_key_exists('latitude',$results)     ? $results['latitude']     : ''),
				'longitude' => (array_key_exists('longitude',$results)    ? $results['longitude']    : ''),
			);
			// Append visitor coords
			craft()->smartMap->appendVisitorCoords();
			// If valid IP, set cache & cookie
			if (craft()->smartMap->validIp(craft()->smartMap->visitor['ip'])) {
				craft()->smartMap->setGeoDataCookie($ip);
				craft()->smartMap->cacheGeoData(craft()->smartMap->visitor['ip'], 'FreeGeoIp.net');
			}
		}
		catch (\Exception $e)
		{
			$message = 'The request to FreeGeoIp.net failed: '.$e->getMessage();
			SmartMapPlugin::log($message, LogLevel::Warning);
		}
	}

	// Get raw API data
	public function rawData($ip = null)
	{
		$client = new \Guzzle\Http\Client($this->_freegeoipApi);
		return $client
			->get($ip)
			->send()
			->json();
	}

}