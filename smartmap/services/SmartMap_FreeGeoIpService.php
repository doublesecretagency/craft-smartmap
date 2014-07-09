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
			$client = new \Guzzle\Http\Client($this->_freegeoipApi);
			$results = $client
				->get($ip)
				->send()
				->json();
            craft()->smartMap->here = array(
                'ip'           => $results['ip'],
                'city'         => $results['city'],
                'state'        => $results['region_name'],
                'zipcode'      => $results['zipcode'],
                'country'      => $results['country_name'],
                'latitude'     => $results['latitude'],
                'longitude'    => $results['longitude'],
            );
            craft()->smartMap->setGeoDataCookie($ip);
			craft()->smartMap->cacheGeoData($ip, 'FreeGeoIp.net');
		}
		catch (\Exception $e)
		{
			$message = 'The request to FreeGeoIp.net failed: '.$e->getMessage();
			Craft::log($message, LogLevel::Warning);
		}
    }

}