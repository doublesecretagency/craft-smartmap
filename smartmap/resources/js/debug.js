
// Draw map with markers
function drawMap() {

	// Get current JS coords
	var js = {
		'lat': $('td#js-latitude').text().trim(),
		'lng': $('td#js-longitude').text().trim()
	}

	// Get current PHP coords
	var php = {
		'lat': $('td#php-latitude').text().trim(),
		'lng': $('td#php-longitude').text().trim()
	}

	// Set whether coordinates exist
	var coordsJs = Boolean(js.lat && js.lng);
	var coordsPhp = Boolean(php.lat && php.lng);

	// Determine map center
	var center;
	if (coordsJs) {
		center = new google.maps.LatLng(js.lat, js.lng);
	} else if (coordsPhp) {
		center = new google.maps.LatLng(php.lat, php.lng);
	} else {
		center = new google.maps.LatLng(0, 0);
		alert('Unable to determine map center.');
	}

	// Draw map
	var el = document.getElementById('where-am-i');
	if (el) {
		var visitorMap = new google.maps.Map(el, {
			zoom: 13,
			scrollwheel: false,
			center: center
		});
	}

	// Place JS marker
	if (coordsJs) {
		new google.maps.Marker({
			position: new google.maps.LatLng(js.lat, js.lng),
			map: visitorMap,
			title: 'JavaScript',
			icon: '//www.googlemapsmarkers.com/v1/J/C6CEF2/'
		});
	}
	// Place PHP marker
	if (coordsPhp) {
		new google.maps.Marker({
			position: new google.maps.LatLng(php.lat, php.lng),
			map: visitorMap,
			title: 'PHP',
			icon: '//www.googlemapsmarkers.com/v1/P/FF695F/'
		});
	}

}

// ========================================================== //

// Handle successful geolocation attempt
var hasBeenRun = false;
function success(position) {
	if (hasBeenRun) {return;} // FF bug, loops back through
	hasBeenRun = true;
	$('td#js-latitude').text(position.coords.latitude.toFixed(6));
	$('td#js-longitude').text(position.coords.longitude.toFixed(6));
	drawMap();
}
// Handle failed geolocation attempt
function error(msg) {
	var message = (typeof msg == 'string' ? msg : 'HTML5 geolocation failed.');
	alert(message);
}
// Trigger geolocation detection
if (navigator.geolocation) {
	navigator.geolocation.getCurrentPosition(success, error);
} else {
	error('This browser does not support HTML5 geolocation.');
}

$(function () {
	drawMap();
});

// ========================================================== //

// Haversine Distance Formula
Number.prototype.toRad = function() {
	return this * Math.PI / 180;
}
function calculateDistance(lat1, lng1, lat2, lng2, units) {
	switch (units) {
		case 'km':
			var R = 6371;
			break;
		case 'mi':
		default:
			var R = 3959;
			break;
	}
	var x1 = lat2 - lat1;
	var dLat = x1.toRad();
	var x2 = lng2 - lng1;
	var dLng = x2.toRad();
	var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
			Math.cos(lat1.toRad()) * Math.cos(lat2.toRad()) *
			Math.sin(dLng/2) * Math.sin(dLng/2);
	var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
	var d = R * c;
	return d;
}