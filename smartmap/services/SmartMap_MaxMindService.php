<?php
namespace Craft;

class SmartMap_MaxMindService extends BaseApplicationComponent
{

    public $available = false;

	private $_maxmindApi = 'https://geoip.maxmind.com/geoip/v2.0/';
	private $_maxmindUserId;
    private $_maxmindLicenseKey;

    // 
    public function init()
    {
    	parent::init();
    	$settings = craft()->plugins->getPlugin('smartMap')->getSettings();
        if ($settings['maxmindService']) {
            $this->_maxmindApi .= $settings['maxmindService'].'/';
        } else {
            $this->_maxmindApi = null;
        }
        $this->_maxmindUserId     = $settings['maxmindUserId'];
        $this->_maxmindLicenseKey = $settings['maxmindLicenseKey'];
        $this->available = ($settings['maxmindService'] && $settings['maxmindUserId'] && $settings['maxmindLicenseKey']);
    }

    // Look up geolocation data based on IP address
    public function lookupIpData($ip)
    {
        try
        {
            $client = new \Guzzle\Http\Client($this->_maxmindApi);
            $authorization = 'Basic '.base64_encode($this->_maxmindUserId.':'.$this->_maxmindLicenseKey);
            $results = $client
                ->get($ip ? $ip : 'me')
                ->addHeader('Authorization', $authorization)
                ->send()
                ->json();
            craft()->smartMap->here = array(
                'ip'           => $results['traits']['ip_address'],
                'city'         => $results['city']['names']['en'],
                'state'        => $results['subdivisions'][0]['names']['en'],
                'zipcode'      => $results['postal']['code'],
                'country'      => $results['country']['names']['en'],
                'latitude'     => $results['location']['latitude'],
                'longitude'    => $results['location']['longitude'],
            );
            craft()->smartMap->setGeoDataCookie($ip);
            craft()->smartMap->cacheGeoData($ip, 'MaxMind');
        }
        catch (\Exception $e)
        {
            $message = 'The request to MaxMind failed: '.$e->getMessage();
            Craft::log($message, LogLevel::Warning);
            craft()->smartMap_freeGeoIp->lookupIpData($ip);
        }
    }

}