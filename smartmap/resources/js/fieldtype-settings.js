var prefix        = '#types-SmartMap_Address-';
var $layoutValues = $(prefix + 'layout-values');
var $layoutTable  = $(prefix + 'layout-table');
var $bpPanel      = $(prefix + 'blueprint-panel');
var $bpClear      = $(prefix + 'blueprint-panel .clear');

var tableRowsId   = (prefix + 'layout-table-rows').replace(/^#/,'');

var fieldtypeSettings = {
	blueprint: function () {
		$layoutValues.html('');
		$('.layout-table-row').each(function () {
			var subfield = $(this).attr('id').replace(/^types-SmartMap_Address-layout-table-/, '');
			fieldtypeSettings.subfieldEnabled(subfield, $(this));
			fieldtypeSettings.subfieldWidth(subfield, $(this));
			fieldtypeSettings.moveBlueprintRow(subfield);
			$bpClear.appendTo($bpPanel);
		});
	},
	subfieldEnabled: function (subfield, $el) {
		var $ltRow = $(prefix + 'layout-table-' + subfield);
		var $bpField = $(prefix + 'blueprint-' + subfield);
		var checked  = $el.find('.layout-table-enable input').is(':checked');
		if (checked) {
			$ltRow.removeClass('disabled');
			$bpField.show();
		} else {
			$ltRow.addClass('disabled');
			$bpField.hide();
		}
		this.appendLayoutValue(subfield, 'enable', (checked ? 1 : 0));
	},
	subfieldWidth: function (subfield, $el) {
		var $ltWidth = $(prefix + 'layout-table-' + subfield + ' .layout-table-width input');
		var $bpField = $(prefix + 'blueprint-' + subfield);
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
	},
	appendLayoutValue: function (subfield, type, value) {
		var name = 'types[SmartMap_Address][layout][' + subfield + '][' + type + ']';
		var hidden = '<input type="hidden" name="' + name + '" value="' + value + '" />';
		$layoutValues.append(hidden);
	},
	moveBlueprintRow: function (subfield) {
		$(prefix + 'blueprint-' + subfield).appendTo($bpPanel);
	}
}

// Initialize blueprint
$(fieldtypeSettings.blueprint);
// Add events
$layoutTable.on('change', '.layout-table-enable input', fieldtypeSettings.blueprint);
$layoutTable.on('change', '.layout-table-width input', fieldtypeSettings.blueprint);

$(function() {
	// Configure sortable table of subfields
	var el = document.getElementById(tableRowsId);
	var sortable = new Sortable(el, {
		handle: '.move',
		animation: 150,
		ghostClass: 'sortable-ghost',
		onUpdate: fieldtypeSettings.blueprint
	});
});
