// Initialize
var map;

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
        map = new google.maps.Map(el, {
            zoom: 13,
            scrollwheel: false,
            center: center
        });
    }

    // Place JS marker
    if (map && coordsJs) {
        drawMarker(js, 'blu', 'J', 'JavaScript', 'Position detected via JavaScript (HTML5 geolocation)');
    }
    // Place PHP marker
    if (map && coordsPhp) {
        drawMarker(php, 'orange', 'P', 'PHP', 'Position detected via PHP');
    }

}

// Draw retina marker
function drawMarker(coords, color, letter, title, content) {
    var marker = new google.maps.Marker({
        position: new google.maps.LatLng(coords.lat, coords.lng),
        title: title,
        map: map,
        icon: {
            url: 'https://maps.google.com/mapfiles/kml/paddle/'+color+'-blank.png',
            size: new google.maps.Size(64, 64),
            scaledSize: new google.maps.Size(40, 40),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(20, 40),
            labelOrigin: new google.maps.Point(20, 12)
        },
        label: letter,
        anchorPoint: new google.maps.Point(0, -40)
    });
    var infowindow = new google.maps.InfoWindow({
        content: content
    });
    marker.addListener('click', function() {
        infowindow.open(map, marker);
    });
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
function error(e) {
    var message = 'Unable to perform HTML5 geolocation. ';
    switch (typeof e) {
        case 'object':
            message += e.message;
            break;
        case 'string':
            message += e;
            break;
    }
    console.error(message);
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

// // ========================================================== //
//
// // Haversine Distance Formula
// Number.prototype.toRad = function() {
//     return this * Math.PI / 180;
// }
// function calculateDistance(lat1, lng1, lat2, lng2, units) {
//     switch (units) {
//         case 'km':
//             var R = 6371;
//             break;
//         case 'mi':
//         default:
//             var R = 3959;
//             break;
//     }
//     var x1 = lat2 - lat1;
//     var dLat = x1.toRad();
//     var x2 = lng2 - lng1;
//     var dLng = x2.toRad();
//     var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
//             Math.cos(lat1.toRad()) * Math.cos(lat2.toRad()) *
//             Math.sin(dLng/2) * Math.sin(dLng/2);
//     var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
//     var d = R * c;
//     return d;
// }