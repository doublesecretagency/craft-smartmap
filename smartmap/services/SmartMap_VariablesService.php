<?php
namespace Craft;

class SmartMap_VariablesService extends BaseApplicationComponent
{

    private $_mapCounter = 0;
    private $_allMapsInitialized = false;

    private $_segments;

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
    private function _initializeAllMaps()
    {

        $this->_segments = craft()->request->getSegments();
        array_unshift($this->_segments, '');

        //craft()->smartMap->checkApiKey();

        // Include JavaScript & CSS
        craft()->templates->includeJsFile('//maps.google.com/maps/api/js?sensor=false');
        craft()->templates->includeJsResource('smartmap/js/smartmap.js');
        craft()->templates->includeCssResource('smartmap/css/smartmap.css');

        // All maps JS
        $allMapsJs = 'smartMap.searchUrl = "'.UrlHelper::getActionUrl('smartMap/search').'";'.PHP_EOL;
        craft()->templates->includeJs($allMapsJs);

        $this->_allMapsInitialized = true;
    }

    // JS of individual map
    private function _mapJs($markers, $options)
    {

        // Generate unique map ID
        $uniqueId = ++$this->_mapCounter;

        $js = '';

        // Decipher map info
        $map = craft()->smartMap->markerCoords($markers, $options);

        // "id" option
        if (array_key_exists('id', $options)) {
            $mapId = $options['id'];
        } else {
            $mapId = 'smartmap-mapcanvas-'.$uniqueId;
        }

        // "center" option
        $mapOptions['center'] = json_encode($map['center']);

        // "zoom" option
        if (array_key_exists('zoom', $options) && is_int($options['zoom'])) {
            $mapOptions['zoom'] = $options['zoom'];
        } else {
            $mapOptions['zoom'] = craft()->smartMap->defaultZoom;
        }

        // "scrollZoom" option
        if (array_key_exists('scrollZoom', $options) && is_bool($options['scrollZoom'])) {
            $mapOptions['scrollwheel'] = ($options['scrollZoom'] ? 'true' : 'false');
        } else {
            $mapOptions['scrollwheel'] = 'false';
        }

        // Render map JS
        $renderMap = '';
        foreach ($mapOptions as $option => $value) {
            if ($renderMap) {$renderMap .= ', ';}
            $renderMap .= "$option: $value";
        }
        $js .= '
var marker;
smartMap.drawMap("'.$mapId.'", {'.$renderMap.'});';

        // Add map markers
        if ($map['markers'] && is_array($map['markers'])) {
            foreach ($map['markers'] as $i => $m) {
                $element = $m['element'];
                unset($m['element']);
                $js .= '
smartMap.drawMarker("'.$mapId.'", '.$i.', '.json_encode($m).');';
                if (array_key_exists('markerInfo', $options)) {
                    $infoWindowHtml = $this->_infoWindowHtml($mapId, $i, $element, $options['markerInfo']);
                    $js .= '
smartMap.drawMarkerInfo("'.$mapId.'", '.$i.', '.$infoWindowHtml.');';
                }
            }
        }

        craft()->templates->includeJs($js);

        return $mapId;
    }

    // Generate HTML for InfoWindow
    private function _infoWindowHtml($mapId, $markerNumber, $element, $template)
    {
        $html = craft()->templates->render($template, array(
            'mapId'        => $mapId,
            'markerNumber' => $markerNumber,
            'element'      => $element,
        ));
        return json_encode($html);
    }

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