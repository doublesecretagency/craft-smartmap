
// Define field layout object
var SmartMap_FieldLayout = function ($fieldtype) {
	var parent = this;
	// Define properties
	this.$fieldtype    = $fieldtype;
	this.$layoutValues = $fieldtype.find('.smartmap-fieldtype-layout-values');
	this.$bpPanel      = $fieldtype.find('.blueprint-panel');
	// Add events
	var triggerInputs = '.layout-table-enable input, .layout-table-width input';
	$layoutTable = $fieldtype.find('.smartmap-fieldtype-layout-table');
	$layoutTable.on('change', triggerInputs, function () {parent.blueprint();});
	// Initialize sortable rows
	$('.layout-table-rows').each(function () {
		new Sortable(this, {
			handle: '.move',
			animation: 150,
			ghostClass: 'sortable-ghost',
			onUpdate: function () {parent.blueprint();}
		});
	});
	// Initialize blueprint
	this.blueprint();
};

// Render blueprint of field layout
SmartMap_FieldLayout.prototype.blueprint = function() {

	console.log('Running blueprint...');

	var parent = this;
	//this.$layoutValues.html('');
	this.$fieldtype.find('.layout-table-row').each(function () {
		var subfield = $(this).data('subfield');
		parent._subfieldEnabled(subfield, $(this));
		parent._subfieldWidth(subfield, $(this));
		parent._moveBlueprintRow(subfield);
	});
	this.$bpPanel.find('.clear').appendTo(this.$bpPanel);
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
	this._appendLayoutValue(subfield, 'enable', (checked ? 1 : 0));
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
	this._appendLayoutValue(subfield, 'width', parseInt(width));
};

// Keep track of layout values via hidden fields
SmartMap_FieldLayout.prototype._appendLayoutValue = function(subfield, type, value) {
	var name = 'types[SmartMap_Address][layout][' + subfield + '][' + type + ']';
	var hidden = '<input type="hidden" name="' + name + '" value="' + value + '" />';
	//this.$layoutValues.append(hidden);
};

// Move a row in the blueprint
SmartMap_FieldLayout.prototype._moveBlueprintRow = function(subfield) {
	this.$fieldtype.find('.blueprint-' + subfield).appendTo(this.$bpPanel);
};


// =================================================================================================== //

// When page loads, initialize each field layout
$('.smartmap-fieldtype').each(function () {
	console.log('Looping through settings fields...');
	new SmartMap_FieldLayout($(this));
});
