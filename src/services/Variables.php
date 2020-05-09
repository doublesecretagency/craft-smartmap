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

use craft\base\Element;
use craft\elements\Asset;
use craft\elements\db\ElementQuery;
use craft\elements\MatrixBlock;
use yii\base\Exception;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\helpers\StringHelper;
use craft\helpers\Template;
use craft\helpers\UrlHelper;

use doublesecretagency\smartmap\SmartMap;
use doublesecretagency\smartmap\fields\Address as AddressField;
use doublesecretagency\smartmap\models\Address as AddressModel;
use doublesecretagency\smartmap\web\assets\FrontEndAssets;
use doublesecretagency\smartmap\web\assets\GoogleMapsAssets;

/**
 * Class Variables
 * @since 3.0.0
 */
class Variables extends Component
{

    private $_assetsLoaded = false;

    private $_mapTotal = 1;
    private $_marker = [];

    // Load front-end assets
    public function loadAssets($renderHere = false)
    {
        // If front-end assets haven't yet been loaded
        if (!$this->_assetsLoaded) {

            // Load front-end assets
            $this->_assetsLoaded = true;

            $view = Craft::$app->getView();

            // Fix Google Map UI bug
            $css = '.smartmap-mapcanvas img {max-width: none} /* Fix Google Map UI bug */';
            $view->registerCss($css);

            // Enable console logging
            $devMode = Craft::$app->getConfig()->general->devMode;
            $enableLogging = 'var enableSmartMapLogging = '.($devMode ? 'true' : 'false').';';
            $view->registerJs($enableLogging, $view::POS_HEAD);

            // Register asset bundles
            $googleMapsBundle = $view->registerAssetBundle(GoogleMapsAssets::class);
            $frontEndBundle   = $view->registerAssetBundle(FrontEndAssets::class);

            // If rendering in place
            if ($renderHere) {

                // Remove bundles from queue
                unset($view->assetBundles[GoogleMapsAssets::class]);
                unset($view->assetBundles[FrontEndAssets::class]);

                // Get front-end URL and file path
                $jsResource = $frontEndBundle->baseUrl.'/'.$frontEndBundle->js[0];
                $filePath   = $frontEndBundle->basePath.'/'.$frontEndBundle->js[0];

                // Append timestamp
                if (($timestamp = @filemtime($filePath)) > 0) {
                    $jsResource .= '?v='.$timestamp;
                }

                // Prep JS asset references
                $html = '
<script type="text/javascript" src="'.$googleMapsBundle->js[0].'"></script>
<script type="text/javascript" src="'.$jsResource.'"></script>';

                // Call JS assets immediately
                return Template::raw($html.PHP_EOL);

            }
        }
    }

    // Create dynamic Google Map of locations
    public function dynamicMap($markers = false, $options = [])
    {
        // Extract non-Google parameters
        $mapId  = (isset($options['id']) ? $options['id'] : 'smartmap-mapcanvas-'.$this->_mapTotal);
        unset($options['id']);

        $width  = (isset($options['width'])  ? 'width:'.$options['width'].'px;'   : '');
        $height = (isset($options['height']) ? 'height:'.$options['height'].'px;' : '');
        unset($options['width']);
        unset($options['height']);

        // No zoom by default
        $zoom = false;
        // (1) Convert zoom object to string (if possible)
        if (isset($options['zoom'])) {
            $zoom = (string) $options['zoom'];
        }
        // (2) Convert zoom string to integer (if possible)
        if (is_numeric($zoom)) {
            $zoom = (int) $zoom;
        } else {
            $zoom = false;
        }
        // (3) Zoom is required if no markers exist (https://stackoverflow.com/a/11749810/3467557)
        if (!$zoom && !$markers) {
            $zoom = 11;
        }
        // (4) Set zoom if valid, otherwise remove it
        if ($zoom) {
            $options['zoom'] = $zoom;
        } else {
            unset($options['zoom']);
        }

        // Use only specified field(s)
        $selectedFields = (isset($options['field']) ? $options['field'] : null);

        $markerOptions     = (isset($options['markerOptions'])     ? $options['markerOptions']     : []);
        $infoWindowOptions = (isset($options['infoWindowOptions']) ? $options['infoWindowOptions'] : []);
        unset($options['markerOptions']);
        unset($options['infoWindowOptions']);

        // If marker info template specified, move to info window options
        if (isset($options['markerInfo'])) {
            $infoWindowOptions['template'] = $options['markerInfo'];
            unset($options['markerInfo']);
        }

        // Determine map center
        $markersCenter = $this->_parseMarkers($mapId, $markers, $markerOptions, $selectedFields);
        if (isset($options['center'])) {
            $center = $this->_parseCenter($options['center']);
        } else {
            $center = $this->_parseCenter($markersCenter);
        }
        $options['center'] = 'smartMap.coords('.$center['lat'].','.$center['lng'].')';

        // Set map cleanup data
        $cleanupData = [
            'mapId' => $mapId,
            'zoom'  => $zoom,
        ];

        // Compile JS for map, markers, and info windows
        $js  = $this->_buildMap($mapId, $options);
        $js .= $this->_buildMarkers($mapId, $markerOptions);
        $js .= $this->_buildInfoWindows($mapId, $infoWindowOptions);
        $js .= $this->_mapCleanup($cleanupData);

        // Register JS
        $view = Craft::$app->getView();
        $view->registerJs($js, $view::POS_END);

        // Add to map total
        $this->_mapTotal++;

        $this->loadAssets();
        $html = '<div id="'.$mapId.'" class="smartmap-mapcanvas" style="'.$width.$height.'">'.Craft::t('smart-map', 'Loading map...').'</div>';
        return Template::raw($html);
    }

    // Parse location variations into standard format
    private function _parseMarkers($mapId, $locations, $markerOptions = [], $selectedFields = null)
    {
        // Organize markers
        if (!is_array($locations)) {
            // If $locations is an ElementQuery
            if (is_a($locations, ElementQuery::class)) {
                return $this->_parseMarkers($mapId, $locations->all(), $markerOptions, $selectedFields);
            }
            // If $locations is a single element
            if (is_a($locations, Element::class)) {
                $locations = [$locations];
                return $this->_parseMarkers($mapId, $locations, $markerOptions, $selectedFields);
            }
            // No locations, throw exception
            if (!$locations) {
                throw new Exception('The `locations` parameter cannot be empty.');
            }
        }

        // Set defaults
        $markers = [];
        $center = SmartMap::$plugin->smartMap->defaultCoords();

        // If multiple locations
        if (is_array($locations) && !empty($locations)) {

            $allLats = [];
            $allLngs = [];

            // If location elements are Matrix fields
            if (is_a($locations[0], MatrixBlock::class)) {
                // Get all Address field handles within Matrix
                $handles = [];
                $matrixFieldId = $locations[0]->fieldId;
                $blockTypes = Craft::$app->matrix->getBlockTypesByFieldId($matrixFieldId);
                foreach ($blockTypes as $blockType) {
                    $allFields = $blockType->getFields();
                    $newHandles = $this->_listFieldHandles($allFields, $selectedFields);
                    $handles = array_merge($handles, $newHandles);
                }
            } else if (is_a($locations[0], 'verbb\\supertable\\elements\\SuperTableBlockElement')) {
                if (!class_exists(\verbb\supertable\SuperTable::class)) {
                    throw new Exception('Super Table is not installed.');
                }
                // Get all Address field handles within Super Table
                $handles = [];
                $supertableFieldId = $locations[0]->fieldId;
                $superTable = \verbb\supertable\SuperTable::$plugin->service;
                $blockTypes = $superTable->getBlockTypesByFieldId($supertableFieldId);
                foreach ($blockTypes as $blockType) {
                    $allFields = $blockType->getFields();
                    $newHandles = $this->_listFieldHandles($allFields, $selectedFields);
                    $handles = array_merge($handles, $newHandles);
                }
            } else {
                // Get all Address field handles
                $allFields = Craft::$app->fields->getAllFields();
                $handles = $this->_listFieldHandles($allFields, $selectedFields);
            }

            // Loop through all location elements
            foreach ($locations as $element) {
                $title = $element->title;
                // Loop through all Address fields
                foreach ($handles as $fieldHandle) {
                    // If field is being used in this element
                    if (isset($element->{$fieldHandle}) && !empty($element->{$fieldHandle})) {
                        $el = $element->{$fieldHandle};
                        // Set coordinates
                        $lat = ($el['lat'] ? $el['lat'] : null);
                        $lng = ($el['lng'] ? $el['lng'] : null);
                        // Add marker
                        $markerName = $this->_getMarkerName($el);
                        if ($markerName && is_numeric($lat) && is_numeric($lng)) {
                            $markers[$markerName] = [
                                'title'      => $title,
                                'mapId'      => $mapId,
                                'markerName' => $markerName,
                                'lat'        => $lat,
                                'lng'        => $lng,
                                'element'    => $element,
                            ];
                            // Add coordinates to average
                            $allLats[] = $lat;
                            $allLngs[] = $lng;
                        }
                    }
                }
            }

            // If any coordinates were provided
            if (!empty($allLats) && !empty($allLngs)) {
                // Calculate center of map
                $centerLat = (min($allLats) + max($allLats)) / 2;
                $centerLng = (min($allLngs) + max($allLngs)) / 2;
                $center = [
                    'lat' => round($centerLat, 6),
                    'lng' => round($centerLng, 6)
                ];
            }

        } else {

            // Set solo marker
            $el = $locations;

            if (!is_array($el)) {
                $el = $el->attributes;
            }

            $markerName = $this->_getMarkerName($el);
            if ($markerName) {
                $markers[$markerName] = [
                    'mapId'      => $mapId,
                    'markerName' => $markerName,
                    'lat'        => $el['lat'],
                    'lng'        => $el['lng'],
                    'element'    => Craft::$app->elements->getElementById($locations['elementId']),
                ];
                if (isset($markerOptions['title'])) {
                    $markers[$markerName]['title'] = $markerOptions['title'];
                }
            }
            // If coordinates exist, set center
            if (isset($el['lat']) && isset($el['lng']) && is_numeric($el['lat']) && is_numeric($el['lng'])) {
                $center = [
                    'lat' => $el['lat'],
                    'lng' => $el['lng'],
                ];
            }

        }

        // Set markers
        $this->_marker[$mapId] = $markers;

        // Return center
        return $center;
    }

    // Get marker name from element
    private function _getMarkerName($el)
    {
        // If no element, bail
        if (empty($el)) {
            return false;
        }
        // If no field ID, bail
        if (!$el['fieldId']) {
            return false;
        }
        // If no element ID, bail
        if (!$el['elementId']) {
            return false;
        }
        // Get field by ID
        $field = Craft::$app->fields->getFieldById($el['fieldId']);
        // If field, bail
        if (!$field) {
            return false;
        }
        return $el['elementId'].'.'.$field->handle;
    }

    // Parse coordinates into standard format
    private function _parseCenter($coords)
    {
        // Default coordinates
        $lat = null;
        $lng = null;

        // If object, convert to array
        if (is_object($coords)) {
            // http://stackoverflow.com/a/2476954/3467557
            $coords = get_object_vars($coords);
        }

        // Parse coordinates from array
        if (is_array($coords)) {
            if ((2 == count($coords)) && isset($coords[0]) && isset($coords[1])) {
                // Center is [#, #]
                $lat = $coords[0];
                $lng = $coords[1];
            } else if (isset($coords['lat'])) {
                // Center is {lat:#, lng:#} or variation
                $lat = $coords['lat'];
                if (isset($coords['lng'])) {
                    $lng = $coords['lng'];
                } else if (isset($coords['lon'])) {
                    $lng = $coords['lon'];
                } else if (isset($coords['long'])) {
                    $lng = $coords['long'];
                }
            } else if (isset($coords['latitude']) && isset($coords['longitude'])) {
                // Center is {latitude:#, longitude:#}
                $lat = $coords['latitude'];
                $lng = $coords['longitude'];
            }
        }

        // Fallback
        if (!is_numeric($lat) || !is_numeric($lng)) {
            $default = SmartMap::$plugin->smartMap->defaultCoords();
            $lat = $default['lat'];
            $lng = $default['lng'];
        }

        return [
            'lat' => $lat,
            'lng' => $lng,
        ];
    }

    // Find all Smart Map Address field handles
    private function _listFieldHandles($allFields, $selectedFields = null)
    {
        // Convert selected fields to an array
        if ($selectedFields && is_string($selectedFields)) {
            $selectedFields = [$selectedFields];
        }

        // Collect address field handles
        $handles = [];
        foreach ($allFields as $field) {
            // If not an address field, skip
            if ($field->className() != AddressField::class) {
                continue;
            }
            // If field was not selected, skip
            if ($selectedFields && !in_array($field->handle, $selectedFields)) {
                continue;
            }
            // Add field handle
            $handles[] = $field->handle;
        }
        return $handles;
    }

    // Create single map
    private function _buildMap($mapId, $mapOptions)
    {
        $usingScrollwheel     = isset($mapOptions['scrollwheel']);
        $usingGestureHandling = isset($mapOptions['gestureHandling']);

        if (!$usingScrollwheel && !$usingGestureHandling) {
            $mapOptions['scrollwheel'] = false;
        }

        $options = $this->_jsonify($mapOptions);
        $mapJs  = PHP_EOL;
        $mapJs .= PHP_EOL.'smartMap.log("['.$mapId.'] Drawing map...");';
        $mapJs .= PHP_EOL.'smartMap.createMap("'.$mapId.'", '.$options.');';
        return $mapJs;
    }

    // Create single marker
    private function _buildMarkers($mapId, $markerOptions)
    {
        $markerJs = '';
        foreach ($this->_marker[$mapId] as $markerName => $marker) {

            $lat = $marker['lat'];
            $lng = $marker['lng'];

            if (!$lat || !$lng || !is_numeric($lat) || !is_numeric($lng)) {
                $markerJs .= PHP_EOL;
                $markerJs .= PHP_EOL.'smartMap.log("['.$mapId.'.'.$markerName.'] Unable to draw marker, invalid coordinates.");';
                continue;
            }

            $markerOptions['mapId']    = $mapId;
            $markerOptions['map']      = 'smartMap.map["'.$mapId.'"]';
            $markerOptions['position'] = 'smartMap.coords('.$lat.','.$lng.')';

            if (isset($marker['title'])) {
                $markerOptions['title'] = $marker['title'];
            }

            $options = $this->_jsonify($markerOptions);
            $markerJs .= PHP_EOL;
            $markerJs .= PHP_EOL.'smartMap.log("['.$mapId.'.'.$markerName.'] Drawing marker...");';
            $markerJs .= PHP_EOL.'smartMap.createMarker("'.$mapId.'.'.$markerName.'", '.$options.');';
        }
        return $markerJs;
    }

    // Create single info window
    private function _buildInfoWindows($mapId, $infoWindowOptions)
    {

        $contentExists = isset($infoWindowOptions['content']);
        $template = (isset($infoWindowOptions['template']) ? $infoWindowOptions['template'] : null);
        unset($infoWindowOptions['template']);

        $infoWindowJs = '';
        foreach ($this->_marker[$mapId] as $markerName => $marker) {
            if (!$contentExists) {
                if ($template) {
                    if (isset($marker['title'])) {
                        //$marker['element']['title'] = $marker['title'];
                    }
                    try {
                        $infoWindowOptions['content'] = Craft::$app->getView()->renderTemplate($template, [
                            'element' => $marker['element'],
                            'marker'  => [
                                'mapId'      => $marker['mapId'],
                                'markerName' => $marker['mapId'].'.'.$marker['markerName'],
                                'coords'     => [
                                    'lat' => $marker['lat'],
                                    'lng' => $marker['lng'],
                                ],
                            ],
                        ]);
                    } catch (\Exception $e) {
                        $infoWindowOptions['content']  = '<strong>Marker Info Template Error</strong><br />';
                        $infoWindowOptions['content'] .= $e->getMessage();
                    }
                } else {
                    // Some form of content is required
                    return null;
                }
            }

            $options = $this->_jsonify($infoWindowOptions);
            $infoWindowJs .= PHP_EOL;
            $infoWindowJs .= PHP_EOL.'smartMap.log("['.$mapId.'.'.$markerName.'] Drawing info window...");';
            $infoWindowJs .= PHP_EOL.'smartMap.createInfoWindow("'.$mapId.'.'.$markerName.'", '.$options.');';

        }
        return $infoWindowJs;
    }

    // Cleanup
    private function _mapCleanup($data)
    {
        $mapJs = '';
        if (!$data['zoom']) {
            $mapJs .= PHP_EOL;
            $mapJs .= PHP_EOL.'smartMap.log("['.$data['mapId'].'] Fitting bounds...");';
            $mapJs .= PHP_EOL.'smartMap.fitBounds("'.$data['mapId'].'");';
        }
        return $mapJs;
    }

    // Encode JSON with function calls
    private function _jsonify($dataArr)
    {
        // Don't tokenize these key values
        $protected = ['content'];

        $tokens = [];
        foreach ($dataArr as $key => $value) {

            // Skip tokenizing specified key values
            if (in_array($key, $protected)) {
                continue;
            }

            // Recursive
            if (is_array($value)) {
                $value = $this->_jsonify($value);
            }

            // Identify special strings
            $smartMap  = (false !== strpos((string) $value, 'smartMap.'));
            $googleMap = (false !== strpos((string) $value, 'google.maps.'));

            // Parse special strings back to JS
            if ($smartMap || $googleMap) {
                $token = StringHelper::randomString();
                $dataArr[$key]  = '%'.$token.'%';
                $tokens[]       = '"%'.$token.'%"';
                $replacements[] = $value;
            }

        }
        $json = json_encode($dataArr);
        if ($tokens) {
            $json = str_replace($tokens, $replacements, $json);
        }
        return $json;
    }


    // ================================================================== //
    // ================================================================== //


    // Create <img> of static map
    public function staticMap($markers, $options = [])
    {
        $src = $this->staticMapSrc($markers, $options);
        $dimensions = '';
        foreach (['width','height'] as $side) {
            if (isset($options[$side])) {
                $dimensions .= ' '.$side.'="'.$options[$side].'"';
            }
        }
        $this->loadAssets();
        return Template::raw('<img src="'.$src.'" '.$dimensions.'/>');
    }

    // Get source of static map image
    public function staticMapSrc($markers, $options = [])
    {

        //$filter = SmartMap_FilterCriteriaModel::populateModel($coords, $options = []);

        // Decipher map info
        $map = SmartMap::$plugin->smartMap->markerCoords($markers, $options);

        $width   = (isset($options['width'])   ? $options['width']   : '200');
        $height  = (isset($options['height'])  ? $options['height']  : '150');
        $scale   = (isset($options['scale'])   ? $options['scale']   : '2');
        $maptype = (isset($options['maptype']) ? $options['maptype'] : 'roadmap');

        if (isset($options['markerOptions']) && isset($options['markerOptions']['icon'])) {
            $icon = $options['markerOptions']['icon'];
            if (is_array($icon)) {
                $icon = ($icon['url'] ?? '');
            }
            $markerIcon = 'icon:'.urlencode($icon);
        } else {
            $markerIcon = 'color:red';
        }

        $rootUrl = 'https://maps.googleapis.com/maps/api/staticmap';

        $data  = '?visual_refresh=true';
        $data .= SmartMap::$plugin->smartMap->googleBrowserKey('&amp;');
        $data .= '&amp;scale='.$scale; // Retina
        $data .= '&amp;center='.$map['center']['lat'].','.$map['center']['lng'];
        $data .= '&amp;size='.$width.'x'.$height;
        $data .= '&amp;maptype='.$maptype;

        if (isset($options['zoom'])) {
            $data .= '&amp;zoom='.$options['zoom'];
        }

        $i = 0;
        foreach ($map['markers'] as $marker) {
            if ($i) {$data .= '|';}
            $data .= '&amp;markers='.$markerIcon.'%7C'.$marker['lat'].','.$marker['lng'];
        }

        return Template::raw($rootUrl.$data);

        // MORE COMPLEX EXAMPLE (https://developers.google.com/maps/documentation/staticmaps/index)
        // https://maps.googleapis.com/maps/api/staticmap?center=Brooklyn+Bridge,New+York,NY&zoom=13&size=600x300&maptype=roadmap&markers=color:blue%7Clabel:S%7C40.702147,-74.015794&markers=color:green%7Clabel:G%7C40.711614,-74.012318&markers=color:red%7Ccolor:red%7Clabel:C%7C40.718217,-73.998284&sensor=false

        // SHOULD ALSO BUILD IN FUNCTIONALITY FOR STREET VIEW MAPS
        // https://maps.googleapis.com/maps/api/streetview?size=200x200&location=40.720032,-73.988354&heading=235&sensor=false

    }


    // ================================================================== //
    // ================================================================== //


    /**
     * TODO:
     * - Allow a string for the first parameter. KML files may be remote (and must be publicly accessible).
     */
    // Load a KML map file
    public function kmlMap($kmlFile, $options = [])
    {
        // If no file specified, bail
        if (!$kmlFile) {
            return 'No KML file specified.';
        }
        // Get the KML URL
        if (is_a($kmlFile, Asset::class)) {
            if (!$kmlFile->url) {
                return 'Invalid KML file. Check that your asset source allows for public URLs.';
            }
        } else if (!is_string($kmlFile)) {
            return 'Invalid KML file. Please specify an Asset or absolute URL.';
        }
        // Create a new map
        $mapId = (isset($options['id']) ? $options['id'] : 'smartmap-mapcanvas-'.$this->_mapTotal);
        $html  = $this->dynamicMap([], $options);
        // Apply KML layer
        $this->kmlMapLayer($kmlFile, $mapId);
        return Template::raw($html);
    }

    // Add a KML map layer
    public function kmlMapLayer($kmlFile, $mapId)
    {
        // If no file specified, bail
        if (!$kmlFile) {
            return 'No KML file specified.';
        }
        // Get the KML URL
        if (is_string($kmlFile)) {
            $url = $kmlFile;
        } else if (is_a($kmlFile, Asset::class)) {
            $url = $kmlFile->url;
            if (!$url) {
                return 'Invalid KML file. Check that your asset source allows for public URLs.';
            }
        } else {
            return 'Invalid KML file. Please specify an Asset or absolute URL.';
        }
        // Get view
        $view = Craft::$app->getView();
        // Log attempt
        $js = '
smartMap.log("['.$mapId.'] Adding KML layer: '.$url.'");';
        // Apply KML layer
        if (UrlHelper::isAbsoluteUrl($url)) {
            // Success
            $js .= '
new google.maps.KmlLayer("'.$url.'", {
    map: smartMap.map["'.$mapId.'"]
});';
            $message = 'KML layer applied successfully.';
        } else {
            // Failure
            $message = 'Error: URL for KML file must be absolute.';
        }
        $js .= '
smartMap.log("['.$mapId.'] '.$message.'");';
        // Output response message
        $view->registerJs($js, $view::POS_END);
    }


    // ================================================================== //
    // ================================================================== //


    // Get a link to open the Google map
    public function linkToGoogle($address, $title = null)
    {
        // If missing or invalid address, bail
        if (!$address || !is_a($address, AddressModel::class)) {
            return '#invalid-address-field';
        }
        // If missing coordinates, bail
        if (!$address->hasCoords()) {
            return '#missing-address-coordinates';
        }
        // Get coordinates
        $coords = implode(',', $address->coords);
        // Get location name
        if ($title) {
            $place = urlencode((string) $title);
        } else if ($address->isEmpty()) {
            $place = $coords;
        } else {
            $place = urlencode((string) $address->format(true, true));
        }
        // Return link
        return 'https://www.google.com/maps/place/'.$place.'/@'.$coords;
    }

    // Get a link to open directions on a Google map
    public function linkToDirections($destinationAddress, $destinationTitle = null, $originTitle = false, $originAddress = false)
    {
        // If no destination address, bail
        if (!$destinationAddress) {
            return '#missing-address-field';
        }
        // If destination address isn't an Address model, bail
        if (!is_a($destinationAddress, AddressModel::class)) {
            return '#invalid-address-field';
        }
        // If starting address isn't an Address model, set it to false
        if (!is_a($originAddress, AddressModel::class)) {
            $originAddress = false;
        }
        // Compile URL
        $url = 'https://www.google.com/maps/dir/?api=1&';
        if ($originAddress) {
            $url .= 'origin='.$this->_formatForDirections($originAddress, $originTitle).'&';
        }
        $url .= 'destination='.$this->_formatForDirections($destinationAddress, $destinationTitle);
        // Return link
        return $url;
    }

    /**
     * Format address for directions URL
     *
     * @return string
     */
    public function _formatForDirections($address, $title = false)
    {
        // 1. Comma-separated latitude/longitude coordinates
        if ($address->hasCoords()) {
            return implode(',', $address->coords);
        }
        // 2. Address
        if (!$address->isEmpty()) {
            return urlencode((string) $address->format(true, true));
        }
        // 3A. Place name (custom title)
        if ($title) {
            return urlencode($title);
        }
        // 3B. Place name (entry title)
        $element = Entry::find()->id($address->elementId)->one();
        if ($element) {
            return urlencode($element->title);
        }
        // ¯\_(ツ)_/¯
        return false;
    }

}
