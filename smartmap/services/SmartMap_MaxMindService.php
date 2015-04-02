<?php
namespace Craft;

class SmartMap_MaxMindService extends BaseApplicationComponent
{

	public $available = false;

	private $_maxmindApi = 'https://geoip.maxmind.com/geoip/v2.0/';
	private $_maxmindUserId;
	private $_maxmindLicenseKey;

	// Load MaxMind settings
	public function init()
	{
		parent::init();
		$s = craft()->plugins->getPlugin('smartMap')->getSettings();
		if ('maxmind' == $s['geolocation']) {

			if ($s['maxmindService']) {
				$this->_maxmindApi .= $s['maxmindService'].'/';
			} else {
				$this->_maxmindApi = null;
			}

			$this->_maxmindUserId     = $s['maxmindUserId'];
			$this->_maxmindLicenseKey = $s['maxmindLicenseKey'];

			$this->available = (
				   $this->_maxmindApi
				&& $this->_maxmindUserId
				&& $this->_maxmindLicenseKey
			);
		}
	}

	// Look up geolocation data based on IP address
	public function lookupIpData($ip)
	{
		try
		{
        	craft()->smartMap->loadGeoData();
			// Ping geo location service
			$results = $this->rawData($ip);
			// Populate "here" array
			if (array_key_exists('traits',$results)) {
				craft()->smartMap->here['ip'] = $results['traits']['ip_address'];
			}
			if (array_key_exists('city',$results)) {
				craft()->smartMap->here['city'] = $results['city']['names']['en'];
			}
			if (array_key_exists('subdivisions',$results) && !empty($results['subdivisions'])) {
				craft()->smartMap->here['state'] = $results['subdivisions'][0]['names']['en'];
			}
			if (array_key_exists('postal',$results)) {
				craft()->smartMap->here['zipcode'] = $results['postal']['code'];
			}
			if (array_key_exists('country',$results)) {
				craft()->smartMap->here['country'] = $results['country']['names']['en'];
			}
			if (array_key_exists('location',$results)) {
				craft()->smartMap->here['latitude'] = $results['location']['latitude'];
				craft()->smartMap->here['longitude'] = $results['location']['longitude'];
			}
			// If valid IP, set cache & cookie
			if (craft()->smartMap->validIp(craft()->smartMap->here['ip'])) {
				craft()->smartMap->setGeoDataCookie($ip);
				craft()->smartMap->cacheGeoData(craft()->smartMap->here['ip'], 'MaxMind');
			} else {
				/*
				// Else, grap IP using FreeGeoIp
				$freeGeoIp = craft()->smartMap_freeGeoIp->rawData();
				if (array_key_exists('ip', $freeGeoIp)) {
					$this->lookupIpData($freeGeoIp['ip']);
				}
				*/
			}
		}
		catch (\Exception $e)
		{
			$message = 'The request to MaxMind failed: '.$e->getMessage();
			Craft::log($message, LogLevel::Warning);
			craft()->smartMap_freeGeoIp->lookupIpData($ip);
		}
	}

	// Get raw API data
	public function rawData($ip = null)
	{
		$client = new \Guzzle\Http\Client($this->_maxmindApi);
		$authorization = 'Basic '.base64_encode($this->_maxmindUserId.':'.$this->_maxmindLicenseKey);
		return $client
			->get($ip ? $ip : 'me')
			->addHeader('Authorization', $authorization)
			->send()
			->json();
	}

}