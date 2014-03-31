// Smart Map JS object
var smartMap = {
    // Default values
    maps: {},
    searchUrl: '',
    _renderedMaps: [],
    //addMap: function (mapModel) {},
    // Draw individual map
    drawMap: function (mapId, map) {
        var mapEl = document.getElementById(mapId);
        smartMap._renderedMaps[mapId] = new google.maps.Map(mapEl, {
            zoom: map.zoom,
            center: smartMap._getLatLng(map.center)
        });
    },
    // Draw all markers for specified map
    drawMarkers: function (mapId, markers) {
        var marker;
        var mapCanvas = smartMap._renderedMaps[mapId];
        for (i in markers) {
            marker = markers[i];
            smartMap._drawMarker(mapCanvas, marker);
        }
    },
    // Draw individual marker
    _drawMarker: function (mapCanvas, marker) {
        var coords = {
            'lat': marker['lat'],
            'lng': marker['lng'],
        }
        return new google.maps.Marker({
            position: smartMap._getLatLng(coords),
            map: mapCanvas,
            title: marker['title']
        });
    },
    // Get map options
    _getLatLng: function (coords) {
        return new google.maps.LatLng(coords.lat, coords.lng);
    },
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

// NEW
// =========================================================== //
// OLD

/*
// Smart Map JS object
var smartMapOLD = {
    // Default values
    map: null,
    marker: {},
    infoWindow: {},
    // Add new map marker
    addMarker: function (coords, title) {
        return new google.maps.Marker({
            position: smartMapOLD.getLatLng(coords),
            map: smartMapOLD.map,
            title: title
        });
    },
    // Add new info window
    addInfoWindow: function (i, content) {
        smartMapOLD.infoWindow[i] = new google.maps.InfoWindow({'content':content});
        google.maps.event.addListener(smartMapOLD.marker[i], 'click', function() {
            for (var key in smartMapOLD.infoWindow) {
                smartMapOLD.infoWindow[key].close();
            }
            smartMapOLD.infoWindow[i].open(smartMapOLD.map, smartMapOLD.marker[i]);
        });
    },
    /*
    // Zoom in on a marker
    zoomOnMarker: function (i, zoom) {
        smartMapOLD.map.setZoom(zoom);
        smartMapOLD.map.panTo(smartMapOLD.marker[i].position);
    },
    / /
    // Set marker click event
    markerClickEvent: function (marker, callback) {
        //google.maps.event.addListener(marker, 'click', callback);
    }
}
*/