<?php
namespace Craft;

class SmartMap_VariablesService extends BaseApplicationComponent
{

    // Create dynamic Google Map of locations
    public function googleMap($markers, $options = array())
    {

        //craft()->smartMap->checkApiKey();

        craft()->templates->includeJsFile('http://maps.google.com/maps/api/js?sensor=false');
        craft()->templates->includeJsResource('smartmap/js/smartmap.js');
        craft()->templates->includeCssResource('smartmap/css/smartmap.css');

        $js = '';

        $map = $this->_getMapCoords($markers);

        if (array_key_exists('center', $options)) {
            $center = $options['center'];
        } else {
            $center = $map['center'];
        }
        $js .= 'smartMap.center = '.json_encode($center).';'.PHP_EOL;

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
        if ($markers) {
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
    public function image($coords, $options = array())
    {
        $src = $this->imageSource($coords, $options);
        return $this->_safeOutput('<img src="'.$src.'" />');
    }

    // Get source of static map image
    public function imageSource($coords, $options = array())
    {

        //$filter = SmartMap_FilterCriteriaModel::populateModel($coords, $options = array());

        $src  = 'http://maps.googleapis.com/maps/api/staticmap?sensor=false';
        //$src .= '&key='.$this->mapApiKey;
        $src .= '&center='.$coords['lat'].','.$coords['lng'];
        $src .= '&zoom='.($options['zoom'] ? $options['zoom'] : 15);
        $src .= '&size=150x150';
        $src .= '&visual_refresh=true';
        $src .= '&maptype=roadmap';
        $src .= '&markers=color:green%7C'.$coords['lat'].','.$coords['lng'];

        return $src;

        // MORE COMPLEX EXAMPLE (https://developers.google.com/maps/documentation/staticmaps/index)
        // http://maps.googleapis.com/maps/api/staticmap?center=Brooklyn+Bridge,New+York,NY&zoom=13&size=600x300&maptype=roadmap&markers=color:blue%7Clabel:S%7C40.702147,-74.015794&markers=color:green%7Clabel:G%7C40.711614,-74.012318&markers=color:red%7Ccolor:red%7Clabel:C%7C40.718217,-73.998284&sensor=false

        // SHOULD ALSO BUILD IN FUNCTIONALITY FOR STREET VIEW MAPS
        // http://maps.googleapis.com/maps/api/streetview?size=200x200&location=40.720032,-73.988354&heading=235&sensor=false

    }

    // Decipher map center & markers based on locations
    private function _getMapCoords($locations)
    {

        // Initialize variables
        $error   = false;
        $markers = array();
        $allLats = array();
        $allLngs = array();
        $handles = array();

        // If no locations are specified
        if (empty($locations)) {
            $error = "No locations specified";
        } else {
            // Find all Smart Map Address field handles
            foreach (craft()->fields->getAllFields() as $field) {
                if ($field->type == 'SmartMap_Address') {
                    $handles[] = $field->handle;
                }
            }
            // Loop through locations
            foreach ($locations as $loc) {
                if (is_object($loc)) {
                // If location is an object
                    if (!empty($handles)) {
                        foreach ($handles as $handle) {
                            $address = $loc->{$handle};
                            if (!empty($address)) {
                                $lat = $address['lat'];
                                $lng = $address['lng'];
                                $markers[] = array(
                                    'lat'   => (float) $lat,
                                    'lng'   => (float) $lng,
                                    'title' => $loc->title
                                );
                                $allLats[] = $lat;
                                $allLngs[] = $lng;
                            }
                        }
                    }
                } else if (is_array($loc)) {
                // Else, if location is an array
                    if (!craft()->smartMap->_isAssoc($loc) && count($loc) == 2) {
                        $lat = $loc[0];
                        $lng = $loc[1];
                        $title = '';
                    } else {
                        $lat = craft()->smartMap->_findKeyInArray($loc, array('latitude','lat'));
                        $lng = craft()->smartMap->_findKeyInArray($loc, array('longitude','lng','lon','long'));
                        $title = (array_key_exists('title',$loc) ? $loc['title'] : '');
                    }
                    $markers[] = array(
                        'lat'   => $lat,
                        'lng'   => $lng,
                        'title' => $title
                    );
                    $allLats[] = $lat;
                    $allLngs[] = $lng;
                }
            }
        }

        // If error was triggered
        if ($error) {
            $center = array(
                'lat' => 0,
                'lng' => 0
            );
            $markers[] = array(
                'lat'   => 0,
                'lng'   => 0,
                'title' => $error
            );
        } else {
            // Calculate center of map
            $centerLat = (min($allLats) + max($allLats)) / 2;
            $centerLng = (min($allLngs) + max($allLngs)) / 2;
            $center = array(
                'lat' => round($centerLat, 6),
                'lng' => round($centerLng, 6)
            );
        }

        // Return center point and all markers
        return array(
            'center'  => $center,
            'markers' => $markers,
        );
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