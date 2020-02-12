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

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use doublesecretagency\smartmap\SmartMap;

/**
 * Class MaxMind
 * @since 3.0.0
 */
class MaxMind extends Component
{

    public $available = false;

    private $_maxmindApi = 'https://geoip.maxmind.com/geoip/v2.0/';
    private $_maxmindUserId;
    private $_maxmindLicenseKey;

    // Load MaxMind settings
    public function init()
    {
        parent::init();
        $s = SmartMap::$plugin->getSettings();
        if ('maxmind' == $s['geolocation']) {

            if ($s['maxmindService']) {
                $this->_maxmindApi .= $s['maxmindService'].'/';
            } else {
                $this->_maxmindApi = null;
            }

            $this->_maxmindUserId     = $s->getMaxmindUserId();
            $this->_maxmindLicenseKey = $s->getMaxmindLicenseKey();

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
        // Log lookup
        Craft::info('Visitor lookup via MaxMind', __METHOD__);

        // If no access key exists
        if (!$this->_maxmindUserId || !$this->_maxmindLicenseKey) {
            // Log deprecation
            $settingsPage = UrlHelper::cpUrl('settings/plugins/smart-map#settings-geolocation');
            $logMessage = 'Your MaxMind <a href="'.$settingsPage.'">API keys</a> are missing.';
            Craft::$app->getDeprecator()->log('SmartMap_MaxMindService::lookupIpData()', $logMessage);
            // Bail
            return;
        }

        // If not available, bail
        if (!$this->available) {
            return;
        }

        // Attempt lookup
        try
        {
            // Get plugin class
            $smartMap = SmartMap::$plugin->smartMap;
            // Load geo data
            $smartMap->loadGeoData();
            // Ping geo location service
            $results = $this->rawData($ip);
            // Populate visitor geolocation data
            if (isset($results['traits'])) {
                $smartMap->visitor['ip'] = $results['traits']['ip_address'];
            }
            if (isset($results['city'])) {
                $smartMap->visitor['city'] = $results['city']['names']['en'];
            }
            if (isset($results['subdivisions']) && !empty($results['subdivisions'])) {
                $smartMap->visitor['state'] = $results['subdivisions'][0]['names']['en'];
            }
            if (isset($results['postal'])) {
                $smartMap->visitor['zipcode'] = $results['postal']['code'];
            }
            if (isset($results['country'])) {
                $smartMap->visitor['country'] = $results['country']['names']['en'];
            }
            if (isset($results['location'])) {
                $smartMap->visitor['latitude'] = $results['location']['latitude'];
                $smartMap->visitor['longitude'] = $results['location']['longitude'];
            }
            // Append visitor coords
            $smartMap->appendVisitorCoords();
            // If valid IP, set cache
            if (filter_var($smartMap->visitor['ip'], FILTER_VALIDATE_IP)) {
                $smartMap->cacheGeoData($smartMap->visitor['ip'], 'MaxMind');
            }
        }
        catch (\Exception $e)
        {
            $message = 'The request to MaxMind failed: '.$e->getMessage();
            Craft::warning($message, __METHOD__);
        }
    }

    // Get raw API data
    public function rawData($ip = null)
    {
        // Create Guzzle client
        $client = Craft::createGuzzleClient(['timeout' => 4, 'connect_timeout' => 4]);
        $authorization = 'Basic '.base64_encode($this->_maxmindUserId.':'.$this->_maxmindLicenseKey);
        // Set IP address for lookup
        $url = $this->_maxmindApi.($ip ? $ip : 'me');
        // Get API response
        $response = $client->request('GET', $url, [
            'headers' => ['Authorization' => $authorization]
        ]);
        // Return nested array of results
        return Json::decode($response->getBody());
    }

}
