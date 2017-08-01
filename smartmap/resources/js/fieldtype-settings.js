
// Define field layout object
var SmartMap_FieldLayout = function ($fieldtype) {
	if (!$fieldtype.hasClass('blueprint-activated')) {
		var parent = this;
		// Initialize layout
		this.layout = {};
		// Define properties
		this.$fieldtype   = $fieldtype;
		this.$layoutInput = $fieldtype.find('.smartmap-fieldtype-layout-values input');
		this.$bpPanel     = $fieldtype.find('.blueprint-panel');
		// Add events
		var triggerInputs = '.layout-table-enable input, .layout-table-width input';
		$layoutTable = $fieldtype.find('.smartmap-fieldtype-layout-table');
		$layoutTable.on('change', triggerInputs, function () {parent.blueprint();});
		// Initialize sortable rows
		$fieldtype.find('.layout-table-rows').each(function () {
			new Sortable(this, {
				handle: '.move',
				animation: 150,
				ghostClass: 'sortable-ghost',
				onUpdate: function () {parent.blueprint();}
			});
		});
		// Initialize blueprint
		this.blueprint();
		$fieldtype.addClass('blueprint-activated');
	}
};

// Render blueprint of field layout
SmartMap_FieldLayout.prototype.blueprint = function() {
	var parent = this;
	// Clear layout
	this.layout = {};
	// Loop through subfields
	this.$fieldtype.find('.layout-table-subfield').each(function () {
		var subfield = $(this).data('subfield');
		parent.layout[subfield] = {};
		parent._subfieldWidth(subfield, $(this));
		parent._subfieldEnabled(subfield, $(this));
		parent._moveBlueprintRow(subfield);
	});
	// Set layout data
	this.$layoutInput.val(JSON.stringify(this.layout));
	// Append clear to bluprint panel
	this.$bpPanel.find('.clear').appendTo(this.$bpPanel);
};

// Check width of subfield
SmartMap_FieldLayout.prototype._subfieldWidth = function(subfield, $el) {
	this.$ltWidth = this.$fieldtype.find('tr[data-subfield="' + subfield + '"] .layout-table-width input');
	this.$bpField = this.$fieldtype.find('.blueprint-' + subfield);
	var width = this.$ltWidth.val();
	if (!width || isNaN(width) || (width > 100)) {
		width = 100;
	} else if (width < 10) {
		width = 10;
	}
	this.$ltWidth.val(width);
	this.$bpField.css({
		'float': 'left',
		'width': (width - 1) + '%',
		'margin-left': '1%'
	});
	this.layout[subfield]['width'] = parseInt(width);
};

// Check if subfield is enabled
SmartMap_FieldLayout.prototype._subfieldEnabled = function(subfield, $el) {
	this.$ltRow = this.$fieldtype.find('tr[data-subfield="' + subfield + '"]');
	this.$bpField = this.$fieldtype.find('.blueprint-' + subfield);
	var checked  = $el.find('.layout-table-enable input').is(':checked');
	if (checked) {
		this.$ltRow.removeClass('disabled');
		this.$bpField.show();
	} else {
		this.$ltRow.addClass('disabled');
		this.$bpField.hide();
	}
	this.layout[subfield]['enable'] = (checked ? 1 : 0);
};

// Move a row in the blueprint
SmartMap_FieldLayout.prototype._moveBlueprintRow = function(subfield) {
	var $blueprintSubfield = this.$fieldtype.find('.blueprint-' + subfield);
	this.$bpPanel.append($blueprintSubfield);
};

// =================================================================================================== //
// =================================================================================================== //

var $fieldSetting, mapCurrent;
var g;

$(function () {
	$fieldSetting = {
		'lat'  : $('#types-SmartMap_Address-latitude'),
		'lng'  : $('#types-SmartMap_Address-longitude'),
		'zoom' : $('#types-SmartMap_Address-zoom')
	}
	mapCurrent = {
		'lat'  : parseInt($fieldSetting.lat.val())  || 0,
		'lng'  : parseInt($fieldSetting.lng.val())  || 0,
		'zoom' : parseInt($fieldSetting.zoom.val()) || 11
	}
	g = loadMap();
	// Hide/show map defaults when checkbox is checked
	$('#types-SmartMap_Address-dragPinDefault').on('change', function () {
		var $dragPinDefaults = $('#types-SmartMap_Address-dragpin-defaults');
		if ($(this).is(':checked')) {
			$dragPinDefaults.slideDown();
		} else {
			$dragPinDefaults.slideUp();
		}
		google.maps.event.trigger(g.map,'resize');
		var center = new google.maps.LatLng(
			parseInt($fieldSetting.lat.val()),
			parseInt($fieldSetting.lng.val())
		);
		g.map.panTo(center);
	});
	// Refocus map when values are adjusted
	$('.default-value input').on('keypress', function (event) {
		if (event.which == 13) {
			var center = new google.maps.LatLng(
				parseInt($fieldSetting.lat.val()),
				parseInt($fieldSetting.lng.val())
			);
			g.map.panTo(center);
			g.marker.setPosition(center);
			g.map.setZoom(parseInt($fieldSetting.zoom.val()));
			event.preventDefault();
		}
	});

	// When page loads, initialize each field layout
	$('.smartmap-fieldtype').each(function () {
		new SmartMap_FieldLayout($(this));
	});

	// When type select inputs change
	$('.matrix-configurator').on('change', 'select[id$="type"]', function () {
		if ('SmartMap_Address' == $(this).val()) {
			$('.smartmap-fieldtype').each(function () {
				new SmartMap_FieldLayout($(this));
			});
		}
	});
});

function getCoords() {

	var coords;
	var lat = mapCurrent.lat;
	var lng = mapCurrent.lng;

	// Set default map coordinates
	if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
		coords = {
			'lat': lat,
			'lng': lng
		};
	} else {
		// Set default map position
		coords = {
			'lat': 0,
			'lng': 0
		};
		// If SSL and JS geolocation available, recenter
		if (('https:' == window.location.protocol) && navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(function (position) {
				var geoLat = position.coords.latitude;
				var geoLng = position.coords.longitude;
				coords = {
					'lat': geoLat,
					'lng': geoLng
				};
				var center = new google.maps.LatLng(geoLat, geoLng);
				$fieldSetting.lat.val(geoLat);
				$fieldSetting.lng.val(geoLng);
				g.marker.setPosition(center);
			});
		}
	}

	return coords;
}

function loadMap() {

	var coords = getCoords();

	// Set map options
	var mapOptions = {
		zoom: mapCurrent.zoom,
		center: coords,
		scrollwheel: false,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};

	// Draw blank map
	var mapCanvas = $('#types-SmartMap_Address-dragpin-map');
	var map = new google.maps.Map(mapCanvas, mapOptions);

	// Set marker for this map
	var marker = new google.maps.Marker({
		position: coords,
		map: map,
		draggable: true
	});

	// When marker is dropped
	google.maps.event.addListener(marker, "dragend", function(event) {
		map.panTo(event.latLng);
		$fieldSetting.lat.val(event.latLng.lat());
		$fieldSetting.lng.val(event.latLng.lng());
	});

	// When map is zoomed
	google.maps.event.addListener(map, "zoom_changed", function(event) {
		$fieldSetting.zoom.val(map.getZoom());
	});

	return {
		'map': map,
		'marker': marker
	};

}
