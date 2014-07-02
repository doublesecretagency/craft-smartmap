
// IMPORTANT
// Note that the geocoder may return more than one result.

var geocoder = new google.maps.Geocoder();
var address = {};
var addressOptions = {};
var checkAddress;
var $ul;
var trigger;
var a;
var state;
var zip;
var coords;
var handle;
var dragPinModalMap = {};
var dragPinModalMarker = {};
var dragPinModalWindow = {};

$(document).on('keydown', '.smartmap-field input', function (e) {
    if (9 == e.which) {
        handle = $(this).closest('.smartmap-field').attr('id');
        if (!$('#'+handle+'-lat').val() || !$('#'+handle+'-lng').val()) {
            findCoords(handle);
        }
    }
    return true;
});

$('.smartmap-seach-addresses').click(function() {
    handle = $(this).closest('.smartmap-field').attr('id');
    findCoords(handle);
});
$('.smartmap-drag-pin').click(function() {
    handle = $(this).closest('.smartmap-field').attr('id');
    modalDragPin(handle);
});

function getCoords(handle) {

    var lat = $('#'+handle+'-lat').val();
    var lng = $('#'+handle+'-lng').val();

    if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
        var coords = {
            'lat': lat,
            'lng': lng
        };
    } else if (here) {
        var coords = {
            'lat': here.lat,
            'lng': here.lng
        };
    } else {
        var coords = {
            'lat': 0,
            'lng': 0
        };
    }

    return new google.maps.LatLng(coords.lat, coords.lng);
}

function renderMap(handle, coords) {

    // If map already created
    if (dragPinModalMap[handle]) {
        // Remove marker and center map on new coords
        dragPinModalMarker[handle].setMap(null);
        dragPinModalMap[handle].panTo(coords);
    } else {
        // Create map
        var mapCanvas = document.getElementById('smartmap-'+handle+'-drag-pin-canvas');
        var mapOptions = {
            center: coords,
            zoom: 11,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        dragPinModalMap[handle] = new google.maps.Map(mapCanvas, mapOptions);
    }

    // Set marker for this map
    dragPinModalMarker[handle] = new google.maps.Marker({
        position: coords,
        map: dragPinModalMap[handle],
        draggable: true
    });

    // When marker dropped, re-center map
    google.maps.event.addListener(dragPinModalMarker[handle], 'dragend', function(event) {
        dragPinModalMap[handle].panTo(event.latLng);
    });

    // Return map marker
    return dragPinModalMarker[handle];
}

function modalDragPin(handle) {

    if (dragPinModalWindow[handle]) {
        // If modal already exists, just show it
        dragPinModalWindow[handle].show();
    } else {
        // Setup modal HTML
        var $modal = $('<form id="smartmap-'+handle+'-modal-drag-pin" class="modal elementselectormodal smartmap-modal-drag-pin"/>').appendTo(Garnish.$bod),
            $body = $('<div class="body"/>').appendTo($modal).html('<div id="smartmap-'+handle+'-drag-pin-canvas" style="height:100%"></div>'),
            $footer = $('<footer class="footer"/>').appendTo($modal),
            $buttons = $('<div class="buttons right"/>').appendTo($footer),
            $cancelBtn = $('<div class="btn modal-cancel">'+Craft.t('Cancel')+'</div>').appendTo($buttons);
            $okBtn = $('<input type="submit" class="btn submit modal-submit-drag-pin" value="'+Craft.t('Done')+'"/>').appendTo($buttons);

        // Create modal
        dragPinModalWindow[handle] = new Garnish.Modal($modal);
    }

    var coords = getCoords(handle);
    var marker = renderMap(handle, coords);

    // Set modal close trigger
    $('.modal-cancel').on('click', function() {
        dragPinModalWindow[handle].hide();
    });

    // Set modal submit trigger
    $('.modal-submit-drag-pin').on('click', function() {
        $('#'+handle+'-lat').val(marker.getPosition().lat());
        $('#'+handle+'-lng').val(marker.getPosition().lng());
        dragPinModalWindow[handle].hide();
        return false;
    });

}

function autoDetect() {
    // On blur of each field, try to automatically determine coordinates.
}

function openMatches(handle, address) {

    var data = {'address': address};

    // Load address search modal
    Craft.postActionRequest('smartMap_Modal/addressSearch', data, $.proxy(function(response, textStatus)
    {
        if (textStatus == 'success')
        {
            if (response instanceof Object) {
                Craft.cp.displayError(response.message);
            } else {

                // Setup modal HTML
                var $modal = $('<form id="smartmap-'+handle+'-modal-address-search" class="modal elementselectormodal smartmap-modal-address-search"/>').appendTo(Garnish.$bod),
                    $body = $('<div class="body"/>').appendTo($modal).html(response),
                    $footer = $('<footer class="footer"/>').appendTo($modal),
                    $buttons = $('<div class="buttons right"/>').appendTo($footer),
                    $cancelBtn = $('<div class="btn modal-cancel">'+Craft.t('Cancel')+'</div>').appendTo($buttons);

                // Initialize UI elements
                Craft.initUiElements($body);

                // Create modal
                var modal = new Garnish.Modal($modal, {
                    //onHide: $.proxy(this, 'onActionResponse')
                });

            }

            // Set row select trigger
            $('.smartmap-modal-row td').on('click', function() {
                $(this).parent().children().each(function () {
                    var value    = $(this).text();
                    var subfield = $(this).data('subfield');
                    $('#'+handle+'-'+subfield).val(value);
                });
                modal.hide();
            });

            // Set modal close trigger
            $('.modal-cancel').on('click', function() {
                modal.hide();
            });

        }

    }, this));

}

function findCoords(handle) {
    
    //console.log('Finding Coordinates:', handle);
    
    $ul = $('#'+handle+'-options');
    $ul.html('');

    address[handle] = {
        'street1' : $('#'+handle+'-street1').val(),
        'street2' : $('#'+handle+'-street2').val(),
        'city'    : $('#'+handle+'-city').val(),
        'state'   : $('#'+handle+'-state').val(),
        'zip'     : $('#'+handle+'-zip').val(),
        'country' : $('#'+handle+'-country').val()
    }

    addressOptions[handle] = [];

    //'123 Main Street, Los Angeles, CA 90000, USA';
    checkAddress  = (address[handle].street1 ?      address[handle].street1 : '');
    checkAddress += (address[handle].city    ? ', '+address[handle].city    : '');
    checkAddress += (address[handle].state   ? ', '+address[handle].state   : '');
    checkAddress += (address[handle].zip     ? ', '+address[handle].zip     : '');
    checkAddress += (address[handle].country ? ', '+address[handle].country : '');

    openMatches(handle, checkAddress);

}

function deconstructAddress(address) {

    components = address.address_components;

    var number, street, subcity, city, state, zip, country;

    for (c in components) {

        //console.log(components[c]['types'][0]+':',components[c]['short_name']);

        switch (components[c]['types'][0]) {
            case 'street_number':
                number = components[c]['short_name'];
                break;
            case 'route':
                street = components[c]['short_name'];
                break;
            case 'sublocality':
                subcity = components[c]['short_name'];
                break;
            case 'locality':
                city = components[c]['short_name'];
                break;
            case 'administrative_area_level_1':
                state = components[c]['short_name'];
                break;
            case 'postal_code':
                zip = components[c]['short_name'];
                break;
            case 'country':
                country = components[c]['long_name'];
                break;
        }
    }

    addressOptions[handle][i] = {
        'street1' : ((number ? number : '')+' '+(street ? street : '')).trim(),
        'city'    : (typeof subcity === 'undefined' ? city : subcity),
        'state'   : state,
        'zip'     : zip,
        'country' : country,
        'lat'     : address.geometry.location.lat(),
        'lng'     : address.geometry.location.lng()
    }

    trigger = "loadAddress('"+handle+"',"+i+")";
    formatted_address = address.formatted_address;

    $ul.append('<li><span onmousedown="'+trigger+'">'+formatted_address+'</span></li>');
}

function loadAddress(handle, i) {

    //console.log('loadAddress('+handle+'):',i);

    var address = addressOptions[handle][i];

    $('#'+handle+'-street1').val(address.street1 ? address.street1 : '');
    $('#'+handle+'-city').val(address.city ? address.city : '');
    $('#'+handle+'-state').val(address.state ? address.state : '');
    $('#'+handle+'-zip').val(address.zip ? address.zip : '');
    $('#'+handle+'-country').val(address.country ? address.country : '');
    $('#'+handle+'-lat').val(address.lat ? address.lat : '');
    $('#'+handle+'-lng').val(address.lng ? address.lng : '');

    setTimeout(function () {
        $('#'+handle+' .smartmap-matches').hide();
    }, 250);

    //console.log('Should be hidden',$('#'+handle+' .smartmap-matches'));

}