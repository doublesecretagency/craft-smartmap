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
use doublesecretagency\smartmap\SmartMap;

/**
 * Class FreeGeoIp
 * @since 3.0.0
 */
class FreeGeoIp extends Component
{

    private $_freegeoipApi = 'http://freegeoip.net/json/';

    // Look up geolocation data based on IP address
    public function lookupIpData($ip)
    {
        // Log lookup
        Craft::info('Visitor lookup via FreeGeoIp.net', __METHOD__);
        // Attempt lookup
        try
        {
            // Ping geo location service
            $results = $this->rawData($ip);
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
            // If valid IP, set cache & cookie
            if (SmartMap::$plugin->smartMap->validIp(SmartMap::$plugin->smartMap->visitor['ip'])) {
                SmartMap::$plugin->smartMap->setGeoDataCookie($ip);
                SmartMap::$plugin->smartMap->cacheGeoData(SmartMap::$plugin->smartMap->visitor['ip'], 'FreeGeoIp.net');
            }
        }
        catch (\Exception $e)
        {
            $message = 'The request to FreeGeoIp.net failed: '.$e->getMessage();
            Craft::warning($message, __METHOD__);
        }
    }

    // Get raw API data
    public function rawData($ip = null)
    {
        // Create Guzzle client
        $client = Craft::createGuzzleClient();
        // Set IP address for lookup
        $url = $this->_freegeoipApi.($ip ? $ip : 'me');
        // Get API response
        $response = $client->request('GET', $url);
        // Return nested array of results
        return json_decode($response->getBody(), true);
    }

}