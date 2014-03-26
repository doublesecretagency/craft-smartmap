<?php
namespace Craft;

class SmartMap_VariablesService extends BaseApplicationComponent
{

    private $_mapCounter = 0;
    private $_allMapsInitialized = false;

    // Create dynamic Google Map of locations
    public function dynamicMap($markers = false, $options = array())
    {

        if (!$this->_allMapsInitialized) {
            $this->_initializeAllMaps();
        }

        // If solo marker, process as an array
        if ($markers && !is_array($markers)) {
            return $this->dynamicMap(array($markers), $options);
        }

        // Render JS of map
        $mapId = $this->_mapJs($markers, $options);

        // Render width/height of map
        $style = '';
        $style .= (array_key_exists('width', $options)  ? 'width:'.$options['width'].'px;'   : '');
        $style .= (array_key_exists('height', $options) ? 'height:'.$options['height'].'px;' : '');

        return $this->_safeOutput(PHP_EOL.'<div id="'.$mapId.'" class="smartmap-mapcanvas" style="'.$style.'">Loading map...</div>');

    }

    // Initialize collection of all maps
    private function _initializeAllMaps() {

        //craft()->smartMap->checkApiKey();

        // Include JavaScript & CSS
        craft()->templates->includeJsFile('//maps.google.com/maps/api/js?sensor=false');
        craft()->templates->includeJsResource('smartmap/js/smartmap.js');
        craft()->templates->includeCssResource('smartmap/css/smartmap.css');

        // All maps JS
        $allMapsJs = 'smartMap.maps = {};'.PHP_EOL;
        //$allMapsJs .= 'smartMap.searchUrl = "'.UrlHelper::getActionUrl('smartMap/search').'";'.PHP_EOL;
        craft()->templates->includeJs($allMapsJs);

        $this->_allMapsInitialized = true;
    }

    // JS of individual map
    private function _mapJs($markers, $options) {

        // Generate unique map ID
        $uniqueId = ++$this->_mapCounter;

        $js = '';

        // Decipher map info
        $map = craft()->smartMap->markerCoords($markers, $options);

        if (array_key_exists('id', $options)) {
            $mapId = $options['id'];
            $js .= 'id: "'.$options['id'].'";'.PHP_EOL;
        } else {
            $mapId = 'smartmap-mapcanvas-'.$uniqueId;
        }

        $mapOptions['center'] = json_encode($map['center']);
        $mapOptions['zoom'] = (array_key_exists('zoom', $options) ? $options['zoom'] : craft()->smartMap->defaultZoom);

        $renderMap = '';
        foreach ($mapOptions as $option => $value) {
            if ($renderMap) {$renderMap .= ', ';}
            $renderMap .= "$option: $value";
        }
        $js .= '
smartMap.drawMap("'.$mapId.'", {'.$renderMap.'});';

        // Add map markers
        if ($map['markers']) {
            $js .= '
smartMap.drawMarkers("'.$mapId.'", '.json_encode($map['markers']).');';
        }

        //$template = (array_key_exists('markerInfo', $options) ? $options['markerInfo'] : false);

        /*
        // Add map markers
        if ($map['markers']) {
            foreach ($map['markers'] as $i => $m) {
                $js .= $this->_dynamicMapMarker($i, $m);
                $js .= $this->_dynamicMapMarkerInfo($i, $markers[$i], $template);
                //if (array_key_exists('click', $options)) {
                //  $js .= 'smartMap.markerClickEvent(marker, '.$options['click'].');'.PHP_EOL;
                //}
            }
        }
        */
        
        craft()->templates->includeJs($js);

        return $mapId;
    }

    /*
    // Add marker
    private function _dynamicMapMarker($i, $m)
    {
        $title = preg_replace('/"/', '\"', $m['title']);
        unset($m['title']);
        return 'smartMap.marker['.$i.'] = smartMap._drawMarker('.json_encode($m).', "'.$title.'");'.PHP_EOL;
    }
    // Add marker info bubble (if specified)
    //  * WARNING: Marker info bubbles are an undocumented feature of *
    //  * the Smart Map plugin. This feature may change at any time.  *
    private function _dynamicMapMarkerInfo($i, $entry, $template)
    {
        if ($template) {
            $segments = craft()->request->getSegments();
            array_unshift($segments, '');
            $html = craft()->templates->render($template, array(
                'i' => $i,
                'segment' => $segments,
            ));
            $infoWindow = craft()->templates->renderObjectTemplate($html, $entry);
            $infoWindow = json_encode($infoWindow);
            return "smartMap.addInfoWindow($i, $infoWindow)".PHP_EOL;
        } else {
            return '';
        }
    }
    */

    // Create <img> of static map
    public function staticMap($markers, $options = array())
    {
        $src = $this->staticMapSrc($markers, $options);
        return $this->_safeOutput('<img src="'.$src.'" />');
    }

    // Get source of static map image
    public function staticMapSrc($markers, $options = array())
    {

        //$filter = SmartMap_FilterCriteriaModel::populateModel($coords, $options = array());

        // Decipher map info
        $map = craft()->smartMap->markerCoords($markers, $options);

        $width  = (array_key_exists('width', $options)  ? $options['width']  : '200');
        $height = (array_key_exists('height', $options) ? $options['height'] : '200');

        $src  = '//maps.googleapis.com/maps/api/staticmap?sensor=false';
        //$src .= '&key='.craft()->smartMap->mapApiKey;
        $src .= '&center='.$map['center']['lat'].','.$map['center']['lng'];
        $src .= '&zoom='.(array_key_exists('zoom', $options) ? $options['zoom'] : craft()->smartMap->defaultZoom);
        $src .= '&size='.$width.'x'.$height;
        $src .= '&visual_refresh=true';
        $src .= '&maptype=roadmap';

        $i = 0;
        foreach ($map['markers'] as $marker) {
            if ($i) {$src .= '|';}
            $src .= '&markers=color:red%7C'.$marker['lat'].','.$marker['lng'];
        }

        return $src;

        // MORE COMPLEX EXAMPLE (https://developers.google.com/maps/documentation/staticmaps/index)
        // http://maps.googleapis.com/maps/api/staticmap?center=Brooklyn+Bridge,New+York,NY&zoom=13&size=600x300&maptype=roadmap&markers=color:blue%7Clabel:S%7C40.702147,-74.015794&markers=color:green%7Clabel:G%7C40.711614,-74.012318&markers=color:red%7Ccolor:red%7Clabel:C%7C40.718217,-73.998284&sensor=false

        // SHOULD ALSO BUILD IN FUNCTIONALITY FOR STREET VIEW MAPS
        // http://maps.googleapis.com/maps/api/streetview?size=200x200&location=40.720032,-73.988354&heading=235&sensor=false

    }

    // Get a link to open the Google map
    public function linkToGoogle($address)
    {
        $q = $address['street1'].', '.$address['city'].', '.$address['state'].', '.$address['zip'];
        return 'http://maps.google.com/?q='.$q;
    }
    
    // ============================================================== //

    // Marks html content as safe for output within templates
    //  - Courtesy of Selvin Ortiz (https://github.com/selvinortiz/spamguard/blob/master/bridge/Bridge.php)
    private function _safeOutput($content, $charset = null)
    {
        if (is_null($charset)) {
            $charset = craft()->templates->getTwig()->getCharset();
        }
        return new \Twig_Markup($content, (string) $charset);
    }

}