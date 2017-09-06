// Mute logging if unavailable
if (!window.console) {
    window.console = {
        log: function(obj){}
    };
}

// Log loading
console.log('Loading smartMap object...');

// Smart Map JS object
var smartMap = {
	map: {},
	marker: {},
	bounds: {},
	infoWindow: {},
	enableLogging: false,
	// Log message
	log: function (message) {
		try {
			if (smartMap.enableLogging) {
				console.log(message);
			}
		} catch (err) {
			// do nothing
		}
	},
	// Create & delete items
	createMap: function (mapId, options) {
		var div = document.getElementById(mapId);
		this.map[mapId] = new google.maps.Map(div, options);
		this.bounds[mapId] = new google.maps.LatLngBounds();
		smartMap.log('['+mapId+'] Map rendered.');
		return this.map[mapId];
	},
	createMarker: function (markerName, options) {
		this.marker[markerName] = new google.maps.Marker(options);
		this.bounds[options.mapId].extend(this.marker[markerName].position);
		smartMap.log('['+markerName+'] Marker rendered.');
		return this.marker[markerName];
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
		smartMap.log('['+markerName+'] Info window rendered.');
		return this.infoWindow[markerName];
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
	// Zoom map to fit all markers
	fitBounds: function (mapId) {
		this.map[mapId].fitBounds(this.bounds[mapId]);
		smartMap.log('['+mapId+'] Bounds fit.');
	},
	// Refresh map
	refreshMap: function (mapId) {
		google.maps.event.trigger(this.map[mapId], 'resize');
	},
	// Style map
	styleMap: function (mapId, styles) {
		this.map[mapId].setOptions({styles: styles});
	},
	// Zoom in on a marker
	// SEE DOCS: https://www.doublesecretagency.com/plugins/smart-map/docs/adding-marker-info-bubbles
	zoomOnMarker: function (mapId, markerName, zoom) {
		this.map[mapId].setZoom(zoom);
		this.map[mapId].panTo(this.marker[markerName].position);
	}
}

smartMap.log('smartMap object loaded.');