// Smart Map JS object
var smartMap = {
    // Default values
    maps: {},
    searchUrl: '',
    _renderedMaps: [],
    _renderedMarkers: [],
    _renderedInfoWindows: [],
    //addMap: function (mapModel) {},

    // DETERMINING COORDS

    // Get closest matches for address
    getMatches: function (address, callback) {
        var output = {
            results : [],
            error   : null
        };
        //'123 Main Street, Los Angeles, CA 90000, USA';
        checkAddress  = (address.street1 ?      address.street1 : '');
        checkAddress += (address.city    ? ', '+address.city    : '');
        checkAddress += (address.state   ? ', '+address.state   : '');
        checkAddress += (address.zip     ? ', '+address.zip     : '');
        checkAddress += (address.country ? ', '+address.country : '');
        // Get results
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({'address': checkAddress}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                for (i in results) {
                    output.results.push(smartMap._cleanupAddress(results[i]));
                }
            } else {
                if ('ZERO_RESULTS' == status) {
                    output.error = 'No results were found.';
                } else {
                    output.error = 'Geocode was not successful for the following reason: '+status;
                }
            }
            callback(output);
        });

    },
    // Clean up address
    _cleanupAddress: function (address) {
        var number, street, subcity, city, state, zip, country;
        var c = address.address_components;
        for (i in c) {
            //console.log(c[i]['types'][0]+':',c[i]['short_name']);
            switch (c[i]['types'][0]) {
                case 'street_number':
                    number  = c[i]['short_name'];
                    break;
                case 'route':
                    street  = c[i]['short_name'];
                    break;
                case 'sublocality':
                    subcity = c[i]['short_name'];
                    break;
                case 'locality':
                    city    = c[i]['short_name'];
                    break;
                case 'administrative_area_level_1':
                    state   = c[i]['short_name'];
                    break;
                case 'postal_code':
                    zip     = c[i]['short_name'];
                    break;
                case 'country':
                    country = c[i]['long_name'];
                    break;
            }
        }
        return {
            'formatted' : address.formatted_address,
            'address'   : {
                'street1' : ((number ? number : '')+' '+(street ? street : '')).trim(),
                'city'    : (typeof subcity === 'undefined' ? city : subcity),
                'state'   : state,
                'zip'     : zip,
                'country' : country
            },
            'coords'    : {
                'lat'     : address.geometry.location.lat(),
                'lng'     : address.geometry.location.lng()
            },
        };
    },

    // DRAWING MAPS & MARKERS

    // Draw individual map
    drawMap: function (mapId, map) {
        var mapEl = document.getElementById(mapId);
        smartMap._renderedMaps[mapId] = new google.maps.Map(mapEl, {
            zoom: map.zoom,
            scrollwheel: map.scrollwheel,
            center: smartMap._getLatLng(map.center)
        });
    },
    // Draw all markers
    drawMarkers: function (mapId, markers) {
        for (var i in markers) {
            smartMap.drawMarker(mapId, i, markers[i]);
        }
    },
    // Draw individual marker
    drawMarker: function (mapId, i, marker) {
        var mapCanvas = smartMap._renderedMaps[mapId];
        var coords = {
            'lat': marker['lat'],
            'lng': marker['lng'],
        }
        smartMap._renderedMarkers[i] = new google.maps.Marker({
            position: smartMap._getLatLng(coords),
            map: mapCanvas,
            title: marker['title']
        });
    },
    // Draw individual marker info
    drawMarkerInfo: function (mapId, i, infoWindowHtml) {
        var mapCanvas = smartMap._renderedMaps[mapId];
        var marker = smartMap._renderedMarkers[i];
        smartMap._renderedInfoWindows[i] = new google.maps.InfoWindow({'content':infoWindowHtml});
        google.maps.event.addListener(marker, 'click', function() {
            for (var key in smartMap._renderedInfoWindows) {
                smartMap._renderedInfoWindows[key].close();
            }
            smartMap._renderedInfoWindows[i].open(mapCanvas, marker);
        });
    },
    // Zoom in on a marker
    zoomOnMarker: function (mapId, i, zoom) {
        smartMap._renderedMaps[mapId].setZoom(zoom);
        smartMap._renderedMaps[mapId].panTo(smartMap._renderedMarkers[i].position);
    },
    // Get map options
    _getLatLng: function (coords) {
        return new google.maps.LatLng(coords.lat, coords.lng);
    },

    // SEARCH

    // Conduct search via AJAX
    search: function (data) {
        if (typeof jQuery != 'function') {
            console.error('Sorry, jQuery is required to use smartMap.search');
            return;
        }
        if (typeof data != 'object') {
            data = {};
        }
        jQuery.post(smartMap.searchUrl, data, function (response) {
            if (typeof response == 'string') {
                alert(response);
            } else if (typeof response == 'object') {
                var mapId = (data.id ? data.id : 'smartmap-mapcanvas-1');
                var mapCanvas = smartMap._renderedMaps[mapId];
                smartMap.drawMarkers(mapId, response.markers);
                mapCanvas.panTo(response.center);
                if (data.zoom) {
                    mapCanvas.setZoom(data.zoom);
                }
            }
        });
    }
}