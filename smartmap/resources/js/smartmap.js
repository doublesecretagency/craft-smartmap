// Smart Map JS object
var smartMapNEW = {
    maps: [],
    _renderedMaps: [],
    //addMap: function (mapModel) {},
    // Final rendering of all maps
    renderMaps: function () {
        var map;
        for (mapId in smartMapNEW.maps) {
            map = smartMapNEW.maps[mapId];
            smartMapNEW._drawMap(mapId, map);
            smartMapNEW._drawMarkers(mapId, map);
        }
    },
    // Draw individual map
    _drawMap: function (mapId, map) {
        var mapEl = document.getElementById('smartmap-mapcanvas-'+mapId);
        smartMapNEW._renderedMaps[mapId] = new google.maps.Map(mapEl, {
            zoom: map.zoom,
            center: smartMapNEW._getLatLng(map.center)
        });
    },
    // Draw all markers for specified map
    _drawMarkers: function (mapId, map) {
        var marker;
        var googleMap = smartMapNEW._renderedMaps[mapId];
        for (i in map['markers']) {
            marker = map['markers'][i];
            smartMapNEW._drawMarker(googleMap, marker);
        }
    },
    // Draw individual marker
    _drawMarker: function (googleMap, marker) {
        var coords = {
            'lat': marker['lat'],
            'lng': marker['lng'],
        }
        return new google.maps.Marker({
            position: smartMapNEW._getLatLng(coords),
            map: googleMap,
            title: marker['title']
        });
    },
    // Get map options
    _getLatLng: function (coords) {
        return new google.maps.LatLng(coords.lat, coords.lng);
    },
    // 
    _: function () {
        return;
    }
}

// NEW
// =========================================================== //
// OLD

// Smart Map JS object
var smartMap = {
    // Default values
    map: null,
    marker: {},
    infoWindow: {},
    id: 'smartmap-mapcanvas',
    searchUrl: '',
    zoom: 8,
    center: {
        // Point Nemo
        lat: -48.876667,
        lng: -123.393333
    },
    // Conduct search via AJAX
    search: function (data) {
        jQuery.post(smartMap.searchUrl, data, function (response) {
            if (typeof response == 'string') {
                alert(response);
            } else if (typeof response == 'object') {
                var marker;
                for (i in response.markers) {
                    marker = response.markers[i];
                    smartMap.addMarker(marker, marker.title);
                }
                smartMap.map.panTo(response.center);
                if (data.zoom) {
                    smartMap.map.setZoom(data.zoom);
                }
            }
        });
    },
    // Initialize map object
    init: function () {
        console.log(smartMap.id);
        console.log(smartMap.getEl(smartMap.id));
        smartMap.map = new google.maps.Map(smartMap.getEl(smartMap.id), smartMap.getOptions());
        console.log('still broken');
    },
    // Get map element
    getEl: function (id) {
        return document.getElementById(id);
    },
    // Get map options
    getOptions: function () {
        return {
            zoom: smartMap.zoom,
            center: smartMap.getLatLng(smartMap.center)
        };
    },
    // Get map options
    getLatLng: function (coords) {
        return new google.maps.LatLng(coords.lat, coords.lng);
    },
    // Add new map marker
    addMarker: function (coords, title) {
        return new google.maps.Marker({
            position: smartMap.getLatLng(coords),
            map: smartMap.map,
            title: title
        });
    },
    // Add new info window
    addInfoWindow: function (i, content) {
        smartMap.infoWindow[i] = new google.maps.InfoWindow({'content':content});
        google.maps.event.addListener(smartMap.marker[i], 'click', function() {
            for (var key in smartMap.infoWindow) {
                smartMap.infoWindow[key].close();
            }
            smartMap.infoWindow[i].open(smartMap.map, smartMap.marker[i]);
        });
    },
    /*
    // Zoom in on a marker
    zoomOnMarker: function (i, zoom) {
        smartMap.map.setZoom(zoom);
        smartMap.map.panTo(smartMap.marker[i].position);
    },
    */
    // Set marker click event
    markerClickEvent: function (marker, callback) {
        //google.maps.event.addListener(marker, 'click', callback);
    }
}