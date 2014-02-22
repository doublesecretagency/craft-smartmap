<?php
namespace Craft;

class SmartMap_VariablesService extends BaseApplicationComponent
{

    // Create dynamic Google Map of locations
    public function googleMap($markers = false, $options = array())
    {
        // If solo marker, process as an array
        if ($markers && !is_array($markers)) {
            return $this->googleMap(array($markers), $options);
        }

        //craft()->smartMap->checkApiKey();

        // Include JavaScript & CSS
        craft()->templates->includeJsFile('//maps.google.com/maps/api/js?sensor=false');
        craft()->templates->includeJsResource('smartmap/js/smartmap.js');
        craft()->templates->includeCssResource('smartmap/css/smartmap.css');
        craft()->templates->includeJs('smartMap.searchUrl = "'.UrlHelper::getActionUrl('smartMap/search').'";');

        // Decipher map info
        $map = craft()->smartMap->markerCoords($markers, $options);

        // Initialize JS, starting with center
        $js = 'smartMap.center = '.json_encode($map['center']).';'.PHP_EOL;

        if (array_key_exists('id', $options)) {
            $id = $options['id'];
            $js .= 'smartMap.id = "'.$options['id'].'";'.PHP_EOL;
        } else {
            $id = 'smartmap-mapcanvas';
        }

        $js .= (array_key_exists('zoom', $options) ? 'smartMap.zoom = '.$options['zoom'].';'.PHP_EOL : '');

        $js .= 'smartMap.init();'.PHP_EOL;

        $template = (array_key_exists('markerInfo', $options) ? $options['markerInfo'] : false);

        // Add map markers
        if ($map['markers']) {
            foreach ($map['markers'] as $i => $m) {
                $js .= $this->_addMarker($i, $m);
                $js .= $this->_addMarkerInfo($i, $markers[$i], $template);
                //if (array_key_exists('click', $options)) {
                //  $js .= 'smartMap.markerClickEvent(marker, '.$options['click'].');'.PHP_EOL;
                //}
            }
        }
        
        craft()->templates->includeJs($js);

        $css = '';
        $css .= (array_key_exists('width', $options)  ? 'width:'.$options['width'].'px;'   : '');
        $css .= (array_key_exists('height', $options) ? 'height:'.$options['height'].'px;' : '');

        return $this->_safeOutput('<div id="'.$id.'" class="smartmap-mapcanvas" style="'.$css.'"">Loading map...</div>');

    }

    // Create <img> of static map
    public function staticImg($markers, $options = array())
    {
        $src = $this->staticImgSource($markers, $options);
        return $this->_safeOutput('<img src="'.$src.'" />');
    }

    // Get source of static map image
    public function staticImgSource($markers, $options = array())
    {

        //$filter = SmartMap_FilterCriteriaModel::populateModel($coords, $options = array());

        // Decipher map info
        $map = craft()->smartMap->markerCoords($markers, $options);

        $width  = (array_key_exists('width', $options)  ? $options['width']  : '200');
        $height = (array_key_exists('height', $options) ? $options['height'] : '200');

        $src  = '//maps.googleapis.com/maps/api/staticmap?sensor=false';
        //$src .= '&key='.craft()->smartMap->mapApiKey;
        $src .= '&center='.$map['center']['lat'].','.$map['center']['lng'];
        $src .= '&zoom='.(array_key_exists('zoom', $options) ? $options['zoom'] : 15);
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

    // Add marker
    private function _addMarker($i, $m)
    {
        $title = $m['title'];
        unset($m['title']);
        return 'smartMap.marker['.$i.'] = smartMap.addMarker('.json_encode($m).', "'.$title.'");'.PHP_EOL;
    }
    // Add marker info bubble (if specified)
    private function _addMarkerInfo($i, $entry, $template)
    {
        if ($template) {
            $html = craft()->templates->render($template, array('i'=>$i));
            $infoWindow = craft()->templates->renderObjectTemplate($html, $entry);
            $infoWindow = json_encode($infoWindow);
            return "smartMap.addInfoWindow($i, $infoWindow)".PHP_EOL;
        } else {
            return '';
        }
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