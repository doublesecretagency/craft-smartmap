// Smart Map JS object
var smartMap = {
    maps: [],
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
        var googleMap = smartMap._renderedMaps[mapId];
        for (i in markers) {
            marker = markers[i];
            smartMap._drawMarker(googleMap, marker);
        }
    },
    // Draw individual marker
    _drawMarker: function (googleMap, marker) {
        var coords = {
            'lat': marker['lat'],
            'lng': marker['lng'],
        }
        return new google.maps.Marker({
            position: smartMap._getLatLng(coords),
            map: googleMap,
            title: marker['title']
        });
    },
    // Get map options
    _getLatLng: function (coords) {
        return new google.maps.LatLng(coords.lat, coords.lng);
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
        jQuery.post(smartMapOLD.searchUrl, data, function (response) {
            if (typeof response == 'string') {
                alert(response);
            } else if (typeof response == 'object') {
                var marker;
                for (i in response.markers) {
                    marker = response.markers[i];
                    smartMapOLD.addMarker(marker, marker.title);
                }
                smartMapOLD.map.panTo(response.center);
                if (data.zoom) {
                    smartMapOLD.map.setZoom(data.zoom);
                }
            }
        });
    },
    // Initialize map object
    init: function () {
        console.log(smartMapOLD.id);
        console.log(smartMapOLD.getEl(smartMapOLD.id));
        smartMapOLD.map = new google.maps.Map(smartMapOLD.getEl(smartMapOLD.id), smartMapOLD.getOptions());
        console.log('still broken');
    },
    // Get map element
    getEl: function (id) {
        return document.getElementById(id);
    },
    // Get map options
    getOptions: function () {
        return {
            zoom: smartMapOLD.zoom,
            center: smartMapOLD.getLatLng(smartMapOLD.center)
        };
    },
    // Get map options
    getLatLng: function (coords) {
        return new google.maps.LatLng(coords.lat, coords.lng);
    },
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