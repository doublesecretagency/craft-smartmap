// Smart Map JS object
var smartMap = {
    map: {},
    marker: {},
    infoWindow: {},
    // Create & delete items
    createMap: function (mapId, options) {
        var div = document.getElementById(mapId);
        this.map[mapId] = new google.maps.Map(div, options);
    },
    createMarker: function (markerName, options) {
        this.marker[markerName] = new google.maps.Marker(options);
    },
    deleteMarker: function (markerName) {
        this.marker[markerName].setMap(null);
    },
    createInfoWindow: function (markerName, options) {
        var marker = this.marker[markerName];
        var map = marker.getMap();
        this.infoWindow[markerName] = new google.maps.InfoWindow(options);
        google.maps.event.addListener(marker, 'click', function() {
            for (var key in smartMap.infoWindow) {
                smartMap.infoWindow[key].close();
            }
            smartMap.infoWindow[markerName].open(map, marker);
        });
    },
    // List items
    listMaps: function () {
        return Object.keys(this.map);
    },
    listMarkers: function () {
        return Object.keys(this.marker);
    },
    listInfoWindows: function () {
        return Object.keys(this.infoWindow);
    },
    // Get coordinates object
    coords: function (lat, lng) {
        return new google.maps.LatLng(lat, lng);
    },
    
    
    // Search via AJAX
    // Should this entire thing be removed?
    // It could be in the docs as only a demo.
    // No need for it to be in the core code.
    search: function (url, data, callback) {
        /*
        if (typeof jQuery != 'function') {
            console.error('Sorry, jQuery is required to use smartMap.search');
            return;
        }
        if (typeof data != 'object') {
            data = {};
        }
        */
        jQuery.post(url, data, callback);
        /*
        // This part is actually the useful example!
        jQuery.post(url, data, function (response) {
            if (typeof response == 'string') {
                alert(response);
            } else if (typeof response == 'object') {
                var mapId = (data.id ? data.id : 'smartmap-mapcanvas-1');
                for (var key in response.markers) {
                    var marker = response.markers[key];
                    var coords = smartMap.coords(marker.lat, marker.lng);
                    smartMap.createMarker(marker.name, marker.options);
                }
                smartMap.map[mapId].panTo(response.center);
                if (data.zoom) {
                    smartMap.map[mapId].setZoom(data.zoom);
                }
            }
        });
        */
    }
}

// ============================================================================ //

    /*
    // Zoom in on a marker
    // SEE DOCS: https://github.com/lindseydiloreto/craft-smartmap/wiki/Adding-marker-info-bubbles
    zoomOnMarker: function (mapId, i, zoom) {
        smartMap._renderedMaps[mapId].setZoom(zoom);
        smartMap._renderedMaps[mapId].panTo(smartMap._renderedMarkers[i].position);
    },
    */

// ============================================================================ //

// EXAMPLES

/*
// Create single map
var mapOptions = {
     // "center" and "zoom" are required
    center: smartMap.coords(33, -117.5),
    zoom: 6
};
smartMap.createMap('smartmap-mapcanvas-1', mapOptions);

// Create single marker
var markerOptions = {
     // "position" and "map" are required
    position: smartMap.coords(34, -118),
    map: smartMap.map['smartmap-mapcanvas-1']
};
smartMap.createMarker('smartmap-mapcanvas-1.16.fieldHandle', markerOptions);

// Create single marker
var markerOptions = {
     // "position" and "map" are required
    position: smartMap.coords(31, -117),
    map: smartMap.map['smartmap-mapcanvas-1']
};
smartMap.createMarker('smartmap-mapcanvas-1.17.fieldHandle', markerOptions);

// Create single info window
var infoWindowOptions = {
     // "content" is required
    content: '<h2>Dude!</h2>'
};
smartMap.createInfoWindow('smartmap-mapcanvas-1.16.fieldHandle', infoWindowOptions);

// Create single info window
var infoWindowOptions = {
     // "content" is required
    content: '<h2>Sweet!</h2>'
};
smartMap.createInfoWindow('smartmap-mapcanvas-1.17.fieldHandle', infoWindowOptions);
*/

//console.log(smartMap.listMarkers());

//smartMap.map['smartmap-mapcanvas-1'].setOptions({styles: styles});
//smartMap.marker['smartmap-mapcanvas-1.17.fieldHandle'].setDraggable(true);