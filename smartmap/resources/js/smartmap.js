// Smart Map JS object
var smartMapNEW = {
    maps: [],
    //addMap: function (mapModel) {},
    // Final rendering of all maps
    renderMaps: function () {
        for (i in smartMapNEW.maps) {
            var map = smartMapNEW.maps[i];

            var mapEl = document.getElementById(map.id);
        }
        smartMapNEW.map = new google.maps.Map(mapEl, {
            zoom: map.zoom,
            center: smartMapNEW.getLatLng(map.center)
        });
    },
    // Get map options
    getLatLng: function (coords) {
        return new google.maps.LatLng(coords.lat, coords.lng);
    }
}

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
        smartMap.map = new google.maps.Map(smartMap.getEl(smartMap.id), smartMap.getOptions());
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