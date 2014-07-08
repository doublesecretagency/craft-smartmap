
var handle;
var address = {};
var dragPin = {};

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
    if (dragPin[handle]['map']) {
        // Remove marker and center map on new coords
        dragPin[handle]['marker'].setMap(null);
        dragPin[handle]['map'].panTo(coords);
    } else {
        // Create map
        var mapCanvas = document.getElementById('smartmap-'+handle+'-drag-pin-canvas');
        var mapOptions = {
            center: coords,
            zoom: 11,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        dragPin[handle]['map'] = new google.maps.Map(mapCanvas, mapOptions);
    }

    // Set marker for this map
    dragPin[handle]['marker'] = new google.maps.Marker({
        position: coords,
        map: dragPin[handle]['map'],
        draggable: true
    });

    // When marker dropped, re-center map
    google.maps.event.addListener(dragPin[handle]['marker'], 'dragend', function(event) {
        dragPin[handle]['map'].panTo(event.latLng);
    });

    // Return map marker
    return dragPin[handle]['marker'];
}

function modalDragPin(handle) {

    if (handle in dragPin) {
        // If modal already exists, just show it
        dragPin[handle]['modal'].show();
    } else {
        dragPin[handle] = {};
        // Setup modal HTML
        var $modal = $('<form id="smartmap-'+handle+'-modal-drag-pin" class="modal elementselectormodal smartmap-modal-drag-pin"/>').appendTo(Garnish.$bod),
            $body = $('<div class="body"/>').appendTo($modal).html('<div id="smartmap-'+handle+'-drag-pin-canvas" style="height:100%"></div>'),
            $footer = $('<footer class="footer"/>').appendTo($modal),
            $buttons = $('<div class="buttons right"/>').appendTo($footer),
            $cancelBtn = $('<div class="btn modal-cancel">'+Craft.t('Cancel')+'</div>').appendTo($buttons);
            $okBtn = $('<input type="submit" class="btn submit modal-submit-drag-pin" value="'+Craft.t('Done')+'"/>').appendTo($buttons);

        // Create modal
        dragPin[handle]['modal'] = new Garnish.Modal($modal);
    }

    var coords = getCoords(handle);
    var marker = renderMap(handle, coords);

    // Set modal close trigger
    $('.modal-cancel').on('click', function() {
        dragPin[handle]['modal'].hide();
    });

    // Set modal submit trigger
    $('.modal-submit-drag-pin').on('click', function() {
        $('#'+handle+'-lat').val(marker.getPosition().lat());
        $('#'+handle+'-lng').val(marker.getPosition().lng());
        dragPin[handle]['modal'].hide();
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

    address[handle] = {
        'street1' : $('#'+handle+'-street1').val(),
        'street2' : $('#'+handle+'-street2').val(),
        'city'    : $('#'+handle+'-city').val(),
        'state'   : $('#'+handle+'-state').val(),
        'zip'     : $('#'+handle+'-zip').val(),
        'country' : $('#'+handle+'-country').val()
    }

    //'123 Main Street, Los Angeles, CA 90000, USA';
    var checkAddress;
    checkAddress  = (address[handle].street1 ?      address[handle].street1 : '');
    checkAddress += (address[handle].city    ? ', '+address[handle].city    : '');
    checkAddress += (address[handle].state   ? ', '+address[handle].state   : '');
    checkAddress += (address[handle].zip     ? ', '+address[handle].zip     : '');
    checkAddress += (address[handle].country ? ', '+address[handle].country : '');

    openMatches(handle, checkAddress);

}