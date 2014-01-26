// Smart Map JS object
var smartMap = {
    // Default values
    map: null,
    marker: {},
    infoWindow: {},
    id: 'smartmap-mapcanvas',
    zoom: 8,
    center: {
        lat: 90,
        lng: 0
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
            center: new google.maps.LatLng(smartMap.center.lat, smartMap.center.lng)
        };
    },
    // Add new map marker
    addMarker: function (coords, title) {
        return new google.maps.Marker({
            position: coords,
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
    // Zoom in on a marker
    zoomOnMarker: function (i, zoom) {
        smartMap.map.setZoom(zoom);
        smartMap.map.panTo(smartMap.marker[i].position);
    },
    // Set marker click event
    markerClickEvent: function (marker, callback) {
        //google.maps.event.addListener(marker, 'click', callback);
    }
}