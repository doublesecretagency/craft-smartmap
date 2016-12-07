
// ============================ //
// IE < 9 console.log patch
if (!window.console) {
    window.console = {
        log: function(obj){}
    };
}
// ============================ //

var visitor;
var handle;
var address = {};
var dragPin = {};

// On load
$(function () {
	addNoHeadingClass();
	// Compatibility with CP Field Links
	if ($('.cpFieldLinks').length > 0) {
		$('div.smartmap-top-right').addClass('cpFieldLinks');
	}
});

// Listen for new blocks
$(document).on('click', '.matrix .btn, .menu ul li a', function () {
	addNoHeadingClass();
});

// Tab "onBlur" patch
$(document).on('keydown', '.smartmap-field input', function (e) {
	if (9 == e.which) {
		handle = $(this).closest('.smartmap-field').attr('id');
		if (!$('#'+handle+'-lat').val() || !$('#'+handle+'-lng').val()) {
			findCoords(handle);
		}
	}
	return true;
});

// Listen for new blocks
$(document).on('click', '.smartmap-search-addresses', function () {
	handle = $(this).closest('.smartmap-field').attr('id');
	console.log('Searching addresses...');
	findCoords(handle);
});
// Listen for new blocks
$(document).on('click', '.smartmap-drag-pin', function () {
	handle = $(this).closest('.smartmap-field').attr('id');
	console.log('Opening drag pin modal...');
	modalDragPin(handle);
});

// Adjust CSS within Matrix blocks
function addNoHeadingClass() {
	var $containers = $('.matrixblock .smartmap-field').closest('.field');
	$containers.not(':has(.heading)').addClass('smartmap-no-heading');
}


function getCoords(handle, existingCoords) {

	var coords;

	// Set default map coordinates
	if (existingCoords) {
		coords = existingCoords;
	} else {
		// Set default map position
		if (visitor) {
			coords = {
				'lat': visitor.lat,
				'lng': visitor.lng
			};
		} else {
			coords = {
				'lat': 0,
				'lng': 0
			};
		}
		// If JS geolocation available, recenter
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function (position) {
				var center = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
				dragPin[handle]['marker'].setPosition(center);
				dragPin[handle]['map'].panTo(center);
			});
		}
	}

	return new google.maps.LatLng(coords.lat, coords.lng);
}

function _renderMap(handle, mapCanvas, coords, zoom) {

	// If map already created
	if (dragPin[handle]['map']) {
		// Remove marker and center map on new coords
		dragPin[handle]['marker'].setMap(null);
		dragPin[handle]['map'].panTo(coords);
	} else {
		var mapOptions = {
			center: coords,
			zoom: zoom,
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
		$('form.smartmap-modal-drag-pin').remove();
		var $modal = $('<form id="smartmap-'+handle+'-modal-drag-pin" class="modal elementselectormodal smartmap-modal-drag-pin"/>').appendTo(Garnish.$bod),
			$body = $('<div class="body"/>').appendTo($modal).html('<div id="smartmap-'+handle+'-drag-pin-canvas" style="height:100%"></div>'),
			$footer = $('<footer class="footer"/>').appendTo($modal),
			$buttons = $('<div class="buttons right"/>').appendTo($footer),
			$cancelBtn = $('<div class="btn modal-cancel">'+Craft.t('Cancel')+'</div>').appendTo($buttons);
			$okBtn = $('<input type="submit" class="btn submit modal-submit-drag-pin" value="'+Craft.t('Done')+'"/>').appendTo($buttons);

		// Create modal
		dragPin[handle]['modal'] = new Garnish.Modal($modal);
	}

	// Get canvas
	var mapCanvas = document.getElementById('smartmap-'+handle+'-drag-pin-canvas');

	// Wait until canvas has height & width
	var refreshIntervalId = setInterval(function () {

		// Once canvas has width
		if ($(mapCanvas).width()) {

			var existingCoords;
			var zoom = 11;

			var lat = $('#'+handle+'-lat').val();
			var lng = $('#'+handle+'-lng').val();

			var defaultData = $('#'+handle+'-drag-pin-data').data();

			if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
				existingCoords = {
					'lat': lat,
					'lng': lng
				};
			} else if (defaultData.default) {
				existingCoords = {
					'lat': defaultData.lat,
					'lng': defaultData.lng
				};
				zoom = defaultData.zoom;
			} else {
				existingCoords = false;
			}

			// Render map
			var coords = getCoords(handle, existingCoords);
			var marker = _renderMap(handle, mapCanvas, coords, zoom);

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

			// Break loop
			clearInterval(refreshIntervalId);
		}

	}, 10);

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
					onShow: function () {
						$('.smartmap-field input').blur();
					}
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