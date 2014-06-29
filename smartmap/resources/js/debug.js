
// Draw map with markers
function drawMap() {

    // Get current PHP coords
    var php = {
        'lat': $('td#php-latitude').text(),
        'lng': $('td#php-longitude').text()
    }

    // Get current JS coords
    var js = {
        'lat': $('td#js-latitude').text(),
        'lng': $('td#js-longitude').text()
    }

    // Average coordinates
    var avgLat = avgCoords(php.lat, js.lat);
    var avgLng = avgCoords(php.lng, js.lng);

    // Ensure avg. coords could be calculated
    if (!avgLat || !avgLng) {
        alert('Unable to determine map center.');
        return;
    }

    // Draw map
    var el = document.getElementById('where-am-i');
    var myMap = new google.maps.Map(el, {
        zoom: 13,
        scrollwheel: false,
        center: new google.maps.LatLng(avgLat, avgLng)
    });

    // Place PHP marker
    if (php.lat && php.lng) {
        new google.maps.Marker({
            position: new google.maps.LatLng(php.lat, php.lng),
            map: myMap,
            title: 'PHP',
            icon: '//www.googlemapsmarkers.com/v1/P/FF695F/'
        });
    }

    // Place JS marker
    if (js.lat && js.lng) {
        new google.maps.Marker({
            position: new google.maps.LatLng(js.lat, js.lng),
            map: myMap,
            title: 'JavaScript',
            icon: '//www.googlemapsmarkers.com/v1/J/C6CEF2/'
        });
    }
    
}

// Calculate average of coordinates
function avgCoords(php, js) {
    php = parseFloat(php);
    js  = parseFloat(js);
    if (isNaN(php) && isNaN(js)) {
        return false;
    } else if (isNaN(php)) {
        return js;
    } else if (isNaN(js)) {
        return php;
    } else {
        return (php + js) / 2;
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