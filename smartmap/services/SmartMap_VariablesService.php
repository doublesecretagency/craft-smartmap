<?php
namespace Craft;

class SmartMap_VariablesService extends BaseApplicationComponent
{

    private $_needsJs = true;

    private $_mapTotal = 1;
    private $_marker = array();

    public function init() {
        parent::init();
        craft()->templates->includeCssResource('smartmap/css/smartmap.css');
    }

    // Ensure JS has been loaded
    public function loadJs($renderHere = false)
    {
        if ($this->_needsJs) {
            $this->_needsJs = false;

            // Google Maps API
            $api  = 'https://maps.googleapis.com/maps/api/js';
            $api .= craft()->smartMap->googleBrowserKey('?');

            // Smart Map JS
            $resourceUrl = 'smartmap/js/smartmap.js';

            // Enable console logging
            $devMode = craft()->config->get('devMode');
            $enableConsoleLogging = 'smartMap.enableLogging = '.($devMode ? 'true' : 'false').';';

            // Render JS
            if ($renderHere) {
                // Directly output JS
                return TemplateHelper::getRaw('
<script type="text/javascript" src="'.$api.'"></script>
<script type="text/javascript" src="'.UrlHelper::getResourceUrl($resourceUrl).'"></script>
<script type="text/javascript">'.$enableConsoleLogging.'</script>'.PHP_EOL);
            } else {
                // Queue up JS for footer
                craft()->templates->includeJsFile($api);
                craft()->templates->includeJsResource($resourceUrl);
                craft()->templates->includeJs($enableConsoleLogging, true);
            }
        }
    }

    // Create dynamic Google Map of locations
    public function dynamicMap($markers = false, $options = array())
    {

        // Extract non-Google parameters
        $mapId  = (array_key_exists('id', $options) ? $options['id'] : 'smartmap-mapcanvas-'.$this->_mapTotal);
        unset($options['id']);

        $width  = (array_key_exists('width', $options)  ? 'width:'.$options['width'].'px;'   : '');
        $height = (array_key_exists('height', $options) ? 'height:'.$options['height'].'px;' : '');
        unset($options['width']);
        unset($options['height']);

        // No zoom by default
        $zoom = false;
        // (1) Convert zoom object to string (if possible)
        if (array_key_exists('zoom', $options)) {
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

        $markerOptions     = (array_key_exists('markerOptions', $options)     ? $options['markerOptions']     : array());
        $infoWindowOptions = (array_key_exists('infoWindowOptions', $options) ? $options['infoWindowOptions'] : array());
        unset($options['markerOptions']);
        unset($options['infoWindowOptions']);

        // If marker info template specified, move to info window options
        if (array_key_exists('markerInfo', $options)) {
            $infoWindowOptions['template'] = $options['markerInfo'];
            unset($options['markerInfo']);
        }

        // Determine map center
        $markersCenter = $this->_parseMarkers($mapId, $markers, $markerOptions);
        if (array_key_exists('center', $options)) {
            $center = $this->_parseCenter($options['center']);
        } else {
            $center = $this->_parseCenter($markersCenter);
        }
        $options['center'] = 'smartMap.coords('.$center['lat'].','.$center['lng'].')';

        // Set map cleanup data
        $cleanupData = array(
            'mapId' => $mapId,
            'zoom'  => $zoom,
        );

        // Compile JS for map, markers, and info windows
        $js  = $this->_buildMap($mapId, $options);
        $js .= $this->_buildMarkers($mapId, $markerOptions);
        $js .= $this->_buildInfoWindows($mapId, $infoWindowOptions);
        $js .= $this->_mapCleanup($cleanupData);
        craft()->templates->includeJs($js);

        // Add to map total
        $this->_mapTotal++;

        $this->loadJs();
        $html = '<div id="'.$mapId.'" class="smartmap-mapcanvas" style="'.$width.$height.'">Loading map...</div>';
        return TemplateHelper::getRaw($html);
    }

    // Parse location variations into standard format
    private function _parseMarkers($mapId, $locations, $markerOptions = array())
    {

        // Organize markers
        if (!is_array($locations)) {
            // If $locations is an ElementCriteriaModel
            if (is_a($locations, 'Craft\\ElementCriteriaModel')) {
                return $this->_parseMarkers($mapId, $locations->find());
            // If $locations is a single element
            } else if (is_a($locations, 'Craft\\BaseElementModel')) {
                $locations = array($locations);
                return $this->_parseMarkers($mapId, $locations);
            } else if (!$locations) {
                throw new Exception('The `locations` parameter cannot be empty.');
            }
        }

        // Set defaults
        $markers = array();
        $center = craft()->smartMap->defaultCoords();

        // If multiple locations
        if (!empty($locations) && array_key_exists(0, $locations)) {

            $allLats = array();
            $allLngs = array();

            // If location elements are Matrix fields
            if (is_a($locations[0], 'Craft\\MatrixBlockModel')) {
                // Get all Address field handles within Matrix
                $handles = array();
                $matrixFieldId = $locations[0]->fieldId;
                $blockTypes = craft()->matrix->getBlockTypesByFieldId($matrixFieldId);
                foreach ($blockTypes as $blockType) {
                    $allFields = $blockType->getFields();
                    $newHandles = $this->_listFieldHandles($allFields);
                    $handles = array_merge($handles, $newHandles);
                }
            } else {
                // Get all Address field handles
                $allFields = craft()->fields->getAllFields();
                $handles = $this->_listFieldHandles($allFields);
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
                            $markers[$markerName] = array(
                                'title'      => $title,
                                'mapId'      => $mapId,
                                'markerName' => $markerName,
                                'lat'        => $lat,
                                'lng'        => $lng,
                                'element'    => $element,
                            );
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
                $center = array(
                    'lat' => round($centerLat, 6),
                    'lng' => round($centerLng, 6)
                );
            }

        } else {

            // Set solo marker
            $el = $locations;

            if (!is_array($el)) {
                $el = $el->attributes;
            }

            $markerName = $this->_getMarkerName($el);
            if ($markerName) {
                $markers[$markerName] = array(
                    'mapId'      => $mapId,
                    'markerName' => $markerName,
                    'lat'        => $el['lat'],
                    'lng'        => $el['lng'],
                    'element'    => craft()->elements->getElementById($locations['elementId']),
                );
                if (array_key_exists('title', $markerOptions)) {
                    $markers[$markerName]['title'] = $markerOptions['title'];
                }
            }
            // If coordinates exist, set center
            if (array_key_exists('lat',$el) && array_key_exists('lng',$el) && is_numeric($el['lat']) && is_numeric($el['lng'])) {
                $center = array(
                    'lat' => $el['lat'],
                    'lng' => $el['lng'],
                );
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
        if (!empty($el) && $el['fieldId'] && $el['elementId']) {
            $field = craft()->fields->getFieldById($el['fieldId']);
            return $el['elementId'].'.'.$field->handle;
        } else {
            return false;
        }
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
            if ((2 == count($coords)) && array_key_exists(0, $coords) && array_key_exists(1, $coords)) {
                // Center is [#, #]
                $lat = $coords[0];
                $lng = $coords[1];
            } else if (array_key_exists('lat', $coords)) {
                // Center is {lat:#, lng:#} or variation
                $lat = $coords['lat'];
                if (array_key_exists('lng', $coords)) {
                    $lng = $coords['lng'];
                } else if (array_key_exists('lon', $coords)) {
                    $lng = $coords['lon'];
                } else if (array_key_exists('long', $coords)) {
                    $lng = $coords['long'];
                }
            } else if (array_key_exists('latitude', $coords) && array_key_exists('longitude', $coords)) {
                // Center is {latitude:#, longitude:#}
                $lat = $coords['latitude'];
                $lng = $coords['longitude'];
            }
        }

        // Fallback
        if (!is_numeric($lat) || !is_numeric($lng)) {
            $default = craft()->smartMap->defaultCoords();
            $lat = $default['lat'];
            $lng = $default['lng'];
        }

        return array(
            'lat' => $lat,
            'lng' => $lng,
        );
    }

    // Find all Smart Map Address field handles
    private function _listFieldHandles($allFields)
    {
        $handles = array();
        foreach ($allFields as $field) {
            if ($field->type == 'SmartMap_Address') {
                $handles[] = $field->handle;
            }
        }
        return $handles;
    }

    // Create single map
    private function _buildMap($mapId, $mapOptions)
    {
        // LEGACY: "scrollZoom" option
        if (!array_key_exists('scrollwheel', $mapOptions)) {
            if (array_key_exists('scrollZoom', $mapOptions)) {
                $mapOptions['scrollwheel'] = (bool) $mapOptions['scrollZoom'];
            } else {
                $mapOptions['scrollwheel'] = false;
            }
        }
        unset($mapOptions['scrollZoom']);

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

            if (array_key_exists('title', $marker)) {
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

        $contentExists = array_key_exists('content', $infoWindowOptions);
        $template = (array_key_exists('template', $infoWindowOptions) ? $infoWindowOptions['template'] : null);
        unset($infoWindowOptions['template']);

        $infoWindowJs = '';
        foreach ($this->_marker[$mapId] as $markerName => $marker) {
            if (!$contentExists) {
                if ($template) {
                    if (array_key_exists('title', $marker)) {
                        //$marker['element']['title'] = $marker['title'];
                    }
                    try {
                        $markerVars = array(
                            'mapId'      => $marker['mapId'],
                            'markerName' => $marker['mapId'].'.'.$marker['markerName'],
                            'coords'     => array(
                                'lat' => $marker['lat'],
                                'lng' => $marker['lng'],
                            ),
                        );
                        $html = craft()->templates->render($template, array(
                            'marker'  => $markerVars,
                            'element' => $marker['element'],
                        ));
                        $infoWindowOptions['content'] = $html;
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
        $tokens = array();
        //array_walk_recursive($dataArr, function ($value, $key) {});
        foreach ($dataArr as $key => $value) {
            $token = StringHelper::randomString();
            $smartMap  = (0 === strpos((string) $value, 'smartMap.'));
            $googleMap = (0 === strpos((string) $value, 'google.maps.'));
            if ($smartMap || $googleMap) {
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
    public function staticMap($markers, $options = array())
    {
        $src = $this->staticMapSrc($markers, $options);
        $dimensions = '';
        foreach (array('width','height') as $side) {
            if (array_key_exists($side, $options)) {
                $dimensions .= ' '.$side.'="'.$options[$side].'"';
            }
        }
        $this->loadJs();
        return TemplateHelper::getRaw('<img src="'.$src.'" '.$dimensions.'/>');
    }

    // Get source of static map image
    public function staticMapSrc($markers, $options = array())
    {

        //$filter = SmartMap_FilterCriteriaModel::populateModel($coords, $options = array());

        // Decipher map info
        $map = craft()->smartMap->markerCoords($markers, $options);

        $width   = (array_key_exists('width', $options)   ? $options['width']   : '200');
        $height  = (array_key_exists('height', $options)  ? $options['height']  : '150');
        $scale   = (array_key_exists('scale', $options)   ? $options['scale']   : '2');
        $maptype = (array_key_exists('maptype', $options) ? $options['maptype'] : 'roadmap');

        if (array_key_exists('markerOptions', $options) && array_key_exists('icon', $options['markerOptions'])) {
            $markerIcon = 'icon:'.urlencode($options['markerOptions']['icon']);
        } else {
            $markerIcon = 'color:red';
        }

        $rootUrl = 'https://maps.googleapis.com/maps/api/staticmap';

        $data  = '?visual_refresh=true';
        $data .= craft()->smartMap->googleBrowserKey();
        $data .= '&amp;scale='.$scale; // Retina
        $data .= '&amp;center='.$map['center']['lat'].','.$map['center']['lng'];
        $data .= '&amp;size='.$width.'x'.$height;
        $data .= '&amp;maptype='.$maptype;

        if (array_key_exists('zoom', $options)) {
            $data .= '&amp;zoom='.$options['zoom'];
        }

        $i = 0;
        foreach ($map['markers'] as $marker) {
            if ($i) {$data .= '|';}
            $data .= '&amp;markers='.$markerIcon.'%7C'.$marker['lat'].','.$marker['lng'];
        }

        return $rootUrl.$data;

        // MORE COMPLEX EXAMPLE (https://developers.google.com/maps/documentation/staticmaps/index)
        // https://maps.googleapis.com/maps/api/staticmap?center=Brooklyn+Bridge,New+York,NY&zoom=13&size=600x300&maptype=roadmap&markers=color:blue%7Clabel:S%7C40.702147,-74.015794&markers=color:green%7Clabel:G%7C40.711614,-74.012318&markers=color:red%7Ccolor:red%7Clabel:C%7C40.718217,-73.998284&sensor=false

        // SHOULD ALSO BUILD IN FUNCTIONALITY FOR STREET VIEW MAPS
        // https://maps.googleapis.com/maps/api/streetview?size=200x200&location=40.720032,-73.988354&heading=235&sensor=false

    }


    // ================================================================== //
    // ================================================================== //


    // Load a KML map file
    public function kmlMap($kmlFile, $options = array())
    {
        if (!$kmlFile || !is_a($kmlFile, 'Craft\AssetFileModel')) {
            return 'Invalid KML file';
        }
        // Create a new map
        $mapId = (array_key_exists('id', $options) ? $options['id'] : 'smartmap-mapcanvas-'.$this->_mapTotal);
        $html  = $this->dynamicMap(array(), $options);
        // Apply KML layer
        $this->kmlMapLayer($kmlFile, $mapId);
        return TemplateHelper::getRaw($html);
    }


    // Add a KML map layer
    public function kmlMapLayer($kmlFile, $mapId)
    {
        if (!$kmlFile || !is_a($kmlFile, 'Craft\AssetFileModel')) {
            return 'Invalid KML file';
        }
        // Include JS to apply KML layer
        craft()->templates->includeJs('
$(function () {
    var src = "'.$kmlFile->url.'";
    new google.maps.KmlLayer(src, {
        map: smartMap.map["'.$mapId.'"]
    });
});
');
    }


    // ================================================================== //
    // ================================================================== //


    // Get a link to open the Google map
    public function linkToGoogle($address)
    {
        if (!$address) {
            return '#invalid-address-field';
        }
        $q = '';
        $components = array('street1','city','state','zip');
        foreach ($components as $key) {
            if ($address->{$key}) {
                if ($q) {$q .= ', ';}
                $q .= $address->{$key};
            }
        }
        if ($address->lat && $address->lng) {
            $coords = $address->lat.'+'.$address->lng;
            return 'https://maps.google.com/maps?q='.($q ? rawurlencode($q) : $coords).'&ll='.$coords;
        } else {
            return '#no-address-coordinates';
        }
    }

    // Get a link to open directions on a Google map
    public function linkToDirections($address, $title = null)
    {
        if (!$address) {
            return '#invalid-address-field';
        }
        if ($address->lat && $address->lng) {
            $coords = $address->lat.','.$address->lng;
        } else {
            return '#no-address-coordinates';
        }
        if (!$title) {
            $components = array('street1','city','state','zip');
            foreach ($components as $key) {
                if ($address->{$key}) {
                    if ($title) {$title .= ', ';}
                    $title .= $address->{$key};
                }
            }
        }
        return 'https://maps.google.com/maps?daddr='.rawurlencode($title).'@'.$coords;
    }

}