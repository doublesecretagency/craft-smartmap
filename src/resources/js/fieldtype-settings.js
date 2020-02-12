window.SmartMap = {};

SmartMap.Address = Garnish.Base.extend({
    $container: null,
    $layoutTable: null,
    $blueprintPanel: null,
    $values: null,
    layout: {},

    g: null,
    $dragPinDefaults: null,
    $fieldSetting: null,
    mapCurrent: null,

    init: function(container) {
        var parent = this;
        this.$container = $(container);
        this.$layoutTable = this.$container.find('.smartmap-layout-table');
        this.$blueprintPanel = this.$container.find('.blueprint-panel');
        this.$dragPinDefaults = this.$container.find('.smartmap-dragpin-defaults');
        this.$values = this.$container.find('input.smartmap-layout-values');

        // Update blueprint
        this._updateBlueprint();

        // Get field settings
        this.$fieldSetting = {
            'lat'  : this.$dragPinDefaults.find('.lat input'),
            'lng'  : this.$dragPinDefaults.find('.lng input'),
            'zoom' : this.$dragPinDefaults.find('.zoom input')
        };
        this.mapCurrent = {
            'lat'  : parseFloat(this.$fieldSetting.lat.val()) || 0,
            'lng'  : parseFloat(this.$fieldSetting.lng.val()) || 0,
            'zoom' : parseInt(this.$fieldSetting.zoom.val())  || 11
        };

        // Get map & marker objects
        this.g = this.loadMap();

        // Get dragpin checkbox & inputs
        var $checkbox = this.$container.find('.dragpin-checkbox input[type="checkbox"]');
        var $dragpinInputs = this.$dragPinDefaults.find('input');

        // Hide/show map defaults when checkbox is checked
        $checkbox.on('change', function () {
            if ($(this).is(':checked')) {
                parent.$dragPinDefaults.slideDown();
            } else {
                parent.$dragPinDefaults.slideUp();
            }
            google.maps.event.trigger(parent.g.map,'resize');
            var center = new google.maps.LatLng(
                parseFloat(parent.$fieldSetting.lat.val()),
                parseFloat(parent.$fieldSetting.lng.val())
            );
            parent.g.map.panTo(center);
        });

        // Refocus map when values are adjusted
        $dragpinInputs.on('keypress', function (event) {
            if (13 === event.which) {
                event.preventDefault();
                var center = new google.maps.LatLng(
                    parseFloat(parent.$fieldSetting.lat.val()),
                    parseFloat(parent.$fieldSetting.lng.val())
                );
                parent.g.map.panTo(center);
                parent.g.marker.setPosition(center);
                parent.g.map.setZoom(parseInt(parent.$fieldSetting.zoom.val()));
            }
        });

        // Initialize sortable rows
        this.$layoutTable.find('.layout-table-rows').each(function () {
            new Sortable(this, {
                handle: '.move',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onUpdate: function () {
                    // Update blueprint
                    parent._updateBlueprint();
                }
            });
        });

        // Update blueprint when subfields are adjusted
        var triggerInputs = '.layout-table-enable input, .layout-table-width input';
        this.$layoutTable.on('change', triggerInputs, function () {
            parent._updateBlueprint();
        });

    },

    // Load map
    loadMap: function () {
        var parent = this;
        var coords = this.getCoords();

        // Set map options
        var mapOptions = {
            zoom: this.mapCurrent.zoom,
            center: coords,
            scrollwheel: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        // Draw blank map
        var $mapCanvas = this.$dragPinDefaults.find('.dragpin-map');
        var map = new google.maps.Map($mapCanvas[0], mapOptions);

        // Set marker for this map
        var marker = new google.maps.Marker({
            position: coords,
            map: map,
            draggable: true
        });

        // When marker is dropped
        google.maps.event.addListener(marker, 'dragend', function(event) {
            map.panTo(event.latLng);
            parent.$fieldSetting.lat.val(event.latLng.lat());
            parent.$fieldSetting.lng.val(event.latLng.lng());
        });

        // When map is zoomed
        google.maps.event.addListener(map, 'zoom_changed', function(event) {
            parent.$fieldSetting.zoom.val(map.getZoom());
        });

        return {
            'map': map,
            'marker': marker
        };

    },

    // Get coordinates
    getCoords: function () {
        var parent = this;
        var coords;
        var lat = this.mapCurrent.lat;
        var lng = this.mapCurrent.lng;

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
            if (('https:' === window.location.protocol) && navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var geoLat = position.coords.latitude;
                    var geoLng = position.coords.longitude;
                    coords = {
                        'lat': geoLat,
                        'lng': geoLng
                    };
                    // Because geolocation is asynchronous, load map here
                    var center = new google.maps.LatLng(geoLat, geoLng);
                    parent.$fieldSetting.lat.val(geoLat);
                    parent.$fieldSetting.lng.val(geoLng);
                    g.marker.setPosition(center);
                });
            }
        }

        return coords;
    },

    // Check width of subfield
    _subfieldWidth: function(subfield, $tr) {
        var $ltWidth = $tr.find('.layout-table-width input');
        var $bpField = this.$blueprintPanel.find('.blueprint-' + subfield);
        var width = $ltWidth.val();
        if (!width || isNaN(width) || (width > 100)) {
            width = 100;
        } else if (width < 10) {
            width = 10;
        }
        width = parseInt(width);
        $ltWidth.val(width);
        $bpField.css({
            'float': 'left',
            'width': (width - 1) + '%',
            'margin-left': '1%'
        });
        this.layout[subfield]['width'] = width;
    },

    // Check if subfield is enabled
    _subfieldEnabled: function(subfield, $tr) {
        var $bpField = this.$blueprintPanel.find('.blueprint-' + subfield);
        var checked  = $tr.find('.layout-table-enable input').is(':checked');
        if (checked) {
            $tr.removeClass('disabled');
            $bpField.show();
        } else {
            $tr.addClass('disabled');
            $bpField.hide();
        }
        this.layout[subfield]['enable'] = (checked ? 1 : 0);
    },

    // Check subfield position
    _subfieldPosition: function(subfield, position) {
        this.layout[subfield]['position'] = position;
    },

    // Move a row in the blueprint
    _moveBlueprintRow: function(subfield) {
        var $bpField = this.$blueprintPanel.find('.blueprint-' + subfield);
        this.$blueprintPanel.append($bpField);
    },

    // Update blueprint of field layout
    _updateBlueprint: function() {
        var parent = this;
        // Re-initialize layout data
        this.layout = {};
        // Loop through subfields
        this.$layoutTable.find('tr.layout-table-subfield').each(function (i) {
            var subfield = $(this).data('subfield');
            parent.layout[subfield] = {};
            parent._subfieldWidth(subfield, $(this));
            parent._subfieldEnabled(subfield, $(this));
            parent._subfieldPosition(subfield, (i + 1));
            parent._moveBlueprintRow(subfield);
        });
        // Update blueprint data
        this.$values.val(JSON.stringify(this.layout));
    }

});
