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

use doublesecretagency\smartmap\events\SearchResultsEvent;
use doublesecretagency\smartmap\SmartMap;

/**
 * Class Main
 * @since 3.0.0
 */
class Main extends Component
{

    /**
     * @event SearchResultsEvent The event that is triggered after search results are restructured.
     */
    const EVENT_MODIFY_SEARCH_RESULTS = 'modifySearchResults';

    // Search for address using Google Maps API
    public function addressSearch($address)
    {
        $message = false;

        $api  = 'https://maps.googleapis.com/maps/api/geocode/json';
        $api .= '?address='.rawurlencode($address);
        $api .= SmartMap::$plugin->smartMap->googleServerKey();

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $api,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $response = Json::decode(curl_exec($ch));
        $error = curl_error($ch);

        if ($error) {
            Craft::error('cURL error: '.$error, __METHOD__);
        }

        curl_close($ch);

        switch ($response['status']) {
            case 'OK':
                return [
                    'success' => true,
                    'results' => $this->_restructureSearchResults($response['results'])
                ];
                break;
            case 'ZERO_RESULTS':
                $message = Craft::t('smart-map','The geocode was successful but returned no results.');
                break;
            case 'OVER_QUERY_LIMIT':
                $message = Craft::t('smart-map','You are over your quota. If this is a shared server, enable <a href="{url}">Google Maps API Keys.</a>', [
                    'url' => UrlHelper::cpUrl('settings/plugins/smart-map')
                ]);
                break;
            case 'REQUEST_DENIED':
                if (isset($response['error_message'])) {
                    $message = $response['error_message'];
                } else {
                    $message = Craft::t('smart-map','Your request was denied for some reason.');
                }
                break;
            case 'INVALID_REQUEST':
                $message = Craft::t('smart-map','Invalid request. Please provide more address information.');
                break;
        }

        if (!$message) {
            if (!$response) {
                $message = Craft::t('smart-map','Failed to execute cURL command.');
            } else if (isset($response['status'])) {
                $message = Craft::t('smart-map','Response from Google Maps API:').' '.$response['status'];
            } else {
                $message = Craft::t('smart-map','Unknown cURL response:').' '.Json::encode($response);
            }
        }

        Craft::error('Address search error: '.$message, __METHOD__);

        return [
            'success' => false,
            'message' => $message
        ];
    }

    // Rearrange the search results to be more usable
    private function _restructureSearchResults($searchResults)
    {
        $restructuredResults = [];
        foreach ($searchResults as $result) {
            $restructured = [];
            foreach ($result['address_components'] as $component) {
                if (empty($component['types'])) {
                    $restructured[$c] = '';
                } else {
                    $c = $component['types'][0];
                    switch ($c) {
                        case 'locality':
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

        // Allow plugins to modify the search results
        $event = new SearchResultsEvent([
            'results' => $restructuredResults
        ]);
        $this->trigger(self::EVENT_MODIFY_SEARCH_RESULTS, $event);

        return $event->results;
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
                city = components[c]['long_name'];
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
