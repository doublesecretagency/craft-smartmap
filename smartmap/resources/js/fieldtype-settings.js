
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
