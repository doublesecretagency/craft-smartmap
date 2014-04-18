
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

$('.smartmap-field input').on('blur', function () {
    handle = $(this).closest('.smartmap-field').attr('id');
    findCoords(handle);
    return true;
});

function autoDetect() {
    // On blur of each field, try to automatically determine coordinates.
}

function closeNoResults(handle) {
    $('#'+handle+' .smartmap-no-results').hide();
}
function openNoResults(handle) {
    closeMatches(handle);
    $('#'+handle+' .smartmap-no-results').show();
}
function closeMatches(handle) {
    if (typeof handle == 'object') {
        handle = $(handle).parents('.smartmap-field').attr('id');
    }
    $('#'+handle+' .smartmap-matches').hide();
}
function openMatches(handle) {
    closeNoResults(handle);
    var $el = $('#'+handle+' .smartmap-matches');
    $el.show();
    // Close when pressing "esc"
    $(document).on('keydown', function (e) {
        if (e.keyCode === 27) {
            closeMatches(handle);
        }
    });
    // Close when clicking outside
    $(document).on('click', function (e) {
        var ancestors = $(e.target).closest($el).length;
        if (0 === ancestors) {
            closeMatches(handle);
        }
    });
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

    geocoder.geocode({'address': checkAddress}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            for (i in results) {
                deconstructAddress(results[i]);
            }
            openMatches(handle);
        } else {
            if ('ZERO_RESULTS' == status) {
                openNoResults(handle);
            } else {
                console.log('Geocode was not successful for the following reason:', status);
            }
        }
    });

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