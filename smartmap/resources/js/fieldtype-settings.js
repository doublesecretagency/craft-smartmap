
// Define field layout object
var SmartMap_FieldLayout = function ($parent) {

	// Define properties
	this.$parent       = $parent;
	this.$layoutValues = $parent.find('.layout-values');
	this.$bpPanel      = $parent.find('.blueprint-panel');

	// Add events
	$layoutTable  = $parent.find('.layout-table');
	$layoutTable.on('change', '.layout-table-enable input', this.blueprint);
	$layoutTable.on('change', '.layout-table-width input', this.blueprint);

	/*
	var tableRowsId = ('.layout-table-rows').replace(/^#/,'');
	var el          = document.getElementById(tableRowsId);

	new Sortable(el, {
		handle: '.move',
		animation: 150,
		ghostClass: 'sortable-ghost',
		onUpdate: this.blueprint
	});
	*/

	// Initialize blueprint
	this.blueprint();
};

// Render blueprint of field layout
SmartMap_FieldLayout.prototype.blueprint = function() {

	console.log('Running blueprint...');

	this.$layoutValues.html('');
	this.$parent.find('.layout-table-row').each(function () {

		// id was converted to class
		var subfield = $(this).attr('id').replace(/^types-SmartMap_Address-layout-table-/, '');

		
		fieldtypeSettings.subfieldEnabled(subfield, $(this));
		fieldtypeSettings.subfieldWidth(subfield, $(this));
		fieldtypeSettings.moveBlueprintRow(subfield);
		this.$bpPanel.find('.clear').appendTo(this.$bpPanel);
	});
};

// Check if subfield is enabled
SmartMap_FieldLayout.prototype.subfieldEnabled = function(subfield, $el) {
	this.$ltRow = $parent.find('.layout-table-' + subfield);
	this.$bpField = $parent.find('.blueprint-' + subfield);
	var checked  = $el.find('.layout-table-enable input').is(':checked');
	if (checked) {
		$ltRow.removeClass('disabled');
		$bpField.show();
	} else {
		$ltRow.addClass('disabled');
		$bpField.hide();
	}
	this.appendLayoutValue(subfield, 'enable', (checked ? 1 : 0));
};

// Check width of subfield
SmartMap_FieldLayout.prototype.subfieldWidth = function(subfield, $el) {
	this.$ltWidth = $parent.find('.layout-table-' + subfield + ' .layout-table-width input');
	this.$bpField = $parent.find('.blueprint-' + subfield);
	var width = $ltWidth.val();
	if (!width || isNaN(width) || (width > 100)) {
		width = 100;
	} else if (width < 10) {
		width = 10;
	}
	$ltWidth.val(width);
	$bpField.css({
		'float': 'left',
		'width': (width - 1) + '%',
		'margin-left': '1%'
	});
	this.appendLayoutValue(subfield, 'width', parseInt(width));
};

// Keep track of layout values via hidden fields
SmartMap_FieldLayout.prototype.appendLayoutValue = function(subfield, type, value) {
	var name = 'types[SmartMap_Address][layout][' + subfield + '][' + type + ']';
	var hidden = '<input type="hidden" name="' + name + '" value="' + value + '" />';
	this.$layoutValues.append(hidden);
};

// Move a row in the blueprint
SmartMap_FieldLayout.prototype.moveBlueprintRow = function(subfield) {
	this.$parent.find('.blueprint-' + subfield).appendTo(this.$bpPanel);
};


// =================================================================================================== //

// When page loads, initialize each field layout
$('.smartmap-fieldtype').each(function () {
	console.log('Looping through settings fields...');
	new SmartMap_FieldLayout($(this));
});
