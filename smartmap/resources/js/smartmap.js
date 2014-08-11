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
	// Zoom in on a marker
	// SEE DOCS: https://craftpl.us/plugins/smart-map/docs/adding-marker-info-bubbles
	zoomOnMarker: function (mapId, markerName, zoom) {
		this.map[mapId].setZoom(zoom);
		this.map[mapId].panTo(this.marker[markerName].position);
	}
}