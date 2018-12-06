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

use doublesecretagency\smartmap\SmartMap;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;

/**
 * Class Ipstack
 * @since 3.2.0
 */
class Ipstack extends Component
{

    public $available = false;

    private $_ipstackApi = 'http://api.ipstack.com/{ip}?access_key={key}&legacy=1';
    private $_ipstackAccessKey;

    // Load ipstack settings
    public function init()
    {
        parent::init();
        $s = SmartMap::$plugin->getSettings();
        if ('ipstack' == $s['geolocation']) {
            $this->_ipstackAccessKey = $s['ipstackAccessKey'];
            $this->available = (
                $this->_ipstackApi
                && $this->_ipstackAccessKey
            );
        }
    }

    // Look up geolocation data based on IP address
    public function lookupIpData($ip)
    {
        // Log lookup
        Craft::info('Visitor lookup via ipstack', __METHOD__);

        // If no access key exists
        if (!$this->_ipstackAccessKey) {
            // Log deprecation
            $settingsPage = UrlHelper::cpUrl('settings/plugins/smart-map#settings-geolocation');
            $logMessage = 'The FreeGeoIp.net service was discontinued on July 1st, 2018. It has been replaced with ipstack, which requires an <a href="'.$settingsPage.'">API key</a>.';
            Craft::$app->getDeprecator()->log('Ipstack::lookupIpData()', $logMessage);
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
            // Ping geo location service
            $results = $this->rawData($ip);
            // If failed to get geolocation data
            if (array_key_exists('success', $results) && !$results['success']) {
                // Get error message
                switch ($results['error']['type']) {
                    case 'missing_access_key':
                        $message = 'You have not supplied an API Access Key.';
                        break;
                    case 'invalid_access_key':
                        $message = 'Your API Access Key is invalid.';
                        break;
                    default:
                        $message = $results['error']['info'];
                        break;
                }
                // Log error
                Craft::error($message, __METHOD__);
                // Bail
                return;
            }
            // Populate visitor geolocation data
            SmartMap::$plugin->smartMap->visitor = [
                'ip'        => (array_key_exists('ip',$results)           ? $results['ip']           : ''),
                'city'      => (array_key_exists('city',$results)         ? $results['city']         : ''),
                'state'     => (array_key_exists('region_name',$results)  ? $results['region_name']  : ''),
                'zipcode'   => (array_key_exists('zipcode',$results)      ? $results['zipcode']      : ''),
                'country'   => (array_key_exists('country_name',$results) ? $results['country_name'] : ''),
                'latitude'  => (array_key_exists('latitude',$results)     ? $results['latitude']     : ''),
                'longitude' => (array_key_exists('longitude',$results)    ? $results['longitude']    : ''),
            ];
            // Append visitor coords
            SmartMap::$plugin->smartMap->appendVisitorCoords();
            // If valid IP, set cache
            if (SmartMap::$plugin->smartMap->validIp(SmartMap::$plugin->smartMap->visitor['ip'])) {
                SmartMap::$plugin->smartMap->cacheGeoData(SmartMap::$plugin->smartMap->visitor['ip'], 'ipstack');
            }
        }
        catch (\Exception $e)
        {
            $message = 'The request to ipstack failed: '.$e->getMessage();
            Craft::warning($message, __METHOD__);
        }
    }

    // Get raw API data
    public function rawData($ip = null)
    {
        // Create Guzzle client
        $client = Craft::createGuzzleClient(['timeout' => 4, 'connect_timeout' => 4]);
        // Set endpoint for lookup
        $endpoint = $this->_ipstackApi;
        $endpoint = str_replace('{ip}', $ip, $endpoint);
        $endpoint = str_replace('{key}', $this->_ipstackAccessKey, $endpoint);
        // Get API response
        $response = $client->request('GET', $endpoint);
        // Return nested array of results
        return json_decode($response->getBody(), true);
    }

}
