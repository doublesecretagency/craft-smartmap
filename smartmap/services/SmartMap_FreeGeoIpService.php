<?php
namespace Craft;

class SmartMap_FreeGeoIpService extends BaseApplicationComponent
{

	private $_freegeoipApi = 'http://freegeoip.net/json/';

	// Look up geolocation data based on IP address
	public function lookupIpData($ip)
	{
		try
		{
			// Ping geo location service
			$results = $this->rawData($ip);
			// Populate "here" array
			craft()->smartMap->here = array(
				'ip'        => (array_key_exists('ip',$results)           ? $results['ip']           : ''),
				'city'      => (array_key_exists('city',$results)         ? $results['city']         : ''),
				'state'     => (array_key_exists('region_name',$results)  ? $results['region_name']  : ''),
				'zipcode'   => (array_key_exists('zipcode',$results)      ? $results['zipcode']      : ''),
				'country'   => (array_key_exists('country_name',$results) ? $results['country_name'] : ''),
				'latitude'  => (array_key_exists('latitude',$results)     ? $results['latitude']     : ''),
				'longitude' => (array_key_exists('longitude',$results)    ? $results['longitude']    : ''),
			);
			// If valid IP, set cache & cookie
			if (craft()->smartMap->validIp(craft()->smartMap->here['ip'])) {
				craft()->smartMap->setGeoDataCookie($ip);
				craft()->smartMap->cacheGeoData(craft()->smartMap->here['ip'], 'FreeGeoIp.net');
			}
		}
		catch (\Exception $e)
		{
			$message = 'The request to FreeGeoIp.net failed: '.$e->getMessage();
			Craft::log($message, LogLevel::Warning);
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