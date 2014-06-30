
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
    var myMap = new google.maps.Map(el, {
        zoom: 13,
        scrollwheel: false,
        center: center
    });

    // Place JS marker
    if (coordsJs) {
        new google.maps.Marker({
            position: new google.maps.LatLng(js.lat, js.lng),
            map: myMap,
            title: 'JavaScript',
            icon: '//www.googlemapsmarkers.com/v1/J/C6CEF2/'
        });
    }
    // Place PHP marker
    if (coordsPhp) {
        new google.maps.Marker({
            position: new google.maps.LatLng(php.lat, php.lng),
            map: myMap,
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