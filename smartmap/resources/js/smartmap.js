// Smart Map JS object
var smartMap = {
    // Default values
    maps: {},
    searchUrl: '',
    _renderedMaps: [],
    _renderedMarkers: [],
    _renderedInfoWindows: [],
    //addMap: function (mapModel) {},
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