
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
	$('#'+handle+' .smartmap-matches').show();
	return true;
});

function autoDetect() {
	// On blur of each field, try to automatically determine coordinates.
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
		'zip'     : $('#'+handle+'-zip').val()
	}

	addressOptions[handle] = [];

	//'123 Main Street, Los Angeles, CA 90000, USA';
	checkAddress  = (address[handle].street1 ?      address[handle].street1 : '');
	checkAddress += (address[handle].city    ? ', '+address[handle].city    : '');
	checkAddress += (address[handle].state   ? ', '+address[handle].state   : '');
	checkAddress += (address[handle].zip     ? ', '+address[handle].zip     : '');

	geocoder.geocode({'address': checkAddress}, function(results, status) {
		if (status == google.maps.GeocoderStatus.OK) {
			$('#'+handle+' .smartmap-matches-intro').html('Found the following matches: (Click to auto-fill)');
			for (i in results) {
				deconstructAddress(results[i]);
			}
		} else {
			if ('ZERO_RESULTS' == status) {
				$('#'+handle+' .smartmap-matches-intro').html('No matches found.');
			} else {
				console.log('Geocode was not successful for the following reason:', status);
			}
		}
	});

}

function deconstructAddress(address) {

	components = address.address_components;

	for (c in components) {

		console.log(components[c]['types'][0]+':',components[c]['short_name']);

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
				country = components[c]['short_name'];
				break;
		}
	}

	addressOptions[handle][i] = {
		'street1' : number+' '+street,
		'city'    : (typeof subcity === 'undefined' ? city : subcity),
		'state'   : state,
		'zip'     : zip,
		'lat'     : address.geometry.location.lat(),
		'lng'     : address.geometry.location.lng()
	}

	trigger = "loadAddress('"+handle+"',"+i+")";
	formatted_address = address.formatted_address;

	$ul.append('<li><span onmousedown="'+trigger+'">'+formatted_address+'</span></li>');
}

function loadAddress(handle, i) {

	//console.log('loadAddress('+handle+'):',i);

	var selected = addressOptions[handle][i];

	$('#'+handle+'-street1').val(selected.street1);
	$('#'+handle+'-city').val(selected.city);
	$('#'+handle+'-state').val(selected.state);
	$('#'+handle+'-zip').val(selected.zip);
	$('#'+handle+'-lat').val(selected.lat);
	$('#'+handle+'-lng').val(selected.lng);

	//console.log('Got it!');
	
	setTimeout(function () {
		$('#'+handle+' .smartmap-matches').hide();
	}, 100);

	//console.log('Should be hidden',$('#'+handle+' .smartmap-matches'));

}